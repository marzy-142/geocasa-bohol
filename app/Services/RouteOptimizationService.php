<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class RouteOptimizationService
{
    /**
     * Get route statistics
     */
    public function getRouteStats(): array
    {
        return [
            'total_routes' => $this->getTotalRoutes(),
            'route_performance' => $this->getRoutePerformance(),
            'route_usage' => $this->getRouteUsage(),
            'route_errors' => $this->getRouteErrors(),
        ];
    }

    /**
     * Get total routes
     */
    private function getTotalRoutes(): int
    {
        $routes = Route::getRoutes();
        return count($routes);
    }

    /**
     * Get route performance
     */
    private function getRoutePerformance(): array
    {
        $performance = Cache::get('route_performance', []);
        
        if (empty($performance)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'route_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($performance) / count($performance), 2),
            'fastest_time' => min($performance),
            'slowest_time' => max($performance),
            'route_count' => count($performance),
        ];
    }

    /**
     * Get route usage
     */
    private function getRouteUsage(): array
    {
        $usage = Cache::get('route_usage', []);
        
        if (empty($usage)) {
            return [
                'web' => 0,
                'api' => 0,
                'admin' => 0,
                'other' => 0,
            ];
        }
        
        return $usage;
    }

    /**
     * Get route errors
     */
    private function getRouteErrors(): array
    {
        return Cache::get('route_errors', []);
    }

    /**
     * Optimize routes
     */
    public function optimizeRoutes(): array
    {
        $optimizations = [];
        
        // Optimize route order
        $optimizations['order_optimization'] = $this->optimizeRouteOrder();
        
        // Optimize route performance
        $optimizations['performance_optimization'] = $this->optimizeRoutePerformance();
        
        // Optimize route caching
        $optimizations['caching_optimization'] = $this->optimizeRouteCaching();
        
        // Optimize route security
        $optimizations['security_optimization'] = $this->optimizeRouteSecurity();
        
        return $optimizations;
    }

    /**
     * Optimize route order
     */
    private function optimizeRouteOrder(): array
    {
        try {
            $routes = Route::getRoutes();
            $optimizations = [];
            
            // Check for route conflicts
            $routePaths = [];
            foreach ($routes as $route) {
                $path = $route->uri();
                if (isset($routePaths[$path])) {
                    $optimizations[] = "Duplicate route path: {$path}";
                } else {
                    $routePaths[$path] = $route->methods();
                }
            }
            
            // Check for route priority
            $highPriorityRoutes = ['/api/', '/admin/', '/auth/'];
            $lowPriorityRoutes = ['/{slug}', '/{id}', '/{any}'];
            
            foreach ($routes as $route) {
                $path = $route->uri();
                
                // Check if high priority routes are after low priority routes
                foreach ($highPriorityRoutes as $highPriority) {
                    if (str_contains($path, $highPriority)) {
                        foreach ($lowPriorityRoutes as $lowPriority) {
                            if (str_contains($path, $lowPriority)) {
                                $optimizations[] = "Route '{$path}' has conflicting priority patterns";
                            }
                        }
                    }
                }
            }
            
            return [
                'success' => true,
                'routes' => count($routes),
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
     * Optimize route performance
     */
    private function optimizeRoutePerformance(): array
    {
        try {
            $performance = $this->getRoutePerformance();
            $optimizations = [];
            
            // Check average route time
            if ($performance['average_time'] > 100) { // 100ms
                $optimizations[] = 'Average route time is slow. Consider optimizing routes';
            }
            
            // Check slowest route time
            if ($performance['slowest_time'] > 1000) { // 1s
                $optimizations[] = 'Some routes are very slow. Consider investigating';
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
     * Optimize route caching
     */
    private function optimizeRouteCaching(): array
    {
        try {
            $cacheEnabled = config('route.cache_enabled', false);
            $cacheTtl = config('route.cache_ttl', 3600);
            
            $optimizations = [];
            
            // Check if caching is enabled
            if (!$cacheEnabled) {
                $optimizations[] = 'Route caching is disabled. Consider enabling for better performance';
            }
            
            // Check cache TTL
            if ($cacheTtl < 300) { // 5 minutes
                $optimizations[] = 'Route cache TTL is short. Consider increasing for better performance';
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
     * Optimize route security
     */
    private function optimizeRouteSecurity(): array
    {
        try {
            $routes = Route::getRoutes();
            $optimizations = [];
            
            // Check for unprotected routes
            $unprotectedRoutes = [];
            foreach ($routes as $route) {
                $path = $route->uri();
                $middleware = $route->gatherMiddleware();
                
                // Check if route is unprotected
                if (empty($middleware) && !str_contains($path, '/public/')) {
                    $unprotectedRoutes[] = $path;
                }
            }
            
            if (!empty($unprotectedRoutes)) {
                $optimizations[] = 'Unprotected routes detected: ' . implode(', ', $unprotectedRoutes);
            }
            
            // Check for sensitive routes without authentication
            $sensitiveRoutes = ['/admin/', '/api/', '/dashboard/'];
            foreach ($routes as $route) {
                $path = $route->uri();
                $middleware = $route->gatherMiddleware();
                
                foreach ($sensitiveRoutes as $sensitive) {
                    if (str_contains($path, $sensitive) && !in_array('auth', $middleware)) {
                        $optimizations[] = "Sensitive route '{$path}' without authentication";
                    }
                }
            }
            
            return [
                'success' => true,
                'routes' => count($routes),
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
     * Get route recommendations
     */
    public function getRouteRecommendations(): array
    {
        $stats = $this->getRouteStats();
        $recommendations = [];
        
        // Performance recommendations
        $performance = $stats['route_performance'];
        if ($performance['average_time'] > 100) {
            $recommendations[] = 'Route performance is slow. Consider optimizing routes';
        }
        
        // Usage recommendations
        $usage = $stats['route_usage'];
        if ($usage['api'] > 50) {
            $recommendations[] = 'Many API routes. Consider implementing rate limiting';
        }
        
        // Error recommendations
        if (count($stats['route_errors']) > 0) {
            $recommendations[] = 'Route errors detected. Consider investigating and fixing';
        }
        
        return $recommendations;
    }

    /**
     * Get route health
     */
    public function getRouteHealth(): array
    {
        $stats = $this->getRouteStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check performance
        $performance = $stats['route_performance'];
        if ($performance['average_time'] > 100) {
            $health['status'] = 'unhealthy';
            $health['checks']['performance'] = 'Slow routes: ' . $performance['average_time'] . 'ms';
        } else {
            $health['checks']['performance'] = 'OK';
        }
        
        // Check errors
        if (count($stats['route_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['errors'] = 'Route errors: ' . count($stats['route_errors']);
        } else {
            $health['checks']['errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get route performance metrics
     */
    public function getRoutePerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test route performance
        $this->testRoutePerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'route_time' => $executionTime,
            'route_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_routes' => $this->getTotalRoutes(),
            'route_usage' => $this->getRouteUsage(),
        ];
    }

    /**
     * Test route performance
     */
    private function testRoutePerformance(): void
    {
        try {
            // Test route by making a request
            $response = app('Illuminate\Contracts\Http\Kernel')->handle(
                app('Illuminate\Http\Request')->create('/test', 'GET')
            );
            
        } catch (\Exception $e) {
            Log::error("Route test failed: " . $e->getMessage());
        }
    }
}

