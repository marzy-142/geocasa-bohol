<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class ConfigurationOptimizationService
{
    /**
     * Get configuration statistics
     */
    public function getConfigurationStats(): array
    {
        return [
            'total_configs' => $this->getTotalConfigs(),
            'config_size' => $this->getConfigSize(),
            'config_performance' => $this->getConfigPerformance(),
            'config_errors' => $this->getConfigErrors(),
        ];
    }

    /**
     * Get total configs
     */
    private function getTotalConfigs(): int
    {
        $configPath = config_path();
        $files = glob($configPath . '/*.php');
        return count($files);
    }

    /**
     * Get config size
     */
    private function getConfigSize(): array
    {
        $configPath = config_path();
        $totalSize = 0;
        $fileCount = 0;
        
        if (is_dir($configPath)) {
            $files = glob($configPath . '/*.php');
            
            foreach ($files as $file) {
                if (is_file($file)) {
                    $totalSize += filesize($file);
                    $fileCount++;
                }
            }
        }
        
        return [
            'total_size' => $totalSize,
            'total_size_kb' => round($totalSize / 1024, 2),
            'file_count' => $fileCount,
            'average_size' => $fileCount > 0 ? round($totalSize / $fileCount) : 0,
        ];
    }

    /**
     * Get config performance
     */
    private function getConfigPerformance(): array
    {
        $performance = Cache::get('config_performance', []);
        
        if (empty($performance)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'config_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($performance) / count($performance), 2),
            'fastest_time' => min($performance),
            'slowest_time' => max($performance),
            'config_count' => count($performance),
        ];
    }

    /**
     * Get config errors
     */
    private function getConfigErrors(): array
    {
        return Cache::get('config_errors', []);
    }

    /**
     * Optimize configuration
     */
    public function optimizeConfiguration(): array
    {
        $optimizations = [];
        
        // Optimize config performance
        $optimizations['performance_optimization'] = $this->optimizeConfigPerformance();
        
        // Optimize config caching
        $optimizations['caching_optimization'] = $this->optimizeConfigCaching();
        
        // Optimize config structure
        $optimizations['structure_optimization'] = $this->optimizeConfigStructure();
        
        // Optimize config security
        $optimizations['security_optimization'] = $this->optimizeConfigSecurity();
        
        return $optimizations;
    }

    /**
     * Optimize config performance
     */
    private function optimizeConfigPerformance(): array
    {
        try {
            $performance = $this->getConfigPerformance();
            $optimizations = [];
            
            // Check average config time
            if ($performance['average_time'] > 10) { // 10ms
                $optimizations[] = 'Average config time is slow. Consider optimizing config files';
            }
            
            // Check slowest config time
            if ($performance['slowest_time'] > 100) { // 100ms
                $optimizations[] = 'Some config files are very slow. Consider investigating';
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
     * Optimize config caching
     */
    private function optimizeConfigCaching(): array
    {
        try {
            $cacheEnabled = config('config.cache_enabled', false);
            $cacheTtl = config('config.cache_ttl', 3600);
            
            $optimizations = [];
            
            // Check if caching is enabled
            if (!$cacheEnabled) {
                $optimizations[] = 'Config caching is disabled. Consider enabling for better performance';
            }
            
            // Check cache TTL
            if ($cacheTtl < 300) { // 5 minutes
                $optimizations[] = 'Config cache TTL is short. Consider increasing for better performance';
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
     * Optimize config structure
     */
    private function optimizeConfigStructure(): array
    {
        try {
            $configPath = config_path();
            $files = glob($configPath . '/*.php');
            $optimizations = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);
                
                // Check for large config files
                if (strlen($content) > 10000) { // 10KB
                    $optimizations[] = "Config file '{$filename}' is large. Consider breaking into smaller files";
                }
                
                // Check for complex config files
                $complexity = substr_count($content, '[') + substr_count($content, ']') + substr_count($content, '=>');
                if ($complexity > 100) {
                    $optimizations[] = "Config file '{$filename}' is complex. Consider simplifying";
                }
                
                // Check for hardcoded values
                if (str_contains($content, 'localhost') || str_contains($content, '127.0.0.1')) {
                    $optimizations[] = "Config file '{$filename}' contains hardcoded values. Consider using environment variables";
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
     * Optimize config security
     */
    private function optimizeConfigSecurity(): array
    {
        try {
            $configPath = config_path();
            $files = glob($configPath . '/*.php');
            $optimizations = [];
            
            foreach ($files as $file) {
                $filename = basename($file);
                $content = file_get_contents($file);
                
                // Check for sensitive data
                $sensitivePatterns = ['password', 'secret', 'key', 'token', 'api_key'];
                foreach ($sensitivePatterns as $pattern) {
                    if (str_contains($content, $pattern) && !str_contains($content, 'env(')) {
                        $optimizations[] = "Config file '{$filename}' may contain sensitive data: {$pattern}";
                    }
                }
                
                // Check for debug settings
                if (str_contains($content, 'debug') && str_contains($content, 'true')) {
                    $optimizations[] = "Config file '{$filename}' has debug enabled. Consider disabling in production";
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
     * Get config recommendations
     */
    public function getConfigRecommendations(): array
    {
        $stats = $this->getConfigurationStats();
        $recommendations = [];
        
        // Performance recommendations
        $performance = $stats['config_performance'];
        if ($performance['average_time'] > 10) {
            $recommendations[] = 'Config performance is slow. Consider optimizing config files';
        }
        
        // Size recommendations
        if ($stats['config_size']['total_size_kb'] > 100) {
            $recommendations[] = 'Config files are large. Consider optimizing or splitting';
        }
        
        // Error recommendations
        if (count($stats['config_errors']) > 0) {
            $recommendations[] = 'Config errors detected. Consider investigating and fixing';
        }
        
        return $recommendations;
    }

    /**
     * Get config health
     */
    public function getConfigHealth(): array
    {
        $stats = $this->getConfigurationStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check performance
        $performance = $stats['config_performance'];
        if ($performance['average_time'] > 10) {
            $health['status'] = 'unhealthy';
            $health['checks']['performance'] = 'Slow config: ' . $performance['average_time'] . 'ms';
        } else {
            $health['checks']['performance'] = 'OK';
        }
        
        // Check errors
        if (count($stats['config_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['errors'] = 'Config errors: ' . count($stats['config_errors']);
        } else {
            $health['checks']['errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get config performance metrics
     */
    public function getConfigPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test config performance
        $this->testConfigPerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'config_time' => $executionTime,
            'config_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_configs' => $this->getTotalConfigs(),
            'config_size_kb' => $this->getConfigSize()['total_size_kb'],
        ];
    }

    /**
     * Test config performance
     */
    private function testConfigPerformance(): void
    {
        try {
            // Test config access by reading some common configs
            Config::get('app.name');
            Config::get('database.default');
            Config::get('cache.default');
            Config::get('session.driver');
            Config::get('queue.default');
            
        } catch (\Exception $e) {
            Log::error("Config test failed: " . $e->getMessage());
        }
    }
}