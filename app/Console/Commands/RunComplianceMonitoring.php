<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ComplianceMonitoringService;
use App\Models\Property;
use App\Models\Inquiry;
use App\Models\User;

class RunComplianceMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compliance:monitor {--type=all : Type of monitoring (properties, inquiries, users, all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run automated compliance monitoring checks on properties, inquiries, and user activities';

    protected $complianceService;

    public function __construct(ComplianceMonitoringService $complianceService)
    {
        parent::__construct();
        $this->complianceService = $complianceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $this->info('Starting compliance monitoring checks...');

        $results = [
            'properties' => 0,
            'inquiries' => 0,
            'users' => 0,
            'reports_created' => 0
        ];

        try {
            if ($type === 'all' || $type === 'properties') {
                $this->info('Checking properties for compliance issues...');
                $properties = Property::where('created_at', '>=', now()->subDays(7))->get();
                
                foreach ($properties as $property) {
                    $issues = $this->complianceService->checkProperty($property);
                    $results['properties']++;
                    $results['reports_created'] += count($issues);
                }
                
                $this->info("Checked {$results['properties']} properties");
            }

            if ($type === 'all' || $type === 'inquiries') {
                $this->info('Checking inquiries for compliance issues...');
                $inquiries = Inquiry::where('created_at', '>=', now()->subDays(7))->get();
                
                foreach ($inquiries as $inquiry) {
                    $issues = $this->complianceService->checkInquiry($inquiry);
                    $results['inquiries']++;
                    $results['reports_created'] += count($issues);
                }
                
                $this->info("Checked {$results['inquiries']} inquiries");
            }

            if ($type === 'all' || $type === 'users') {
                $this->info('Checking user activities for compliance issues...');
                $users = User::where('last_activity', '>=', now()->subDays(7))->get();
                
                foreach ($users as $user) {
                    $issues = $this->complianceService->checkUserActivity($user);
                    $results['users']++;
                    $results['reports_created'] += count($issues);
                }
                
                $this->info("Checked {$results['users']} users");
            }

            $this->info("\nCompliance monitoring completed successfully!");
            $this->info("Total reports created: {$results['reports_created']}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error during compliance monitoring: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
