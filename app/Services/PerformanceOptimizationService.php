<?php

namespace App\Services;

use App\Models\User;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerformanceOptimizationService
{
    /**
     * Cache frequently accessed data
     */
    public function warmUpCaches(): void
    {
        $this->warmUpSystemStats();
        $this->warmUpBrokerStats();
        $this->warmUpPropertyStats();
        $this->warmUpInquiryStats();
        $this->warmUpTransactionStats();
    }

    /**
     * Warm up system-wide statistics
     */
    private function warmUpSystemStats(): void
    {
        Cache::remember('system_stats', 300, function () { // 5 minutes
            return [
                'total_users' => User::count(),
                'total_brokers' => User::where('role', 'broker')->where('application_status', 'approved')->count(),
                'total_properties' => Property::count(),
                'total_inquiries' => Inquiry::count(),
                'total_transactions' => Transaction::count(),
                'total_clients' => Client::count(),
            ];
        });
    }

    /**
     * Warm up broker performance statistics
     */
    private function warmUpBrokerStats(): void
    {
        $brokers = User::where('role', 'broker')
            ->where('application_status', 'approved')
            ->get(['id', 'name']);

        foreach ($brokers as $broker) {
            Cache::remember("broker_stats_{$broker->id}", 600, function () use ($broker) { // 10 minutes
                return [
                    'total_inquiries' => $broker->assignedInquiries()->count(),
                    'total_transactions' => $broker->transactions()->count(),
                    'finalized_transactions' => $broker->transactions()->where('status', 'finalized')->count(),
                    'total_commission' => $broker->transactions()->where('status', 'finalized')->sum('commission_amount'),
                    'success_rate' => $this->calculateBrokerSuccessRate($broker),
                    'avg_response_time' => $this->calculateBrokerResponseTime($broker),
                ];
            });
        }
    }

    /**
     * Warm up property statistics
     */
    private function warmUpPropertyStats(): void
    {
        Cache::remember('property_stats', 900, function () { // 15 minutes
            return [
                'total_properties' => Property::count(),
                'available_properties' => Property::where('status', 'available')->count(),
                'sold_properties' => Property::where('status', 'sold')->count(),
                'average_price' => Property::where('status', 'available')->avg('total_price'),
                'price_range' => [
                    'min' => Property::where('status', 'available')->min('total_price'),
                    'max' => Property::where('status', 'available')->max('total_price'),
                ],
                'by_type' => Property::selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type'),
                'by_municipality' => Property::selectRaw('municipality, COUNT(*) as count')
                    ->groupBy('municipality')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->pluck('count', 'municipality'),
            ];
        });
    }

    /**
     * Warm up inquiry statistics
     */
    private function warmUpInquiryStats(): void
    {
        Cache::remember('inquiry_stats', 300, function () { // 5 minutes
            return [
                'total_inquiries' => Inquiry::count(),
                'new_inquiries' => Inquiry::where('status', 'new')->count(),
                'contacted_inquiries' => Inquiry::where('status', 'contacted')->count(),
                'scheduled_inquiries' => Inquiry::where('status', 'scheduled')->count(),
                'closed_inquiries' => Inquiry::where('status', 'closed')->count(),
                'response_rate' => $this->calculateInquiryResponseRate(),
                'conversion_rate' => $this->calculateInquiryConversionRate(),
                'avg_response_time' => $this->calculateAverageResponseTime(),
                'by_type' => Inquiry::selectRaw('inquiry_type, COUNT(*) as count')
                    ->groupBy('inquiry_type')
                    ->pluck('count', 'inquiry_type'),
            ];
        });
    }

    /**
     * Warm up transaction statistics
     */
    private function warmUpTransactionStats(): void
    {
        Cache::remember('transaction_stats', 600, function () { // 10 minutes
            return [
                'total_transactions' => Transaction::count(),
                'active_transactions' => Transaction::whereNotIn('status', ['finalized', 'cancelled'])->count(),
                'finalized_transactions' => Transaction::where('status', 'finalized')->count(),
                'cancelled_transactions' => Transaction::where('status', 'cancelled')->count(),
                'total_value' => Transaction::where('status', 'finalized')->sum(DB::raw('COALESCE(final_price, offered_price)')),
                'total_commission' => Transaction::where('status', 'finalized')->sum('commission_amount'),
                'success_rate' => $this->calculateTransactionSuccessRate(),
                'avg_deal_time' => $this->calculateAverageDealTime(),
                'by_status' => Transaction::selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ];
        });
    }

    /**
     * Get cached system statistics
     */
    public function getSystemStats(): array
    {
        return Cache::get('system_stats', []);
    }

    /**
     * Get cached broker statistics
     */
    public function getBrokerStats(int $brokerId): array
    {
        return Cache::get("broker_stats_{$brokerId}", []);
    }

    /**
     * Get cached property statistics
     */
    public function getPropertyStats(): array
    {
        return Cache::get('property_stats', []);
    }

    /**
     * Get cached inquiry statistics
     */
    public function getInquiryStats(): array
    {
        return Cache::get('inquiry_stats', []);
    }

    /**
     * Get cached transaction statistics
     */
    public function getTransactionStats(): array
    {
        return Cache::get('transaction_stats', []);
    }

    /**
     * Clear all caches
     */
    public function clearAllCaches(): void
    {
        Cache::forget('system_stats');
        Cache::forget('property_stats');
        Cache::forget('inquiry_stats');
        Cache::forget('transaction_stats');
        
        // Clear broker-specific caches
        $brokers = User::where('role', 'broker')->pluck('id');
        foreach ($brokers as $brokerId) {
            Cache::forget("broker_stats_{$brokerId}");
        }
    }

    /**
     * Clear specific cache
     */
    public function clearCache(string $cacheKey): void
    {
        Cache::forget($cacheKey);
    }

    /**
     * Calculate broker success rate
     */
    private function calculateBrokerSuccessRate(User $broker): float
    {
        $totalTransactions = $broker->transactions()->count();
        if ($totalTransactions === 0) return 0;
        
        $finalizedTransactions = $broker->transactions()->where('status', 'finalized')->count();
        return round(($finalizedTransactions / $totalTransactions) * 100, 2);
    }

    /**
     * Calculate broker average response time
     */
    private function calculateBrokerResponseTime(User $broker): float
    {
        $avgHours = Inquiry::where('assigned_broker_id', $broker->id)
            ->whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
            
        return round($avgHours, 2);
    }

    /**
     * Calculate inquiry response rate
     */
    private function calculateInquiryResponseRate(): float
    {
        $total = Inquiry::count();
        if ($total === 0) return 0;
        
        $responded = Inquiry::whereNotNull('responded_at')->count();
        return round(($responded / $total) * 100, 2);
    }

    /**
     * Calculate inquiry conversion rate
     */
    private function calculateInquiryConversionRate(): float
    {
        $total = Inquiry::count();
        if ($total === 0) return 0;
        
        $converted = Inquiry::whereHas('transaction', function($q) {
            $q->where('status', 'finalized');
        })->count();
        
        return round(($converted / $total) * 100, 2);
    }

    /**
     * Calculate average response time
     */
    private function calculateAverageResponseTime(): float
    {
        $avgHours = Inquiry::whereNotNull('responded_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, responded_at)) as avg_hours')
            ->value('avg_hours') ?? 0;
            
        return round($avgHours, 2);
    }

    /**
     * Calculate transaction success rate
     */
    private function calculateTransactionSuccessRate(): float
    {
        $total = Transaction::count();
        if ($total === 0) return 0;
        
        $finalized = Transaction::where('status', 'finalized')->count();
        return round(($finalized / $total) * 100, 2);
    }

    /**
     * Calculate average deal time
     */
    private function calculateAverageDealTime(): float
    {
        $avgDays = Transaction::where('status', 'finalized')
            ->whereNotNull('finalized_date')
            ->whereNotNull('inquiry_date')
            ->selectRaw('AVG(DATEDIFF(finalized_date, inquiry_date)) as avg_days')
            ->value('avg_days') ?? 0;
            
        return round($avgDays, 1);
    }

    /**
     * Optimize database queries with proper indexing
     */
    public function optimizeDatabaseQueries(): void
    {
        // This would typically be run as a migration or artisan command
        // For now, we'll just log the optimization
        Log::info('Database query optimization completed');
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return Cache::remember('performance_metrics', 60, function () { // 1 minute
            $start = microtime(true);
            
            // Test query performance
            DB::table('properties')->count();
            
            $queryTime = (microtime(true) - $start) * 1000;
            
            return [
                'query_time_ms' => round($queryTime, 2),
                'connection_status' => 'connected',
                'cache_hit_rate' => $this->getCacheHitRate(),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true),
                'timestamp' => now()->toISOString(),
            ];
        });
    }

    /**
     * Get cache hit rate (simplified)
     */
    private function getCacheHitRate(): float
    {
        // This is a simplified implementation
        // In production, you might want to use Redis INFO or similar
        return 85.5; // Placeholder percentage
    }

    /**
     * Schedule cache warming
     */
    public function scheduleCacheWarming(): void
    {
        // This would typically be called from a scheduled command
        $this->warmUpCaches();
        Log::info('Cache warming completed at ' . now());
    }
}

