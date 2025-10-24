<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class MaintenanceOptimizationService
{
    /**
     * Get maintenance statistics
     */
    public function getMaintenanceStats(): array
    {
        return [
            'maintenance_count' => $this->getMaintenanceCount(),
            'last_maintenance' => $this->getLastMaintenance(),
            'maintenance_status' => $this->getMaintenanceStatus(),
            'maintenance_time' => $this->getMaintenanceTime(),
            'maintenance_memory' => $this->getMaintenanceMemory(),
            'maintenance_errors' => $this->getMaintenanceErrors(),
        ];
    }

    /**
     * Get maintenance count
     */
    private function getMaintenanceCount(): int
    {
        return Cache::get('maintenance_count', 0);
    }

    /**
     * Get last maintenance
     */
    private function getLastMaintenance(): ?array
    {
        return Cache::get('last_maintenance');
    }

    /**
     * Get maintenance status
     */
    private function getMaintenanceStatus(): string
    {
        return Cache::get('maintenance_status', 'unknown');
    }

    /**
     * Get maintenance time
     */
    private function getMaintenanceTime(): array
    {
        $maintenanceTimes = Cache::get('maintenance_times', []);
        
        if (empty($maintenanceTimes)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'maintenance_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($maintenanceTimes) / count($maintenanceTimes), 2),
            'fastest_time' => min($maintenanceTimes),
            'slowest_time' => max($maintenanceTimes),
            'maintenance_count' => count($maintenanceTimes),
        ];
    }

    /**
     * Get maintenance memory
     */
    private function getMaintenanceMemory(): array
    {
        $maintenanceMemory = Cache::get('maintenance_memory', []);
        
        if (empty($maintenanceMemory)) {
            return [
                'average_memory' => 0,
                'peak_memory' => 0,
                'maintenance_count' => 0,
            ];
        }
        
        return [
            'average_memory' => round(array_sum($maintenanceMemory) / count($maintenanceMemory), 2),
            'peak_memory' => max($maintenanceMemory),
            'maintenance_count' => count($maintenanceMemory),
        ];
    }

    /**
     * Get maintenance errors
     */
    private function getMaintenanceErrors(): array
    {
        return Cache::get('maintenance_errors', []);
    }

    /**
     * Run maintenance tasks
     */
    public function runMaintenanceTasks(): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            // Run maintenance tasks
            $maintenanceResult = $this->executeMaintenanceTasks();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = $endTime - $startTime;
            $memoryUsed = $endMemory - $startMemory;
            
            // Cache maintenance results
            $this->cacheMaintenanceResults($executionTime, $memoryUsed, $maintenanceResult);
            
            return [
                'success' => true,
                'execution_time' => $executionTime,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'memory_used' => $memoryUsed,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'maintenance_result' => $maintenanceResult,
            ];
            
        } catch (\Exception $e) {
            Log::error("Maintenance tasks failed: " . $e->getMessage());
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = $endTime - $startTime;
            $memoryUsed = $endMemory - $startMemory;
            
            // Cache maintenance error
            $this->cacheMaintenanceError($e, $executionTime, $memoryUsed);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
            ];
        }
    }

    /**
     * Execute maintenance tasks
     */
    private function executeMaintenanceTasks(): array
    {
        $tasks = [
            'cache_clear' => $this->clearCaches(),
            'log_cleanup' => $this->cleanupLogs(),
            'temp_cleanup' => $this->cleanupTempFiles(),
            'database_optimize' => $this->optimizeDatabase(),
            'file_cleanup' => $this->cleanupFiles(),
            'session_cleanup' => $this->cleanupSessions(),
        ];
        
        return $tasks;
    }

    /**
     * Clear caches
     */
    private function clearCaches(): array
    {
        try {
            $commands = [
                'cache:clear',
                'config:clear',
                'route:clear',
                'view:clear',
            ];
            
            $results = [];
            foreach ($commands as $command) {
                $result = Artisan::call($command);
                $results[$command] = [
                    'success' => $result === 0,
                    'output' => Artisan::output(),
                ];
            }
            
            return [
                'success' => true,
                'results' => $results,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cleanup logs
     */
    private function cleanupLogs(): array
    {
        try {
            $logDirectory = storage_path('logs');
            $files = glob($logDirectory . '/*.log');
            $cutoffTime = time() - (30 * 24 * 60 * 60); // 30 days
            $deletedCount = 0;
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                    $deletedCount++;
                }
            }
            
            return [
                'success' => true,
                'deleted_files' => $deletedCount,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cleanup temp files
     */
    private function cleanupTempFiles(): array
    {
        try {
            $tempDirectory = storage_path('app/temp');
            $deletedCount = 0;
            
            if (is_dir($tempDirectory)) {
                $files = glob($tempDirectory . '/*');
                $cutoffTime = time() - (7 * 24 * 60 * 60); // 7 days
                
                foreach ($files as $file) {
                    if (is_file($file) && filemtime($file) < $cutoffTime) {
                        unlink($file);
                        $deletedCount++;
                    }
                }
            }
            
            return [
                'success' => true,
                'deleted_files' => $deletedCount,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize database
     */
    private function optimizeDatabase(): array
    {
        try {
            // Get database tables
            $tables = DB::select('SHOW TABLES');
            $optimizedTables = 0;
            
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                DB::statement("OPTIMIZE TABLE {$tableName}");
                $optimizedTables++;
            }
            
            return [
                'success' => true,
                'optimized_tables' => $optimizedTables,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cleanup files
     */
    private function cleanupFiles(): array
    {
        try {
            $directories = [
                storage_path('app/public/temp'),
                storage_path('app/public/uploads/temp'),
                storage_path('framework/cache'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
            ];
            
            $deletedCount = 0;
            $cutoffTime = time() - (7 * 24 * 60 * 60); // 7 days
            
            foreach ($directories as $directory) {
                if (is_dir($directory)) {
                    $files = glob($directory . '/*');
                    
                    foreach ($files as $file) {
                        if (is_file($file) && filemtime($file) < $cutoffTime) {
                            unlink($file);
                            $deletedCount++;
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'deleted_files' => $deletedCount,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cleanup sessions
     */
    private function cleanupSessions(): array
    {
        try {
            $result = Artisan::call('session:gc');
            
            return [
                'success' => $result === 0,
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
     * Cache maintenance results
     */
    private function cacheMaintenanceResults(float $executionTime, int $memoryUsed, array $maintenanceResult): void
    {
        try {
            // Update maintenance count
            $maintenanceCount = Cache::get('maintenance_count', 0);
            Cache::put('maintenance_count', $maintenanceCount + 1, 86400); // 24 hours
            
            // Update last maintenance
            $lastMaintenance = [
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
                'success' => true,
            ];
            Cache::put('last_maintenance', $lastMaintenance, 86400);
            
            // Update maintenance status
            Cache::put('maintenance_status', 'success', 86400);
            
            // Cache execution time
            $maintenanceTimes = Cache::get('maintenance_times', []);
            $maintenanceTimes[] = $executionTime;
            if (count($maintenanceTimes) > 10) {
                $maintenanceTimes = array_slice($maintenanceTimes, -10);
            }
            Cache::put('maintenance_times', $maintenanceTimes, 86400);
            
            // Cache memory usage
            $maintenanceMemory = Cache::get('maintenance_memory', []);
            $maintenanceMemory[] = $memoryUsed;
            if (count($maintenanceMemory) > 10) {
                $maintenanceMemory = array_slice($maintenanceMemory, -10);
            }
            Cache::put('maintenance_memory', $maintenanceMemory, 86400);
            
        } catch (\Exception $e) {
            Log::error("Failed to cache maintenance results: " . $e->getMessage());
        }
    }

    /**
     * Cache maintenance error
     */
    private function cacheMaintenanceError(\Exception $e, float $executionTime, int $memoryUsed): void
    {
        try {
            // Update maintenance status
            Cache::put('maintenance_status', 'error', 86400);
            
            // Cache error
            $maintenanceErrors = Cache::get('maintenance_errors', []);
            $maintenanceErrors[] = [
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
            ];
            
            // Keep only last 10 errors
            if (count($maintenanceErrors) > 10) {
                $maintenanceErrors = array_slice($maintenanceErrors, -10);
            }
            
            Cache::put('maintenance_errors', $maintenanceErrors, 86400);
            
        } catch (\Exception $e) {
            Log::error("Failed to cache maintenance error: " . $e->getMessage());
        }
    }

    /**
     * Get maintenance recommendations
     */
    public function getMaintenanceRecommendations(): array
    {
        $stats = $this->getMaintenanceStats();
        $recommendations = [];
        
        // Maintenance time recommendations
        if ($stats['maintenance_time']['average_time'] > 300) { // 5 minutes
            $recommendations[] = 'Maintenance time is slow. Consider optimizing maintenance tasks';
        }
        
        // Maintenance memory recommendations
        if ($stats['maintenance_memory']['peak_memory'] > 200 * 1024 * 1024) { // 200MB
            $recommendations[] = 'Maintenance memory usage is high. Consider optimizing memory usage';
        }
        
        // Maintenance error recommendations
        if (count($stats['maintenance_errors']) > 0) {
            $recommendations[] = 'Recent maintenance errors detected. Check error logs and fix issues';
        }
        
        // Maintenance frequency recommendations
        if ($stats['maintenance_count'] < 1) {
            $recommendations[] = 'No maintenance tasks have been run recently. Consider running maintenance tasks';
        }
        
        return $recommendations;
    }

    /**
     * Get maintenance health
     */
    public function getMaintenanceHealth(): array
    {
        $stats = $this->getMaintenanceStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check maintenance status
        if ($stats['maintenance_status'] === 'error') {
            $health['status'] = 'unhealthy';
            $health['checks']['maintenance_status'] = 'Maintenance failed';
        } else {
            $health['checks']['maintenance_status'] = 'OK';
        }
        
        // Check maintenance time
        if ($stats['maintenance_time']['average_time'] > 300) {
            $health['status'] = 'unhealthy';
            $health['checks']['maintenance_time'] = 'Slow maintenance time: ' . $stats['maintenance_time']['average_time'] . 's';
        } else {
            $health['checks']['maintenance_time'] = 'OK';
        }
        
        // Check maintenance memory
        if ($stats['maintenance_memory']['peak_memory'] > 200 * 1024 * 1024) {
            $health['status'] = 'unhealthy';
            $health['checks']['maintenance_memory'] = 'High memory usage: ' . round($stats['maintenance_memory']['peak_memory'] / 1024 / 1024, 2) . 'MB';
        } else {
            $health['checks']['maintenance_memory'] = 'OK';
        }
        
        // Check maintenance errors
        if (count($stats['maintenance_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['maintenance_errors'] = 'Recent maintenance errors: ' . count($stats['maintenance_errors']);
        } else {
            $health['checks']['maintenance_errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get maintenance performance metrics
     */
    public function getMaintenancePerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test maintenance performance
        $this->clearCaches();
        $this->cleanupLogs();
        $this->cleanupTempFiles();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'maintenance_time' => $executionTime,
            'maintenance_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'maintenance_count' => $this->getMaintenanceCount(),
            'maintenance_status' => $this->getMaintenanceStatus(),
        ];
    }
}

