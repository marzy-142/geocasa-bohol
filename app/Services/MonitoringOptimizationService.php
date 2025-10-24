<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class MonitoringOptimizationService
{
    /**
     * Get system monitoring statistics
     */
    public function getSystemMonitoringStats(): array
    {
        return [
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCpuUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'database_connections' => $this->getDatabaseConnections(),
            'queue_jobs' => $this->getQueueJobs(),
            'cache_hit_rate' => $this->getCacheHitRate(),
            'response_times' => $this->getResponseTimes(),
        ];
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $peakMemoryUsage = memory_get_peak_usage(true);
        $memoryLimit = $this->getMemoryLimit();
        
        return [
            'current' => $memoryUsage,
            'current_mb' => round($memoryUsage / 1024 / 1024, 2),
            'peak' => $peakMemoryUsage,
            'peak_mb' => round($peakMemoryUsage / 1024 / 1024, 2),
            'limit' => $memoryLimit,
            'limit_mb' => round($memoryLimit / 1024 / 1024, 2),
            'usage_percentage' => round(($memoryUsage / $memoryLimit) * 100, 2),
        ];
    }

    /**
     * Get CPU usage
     */
    private function getCpuUsage(): array
    {
        $load = sys_getloadavg();
        
        return [
            'load_1min' => $load[0] ?? 0,
            'load_5min' => $load[1] ?? 0,
            'load_15min' => $load[2] ?? 0,
        ];
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $totalSpace = disk_total_space('/');
        $freeSpace = disk_free_space('/');
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'total' => $totalSpace,
            'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
            'used' => $usedSpace,
            'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
            'free' => $freeSpace,
            'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
            'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
        ];
    }

    /**
     * Get database connections
     */
    private function getDatabaseConnections(): array
    {
        try {
            $connections = DB::getConnections();
            $connectionCount = count($connections);
            
            return [
                'active_connections' => $connectionCount,
                'max_connections' => ini_get('mysqli.max_links') ?: 'unknown',
                'connection_status' => 'connected',
            ];
        } catch (\Exception $e) {
            return [
                'active_connections' => 0,
                'max_connections' => 'unknown',
                'connection_status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue jobs
     */
    private function getQueueJobs(): array
    {
        try {
            $pendingJobs = Queue::size();
            $failedJobs = $this->getFailedJobsCount();
            
            return [
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'queue_status' => 'active',
            ];
        } catch (\Exception $e) {
            return [
                'pending_jobs' => 0,
                'failed_jobs' => 0,
                'queue_status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get failed jobs count
     */
    private function getFailedJobsCount(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache hit rate
     */
    private function getCacheHitRate(): array
    {
        try {
            $cacheStats = Cache::getRedis()->info('stats');
            $hits = $cacheStats['keyspace_hits'] ?? 0;
            $misses = $cacheStats['keyspace_misses'] ?? 0;
            $total = $hits + $misses;
            
            return [
                'hits' => $hits,
                'misses' => $misses,
                'total' => $total,
                'hit_rate' => $total > 0 ? round(($hits / $total) * 100, 2) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'hits' => 0,
                'misses' => 0,
                'total' => 0,
                'hit_rate' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get response times
     */
    private function getResponseTimes(): array
    {
        $responseTimes = Cache::get('response_times', []);
        
        if (empty($responseTimes)) {
            return [
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'count' => 0,
            ];
        }
        
        return [
            'average' => round(array_sum($responseTimes) / count($responseTimes), 2),
            'min' => min($responseTimes),
            'max' => max($responseTimes),
            'count' => count($responseTimes),
        ];
    }

    /**
     * Record response time
     */
    public function recordResponseTime(float $responseTime): void
    {
        $responseTimes = Cache::get('response_times', []);
        $responseTimes[] = $responseTime;
        
        // Keep only last 100 response times
        if (count($responseTimes) > 100) {
            $responseTimes = array_slice($responseTimes, -100);
        }
        
        Cache::put('response_times', $responseTimes, 3600); // 1 hour
    }

    /**
     * Get memory limit
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
     * Monitor application health
     */
    public function monitorApplicationHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check memory usage
        $memoryUsage = $this->getMemoryUsage();
        if ($memoryUsage['usage_percentage'] > 90) {
            $health['status'] = 'unhealthy';
            $health['checks']['memory'] = 'High memory usage: ' . $memoryUsage['usage_percentage'] . '%';
        } else {
            $health['checks']['memory'] = 'OK';
        }
        
        // Check disk usage
        $diskUsage = $this->getDiskUsage();
        if ($diskUsage['usage_percentage'] > 90) {
            $health['status'] = 'unhealthy';
            $health['checks']['disk'] = 'High disk usage: ' . $diskUsage['usage_percentage'] . '%';
        } else {
            $health['checks']['disk'] = 'OK';
        }
        
        // Check database connections
        $dbConnections = $this->getDatabaseConnections();
        if ($dbConnections['connection_status'] !== 'connected') {
            $health['status'] = 'unhealthy';
            $health['checks']['database'] = 'Database connection error';
        } else {
            $health['checks']['database'] = 'OK';
        }
        
        // Check queue jobs
        $queueJobs = $this->getQueueJobs();
        if ($queueJobs['queue_status'] !== 'active') {
            $health['status'] = 'unhealthy';
            $health['checks']['queue'] = 'Queue system error';
        } else {
            $health['checks']['queue'] = 'OK';
        }
        
        // Check response times
        $responseTimes = $this->getResponseTimes();
        if ($responseTimes['average'] > 5000) { // 5 seconds
            $health['status'] = 'unhealthy';
            $health['checks']['response_time'] = 'Slow response time: ' . $responseTimes['average'] . 'ms';
        } else {
            $health['checks']['response_time'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test database performance
        $dbStartTime = microtime(true);
        DB::table('users')->count();
        $dbTime = microtime(true) - $dbStartTime;
        
        // Test cache performance
        $cacheStartTime = microtime(true);
        Cache::put('test_key', 'test_value', 60);
        Cache::get('test_key');
        Cache::forget('test_key');
        $cacheTime = microtime(true) - $cacheStartTime;
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;
        
        return [
            'total_time' => $totalTime,
            'total_time_ms' => round($totalTime * 1000, 2),
            'database_time' => $dbTime,
            'database_time_ms' => round($dbTime * 1000, 2),
            'cache_time' => $cacheTime,
            'cache_time_ms' => round($cacheTime * 1000, 2),
            'memory_usage' => $this->getMemoryUsage(),
        ];
    }

    /**
     * Get monitoring recommendations
     */
    public function getMonitoringRecommendations(): array
    {
        $stats = $this->getSystemMonitoringStats();
        $recommendations = [];
        
        // Memory usage recommendations
        if ($stats['memory_usage']['usage_percentage'] > 80) {
            $recommendations[] = 'High memory usage detected. Consider optimizing memory usage or increasing memory limit';
        }
        
        // Disk usage recommendations
        if ($stats['disk_usage']['usage_percentage'] > 80) {
            $recommendations[] = 'High disk usage detected. Consider cleaning up old files or increasing disk space';
        }
        
        // Database connection recommendations
        if ($stats['database_connections']['connection_status'] !== 'connected') {
            $recommendations[] = 'Database connection issues detected. Check database configuration and connectivity';
        }
        
        // Queue job recommendations
        if ($stats['queue_jobs']['failed_jobs'] > 10) {
            $recommendations[] = 'Many failed queue jobs detected. Check job processing and error handling';
        }
        
        // Cache hit rate recommendations
        if ($stats['cache_hit_rate']['hit_rate'] < 80) {
            $recommendations[] = 'Low cache hit rate detected. Consider optimizing caching strategy';
        }
        
        // Response time recommendations
        if ($stats['response_times']['average'] > 1000) {
            $recommendations[] = 'Slow response times detected. Consider optimizing queries and caching';
        }
        
        return $recommendations;
    }

    /**
     * Clear monitoring data
     */
    public function clearMonitoringData(): void
    {
        try {
            Cache::forget('response_times');
            Cache::forget('monitoring_stats');
            Log::info('Monitoring data cleared');
        } catch (\Exception $e) {
            Log::error("Failed to clear monitoring data: " . $e->getMessage());
        }
    }

    /**
     * Get monitoring alerts
     */
    public function getMonitoringAlerts(): array
    {
        $alerts = [];
        $stats = $this->getSystemMonitoringStats();
        
        // Memory alert
        if ($stats['memory_usage']['usage_percentage'] > 90) {
            $alerts[] = [
                'type' => 'critical',
                'message' => 'Memory usage is critically high: ' . $stats['memory_usage']['usage_percentage'] . '%',
                'timestamp' => now(),
            ];
        }
        
        // Disk alert
        if ($stats['disk_usage']['usage_percentage'] > 90) {
            $alerts[] = [
                'type' => 'critical',
                'message' => 'Disk usage is critically high: ' . $stats['disk_usage']['usage_percentage'] . '%',
                'timestamp' => now(),
            ];
        }
        
        // Database alert
        if ($stats['database_connections']['connection_status'] !== 'connected') {
            $alerts[] = [
                'type' => 'critical',
                'message' => 'Database connection failed',
                'timestamp' => now(),
            ];
        }
        
        // Queue alert
        if ($stats['queue_jobs']['failed_jobs'] > 50) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'High number of failed queue jobs: ' . $stats['queue_jobs']['failed_jobs'],
                'timestamp' => now(),
            ];
        }
        
        // Response time alert
        if ($stats['response_times']['average'] > 5000) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Slow response times detected: ' . $stats['response_times']['average'] . 'ms',
                'timestamp' => now(),
            ];
        }
        
        return $alerts;
    }
}

