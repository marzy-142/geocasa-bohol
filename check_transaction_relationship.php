<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inquiry;
use App\Models\Transaction;

echo "=== Checking Transaction Relationship ===\n\n";

// Check inquiry 36
$inquiry = Inquiry::find(36);

echo "Inquiry ID: " . $inquiry->id . "\n";
echo "Inquiry Status: " . $inquiry->status . "\n";
echo "Transaction ID column: " . ($inquiry->transaction_id ?? 'NULL') . "\n\n";

// Try to load the transaction
$transaction = $inquiry->transaction;

echo "Has transaction (via relationship): " . ($transaction ? 'YES' : 'NO') . "\n";

if ($transaction) {
    echo "Transaction Number: " . $transaction->transaction_number . "\n";
    echo "Transaction Status: " . $transaction->status . "\n";
} else {
    echo "\nLet's check if transaction exists in DB:\n";
    $dbTransaction = Transaction::where('inquiry_id', 36)->first();
    
    if ($dbTransaction) {
        echo "⚠️ Transaction EXISTS in database but relationship is broken!\n";
        echo "Transaction Number: " . $dbTransaction->transaction_number . "\n";
        echo "Transaction inquiry_id: " . $dbTransaction->inquiry_id . "\n";
        
        // Check the relationship definition
        echo "\n=== Checking Relationship Definition ===\n";
        echo "Let's check if there's a transaction_id column on inquiries table...\n";
    } else {
        echo "❌ No transaction found with inquiry_id = 36\n";
    }
}
