<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BackupService
{
    public const BACKUP_DIR = 'backups';

    /**
     * Get absolute path to the backups directory.
     */
    public static function getBackupDirectory(): string
    {
        $dir = storage_path('app/' . self::BACKUP_DIR);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Create a pure PHP SQL Database dump.
     */
    public static function createDatabaseDump(?string $destSqlPath = null): string
    {
        $destPath = $destSqlPath ?: tempnam(sys_get_temp_dir(), 'ff_db_dump_') . '.sql';
        $handle = fopen($destPath, 'w+b');
        if (!$handle) {
            throw new Exception("Unable to create database dump file at: {$destPath}");
        }

        $dbName = config('database.connections.mysql.database', env('DB_DATABASE', 'filesytem_laravel'));

        fwrite($handle, "-- --------------------------------------------------------\n");
        fwrite($handle, "-- FileFusion Database Backup Dump\n");
        fwrite($handle, "-- Database: {$dbName}\n");
        fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s T') . "\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET time_zone = '+00:00';\n\n");

        $tablesResult = DB::select('SHOW TABLES');
        $tables = [];
        foreach ($tablesResult as $tblObj) {
            $prop = 'Tables_in_' . $dbName;
            $table = $tblObj->$prop ?? array_values((array)$tblObj)[0] ?? null;
            if ($table) {
                $tables[] = $table;
            }
        }

        foreach ($tables as $table) {
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Table structure for table `{$table}`\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
            $createStatement = $createResult[0]->{'Create Table'} ?? null;
            if ($createStatement) {
                fwrite($handle, $createStatement . ";\n\n");
            }

            // Dump data in chunks of 500
            $count = DB::table($table)->count();
            if ($count > 0) {
                fwrite($handle, "-- Dumping data for table `{$table}` ({$count} rows)\n");
                
                $rows = DB::table($table)->get();
                $chunkSize = 100;
                $rowChunks = $rows->chunk($chunkSize);

                foreach ($rowChunks as $chunk) {
                    $insertValues = [];
                    $columns = [];

                    foreach ($chunk as $row) {
                        $rowArray = (array) $row;
                        if (empty($columns)) {
                            $columns = array_map(fn($col) => "`{$col}`", array_keys($rowArray));
                        }

                        $escapedValues = [];
                        foreach ($rowArray as $val) {
                            if ($val === null) {
                                $escapedValues[] = 'NULL';
                            } elseif (is_numeric($val) && !is_string($val)) {
                                $escapedValues[] = $val;
                            } else {
                                $escaped = addslashes((string)$val);
                                $escaped = str_replace(["\r", "\n"], ["\\r", "\\n"], $escaped);
                                $escapedValues[] = "'{$escaped}'";
                            }
                        }
                        $insertValues[] = '(' . implode(', ', $escapedValues) . ')';
                    }

                    if (!empty($insertValues)) {
                        $colStr = implode(', ', $columns);
                        $valStr = implode(",\n", $insertValues);
                        fwrite($handle, "INSERT INTO `{$table}` ({$colStr}) VALUES\n{$valStr};\n");
                    }
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($handle, "-- End of Database Backup Dump\n");
        fclose($handle);

        return $destPath;
    }

    /**
     * Create a Full System Backup (Database + Uploaded Physical Files + Config Manifest).
     */
    public static function createFullBackup(): array
    {
        $backupDir = self::getBackupDirectory();
        $timestamp = date('Y_m_d_His');
        $filename = "backup_full_{$timestamp}.zip";
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip archive at: {$zipPath}");
        }

        // 1. Dump Database
        $sqlDumpPath = self::createDatabaseDump();
        $zip->addFile($sqlDumpPath, 'database.sql');

        // 2. Add Storage Files (excluding backups directory to avoid recursion)
        $storageAppPath = storage_path('app');
        $totalFilesCount = 0;
        $totalUncompressedBytes = filesize($sqlDumpPath);

        if (is_dir($storageAppPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storageAppPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $filePath = $file->getRealPath();
                $relPath = substr($filePath, strlen($storageAppPath) + 1);

                // Skip the backups directory itself
                if (str_starts_with(str_replace('\\', '/', $relPath), self::BACKUP_DIR)) {
                    continue;
                }

                if ($file->isDir()) {
                    $zip->addEmptyDir('storage/' . str_replace('\\', '/', $relPath));
                } elseif ($file->isFile()) {
                    $zip->addFile($filePath, 'storage/' . str_replace('\\', '/', $relPath));
                    $totalFilesCount++;
                    $totalUncompressedBytes += filesize($filePath);
                }
            }
        }

        // 3. Add Manifest
        $manifest = [
            'type' => 'full',
            'created_at' => date('Y-m-d H:i:s'),
            'app_name' => config('app.name', 'FileFusion'),
            'app_version' => '2.0.0',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.connections.mysql.database', 'filesytem_laravel'),
            'files_count' => $totalFilesCount,
            'uncompressed_bytes' => $totalUncompressedBytes,
            'encryption_key_version' => env('FILE_KEY_VERSION', 'v4'),
        ];
        $zip->addFromString('backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));

        $zip->close();
        @unlink($sqlDumpPath);

        $compressedSize = file_exists($zipPath) ? filesize($zipPath) : 0;
        $checksum = file_exists($zipPath) ? hash_file('sha256', $zipPath) : '';

        return [
            'success' => true,
            'type' => 'full',
            'filename' => $filename,
            'path' => $zipPath,
            'size' => $compressedSize,
            'size_formatted' => self::formatBytes($compressedSize),
            'files_count' => $totalFilesCount,
            'checksum' => $checksum,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create a Database-Only Backup Archive.
     */
    public static function createDbBackup(): array
    {
        $backupDir = self::getBackupDirectory();
        $timestamp = date('Y_m_d_His');
        $filename = "backup_db_{$timestamp}.zip";
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip archive at: {$zipPath}");
        }

        $sqlDumpPath = self::createDatabaseDump();
        $zip->addFile($sqlDumpPath, 'database.sql');

        $manifest = [
            'type' => 'database',
            'created_at' => date('Y-m-d H:i:s'),
            'database' => config('database.connections.mysql.database', 'filesytem_laravel'),
            'uncompressed_bytes' => filesize($sqlDumpPath),
        ];
        $zip->addFromString('backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();
        @unlink($sqlDumpPath);

        $compressedSize = file_exists($zipPath) ? filesize($zipPath) : 0;
        $checksum = file_exists($zipPath) ? hash_file('sha256', $zipPath) : '';

        return [
            'success' => true,
            'type' => 'database',
            'filename' => $filename,
            'path' => $zipPath,
            'size' => $compressedSize,
            'size_formatted' => self::formatBytes($compressedSize),
            'checksum' => $checksum,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create a Files-Only Backup Archive.
     */
    public static function createFilesBackup(): array
    {
        $backupDir = self::getBackupDirectory();
        $timestamp = date('Y_m_d_His');
        $filename = "backup_files_{$timestamp}.zip";
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Cannot create zip archive at: {$zipPath}");
        }

        $storageAppPath = storage_path('app');
        $totalFilesCount = 0;
        $totalUncompressedBytes = 0;

        if (is_dir($storageAppPath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storageAppPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $filePath = $file->getRealPath();
                $relPath = substr($filePath, strlen($storageAppPath) + 1);

                if (str_starts_with(str_replace('\\', '/', $relPath), self::BACKUP_DIR)) {
                    continue;
                }

                if ($file->isDir()) {
                    $zip->addEmptyDir('storage/' . str_replace('\\', '/', $relPath));
                } elseif ($file->isFile()) {
                    $zip->addFile($filePath, 'storage/' . str_replace('\\', '/', $relPath));
                    $totalFilesCount++;
                    $totalUncompressedBytes += filesize($filePath);
                }
            }
        }

        $manifest = [
            'type' => 'files',
            'created_at' => date('Y-m-d H:i:s'),
            'files_count' => $totalFilesCount,
            'uncompressed_bytes' => $totalUncompressedBytes,
        ];
        $zip->addFromString('backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        $compressedSize = file_exists($zipPath) ? filesize($zipPath) : 0;
        $checksum = file_exists($zipPath) ? hash_file('sha256', $zipPath) : '';

        return [
            'success' => true,
            'type' => 'files',
            'filename' => $filename,
            'path' => $zipPath,
            'size' => $compressedSize,
            'size_formatted' => self::formatBytes($compressedSize),
            'files_count' => $totalFilesCount,
            'checksum' => $checksum,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * List all available backup archives.
     */
    public static function listBackups(): array
    {
        $backupDir = self::getBackupDirectory();
        $files = glob($backupDir . DIRECTORY_SEPARATOR . '*.zip');
        $backups = [];

        if ($files) {
            foreach ($files as $filePath) {
                $filename = basename($filePath);
                $size = filesize($filePath);
                $modified = filemtime($filePath);

                $type = 'custom';
                if (str_starts_with($filename, 'backup_full_')) $type = 'full';
                elseif (str_starts_with($filename, 'backup_db_')) $type = 'database';
                elseif (str_starts_with($filename, 'backup_files_')) $type = 'files';

                $backups[] = [
                    'filename' => $filename,
                    'type' => $type,
                    'size' => $size,
                    'size_formatted' => self::formatBytes($size),
                    'timestamp' => $modified,
                    'created_at' => date('Y-m-d H:i:s', $modified),
                    'time_ago' => self::timeAgo($modified),
                    'checksum' => hash_file('sha256', $filePath),
                ];
            }

            // Sort newest first
            usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        }

        return $backups;
    }

    /**
     * Delete a backup file.
     */
    public static function deleteBackup(string $filename): bool
    {
        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        $path = self::getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($path) && str_ends_with($filename, '.zip')) {
            return @unlink($path);
        }
        return false;
    }

    /**
     * Get absolute path for downloading.
     */
    public static function getBackupPath(string $filename): ?string
    {
        $filename = basename($filename);
        $path = self::getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($path) && str_ends_with($filename, '.zip')) {
            return $path;
        }
        return null;
    }

    /**
     * Calculate approximate raw database size in bytes.
     */
    public static function getDatabaseSizeBytes(): int
    {
        try {
            $dbName = config('database.connections.mysql.database', env('DB_DATABASE', 'filesytem_laravel'));
            $res = DB::select("
                SELECT SUM(data_length + index_length) AS db_size
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            return (int) ($res[0]->db_size ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private static function timeAgo(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' mins ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hrs ago';
        return floor($diff / 86400) . ' days ago';
    }
}
