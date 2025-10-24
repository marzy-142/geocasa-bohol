<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Property;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\Transaction;

class MonitoringService
{
    /**
     * Get application metrics
     */
    public function getMetrics(): array
    {
        return Cache::remember('app_metrics', 300, function () {
            return [
                'users' => $this->getUserMetrics(),
                'properties' => $this->getPropertyMetrics(),
                'inquiries' => $this->getInquiryMetrics(),
                'transactions' => $this->getTransactionMetrics(),
                'system' => $this->getSystemMetrics(),
                'performance' => $this->getPerformanceMetrics(),
            ];
        });
    }

    /**
     * Get user-related metrics
     */
    protected function getUserMetrics(): array
    {
        try {
            return [
                'total_users' => User::count(),
                'active_users_today' => User::whereDate('created_at', today())->count(), // Using created_at as proxy for activity
                'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
                'brokers_pending_approval' => User::where('role', 'broker')
                    ->where('broker_approved', false)
                    ->count(),
                'approved_brokers' => User::where('role', 'broker')
                    ->where('broker_approved', true)
                    ->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get user metrics', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to fetch user metrics'];
        }
    }

    /**
     * Get property-related metrics
     */
    protected function getPropertyMetrics(): array
    {
        try {
            return [
                'total_properties' => Property::count(),
                'active_properties' => Property::where('status', 'active')->count(),
                'sold_properties' => Property::where('status', 'sold')->count(),
                'properties_with_virtual_tours' => Property::where('has_virtual_tour', true)->count(),
                'new_properties_this_week' => Property::where('created_at', '>=', now()->subWeek())->count(),
                'average_property_price' => Property::where('status', 'active')->avg('price'),
                'properties_by_type' => Property::select('type')
                    ->selectRaw('count(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get property metrics', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to fetch property metrics'];
        }
    }

    /**
     * Get inquiry-related metrics
     */
    protected function getInquiryMetrics(): array
    {
        try {
            return [
                'total_inquiries' => Inquiry::count(),
                'new_inquiries_today' => Inquiry::whereDate('created_at', today())->count(),
                'pending_inquiries' => Inquiry::where('status', 'pending')->count(),
                'responded_inquiries' => Inquiry::where('status', 'responded')->count(),
                'inquiries_this_week' => Inquiry::where('created_at', '>=', now()->subWeek())->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get inquiry metrics', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to fetch inquiry metrics'];
        }
    }

    /**
     * Get transaction-related metrics
     */
    protected function getTransactionMetrics(): array
    {
        try {
            return [
                'total_transactions' => Transaction::count(),
                'completed_transactions' => Transaction::where('status', 'completed')->count(),
                'pending_transactions' => Transaction::where('status', 'pending')->count(),
                'transactions_this_month' => Transaction::where('created_at', '>=', now()->subMonth())->count(),
                'total_transaction_value' => Transaction::where('status', 'completed')->sum('amount'),
                'average_transaction_value' => Transaction::where('status', 'completed')->avg('amount'),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get transaction metrics', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to fetch transaction metrics'];
        }
    }

    /**
     * Get system-related metrics
     */
    protected function getSystemMetrics(): array
    {
        try {
            return [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'memory_usage' => $this->formatBytes(memory_get_usage(true)),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
                'disk_usage' => $this->getDiskUsage(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get system metrics', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to fetch system metrics'];
        }
    }

    /**
     * Get performance-related metrics
     */
    protected function getPerformanceMetrics(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $dbResponseTime = round((microtime(true) - $start) * 1000, 2);

            $start = microtime(true);
            Cache::get('test_key');
            $cacheResponseTime = round((microtime(true) - $start) * 1000, 2);

            return [
                'database_response_time_ms' => $dbResponseTime,
                'cache_response_time_ms' => $cacheResponseTime,
                'average_response_time_ms' => $this->getAverageResponseTime(),
                'slow_queries_count' => $this->getSlowQueriesCount(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics', ['error' => $e->getMessage()]);
            return ['error' => 'Unable to fetch performance metrics'];
        }
    }

    /**
     * Get disk usage information
     */
    protected function getDiskUsage(): array
    {
        try {
            $storagePath = storage_path();
            $totalBytes = disk_total_space($storagePath);
            $freeBytes = disk_free_space($storagePath);
            $usedBytes = $totalBytes - $freeBytes;

            return [
                'total' => $this->formatBytes($totalBytes),
                'used' => $this->formatBytes($usedBytes),
                'free' => $this->formatBytes($freeBytes),
                'usage_percentage' => round(($usedBytes / $totalBytes) * 100, 2),
            ];
        } catch (\Exception $e) {
            return ['error' => 'Unable to get disk usage'];
        }
    }

    /**
     * Get average response time from logs (simplified implementation)
     */
    protected function getAverageResponseTime(): float
    {
        // This is a simplified implementation
        // In a real application, you might want to store response times in a dedicated table
        return 0.0;
    }

    /**
     * Get count of slow queries (simplified implementation)
     */
    protected function getSlowQueriesCount(): int
    {
        // This is a simplified implementation
        // In a real application, you might want to enable MySQL slow query log
        return 0;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Log critical metrics for monitoring
     */
    public function logCriticalMetrics(): void
    {
        try {
            $metrics = $this->getMetrics();
            
            // Log critical thresholds
            if (isset($metrics['system']['memory_usage'])) {
                $memoryUsage = memory_get_usage(true);
                $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
                
                if ($memoryUsage > ($memoryLimit * 0.8)) {
                    Log::warning('High memory usage detected', [
                        'current' => $this->formatBytes($memoryUsage),
                        'limit' => $this->formatBytes($memoryLimit),
                        'percentage' => round(($memoryUsage / $memoryLimit) * 100, 2),
                    ]);
                }
            }
            
            // Log disk usage warnings
            if (isset($metrics['system']['disk_usage']['usage_percentage'])) {
                $diskUsage = $metrics['system']['disk_usage']['usage_percentage'];
                
                if ($diskUsage > 80) {
                    Log::warning('High disk usage detected', [
                        'usage_percentage' => $diskUsage,
                        'disk_info' => $metrics['system']['disk_usage'],
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to log critical metrics', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Parse bytes from PHP ini format (e.g., '128M', '1G')
     */
    protected function parseBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}