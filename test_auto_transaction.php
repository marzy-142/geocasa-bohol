<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

echo "=== AUTO-TRANSACTION CREATION TEST ===\n\n";

// 1. Check existing inquiries
echo "1. Checking existing inquiries...\n";
$inquiries = Inquiry::with(['property', 'client', 'broker'])
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

echo "Found {$inquiries->count()} recent inquiries:\n\n";
foreach ($inquiries as $inquiry) {
    $hasTransaction = $inquiry->transaction ? 'YES' : 'NO';
    echo "ID: {$inquiry->id}\n";
    echo "  Status: {$inquiry->status}\n";
    echo "  Outcome: " . ($inquiry->completion_outcome ?? 'N/A') . "\n";
    echo "  Property: " . ($inquiry->property->title ?? 'N/A') . "\n";
    echo "  Client: " . ($inquiry->client->name ?? 'N/A') . "\n";
    echo "  Broker: " . ($inquiry->broker->name ?? 'N/A') . "\n";
    echo "  Has Transaction: {$hasTransaction}\n";
    if ($inquiry->transaction) {
        echo "  Transaction #: {$inquiry->transaction->transaction_number}\n";
    }
    echo "\n";
}

// 2. Find an inquiry suitable for testing
echo "\n2. Looking for a test inquiry (new/pending without transaction)...\n";
$testInquiry = Inquiry::whereIn('status', ['new', 'pending', 'contacted'])
    ->whereNull('completion_outcome')
    ->whereDoesntHave('transaction')
    ->with(['property', 'client', 'broker'])
    ->first();

if (!$testInquiry) {
    echo "No suitable test inquiry found. Creating one...\n";
    
    // Get a property, client, and broker for testing
    $property = Property::where('status', 'available')->first();
    $client = User::where('role', 'buyer')->first();
    $broker = User::where('role', 'broker')->first();
    
    if (!$property || !$client || !$broker) {
        echo "ERROR: Missing required data (property, buyer, or broker)\n";
        echo "Property: " . ($property ? "Found" : "Not found") . "\n";
        echo "Client: " . ($client ? "Found" : "Not found") . "\n";
        echo "Broker: " . ($broker ? "Found" : "Not found") . "\n";
        exit(1);
    }
    
    $testInquiry = Inquiry::create([
        'property_id' => $property->id,
        'client_id' => $client->id,
        'broker_id' => $broker->id,
        'status' => 'new',
        'message' => 'Test inquiry for auto-transaction creation',
    ]);
    
    echo "Created test inquiry ID: {$testInquiry->id}\n";
} else {
    echo "Found test inquiry ID: {$testInquiry->id}\n";
}

echo "\nTest Inquiry Details:\n";
echo "  Property: " . ($testInquiry->property->title ?? 'N/A') . "\n";
echo "  Client: " . ($testInquiry->client->name ?? 'N/A') . "\n";
echo "  Broker: " . ($testInquiry->broker->name ?? 'N/A') . "\n";
echo "  Status: {$testInquiry->status}\n";

// Skip if missing required data
if (!$testInquiry->client_id || !$testInquiry->property_id) {
    echo "\nSkipping this inquiry - missing client or property.\n";
    echo "Looking for another inquiry...\n";
    
    $testInquiry = Inquiry::whereIn('status', ['new', 'pending', 'contacted'])
        ->whereNull('completion_outcome')
        ->whereDoesntHave('transaction')
        ->whereNotNull('client_id')
        ->whereNotNull('property_id')
        ->with(['property.broker', 'client', 'broker'])
        ->first();
    
    if (!$testInquiry) {
        echo "No suitable inquiry found. You may need to create one manually.\n";
        exit(1);
    }
    
    echo "\nFound better test inquiry ID: {$testInquiry->id}\n";
    echo "  Property: " . ($testInquiry->property->title ?? 'N/A') . "\n";
    echo "  Client: " . ($testInquiry->client->name ?? 'N/A') . "\n";
    echo "  Broker: " . ($testInquiry->broker->name ?? 'N/A') . "\n";
    echo "  Status: {$testInquiry->status}\n";
}

// 3. Test auto-transaction creation
echo "\n3. Testing auto-transaction creation...\n";
echo "Updating inquiry to status='completed', outcome='won'...\n";

try {
    DB::beginTransaction();
    
    $testInquiry->update([
        'status' => 'completed',
        'completion_outcome' => 'won',
        'completion_notes' => 'Test: Client decided to purchase the property!',
        'contacted_at' => now()->subDays(5),
        'scheduled_at' => now()->subDays(2),
    ]);
    
    DB::commit();
    
    echo "✓ Inquiry updated successfully\n";
    
    // Reload the inquiry with transaction
    $testInquiry->refresh();
    $testInquiry->load('transaction');
    
    // Check if transaction was created
    if ($testInquiry->transaction) {
        echo "✓ AUTO-TRANSACTION CREATED SUCCESSFULLY!\n\n";
        
        $transaction = $testInquiry->transaction;
        echo "Transaction Details:\n";
        echo "  Transaction #: {$transaction->transaction_number}\n";
        echo "  Status: {$transaction->status}\n";
        echo "  Property ID: {$transaction->property_id}\n";
        echo "  Client ID: {$transaction->client_id}\n";
        echo "  Broker ID: {$transaction->broker_id}\n";
        echo "  Viewing Date: " . ($transaction->viewing_date ?? 'N/A') . "\n";
        echo "  Created: {$transaction->created_at}\n";
        
        if ($transaction->broker_notes) {
            echo "\n  Broker Notes:\n";
            echo "  " . str_replace("\n", "\n  ", $transaction->broker_notes) . "\n";
        }
        
        // Check inquiry status
        echo "\n✓ Inquiry status updated to: {$testInquiry->status}\n";
        
        // Check conversation link
        if ($transaction->conversation_id) {
            echo "✓ Conversation linked to transaction (ID: {$transaction->conversation_id})\n";
        }
        
        echo "\n=== TEST PASSED ===\n";
        echo "Auto-transaction creation is working correctly!\n";
        
    } else {
        echo "✗ TRANSACTION NOT CREATED\n";
        echo "This might indicate an issue with the auto-creation logic.\n";
        
        // Check logs
        echo "\nCheck Laravel logs at: storage/logs/laravel.log\n";
        echo "\n=== TEST FAILED ===\n";
    }
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\n=== TEST FAILED ===\n";
}

echo "\n4. Testing bidirectional sync (Transaction → Inquiry)...\n";
if (isset($transaction)) {
    echo "Updating transaction status to 'negotiation'...\n";
    
    try {
        $transaction->update(['status' => 'negotiation']);
        $testInquiry->refresh();
        
        echo "✓ Transaction updated\n";
        echo "Inquiry status after sync: {$testInquiry->status}\n";
        
        if ($testInquiry->status === 'in_transaction') {
            echo "✓ Status sync working (inquiry remained 'in_transaction')\n";
        }
        
    } catch (\Exception $e) {
        echo "✗ Sync error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
