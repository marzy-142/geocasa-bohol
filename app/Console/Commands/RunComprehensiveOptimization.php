<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ComprehensivePerformanceOptimizationService;
use Illuminate\Support\Facades\Log;

class RunComprehensiveOptimization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'optimize:comprehensive 
                            {--clear-cache : Clear all caches before optimization}
                            {--warm-cache : Warm up caches after optimization}
                            {--export-report : Export optimization report to file}
                            {--format=json : Report format (json, yaml, csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive performance optimization across all system components';

    protected ComprehensivePerformanceOptimizationService $optimizationService;

    public function __construct(ComprehensivePerformanceOptimizationService $optimizationService)
    {
        parent::__construct();
        $this->optimizationService = $optimizationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting comprehensive performance optimization...');
        Log::info('Starting comprehensive performance optimization command');

        try {
            // Clear caches if requested
            if ($this->option('clear-cache')) {
                $this->info('Clearing all caches...');
                $cacheResults = $this->optimizationService->clearAllCaches();
                $this->displayCacheResults($cacheResults);
            }

            // Run comprehensive optimization
            $this->info('Running comprehensive optimization...');
            $results = $this->optimizationService->runComprehensiveOptimization();

            // Display results
            $this->displayOptimizationResults($results);

            // Warm up caches if requested
            if ($this->option('warm-cache')) {
                $this->info('Warming up caches...');
                $warmResults = $this->optimizationService->warmUpAllCaches();
                $this->displayWarmResults($warmResults);
            }

            // Export report if requested
            if ($this->option('export-report')) {
                $this->info('Exporting optimization report...');
                $this->exportReport($results);
            }

            $this->info('Comprehensive performance optimization completed successfully!');
            Log::info('Comprehensive performance optimization command completed successfully');

        } catch (\Exception $e) {
            $this->error('Optimization failed: ' . $e->getMessage());
            Log::error('Comprehensive performance optimization command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Display cache clearing results
     */
    private function displayCacheResults(array $results): void
    {
        $this->table(
            ['Service', 'Status'],
            collect($results)->map(function ($status, $service) {
                return [$service, $status];
            })->toArray()
        );
    }

    /**
     * Display optimization results
     */
    private function displayOptimizationResults(array $results): void
    {
        $this->info('Optimization Results:');
        $this->line('');

        // Overall status
        $status = $results['overall_success'] ? '✅ Success' : '❌ Failed';
        $this->line("Overall Status: {$status}");
        $this->line("Execution Time: {$results['execution_time']}s");
        $this->line("Memory Usage: " . round($results['memory_usage'] / 1024 / 1024, 2) . "MB");
        $this->line('');

        // Service results
        $this->info('Service Results:');
        $serviceResults = [];
        foreach ($results['optimization_results'] as $service => $result) {
            $status = $result['success'] ? '✅' : '❌';
            $serviceResults[] = [$service, $status, $result['error'] ?? 'OK'];
        }
        $this->table(['Service', 'Status', 'Details'], $serviceResults);

        // Recommendations
        if (!empty($results['recommendations'])) {
            $this->line('');
            $this->info('Recommendations:');
            foreach ($results['recommendations'] as $service => $recommendations) {
                $this->line("{$service}:");
                foreach ($recommendations as $recommendation) {
                    $this->line("  • {$recommendation}");
                }
            }
        }

        // Health status
        if (!empty($results['health_status'])) {
            $this->line('');
            $this->info('Health Status:');
            foreach ($results['health_status'] as $service => $health) {
                $status = $health['status'] ?? 'unknown';
                $statusIcon = $status === 'healthy' ? '✅' : ($status === 'unhealthy' ? '❌' : '⚠️');
                $this->line("  {$statusIcon} {$service}: {$status}");
            }
        }
    }

    /**
     * Display cache warming results
     */
    private function displayWarmResults(array $results): void
    {
        $this->table(
            ['Service', 'Status'],
            collect($results)->map(function ($status, $service) {
                return [$service, $status];
            })->toArray()
        );
    }

    /**
     * Export optimization report
     */
    private function exportReport(array $results): void
    {
        $format = $this->option('format');
        $timestamp = now()->format('Y_m_d_H_i_s');
        $filename = "optimization_report_{$timestamp}.{$format}";

        try {
            switch ($format) {
                case 'json':
                    $content = json_encode($results, JSON_PRETTY_PRINT);
                    break;
                case 'yaml':
                    $content = yaml_emit($results);
                    break;
                case 'csv':
                    $content = $this->generateCsvReport($results);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported format: {$format}");
            }

            file_put_contents(storage_path("app/reports/{$filename}"), $content);
            $this->info("Report exported to: storage/app/reports/{$filename}");

        } catch (\Exception $e) {
            $this->error("Failed to export report: " . $e->getMessage());
        }
    }

    /**
     * Generate CSV report
     */
    private function generateCsvReport(array $results): string
    {
        $csv = "Service,Status,Execution Time,Memory Usage,Recommendations\n";

        foreach ($results['optimization_results'] as $service => $result) {
            $status = $result['success'] ? 'Success' : 'Failed';
            $recommendations = isset($results['recommendations'][$service])
                ? implode('; ', $results['recommendations'][$service])
                : 'None';
            
            $csv .= "{$service},{$status},{$results['execution_time']},{$results['memory_usage']},\"{$recommendations}\"\n";
        }

        return $csv;
    }
}
