<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DeploymentOptimizationService
{
    /**
     * Get deployment statistics
     */
    public function getDeploymentStats(): array
    {
        return [
            'deployment_count' => $this->getDeploymentCount(),
            'last_deployment' => $this->getLastDeployment(),
            'deployment_status' => $this->getDeploymentStatus(),
            'deployment_time' => $this->getDeploymentTime(),
            'deployment_memory' => $this->getDeploymentMemory(),
            'deployment_errors' => $this->getDeploymentErrors(),
        ];
    }

    /**
     * Get deployment count
     */
    private function getDeploymentCount(): int
    {
        return Cache::get('deployment_count', 0);
    }

    /**
     * Get last deployment
     */
    private function getLastDeployment(): ?array
    {
        return Cache::get('last_deployment');
    }

    /**
     * Get deployment status
     */
    private function getDeploymentStatus(): string
    {
        return Cache::get('deployment_status', 'unknown');
    }

    /**
     * Get deployment time
     */
    private function getDeploymentTime(): array
    {
        $deploymentTimes = Cache::get('deployment_times', []);
        
        if (empty($deploymentTimes)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'deployment_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($deploymentTimes) / count($deploymentTimes), 2),
            'fastest_time' => min($deploymentTimes),
            'slowest_time' => max($deploymentTimes),
            'deployment_count' => count($deploymentTimes),
        ];
    }

    /**
     * Get deployment memory
     */
    private function getDeploymentMemory(): array
    {
        $deploymentMemory = Cache::get('deployment_memory', []);
        
        if (empty($deploymentMemory)) {
            return [
                'average_memory' => 0,
                'peak_memory' => 0,
                'deployment_count' => 0,
            ];
        }
        
        return [
            'average_memory' => round(array_sum($deploymentMemory) / count($deploymentMemory), 2),
            'peak_memory' => max($deploymentMemory),
            'deployment_count' => count($deploymentMemory),
        ];
    }

    /**
     * Get deployment errors
     */
    private function getDeploymentErrors(): array
    {
        return Cache::get('deployment_errors', []);
    }

    /**
     * Optimize deployment process
     */
    public function optimizeDeployment(): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            // Pre-deployment optimizations
            $this->preDeploymentOptimizations();
            
            // Run deployment commands
            $deploymentResult = $this->runDeploymentCommands();
            
            // Post-deployment optimizations
            $this->postDeploymentOptimizations();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = $endTime - $startTime;
            $memoryUsed = $endMemory - $startMemory;
            
            // Cache deployment results
            $this->cacheDeploymentResults($executionTime, $memoryUsed, $deploymentResult);
            
            return [
                'success' => true,
                'execution_time' => $executionTime,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'memory_used' => $memoryUsed,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'deployment_result' => $deploymentResult,
            ];
            
        } catch (\Exception $e) {
            Log::error("Deployment optimization failed: " . $e->getMessage());
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = $endTime - $startTime;
            $memoryUsed = $endMemory - $startMemory;
            
            // Cache deployment error
            $this->cacheDeploymentError($e, $executionTime, $memoryUsed);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
            ];
        }
    }

    /**
     * Pre-deployment optimizations
     */
    private function preDeploymentOptimizations(): void
    {
        try {
            // Clear application caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            // Optimize autoloader
            Artisan::call('optimize:clear');
            
            Log::info('Pre-deployment optimizations completed');
            
        } catch (\Exception $e) {
            Log::error("Pre-deployment optimizations failed: " . $e->getMessage());
        }
    }

    /**
     * Run deployment commands
     */
    private function runDeploymentCommands(): array
    {
        $commands = [
            'migrate' => ['--force' => true],
            'config:cache' => [],
            'route:cache' => [],
            'view:cache' => [],
            'optimize' => [],
        ];
        
        $results = [];
        
        foreach ($commands as $command => $options) {
            try {
                $result = Artisan::call($command, $options);
                $output = Artisan::output();
                
                $results[$command] = [
                    'success' => $result === 0,
                    'output' => $output,
                    'exit_code' => $result,
                ];
                
            } catch (\Exception $e) {
                $results[$command] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'exit_code' => 1,
                ];
            }
        }
        
        return $results;
    }

    /**
     * Post-deployment optimizations
     */
    private function postDeploymentOptimizations(): void
    {
        try {
            // Warm up caches
            Artisan::call('caches:warmup');
            
            // Optimize database
            Artisan::call('db:optimize');
            
            // Clear old logs
            $this->clearOldLogs();
            
            Log::info('Post-deployment optimizations completed');
            
        } catch (\Exception $e) {
            Log::error("Post-deployment optimizations failed: " . $e->getMessage());
        }
    }

    /**
     * Clear old logs
     */
    private function clearOldLogs(): void
    {
        try {
            $logDirectory = storage_path('logs');
            $files = glob($logDirectory . '/*.log');
            $cutoffTime = time() - (30 * 24 * 60 * 60); // 30 days
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to clear old logs: " . $e->getMessage());
        }
    }

    /**
     * Cache deployment results
     */
    private function cacheDeploymentResults(float $executionTime, int $memoryUsed, array $deploymentResult): void
    {
        try {
            // Update deployment count
            $deploymentCount = Cache::get('deployment_count', 0);
            Cache::put('deployment_count', $deploymentCount + 1, 86400); // 24 hours
            
            // Update last deployment
            $lastDeployment = [
                'timestamp' => now(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
                'success' => true,
            ];
            Cache::put('last_deployment', $lastDeployment, 86400);
            
            // Update deployment status
            Cache::put('deployment_status', 'success', 86400);
            
            // Cache execution time
            $deploymentTimes = Cache::get('deployment_times', []);
            $deploymentTimes[] = $executionTime;
            if (count($deploymentTimes) > 10) {
                $deploymentTimes = array_slice($deploymentTimes, -10);
            }
            Cache::put('deployment_times', $deploymentTimes, 86400);
            
            // Cache memory usage
            $deploymentMemory = Cache::get('deployment_memory', []);
            $deploymentMemory[] = $memoryUsed;
            if (count($deploymentMemory) > 10) {
                $deploymentMemory = array_slice($deploymentMemory, -10);
            }
            Cache::put('deployment_memory', $deploymentMemory, 86400);
            
        } catch (\Exception $e) {
            Log::error("Failed to cache deployment results: " . $e->getMessage());
        }
    }

    /**
     * Cache deployment error
     */
    private function cacheDeploymentError(\Exception $e, float $executionTime, int $memoryUsed): void
    {
        try {
            // Update deployment status
            Cache::put('deployment_status', 'error', 86400);
            
            // Cache error
            $deploymentErrors = Cache::get('deployment_errors', []);
            $deploymentErrors[] = [
                'timestamp' => now(),
                'error' => $e->getMessage(),
                'execution_time' => $executionTime,
                'memory_used' => $memoryUsed,
            ];
            
            // Keep only last 10 errors
            if (count($deploymentErrors) > 10) {
                $deploymentErrors = array_slice($deploymentErrors, -10);
            }
            
            Cache::put('deployment_errors', $deploymentErrors, 86400);
            
        } catch (\Exception $e) {
            Log::error("Failed to cache deployment error: " . $e->getMessage());
        }
    }

    /**
     * Get deployment recommendations
     */
    public function getDeploymentRecommendations(): array
    {
        $stats = $this->getDeploymentStats();
        $recommendations = [];
        
        // Deployment time recommendations
        if ($stats['deployment_time']['average_time'] > 300) { // 5 minutes
            $recommendations[] = 'Deployment time is slow. Consider optimizing deployment process';
        }
        
        // Deployment memory recommendations
        if ($stats['deployment_memory']['peak_memory'] > 200 * 1024 * 1024) { // 200MB
            $recommendations[] = 'Deployment memory usage is high. Consider optimizing memory usage';
        }
        
        // Deployment error recommendations
        if (count($stats['deployment_errors']) > 0) {
            $recommendations[] = 'Recent deployment errors detected. Check error logs and fix issues';
        }
        
        // Deployment frequency recommendations
        if ($stats['deployment_count'] > 10) {
            $recommendations[] = 'High deployment frequency. Consider implementing blue-green deployment';
        }
        
        return $recommendations;
    }

    /**
     * Rollback deployment
     */
    public function rollbackDeployment(): array
    {
        $startTime = microtime(true);
        
        try {
            // Rollback database migrations
            $migrationResult = Artisan::call('migrate:rollback', ['--force' => true]);
            
            // Clear caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            $endTime = microtime(true);
            $executionTime = $endTime - $startTime;
            
            // Update deployment status
            Cache::put('deployment_status', 'rolled_back', 86400);
            
            return [
                'success' => $migrationResult === 0,
                'execution_time' => $executionTime,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'migration_result' => $migrationResult,
            ];
            
        } catch (\Exception $e) {
            Log::error("Deployment rollback failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => 0,
            ];
        }
    }

    /**
     * Get deployment health
     */
    public function getDeploymentHealth(): array
    {
        $stats = $this->getDeploymentStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check deployment status
        if ($stats['deployment_status'] === 'error') {
            $health['status'] = 'unhealthy';
            $health['checks']['deployment_status'] = 'Deployment failed';
        } else {
            $health['checks']['deployment_status'] = 'OK';
        }
        
        // Check deployment time
        if ($stats['deployment_time']['average_time'] > 300) {
            $health['status'] = 'unhealthy';
            $health['checks']['deployment_time'] = 'Slow deployment time: ' . $stats['deployment_time']['average_time'] . 's';
        } else {
            $health['checks']['deployment_time'] = 'OK';
        }
        
        // Check deployment memory
        if ($stats['deployment_memory']['peak_memory'] > 200 * 1024 * 1024) {
            $health['status'] = 'unhealthy';
            $health['checks']['deployment_memory'] = 'High memory usage: ' . round($stats['deployment_memory']['peak_memory'] / 1024 / 1024, 2) . 'MB';
        } else {
            $health['checks']['deployment_memory'] = 'OK';
        }
        
        // Check deployment errors
        if (count($stats['deployment_errors']) > 0) {
            $health['status'] = 'unhealthy';
            $health['checks']['deployment_errors'] = 'Recent deployment errors: ' . count($stats['deployment_errors']);
        } else {
            $health['checks']['deployment_errors'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get deployment performance metrics
     */
    public function getDeploymentPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test deployment performance
        $this->preDeploymentOptimizations();
        $this->postDeploymentOptimizations();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'deployment_time' => $executionTime,
            'deployment_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'deployment_count' => $this->getDeploymentCount(),
            'deployment_status' => $this->getDeploymentStatus(),
        ];
    }
}

