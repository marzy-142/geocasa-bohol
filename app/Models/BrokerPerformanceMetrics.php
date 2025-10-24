<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class BrokerPerformanceMetrics extends Model
{
    use HasFactory;

    protected $fillable = [
        'broker_id',
        'metric_date',
        'total_inquiries',
        'total_clients',
        'active_conversations',
        'avg_response_time_minutes',
        'conversion_rate',
        'client_satisfaction_score',
        'workload_score',
        'detailed_metrics',
    ];

    protected $casts = [
        'metric_date' => 'date',
        'avg_response_time_minutes' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'client_satisfaction_score' => 'decimal:2',
        'detailed_metrics' => 'array',
    ];

    /**
     * Get the broker that owns the metrics
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    /**
     * Scope for metrics by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    /**
     * Scope for recent metrics
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('metric_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific broker
     */
    public function scopeForBroker($query, int $brokerId)
    {
        return $query->where('broker_id', $brokerId);
    }

    /**
     * Get performance grade based on efficiency score
     */
    public function getPerformanceGradeAttribute(): string
    {
        $efficiency = $this->calculateEfficiencyScore();
        
        if ($efficiency >= 90) return 'A+';
        if ($efficiency >= 85) return 'A';
        if ($efficiency >= 80) return 'A-';
        if ($efficiency >= 75) return 'B+';
        if ($efficiency >= 70) return 'B';
        if ($efficiency >= 65) return 'B-';
        if ($efficiency >= 60) return 'C+';
        if ($efficiency >= 55) return 'C';
        if ($efficiency >= 50) return 'C-';
        return 'D';
    }

    /**
     * Get performance level description
     */
    public function getPerformanceLevelAttribute(): string
    {
        $efficiency = $this->calculateEfficiencyScore();
        
        if ($efficiency >= 85) return 'Excellent';
        if ($efficiency >= 70) return 'Good';
        if ($efficiency >= 55) return 'Satisfactory';
        return 'Needs Improvement';
    }

    /**
     * Calculate efficiency score based on multiple factors
     */
    public function calculateEfficiencyScore(): float
    {
        $responseScore = $this->calculateResponseScore();
        $conversionScore = $this->conversion_rate ?? 0;
        $workloadScore = $this->calculateWorkloadScore();
        $satisfactionScore = $this->client_satisfaction_score ?? 0;

        // Weighted scoring
        return round(
            ($responseScore * 0.3) +
            ($conversionScore * 0.3) +
            ($workloadScore * 0.2) +
            ($satisfactionScore * 0.2),
            2
        );
    }

    /**
     * Calculate response time score
     */
    protected function calculateResponseScore(): float
    {
        $avgResponseTime = $this->avg_response_time_minutes ?? 0;
        
        if ($avgResponseTime == 0) return 100;
        
        // Scale: 0-60 minutes = 100-80, 60-240 minutes = 80-60, 240+ minutes = 60-0
        if ($avgResponseTime <= 60) {
            return 100 - (($avgResponseTime / 60) * 20);
        } elseif ($avgResponseTime <= 240) {
            return 80 - ((($avgResponseTime - 60) / 180) * 20);
        } else {
            return max(0, 60 - ((($avgResponseTime - 240) / 240) * 60));
        }
    }

    /**
     * Calculate workload score
     */
    protected function calculateWorkloadScore(): float
    {
        $totalWorkload = $this->total_inquiries + ($this->total_clients * 0.5) + ($this->active_conversations * 0.1);
        
        // Lower workload = higher score
        if ($totalWorkload == 0) return 100;
        
        $score = max(0, 100 - ($totalWorkload * 5));
        return min(100, $score);
    }

    /**
     * Get workload level
     */
    public function getWorkloadLevelAttribute(): string
    {
        $totalWorkload = $this->total_inquiries + ($this->total_clients * 0.5) + ($this->active_conversations * 0.1);
        
        if ($totalWorkload <= 5) return 'Low';
        if ($totalWorkload <= 10) return 'Moderate';
        if ($totalWorkload <= 15) return 'High';
        return 'Very High';
    }

    /**
     * Get response time level
     */
    public function getResponseTimeLevelAttribute(): string
    {
        $avgResponseTime = $this->avg_response_time_minutes ?? 0;
        
        if ($avgResponseTime <= 60) return 'Excellent';
        if ($avgResponseTime <= 120) return 'Good';
        if ($avgResponseTime <= 240) return 'Satisfactory';
        return 'Needs Improvement';
    }

    /**
     * Get conversion rate level
     */
    public function getConversionRateLevelAttribute(): string
    {
        $conversionRate = $this->conversion_rate ?? 0;
        
        if ($conversionRate >= 25) return 'Excellent';
        if ($conversionRate >= 20) return 'Good';
        if ($conversionRate >= 15) return 'Satisfactory';
        return 'Needs Improvement';
    }

    /**
     * Get metrics summary
     */
    public function getSummaryAttribute(): array
    {
        return [
            'id' => $this->id,
            'broker_name' => $this->broker->name ?? 'Unknown',
            'metric_date' => $this->metric_date instanceof Carbon ? $this->metric_date->format('M j, Y') : $this->metric_date,
            'performance_grade' => $this->performance_grade,
            'performance_level' => $this->performance_level,
            'efficiency_score' => $this->calculateEfficiencyScore(),
            'workload_level' => $this->workload_level,
            'response_time_level' => $this->response_time_level,
            'conversion_rate_level' => $this->conversion_rate_level,
            'total_inquiries' => $this->total_inquiries,
            'total_clients' => $this->total_clients,
            'active_conversations' => $this->active_conversations,
            'avg_response_time_minutes' => $this->avg_response_time_minutes,
            'conversion_rate' => $this->conversion_rate,
            'client_satisfaction_score' => $this->client_satisfaction_score,
        ];
    }

    /**
     * Generate daily metrics for a broker
     */
    public static function generateDailyMetrics(int $brokerId, $date = null): self
    {
        $date = $date ? Carbon::parse($date) : now()->subDay();
        
        // Check if metrics already exist for this date
        $existing = self::where('broker_id', $brokerId)
            ->where('metric_date', $date->toDateString())
            ->first();

        if ($existing) {
            return $existing;
        }

        // Calculate metrics for the day
        $metrics = self::calculateDailyMetrics($brokerId, $date);

        return self::create([
            'broker_id' => $brokerId,
            'metric_date' => $date->toDateString(),
            'total_inquiries' => $metrics['total_inquiries'],
            'total_clients' => $metrics['total_clients'],
            'active_conversations' => $metrics['active_conversations'],
            'avg_response_time_minutes' => $metrics['avg_response_time_minutes'],
            'conversion_rate' => $metrics['conversion_rate'],
            'client_satisfaction_score' => $metrics['client_satisfaction_score'],
            'workload_score' => $metrics['workload_score'],
            'detailed_metrics' => $metrics['detailed_metrics'],
        ]);
    }

    /**
     * Calculate daily metrics for a broker
     */
    protected static function calculateDailyMetrics(int $brokerId, $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Total inquiries for the day
        $totalInquiries = Inquiry::where('assigned_broker_id', $brokerId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        // Total active clients
        $totalClients = Client::where('broker_id', $brokerId)
            ->where('status', 'active')
            ->whereBetween('relationship_established_at', [$startOfDay, $endOfDay])
            ->count();

        // Active conversations
        $activeConversations = Conversation::whereJsonContains('participants', $brokerId)
            ->where('last_message_at', '>=', $startOfDay)
            ->count();

        // Average response time
        $avgResponseTime = self::calculateDailyResponseTime($brokerId, $startOfDay, $endOfDay);

        // Conversion rate
        $conversionRate = self::calculateDailyConversionRate($brokerId, $startOfDay, $endOfDay);

        // Client satisfaction score (placeholder - would come from feedback system)
        $clientSatisfactionScore = self::calculateClientSatisfactionScore($brokerId, $date);

        // Workload score
        $workloadScore = self::calculateStaticWorkloadScore($totalInquiries, $totalClients, $activeConversations);

        return [
            'total_inquiries' => $totalInquiries,
            'total_clients' => $totalClients,
            'active_conversations' => $activeConversations,
            'avg_response_time_minutes' => $avgResponseTime,
            'conversion_rate' => $conversionRate,
            'client_satisfaction_score' => $clientSatisfactionScore,
            'workload_score' => $workloadScore,
            'detailed_metrics' => [
                'inquiries_by_status' => self::getInquiriesByStatus($brokerId, $startOfDay, $endOfDay),
                'transactions_by_status' => self::getTransactionsByStatus($brokerId, $startOfDay, $endOfDay),
                'response_times_by_hour' => self::getResponseTimesByHour($brokerId, $startOfDay, $endOfDay),
            ],
        ];
    }

    /**
     * Calculate daily response time
     */
    protected static function calculateDailyResponseTime(int $brokerId, $startOfDay, $endOfDay): float
    {
        // This would be calculated based on message timestamps
        // For now, return a placeholder
        return 120.0; // 2 hours average
    }

    /**
     * Calculate daily conversion rate
     */
    protected static function calculateDailyConversionRate(int $brokerId, $startOfDay, $endOfDay): float
    {
        $totalInquiries = Inquiry::where('assigned_broker_id', $brokerId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        $convertedInquiries = Inquiry::where('assigned_broker_id', $brokerId)
            ->where('status', 'closed')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->count();

        return $totalInquiries > 0 ? round(($convertedInquiries / $totalInquiries) * 100, 2) : 0;
    }

    /**
     * Calculate client satisfaction score
     */
    protected static function calculateClientSatisfactionScore(int $brokerId, $date): float
    {
        // This would typically come from client feedback or rating system
        // For now, return a placeholder based on performance indicators
        return 85.0; // Placeholder score
    }

    /**
     * Calculate workload score (static version)
     */
    protected static function calculateStaticWorkloadScore(int $inquiries, int $clients, int $conversations): float
    {
        $totalWorkload = $inquiries + ($clients * 0.5) + ($conversations * 0.1);
        return max(0, 100 - ($totalWorkload * 5));
    }

    /**
     * Get inquiries by status
     */
    protected static function getInquiriesByStatus(int $brokerId, $startOfDay, $endOfDay): array
    {
        return Inquiry::where('assigned_broker_id', $brokerId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get transactions by status
     */
    protected static function getTransactionsByStatus(int $brokerId, $startOfDay, $endOfDay): array
    {
        return Transaction::where('broker_id', $brokerId)
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get response times by hour
     */
    protected static function getResponseTimesByHour(int $brokerId, $startOfDay, $endOfDay): array
    {
        // This would be calculated based on actual message timestamps
        // For now, return placeholder data
        return [];
    }

    /**
     * Get broker performance trends
     */
    public static function getBrokerPerformanceTrends(int $brokerId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $metrics = self::where('broker_id', $brokerId)
            ->where('metric_date', '>=', $startDate)
            ->orderBy('metric_date')
            ->get();

        return $metrics->map(function ($metric) {
            return [
                'date' => $metric->metric_date->format('Y-m-d'),
                'efficiency_score' => $metric->calculateEfficiencyScore(),
                'conversion_rate' => $metric->conversion_rate,
                'avg_response_time' => $metric->avg_response_time_minutes,
                'workload_score' => $metric->workload_score,
                'client_satisfaction' => $metric->client_satisfaction_score,
            ];
        })->toArray();
    }

    /**
     * Get broker performance comparison
     */
    public static function getBrokerPerformanceComparison(int $brokerId, int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $brokerMetrics = self::where('broker_id', $brokerId)
            ->where('metric_date', '>=', $startDate)
            ->get();

        $allBrokerMetrics = self::where('metric_date', '>=', $startDate)
            ->get();

        return [
            'broker_averages' => [
                'efficiency_score' => $brokerMetrics->avg('efficiency_score'),
                'conversion_rate' => $brokerMetrics->avg('conversion_rate'),
                'avg_response_time' => $brokerMetrics->avg('avg_response_time_minutes'),
                'workload_score' => $brokerMetrics->avg('workload_score'),
            ],
            'peer_averages' => [
                'efficiency_score' => $allBrokerMetrics->avg('efficiency_score'),
                'conversion_rate' => $allBrokerMetrics->avg('conversion_rate'),
                'avg_response_time' => $allBrokerMetrics->avg('avg_response_time_minutes'),
                'workload_score' => $allBrokerMetrics->avg('workload_score'),
            ],
        ];
    }
}

