<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseBackupService
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true, true);
        }
    }

    /**
     * Create a full database backup.
     *
     * @return array{success: bool, filename: string, path: string, size: int, size_human: string, error?: string}
     */
    public function createBackup(string $prefix = 'db_backup_'): array
    {
        $timestamp = date('Y-m-d_His');
        $connection = config('database.default');

        try {
            if ($connection === 'sqlite') {
                $filename = "{$prefix}{$timestamp}.sqlite";
                $targetPath = $this->backupDir.DIRECTORY_SEPARATOR.$filename;
                $dbPath = config('database.connections.sqlite.database');

                if ($dbPath === ':memory:') {
                    // For in-memory database in tests, export schema and data via PDO
                    return $this->exportSqlDump("{$prefix}{$timestamp}.sql", 'sqlite');
                }

                if (File::exists($dbPath)) {
                    File::copy($dbPath, $targetPath);
                    $size = File::size($targetPath);

                    return [
                        'success' => true,
                        'filename' => $filename,
                        'path' => $targetPath,
                        'size' => $size,
                        'size_human' => $this->formatBytes($size),
                    ];
                }
            }

            // MySQL or default SQL dump
            $filename = "{$prefix}{$timestamp}.sql";

            return $this->exportSqlDump($filename, $connection);
        } catch (\Throwable $e) {
            Log::error('Database backup failed: '.$e->getMessage(), ['exception' => $e]);

            return [
                'success' => false,
                'filename' => '',
                'path' => '',
                'size' => 0,
                'size_human' => '0 B',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Pure PHP SQL Dumper for MySQL or SQLite.
     *
     * @return array{success: bool, filename: string, path: string, size: int, size_human: string}
     */
    protected function exportSqlDump(string $filename, string $driver): array
    {
        $targetPath = $this->backupDir.DIRECTORY_SEPARATOR.$filename;
        $handle = fopen($targetPath, 'w');

        if (! $handle) {
            throw new \RuntimeException("Unable to open backup target file: {$targetPath}");
        }

        $now = date('Y-m-d H:i:s');
        fwrite($handle, "-- JMOS Database Backup\n");
        fwrite($handle, "-- Generated: {$now}\n");
        fwrite($handle, "-- Driver: {$driver}\n\n");

        if ($driver === 'mysql') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "START TRANSACTION;\n\n");
        } elseif ($driver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = OFF;\nBEGIN TRANSACTION;\n\n");
        }

        $pdo = DB::connection()->getPdo();

        if ($driver === 'sqlite') {
            $tablesQuery = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = $tablesQuery ? $tablesQuery->fetchAll(\PDO::FETCH_COLUMN) : [];

            foreach ($tables as $table) {
                $createStmt = $pdo->query("SELECT sql FROM sqlite_master WHERE type='table' AND name = ".$pdo->quote($table));
                $createSql = $createStmt ? $createStmt->fetchColumn() : null;

                if ($createSql) {
                    fwrite($handle, "DROP TABLE IF EXISTS \"{$table}\";\n");
                    fwrite($handle, $createSql.";\n\n");

                    $rowsStmt = $pdo->query("SELECT * FROM \"{$table}\"");
                    if ($rowsStmt) {
                        while ($row = $rowsStmt->fetch(\PDO::FETCH_ASSOC)) {
                            $cols = array_map(fn ($col) => "\"{$col}\"", array_keys($row));
                            $vals = array_map(function ($val) use ($pdo) {
                                if ($val === null) {
                                    return 'NULL';
                                }

                                return $pdo->quote((string) $val);
                            }, array_values($row));

                            fwrite($handle, 'INSERT INTO "'.$table.'" ('.implode(', ', $cols).') VALUES ('.implode(', ', $vals).");\n");
                        }
                        fwrite($handle, "\n");
                    }
                }
            }
            fwrite($handle, "COMMIT;\nPRAGMA foreign_keys = ON;\n");
        } else {
            // MySQL Driver
            $tablesQuery = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $tables = $tablesQuery ? $tablesQuery->fetchAll(\PDO::FETCH_COLUMN) : [];

            foreach ($tables as $table) {
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
                $createRow = $createStmt ? $createStmt->fetch(\PDO::FETCH_ASSOC) : null;
                $createSql = $createRow['Create Table'] ?? null;

                if ($createSql) {
                    fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                    fwrite($handle, $createSql.";\n\n");

                    $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
                    if ($rowsStmt) {
                        $chunk = [];
                        $cols = null;

                        while ($row = $rowsStmt->fetch(\PDO::FETCH_ASSOC)) {
                            if ($cols === null) {
                                $cols = array_map(fn ($col) => "`{$col}`", array_keys($row));
                            }

                            $vals = array_map(function ($val) use ($pdo) {
                                if ($val === null) {
                                    return 'NULL';
                                }

                                return $pdo->quote((string) $val);
                            }, array_values($row));

                            $chunk[] = '('.implode(', ', $vals).')';

                            if (count($chunk) >= 200) {
                                fwrite($handle, 'INSERT INTO `'.$table.'` ('.implode(', ', $cols).") VALUES\n".implode(",\n", $chunk).";\n");
                                $chunk = [];
                            }
                        }

                        if (! empty($chunk)) {
                            fwrite($handle, 'INSERT INTO `'.$table.'` ('.implode(', ', $cols).") VALUES\n".implode(",\n", $chunk).";\n");
                        }
                        fwrite($handle, "\n");
                    }
                }
            }

            fwrite($handle, "COMMIT;\nSET FOREIGN_KEY_CHECKS=1;\n");
        }

        fclose($handle);

        $size = File::size($targetPath);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $targetPath,
            'size' => $size,
            'size_human' => $this->formatBytes($size),
        ];
    }

    /**
     * List existing backups in storage/app/backups.
     *
     * @return array<int, array{filename: string, size: int, size_human: string, created_at: string, timestamp: int}>
     */
    public function listBackups(): array
    {
        if (! File::exists($this->backupDir)) {
            return [];
        }

        $files = File::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            if (str_starts_with($filename, '.') || (! str_ends_with($filename, '.sql') && ! str_ends_with($filename, '.sqlite') && ! str_ends_with($filename, '.zip'))) {
                continue;
            }

            $size = $file->getSize();
            $mtime = $file->getMTime();

            $backups[] = [
                'filename' => $filename,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'created_at' => date('Y-m-d H:i:s', $mtime),
                'timestamp' => $mtime,
            ];
        }

        usort($backups, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Get safe absolute path to backup file, preventing directory traversal.
     */
    public function getBackupPath(string $filename): ?string
    {
        $safeName = basename($filename);
        $fullPath = $this->backupDir.DIRECTORY_SEPARATOR.$safeName;

        if (File::exists($fullPath)) {
            return $fullPath;
        }

        return null;
    }

    /**
     * Delete a backup file.
     */
    public function deleteBackup(string $filename): bool
    {
        $path = $this->getBackupPath($filename);
        if ($path && File::exists($path)) {
            return File::delete($path);
        }

        return false;
    }

    /**
     * Helper to format bytes to human readable form.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
