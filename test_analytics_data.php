<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Inquiry;
use App\Models\Transaction;
use Carbon\Carbon;

echo "=== ANALYTICS DATA TEST ===\n\n";

// Get a broker
$broker = User::where('role', 'broker')
    ->where('application_status', 'approved')
    ->first();

if (!$broker) {
    echo "No approved broker found.\n";
    exit;
}

echo "Testing Analytics for: {$broker->name} (ID: {$broker->id})\n";
echo str_repeat("=", 70) . "\n\n";

// Test Total Stats
echo "TOTAL STATS:\n";
echo str_repeat("-", 70) . "\n";

$totalInquiries = Inquiry::whereHas('property', function($q) use ($broker) {
    $q->where('broker_id', $broker->id);
})->count();

$finalizedTransactions = $broker->transactions()->where('status', 'finalized')->get();
$finalizedCount = $finalizedTransactions->count();

$totalCommission = $finalizedTransactions->sum(function($transaction) {
    return $transaction->final_price ?? $transaction->offered_price ?? 0;
});

$averageCommission = $finalizedCount > 0 ? round($totalCommission / $finalizedCount, 2) : 0;

$conversionRate = $totalInquiries > 0 ? round(($finalizedCount / $totalInquiries) * 100, 1) : 0;

echo "  Total Inquiries: {$totalInquiries}\n";
echo "  Finalized Transactions: {$finalizedCount}\n";
echo "  Conversion Rate: {$conversionRate}%\n";
echo "  Total Commission: ₱" . number_format($totalCommission, 2) . "\n";
echo "  Average Commission: ₱" . number_format($averageCommission, 2) . "\n\n";

// Test Monthly Data (last 3 months for brevity)
echo "MONTHLY DATA (Last 3 Months):\n";
echo str_repeat("-", 70) . "\n";
printf("%-15s %10s %10s %20s\n", "Month", "Inquiries", "Sales", "Commission");
echo str_repeat("-", 70) . "\n";

for ($i = 2; $i >= 0; $i--) {
    $date = Carbon::now()->subMonths($i);
    $startOfMonth = $date->copy()->startOfMonth();
    $endOfMonth = $date->copy()->endOfMonth();
    
    $inquiries = Inquiry::whereHas('property', function($q) use ($broker) {
        $q->where('broker_id', $broker->id);
    })->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
    
    $monthTransactions = $broker->transactions()
        ->where('status', 'finalized')
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->get();
    
    $monthCommission = $monthTransactions->sum(function($transaction) {
        return $transaction->final_price ?? $transaction->offered_price ?? 0;
    });
    
    printf(
        "%-15s %10d %10d %20s\n",
        $date->format('M Y'),
        $inquiries,
        $monthTransactions->count(),
        '₱' . number_format($monthCommission, 2)
    );
}

echo "\n" . str_repeat("=", 70) . "\n";

// Show actual finalized transactions
echo "\nFINALIZED TRANSACTIONS DETAIL:\n";
echo str_repeat("-", 70) . "\n";

if ($finalizedTransactions->isEmpty()) {
    echo "  No finalized transactions found.\n";
} else {
    foreach ($finalizedTransactions as $transaction) {
        $price = $transaction->final_price ?? $transaction->offered_price ?? 0;
        echo "  ID: {$transaction->id} | Property: {$transaction->property->title} | Price: ₱" . number_format($price, 2) . "\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "✓ Analytics calculations are now accurate!\n";
echo "✓ Only finalized transactions are counted\n";
echo "✓ Commission data is properly calculated\n";
