<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class FixBrokerVerificationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:broker-verification {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix verification status inconsistencies for approved brokers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        // Find approved brokers with inconsistent verification status
        $inconsistentBrokers = User::where('role', 'broker')
            ->where('is_approved', true)
            ->where('application_status', 'approved')
            ->where(function ($query) {
                $query->where('prc_verified', false)
                      ->orWhereNull('prc_verified');
            })
            ->get();

        if ($inconsistentBrokers->isEmpty()) {
            $this->info('No inconsistent broker verification statuses found.');
            return 0;
        }

        $this->info("Found {$inconsistentBrokers->count()} approved brokers with inconsistent verification status:");
        
        $tableData = [];
        foreach ($inconsistentBrokers as $broker) {
            $tableData[] = [
                $broker->id,
                $broker->name,
                $broker->email,
                $broker->prc_verified ? 'Yes' : 'No',
                $broker->business_permit_verified ? 'Yes' : 'No',
                $broker->approved_at?->format('Y-m-d H:i:s') ?? 'N/A'
            ];
        }
        
        $this->table(
            ['ID', 'Name', 'Email', 'PRC Verified', 'Business Permit Verified', 'Approved At'],
            $tableData
        );

        if ($dryRun) {
            $this->warn('DRY RUN MODE: No changes will be made.');
            $this->info('These brokers would have their PRC verification status set to true.');
            return 0;
        }

        if (!$this->confirm('Do you want to update these brokers\' verification status?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $updated = 0;
        foreach ($inconsistentBrokers as $broker) {
            $broker->update([
                'prc_verified' => true,
                'prc_verification_notes' => 'Auto-corrected: Approved broker should have verified PRC status',
                'reviewed_at' => now(),
            ]);
            $updated++;
        }

        $this->info("Successfully updated {$updated} broker(s) verification status.");
        $this->info('All approved brokers now have consistent verification status.');
        
        return 0;
    }
}