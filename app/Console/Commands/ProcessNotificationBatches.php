<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmartNotificationService;

class ProcessNotificationBatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-batches {--force : Force process all pending batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending notification batches for users';

    protected SmartNotificationService $notificationService;

    public function __construct(SmartNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing pending notification batches...');

        try {
            $this->notificationService->processPendingBatches();
            $this->info('✅ Notification batches processed successfully.');
        } catch (\Exception $e) {
            $this->error('❌ Failed to process notification batches: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
