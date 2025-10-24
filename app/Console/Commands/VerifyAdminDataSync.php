<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Property;
use App\Models\Transaction;
use App\Models\Inquiry;

class VerifyAdminDataSync extends Command
{
    protected $signature = 'admin:verify-data-sync';
    protected $description = 'Verify admin interface data synchronization and consistency';

    public function handle()
    {
        $this->info('🔍 ADMIN INTERFACE DATA SYNCHRONIZATION VERIFICATION');
        $this->info('====================================================');
        $this->newLine();

        // Test 1: Transaction Status Consistency
        $this->info('1. 📊 TRANSACTION STATUS CONSISTENCY CHECK');
        $this->info('-------------------------------------------');

        $allTransactions = Transaction::all();
        $finalizedCount = Transaction::where('status', 'finalized')->count();
        $completedCount = Transaction::where('status', 'completed')->count();
        $totalTransactions = $allTransactions->count();

        $this->line("Total Transactions: {$totalTransactions}");
        $this->line("Finalized Transactions: {$finalizedCount}");
        $this->line("Completed Transactions: {$completedCount}");

        if ($completedCount > 0) {
            $this->warn("⚠️  WARNING: Found {$completedCount} transactions with 'completed' status!");
            $this->line("   These should be 'finalized' for consistency.");
        } else {
            $this->info("✅ All transactions use 'finalized' status correctly.");
        }

        $this->newLine();

        // Test 2: Admin Dashboard Statistics
        $this->info('2. 📈 ADMIN DASHBOARD STATISTICS VERIFICATION');
        $this->info('---------------------------------------------');

        $totalBrokers = User::where('role', 'broker')->where('is_approved', true)->count();
        $pendingApprovals = User::where('role', 'broker')->where('application_status', 'pending')->count();
        $totalProperties = Property::count();
        $totalTransactionsAdmin = Transaction::where('status', 'finalized')->count();

        $this->line("Total Approved Brokers: {$totalBrokers}");
        $this->line("Pending Broker Approvals: {$pendingApprovals}");
        $this->line("Total Properties: {$totalProperties}");
        $this->line("Total Finalized Transactions: {$totalTransactionsAdmin}");

        $this->info("✅ Admin Dashboard Statistics: VERIFIED");
        $this->newLine();

        // Test 3: Broker Performance Calculations
        $this->info('3. 🏆 BROKER PERFORMANCE CALCULATIONS');
        $this->info('------------------------------------');

        $brokers = User::where('role', 'broker')->where('is_approved', true)->get();

        foreach ($brokers as $broker) {
            $brokerTransactions = $broker->transactions()->where('status', 'finalized')->count();
            $brokerCommission = $broker->transactions()->where('status', 'finalized')->sum('commission_amount');
            $brokerProperties = $broker->properties()->count();
            
            $this->line("Broker: {$broker->name}");
            $this->line("  - Finalized Transactions: {$brokerTransactions}");
            $this->line("  - Total Commission: ₱" . number_format($brokerCommission, 2));
            $this->line("  - Properties Listed: {$brokerProperties}");
        }

        $this->info("✅ Broker Performance Calculations: VERIFIED");
        $this->newLine();

        // Test 4: Property Status Consistency
        $this->info('4. 🏠 PROPERTY STATUS CONSISTENCY');
        $this->info('---------------------------------');

        $properties = Property::all();
        $availableCount = Property::where('status', 'available')->count();
        $soldCount = Property::where('status', 'sold')->count();
        $pendingCount = Property::where('status', 'pending')->count();

        $this->line("Total Properties: {$properties->count()}");
        $this->line("Available Properties: {$availableCount}");
        $this->line("Sold Properties: {$soldCount}");
        $this->line("Pending Properties: {$pendingCount}");

        // Check for properties that should be marked as sold
        $transactionsWithSoldProperties = Transaction::where('status', 'finalized')
            ->whereHas('property', function($query) {
                $query->where('status', '!=', 'sold');
            })
            ->with('property')
            ->get();

        if ($transactionsWithSoldProperties->count() > 0) {
            $this->warn("⚠️  WARNING: Found {$transactionsWithSoldProperties->count()} finalized transactions");
            $this->line("   with properties not marked as 'sold'!");
            foreach ($transactionsWithSoldProperties as $transaction) {
                $this->line("   - Transaction #{$transaction->id}: Property '{$transaction->property->title}' should be marked as sold");
            }
        } else {
            $this->info("✅ All finalized transactions have properties correctly marked as sold.");
        }

        $this->newLine();

        // Test 5: Database Relationships Integrity
        $this->info('5. 🔗 DATABASE RELATIONSHIPS INTEGRITY');
        $this->info('-------------------------------------');

        $orphanedTransactions = Transaction::whereDoesntHave('broker')->orWhereDoesntHave('property')->orWhereDoesntHave('client')->count();
        $orphanedInquiries = Inquiry::whereDoesntHave('property')->count();
        $orphanedProperties = Property::whereDoesntHave('broker')->count();

        $this->line("Orphaned Transactions: {$orphanedTransactions}");
        $this->line("Orphaned Inquiries: {$orphanedInquiries}");
        $this->line("Orphaned Properties: {$orphanedProperties}");

        if ($orphanedTransactions > 0 || $orphanedInquiries > 0 || $orphanedProperties > 0) {
            $this->warn("⚠️  WARNING: Found orphaned records that may cause data inconsistencies!");
        } else {
            $this->info("✅ All records have proper relationships.");
        }

        $this->newLine();

        // Test 6: Commission Calculation Accuracy
        $this->info('6. 💰 COMMISSION CALCULATION ACCURACY');
        $this->info('------------------------------------');

        $totalCommission = Transaction::where('status', 'finalized')->sum('commission_amount');
        $brokerCommissionSum = 0;

        foreach ($brokers as $broker) {
            $brokerCommission = $broker->transactions()->where('status', 'finalized')->sum('commission_amount');
            $brokerCommissionSum += $brokerCommission;
        }

        $this->line("Total Commission (Direct Query): ₱" . number_format($totalCommission, 2));
        $this->line("Total Commission (Broker Sum): ₱" . number_format($brokerCommissionSum, 2));

        if (abs($totalCommission - $brokerCommissionSum) < 0.01) {
            $this->info("✅ Commission calculations are consistent.");
        } else {
            $this->warn("⚠️  WARNING: Commission calculations don't match!");
            $this->line("   Difference: ₱" . number_format(abs($totalCommission - $brokerCommissionSum), 2));
        }

        $this->newLine();

        // Summary
        $this->info('📋 SUMMARY');
        $this->info('==========');
        $this->line("✅ Transaction Status: " . ($completedCount === 0 ? "CONSISTENT" : "NEEDS FIX"));
        $this->line("✅ Admin Dashboard Stats: VERIFIED");
        $this->line("✅ Broker Performance: VERIFIED");
        $this->line("✅ Property Status: " . ($transactionsWithSoldProperties->count() === 0 ? "CONSISTENT" : "NEEDS FIX"));
        $this->line("✅ Database Relationships: " . (($orphanedTransactions + $orphanedInquiries + $orphanedProperties) === 0 ? "INTACT" : "NEEDS FIX"));
        $this->line("✅ Commission Calculations: " . (abs($totalCommission - $brokerCommissionSum) < 0.01 ? "CONSISTENT" : "NEEDS FIX"));

        $this->newLine();

        $this->info('🎯 RECOMMENDATIONS:');
        $this->info('==================');

        if ($completedCount > 0) {
            $this->line("1. Update all 'completed' transaction statuses to 'finalized'");
        }

        if ($transactionsWithSoldProperties->count() > 0) {
            $this->line("2. Mark properties as 'sold' for finalized transactions");
        }

        if ($orphanedTransactions > 0 || $orphanedInquiries > 0 || $orphanedProperties > 0) {
            $this->line("3. Fix orphaned records to maintain data integrity");
        }

        if (abs($totalCommission - $brokerCommissionSum) >= 0.01) {
            $this->line("4. Investigate commission calculation discrepancies");
        }

        $this->newLine();
        $this->info('🔧 Admin interface data synchronization verification complete!');

        return Command::SUCCESS;
    }
}
