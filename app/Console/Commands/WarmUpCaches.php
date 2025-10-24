<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceOptimizationService;
use Illuminate\Support\Facades\Log;

class WarmUpCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm-up {--clear : Clear existing caches before warming}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application caches for better performance';

    protected PerformanceOptimizationService $performanceService;

    public function __construct(PerformanceOptimizationService $performanceService)
    {
        parent::__construct();
        $this->performanceService = $performanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cache warming process...');

        if ($this->option('clear')) {
            $this->info('Clearing existing caches...');
            $this->performanceService->clearAllCaches();
        }

        try {
            $this->performanceService->warmUpCaches();
            $this->info('Cache warming completed successfully!');
            Log::info('Cache warming completed via artisan command');
        } catch (\Exception $e) {
            $this->error('Cache warming failed: ' . $e->getMessage());
            Log::error('Cache warming failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

