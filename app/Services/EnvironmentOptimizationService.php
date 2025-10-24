<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class EnvironmentOptimizationService
{
    /**
     * Get environment statistics
     */
    public function getEnvironmentStats(): array
    {
        return [
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'log_level' => config('logging.level'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'database_driver' => config('database.default'),
        ];
    }

    /**
     * Optimize environment
     */
    public function optimizeEnvironment(): array
    {
        $optimizations = [];
        
        // Optimize debug settings
        $optimizations['debug_optimization'] = $this->optimizeDebugSettings();
        
        // Optimize logging settings
        $optimizations['logging_optimization'] = $this->optimizeLoggingSettings();
        
        // Optimize cache settings
        $optimizations['cache_optimization'] = $this->optimizeCacheSettings();
        
        // Optimize session settings
        $optimizations['session_optimization'] = $this->optimizeSessionSettings();
        
        // Optimize queue settings
        $optimizations['queue_optimization'] = $this->optimizeQueueSettings();
        
        // Optimize database settings
        $optimizations['database_optimization'] = $this->optimizeDatabaseSettings();
        
        return $optimizations;
    }

    /**
     * Optimize debug settings
     */
    private function optimizeDebugSettings(): array
    {
        try {
            $debugMode = config('app.debug');
            $environment = app()->environment();
            $optimizations = [];
            
            // Check debug mode in production
            if ($debugMode && $environment === 'production') {
                $optimizations[] = 'Debug mode is enabled in production. Consider disabling for security';
            }
            
            // Check log level in production
            $logLevel = config('logging.level');
            if ($logLevel === 'debug' && $environment === 'production') {
                $optimizations[] = 'Log level is debug in production. Consider setting to info or warning';
            }
            
            return [
                'success' => true,
                'debug_mode' => $debugMode,
                'environment' => $environment,
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize logging settings
     */
    private function optimizeLoggingSettings(): array
    {
        try {
            $logLevel = config('logging.level');
            $logDriver = config('logging.default');
            $optimizations = [];
            
            // Check log level
            if ($logLevel === 'debug' && app()->environment('production')) {
                $optimizations[] = 'Log level is debug in production. Consider setting to info or warning';
            }
            
            // Check log driver
            if ($logDriver === 'single' && app()->environment('production')) {
                $optimizations[] = 'Using single log driver in production. Consider using daily or syslog';
            }
            
            return [
                'success' => true,
                'log_level' => $logLevel,
                'log_driver' => $logDriver,
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize cache settings
     */
    private function optimizeCacheSettings(): array
    {
        try {
            $cacheDriver = config('cache.default');
            $cacheTtl = config('cache.ttl', 3600);
            $optimizations = [];
            
            // Check cache driver
            if ($cacheDriver === 'file' && app()->environment('production')) {
                $optimizations[] = 'Using file cache in production. Consider using Redis or Memcached';
            }
            
            // Check cache TTL
            if ($cacheTtl < 300) { // 5 minutes
                $optimizations[] = 'Cache TTL is short. Consider increasing for better performance';
            }
            
            return [
                'success' => true,
                'cache_driver' => $cacheDriver,
                'cache_ttl' => $cacheTtl,
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize session settings
     */
    private function optimizeSessionSettings(): array
    {
        try {
            $sessionDriver = config('session.driver');
            $sessionLifetime = config('session.lifetime');
            $sessionSecure = config('session.secure');
            $optimizations = [];
            
            // Check session driver
            if ($sessionDriver === 'file' && app()->environment('production')) {
                $optimizations[] = 'Using file session driver in production. Consider using Redis or database';
            }
            
            // Check session lifetime
            if ($sessionLifetime > 1440) { // 24 hours
                $optimizations[] = 'Session lifetime is long. Consider reducing for security';
            }
            
            // Check session secure flag
            if (!$sessionSecure && app()->environment('production')) {
                $optimizations[] = 'Session secure flag is disabled in production. Consider enabling for security';
            }
            
            return [
                'success' => true,
                'session_driver' => $sessionDriver,
                'session_lifetime' => $sessionLifetime,
                'session_secure' => $sessionSecure,
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize queue settings
     */
    private function optimizeQueueSettings(): array
    {
        try {
            $queueDriver = config('queue.default');
            $queueRetry = config('queue.retry_after');
            $queueTimeout = config('queue.timeout');
            $optimizations = [];
            
            // Check queue driver
            if ($queueDriver === 'sync' && app()->environment('production')) {
                $optimizations[] = 'Using sync queue driver in production. Consider using Redis or database';
            }
            
            // Check queue retry
            if ($queueRetry < 60) {
                $optimizations[] = 'Queue retry after is short. Consider increasing for better reliability';
            }
            
            // Check queue timeout
            if ($queueTimeout > 300) { // 5 minutes
                $optimizations[] = 'Queue timeout is long. Consider reducing to prevent long-running jobs';
            }
            
            return [
                'success' => true,
                'queue_driver' => $queueDriver,
                'queue_retry' => $queueRetry,
                'queue_timeout' => $queueTimeout,
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Optimize database settings
     */
    private function optimizeDatabaseSettings(): array
    {
        try {
            $dbDriver = config('database.default');
            $dbConnection = config("database.connections.{$dbDriver}");
            $optimizations = [];
            
            // Check database driver
            if ($dbDriver === 'sqlite' && app()->environment('production')) {
                $optimizations[] = 'Using SQLite in production. Consider using MySQL or PostgreSQL';
            }
            
            // Check database connection pooling
            if (!isset($dbConnection['options']['pool'])) {
                $optimizations[] = 'Database connection pooling is not configured. Consider enabling for better performance';
            }
            
            // Check database strict mode
            if (!isset($dbConnection['strict']) || !$dbConnection['strict']) {
                $optimizations[] = 'Database strict mode is disabled. Consider enabling for better data integrity';
            }
            
            return [
                'success' => true,
                'database_driver' => $dbDriver,
                'database_connection' => $dbConnection,
                'optimizations' => $optimizations,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get environment recommendations
     */
    public function getEnvironmentRecommendations(): array
    {
        $stats = $this->getEnvironmentStats();
        $recommendations = [];
        
        // Debug recommendations
        if ($stats['debug_mode'] && app()->environment('production')) {
            $recommendations[] = 'Debug mode is enabled in production. Consider disabling for security';
        }
        
        // Logging recommendations
        if ($stats['log_level'] === 'debug' && app()->environment('production')) {
            $recommendations[] = 'Log level is debug in production. Consider setting to info or warning';
        }
        
        // Cache recommendations
        if ($stats['cache_driver'] === 'file' && app()->environment('production')) {
            $recommendations[] = 'Using file cache in production. Consider using Redis or Memcached';
        }
        
        // Session recommendations
        if ($stats['session_driver'] === 'file' && app()->environment('production')) {
            $recommendations[] = 'Using file session driver in production. Consider using Redis or database';
        }
        
        // Queue recommendations
        if ($stats['queue_driver'] === 'sync' && app()->environment('production')) {
            $recommendations[] = 'Using sync queue driver in production. Consider using Redis or database';
        }
        
        // Database recommendations
        if ($stats['database_driver'] === 'sqlite' && app()->environment('production')) {
            $recommendations[] = 'Using SQLite in production. Consider using MySQL or PostgreSQL';
        }
        
        return $recommendations;
    }

    /**
     * Get environment health
     */
    public function getEnvironmentHealth(): array
    {
        $stats = $this->getEnvironmentStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check debug mode
        if ($stats['debug_mode'] && app()->environment('production')) {
            $health['status'] = 'unhealthy';
            $health['checks']['debug_mode'] = 'Debug mode enabled in production';
        } else {
            $health['checks']['debug_mode'] = 'OK';
        }
        
        // Check log level
        if ($stats['log_level'] === 'debug' && app()->environment('production')) {
            $health['status'] = 'unhealthy';
            $health['checks']['log_level'] = 'Debug log level in production';
        } else {
            $health['checks']['log_level'] = 'OK';
        }
        
        // Check cache driver
        if ($stats['cache_driver'] === 'file' && app()->environment('production')) {
            $health['status'] = 'unhealthy';
            $health['checks']['cache_driver'] = 'File cache in production';
        } else {
            $health['checks']['cache_driver'] = 'OK';
        }
        
        // Check session driver
        if ($stats['session_driver'] === 'file' && app()->environment('production')) {
            $health['status'] = 'unhealthy';
            $health['checks']['session_driver'] = 'File session driver in production';
        } else {
            $health['checks']['session_driver'] = 'OK';
        }
        
        // Check queue driver
        if ($stats['queue_driver'] === 'sync' && app()->environment('production')) {
            $health['status'] = 'unhealthy';
            $health['checks']['queue_driver'] = 'Sync queue driver in production';
        } else {
            $health['checks']['queue_driver'] = 'OK';
        }
        
        // Check database driver
        if ($stats['database_driver'] === 'sqlite' && app()->environment('production')) {
            $health['status'] = 'unhealthy';
            $health['checks']['database_driver'] = 'SQLite database in production';
        } else {
            $health['checks']['database_driver'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get environment performance metrics
     */
    public function getEnvironmentPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test environment performance
        $this->testEnvironmentPerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'environment_time' => $executionTime,
            'environment_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
        ];
    }

    /**
     * Test environment performance
     */
    private function testEnvironmentPerformance(): void
    {
        try {
            // Test environment access
            app()->environment();
            config('app.debug');
            config('logging.level');
            config('cache.default');
            config('session.driver');
            config('queue.default');
            config('database.default');
            
        } catch (\Exception $e) {
            Log::error("Environment test failed: " . $e->getMessage());
        }
    }
}