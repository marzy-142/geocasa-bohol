<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonitoringService;
use Illuminate\Support\Facades\Log;

class MonitorSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:monitor {--metrics : Display current metrics} {--critical : Log critical metrics only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor system health and performance metrics';

    /**
     * Execute the console command.
     */
    public function handle(MonitoringService $monitoringService)
    {
        $this->info('Starting system monitoring...');
        
        try {
            if ($this->option('critical')) {
                // Log only critical metrics
                $monitoringService->logCriticalMetrics();
                $this->info('Critical metrics logged successfully.');
                return 0;
            }
            
            // Get all metrics
            $metrics = $monitoringService->getMetrics();
            
            if ($this->option('metrics')) {
                // Display metrics in console
                $this->displayMetrics($metrics);
            } else {
                // Log all metrics
                Log::info('System Metrics', $metrics);
                $this->info('System metrics logged successfully.');
            }
            
            // Always check critical metrics
            $monitoringService->logCriticalMetrics();
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Failed to monitor system: ' . $e->getMessage());
            Log::error('System monitoring failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }
    
    /**
     * Display metrics in a formatted table
     */
    protected function displayMetrics(array $metrics): void
    {
        $this->info('=== SYSTEM METRICS ===');
        
        // User Metrics
        if (isset($metrics['users']) && !isset($metrics['users']['error'])) {
            $this->info('\n--- User Metrics ---');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Users', $metrics['users']['total_users'] ?? 'N/A'],
                    ['Active Today', $metrics['users']['active_users_today'] ?? 'N/A'],
                    ['New This Week', $metrics['users']['new_users_this_week'] ?? 'N/A'],
                    ['Pending Brokers', $metrics['users']['brokers_pending_approval'] ?? 'N/A'],
                    ['Approved Brokers', $metrics['users']['approved_brokers'] ?? 'N/A'],
                ]
            );
        }
        
        // Property Metrics
        if (isset($metrics['properties']) && !isset($metrics['properties']['error'])) {
            $this->info('\n--- Property Metrics ---');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Properties', $metrics['properties']['total_properties'] ?? 'N/A'],
                    ['Active Properties', $metrics['properties']['active_properties'] ?? 'N/A'],
                    ['Sold Properties', $metrics['properties']['sold_properties'] ?? 'N/A'],
                    ['With Virtual Tours', $metrics['properties']['properties_with_virtual_tours'] ?? 'N/A'],
                    ['New This Week', $metrics['properties']['new_properties_this_week'] ?? 'N/A'],
                    ['Average Price', number_format($metrics['properties']['average_property_price'] ?? 0, 2)],
                ]
            );
        }
        
        // Inquiry Metrics
        if (isset($metrics['inquiries']) && !isset($metrics['inquiries']['error'])) {
            $this->info('\n--- Inquiry Metrics ---');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Inquiries', $metrics['inquiries']['total_inquiries'] ?? 'N/A'],
                    ['New Today', $metrics['inquiries']['new_inquiries_today'] ?? 'N/A'],
                    ['Pending', $metrics['inquiries']['pending_inquiries'] ?? 'N/A'],
                    ['Responded', $metrics['inquiries']['responded_inquiries'] ?? 'N/A'],
                    ['This Week', $metrics['inquiries']['inquiries_this_week'] ?? 'N/A'],
                ]
            );
        }
        
        // Transaction Metrics
        if (isset($metrics['transactions']) && !isset($metrics['transactions']['error'])) {
            $this->info('\n--- Transaction Metrics ---');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Transactions', $metrics['transactions']['total_transactions'] ?? 'N/A'],
                    ['Completed', $metrics['transactions']['completed_transactions'] ?? 'N/A'],
                    ['Pending', $metrics['transactions']['pending_transactions'] ?? 'N/A'],
                    ['This Month', $metrics['transactions']['transactions_this_month'] ?? 'N/A'],
                    ['Total Value', number_format($metrics['transactions']['total_transaction_value'] ?? 0, 2)],
                    ['Average Value', number_format($metrics['transactions']['average_transaction_value'] ?? 0, 2)],
                ]
            );
        }
        
        // System Metrics
        if (isset($metrics['system']) && !isset($metrics['system']['error'])) {
            $this->info('\n--- System Metrics ---');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['PHP Version', $metrics['system']['php_version'] ?? 'N/A'],
                    ['Laravel Version', $metrics['system']['laravel_version'] ?? 'N/A'],
                    ['Environment', $metrics['system']['environment'] ?? 'N/A'],
                    ['Debug Mode', $metrics['system']['debug_mode'] ? 'Enabled' : 'Disabled'],
                    ['Memory Usage', $metrics['system']['memory_usage'] ?? 'N/A'],
                    ['Memory Peak', $metrics['system']['memory_peak'] ?? 'N/A'],
                ]
            );
            
            if (isset($metrics['system']['disk_usage']) && is_array($metrics['system']['disk_usage'])) {
                $this->info('\n--- Disk Usage ---');
                $diskUsage = $metrics['system']['disk_usage'];
                $this->table(
                    ['Metric', 'Value'],
                    [
                        ['Total Space', $diskUsage['total'] ?? 'N/A'],
                        ['Used Space', $diskUsage['used'] ?? 'N/A'],
                        ['Free Space', $diskUsage['free'] ?? 'N/A'],
                        ['Usage %', ($diskUsage['usage_percentage'] ?? 0) . '%'],
                    ]
                );
            }
        }
        
        // Performance Metrics
        if (isset($metrics['performance']) && !isset($metrics['performance']['error'])) {
            $this->info('\n--- Performance Metrics ---');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['DB Response Time', ($metrics['performance']['database_response_time_ms'] ?? 0) . 'ms'],
                    ['Cache Response Time', ($metrics['performance']['cache_response_time_ms'] ?? 0) . 'ms'],
                    ['Avg Response Time', ($metrics['performance']['average_response_time_ms'] ?? 0) . 'ms'],
                    ['Slow Queries', $metrics['performance']['slow_queries_count'] ?? 0],
                ]
            );
        }
        
        // Display any errors
        $errors = [];
        foreach ($metrics as $category => $data) {
            if (isset($data['error'])) {
                $errors[] = [$category, $data['error']];
            }
        }
        
        if (!empty($errors)) {
            $this->error('\n--- Errors ---');
            $this->table(['Category', 'Error'], $errors);
        }
    }
}