<?php

namespace App\Services;

use App\Models\BackupDestination;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupDestinationService
{
    /**
     * Upload a file to the backup destination.
     */
    public function upload(BackupDestination $destination, string $filePath, string $filename): void
    {
        match ($destination->type) {
            'local' => $this->uploadToLocal($destination, $filePath, $filename),
            's3' => $this->uploadToS3($destination, $filePath, $filename),
            'b2' => $this->uploadToB2($destination, $filePath, $filename),
            'gdrive' => $this->uploadToGoogleDrive($destination, $filePath, $filename),
            'ftp' => $this->uploadToFtp($destination, $filePath, $filename),
            'sftp' => $this->uploadToSftp($destination, $filePath, $filename),
            default => throw new \RuntimeException("Unsupported destination type: {$destination->type}"),
        };
    }

    /**
     * Test the backup destination connection and credentials.
     */
    public function test(BackupDestination $destination): bool
    {
        try {
            return match ($destination->type) {
                'local' => $this->testLocal($destination),
                's3' => $this->testS3($destination),
                'b2' => $this->testB2($destination),
                'gdrive' => $this->testGoogleDrive($destination),
                'ftp' => $this->testFtp($destination),
                'sftp' => $this->testSftp($destination),
                default => throw new \RuntimeException("Unsupported destination type: {$destination->type}"),
            };
        } catch (\Exception $e) {
            Log::error('Backup destination test failed', [
                'destination_id' => $destination->id,
                'type' => $destination->type,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Upload to local storage.
     */
    private function uploadToLocal(BackupDestination $destination, string $filePath, string $filename): void
    {
        $fullPath = rtrim($destination->path, '/') . '/' . $filename;
        Storage::disk('local')->put($fullPath, File::get($filePath));
    }

    /**
     * Test local storage.
     */
    private function testLocal(BackupDestination $destination): bool
    {
        $testPath = rtrim($destination->path, '/') . '/test_' . uniqid() . '.txt';
        $testContent = 'SnapsQL connection test - ' . now()->toDateTimeString();

        try {
            Storage::disk('local')->put($testPath, $testContent);
            Storage::disk('local')->delete($testPath);

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Local storage test failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload to S3.
     */
    private function uploadToS3(BackupDestination $destination, string $filePath, string $filename): void
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('S3 credentials not configured');
        }

        $s3Path = rtrim($destination->path, '/') . '/' . $filename;

        $s3Disk = Storage::build([
            'driver' => 's3',
            'key' => $credentials['key'] ?? null,
            'secret' => $credentials['secret'] ?? null,
            'region' => $credentials['region'] ?? 'auto',
            'bucket' => $credentials['bucket'] ?? null,
            'url' => $credentials['url'] ?? null,
            'endpoint' => $credentials['endpoint'] ?? null,
            'use_path_style_endpoint' => true,
        ]);

        $s3Disk->put($s3Path, File::get($filePath));
    }

    /**
     * Test S3 connection.
     */
    private function testS3(BackupDestination $destination): bool
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('S3 credentials not configured');
        }

        try {
            $client = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => 'auto',
                'endpoint' => $credentials['endpoint'] ?? null,
                'credentials' => [
                    'key' => $credentials['key'] ?? null,
                    'secret' => $credentials['secret'] ?? null,
                ],
                'use_path_style_endpoint' => true,
            ]);

            $testFile = 'snapsql_test_' . time() . '.txt';

            // Try to write a tiny test file
            $client->putObject([
                'Bucket' => $credentials['bucket'] ?? null,
                'Key' => $testFile,
                'Body' => 'Connection test from SnapsQL',
            ]);

            // Try to delete it (optional, but good for cleanup)
            $client->deleteObject([
                'Bucket' => $credentials['bucket'] ?? null,
                'Key' => $testFile,
            ]);

            return true;
        } catch (\Aws\S3\Exception\S3Exception $e) {
            throw new \RuntimeException('S3 connection test failed: ' . ($e->getAwsErrorMessage() ?: $e->getMessage()));
        } catch (\Exception $e) {
            throw new \RuntimeException('S3 connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload to Backblaze B2.
     */
    private function uploadToB2(BackupDestination $destination, string $filePath, string $filename): void
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('B2 credentials not configured');
        }

        $b2Path = rtrim($destination->path, '/') . '/' . $filename;

        $b2Disk = Storage::build([
            'driver' => 's3',
            'key' => $credentials['key_id'] ?? null,
            'secret' => $credentials['application_key'] ?? null,
            'region' => $credentials['region'] ?? 'us-west-000',
            'bucket' => $credentials['bucket'] ?? null,
            'endpoint' => 'https://s3.' . $credentials['region'] ?? 'us-west-000' . '.backblazeb2.com',
            'use_path_style_endpoint' => true,
        ]);

        $b2Disk->put($b2Path, File::get($filePath));
    }

    /**
     * Test Backblaze B2 connection.
     */
    private function testB2(BackupDestination $destination): bool
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('B2 credentials not configured');
        }

        $testPath = rtrim($destination->path, '/') . '/test_' . uniqid() . '.txt';
        $testContent = 'SnapsQL connection test - ' . now()->toDateTimeString();

        $b2Disk = Storage::build([
            'driver' => 's3',
            'key' => $credentials['key_id'] ?? null,
            'secret' => $credentials['application_key'] ?? null,
            'region' => $credentials['region'] ?? 'us-west-000',
            'bucket' => $credentials['bucket'] ?? null,
            'endpoint' => 'https://s3.' . $credentials['region'] ?? 'us-west-000' . '.backblazeb2.com',
            'use_path_style_endpoint' => true,
        ]);

        try {
            $b2Disk->put($testPath, $testContent);
            $b2Disk->delete($testPath);

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('B2 connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload to Google Drive.
     */
    private function uploadToGoogleDrive(BackupDestination $destination, string $filePath, string $filename): void
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('Google Drive credentials not configured');
        }

        $accessToken = $credentials['access_token'] ?? null;
        $folderId = $destination->path ?: 'root';

        if (!$accessToken) {
            throw new \RuntimeException('Google Drive access token not provided');
        }

        $fileContents = File::get($filePath);
        $mimeType = 'application/octet-stream';

        $metadata = [
            'name' => $filename,
        ];

        if ($folderId !== 'root') {
            $metadata['parents'] = [$folderId];
        }

        $boundary = '----WebKitFormBoundary' . uniqid();
        $delimiter = "\r\n--{$boundary}\r\n";
        $closeDelimiter = "\r\n--{$boundary}--\r\n";

        $body = $delimiter
            . 'Content-Type: application/json; charset=UTF-8' . "\r\n\r\n"
            . json_encode($metadata)
            . $delimiter
            . 'Content-Type: ' . $mimeType . "\r\n"
            . 'Content-Transfer-Encoding: binary' . "\r\n\r\n"
            . $fileContents
            . $closeDelimiter;

        $response = Http::withToken($accessToken)
            ->withBody($body, 'multipart/related; boundary=' . $boundary)
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

        if (!$response->successful()) {
            throw new \RuntimeException('Google Drive upload failed: ' . $response->body());
        }
    }

    /**
     * Test Google Drive connection.
     */
    private function testGoogleDrive(BackupDestination $destination): bool
    {
        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('Google Drive credentials not configured');
        }

        $accessToken = $credentials['access_token'] ?? null;

        if (!$accessToken) {
            throw new \RuntimeException('Google Drive access token not provided');
        }

        try {
            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/drive/v3/about', [
                    'fields' => 'user',
                ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Google Drive API request failed: ' . $response->body());
            }

            $folderId = $destination->path ?: 'root';

            if ($folderId !== 'root') {
                $folderResponse = Http::withToken($accessToken)
                    ->get("https://www.googleapis.com/drive/v3/files/{$folderId}", [
                        'fields' => 'id,name',
                    ]);

                if (!$folderResponse->successful()) {
                    throw new \RuntimeException('Google Drive folder not found or inaccessible');
                }
            }

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException('Google Drive connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload to FTP.
     */
    private function uploadToFtp(BackupDestination $destination, string $filePath, string $filename): void
    {
        if (!extension_loaded('ftp')) {
            throw new \RuntimeException('FTP extension is not installed');
        }

        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('FTP credentials not configured');
        }

        $ftpPath = rtrim($destination->path, '/') . '/' . $filename;

        $connection = @ftp_connect($credentials['host'] ?? 'localhost', $credentials['port'] ?? 21);

        if (!$connection) {
            throw new \RuntimeException('Failed to connect to FTP server');
        }

        try {
            if (!@ftp_login($connection, $credentials['username'] ?? '', $credentials['password'] ?? '')) {
                throw new \RuntimeException('FTP authentication failed');
            }

            if (isset($credentials['passive']) && $credentials['passive']) {
                @ftp_pasv($connection, true);
            }

            if (!@ftp_put($connection, $ftpPath, $filePath, FTP_BINARY)) {
                throw new \RuntimeException('Failed to upload file to FTP server');
            }
        } finally {
            @ftp_close($connection);
        }
    }

    /**
     * Test FTP connection.
     */
    private function testFtp(BackupDestination $destination): bool
    {
        if (!extension_loaded('ftp')) {
            throw new \RuntimeException('FTP extension is not installed');
        }

        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('FTP credentials not configured');
        }

        $connection = @ftp_connect($credentials['host'] ?? 'localhost', $credentials['port'] ?? 21);

        if (!$connection) {
            throw new \RuntimeException('Failed to connect to FTP server');
        }

        try {
            if (!@ftp_login($connection, $credentials['username'] ?? '', $credentials['password'] ?? '')) {
                throw new \RuntimeException('FTP authentication failed');
            }

            if (isset($credentials['passive']) && $credentials['passive']) {
                @ftp_pasv($connection, true);
            }

            $testPath = rtrim($destination->path, '/') . '/test_' . uniqid() . '.txt';
            $testContent = 'SnapsQL connection test - ' . now()->toDateTimeString();
            $tempFile = sys_get_temp_dir() . '/snapsql_ftp_test_' . uniqid() . '.txt';

            File::put($tempFile, $testContent);

            try {
                if (!@ftp_put($connection, $testPath, $tempFile, FTP_BINARY)) {
                    throw new \RuntimeException('Failed to upload test file');
                }

                @ftp_delete($connection, $testPath);

                return true;
            } finally {
                if (File::exists($tempFile)) {
                    File::delete($tempFile);
                }
            }
        } finally {
            @ftp_close($connection);
        }
    }

    /**
     * Upload to SFTP.
     */
    private function uploadToSftp(BackupDestination $destination, string $filePath, string $filename): void
    {
        if (!extension_loaded('ssh2')) {
            throw new \RuntimeException('SSH2 extension is not installed');
        }

        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('SFTP credentials not configured');
        }

        $sftpPath = rtrim($destination->path, '/') . '/' . $filename;

        $connection = @ssh2_connect($credentials['host'] ?? 'localhost', $credentials['port'] ?? 22);

        if (!$connection) {
            throw new \RuntimeException('Failed to connect to SFTP server');
        }

        if (!@ssh2_auth_password($connection, $credentials['username'] ?? '', $credentials['password'] ?? '')) {
            throw new \RuntimeException('SFTP authentication failed');
        }

        $sftp = @ssh2_sftp($connection);

        if (!$sftp) {
            throw new \RuntimeException('Failed to initialize SFTP subsystem');
        }

        $stream = @fopen("ssh2.sftp://{$sftp}{$sftpPath}", 'w');

        if (!$stream) {
            throw new \RuntimeException('Failed to open SFTP file stream');
        }

        try {
            $fileContents = File::get($filePath);

            if (@fwrite($stream, $fileContents) === false) {
                throw new \RuntimeException('Failed to write file to SFTP server');
            }
        } finally {
            @fclose($stream);
        }
    }

    /**
     * Test SFTP connection.
     */
    private function testSftp(BackupDestination $destination): bool
    {
        if (!extension_loaded('ssh2')) {
            throw new \RuntimeException('SSH2 extension is not installed');
        }

        $credentials = $destination->credentials;

        if (empty($credentials)) {
            throw new \RuntimeException('SFTP credentials not configured');
        }

        $connection = @ssh2_connect($credentials['host'] ?? 'localhost', $credentials['port'] ?? 22);

        if (!$connection) {
            throw new \RuntimeException('Failed to connect to SFTP server');
        }

        if (!@ssh2_auth_password($connection, $credentials['username'] ?? '', $credentials['password'] ?? '')) {
            throw new \RuntimeException('SFTP authentication failed');
        }

        $sftp = @ssh2_sftp($connection);

        if (!$sftp) {
            throw new \RuntimeException('Failed to initialize SFTP subsystem');
        }

        $testPath = rtrim($destination->path, '/') . '/test_' . uniqid() . '.txt';
        $testContent = 'SnapsQL connection test - ' . now()->toDateTimeString();

        $stream = @fopen("ssh2.sftp://{$sftp}{$testPath}", 'w');

        if (!$stream) {
            throw new \RuntimeException('Failed to open SFTP file stream');
        }

        try {
            if (@fwrite($stream, $testContent) === false) {
                throw new \RuntimeException('Failed to write test file');
            }
        } finally {
            @fclose($stream);
        }

        try {
            @unlink("ssh2.sftp://{$sftp}{$testPath}");
        } catch (\Exception $e) {
            Log::warning('Failed to delete SFTP test file', ['path' => $testPath, 'error' => $e->getMessage()]);
        }

        return true;
    }
}
