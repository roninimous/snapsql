<?php

namespace App\Jobs;

use App\Models\Backup;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

class RestoreDatabase implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Backup $backup
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $tempFile = null;

        try {
            // 1. Download backup to temp file if not local or ensures it's accessible
            // Since we only support local for now based on previous code context (DatabaseController checks Storage::disk('local')),
            // we can try to use the path directly if possible, but safer to copy to temp in case of permissions or cloud storage later.

            $tempFile = $this->prepareBackupFile();

            if (!File::exists($tempFile)) {
                throw new \RuntimeException('Backup file could not be prepared for restore');
            }

            // 2. Perform Restore
            $this->performRestore($tempFile);

            Log::info('Database restored successfully', ['backup_id' => $this->backup->id]);

        } catch (\Exception $e) {
            Log::error('Database restore failed', [
                'backup_id' => $this->backup->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to fail the job
        } finally {
            if ($tempFile && File::exists($tempFile)) {
                File::delete($tempFile);
            }
        }
    }

    private function prepareBackupFile(): string
    {
        $tempFile = sys_get_temp_dir() . '/snapsql_restore_' . uniqid() . '.sql';

        // Assuming local disk for now as per controller logic, allowing extensibility
        if (Storage::disk('local')->exists($this->backup->file_path)) {
            $content = Storage::disk('local')->get($this->backup->file_path);
            File::put($tempFile, $content);
            return $tempFile;
        }

        throw new \RuntimeException('Backup file not found in storage');
    }

    private function performRestore(string $sqlFile): void
    {
        $mysqlPath = $this->findMysql();
        $database = $this->backup->database;

        if (!$mysqlPath) {
            throw new \RuntimeException('mysql command not found');
        }

        // Construct command: mysql -u user -p password -h host -P port dbname < file.sql
        // Use Process::run with input redirection or shell construction. 
        // Process::run doesn't support '<' redirection easily in array mode for all shells reliably without 'sh -c'.
        // But we can read file content and pass as input? No, too big.
        // Best to use 'sh -c'.

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($mysqlPath),
            escapeshellarg($database->host),
            escapeshellarg($database->port),
            escapeshellarg($database->username),
            escapeshellarg($database->password ?? ''),
            escapeshellarg($database->database),
            escapeshellarg($sqlFile)
        );

        // Mask password in logs? Process::run might log it?
        // We execute explicitly using sh -c to support input redirection (<) safely.

        $shCommand = [
            'sh',
            '-c',
            $command
        ];

        $result = Process::run($shCommand);

        if (!$result->successful()) {
            throw new \RuntimeException('Restore failed: ' . $result->errorOutput());
        }
    }

    private function findMysql(): ?string
    {
        $customPath = env('MYSQL_PATH'); // Allow env override

        if ($customPath && file_exists($customPath) && is_executable($customPath)) {
            return $customPath;
        }

        $commonPaths = [
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/bin/mysql',
            '/opt/homebrew/bin/mysql', // Homebrew on Mac
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                return $path;
            }
        }

        $whichResult = Process::run(['which', 'mysql']);
        if ($whichResult->successful()) {
            $path = trim($whichResult->output());
            if (!empty($path) && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
