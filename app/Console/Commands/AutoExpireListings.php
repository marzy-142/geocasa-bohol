<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Property;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoExpireListings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property:auto-expire {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire listings that have passed their expiry date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-expire listings process...');
        
        $isDryRun = $this->option('dry-run');
        
        // Get properties that are expired but still active
        $expiredProperties = Property::where('status', '!=', 'pending_renewal')
            ->where('expiry_date', '<', Carbon::now())
            ->whereNotNull('expiry_date')
            ->with('broker')
            ->get();
        
        if ($expiredProperties->isEmpty()) {
            $this->info('No expired properties found.');
            return 0;
        }
        
        $this->info("Found {$expiredProperties->count()} expired properties to process.");
        
        $expiredCount = 0;
        $errorCount = 0;
        
        foreach ($expiredProperties as $property) {
            try {
                if ($isDryRun) {
                    $this->line("[DRY RUN] Would expire: {$property->title} (ID: {$property->id}) - Expired on {$property->expiry_date->format('M d, Y')}");
                    $expiredCount++;
                } else {
                    $oldStatus = $property->status;
                    $property->markAsExpired();
                    $expiredCount++;
                    
                    $this->line("✓ Expired: {$property->title} (Status: {$oldStatus} → pending_renewal)");
                    
                    Log::info('Property auto-expired', [
                        'property_id' => $property->id,
                        'property_title' => $property->title,
                        'broker_id' => $property->broker_id,
                        'old_status' => $oldStatus,
                        'expiry_date' => $property->expiry_date->toDateString()
                    ]);
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("✗ Failed to expire property {$property->id}: {$e->getMessage()}");
                Log::error('Property auto-expire failed', [
                    'property_id' => $property->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $this->info("Auto-expire process completed.");
        $this->info("Expired: {$expiredCount}, Errors: {$errorCount}");
        
        return 0;
    }
}
