<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ValidationOptimizationService
{
    /**
     * Get validation statistics
     */
    public function getValidationStats(): array
    {
        return [
            'total_validations' => $this->getTotalValidations(),
            'successful_validations' => $this->getSuccessfulValidations(),
            'failed_validations' => $this->getFailedValidations(),
            'validation_rules' => $this->getValidationRules(),
            'validation_performance' => $this->getValidationPerformance(),
        ];
    }

    /**
     * Get total validations
     */
    private function getTotalValidations(): int
    {
        return Cache::get('total_validations', 0);
    }

    /**
     * Get successful validations
     */
    private function getSuccessfulValidations(): int
    {
        return Cache::get('successful_validations', 0);
    }

    /**
     * Get failed validations
     */
    private function getFailedValidations(): int
    {
        return Cache::get('failed_validations', 0);
    }

    /**
     * Get validation rules
     */
    private function getValidationRules(): array
    {
        return Cache::get('validation_rules', []);
    }

    /**
     * Get validation performance
     */
    private function getValidationPerformance(): array
    {
        $performance = Cache::get('validation_performance', []);
        
        if (empty($performance)) {
            return [
                'average_time' => 0,
                'fastest_time' => 0,
                'slowest_time' => 0,
                'validation_count' => 0,
            ];
        }
        
        return [
            'average_time' => round(array_sum($performance) / count($performance), 2),
            'fastest_time' => min($performance),
            'slowest_time' => max($performance),
            'validation_count' => count($performance),
        ];
    }

    /**
     * Optimize validation
     */
    public function optimizeValidation(): array
    {
        $optimizations = [];
        
        // Optimize validation rules
        $optimizations['rules_optimization'] = $this->optimizeValidationRules();
        
        // Optimize validation performance
        $optimizations['performance_optimization'] = $this->optimizeValidationPerformance();
        
        // Optimize validation caching
        $optimizations['caching_optimization'] = $this->optimizeValidationCaching();
        
        // Optimize validation messages
        $optimizations['messages_optimization'] = $this->optimizeValidationMessages();
        
        return $optimizations;
    }

    /**
     * Optimize validation rules
     */
    private function optimizeValidationRules(): array
    {
        try {
            $rules = $this->getValidationRules();
            $optimizations = [];
            
            foreach ($rules as $rule) {
                // Check for redundant rules
                if (in_array('required', $rule['rules']) && in_array('nullable', $rule['rules'])) {
                    $optimizations[] = "Rule '{$rule['field']}' has conflicting required/nullable rules";
                }
                
                // Check for complex rules
                if (count($rule['rules']) > 10) {
                    $optimizations[] = "Rule '{$rule['field']}' is complex. Consider simplifying";
                }
                
                // Check for expensive rules
                $expensiveRules = ['unique', 'exists', 'regex'];
                $hasExpensiveRules = array_intersect($expensiveRules, $rule['rules']);
                if (!empty($hasExpensiveRules)) {
                    $optimizations[] = "Rule '{$rule['field']}' has expensive rules. Consider optimizing";
                }
            }
            
            return [
                'success' => true,
                'rules' => $rules,
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
     * Optimize validation performance
     */
    private function optimizeValidationPerformance(): array
    {
        try {
            $performance = $this->getValidationPerformance();
            $optimizations = [];
            
            // Check average validation time
            if ($performance['average_time'] > 100) { // 100ms
                $optimizations[] = 'Average validation time is slow. Consider optimizing rules';
            }
            
            // Check slowest validation time
            if ($performance['slowest_time'] > 1000) { // 1s
                $optimizations[] = 'Some validations are very slow. Consider investigating';
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
     * Optimize validation caching
     */
    private function optimizeValidationCaching(): array
    {
        try {
            $cacheEnabled = config('validation.cache_enabled', false);
            $cacheTtl = config('validation.cache_ttl', 3600);
            
            $optimizations = [];
            
            // Check if caching is enabled
            if (!$cacheEnabled) {
                $optimizations[] = 'Validation caching is disabled. Consider enabling for better performance';
            }
            
            // Check cache TTL
            if ($cacheTtl < 300) { // 5 minutes
                $optimizations[] = 'Validation cache TTL is short. Consider increasing for better performance';
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
     * Optimize validation messages
     */
    private function optimizeValidationMessages(): array
    {
        try {
            $messages = config('validation.messages', []);
            $optimizations = [];
            
            // Check for missing messages
            $requiredMessages = [
                'required',
                'email',
                'min',
                'max',
                'numeric',
                'string',
                'array',
            ];
            
            foreach ($requiredMessages as $message) {
                if (!isset($messages[$message])) {
                    $optimizations[] = "Missing validation message for rule: {$message}";
                }
            }
            
            // Check for long messages
            foreach ($messages as $rule => $message) {
                if (strlen($message) > 200) {
                    $optimizations[] = "Validation message for '{$rule}' is long. Consider shortening";
                }
            }
            
            return [
                'success' => true,
                'messages' => $messages,
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
     * Get validation recommendations
     */
    public function getValidationRecommendations(): array
    {
        $stats = $this->getValidationStats();
        $recommendations = [];
        
        // Validation volume recommendations
        if ($stats['total_validations'] > 10000) {
            $recommendations[] = 'High validation volume. Consider implementing caching';
        }
        
        // Failure rate recommendations
        $totalValidations = $stats['total_validations'];
        if ($totalValidations > 0) {
            $failureRate = ($stats['failed_validations'] / $totalValidations) * 100;
            if ($failureRate > 20) {
                $recommendations[] = 'High validation failure rate. Consider improving validation rules';
            }
        }
        
        // Performance recommendations
        $performance = $stats['validation_performance'];
        if ($performance['average_time'] > 100) {
            $recommendations[] = 'Slow validation performance. Consider optimizing rules';
        }
        
        return $recommendations;
    }

    /**
     * Get validation health
     */
    public function getValidationHealth(): array
    {
        $stats = $this->getValidationStats();
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => now(),
        ];
        
        // Check failure rate
        $totalValidations = $stats['total_validations'];
        if ($totalValidations > 0) {
            $failureRate = ($stats['failed_validations'] / $totalValidations) * 100;
            if ($failureRate > 20) {
                $health['status'] = 'unhealthy';
                $health['checks']['failure_rate'] = 'High failure rate: ' . round($failureRate, 2) . '%';
            } else {
                $health['checks']['failure_rate'] = 'OK';
            }
        } else {
            $health['checks']['failure_rate'] = 'OK';
        }
        
        // Check performance
        $performance = $stats['validation_performance'];
        if ($performance['average_time'] > 100) {
            $health['status'] = 'unhealthy';
            $health['checks']['performance'] = 'Slow validation: ' . $performance['average_time'] . 'ms';
        } else {
            $health['checks']['performance'] = 'OK';
        }
        
        return $health;
    }

    /**
     * Get validation performance metrics
     */
    public function getValidationPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test validation performance
        $this->testValidationPerformance();
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'validation_time' => $executionTime,
            'validation_time_ms' => round($executionTime * 1000, 2),
            'memory_usage' => memory_get_usage(true),
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'total_validations' => $this->getTotalValidations(),
            'successful_validations' => $this->getSuccessfulValidations(),
        ];
    }

    /**
     * Test validation performance
     */
    private function testValidationPerformance(): void
    {
        try {
            // Test validation with sample data
            $data = [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'age' => 25,
            ];
            
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'age' => 'required|numeric|min:18|max:100',
            ];
            
            $validator = Validator::make($data, $rules);
            $validator->validate();
            
        } catch (\Exception $e) {
            Log::error("Validation test failed: " . $e->getMessage());
        }
    }
}

