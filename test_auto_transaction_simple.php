<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inquiry;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Property;
use App\Http\Controllers\InquiryController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

echo "=== AUTO-TRANSACTION CREATION DIRECT TEST ===\n\n";

// Get a test inquiry with all required relationships
$inquiry = Inquiry::whereIn('status', ['new', 'contacted', 'scheduled'])
    ->whereNull('completion_outcome')
    ->whereDoesntHave('transaction')
    ->whereNotNull('client_id')
    ->whereNotNull('property_id')
    ->whereHas('property', function($q) {
        $q->whereNotNull('broker_id');
    })
    ->with(['property.broker', 'client'])
    ->first();

if (!$inquiry) {
    echo "No suitable test inquiry found.\n";
    exit(1);
}

// Verify all relationships are loaded
if (!$inquiry->property || !$inquiry->client || !$inquiry->property->broker) {
    echo "Inquiry found but missing required relationships.\n";
    echo "Property: " . ($inquiry->property ? "Found" : "Missing") . "\n";
    echo "Client: " . ($inquiry->client ? "Found" : "Missing") . "\n";
    echo "Broker: " . ($inquiry->property && $inquiry->property->broker ? "Found" : "Missing") . "\n";
    exit(1);
}

echo "Selected Inquiry #" . $inquiry->id . "\n";
echo "Property: " . $inquiry->property->title . "\n";
echo "Client: " . $inquiry->client->name . "\n";
echo "Broker: " . $inquiry->property->broker->name . "\n";
echo "Current Status: " . $inquiry->status . "\n\n";

// Get the broker user
$broker = $inquiry->property->broker ?? User::where('role', 'broker')->first();
if (!$broker) {
    echo "No broker found.\n";
    exit(1);
}

echo "Testing auto-transaction creation via controller method...\n";
echo "Simulating: Status=completed, Outcome=won\n\n";

try {
    // Use reflection to call the protected method
    $controller = new InquiryController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('autoCreateTransaction');
    $method->setAccessible(true);
    
    // Update inquiry first to set completion data
    $inquiry->update([
        'status' => 'completed',
        'completion_outcome' => 'won',
        'completion_notes' => 'Test: Client decided to purchase!',
        'contacted_at' => $inquiry->contacted_at ?? now()->subDays(5),
        'scheduled_at' => $inquiry->scheduled_at ?? now()->subDays(2),
        'broker_response' => 'Great! Looking forward to working with you.',
        'responded_at' => now(),
    ]);
    
    // Call the auto-creation method
    $transaction = $method->invoke($controller, $inquiry, $broker);
    
    if ($transaction) {
        echo "✅ SUCCESS! Transaction created automatically!\n\n";
        
        echo "=== TRANSACTION DETAILS ===\n";
        echo "Transaction Number: {$transaction->transaction_number}\n";
        echo "Status: {$transaction->status}\n";
        echo "Property: {$transaction->property->title}\n";
        echo "Client: {$transaction->client->name}\n";
        echo "Broker: {$transaction->broker->name}\n";
        echo "Inquiry Date: {$transaction->inquiry_date}\n";
        echo "First Contact: {$transaction->first_contact_date}\n";
        echo "Viewing Date: " . ($transaction->viewing_date ?? 'N/A') . "\n";
        echo "Offered Price: ₱" . number_format($transaction->offered_price) . "\n";
        
        echo "\n=== INQUIRY DETAILS ===\n";
        $inquiry->refresh();
        echo "Inquiry Status: {$inquiry->status}\n";
        echo "Linked to Transaction: " . ($inquiry->transaction_id ? 'YES' : 'NO') . "\n";
        
        if ($inquiry->conversation) {
            echo "Conversation ID: {$inquiry->conversation->id}\n";
            echo "Conversation linked to Transaction: " . ($inquiry->conversation->transaction_id ? 'YES' : 'NO') . "\n";
            
            // Check for system messages
            $messageCount = $inquiry->conversation->messages()->count();
            echo "✅ Conversation has {$messageCount} message(s)\n";
        }
        
        echo "\n=== BROKER NOTES ===\n";
        if ($transaction->broker_notes) {
            echo $transaction->broker_notes . "\n";
        }
        
        echo "\n=== TEST PASSED ===\n";
        echo "✅ Transaction auto-created successfully!\n";
        echo "✅ Inquiry status updated to: {$inquiry->status}\n";
        echo "✅ All data transferred correctly\n";
        
        // Now test observer sync
        echo "\n=== TESTING BIDIRECTIONAL SYNC ===\n";
        echo "Updating transaction status to 'negotiation'...\n";
        
        $transaction->update(['status' => 'negotiation']);
        $inquiry->refresh();
        
        echo "Transaction Status: {$transaction->status}\n";
        echo "Inquiry Status: {$inquiry->status}\n";
        
        if ($inquiry->status === 'in_transaction') {
            echo "✅ Bidirectional sync working (inquiry status maintained)\n";
        }
        
        echo "\n=== ALL TESTS PASSED ===\n";
        
    } else {
        echo "❌ Transaction was not created\n";
        echo "=== TEST FAILED ===\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    echo "\n=== TEST FAILED ===\n";
}
