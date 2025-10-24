<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\User;
use App\Models\Property;
use App\Enums\InquiryStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InquiryMonitoringService
{
    /**
     * Log inquiry creation event
     */
    public function logInquiryCreated(Inquiry $inquiry, array $metadata = []): void
    {
        $logData = [
            'event' => 'inquiry_created',
            'inquiry_id' => $inquiry->id,
            'property_id' => $inquiry->property_id,
            'client_id' => $inquiry->client_id,
            'status' => $inquiry->status->value,
            'email' => $inquiry->email,
            'ip_address' => $inquiry->ip_address,
            'user_agent' => $inquiry->user_agent,
            'created_at' => $inquiry->created_at->toISOString(),
            'metadata' => $metadata
        ];

        Log::channel('inquiry')->info('Inquiry created', $logData);
        
        // Update real-time metrics
        $this->updateInquiryMetrics('created');
    }

    /**
     * Log inquiry status change
     */
    public function logStatusChange(Inquiry $inquiry, InquiryStatus $oldStatus, InquiryStatus $newStatus, ?User $updatedBy = null): void
    {
        $logData = [
            'event' => 'inquiry_status_changed',
            'inquiry_id' => $inquiry->id,
            'property_id' => $inquiry->property_id,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'updated_by' => $updatedBy?->id,
            'updated_by_role' => $updatedBy?->role,
            'updated_at' => now()->toISOString(),
            'time_in_previous_status' => $this->calculateTimeInStatus($inquiry, $oldStatus)
        ];

        Log::channel('inquiry')->info('Inquiry status changed', $logData);
        
        // Update status-specific metrics
        $this->updateStatusMetrics($oldStatus, $newStatus);
    }

    /**
     * Log broker assignment
     */
    public function logBrokerAssignment(Inquiry $inquiry, ?User $broker, array $assignmentData = []): void
    {
        $logData = [
            'event' => 'broker_assigned',
            'inquiry_id' => $inquiry->id,
            'property_id' => $inquiry->property_id,
            'broker_id' => $broker?->id,
            'broker_name' => $broker?->name,
            'assignment_method' => $assignmentData['method'] ?? 'auto',
            'broker_workload' => $assignmentData['workload'] ?? null,
            'broker_score' => $assignmentData['score'] ?? null,
            'assigned_at' => now()->toISOString()
        ];

        Log::channel('inquiry')->info('Broker assigned to inquiry', $logData);
        
        // Update broker metrics
        if ($broker) {
            $this->updateBrokerMetrics($broker->id, 'assignment');
        }
    }

    /**
     * Log duplicate prevention action
     */
    public function logDuplicatePrevention(array $inquiryData, array $duplicateResult): void
    {
        $logData = [
            'event' => 'duplicate_prevention',
            'email' => $inquiryData['email'] ?? null,
            'property_id' => $inquiryData['property_id'] ?? null,
            'ip_address' => $inquiryData['ip_address'] ?? null,
            'is_duplicate' => $duplicateResult['is_duplicate'],
            'duplicate_type' => $duplicateResult['duplicate_type'] ?? null,
            'action_taken' => $duplicateResult['action'],
            'original_inquiry_id' => $duplicateResult['original_inquiry_id'] ?? null,
            'similarity_score' => $duplicateResult['similarity_score'] ?? null,
            'detected_at' => now()->toISOString()
        ];

        Log::channel('inquiry')->warning('Duplicate inquiry detected', $logData);
        
        // Update duplicate metrics
        $this->updateDuplicateMetrics($duplicateResult);
    }

    /**
     * Log notification sent
     */
    public function logNotificationSent(string $notificationType, $recipient, array $data = []): void
    {
        $logData = [
            'event' => 'notification_sent',
            'notification_type' => $notificationType,
            'recipient_id' => is_object($recipient) ? $recipient->id : $recipient,
            'recipient_type' => is_object($recipient) ? get_class($recipient) : 'unknown',
            'channels' => $data['channels'] ?? [],
            'inquiry_id' => $data['inquiry_id'] ?? null,
            'sent_at' => now()->toISOString()
        ];

        Log::channel('inquiry')->info('Notification sent', $logData);
        
        // Update notification metrics
        $this->updateNotificationMetrics($notificationType);
    }

    /**
     * Log system error
     */
    public function logError(string $operation, \Throwable $exception, array $context = []): void
    {
        $logData = [
            'event' => 'inquiry_error',
            'operation' => $operation,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'context' => $context,
            'occurred_at' => now()->toISOString()
        ];

        Log::channel('inquiry')->error('Inquiry system error', $logData);
        
        // Update error metrics
        $this->updateErrorMetrics($operation);
    }

    /**
     * Get real-time inquiry metrics
     */
    public function getRealtimeMetrics(): array
    {
        $cacheKey = 'inquiry_metrics_realtime';
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'total_inquiries_today' => $this->getTodayInquiryCount(),
                'new_inquiries_count' => $this->getInquiriesByStatus(InquiryStatus::NEW),
                'contacted_inquiries_count' => $this->getInquiriesByStatus(InquiryStatus::CONTACTED),
                'scheduled_inquiries_count' => $this->getInquiriesByStatus(InquiryStatus::SCHEDULED),
                'completed_inquiries_count' => $this->getInquiriesByStatus(InquiryStatus::COMPLETED),
                'overdue_inquiries_count' => $this->getOverdueInquiryCount(),
                'average_response_time' => $this->getAverageResponseTime(),
                'conversion_rate' => $this->getConversionRate(),
                'active_brokers_count' => $this->getActiveBrokerCount(),
                'duplicate_prevention_rate' => $this->getDuplicatePreventionRate(),
                'last_updated' => now()->toISOString()
            ];
        });
    }

    /**
     * Get performance metrics for dashboard
     */
    public function getPerformanceMetrics(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'inquiry_trends' => $this->getInquiryTrends($startDate),
            'status_distribution' => $this->getStatusDistribution($startDate),
            'broker_performance' => $this->getBrokerPerformance($startDate),
            'property_performance' => $this->getPropertyPerformance($startDate),
            'response_time_trends' => $this->getResponseTimeTrends($startDate),
            'conversion_trends' => $this->getConversionTrends($startDate),
            'duplicate_statistics' => $this->getDuplicateStatistics($startDate),
            'error_rates' => $this->getErrorRates($startDate)
        ];
    }

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'alerts' => []
        ];

        // Check inquiry processing rate
        $processingRate = $this->checkInquiryProcessingRate();
        $health['checks']['inquiry_processing'] = $processingRate;
        if (!$processingRate['healthy']) {
            $health['status'] = 'warning';
            $health['alerts'][] = 'Inquiry processing rate is below threshold';
        }

        // Check broker availability
        $brokerAvailability = $this->checkBrokerAvailability();
        $health['checks']['broker_availability'] = $brokerAvailability;
        if (!$brokerAvailability['healthy']) {
            $health['status'] = 'critical';
            $health['alerts'][] = 'Insufficient active brokers available';
        }

        // Check error rates
        $errorRate = $this->checkErrorRate();
        $health['checks']['error_rate'] = $errorRate;
        if (!$errorRate['healthy']) {
            $health['status'] = 'warning';
            $health['alerts'][] = 'Error rate is above acceptable threshold';
        }

        // Check overdue inquiries
        $overdueCheck = $this->checkOverdueInquiries();
        $health['checks']['overdue_inquiries'] = $overdueCheck;
        if (!$overdueCheck['healthy']) {
            $health['status'] = 'warning';
            $health['alerts'][] = 'High number of overdue inquiries detected';
        }

        return $health;
    }

    /**
     * Generate alerts for critical issues
     */
    public function generateAlerts(): array
    {
        $alerts = [];

        // Check for critical overdue inquiries
        $criticalOverdue = Inquiry::where('status', InquiryStatus::NEW)
            ->where('created_at', '<', now()->subHours(48))
            ->count();

        if ($criticalOverdue > 0) {
            $alerts[] = [
                'type' => 'critical',
                'message' => "Found {$criticalOverdue} inquiries overdue by more than 48 hours",
                'action_required' => 'Immediate broker assignment needed'
            ];
        }

        // Check for broker overload
        $overloadedBrokers = $this->getOverloadedBrokers();
        if (count($overloadedBrokers) > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => count($overloadedBrokers) . ' brokers are overloaded',
                'action_required' => 'Consider redistributing workload'
            ];
        }

        // Check for high duplicate rate
        $duplicateRate = $this->getDuplicatePreventionRate();
        if ($duplicateRate > 0.3) { // More than 30% duplicates
            $alerts[] = [
                'type' => 'warning',
                'message' => 'High duplicate inquiry rate detected',
                'action_required' => 'Review duplicate prevention rules'
            ];
        }

        return $alerts;
    }

    /**
     * Private helper methods
     */
    private function updateInquiryMetrics(string $action): void
    {
        $key = "inquiry_metrics:{$action}:" . now()->format('Y-m-d');
        Cache::increment($key, 1);
        Cache::expire($key, 86400); // 24 hours
    }

    private function updateStatusMetrics(InquiryStatus $oldStatus, InquiryStatus $newStatus): void
    {
        $date = now()->format('Y-m-d');
        Cache::increment("status_transitions:{$oldStatus->value}_to_{$newStatus->value}:{$date}", 1);
    }

    private function updateBrokerMetrics(int $brokerId, string $action): void
    {
        $key = "broker_metrics:{$brokerId}:{$action}:" . now()->format('Y-m-d');
        Cache::increment($key, 1);
    }

    private function updateDuplicateMetrics(array $duplicateResult): void
    {
        $date = now()->format('Y-m-d');
        Cache::increment("duplicate_metrics:total:{$date}", 1);
        
        if ($duplicateResult['is_duplicate']) {
            Cache::increment("duplicate_metrics:prevented:{$date}", 1);
            Cache::increment("duplicate_metrics:type:{$duplicateResult['duplicate_type']}:{$date}", 1);
        }
    }

    private function updateNotificationMetrics(string $type): void
    {
        $key = "notification_metrics:{$type}:" . now()->format('Y-m-d');
        Cache::increment($key, 1);
    }

    private function updateErrorMetrics(string $operation): void
    {
        $key = "error_metrics:{$operation}:" . now()->format('Y-m-d');
        Cache::increment($key, 1);
    }

    private function calculateTimeInStatus(Inquiry $inquiry, InquiryStatus $status): ?int
    {
        // This would require status history tracking
        // For now, return null - implement based on your status history model
        return null;
    }

    private function getTodayInquiryCount(): int
    {
        return Inquiry::whereDate('created_at', today())->count();
    }

    private function getInquiriesByStatus(InquiryStatus $status): int
    {
        return Inquiry::where('status', $status)->count();
    }

    private function getOverdueInquiryCount(): int
    {
        return Inquiry::where('status', InquiryStatus::NEW)
            ->where('created_at', '<', now()->subHours(24))
            ->count();
    }

    private function getAverageResponseTime(): float
    {
        // Calculate average time from NEW to CONTACTED status
        return DB::table('inquiries')
            ->where('status', '!=', InquiryStatus::NEW->value)
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, updated_at)')) ?? 0;
    }

    private function getConversionRate(): float
    {
        $total = Inquiry::whereDate('created_at', '>=', now()->subDays(30))->count();
        $completed = Inquiry::where('status', InquiryStatus::COMPLETED)
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->count();

        return $total > 0 ? ($completed / $total) * 100 : 0;
    }

    private function getActiveBrokerCount(): int
    {
        return User::where('role', 'broker')
            ->where('is_active', true)
            ->where('status', 'approved')
            ->count();
    }

    private function getDuplicatePreventionRate(): float
    {
        $totalAttempts = Cache::get("duplicate_metrics:total:" . now()->format('Y-m-d'), 0);
        $prevented = Cache::get("duplicate_metrics:prevented:" . now()->format('Y-m-d'), 0);

        return $totalAttempts > 0 ? ($prevented / $totalAttempts) : 0;
    }

    private function getInquiryTrends(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getStatusDistribution(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getBrokerPerformance(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->join('properties', 'inquiries.property_id', '=', 'properties.id')
            ->join('users', 'properties.broker_id', '=', 'users.id')
            ->select('users.id', 'users.name', 
                DB::raw('COUNT(inquiries.id) as total_inquiries'),
                DB::raw('SUM(CASE WHEN inquiries.status = "completed" THEN 1 ELSE 0 END) as completed_inquiries'))
            ->where('inquiries.created_at', '>=', $startDate)
            ->groupBy('users.id', 'users.name')
            ->get()
            ->toArray();
    }

    private function getPropertyPerformance(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->join('properties', 'inquiries.property_id', '=', 'properties.id')
            ->select('properties.id', 'properties.title', DB::raw('COUNT(*) as inquiry_count'))
            ->where('inquiries.created_at', '>=', $startDate)
            ->groupBy('properties.id', 'properties.title')
            ->orderByDesc('inquiry_count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getResponseTimeTrends(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->select(DB::raw('DATE(created_at) as date'), 
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_response_time'))
            ->where('created_at', '>=', $startDate)
            ->where('status', '!=', InquiryStatus::NEW->value)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getConversionTrends(Carbon $startDate): array
    {
        return DB::table('inquiries')
            ->select(DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'))
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->conversion_rate = $item->total > 0 ? ($item->completed / $item->total) * 100 : 0;
                return $item;
            })
            ->toArray();
    }

    private function getDuplicateStatistics(Carbon $startDate): array
    {
        // This would query your duplicate_logs table
        return [
            'total_attempts' => 0,
            'duplicates_prevented' => 0,
            'prevention_rate' => 0
        ];
    }

    private function getErrorRates(Carbon $startDate): array
    {
        // This would analyze error logs
        return [
            'total_errors' => 0,
            'error_rate' => 0,
            'by_operation' => []
        ];
    }

    private function checkInquiryProcessingRate(): array
    {
        $recentInquiries = Inquiry::where('created_at', '>=', now()->subHour())->count();
        $threshold = 10; // Minimum expected per hour
        
        return [
            'healthy' => $recentInquiries >= $threshold,
            'value' => $recentInquiries,
            'threshold' => $threshold,
            'message' => "Processing {$recentInquiries} inquiries per hour"
        ];
    }

    private function checkBrokerAvailability(): array
    {
        $activeBrokers = $this->getActiveBrokerCount();
        $threshold = 3; // Minimum required active brokers
        
        return [
            'healthy' => $activeBrokers >= $threshold,
            'value' => $activeBrokers,
            'threshold' => $threshold,
            'message' => "{$activeBrokers} active brokers available"
        ];
    }

    private function checkErrorRate(): array
    {
        $errors = Cache::get("error_metrics:total:" . now()->format('Y-m-d'), 0);
        $total = $this->getTodayInquiryCount();
        $errorRate = $total > 0 ? ($errors / $total) * 100 : 0;
        $threshold = 5; // 5% error rate threshold
        
        return [
            'healthy' => $errorRate <= $threshold,
            'value' => $errorRate,
            'threshold' => $threshold,
            'message' => "{$errorRate}% error rate today"
        ];
    }

    private function checkOverdueInquiries(): array
    {
        $overdue = $this->getOverdueInquiryCount();
        $threshold = 10; // Maximum acceptable overdue inquiries
        
        return [
            'healthy' => $overdue <= $threshold,
            'value' => $overdue,
            'threshold' => $threshold,
            'message' => "{$overdue} overdue inquiries"
        ];
    }

    private function getOverloadedBrokers(): array
    {
        return User::where('role', 'broker')
            ->where('is_active', true)
            ->whereHas('properties.inquiries', function ($query) {
                $query->whereIn('status', [InquiryStatus::NEW, InquiryStatus::CONTACTED])
                    ->havingRaw('COUNT(*) > 20'); // More than 20 active inquiries
            })
            ->get()
            ->toArray();
    }
}