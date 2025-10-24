<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class VerifyBrokerDataConsistency extends Command
{
    protected $signature = 'admin:verify-broker-data';
    protected $description = 'Verify broker data consistency across admin interfaces';

    public function handle()
    {
        $this->info('🔍 Verifying Broker Data Consistency...');
        $this->newLine();

        $issues = [];
        $brokers = User::approvedBrokers()->get();

        foreach ($brokers as $broker) {
            $this->line("Checking broker: {$broker->name} (ID: {$broker->id})");

            // Check transaction counts
            $totalTransactions = $broker->transactions()->count();
            $finalizedTransactions = $broker->transactions()->where('status', 'finalized')->count();
            $completedTransactions = $broker->transactions()->where('status', 'completed')->count();

            if ($completedTransactions > 0) {
                $issues[] = [
                    'broker' => $broker->name,
                    'issue' => "Has {$completedTransactions} transactions with 'completed' status (should be 'finalized')",
                    'type' => 'status_inconsistency'
                ];
            }

            // Check commission calculations
            $totalCommission = $broker->transactions()->where('status', 'finalized')->sum('commission_amount');
            $oldCommission = $broker->transactions()->where('status', 'completed')->sum('commission_amount');

            if ($oldCommission > 0) {
                $issues[] = [
                    'broker' => $broker->name,
                    'issue' => "Commission calculation includes 'completed' transactions: ₱" . number_format($oldCommission, 2),
                    'type' => 'commission_inconsistency'
                ];
            }

            // Check property counts
            $totalProperties = $broker->properties()->count();
            $soldProperties = $broker->properties()->where('status', 'sold')->count();
            $availableProperties = $broker->properties()->where('status', 'available')->count();

            // Check if finalized transactions have properties marked as sold
            $finalizedTransactionProperties = $broker->transactions()
                ->where('status', 'finalized')
                ->with('property')
                ->get()
                ->pluck('property')
                ->filter()
                ->unique('id')
                ->count();

            // Check for transactions with properties not marked as sold
            $transactionsWithUnsoldProperties = $broker->transactions()
                ->where('status', 'finalized')
                ->with('property')
                ->get()
                ->filter(function ($transaction) {
                    return $transaction->property && $transaction->property->status !== 'sold';
                });

            if ($transactionsWithUnsoldProperties->count() > 0) {
                $issues[] = [
                    'broker' => $broker->name,
                    'issue' => "Found {$transactionsWithUnsoldProperties->count()} finalized transactions with properties not marked as 'sold'",
                    'type' => 'property_status_mismatch'
                ];
            }

            $this->info("  ✓ Total Transactions: {$totalTransactions}");
            $this->info("  ✓ Finalized Transactions: {$finalizedTransactions}");
            $this->info("  ✓ Total Commission: ₱" . number_format($totalCommission, 2));
            $this->info("  ✓ Total Properties: {$totalProperties}");
            $this->info("  ✓ Sold Properties: {$soldProperties}");
            $this->newLine();
        }

        // Check overall statistics consistency
        $this->info('📊 Checking Overall Statistics Consistency...');
        $this->newLine();

        $totalBrokers = User::approvedBrokers()->count();
        $totalFinalizedTransactions = Transaction::where('status', 'finalized')->count();
        $totalCompletedTransactions = Transaction::where('status', 'completed')->count();
        $totalCommission = Transaction::where('status', 'finalized')->sum('commission_amount');

        $this->info("Total Approved Brokers: {$totalBrokers}");
        $this->info("Total Finalized Transactions: {$totalFinalizedTransactions}");
        $this->info("Total Completed Transactions: {$totalCompletedTransactions}");
        $this->info("Total Commission (Finalized): ₱" . number_format($totalCommission, 2));

        if ($totalCompletedTransactions > 0) {
            $issues[] = [
                'broker' => 'System-wide',
                'issue' => "Found {$totalCompletedTransactions} transactions with 'completed' status system-wide",
                'type' => 'system_status_inconsistency'
            ];
        }

        // Check top broker calculations
        $this->info('🏆 Checking Top Broker Calculations...');
        $this->newLine();

        $topBrokerBySales = User::approvedBrokers()
            ->withCount([
                'transactions as total_sales' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ])
            ->orderByDesc('total_sales')
            ->first();

        $topBrokerByCommission = User::approvedBrokers()
            ->withSum([
                'transactions as total_commission' => function ($query) {
                    $query->where('status', 'finalized');
                }
            ], 'commission_amount')
            ->orderByDesc('total_commission')
            ->first();

        if ($topBrokerBySales) {
            $this->info("Top Broker by Sales: {$topBrokerBySales->name} ({$topBrokerBySales->total_sales} sales)");
        }

        if ($topBrokerByCommission) {
            $this->info("Top Broker by Commission: {$topBrokerByCommission->name} (₱" . number_format($topBrokerByCommission->total_commission ?? 0, 2) . ")");
        }

        // Summary
        $this->newLine();
        if (empty($issues)) {
            $this->info('✅ All broker data is consistent!');
        } else {
            $this->error('❌ Found ' . count($issues) . ' data consistency issues:');
            $this->newLine();

            foreach ($issues as $issue) {
                $this->error("• {$issue['broker']}: {$issue['issue']}");
            }

            $this->newLine();
            $this->warn('Recommendations:');
            $this->line('1. Update any transactions with "completed" status to "finalized"');
            $this->line('2. Ensure property statuses match their associated transactions');
            $this->line('3. Verify commission calculations use "finalized" status only');
        }

        return empty($issues) ? 0 : 1;
    }
}
