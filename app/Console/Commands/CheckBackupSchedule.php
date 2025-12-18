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

        if (!$lock->get()) {
            $this->warn('Another backup schedule check is already running. Skipping...');

            return Command::SUCCESS;
        }

        try {
            $databases = Database::where('is_active', true)
                ->where('backup_frequency', '!=', 'manual')
                ->get();

            $dispatched = 0;

            foreach ($databases as $database) {
                if ($this->isBackupDue($database) && !$this->hasBackupInProgress($database)) {
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

        if (!$lastBackup) {
            return true;
        }

        $lastBackupTime = $lastBackup->started_at ?? $lastBackup->created_at;

        // We subtract 10 seconds to account for slight timing variations in the scheduler
        // so it triggers within the intended minute window.
        $checkTime = now()->addSeconds(10);

        return match ($database->backup_frequency) {
            'hourly' => $lastBackupTime->copy()->addHour()->isBefore($checkTime),
            'daily' => $lastBackupTime->copy()->addDay()->isBefore($checkTime),
            'weekly' => $lastBackupTime->copy()->addWeek()->isBefore($checkTime),
            'custom' => $this->isCustomBackupDue($database, $lastBackupTime, $checkTime),
            default => false,
        };
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

    /**
     * Check if a custom backup is due based on the interval in minutes.
     */
    private function isCustomBackupDue(Database $database, $lastBackupTime, $checkTime): bool
    {
        if (!$database->custom_backup_interval_minutes || $database->custom_backup_interval_minutes < 1) {
            return false;
        }

        return $lastBackupTime->copy()->addMinutes($database->custom_backup_interval_minutes)->isBefore($checkTime);
    }
}
