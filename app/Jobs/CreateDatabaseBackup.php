<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Models\Database;
use App\Services\BackupDestinationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class CreateDatabaseBackup implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Database $database
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $backup = $this->database->backups()->create([
            'filename' => '',
            'file_path' => '',
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $tempFile = null;

        try {
            $tempFile = $this->createDump($backup);

            if (!File::exists($tempFile)) {
                throw new \RuntimeException('Dump file was not created');
            }

            $fileSize = File::size($tempFile);
            $filename = $this->generateFilename();
            $localPath = $this->saveToLocalStorage($tempFile, $filename);

            $backup->update([
                'filename' => $filename,
                'file_path' => $localPath,
                'file_size' => $fileSize,
            ]);

            $this->uploadToCloudDestination($backup, $tempFile);

            $backup->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Database backup failed', [
                'database_id' => $this->database->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $backup->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            $this->sendFailureNotification($e->getMessage());
        } finally {
            if ($tempFile && File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    /**
     * Create database dump using mysqldump.
     */
    private function createDump(Backup $backup): string
    {
        $tempFile = sys_get_temp_dir() . '/snapsql_backup_' . uniqid() . '.sql';

        $mysqldumpPath = $this->findMysqldump();

        if (!$mysqldumpPath) {
            throw new \RuntimeException('mysqldump command not found. Please install mysql-client or mariadb-client package.');
        }

        $command = [
            $mysqldumpPath,
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--routines=false',
            '--skip-triggers',
            '--skip-events',
            '--add-drop-table',
            '--no-tablespaces',
            '--host=' . $this->database->host,
            '--port=' . $this->database->port,
            '--user=' . $this->database->username,
            '--password=' . ($this->database->password ?? ''),
            $this->database->database,
        ];

        $result = Process::run($command);

        if (!$result->successful()) {
            throw new \RuntimeException('mysqldump failed: ' . $result->errorOutput());
        }

        File::put($tempFile, $result->output());

        return $tempFile;
    }

    /**
     * Find the mysqldump executable path.
     */
    private function findMysqldump(): ?string
    {
        $customPath = env('MYSQLDUMP_PATH');

        if ($customPath && file_exists($customPath) && is_executable($customPath)) {
            return $customPath;
        }

        $commonPaths = [
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            '/bin/mysqldump',
            '/opt/homebrew/bin/mysqldump', // Homebrew on Mac
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $whichResult = Process::run(['which', 'mysqldump']);

        if ($whichResult->successful()) {
            $path = trim($whichResult->output());
            if (!empty($path) && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Generate a unique filename for the backup.
     */
    private function generateFilename(): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $dbName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->database->name);

        return "{$dbName}_{$timestamp}.sql";
    }

    /**
     * Save dump to local storage.
     */
    private function saveToLocalStorage(string $tempFile, string $filename): string
    {
        $storagePath = "backups/{$this->database->id}/{$filename}";
        Storage::disk('local')->put($storagePath, File::get($tempFile));

        return $storagePath;
    }

    /**
     * Upload to cloud destination if configured.
     */
    private function uploadToCloudDestination(Backup $backup, string $tempFile): void
    {
        $destination = $this->database->backupDestination;

        if (!$destination || !$destination->is_active) {
            return;
        }

        if ($destination->type === 'local') {
            return;
        }

        $service = app(BackupDestinationService::class);
        $service->upload($destination, $tempFile, $backup->filename);
    }
}
