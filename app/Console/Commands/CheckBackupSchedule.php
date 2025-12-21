<?php

namespace App\Console\Commands;

use App\Jobs\CreateDatabaseBackup;
use App\Models\Database;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckBackupSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backups:check-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for databases that need backups and dispatch backup jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lock = Cache::lock('backup-schedule-check', 60);

        if (! $lock->get()) {
            $this->warn('Another backup schedule check is already running. Skipping...');

            return Command::SUCCESS;
        }

        try {
            $databases = Database::where('is_active', true)
                ->where('backup_frequency', '!=', 'manual')
                ->get();

            $dispatched = 0;

            foreach ($databases as $database) {
                if ($this->isBackupDue($database) && ! $this->hasBackupInProgress($database)) {
                    CreateDatabaseBackup::dispatch($database);
                    $dispatched++;
                    $this->info("Dispatched backup job for database: {$database->name}");
                }
            }

            if ($dispatched > 0) {
                $this->info("Dispatched {$dispatched} backup job(s).");
            } else {
                $this->info('No backups due at this time.');
            }

            return Command::SUCCESS;
        } finally {
            $lock->release();
        }
    }

    /**
     * Check if a backup is due for the given database.
     */
    private function isBackupDue(Database $database): bool
    {
        $lastBackup = $database->backups()
            ->whereIn('status', ['completed', 'failed'])
            ->latest('started_at')
            ->first();

        $userTimezone = $database->user->timezone ?? 'UTC';
        $now = now()->setTimezone($userTimezone);

        // Parse the backup start time (HH:MM format)
        $startTime = $database->backup_start_time ?? '00:00:00';
        [$hour, $minute] = explode(':', $startTime);

        // Calculate the next scheduled backup time
        $nextScheduledTime = $this->calculateNextScheduledTime($database, $lastBackup, $now, (int) $hour, (int) $minute);

        if (! $nextScheduledTime) {
            return false;
        }

        // Check if we've passed the scheduled time (with 10-second buffer)
        return $nextScheduledTime->isBefore($now->copy()->addSeconds(10));
    }

    /**
     * Calculate the next scheduled backup time based on frequency and start time.
     */
    private function calculateNextScheduledTime(Database $database, $lastBackup, $now, int $hour, int $minute): ?\Carbon\Carbon
    {
        $userTimezone = $database->user->timezone ?? 'UTC';

        // If no backup exists yet, schedule for the next occurrence of the start time
        if (! $lastBackup) {
            $nextTime = $now->copy()->setTime($hour, $minute, 0);

            // If the time has passed today, schedule for the next period
            if ($nextTime->isPast()) {
                return match ($database->backup_frequency) {
                    'hourly' => $now->copy()->setMinute($minute)->setSecond(0)->addHour(),
                    'daily' => $nextTime->addDay(),
                    'weekly' => $nextTime->addWeek(),
                    'custom' => $this->calculateNextCustomTime($database, $now, $hour, $minute),
                    default => null,
                };
            }

            return $nextTime;
        }

        // Calculate next time based on last backup
        $lastBackupTime = $lastBackup->started_at
            ? $lastBackup->started_at->setTimezone($userTimezone)
            : $lastBackup->created_at->setTimezone($userTimezone);

        return match ($database->backup_frequency) {
            'hourly' => $lastBackupTime->copy()->addHour()->setMinute($minute)->setSecond(0),
            'daily' => $lastBackupTime->copy()->addDay()->setTime($hour, $minute, 0),
            'weekly' => $lastBackupTime->copy()->addWeek()->setTime($hour, $minute, 0),
            'custom' => $this->calculateNextCustomTime($database, $lastBackupTime, $hour, $minute),
            default => null,
        };
    }

    /**
     * Calculate next custom backup time.
     */
    private function calculateNextCustomTime(Database $database, $fromTime, int $hour, int $minute): ?\Carbon\Carbon
    {
        if (! $database->custom_backup_interval_minutes || $database->custom_backup_interval_minutes < 1) {
            return null;
        }

        $intervalMinutes = $database->custom_backup_interval_minutes;
        $userTimezone = $database->user->timezone ?? 'UTC';

        // Start from the configured start time today
        $startOfPeriod = $fromTime->copy()->setTime($hour, $minute, 0);

        // If we're calculating from "now" and haven't started yet
        if (! $database->backups()->whereIn('status', ['completed', 'failed'])->exists()) {
            if ($startOfPeriod->isPast()) {
                // Calculate how many intervals have passed since start time
                $minutesSinceStart = $fromTime->diffInMinutes($startOfPeriod);
                $intervalsPassed = ceil($minutesSinceStart / $intervalMinutes);

                return $startOfPeriod->addMinutes($intervalsPassed * $intervalMinutes);
            }

            return $startOfPeriod;
        }

        // Add the interval to the last backup time
        return $fromTime->copy()->addMinutes($intervalMinutes);
    }

    /**
     * Check if there's already a backup in progress for the database.
     */
    private function hasBackupInProgress(Database $database): bool
    {
        return $database->backups()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();
    }
}
