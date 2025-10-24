<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;

class ViewOptimizationService
{
    /**
     * Get view statistics
     */
    public function getViewStats(): array
    {
        return [
            'total_views' => $this->getTotalViews(),
            'view_performance' => $this->getViewPerformance(),
            'view_usage' => $this->getViewUsage(),
            'view_errors' => $this->getViewErrors(),
        ];
    }

    /**
     * Get total views
     */
    private function getTotalViews(): int
    {
        $viewPath = resource_path('views');
        $files = glob($viewPath . '/**/*.blade.php');
        return count($files);
    }

    /**
     * Get view performance
     */
    private function getViewPerformance(): array
    {
        $performance = Cache::get('view_performance', []);
        
        if (empty($performance)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'view_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($performance) / count($performance), 2),
            'fastest_time' => min($performance),
            'slowest_time' => max($performance),
            'view_count' => count($performance),
        ];
    }

    /**
     * Get view usage
     */
    private function getViewUsage(): array
    {
        $usage = Cache::get('view_usage', []);
        
        if (empty($usage)) {
            return [
                'pages' => 0,
                'components' => 0,
                'layouts' => 0,
                'partials' => 0,
            ];
        }
        
        return $usage;
    }

    /**
     * Get view errors
     */
    private function getViewErrors(): array
    {
        return Cache::get('view_errors', []);
    }

    /**
     * Optimize views
     */
    public function optimizeViews(): array
    {
        $optimizations = [];
        
        // Optimize view performance
        $optimizations['performance_optimization'] = $this->optimizeViewPerformance();
        
        // Optimize view caching
        $optimizations['caching_optimization'] = $this->optimizeViewCaching();
        
        // Optimize view structure
        $optimizations['structure_optimization'] = $this->optimizeViewStructure();
        
        // Optimize view security
        $optimizations['security_optimization'] = $this->optimizeViewSecurity();
        
        return $optimizations;
    }

    /**
     * Optimize view performance
     */
    private function optimizeViewPerformance(): array
    {
        try {
            $performance = $this->getViewPerformance();
            $optimizations = [];
            
            // Check average view time
            if ($performance['average_time'] > 100) { // 100ms
                $optimizations[] = 'Average view time is slow. Consider optimizing views';
            }
            
            // Check slowest view time
            if ($performance['slowest_time'] > 1000) { // 1s
                $optimizations[] = 'Some views are very slow. Consider investigating';
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
     * Optimize view caching
     */
    private function optimizeViewCaching(): array
    {
        try {
            $cacheEnabled = config('view.cache_enabled', false);
            $cacheTtl = config('view.cache_ttl', 3600);
            
            $optimizations = [];
            
            // Check if caching is enabled
            if (!$cacheEnabled) {
                $optimizations[] = 'View caching is disabled. Consider enabling for better performance';
            }
            
            // Check cache TTL
            if ($cacheTtl < 300) { // 5 minutes
                $optimizations[] = 'View cache TTL is short. Consider increasing for better performance';
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
     * Optimize view structure
     */
    private function optimizeViewStructure(): array
    {
        try {
            $viewPath = resource_path('views');
            $files = glob($viewPath . '/**/*.blade.php');
            $optimizations = [];
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $filename = basename($file);
                
                // Check for large views
                if (strlen($content) > 50000) { // 50KB
                    $optimizations[] = "View '{$filename}' is large. Consider breaking into smaller components";
                }
                
                // Check for complex views
                $complexity = substr_count($content, '@') + substr_count($content, '{{') + substr_count($content, '{!!');
                if ($complexity > 100) {
                    $optimizations[] = "View '{$filename}' is complex. Consider simplifying";
                }
                
                // Check for inline styles
                if (str_contains($content, '<style>') || str_contains($content, 'style=')) {
                    $optimizations[] = "View '{$filename}' has inline styles. Consider using CSS files";
                }
                
                // Check for inline scripts
                if (str_contains($content, '<script>')) {
                    $optimizations[] = "View '{$filename}' has inline scripts. Consider using JS files";
                }
            }
            
            return [
                'success' => true,
                'files' => count($files),
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
     * Optimize view security
     */
    private function optimizeViewSecurity(): array
    {
        try {
            $viewPath = resource_path('views');
            $files = glob($viewPath . '/**/*.blade.php');
            $optimizations = [];
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $filename = basename($file);
                
                // Check for unescaped output
                if (str_contains($content, '{!!') && !str_contains($content, 'e(')) {
                    $optimizations[] = "View '{$filename}' has unescaped output. Consider using {{ }} for security";
                }
                
                // Check for direct PHP code
                if (str_contains($content, '<?php') && !str_contains($content, '@php')) {
                    $optimizations[] = "View '{$filename}' has direct PHP code. Consider using @php directive";
                }
                
                // Check for sensitive data exposure
                $sensitivePatterns = ['password', 'secret', 'key', 'token'];
                foreach ($sensitivePatterns as $pattern) {
                    if (str_contains($content, $pattern) && str_contains($content, '{{')) {
                        $optimizations[] = "View '{$filename}' may expose sensitive data: {$pattern}";
                    }
                }
            }
            
            return [
                'success' => true,
                'files' => count($files),
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
     * Get view recommendations
     */
    public function getViewRecommendations(): array
    {
        $stats = $this->getViewStats();
        $recommendations = [];
        
        // Performance recommendations
        $performance = $stats['view_performance'];
        if ($performance['average_time'] > 100) {
            $recommendations[] = 'View performance is slow. Consider optimizing views';
        }
        
        // Structure recommendations
        if ($stats['total_views'] > 100) {
            $recommendations[] = 'Many views. Consider organizing into components';
        }
        
        // Error recommendations
        if (count($stats['view_errors']) > 0) {
            $recommendations[] = 'View errors detected. Consider investigating and fixing';
        }
        
        return $recommendations;
    }

    /**
     * Get view health
     */
    public function getViewHealth(): array
    {
        $stats = $this->getViewStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check performance
        $performance = $stats['view_performance'];
        if ($performance['average_time'] > 100) {
            $health['status'] = 'unhealthy';
            $health['checks']['performance'] = 'Slow views: ' . $performance['average_time'] . 'ms';
        } else {
            $health['checks']['performance'] = 'OK';
        }
        
        // Check errors
        if (count($stats['view_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['errors'] = 'View errors: ' . count($stats['view_errors']);
        } else {
            $health['checks']['errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get view performance metrics
     */
    public function getViewPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test view performance
        $this->testViewPerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'view_time' => $executionTime,
            'view_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_views' => $this->getTotalViews(),
            'view_usage' => $this->getViewUsage(),
        ];
    }

    /**
     * Test view performance
     */
    private function testViewPerformance(): void
    {
        try {
            // Test view by rendering a simple view
            $view = View::make('welcome');
            $view->render();
            
        } catch (\Exception $e) {
            Log::error("View test failed: " . $e->getMessage());
        }
    }
}

