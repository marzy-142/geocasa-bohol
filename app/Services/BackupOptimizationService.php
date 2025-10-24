<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupOptimizationService
{
    /**
     * Get backup statistics
     */
    public function getBackupStats(): array
    {
        return [
            'backup_count' => $this->getBackupCount(),
            'last_backup' => $this->getLastBackup(),
            'backup_status' => $this->getBackupStatus(),
            'backup_time' => $this->getBackupTime(),
            'backup_size' => $this->getBackupSize(),
            'backup_errors' => $this->getBackupErrors(),
        ];
    }

    /**
     * Get backup count
     */
    private function getBackupCount(): int
    {
        return Cache::get('backup_count', 0);
    }

    /**
     * Get last backup
     */
    private function getLastBackup(): ?array
    {
        return Cache::get('last_backup');
    }

    /**
     * Get backup status
     */
    private function getBackupStatus(): string
    {
        return Cache::get('backup_status', 'unknown');
    }

    /**
     * Get backup time
     */
    private function getBackupTime(): array
    {
        $backupTimes = Cache::get('backup_times', []);
        
        if (empty($backupTimes)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'backup_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($backupTimes) / count($backupTimes), 2),
            'fastest_time' => min($backupTimes),
            'slowest_time' => max($backupTimes),
            'backup_count' => count($backupTimes),
        ];
    }

    /**
     * Get backup size
     */
    private function getBackupSize(): array
    {
        $backupSizes = Cache::get('backup_sizes', []);
        
        if (empty($backupSizes)) {
            return [
                'average_size' => 0,
                'total_size' => 0,
                'backup_count' => 0,
            ];
        }
        
        return [
            'average_size' => round(array_sum($backupSizes) / count($backupSizes), 2),
            'total_size' => array_sum($backupSizes),
            'backup_count' => count($backupSizes),
        ];
    }

    /**
     * Get backup errors
     */
    private function getBackupErrors(): array
    {
        return Cache::get('backup_errors', []);
    }

    /**
     * Create backup
     */
    public function createBackup(): array
    {
        $startTime = microtime(true);
        
        try {
            // Create backup
            $backupResult = $this->executeBackup();
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            // Get backup size
            $backupSize = $this->getCurrentBackupSize();
            
            // Cache backup results
            $this->cacheBackupResults($executionTime, $backupSize, $backupResult);
            
            return [
                'success' => true,
                'execution_time' => $executionTime,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'backup_size' => $backupSize,
                'backup_size_mb' => round($backupSize / 1024 / 1024, 2),
                'backup_result' => $backupResult,
            ];
            
        } catch (\Exception $e) {
            Log::error("Backup creation failed: " . $e->getMessage());
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            // Cache backup error
            $this->cacheBackupError($e, $executionTime);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
            ];
        }
    }

    /**
     * Execute backup
     */
    private function executeBackup(): array
    {
        $backupTasks = [
            'database_backup' => $this->backupDatabase(),
            'file_backup' => $this->backupFiles(),
            'config_backup' => $this->backupConfig(),
        ];
        
        return $backupTasks;
    }

    /**
     * Backup database
     */
    private function backupDatabase(): array
    {
        try {
            $backupPath = storage_path('backups/database_' . now()->format('Y_m_d_H_i_s') . '.sql');
            
            // Ensure backup directory exists
            if (!File::exists(dirname($backupPath))) {
                File::makeDirectory(dirname($backupPath), 0755, true);
            }
            
            // Create database backup
            $result = Artisan::call('backup:run', [
                '--only-db' => true,
                '--destination' => 'local',
                '--destination-path' => 'backups/database_' . now()->format('Y_m_d_H_i_s'),
            ]);
            
            return [
                'success' => $result === 0,
                'backup_path' => $backupPath,
                'output' => Artisan::output(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Backup files
     */
    private function backupFiles(): array
    {
        try {
            $backupPath = storage_path('backups/files_' . now()->format('Y_m_d_H_i_s') . '.zip');
            
            // Ensure backup directory exists
            if (!File::exists(dirname($backupPath))) {
                File::makeDirectory(dirname($backupPath), 0755, true);
            }
            
            // Create file backup
            $result = Artisan::call('backup:run', [
                '--only-files' => true,
                '--destination' => 'local',
                '--destination-path' => 'backups/files_' . now()->format('Y_m_d_H_i_s'),
            ]);
            
            return [
                'success' => $result === 0,
                'backup_path' => $backupPath,
                'output' => Artisan::output(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Backup config
     */
    private function backupConfig(): array
    {
        try {
            $backupPath = storage_path('backups/config_' . now()->format('Y_m_d_H_i_s') . '.zip');
            
            // Ensure backup directory exists
            if (!File::exists(dirname($backupPath))) {
                File::makeDirectory(dirname($backupPath), 0755, true);
            }
            
            // Create config backup
            $result = Artisan::call('backup:run', [
                '--only-config' => true,
                '--destination' => 'local',
                '--destination-path' => 'backups/config_' . now()->format('Y_m_d_H_i_s'),
            ]);
            
            return [
                'success' => $result === 0,
                'backup_path' => $backupPath,
                'output' => Artisan::output(),
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get current backup size
     */
    private function getCurrentBackupSize(): int
    {
        try {
            $backupDirectory = storage_path('backups');
            $totalSize = 0;
            
            if (is_dir($backupDirectory)) {
                $files = glob($backupDirectory . '/*');
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $totalSize += filesize($file);
                    }
                }
            }
            
            return $totalSize;
            
        } catch (\Exception $e) {
            Log::error("Failed to get backup size: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Cache backup results
     */
    private function cacheBackupResults(float $executionTime, int $backupSize, array $backupResult): void
    {
        try {
            // Update backup count
            $backupCount = Cache::get('backup_count', 0);
            Cache::put('backup_count', $backupCount + 1, 86400); // 24 hours
            
            // Update last backup
            $lastBackup = [
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'backup_size' => $backupSize,
                'success' => true,
            ];
            Cache::put('last_backup', $lastBackup, 86400);
            
            // Update backup status
            Cache::put('backup_status', 'success', 86400);
            
            // Cache execution time
            $backupTimes = Cache::get('backup_times', []);
            $backupTimes[] = $executionTime;
            if (count($backupTimes) > 10) {
                $backupTimes = array_slice($backupTimes, -10);
            }
            Cache::put('backup_times', $backupTimes, 86400);
            
            // Cache backup size
            $backupSizes = Cache::get('backup_sizes', []);
            $backupSizes[] = $backupSize;
            if (count($backupSizes) > 10) {
                $backupSizes = array_slice($backupSizes, -10);
            }
            Cache::put('backup_sizes', $backupSizes, 86400);
            
        } catch (\Exception $e) {
            Log::error("Failed to cache backup results: " . $e->getMessage());
        }
    }

    /**
     * Cache backup error
     */
    private function cacheBackupError(\Exception $e, float $executionTime): void
    {
        try {
            // Update backup status
            Cache::put('backup_status', 'error', 86400);
            
            // Cache error
            $backupErrors = Cache::get('backup_errors', []);
            $backupErrors[] = [
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
            ];
            
            // Keep only last 10 errors
            if (count($backupErrors) > 10) {
                $backupErrors = array_slice($backupErrors, -10);
            }
            
            Cache::put('backup_errors', $backupErrors, 86400);
            
        } catch (\Exception $e) {
            Log::error("Failed to cache backup error: " . $e->getMessage());
        }
    }

    /**
     * Get backup recommendations
     */
    public function getBackupRecommendations(): array
    {
        $stats = $this->getBackupStats();
        $recommendations = [];
        
        // Backup time recommendations
        if ($stats['backup_time']['average_time'] > 600) { // 10 minutes
            $recommendations[] = 'Backup time is slow. Consider optimizing backup process';
        }
        
        // Backup size recommendations
        if ($stats['backup_size']['average_size'] > 1024 * 1024 * 1024) { // 1GB
            $recommendations[] = 'Backup size is large. Consider compressing or excluding unnecessary files';
        }
        
        // Backup error recommendations
        if (count($stats['backup_errors']) > 0) {
            $recommendations[] = 'Recent backup errors detected. Check error logs and fix issues';
        }
        
        // Backup frequency recommendations
        if ($stats['backup_count'] < 1) {
            $recommendations[] = 'No backups have been created recently. Consider creating regular backups';
        }
        
        return $recommendations;
    }

    /**
     * Get backup health
     */
    public function getBackupHealth(): array
    {
        $stats = $this->getBackupStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check backup status
        if ($stats['backup_status'] === 'error') {
            $health['status'] = 'unhealthy';
            $health['checks']['backup_status'] = 'Backup failed';
        } else {
            $health['checks']['backup_status'] = 'OK';
        }
        
        // Check backup time
        if ($stats['backup_time']['average_time'] > 600) {
            $health['status'] = 'unhealthy';
            $health['checks']['backup_time'] = 'Slow backup time: ' . $stats['backup_time']['average_time'] . 's';
        } else {
            $health['checks']['backup_time'] = 'OK';
        }
        
        // Check backup size
        if ($stats['backup_size']['average_size'] > 1024 * 1024 * 1024) {
            $health['status'] = 'unhealthy';
            $health['checks']['backup_size'] = 'Large backup size: ' . round($stats['backup_size']['average_size'] / 1024 / 1024 / 1024, 2) . 'GB';
        } else {
            $health['checks']['backup_size'] = 'OK';
        }
        
        // Check backup errors
        if (count($stats['backup_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['backup_errors'] = 'Recent backup errors: ' . count($stats['backup_errors']);
        } else {
            $health['checks']['backup_errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get backup performance metrics
     */
    public function getBackupPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test backup performance
        $this->backupDatabase();
        $this->backupFiles();
        $this->backupConfig();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'backup_time' => $executionTime,
            'backup_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'backup_count' => $this->getBackupCount(),
            'backup_status' => $this->getBackupStatus(),
        ];
    }
}

