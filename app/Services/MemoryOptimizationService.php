<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MemoryOptimizationService
{
    /**
     * Get current memory usage
     */
    public function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $peakMemoryUsage = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();
        
        return [
            'current_usage' => $memoryUsage,
            'current_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak_usage' => $peakMemoryUsage,
            'peak_usage_mb' => round($peakMemoryUsage / 1024 / 1024, 2),
            'memory_limit' => $memoryLimit,
            'memory_limit_mb' => round($memoryLimit / 1024 / 1024, 2),
            'usage_percentage' => round(($memoryUsage / $memoryLimit) * 100, 2),
            'peak_usage_percentage' => round(($peakMemoryUsage / $memoryLimit) * 100, 2),
        ];
    }

    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }

    /**
     * Check if memory usage is high
     */
    public function isMemoryUsageHigh(float $threshold = 80.0): bool
    {
        $usage = $this->getMemoryUsage();
        return $usage['usage_percentage'] > $threshold;
    }

    /**
     * Optimize memory usage
     */
    public function optimizeMemoryUsage(): void
    {
        // Clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Clear application caches
        $this->clearApplicationCaches();
        
        // Force garbage collection
        gc_collect_cycles();
        
        // Log memory optimization
        Log::info('Memory optimization completed', $this->getMemoryUsage());
    }

    /**
     * Clear application caches
     */
    private function clearApplicationCaches(): void
    {
        try {
            // Clear Laravel caches
            Cache::flush();
            
            // Clear view cache
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to clear application caches: " . $e->getMessage());
        }
    }

    /**
     * Monitor memory usage during execution
     */
    public function monitorMemoryUsage(callable $callback, array $context = []): array
    {
        $startMemory = memory_get_usage(true);
        $startTime = microtime(true);
        
        try {
            $result = $callback();
            
            $endMemory = memory_get_usage(true);
            $endTime = microtime(true);
            
            $memoryUsed = $endMemory - $startMemory;
            $executionTime = $endTime - $startTime;
            
            $metrics = [
                'start_memory' => $startMemory,
                'end_memory' => $endMemory,
                'memory_used' => $memoryUsed,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'execution_time' => $executionTime,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'context' => $context,
            ];
            
            // Log if memory usage is high
            if ($memoryUsed > 10 * 1024 * 1024) { // 10MB
                Log::warning('High memory usage detected', $metrics);
            }
            
            return $metrics;
            
        } catch (\Exception $e) {
            Log::error("Memory monitoring failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get memory usage by function
     */
    public function getMemoryUsageByFunction(): array
    {
        $functions = get_defined_functions();
        $memoryUsage = [];
        
        foreach ($functions['user'] as $function) {
            if (function_exists($function)) {
                $reflection = new \ReflectionFunction($function);
                $filename = $reflection->getFileName();
                
                if ($filename) {
                    $memoryUsage[$function] = [
                        'file' => $filename,
                        'line' => $reflection->getStartLine(),
                    ];
                }
            }
        }
        
        return $memoryUsage;
    }

    /**
     * Optimize database queries for memory
     */
    public function optimizeDatabaseQueries(): void
    {
        // Enable query logging
        \DB::enableQueryLog();
        
        // Get current queries
        $queries = \DB::getQueryLog();
        
        // Analyze queries for memory optimization
        foreach ($queries as $query) {
            $this->analyzeQueryForMemoryOptimization($query);
        }
        
        // Clear query log
        \DB::flushQueryLog();
    }

    /**
     * Analyze query for memory optimization
     */
    private function analyzeQueryForMemoryOptimization(array $query): void
    {
        $sql = $query['query'];
        $time = $query['time'];
        
        // Check for potential memory issues
        if (str_contains($sql, 'SELECT *')) {
            Log::warning('Query using SELECT * may cause memory issues', [
                'sql' => $sql,
                'time' => $time,
            ]);
        }
        
        if (str_contains($sql, 'ORDER BY') && !str_contains($sql, 'LIMIT')) {
            Log::warning('Query with ORDER BY without LIMIT may cause memory issues', [
                'sql' => $sql,
                'time' => $time,
            ]);
        }
        
        if (str_contains($sql, 'JOIN') && str_contains($sql, 'GROUP BY')) {
            Log::warning('Complex JOIN with GROUP BY may cause memory issues', [
                'sql' => $sql,
                'time' => $time,
            ]);
        }
    }

    /**
     * Get memory usage recommendations
     */
    public function getMemoryRecommendations(): array
    {
        $usage = $this->getMemoryUsage();
        $recommendations = [];
        
        if ($usage['usage_percentage'] > 80) {
            $recommendations[] = 'Memory usage is high. Consider optimizing queries or increasing memory limit.';
        }
        
        if ($usage['peak_usage_percentage'] > 90) {
            $recommendations[] = 'Peak memory usage is very high. Consider implementing pagination or chunking.';
        }
        
        if ($usage['current_usage_mb'] > 100) {
            $recommendations[] = 'Current memory usage is high. Consider clearing caches or optimizing data structures.';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Memory usage is within acceptable limits.';
        }
        
        return $recommendations;
    }

    /**
     * Force garbage collection
     */
    public function forceGarbageCollection(): void
    {
        $before = memory_get_usage(true);
        gc_collect_cycles();
        $after = memory_get_usage(true);
        
        $freed = $before - $after;
        
        Log::info('Garbage collection completed', [
            'memory_before' => $before,
            'memory_after' => $after,
            'memory_freed' => $freed,
            'memory_freed_mb' => round($freed / 1024 / 1024, 2),
        ]);
    }

    /**
     * Get memory usage by class
     */
    public function getMemoryUsageByClass(): array
    {
        $classes = get_declared_classes();
        $memoryUsage = [];
        
        foreach ($classes as $class) {
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $filename = $reflection->getFileName();
                
                if ($filename) {
                    $memoryUsage[$class] = [
                        'file' => $filename,
                        'methods' => count($reflection->getMethods()),
                        'properties' => count($reflection->getProperties()),
                    ];
                }
            }
        }
        
        return $memoryUsage;
    }
}

