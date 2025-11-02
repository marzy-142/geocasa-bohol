<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inquiry;

echo "=== INQUIRIES WITH TRANSACTIONS ===\n\n";

$inquiries = Inquiry::has('transaction')
    ->with(['property', 'client', 'transaction'])
    ->get();

if ($inquiries->isEmpty()) {
    echo "No inquiries with transactions found.\n";
    echo "Creating a test transaction from an existing inquiry...\n\n";
    
    // Find an inquiry we can use
    $testInquiry = Inquiry::whereIn('status', ['completed', 'in_transaction'])
        ->whereNotNull('client_id')
        ->whereNotNull('property_id')
        ->with(['property.broker', 'client'])
        ->first();
    
    if (!$testInquiry) {
        echo "No suitable inquiries found to test with.\n";
        exit(1);
    }
    
    echo "Found inquiry #{$testInquiry->id} to test with\n";
    echo "You can view it at: /inquiries/{$testInquiry->id}\n\n";
    
    if ($testInquiry->transaction) {
        echo "This inquiry already has a transaction!\n";
        echo "Transaction #: {$testInquiry->transaction->transaction_number}\n";
    }
    
    exit(0);
}

echo "Found {$inquiries->count()} inquiries with transactions:\n\n";

foreach ($inquiries as $inquiry) {
    echo "Inquiry ID: {$inquiry->id}\n";
    echo "  Status: {$inquiry->status}\n";
    echo "  Property: " . ($inquiry->property->title ?? 'N/A') . "\n";
    echo "  Client: " . ($inquiry->client->name ?? 'N/A') . "\n";
    echo "  Transaction #: {$inquiry->transaction->transaction_number}\n";
    echo "  Transaction Status: {$inquiry->transaction->status}\n";
    echo "  View at: http://localhost:8000/inquiries/{$inquiry->id}\n";
    echo "\n";
}

echo "=== TEST THESE URLS ===\n";
foreach ($inquiries->take(3) as $inquiry) {
    echo "http://localhost:8000/inquiries/{$inquiry->id}\n";
}
