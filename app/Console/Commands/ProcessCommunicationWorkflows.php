<?php

namespace App\Console\Commands;

use App\Services\CommunicationWorkflowService;
use Illuminate\Console\Command;

class ProcessCommunicationWorkflows extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'workflows:process 
                            {--escalate : Auto-escalate unanswered inquiries}
                            {--hours=24 : Hours threshold for auto-escalation}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending communication workflows and optionally auto-escalate inquiries';

    protected CommunicationWorkflowService $workflowService;

    public function __construct(CommunicationWorkflowService $workflowService)
    {
        parent::__construct();
        $this->workflowService = $workflowService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting communication workflow processing...');

        try {
            // Process pending workflows
            $this->info('Processing pending workflows...');
            $results = $this->workflowService->processPendingWorkflows();

            $this->displayResults('Workflow Processing', $results);

            // Auto-escalate if requested
            if ($this->option('escalate')) {
                $hoursThreshold = (int) $this->option('hours');
                $this->info("Auto-escalating inquiries older than {$hoursThreshold} hours...");
                
                $escalationResults = $this->workflowService->autoEscalateUnansweredInquiries($hoursThreshold);
                
                $this->displayResults('Auto-Escalation', $escalationResults);
            }

            $this->info('Communication workflow processing completed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to process communication workflows: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Display processing results
     */
    protected function displayResults(string $title, array $results): void
    {
        $this->info("=== {$title} Results ===");
        
        foreach ($results as $key => $value) {
            $formattedKey = ucwords(str_replace('_', ' ', $key));
            $this->line("{$formattedKey}: {$value}");
        }
        
        $this->line('');
    }
}

