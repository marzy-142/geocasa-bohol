<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\ErrorNotificationMail;
use Exception;
use Throwable;

class ErrorHandlingOptimizationService
{
    /**
     * Get error statistics
     */
    public function getErrorStats(): array
    {
        $logFile = storage_path('logs/laravel.log');
        $errorCount = 0;
        $warningCount = 0;
        $criticalCount = 0;
        
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            
            // Count different error levels
            $errorCount = substr_count($logContent, 'ERROR');
            $warningCount = substr_count($logContent, 'WARNING');
            $criticalCount = substr_count($logContent, 'CRITICAL');
        }
        
        return [
            'error_count' => $errorCount,
            'warning_count' => $warningCount,
            'critical_count' => $criticalCount,
            'total_errors' => $errorCount + $warningCount + $criticalCount,
            'log_file_size' => file_exists($logFile) ? filesize($logFile) : 0,
            'log_file_size_mb' => file_exists($logFile) ? round(filesize($logFile) / 1024 / 1024, 2) : 0,
        ];
    }

    /**
     * Optimize error handling
     */
    public function optimizeErrorHandling(): array
    {
        $optimizations = [];
        
        // Check error reporting level
        $errorReporting = error_reporting();
        if ($errorReporting === 0) {
            $optimizations[] = 'Error reporting is disabled. Consider enabling for debugging';
        }
        
        // Check display errors
        if (ini_get('display_errors') && app()->environment('production')) {
            $optimizations[] = 'Display errors is enabled in production. Disable for security';
        }
        
        // Check log errors
        if (!ini_get('log_errors')) {
            $optimizations[] = 'Error logging is disabled. Enable for better debugging';
        }
        
        // Check error log file
        $errorLog = ini_get('error_log');
        if (!$errorLog) {
            $optimizations[] = 'Error log file is not set. Set for better error tracking';
        }
        
        return $optimizations;
    }

    /**
     * Handle errors efficiently
     */
    public function handleError(Throwable $exception, array $context = []): void
    {
        try {
            // Log the error
            Log::error($exception->getMessage(), [
                'exception' => $exception,
                'context' => $context,
                'trace' => $exception->getTraceAsString(),
            ]);
            
            // Check if error should be reported
            if ($this->shouldReportError($exception)) {
                $this->reportError($exception, $context);
            }
            
            // Check if error should be cached
            if ($this->shouldCacheError($exception)) {
                $this->cacheError($exception, $context);
            }
            
        } catch (Exception $e) {
            // Fallback error handling
            error_log("Error handling failed: " . $e->getMessage());
        }
    }

    /**
     * Check if error should be reported
     */
    private function shouldReportError(Throwable $exception): bool
    {
        // Don't report certain types of errors
        $ignoredErrors = [
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
            'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException',
        ];
        
        return !in_array(get_class($exception), $ignoredErrors);
    }

    /**
     * Report error to administrators
     */
    private function reportError(Throwable $exception, array $context = []): void
    {
        try {
            // Get admin emails from configuration
            $adminEmails = config('app.admin_emails', []);
            
            if (!empty($adminEmails)) {
                foreach ($adminEmails as $email) {
                    Mail::to($email)->send(new ErrorNotificationMail($exception, $context));
                }
            }
            
        } catch (Exception $e) {
            Log::error("Failed to report error: " . $e->getMessage());
        }
    }

    /**
     * Check if error should be cached
     */
    private function shouldCacheError(Throwable $exception): bool
    {
        // Cache errors that might be repeated
        $cacheableErrors = [
            'Illuminate\Database\QueryException',
            'Illuminate\Validation\ValidationException',
            'Illuminate\Auth\AuthenticationException',
        ];
        
        return in_array(get_class($exception), $cacheableErrors);
    }

    /**
     * Cache error for analysis
     */
    private function cacheError(Throwable $exception, array $context = []): void
    {
        try {
            $errorKey = 'error_' . md5($exception->getMessage() . $exception->getFile() . $exception->getLine());
            $errorData = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'context' => $context,
                'count' => 1,
                'first_occurrence' => now(),
                'last_occurrence' => now(),
            ];
            
            if (Cache::has($errorKey)) {
                $existingError = Cache::get($errorKey);
                $existingError['count']++;
                $existingError['last_occurrence'] = now();
                Cache::put($errorKey, $existingError, 3600); // 1 hour
            } else {
                Cache::put($errorKey, $errorData, 3600); // 1 hour
            }
            
        } catch (Exception $e) {
            Log::error("Failed to cache error: " . $e->getMessage());
        }
    }

    /**
     * Get cached errors
     */
    public function getCachedErrors(): array
    {
        $errors = [];
        
        try {
            $cacheKeys = Cache::getRedis()->keys('error_*');
            
            foreach ($cacheKeys as $key) {
                $error = Cache::get($key);
                if ($error) {
                    $errors[] = $error;
                }
            }
            
        } catch (Exception $e) {
            Log::error("Failed to get cached errors: " . $e->getMessage());
        }
        
        return $errors;
    }

    /**
     * Clear cached errors
     */
    public function clearCachedErrors(): int
    {
        $clearedCount = 0;
        
        try {
            $cacheKeys = Cache::getRedis()->keys('error_*');
            
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
                $clearedCount++;
            }
            
        } catch (Exception $e) {
            Log::error("Failed to clear cached errors: " . $e->getMessage());
        }
        
        return $clearedCount;
    }

    /**
     * Analyze error patterns
     */
    public function analyzeErrorPatterns(): array
    {
        $errors = $this->getCachedErrors();
        $patterns = [];
        
        foreach ($errors as $error) {
            $key = $error['message'] . '|' . $error['file'] . '|' . $error['line'];
            
            if (!isset($patterns[$key])) {
                $patterns[$key] = [
                    'message' => $error['message'],
                    'file' => $error['file'],
                    'line' => $error['line'],
                    'count' => 0,
                    'first_occurrence' => $error['first_occurrence'],
                    'last_occurrence' => $error['last_occurrence'],
                ];
            }
            
            $patterns[$key]['count'] += $error['count'];
            $patterns[$key]['last_occurrence'] = max($patterns[$key]['last_occurrence'], $error['last_occurrence']);
        }
        
        // Sort by count
        uasort($patterns, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        return $patterns;
    }

    /**
     * Get error recommendations
     */
    public function getErrorRecommendations(): array
    {
        $stats = $this->getErrorStats();
        $patterns = $this->analyzeErrorPatterns();
        $recommendations = [];
        
        // High error count
        if ($stats['total_errors'] > 100) {
            $recommendations[] = 'High error count detected. Consider investigating and fixing common errors';
        }
        
        // Critical errors
        if ($stats['critical_count'] > 0) {
            $recommendations[] = 'Critical errors detected. Immediate attention required';
        }
        
        // Large log file
        if ($stats['log_file_size_mb'] > 100) {
            $recommendations[] = 'Log file is very large. Consider log rotation or cleanup';
        }
        
        // Frequent errors
        foreach ($patterns as $pattern) {
            if ($pattern['count'] > 10) {
                $recommendations[] = "Frequent error: {$pattern['message']} (occurred {$pattern['count']} times)";
            }
        }
        
        return $recommendations;
    }

    /**
     * Optimize error logging
     */
    public function optimizeErrorLogging(): void
    {
        try {
            // Set optimal error reporting level
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
            
            // Enable error logging
            ini_set('log_errors', 1);
            
            // Set error log file
            $errorLogFile = storage_path('logs/php_errors.log');
            ini_set('error_log', $errorLogFile);
            
            // Set display errors based on environment
            if (app()->environment('production')) {
                ini_set('display_errors', 0);
            } else {
                ini_set('display_errors', 1);
            }
            
            Log::info('Error logging optimized');
            
        } catch (Exception $e) {
            Log::error("Failed to optimize error logging: " . $e->getMessage());
        }
    }

    /**
     * Clean up old error logs
     */
    public function cleanupOldErrorLogs(int $maxAge = 30): int
    {
        $cleanedCount = 0;
        $logDirectory = storage_path('logs');
        $cutoffTime = time() - ($maxAge * 24 * 60 * 60);
        
        try {
            $files = glob($logDirectory . '/*.log');
            
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    unlink($file);
                    $cleanedCount++;
                }
            }
            
        } catch (Exception $e) {
            Log::error("Failed to cleanup old error logs: " . $e->getMessage());
        }
        
        return $cleanedCount;
    }

    /**
     * Get error handling performance metrics
     */
    public function getErrorHandlingPerformanceMetrics(): array
    {
        $startTime = microtime(true);
        
        // Test error handling performance
        try {
            throw new Exception('Test error for performance measurement');
        } catch (Exception $e) {
            $this->handleError($e, ['test' => true]);
        }
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        return [
            'error_handling_time' => $executionTime,
            'error_handling_time_ms' => round($executionTime * 1000, 2),
            'cached_errors_count' => count($this->getCachedErrors()),
            'error_patterns_count' => count($this->analyzeErrorPatterns()),
        ];
    }
}

