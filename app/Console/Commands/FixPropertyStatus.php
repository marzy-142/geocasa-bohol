<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\TransactionCompletionService;

class FixPropertyStatus extends Command
{
    protected $signature = 'admin:fix-property-status';
    protected $description = 'Fix property status for finalized transactions';

    public function handle()
    {
        $this->info('🔧 FIXING PROPERTY STATUS FOR FINALIZED TRANSACTIONS');
        $this->info('===================================================');

        // Find finalized transactions with properties not marked as sold
        $transactionsToFix = Transaction::where('status', 'finalized')
            ->whereHas('property', function($query) {
                $query->where('status', '!=', 'sold');
            })
            ->with('property')
            ->get();

        if ($transactionsToFix->count() === 0) {
            $this->info('✅ No transactions need property status fixes.');
            return Command::SUCCESS;
        }

        $this->line("Found {$transactionsToFix->count()} transactions to fix:");
        
        foreach ($transactionsToFix as $transaction) {
            $this->line("- Transaction #{$transaction->id}: Property '{$transaction->property->title}'");
        }

        $this->newLine();

        $completionService = new TransactionCompletionService();
        $fixed = 0;
        $errors = 0;

        foreach ($transactionsToFix as $transaction) {
            try {
                $result = $completionService->completeTransaction($transaction);
                
                if ($result['success']) {
                    $this->info("✅ Fixed Transaction #{$transaction->id}");
                    $fixed++;
                } else {
                    $this->error("❌ Failed to fix Transaction #{$transaction->id}: {$result['message']}");
                    $errors++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Error fixing Transaction #{$transaction->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->newLine();
        $this->info("📊 RESULTS:");
        $this->line("✅ Fixed: {$fixed}");
        $this->line("❌ Errors: {$errors}");

        if ($fixed > 0) {
            $this->newLine();
            $this->info('🎉 Property status fixes completed successfully!');
        }

        return Command::SUCCESS;
    }
}
