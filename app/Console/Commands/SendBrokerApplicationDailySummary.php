<?php

namespace App\Console\Commands;

use App\Services\BrokerApplicationNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBrokerApplicationDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'broker:daily-summary {--force : Force sending even if already sent today}';

    /**
     * The console command description.
     */
    protected $description = 'Send daily summary of broker applications to admin users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Starting broker application daily summary...');

            $notificationService = app(BrokerApplicationNotificationService::class);
            
            // Send daily summary
            $notificationService->sendDailySummary();
            
            // Send notifications for incomplete applications
            $notificationService->notifyIncompleteApplications();

            $this->info('Broker application daily summary sent successfully.');
            
            Log::info('Broker application daily summary command completed successfully');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to send broker application daily summary: ' . $e->getMessage());
            
            Log::error('Broker application daily summary command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
}

