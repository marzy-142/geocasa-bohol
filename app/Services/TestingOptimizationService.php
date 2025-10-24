<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class TestingOptimizationService
{
    /**
     * Get testing statistics
     */
    public function getTestingStats(): array
    {
        return [
            'total_tests' => $this->getTotalTestsCount(),
            'passed_tests' => $this->getPassedTestsCount(),
            'failed_tests' => $this->getFailedTestsCount(),
            'skipped_tests' => $this->getSkippedTestsCount(),
            'test_coverage' => $this->getTestCoverage(),
            'test_execution_time' => $this->getTestExecutionTime(),
            'test_memory_usage' => $this->getTestMemoryUsage(),
        ];
    }

    /**
     * Get total tests count
     */
    private function getTotalTestsCount(): int
    {
        try {
            $result = Artisan::call('test', ['--list-tests' => true]);
            $output = Artisan::output();
            return substr_count($output, '✓') + substr_count($output, '✗');
        } catch (\Exception $e) {
            Log::error("Failed to get total tests count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get passed tests count
     */
    private function getPassedTestsCount(): int
    {
        try {
            $result = Artisan::call('test', ['--list-tests' => true]);
            $output = Artisan::output();
            return substr_count($output, '✓');
        } catch (\Exception $e) {
            Log::error("Failed to get passed tests count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get failed tests count
     */
    private function getFailedTestsCount(): int
    {
        try {
            $result = Artisan::call('test', ['--list-tests' => true]);
            $output = Artisan::output();
            return substr_count($output, '✗');
        } catch (\Exception $e) {
            Log::error("Failed to get failed tests count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get skipped tests count
     */
    private function getSkippedTestsCount(): int
    {
        try {
            $result = Artisan::call('test', ['--list-tests' => true]);
            $output = Artisan::output();
            return substr_count($output, 'S');
        } catch (\Exception $e) {
            Log::error("Failed to get skipped tests count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get test coverage
     */
    private function getTestCoverage(): array
    {
        try {
            $coverageFile = base_path('coverage/coverage-final.json');
            
            if (!file_exists($coverageFile)) {
                return [
                    'coverage_percentage' => 0,
                    'covered_lines' => 0,
                    'total_lines' => 0,
                    'status' => 'no_coverage_data',
                ];
            }
            
            $coverageData = json_decode(file_get_contents($coverageFile), true);
            $totalLines = 0;
            $coveredLines = 0;
            
            foreach ($coverageData as $file => $data) {
                if (isset($data['s'])) {
                    $totalLines += count($data['s']);
                    $coveredLines += count(array_filter($data['s'], function($line) {
                        return $line > 0;
                    }));
                }
            }
            
            $coveragePercentage = $totalLines > 0 ? round(($coveredLines / $totalLines) * 100, 2) : 0;
            
            return [
                'coverage_percentage' => $coveragePercentage,
                'covered_lines' => $coveredLines,
                'total_lines' => $totalLines,
                'status' => 'coverage_available',
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get test coverage: " . $e->getMessage());
            return [
                'coverage_percentage' => 0,
                'covered_lines' => 0,
                'total_lines' => 0,
                'status' => 'error',
            ];
        }
    }

    /**
     * Get test execution time
     */
    private function getTestExecutionTime(): array
    {
        try {
            $testResults = Cache::get('test_execution_time', []);
            
            if (empty($testResults)) {
                return [
                    'last_execution' => null,
                    'average_time' => 0,
                    'fastest_time' => 0,
                    'slowest_time' => 0,
                    'execution_count' => 0,
                ];
            }
            
            return [
                'last_execution' => $testResults['last_execution'] ?? null,
                'average_time' => round(array_sum($testResults['times']) / count($testResults['times']), 2),
                'fastest_time' => min($testResults['times']),
                'slowest_time' => max($testResults['times']),
                'execution_count' => count($testResults['times']),
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get test execution time: " . $e->getMessage());
            return [
                'last_execution' => null,
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'execution_count' => 0,
            ];
        }
    }

    /**
     * Get test memory usage
     */
    private function getTestMemoryUsage(): array
    {
        try {
            $memoryUsage = Cache::get('test_memory_usage', []);
            
            if (empty($memoryUsage)) {
                return [
                    'average_memory' => 0,
                    'peak_memory' => 0,
                    'memory_limit' => $this->getMemoryLimit(),
                ];
            }
            
            return [
                'average_memory' => round(array_sum($memoryUsage) / count($memoryUsage), 2),
                'peak_memory' => max($memoryUsage),
                'memory_limit' => $this->getMemoryLimit(),
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to get test memory usage: " . $e->getMessage());
            return [
                'average_memory' => 0,
                'peak_memory' => 0,
                'memory_limit' => $this->getMemoryLimit(),
            ];
        }
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
     * Run tests with optimization
     */
    public function runOptimizedTests(array $options = []): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            // Prepare test command
            $command = ['test'];
            
            if (isset($options['coverage']) && $options['coverage']) {
                $command[] = '--coverage';
            }
            
            if (isset($options['parallel']) && $options['parallel']) {
                $command[] = '--parallel';
            }
            
            if (isset($options['filter']) && $options['filter']) {
                $command[] = '--filter=' . $options['filter'];
            }
            
            if (isset($options['group']) && $options['group']) {
                $command[] = '--group=' . $options['group'];
            }
            
            // Run tests
            $result = Artisan::call($command[0], array_slice($command, 1));
            $output = Artisan::output();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            
            $executionTime = $endTime - $startTime;
            $memoryUsed = $endMemory - $startMemory;
            
            // Cache results
            $this->cacheTestResults($executionTime, $memoryUsed);
            
            return [
                'success' => $result === 0,
                'execution_time' => $executionTime,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'memory_used' => $memoryUsed,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'output' => $output,
                'exit_code' => $result,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to run optimized tests: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => 0,
                'memory_used' => 0,
            ];
        }
    }

    /**
     * Cache test results
     */
    private function cacheTestResults(float $executionTime, int $memoryUsed): void
    {
        try {
            // Cache execution time
            $testResults = Cache::get('test_execution_time', []);
            $testResults['times'][] = $executionTime;
            $testResults['last_execution'] = now();
            
            // Keep only last 10 executions
            if (count($testResults['times']) > 10) {
                $testResults['times'] = array_slice($testResults['times'], -10);
            }
            
            Cache::put('test_execution_time', $testResults, 3600); // 1 hour
            
            // Cache memory usage
            $memoryUsage = Cache::get('test_memory_usage', []);
            $memoryUsage[] = $memoryUsed;
            
            // Keep only last 10 executions
            if (count($memoryUsage) > 10) {
                $memoryUsage = array_slice($memoryUsage, -10);
            }
            
            Cache::put('test_memory_usage', $memoryUsage, 3600); // 1 hour
            
        } catch (\Exception $e) {
            Log::error("Failed to cache test results: " . $e->getMessage());
        }
    }

    /**
     * Optimize test database
     */
    public function optimizeTestDatabase(): void
    {
        try {
            // Run database migrations for testing
            Artisan::call('migrate', ['--env' => 'testing']);
            
            // Seed test database
            Artisan::call('db:seed', ['--env' => 'testing']);
            
            Log::info('Test database optimized');
            
        } catch (\Exception $e) {
            Log::error("Failed to optimize test database: " . $e->getMessage());
        }
    }

    /**
     * Clean up test artifacts
     */
    public function cleanupTestArtifacts(): void
    {
        try {
            // Clean up test database
            Artisan::call('migrate:fresh', ['--env' => 'testing']);
            
            // Clear test caches
            Cache::flush();
            
            // Clean up test files
            $testFiles = glob(storage_path('app/testing/*'));
            foreach ($testFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            
            Log::info('Test artifacts cleaned up');
            
        } catch (\Exception $e) {
            Log::error("Failed to cleanup test artifacts: " . $e->getMessage());
        }
    }

    /**
     * Get test recommendations
     */
    public function getTestRecommendations(): array
    {
        $stats = $this->getTestingStats();
        $recommendations = [];
        
        // Test coverage recommendations
        if ($stats['test_coverage']['coverage_percentage'] < 80) {
            $recommendations[] = 'Test coverage is low. Consider adding more tests to improve coverage';
        }
        
        // Test execution time recommendations
        if ($stats['test_execution_time']['average_time'] > 60) {
            $recommendations[] = 'Test execution time is slow. Consider optimizing tests or using parallel execution';
        }
        
        // Test memory usage recommendations
        if ($stats['test_memory_usage']['peak_memory'] > 100 * 1024 * 1024) { // 100MB
            $recommendations[] = 'Test memory usage is high. Consider optimizing test data or memory usage';
        }
        
        // Failed tests recommendations
        if ($stats['failed_tests'] > 0) {
            $recommendations[] = 'Some tests are failing. Fix failing tests before proceeding';
        }
        
        // Skipped tests recommendations
        if ($stats['skipped_tests'] > 0) {
            $recommendations[] = 'Some tests are skipped. Consider enabling or removing skipped tests';
        }
        
        return $recommendations;
    }

    /**
     * Generate test report
     */
    public function generateTestReport(): array
    {
        $stats = $this->getTestingStats();
        $recommendations = $this->getTestRecommendations();
        
        return [
            'summary' => [
                'total_tests' => $stats['total_tests'],
                'passed_tests' => $stats['passed_tests'],
                'failed_tests' => $stats['failed_tests'],
                'skipped_tests' => $stats['skipped_tests'],
                'success_rate' => $stats['total_tests'] > 0 ? round(($stats['passed_tests'] / $stats['total_tests']) * 100, 2) : 0,
            ],
            'coverage' => $stats['test_coverage'],
            'performance' => [
                'execution_time' => $stats['test_execution_time'],
                'memory_usage' => $stats['test_memory_usage'],
            ],
            'recommendations' => $recommendations,
            'generated_at' => now(),
        ];
    }

    /**
     * Run specific test groups
     */
    public function runTestGroups(array $groups): array
    {
        $results = [];
        
        foreach ($groups as $group) {
            $result = $this->runOptimizedTests(['group' => $group]);
            $results[$group] = $result;
        }
        
        return $results;
    }

    /**
     * Get test performance metrics
     */
    public function getTestPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test database performance
        $dbStartTime = microtime(true);
        DB::table('users')->count();
        $dbTime = microtime(true) - $dbStartTime;
        
        // Test cache performance
        $cacheStartTime = microtime(true);
        Cache::put('test_performance', 'test_value', 60);
        Cache::get('test_performance');
        Cache::forget('test_performance');
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
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ];
    }
}

