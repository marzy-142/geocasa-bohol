<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class LoggingOptimizationService
{
    /**
     * Get logging statistics
     */
    public function getLoggingStats(): array
    {
        return [
            'log_files' => $this->getLogFiles(),
            'log_size' => $this->getLogSize(),
            'log_levels' => $this->getLogLevels(),
            'log_rotation' => $this->getLogRotation(),
            'log_performance' => $this->getLogPerformance(),
        ];
    }

    /**
     * Get log files
     */
    private function getLogFiles(): array
    {
        $logDirectory = storage_path('logs');
        $files = glob($logDirectory . '/*.log');
        $logFiles = [];
        
        foreach ($files as $file) {
            $logFiles[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'size_mb' => round(filesize($file) / 1024 / 1024, 2),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }
        
        return $logFiles;
    }

    /**
     * Get log size
     */
    private function getLogSize(): array
    {
        $logDirectory = storage_path('logs');
        $totalSize = 0;
        $fileCount = 0;
        
        if (is_dir($logDirectory)) {
            $files = glob($logDirectory . '/*.log');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                    $fileCount++;
                }
            }
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount,
            'average_size' => $fileCount > 0 ? round($totalSize / $fileCount) : 0,
        ];
    }

    /**
     * Get log levels
     */
    private function getLogLevels(): array
    {
        $logDirectory = storage_path('logs');
        $levels = [
            'emergency' => 0,
            'alert' => 0,
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'notice' => 0,
            'info' => 0,
            'debug' => 0,
        ];
        
        if (is_dir($logDirectory)) {
            $files = glob($logDirectory . '/*.log');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $content = file_get_contents($file);
                    
                    foreach ($levels as $level => $count) {
                        $levels[$level] += substr_count($content, strtoupper($level));
                    }
                }
            }
        }
        
        return $levels;
    }

    /**
     * Get log rotation
     */
    private function getLogRotation(): array
    {
        $logDirectory = storage_path('logs');
        $rotationFiles = [];
        
        if (is_dir($logDirectory)) {
            $files = glob($logDirectory . '/*.log.*');
            
            foreach ($files as $file) {
                $rotationFiles[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'size_mb' => round(filesize($file) / 1024 / 1024, 2),
                    'modified' => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
        }
        
        return [
            'rotation_files' => $rotationFiles,
            'rotation_count' => count($rotationFiles),
        ];
    }

    /**
     * Get log performance
     */
    private function getLogPerformance(): array
    {
        $startTime = microtime(true);
        
        // Test log writing performance
        Log::info('Performance test log entry');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'write_time' => $executionTime,
            'write_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ];
    }

    /**
     * Optimize logging
     */
    public function optimizeLogging(): array
    {
        $optimizations = [];
        
        // Clear old logs
        $optimizations['clear_old_logs'] = $this->clearOldLogs();
        
        // Compress logs
        $optimizations['compress_logs'] = $this->compressLogs();
        
        // Optimize log levels
        $optimizations['optimize_log_levels'] = $this->optimizeLogLevels();
        
        // Optimize log rotation
        $optimizations['optimize_log_rotation'] = $this->optimizeLogRotation();
        
        return $optimizations;
    }

    /**
     * Clear old logs
     */
    private function clearOldLogs(): array
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
     * Compress logs
     */
    private function compressLogs(): array
    {
        try {
            $logDirectory = storage_path('logs');
            $files = glob($logDirectory . '/*.log');
            $compressedCount = 0;
            
            foreach ($files as $file) {
                if (is_file($file) && filesize($file) > 1024 * 1024) { // 1MB
                    $compressedFile = $file . '.gz';
                    $content = file_get_contents($file);
                    $compressed = gzcompress($content, 9);
                    
                    if ($compressed !== false) {
                        file_put_contents($compressedFile, $compressed);
                        unlink($file);
                        $compressedCount++;
                    }
                }
            }
            
            return [
                'success' => true,
                'compressed_files' => $compressedCount,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize log levels
     */
    private function optimizeLogLevels(): array
    {
        try {
            $logLevels = $this->getLogLevels();
            $totalLogs = array_sum($logLevels);
            
            $recommendations = [];
            
            // Check for too many debug logs
            if ($logLevels['debug'] > $totalLogs * 0.5) {
                $recommendations[] = 'Too many debug logs. Consider reducing debug logging in production';
            }
            
            // Check for too many error logs
            if ($logLevels['error'] > $totalLogs * 0.3) {
                $recommendations[] = 'Too many error logs. Consider investigating and fixing errors';
            }
            
            // Check for too many warning logs
            if ($logLevels['warning'] > $totalLogs * 0.4) {
                $recommendations[] = 'Too many warning logs. Consider investigating and fixing warnings';
            }
            
            return [
                'success' => true,
                'recommendations' => $recommendations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize log rotation
     */
    private function optimizeLogRotation(): array
    {
        try {
            $logDirectory = storage_path('logs');
            $rotationFiles = glob($logDirectory . '/*.log.*');
            $deletedCount = 0;
            $cutoffTime = time() - (7 * 24 * 60 * 60); // 7 days
            
            foreach ($rotationFiles as $file) {
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
     * Get logging recommendations
     */
    public function getLoggingRecommendations(): array
    {
        $stats = $this->getLoggingStats();
        $recommendations = [];
        
        // Log size recommendations
        if ($stats['log_size']['total_size_mb'] > 100) {
            $recommendations[] = 'Log files are large. Consider clearing old logs or implementing log rotation';
        }
        
        // Log level recommendations
        $logLevels = $stats['log_levels'];
        $totalLogs = array_sum($logLevels);
        
        if ($totalLogs > 0) {
            if ($logLevels['debug'] > $totalLogs * 0.5) {
                $recommendations[] = 'Too many debug logs. Consider reducing debug logging in production';
            }
            
            if ($logLevels['error'] > $totalLogs * 0.3) {
                $recommendations[] = 'Too many error logs. Consider investigating and fixing errors';
            }
            
            if ($logLevels['warning'] > $totalLogs * 0.4) {
                $recommendations[] = 'Too many warning logs. Consider investigating and fixing warnings';
            }
        }
        
        // Log rotation recommendations
        if ($stats['log_rotation']['rotation_count'] > 10) {
            $recommendations[] = 'Many log rotation files. Consider cleaning up old rotation files';
        }
        
        // Log performance recommendations
        if ($stats['log_performance']['write_time_ms'] > 100) {
            $recommendations[] = 'Log writing is slow. Consider optimizing logging configuration';
        }
        
        return $recommendations;
    }

    /**
     * Get logging health
     */
    public function getLoggingHealth(): array
    {
        $stats = $this->getLoggingStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check log size
        if ($stats['log_size']['total_size_mb'] > 100) {
            $health['status'] = 'unhealthy';
            $health['checks']['log_size'] = 'Large log files: ' . $stats['log_size']['total_size_mb'] . 'MB';
        } else {
            $health['checks']['log_size'] = 'OK';
        }
        
        // Check log levels
        $logLevels = $stats['log_levels'];
        $totalLogs = array_sum($logLevels);
        
        if ($totalLogs > 0) {
            if ($logLevels['error'] > $totalLogs * 0.3) {
                $health['status'] = 'unhealthy';
                $health['checks']['log_levels'] = 'Too many error logs: ' . $logLevels['error'];
            } else {
                $health['checks']['log_levels'] = 'OK';
            }
        } else {
            $health['checks']['log_levels'] = 'OK';
        }
        
        // Check log performance
        if ($stats['log_performance']['write_time_ms'] > 100) {
            $health['status'] = 'unhealthy';
            $health['checks']['log_performance'] = 'Slow log writing: ' . $stats['log_performance']['write_time_ms'] . 'ms';
        } else {
            $health['checks']['log_performance'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get logging performance metrics
     */
    public function getLoggingPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test logging performance
        Log::info('Performance test log entry');
        Log::warning('Performance test warning');
        Log::error('Performance test error');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'logging_time' => $executionTime,
            'logging_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'log_files' => count($this->getLogFiles()),
            'log_size_mb' => $this->getLogSize()['total_size_mb'],
        ];
    }
}

