<?php

namespace App\Console\Commands;

use App\Services\InquiryMonitoringService;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Carbon\Carbon;

class CheckInquirySystemHealth extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'inquiry:health-check 
                            {--component= : Check specific component (inquiry_processing, broker_assignment, notifications, database)}
                            {--alert : Send alerts for critical issues}
                            {--verbose : Show detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Check the health of the inquiry system components';

    protected InquiryMonitoringService $monitoringService;

    public function __construct(InquiryMonitoringService $monitoringService)
    {
        parent::__construct();
        $this->monitoringService = $monitoringService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $component = $this->option('component');
        $sendAlerts = $this->option('alert');
        $verbose = $this->option('verbose');

        $this->info('🔍 Checking Inquiry System Health...');
        $this->newLine();

        $healthChecks = [];

        if (!$component || $component === 'inquiry_processing') {
            $healthChecks['inquiry_processing'] = $this->checkInquiryProcessing($verbose);
        }

        if (!$component || $component === 'broker_assignment') {
            $healthChecks['broker_assignment'] = $this->checkBrokerAssignment($verbose);
        }

        if (!$component || $component === 'notifications') {
            $healthChecks['notifications'] = $this->checkNotifications($verbose);
        }

        if (!$component || $component === 'database') {
            $healthChecks['database'] = $this->checkDatabase($verbose);
        }

        if (!$component || $component === 'queue') {
            $healthChecks['queue'] = $this->checkQueue($verbose);
        }

        // Log health check results
        foreach ($healthChecks as $componentName => $result) {
            DB::table('system_health_logs')->insert([
                'component' => $componentName,
                'status' => $result['status'],
                'metrics' => json_encode($result['metrics']),
                'message' => $result['message'],
                'checked_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Display summary
        $this->displayHealthSummary($healthChecks);

        // Send alerts if requested and there are critical issues
        if ($sendAlerts) {
            $this->sendHealthAlerts($healthChecks);
        }

        // Determine exit code
        $hasErrors = collect($healthChecks)->contains(fn($check) => $check['status'] === 'critical');
        
        return $hasErrors ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Check inquiry processing health
     */
    private function checkInquiryProcessing(bool $verbose): array
    {
        $this->info('📝 Checking Inquiry Processing...');
        
        $metrics = [];
        $issues = [];
        
        try {
            // Check recent inquiry creation rate
            $recentInquiries = Inquiry::where('created_at', '>=', now()->subHours(24))->count();
            $metrics['inquiries_24h'] = $recentInquiries;
            
            // Check for stuck inquiries (new status for more than 2 hours)
            $stuckInquiries = Inquiry::where('status', 'new')
                ->where('created_at', '<=', now()->subHours(2))
                ->count();
            $metrics['stuck_inquiries'] = $stuckInquiries;
            
            if ($stuckInquiries > 10) {
                $issues[] = "High number of stuck inquiries: {$stuckInquiries}";
            }
            
            // Check average processing time
            $avgProcessingTime = $this->monitoringService->getAverageProcessingTime();
            $metrics['avg_processing_time_ms'] = $avgProcessingTime;
            
            if ($avgProcessingTime > 5000) { // 5 seconds
                $issues[] = "Slow processing time: {$avgProcessingTime}ms";
            }
            
            // Check error rate
            $errorRate = $this->monitoringService->getErrorRate();
            $metrics['error_rate_percent'] = $errorRate;
            
            if ($errorRate > 5) {
                $issues[] = "High error rate: {$errorRate}%";
            }
            
            if ($verbose) {
                $this->line("  ✓ Recent inquiries (24h): {$recentInquiries}");
                $this->line("  ✓ Stuck inquiries: {$stuckInquiries}");
                $this->line("  ✓ Avg processing time: {$avgProcessingTime}ms");
                $this->line("  ✓ Error rate: {$errorRate}%");
            }
            
        } catch (\Exception $e) {
            $issues[] = "Failed to check inquiry processing: " . $e->getMessage();
        }
        
        $status = empty($issues) ? 'healthy' : (count($issues) > 2 ? 'critical' : 'warning');
        $message = empty($issues) ? 'Inquiry processing is healthy' : implode('; ', $issues);
        
        $this->displayComponentStatus('Inquiry Processing', $status);
        
        return [
            'status' => $status,
            'metrics' => $metrics,
            'message' => $message,
            'issues' => $issues
        ];
    }

    /**
     * Check broker assignment health
     */
    private function checkBrokerAssignment(bool $verbose): array
    {
        $this->info('👥 Checking Broker Assignment...');
        
        $metrics = [];
        $issues = [];
        
        try {
            // Check available brokers
            $availableBrokers = User::where('role', 'broker')
                ->where('is_active', true)
                ->count();
            $metrics['available_brokers'] = $availableBrokers;
            
            if ($availableBrokers < 3) {
                $issues[] = "Low number of available brokers: {$availableBrokers}";
            }
            
            // Check unassigned inquiries
            $unassignedInquiries = Inquiry::whereNull('assigned_broker_id')
                ->where('created_at', '>=', now()->subHours(24))
                ->count();
            $metrics['unassigned_inquiries'] = $unassignedInquiries;
            
            if ($unassignedInquiries > 5) {
                $issues[] = "High number of unassigned inquiries: {$unassignedInquiries}";
            }
            
            // Check broker workload distribution
            $workloadVariance = $this->calculateBrokerWorkloadVariance();
            $metrics['workload_variance'] = $workloadVariance;
            
            if ($workloadVariance > 50) {
                $issues[] = "Uneven broker workload distribution: {$workloadVariance}";
            }
            
            if ($verbose) {
                $this->line("  ✓ Available brokers: {$availableBrokers}");
                $this->line("  ✓ Unassigned inquiries: {$unassignedInquiries}");
                $this->line("  ✓ Workload variance: {$workloadVariance}");
            }
            
        } catch (\Exception $e) {
            $issues[] = "Failed to check broker assignment: " . $e->getMessage();
        }
        
        $status = empty($issues) ? 'healthy' : (count($issues) > 2 ? 'critical' : 'warning');
        $message = empty($issues) ? 'Broker assignment is healthy' : implode('; ', $issues);
        
        $this->displayComponentStatus('Broker Assignment', $status);
        
        return [
            'status' => $status,
            'metrics' => $metrics,
            'message' => $message,
            'issues' => $issues
        ];
    }

    /**
     * Check notifications health
     */
    private function checkNotifications(bool $verbose): array
    {
        $this->info('📧 Checking Notifications...');
        
        $metrics = [];
        $issues = [];
        
        try {
            // Check recent notification success rate
            $recentNotifications = DB::table('notification_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->count();
            
            $failedNotifications = DB::table('notification_logs')
                ->where('created_at', '>=', now()->subHours(24))
                ->where('status', 'failed')
                ->count();
            
            $metrics['notifications_24h'] = $recentNotifications;
            $metrics['failed_notifications'] = $failedNotifications;
            
            $failureRate = $recentNotifications > 0 ? ($failedNotifications / $recentNotifications) * 100 : 0;
            $metrics['failure_rate_percent'] = round($failureRate, 2);
            
            if ($failureRate > 10) {
                $issues[] = "High notification failure rate: {$failureRate}%";
            }
            
            // Check queue size for notifications
            $queueSize = Queue::size('notifications');
            $metrics['queue_size'] = $queueSize;
            
            if ($queueSize > 100) {
                $issues[] = "Large notification queue: {$queueSize} items";
            }
            
            if ($verbose) {
                $this->line("  ✓ Recent notifications: {$recentNotifications}");
                $this->line("  ✓ Failed notifications: {$failedNotifications}");
                $this->line("  ✓ Failure rate: {$failureRate}%");
                $this->line("  ✓ Queue size: {$queueSize}");
            }
            
        } catch (\Exception $e) {
            $issues[] = "Failed to check notifications: " . $e->getMessage();
        }
        
        $status = empty($issues) ? 'healthy' : (count($issues) > 1 ? 'critical' : 'warning');
        $message = empty($issues) ? 'Notifications are healthy' : implode('; ', $issues);
        
        $this->displayComponentStatus('Notifications', $status);
        
        return [
            'status' => $status,
            'metrics' => $metrics,
            'message' => $message,
            'issues' => $issues
        ];
    }

    /**
     * Check database health
     */
    private function checkDatabase(bool $verbose): array
    {
        $this->info('🗄️ Checking Database...');
        
        $metrics = [];
        $issues = [];
        
        try {
            // Check database connection
            $connectionTime = microtime(true);
            DB::connection()->getPdo();
            $connectionTime = (microtime(true) - $connectionTime) * 1000;
            $metrics['connection_time_ms'] = round($connectionTime, 2);
            
            if ($connectionTime > 1000) {
                $issues[] = "Slow database connection: {$connectionTime}ms";
            }
            
            // Check table sizes
            $inquiryCount = Inquiry::count();
            $metrics['inquiry_count'] = $inquiryCount;
            
            // Check for missing indexes (slow queries)
            $slowQueries = $this->checkForSlowQueries();
            $metrics['slow_queries'] = count($slowQueries);
            
            if (count($slowQueries) > 5) {
                $issues[] = "Multiple slow queries detected: " . count($slowQueries);
            }
            
            // Check disk space (if possible)
            $diskUsage = $this->checkDiskUsage();
            if ($diskUsage) {
                $metrics['disk_usage_percent'] = $diskUsage;
                if ($diskUsage > 90) {
                    $issues[] = "High disk usage: {$diskUsage}%";
                }
            }
            
            if ($verbose) {
                $this->line("  ✓ Connection time: {$connectionTime}ms");
                $this->line("  ✓ Inquiry count: {$inquiryCount}");
                $this->line("  ✓ Slow queries: " . count($slowQueries));
                if ($diskUsage) {
                    $this->line("  ✓ Disk usage: {$diskUsage}%");
                }
            }
            
        } catch (\Exception $e) {
            $issues[] = "Failed to check database: " . $e->getMessage();
        }
        
        $status = empty($issues) ? 'healthy' : (count($issues) > 2 ? 'critical' : 'warning');
        $message = empty($issues) ? 'Database is healthy' : implode('; ', $issues);
        
        $this->displayComponentStatus('Database', $status);
        
        return [
            'status' => $status,
            'metrics' => $metrics,
            'message' => $message,
            'issues' => $issues
        ];
    }

    /**
     * Check queue health
     */
    private function checkQueue(bool $verbose): array
    {
        $this->info('⚡ Checking Queue System...');
        
        $metrics = [];
        $issues = [];
        
        try {
            // Check queue sizes
            $defaultQueueSize = Queue::size();
            $metrics['default_queue_size'] = $defaultQueueSize;
            
            if ($defaultQueueSize > 1000) {
                $issues[] = "Large default queue: {$defaultQueueSize} jobs";
            }
            
            // Check failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            $metrics['failed_jobs'] = $failedJobs;
            
            if ($failedJobs > 50) {
                $issues[] = "High number of failed jobs: {$failedJobs}";
            }
            
            if ($verbose) {
                $this->line("  ✓ Default queue size: {$defaultQueueSize}");
                $this->line("  ✓ Failed jobs: {$failedJobs}");
            }
            
        } catch (\Exception $e) {
            $issues[] = "Failed to check queue: " . $e->getMessage();
        }
        
        $status = empty($issues) ? 'healthy' : (count($issues) > 1 ? 'critical' : 'warning');
        $message = empty($issues) ? 'Queue system is healthy' : implode('; ', $issues);
        
        $this->displayComponentStatus('Queue System', $status);
        
        return [
            'status' => $status,
            'metrics' => $metrics,
            'message' => $message,
            'issues' => $issues
        ];
    }

    /**
     * Calculate broker workload variance
     */
    private function calculateBrokerWorkloadVariance(): float
    {
        $brokerWorkloads = User::where('role', 'broker')
            ->where('is_active', true)
            ->withCount(['assignedInquiries as active_inquiries' => function ($query) {
                $query->whereIn('status', ['new', 'contacted', 'scheduled']);
            }])
            ->pluck('active_inquiries')
            ->toArray();
        
        if (empty($brokerWorkloads)) {
            return 0;
        }
        
        $mean = array_sum($brokerWorkloads) / count($brokerWorkloads);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $brokerWorkloads)) / count($brokerWorkloads);
        
        return round(sqrt($variance), 2);
    }

    /**
     * Check for slow queries (simplified)
     */
    private function checkForSlowQueries(): array
    {
        // This is a simplified check - in production you'd query performance_schema
        // or use query logs to identify actual slow queries
        return [];
    }

    /**
     * Check disk usage (simplified)
     */
    private function checkDiskUsage(): ?float
    {
        // This would need to be implemented based on your server environment
        // For now, return null to indicate unavailable
        return null;
    }

    /**
     * Display component status with color coding
     */
    private function displayComponentStatus(string $component, string $status): void
    {
        $icon = match($status) {
            'healthy' => '✅',
            'warning' => '⚠️',
            'critical' => '❌',
            default => '❓'
        };
        
        $this->line("  {$icon} {$component}: " . strtoupper($status));
    }

    /**
     * Display health summary
     */
    private function displayHealthSummary(array $healthChecks): void
    {
        $this->newLine();
        $this->info('📊 Health Check Summary:');
        $this->newLine();
        
        $healthy = 0;
        $warnings = 0;
        $critical = 0;
        
        foreach ($healthChecks as $component => $result) {
            match($result['status']) {
                'healthy' => $healthy++,
                'warning' => $warnings++,
                'critical' => $critical++,
                default => null
            };
        }
        
        $this->line("✅ Healthy: {$healthy}");
        $this->line("⚠️  Warnings: {$warnings}");
        $this->line("❌ Critical: {$critical}");
        
        if ($critical > 0) {
            $this->newLine();
            $this->error('🚨 Critical issues detected! Immediate attention required.');
        } elseif ($warnings > 0) {
            $this->newLine();
            $this->warn('⚠️  Some components need attention.');
        } else {
            $this->newLine();
            $this->info('🎉 All systems are healthy!');
        }
    }

    /**
     * Send health alerts for critical issues
     */
    private function sendHealthAlerts(array $healthChecks): void
    {
        $criticalIssues = collect($healthChecks)
            ->filter(fn($check) => $check['status'] === 'critical')
            ->toArray();
        
        if (empty($criticalIssues)) {
            return;
        }
        
        $this->warn('🚨 Sending alerts for critical issues...');
        
        // Here you would implement actual alerting (email, Slack, etc.)
        // For now, just log the alerts
        foreach ($criticalIssues as $component => $issue) {
            $this->monitoringService->logEvent('system_alert', null, [
                'component' => $component,
                'status' => 'critical',
                'message' => $issue['message'],
                'metrics' => $issue['metrics']
            ]);
        }
    }
}