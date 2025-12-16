<?php

namespace App\Services;

use App\Models\Database;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PDO;

class SchemaComparisonService
{
    /**
     * Compare the current database schema with the backup file schema.
     *
     * @param Database $database
     * @param string $backupPath
     * @return array{compatible: bool, errors: array<string>}
     */
    public function compare(Database $database, string $backupPath): array
    {
        try {
            $liveSchema = $this->getLiveSchema($database);
            $backupSchema = $this->getBackupSchema($backupPath);

            return $this->detectIncompatibilities($liveSchema, $backupSchema);
        } catch (\Exception $e) {
            return [
                'compatible' => false,
                'errors' => ['Failed to compare schemas: ' . $e->getMessage()],
            ];
        }
    }

    private function getLiveSchema(Database $database): array
    {
        // Connect to the specific database
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s',
            $database->host,
            $database->port,
            $database->database
        );

        $pdo = new PDO($dsn, $database->username, $database->password ?? '', [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        $tables = [];
        $stmt = $pdo->query('SHOW TABLES');
        $tableNames = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tableNames as $tableName) {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `{$tableName}`");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $tables[$tableName] = [];
            foreach ($columns as $column) {
                // Normalize type: int(11) -> int, varchar(255) -> varchar
                // We keep full type for strictness but might need loose usage
                $tables[$tableName][$column['Field']] = strtolower($column['Type']);
            }
        }

        return $tables;
    }

    private function getBackupSchema(string $backupPath): array
    {
        if (!Storage::disk('local')->exists($backupPath)) {
            throw new \RuntimeException("Backup file not found at {$backupPath}");
        }

        // We can't read entire file into memory for large backups, 
        // but schema is usually at the top or scattered. 
        // We iterate line by line.

        $stream = Storage::disk('local')->readStream($backupPath);
        $tables = [];
        $currentTable = null;

        while (($line = fgets($stream)) !== false) {
            $line = trim($line);

            // Detect CREATE TABLE
            if (preg_match('/^CREATE TABLE `([^`]+)`/i', $line, $matches)) {
                $currentTable = $matches[1];
                $tables[$currentTable] = [];
                continue;
            }

            // Detect End of Table
            if ($currentTable && (str_starts_with($line, ') ENGINE=') || str_starts_with($line, ');'))) {
                $currentTable = null;
                continue;
            }

            // Detect Columns inside table definition
            // Previous regex was too restrictive: `/^`([^`]+)` ([a-z0-9()]+)/i`
            // New approach: Capture everything after name, then strip known keywords.
            if ($currentTable && preg_match('/^\s*`([^`]+)`\s+(.*)/', $line, $matches)) {
                $columnName = $matches[1];
                $rawDefinition = $matches[2];

                // Remove trailing comma
                $rawDefinition = rtrim($rawDefinition, ',');

                // Remove known keywords to isolate the type
                // Order matters: Remove attributes from right to left usually, or just replace matches.
                // We must handle 'DEFAULT' values BEFORE stripping 'NULL' or other keywords that might be part of the default value
                $replacements = [
                    '/\s+AUTO_INCREMENT/i' => '',
                    '/\s+DEFAULT\s+(\'[^\']*\'|"[^"]*"|CURRENT_TIMESTAMP|NULL|\S+)/i' => '', // Handle explicit DEFAULT NULL or DEFAULT CURRENT_TIMESTAMP
                    '/\s+NOT NULL/i' => '',
                    '/\s+NULL/i' => '', // if just NULL remains
                    '/\s+COMMENT\s+(\'[^\']*\'|"[^"]*")/i' => '',
                    '/\s+COLLATE\s+\S+/i' => '',
                    '/\s+CHARACTER SET\s+\S+/i' => '',
                    '/\s+ON UPDATE\s+\S+/i' => '', // timestamp updates
                    '/\s+CHECK\s*\(.*\)/i' => '', // CHECK constraints like json_valid
                ];

                $cleanType = $rawDefinition;
                foreach ($replacements as $pattern => $replacement) {
                    $cleanType = preg_replace($pattern, $replacement, $cleanType);
                }

                $columnType = strtolower(trim($cleanType));
                $tables[$currentTable][$columnName] = $columnType;
            }
        }

        fclose($stream);
        return $tables;
    }

    private function detectIncompatibilities(array $liveSchema, array $backupSchema): array
    {
        $errors = [];

        // 1. Check for Missing Tables in Backup (Live has table, Backup doesn't)
        // If we restore, we drop active tables usually (mysqldump has DROP TABLE IF EXISTS).
        // If backup DOESN'T have the table, and mysqldump DOESN'T drop it... then it stays?
        // mysqldump usually only drops tables it is about to create.
        // So valid tables in Live might persist? 
        // BUT if the App expects them, and they persist, it's fine.
        // wait, if backup is OLD, it might NOT have a new table.
        // If we restore, that table remains untouched (if dump doesn't drop it).
        // Is that incompatible? Not necessarily.
        // HOWEVER, "Restore" implies resetting state.
        // If the goal is strictness: "Backup schema must match".
        // Let's flag MISSING TABLES as a warning/error because the App might rely on consistent state between tables.

        foreach ($liveSchema as $tableName => $columns) {
            if (!isset($backupSchema[$tableName])) {
                $errors[] = "Table `{$tableName}` exists in current database but is missing in the backup.";
                continue;
            }

            // 2. Check for Missing Columns or Type Mismatch
            foreach ($columns as $colName => $colType) {
                if (!isset($backupSchema[$tableName][$colName])) {
                    $errors[] = "Column `{$tableName}.{$colName}` is missing in the backup.";
                    continue;
                }

                $backupType = $backupSchema[$tableName][$colName];

                // Loose Comparison for types (ignore size differences like int(10) vs int(11) if possible?)
                // Let's just compare base type.
                $liveBase = preg_replace('/\s*\(.*\)/', '', $colType);
                $backupBase = preg_replace('/\s*\(.*\)/', '', $backupType);

                if ($liveBase !== $backupBase) {
                    // Allow some compatibility? e.g. text vs longtext? 
                    // For now, strict.
                    $errors[] = "Column `{$tableName}.{$colName}` type mismatch: Current is '{$colType}', Backup is '{$backupType}'.";
                }
            }
        }

        return [
            'compatible' => empty($errors),
            'errors' => $errors,
        ];
    }
}
