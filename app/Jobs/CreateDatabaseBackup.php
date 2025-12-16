<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Models\Database;
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
    ) {}

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

            if (! File::exists($tempFile)) {
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
        $tempFile = sys_get_temp_dir().'/snapsql_backup_'.uniqid().'.sql';

        $command = [
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--routines',
            '--triggers',
            '--events',
            '--add-drop-table',
            '--no-tablespaces',
            '--host='.$this->database->host,
            '--port='.$this->database->port,
            '--user='.$this->database->username,
            '--password='.$this->database->password,
            $this->database->database,
        ];

        $result = Process::run($command);

        if (! $result->successful()) {
            throw new \RuntimeException('mysqldump failed: '.$result->errorOutput());
        }

        File::put($tempFile, $result->output());

        return $tempFile;
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

        if (! $destination || ! $destination->is_active) {
            return;
        }

        if ($destination->type === 'local') {
            return;
        }

        match ($destination->type) {
            's3' => $this->uploadToS3($destination, $tempFile, $backup->filename),
            'ftp' => $this->uploadToFtp($destination, $tempFile, $backup->filename),
            'sftp' => $this->uploadToSftp($destination, $tempFile, $backup->filename),
            default => Log::warning("Unknown backup destination type: {$destination->type}"),
        };
    }

    /**
     * Upload to S3.
     */
    private function uploadToS3($destination, string $tempFile, string $filename): void
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('S3 credentials not configured');
        }

        $s3Path = rtrim($destination->path, '/').'/'.$filename;

        $s3Disk = Storage::build([
            'driver' => 's3',
            'key' => $credentials['key'] ?? null,
            'secret' => $credentials['secret'] ?? null,
            'region' => $credentials['region'] ?? 'us-east-1',
            'bucket' => $credentials['bucket'] ?? null,
            'url' => $credentials['url'] ?? null,
            'endpoint' => $credentials['endpoint'] ?? null,
            'use_path_style_endpoint' => $credentials['use_path_style_endpoint'] ?? false,
        ]);

        $s3Disk->put($s3Path, File::get($tempFile));
    }

    /**
     * Upload to FTP.
     */
    private function uploadToFtp($destination, string $tempFile, string $filename): void
    {
        if (! extension_loaded('ftp')) {
            throw new \RuntimeException('FTP extension is not installed');
        }

        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('FTP credentials not configured');
        }

        $ftpPath = rtrim($destination->path, '/').'/'.$filename;

        $connection = @ftp_connect($credentials['host'] ?? 'localhost', $credentials['port'] ?? 21);

        if (! $connection) {
            throw new \RuntimeException('Failed to connect to FTP server');
        }

        try {
            if (! @ftp_login($connection, $credentials['username'] ?? '', $credentials['password'] ?? '')) {
                throw new \RuntimeException('FTP authentication failed');
            }

            if (isset($credentials['passive']) && $credentials['passive']) {
                @ftp_pasv($connection, true);
            }

            if (! @ftp_put($connection, $ftpPath, $tempFile, FTP_BINARY)) {
                throw new \RuntimeException('Failed to upload file to FTP server');
            }
        } finally {
            @ftp_close($connection);
        }
    }

    /**
     * Upload to SFTP.
     */
    private function uploadToSftp($destination, string $tempFile, string $filename): void
    {
        if (! extension_loaded('ssh2')) {
            throw new \RuntimeException('SSH2 extension is not installed');
        }

        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('SFTP credentials not configured');
        }

        $sftpPath = rtrim($destination->path, '/').'/'.$filename;

        $connection = @ssh2_connect($credentials['host'] ?? 'localhost', $credentials['port'] ?? 22);

        if (! $connection) {
            throw new \RuntimeException('Failed to connect to SFTP server');
        }

        if (! @ssh2_auth_password($connection, $credentials['username'] ?? '', $credentials['password'] ?? '')) {
            throw new \RuntimeException('SFTP authentication failed');
        }

        $sftp = @ssh2_sftp($connection);

        if (! $sftp) {
            throw new \RuntimeException('Failed to initialize SFTP subsystem');
        }

        $stream = @fopen("ssh2.sftp://{$sftp}{$sftpPath}", 'w');

        if (! $stream) {
            throw new \RuntimeException('Failed to open SFTP file stream');
        }

        try {
            $fileContents = File::get($tempFile);

            if (@fwrite($stream, $fileContents) === false) {
                throw new \RuntimeException('Failed to write file to SFTP server');
            }
        } finally {
            @fclose($stream);
        }
    }
}
