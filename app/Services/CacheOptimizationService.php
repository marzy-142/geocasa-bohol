<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;

class CacheOptimizationService
{
    /**
     * Optimize cache configuration and performance
     */
    public function optimize(): array
    {
        $results = [
            'cache_clear' => $this->clearExpiredCaches(),
            'cache_configuration' => $this->optimizeCacheConfiguration(),
            'redis_optimization' => $this->optimizeRedis(),
            'memory_optimization' => $this->optimizeMemoryUsage(),
            'ttl_optimization' => $this->optimizeTTL(),
        ];

        Log::info('Cache optimization completed', $results);
        return $results;
    }

    /**
     * Clear expired caches
     */
    private function clearExpiredCaches(): array
    {
        $results = [];
        
        try {
            // Clear Laravel caches
            Artisan::call('cache:clear');
            $results['laravel_cache'] = 'Cleared successfully';
            
            // Clear config cache
            Artisan::call('config:clear');
            $results['config_cache'] = 'Cleared successfully';
            
            // Clear route cache
            Artisan::call('route:clear');
            $results['route_cache'] = 'Cleared successfully';
            
            // Clear view cache
            Artisan::call('view:clear');
            $results['view_cache'] = 'Cleared successfully';
            
            // Clear application cache
            Cache::flush();
            $results['application_cache'] = 'Flushed successfully';
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            Log::error('Cache clearing failed: ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Optimize cache configuration
     */
    private function optimizeCacheConfiguration(): array
    {
        $results = [];
        
        try {
            // Check cache driver configuration
            $driver = config('cache.default');
            $results['current_driver'] = $driver;
            
            // Optimize based on driver
            switch ($driver) {
                case 'redis':
                    $results['redis_optimization'] = $this->optimizeRedisConfiguration();
                    break;
                case 'file':
                    $results['file_optimization'] = $this->optimizeFileCacheConfiguration();
                    break;
                case 'database':
                    $results['database_optimization'] = $this->optimizeDatabaseCacheConfiguration();
                    break;
                default:
                    $results['recommendation'] = 'Consider using Redis for better performance';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            Log::error('Cache configuration optimization failed: ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Optimize Redis configuration
     */
    private function optimizeRedis(): array
    {
        $results = [];
        
        try {
            if (config('cache.default') === 'redis') {
                // Check Redis connection
                Redis::ping();
                $results['connection'] = 'Connected successfully';
                
                // Get Redis info
                $info = Redis::info();
                $results['redis_version'] = $info['redis_version'] ?? 'Unknown';
                $results['used_memory'] = $info['used_memory_human'] ?? 'Unknown';
                $results['connected_clients'] = $info['connected_clients'] ?? 'Unknown';
                
                // Optimize Redis settings
                $results['optimizations'] = $this->applyRedisOptimizations();
                
            } else {
                $results['status'] = 'Redis not configured as cache driver';
            }
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
            Log::error('Redis optimization failed: ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Apply Redis optimizations
     */
    private function applyRedisOptimizations(): array
    {
        $optimizations = [];
        
        try {
            // Set optimal Redis configuration
            Redis::config('SET', 'maxmemory-policy', 'allkeys-lru');
            $optimizations['maxmemory_policy'] = 'Set to allkeys-lru';
            
            // Enable compression for large values
            Redis::config('SET', 'hash-max-ziplist-entries', '512');
            $optimizations['hash_compression'] = 'Optimized for memory efficiency';
            
        } catch (\Exception $e) {
            $optimizations['error'] = $e->getMessage();
        }
        
        return $optimizations;
    }

    /**
     * Optimize Redis configuration
     */
    private function optimizeRedisConfiguration(): array
    {
        return [
            'recommendations' => [
                'Enable Redis persistence for production',
                'Configure appropriate maxmemory settings',
                'Use Redis clustering for high availability',
                'Monitor Redis memory usage regularly'
            ]
        ];
    }

    /**
     * Optimize file cache configuration
     */
    private function optimizeFileCacheConfiguration(): array
    {
        return [
            'recommendations' => [
                'Ensure cache directory has proper permissions',
                'Consider using SSD storage for better performance',
                'Monitor disk space usage',
                'Implement cache cleanup strategy'
            ]
        ];
    }

    /**
     * Optimize database cache configuration
     */
    private function optimizeDatabaseCacheConfiguration(): array
    {
        return [
            'recommendations' => [
                'Index the cache table properly',
                'Regular cleanup of expired entries',
                'Monitor database performance',
                'Consider using dedicated cache database'
            ]
        ];
    }

    /**
     * Optimize memory usage
     */
    private function optimizeMemoryUsage(): array
    {
        $results = [];
        
        try {
            // Get current memory usage
            $results['current_memory'] = memory_get_usage(true);
            $results['peak_memory'] = memory_get_peak_usage(true);
            
            // Force garbage collection
            gc_collect_cycles();
            $results['garbage_collection'] = 'Performed';
            
            // Get memory after GC
            $results['memory_after_gc'] = memory_get_usage(true);
            
        } catch (\Exception $e) {
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }

    /**
     * Optimize TTL (Time To Live) settings
     */
    private function optimizeTTL(): array
    {
        return [
            'recommendations' => [
                'Use appropriate TTL for different cache types',
                'Implement cache warming strategies',
                'Monitor cache hit rates',
                'Set shorter TTL for frequently changing data'
            ],
            'suggested_ttl' => [
                'user_sessions' => '30 minutes',
                'configuration_data' => '1 hour',
                'static_content' => '24 hours',
                'database_queries' => '15 minutes'
            ]
        ];
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        $stats = [];
        
        try {
            // Get cache driver info
            $stats['driver'] = config('cache.default');
            
            // Get cache store info
            $stats['store'] = config('cache.stores.' . config('cache.default'));
            
            // Get memory usage
            $stats['memory_usage'] = memory_get_usage(true);
            $stats['peak_memory'] = memory_get_peak_usage(true);
            
            // Test cache performance
            $start = microtime(true);
            Cache::put('test_key', 'test_value', 60);
            Cache::get('test_key');
            Cache::forget('test_key');
            $stats['cache_performance'] = (microtime(true) - $start) * 1000; // in milliseconds
            
        } catch (\Exception $e) {
            $stats['error'] = $e->getMessage();
        }
        
        return $stats;
    }

    /**
     * Get cache health status
     */
    public function getHealth(): array
    {
        $health = ['status' => 'healthy'];
        
        try {
            // Test cache functionality
            Cache::put('health_check', 'ok', 60);
            $value = Cache::get('health_check');
            
            if ($value !== 'ok') {
                $health['status'] = 'unhealthy';
                $health['error'] = 'Cache read/write test failed';
            } else {
                Cache::forget('health_check');
            }
            
            // Check memory usage
            $memoryUsage = memory_get_usage(true);
            if ($memoryUsage > 100 * 1024 * 1024) { // 100MB
                $health['warning'] = 'High memory usage detected';
            }
            
        } catch (\Exception $e) {
            $health['status'] = 'unhealthy';
            $health['error'] = $e->getMessage();
        }
        
        return $health;
    }

    /**
     * Get recommendations
     */
    public function getRecommendations(): array
    {
        return [
            'cache_driver' => [
                'Consider using Redis for better performance in production',
                'Implement cache tagging for better cache management'
            ],
            'performance' => [
                'Monitor cache hit rates regularly',
                'Implement cache warming strategies',
                'Use appropriate TTL values for different data types'
            ],
            'monitoring' => [
                'Set up cache monitoring and alerting',
                'Implement cache performance metrics',
                'Regular cache cleanup and optimization'
            ]
        ];
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        try {
            Cache::flush();
            Artisan::call('cache:clear');
            Log::info('Cache cleared successfully');
        } catch (\Exception $e) {
            Log::error('Cache clearing failed: ' . $e->getMessage());
        }
    }

    /**
     * Warm up cache
     */
    public function warmUpCache(): void
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            Log::info('Cache warmed up successfully');
        } catch (\Exception $e) {
            Log::error('Cache warming failed: ' . $e->getMessage());
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        $metrics = [];
        
        try {
            // Cache performance test
            $iterations = 1000;
            $start = microtime(true);
            
            for ($i = 0; $i < $iterations; $i++) {
                Cache::put("test_metric_{$i}", "value_{$i}", 60);
                Cache::get("test_metric_{$i}");
            }
            
            // Cleanup test keys
            for ($i = 0; $i < $iterations; $i++) {
                Cache::forget("test_metric_{$i}");
            }
            
            $end = microtime(true);
            $metrics['operations_per_second'] = $iterations / ($end - $start);
            $metrics['average_operation_time'] = (($end - $start) / $iterations) * 1000; // milliseconds
            
        } catch (\Exception $e) {
            $metrics['error'] = $e->getMessage();
        }
        
        return $metrics;
    }
}
