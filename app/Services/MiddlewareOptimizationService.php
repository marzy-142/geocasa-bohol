<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class MiddlewareOptimizationService
{
    /**
     * Get middleware statistics
     */
    public function getMiddlewareStats(): array
    {
        return [
            'total_middleware' => $this->getTotalMiddleware(),
            'middleware_performance' => $this->getMiddlewarePerformance(),
            'middleware_usage' => $this->getMiddlewareUsage(),
            'middleware_errors' => $this->getMiddlewareErrors(),
        ];
    }

    /**
     * Get total middleware
     */
    private function getTotalMiddleware(): int
    {
        $middleware = $this->getAllMiddleware();
        return count($middleware);
    }

    /**
     * Get all middleware
     */
    private function getAllMiddleware(): array
    {
        $middleware = [];
        
        // Get global middleware
        $globalMiddleware = app('Illuminate\Contracts\Http\Kernel')->getMiddleware();
        foreach ($globalMiddleware as $middlewareClass) {
            $middleware[] = [
                'name' => $middlewareClass,
                'type' => 'global',
                'priority' => 'high',
            ];
        }
        
        // Get route middleware
        $routeMiddleware = app('router')->getMiddleware();
        foreach ($routeMiddleware as $name => $middlewareClass) {
            $middleware[] = [
                'name' => $name,
                'class' => $middlewareClass,
                'type' => 'route',
                'priority' => 'medium',
            ];
        }
        
        // Get group middleware
        $groupMiddleware = app('router')->getMiddlewareGroups();
        foreach ($groupMiddleware as $name => $middlewareClasses) {
            foreach ($middlewareClasses as $middlewareClass) {
                $middleware[] = [
                    'name' => $name,
                    'class' => $middlewareClass,
                    'type' => 'group',
                    'priority' => 'medium',
                ];
            }
        }
        
        return $middleware;
    }

    /**
     * Get middleware performance
     */
    private function getMiddlewarePerformance(): array
    {
        $performance = Cache::get('middleware_performance', []);
        
        if (empty($performance)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'middleware_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($performance) / count($performance), 2),
            'fastest_time' => min($performance),
            'slowest_time' => max($performance),
            'middleware_count' => count($performance),
        ];
    }

    /**
     * Get middleware usage
     */
    private function getMiddlewareUsage(): array
    {
        $usage = Cache::get('middleware_usage', []);
        
        if (empty($usage)) {
            return [
                'global' => 0,
                'route' => 0,
                'group' => 0,
            ];
        }
        
        return $usage;
    }

    /**
     * Get middleware errors
     */
    private function getMiddlewareErrors(): array
    {
        return Cache::get('middleware_errors', []);
    }

    /**
     * Optimize middleware
     */
    public function optimizeMiddleware(): array
    {
        $optimizations = [];
        
        // Optimize middleware order
        $optimizations['order_optimization'] = $this->optimizeMiddlewareOrder();
        
        // Optimize middleware performance
        $optimizations['performance_optimization'] = $this->optimizeMiddlewarePerformance();
        
        // Optimize middleware caching
        $optimizations['caching_optimization'] = $this->optimizeMiddlewareCaching();
        
        // Optimize middleware security
        $optimizations['security_optimization'] = $this->optimizeMiddlewareSecurity();
        
        return $optimizations;
    }

    /**
     * Optimize middleware order
     */
    private function optimizeMiddlewareOrder(): array
    {
        try {
            $middleware = $this->getAllMiddleware();
            $optimizations = [];
            
            // Check for security middleware order
            $securityMiddleware = ['cors', 'throttle', 'auth', 'csrf'];
            $currentOrder = [];
            
            foreach ($middleware as $mw) {
                if (in_array($mw['name'], $securityMiddleware)) {
                    $currentOrder[] = $mw['name'];
                }
            }
            
            // Check if CORS is before other security middleware
            $corsIndex = array_search('cors', $currentOrder);
            $throttleIndex = array_search('throttle', $currentOrder);
            
            if ($corsIndex !== false && $throttleIndex !== false && $corsIndex > $throttleIndex) {
                $optimizations[] = 'CORS middleware should be before throttle middleware';
            }
            
            // Check for authentication middleware order
            $authIndex = array_search('auth', $currentOrder);
            $csrfIndex = array_search('csrf', $currentOrder);
            
            if ($authIndex !== false && $csrfIndex !== false && $authIndex > $csrfIndex) {
                $optimizations[] = 'Auth middleware should be before CSRF middleware';
            }
            
            return [
                'success' => true,
                'middleware' => $middleware,
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
     * Optimize middleware performance
     */
    private function optimizeMiddlewarePerformance(): array
    {
        try {
            $performance = $this->getMiddlewarePerformance();
            $optimizations = [];
            
            // Check average middleware time
            if ($performance['average_time'] > 50) { // 50ms
                $optimizations[] = 'Average middleware time is slow. Consider optimizing middleware';
            }
            
            // Check slowest middleware time
            if ($performance['slowest_time'] > 200) { // 200ms
                $optimizations[] = 'Some middleware is very slow. Consider investigating';
            }
            
            return [
                'success' => true,
                'performance' => $performance,
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
     * Optimize middleware caching
     */
    private function optimizeMiddlewareCaching(): array
    {
        try {
            $cacheEnabled = config('middleware.cache_enabled', false);
            $cacheTtl = config('middleware.cache_ttl', 3600);
            
            $optimizations = [];
            
            // Check if caching is enabled
            if (!$cacheEnabled) {
                $optimizations[] = 'Middleware caching is disabled. Consider enabling for better performance';
            }
            
            // Check cache TTL
            if ($cacheTtl < 300) { // 5 minutes
                $optimizations[] = 'Middleware cache TTL is short. Consider increasing for better performance';
            }
            
            return [
                'success' => true,
                'cache_enabled' => $cacheEnabled,
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
     * Optimize middleware security
     */
    private function optimizeMiddlewareSecurity(): array
    {
        try {
            $middleware = $this->getAllMiddleware();
            $optimizations = [];
            
            // Check for required security middleware
            $requiredSecurity = ['cors', 'throttle', 'auth', 'csrf'];
            $presentSecurity = [];
            
            foreach ($middleware as $mw) {
                if (in_array($mw['name'], $requiredSecurity)) {
                    $presentSecurity[] = $mw['name'];
                }
            }
            
            $missingSecurity = array_diff($requiredSecurity, $presentSecurity);
            foreach ($missingSecurity as $missing) {
                $optimizations[] = "Missing required security middleware: {$missing}";
            }
            
            // Check for insecure middleware
            $insecureMiddleware = ['trust-proxies', 'api'];
            foreach ($middleware as $mw) {
                if (in_array($mw['name'], $insecureMiddleware)) {
                    $optimizations[] = "Potentially insecure middleware: {$mw['name']}";
                }
            }
            
            return [
                'success' => true,
                'middleware' => $middleware,
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
     * Get middleware recommendations
     */
    public function getMiddlewareRecommendations(): array
    {
        $stats = $this->getMiddlewareStats();
        $recommendations = [];
        
        // Performance recommendations
        $performance = $stats['middleware_performance'];
        if ($performance['average_time'] > 50) {
            $recommendations[] = 'Middleware performance is slow. Consider optimizing middleware';
        }
        
        // Usage recommendations
        $usage = $stats['middleware_usage'];
        if ($usage['global'] > 10) {
            $recommendations[] = 'Many global middleware. Consider moving some to route middleware';
        }
        
        // Error recommendations
        if (count($stats['middleware_errors']) > 0) {
            $recommendations[] = 'Middleware errors detected. Consider investigating and fixing';
        }
        
        return $recommendations;
    }

    /**
     * Get middleware health
     */
    public function getMiddlewareHealth(): array
    {
        $stats = $this->getMiddlewareStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check performance
        $performance = $stats['middleware_performance'];
        if ($performance['average_time'] > 50) {
            $health['status'] = 'unhealthy';
            $health['checks']['performance'] = 'Slow middleware: ' . $performance['average_time'] . 'ms';
        } else {
            $health['checks']['performance'] = 'OK';
        }
        
        // Check errors
        if (count($stats['middleware_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['errors'] = 'Middleware errors: ' . count($stats['middleware_errors']);
        } else {
            $health['checks']['errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get middleware performance metrics
     */
    public function getMiddlewarePerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test middleware performance
        $this->testMiddlewarePerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'middleware_time' => $executionTime,
            'middleware_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_middleware' => $this->getTotalMiddleware(),
            'middleware_usage' => $this->getMiddlewareUsage(),
        ];
    }

    /**
     * Test middleware performance
     */
    private function testMiddlewarePerformance(): void
    {
        try {
            // Test middleware by making a request
            $response = app('Illuminate\Contracts\Http\Kernel')->handle(
                app('Illuminate\Http\Request')->create('/test', 'GET')
            );
            
        } catch (\Exception $e) {
            Log::error("Middleware test failed: " . $e->getMessage());
        }
    }
}

