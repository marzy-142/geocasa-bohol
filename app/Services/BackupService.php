<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Exception;

class BackupService
{
    protected $backupDisk;
    protected $maxBackups;
    
    public function __construct()
    {
        $this->backupDisk = config('backup.disk', 'local');
        $this->maxBackups = config('backup.max_backups', 7);
    }

    /**
     * Create a full backup (database + files)
     */
    public function createFullBackup(): array
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupPath = "backups/{$timestamp}";
        
        try {
            $results = [
                'timestamp' => $timestamp,
                'path' => $backupPath,
                'database' => $this->backupDatabase($backupPath),
                'files' => $this->backupFiles($backupPath),
            ];
            
            // Clean up old backups
            $this->cleanupOldBackups();
            
            Log::info('Full backup completed successfully', $results);
            
            return $results;
        } catch (Exception $e) {
            Log::error('Backup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase(string $backupPath): array
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        $filename = "database_{$dbConfig['database']}.sql";
        $fullPath = "{$backupPath}/{$filename}";
        
        try {
            $startTime = microtime(true);
            
            // Get all table names
            $tables = DB::select('SHOW TABLES');
            $databaseName = $dbConfig['database'];
            $tableKey = "Tables_in_{$databaseName}";
            
            $sqlDump = "-- Database backup created at " . now()->toDateTimeString() . "\n";
            $sqlDump .= "-- Database: {$databaseName}\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                // Get table structure
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $sqlDump .= "-- Table structure for `{$tableName}`\n";
                $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlDump .= $createTable->{'Create Table'} . ";\n\n";
                
                // Get table data
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sqlDump .= "-- Data for table `{$tableName}`\n";
                    $sqlDump .= "INSERT INTO `{$tableName}` VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowData = [];
                        foreach ($row as $value) {
                            if (is_null($value)) {
                                $rowData[] = 'NULL';
                            } else {
                                $rowData[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(',', $rowData) . ')';
                    }
                    
                    $sqlDump .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            // Store backup file
            Storage::disk($this->backupDisk)->put($fullPath, $sqlDump);
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            return [
                'status' => 'success',
                'filename' => $filename,
                'path' => $fullPath,
                'size' => Storage::disk($this->backupDisk)->size($fullPath),
                'duration_ms' => $duration,
            ];
        } catch (Exception $e) {
            Log::error('Database backup failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Backup important files
     */
    public function backupFiles(string $backupPath): array
    {
        $filesToBackup = [
            'storage/app' => 'storage_app',
            'public/uploads' => 'public_uploads',
            '.env' => 'env_file',
        ];
        
        $results = [];
        
        foreach ($filesToBackup as $source => $destination) {
            try {
                $startTime = microtime(true);
                
                $sourcePath = base_path($source);
                $backupFilePath = "{$backupPath}/files/{$destination}";
                
                if (File::exists($sourcePath)) {
                    if (File::isDirectory($sourcePath)) {
                        $this->backupDirectory($sourcePath, $backupFilePath);
                    } else {
                        $content = File::get($sourcePath);
                        Storage::disk($this->backupDisk)->put($backupFilePath, $content);
                    }
                    
                    $endTime = microtime(true);
                    $duration = round(($endTime - $startTime) * 1000, 2);
                    
                    $results[$source] = [
                        'status' => 'success',
                        'destination' => $backupFilePath,
                        'duration_ms' => $duration,
                    ];
                } else {
                    $results[$source] = [
                        'status' => 'skipped',
                        'reason' => 'Source does not exist',
                    ];
                }
            } catch (Exception $e) {
                $results[$source] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }

    /**
     * Backup a directory recursively
     */
    protected function backupDirectory(string $sourcePath, string $backupPath): void
    {
        $files = File::allFiles($sourcePath);
        
        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();
            $destinationPath = "{$backupPath}/{$relativePath}";
            
            $content = File::get($file->getRealPath());
            Storage::disk($this->backupDisk)->put($destinationPath, $content);
        }
    }

    /**
     * Clean up old backups
     */
    public function cleanupOldBackups(): int
    {
        try {
            $backupDirs = collect(Storage::disk($this->backupDisk)->directories('backups'))
                ->map(function ($dir) {
                    return [
                        'path' => $dir,
                        'timestamp' => basename($dir),
                    ];
                })
                ->sortByDesc('timestamp')
                ->values();
            
            $deleted = 0;
            
            if ($backupDirs->count() > $this->maxBackups) {
                $toDelete = $backupDirs->slice($this->maxBackups);
                
                foreach ($toDelete as $backup) {
                    Storage::disk($this->backupDisk)->deleteDirectory($backup['path']);
                    $deleted++;
                }
            }
            
            Log::info('Backup cleanup completed', [
                'total_backups' => $backupDirs->count(),
                'deleted' => $deleted,
                'remaining' => $backupDirs->count() - $deleted,
            ]);
            
            return $deleted;
        } catch (Exception $e) {
            Log::error('Backup cleanup failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * List available backups
     */
    public function listBackups(): array
    {
        try {
            $backups = collect(Storage::disk($this->backupDisk)->directories('backups'))
                ->map(function ($dir) {
                    $timestamp = basename($dir);
                    $files = Storage::disk($this->backupDisk)->allFiles($dir);
                    
                    return [
                        'timestamp' => $timestamp,
                        'path' => $dir,
                        'files' => count($files),
                        'size' => collect($files)->sum(function ($file) {
                            return Storage::disk($this->backupDisk)->size($file);
                        }),
                        'created_at' => Carbon::createFromFormat('Y-m-d_H-i-s', $timestamp),
                    ];
                })
                ->sortByDesc('timestamp')
                ->values()
                ->toArray();
            
            return $backups;
        } catch (Exception $e) {
            Log::error('Failed to list backups', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get backup status and statistics
     */
    public function getBackupStatus(): array
    {
        $backups = $this->listBackups();
        $latestBackup = collect($backups)->first();
        
        return [
            'total_backups' => count($backups),
            'latest_backup' => $latestBackup,
            'total_size' => collect($backups)->sum('size'),
            'disk_usage' => $this->getDiskUsage(),
            'next_cleanup' => count($backups) >= $this->maxBackups,
        ];
    }

    /**
     * Get disk usage information
     */
    protected function getDiskUsage(): array
    {
        try {
            $disk = Storage::disk($this->backupDisk);
            $backupFiles = $disk->allFiles('backups');
            
            return [
                'backup_files' => count($backupFiles),
                'total_backup_size' => collect($backupFiles)->sum(function ($file) use ($disk) {
                    return $disk->size($file);
                }),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}