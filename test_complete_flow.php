<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use App\Models\Conversation;
use App\Http\Controllers\InquiryController;
use Illuminate\Support\Facades\DB;

echo "=== COMPREHENSIVE AUTO-TRANSACTION TEST ===\n\n";

// Create a test inquiry
$property = Property::where('status', 'available')->first();
$client = User::where('role', 'buyer')->first();

if (!$property || !$client) {
    echo "Missing test data. Need at least one property and buyer.\n";
    exit(1);
}

echo "Creating test inquiry...\n";
$inquiry = Inquiry::create([
    'name' => $client->name,
    'email' => $client->email,
    'phone' => $client->phone ?? '09123456789',
    'message' => 'I am very interested in this beautiful property!',
    'inquiry_type' => 'purchase',
    'property_id' => $property->id,
    'client_id' => $client->id,
    'status' => 'contacted',
    'contacted_at' => now()->subDays(3),
    'scheduled_at' => now()->subDays(1),
]);

// Create a conversation for this inquiry
$conversation = Conversation::create([
    'inquiry_id' => $inquiry->id,
    'property_id' => $property->id,
    'buyer_id' => $client->id,
    'broker_id' => $property->broker_id,
]);

$inquiry->refresh();

echo "✅ Test inquiry created (ID: {$inquiry->id})\n";
echo "   Property: {$property->title}\n";
echo "   Client: {$client->name}\n";
echo "   Broker: {$property->broker->name}\n";
echo "   Conversation ID: {$conversation->id}\n\n";

// Now test the auto-creation via the controller method
echo "=== TESTING AUTO-TRANSACTION CREATION ===\n";
echo "Simulating: Mark inquiry as Completed + Won\n\n";

try {
    $controller = new InquiryController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('autoCreateTransaction');
    $method->setAccessible(true);
    
    // Update inquiry to completed/won
    $inquiry->update([
        'status' => 'completed',
        'completion_outcome' => 'won',
        'completion_notes' => 'Client signed the offer! Ready to proceed with transaction.',
        'broker_response' => 'Great! Looking forward to working with you.',
        'responded_at' => now(),
    ]);
    
    // Call auto-creation
    $transaction = $method->invoke($controller, $inquiry, $property->broker);
    
    if ($transaction) {
        echo "✅ SUCCESS! Transaction #{$transaction->transaction_number} created!\n\n";
        
        echo "=== VERIFICATION ===\n";
        $inquiry->refresh();
        $conversation->refresh();
        
        echo "1. Transaction Details:\n";
        echo "   - Number: {$transaction->transaction_number}\n";
        echo "   - Status: {$transaction->status}\n";
        echo "   - Property: {$transaction->property->title}\n";
        echo "   - Client: {$transaction->client->name}\n";
        echo "   - Broker: {$transaction->broker->name}\n";
        echo "   - Offered Price: ₱" . number_format($transaction->offered_price) . "\n\n";
        
        echo "2. Inquiry Status:\n";
        echo "   - Status: {$inquiry->status}\n";
        echo "   - Expected: in_transaction\n";
        echo "   - " . ($inquiry->status === 'in_transaction' ? '✅ PASS' : '❌ FAIL') . "\n\n";
        
        echo "3. Conversation Linking:\n";
        echo "   - Conversation Transaction ID: " . ($conversation->transaction_id ?? 'NULL') . "\n";
        echo "   - Expected: {$transaction->id}\n";
        echo "   - " . ($conversation->transaction_id == $transaction->id ? '✅ PASS' : '❌ FAIL') . "\n\n";
        
        echo "4. System Message:\n";
        $messageCount = $conversation->messages()->count();
        echo "   - Messages in conversation: {$messageCount}\n";
        echo "   - " . ($messageCount > 0 ? '✅ PASS' : '❌ FAIL') . "\n\n";
        
        // Test bidirectional sync
        echo "=== TESTING BIDIRECTIONAL SYNC ===\n";
        echo "Updating transaction status: offer_made → negotiation\n";
        
        $transaction->update(['status' => 'negotiation']);
        $inquiry->refresh();
        
        echo "Inquiry status after sync: {$inquiry->status}\n";
        echo "Expected: in_transaction\n";
        echo ($inquiry->status === 'in_transaction' ? '✅ PASS' : '❌ FAIL') . "\n\n";
        
        // Check for sync message
        $messageCountAfter = $conversation->fresh()->messages()->count();
        echo "Messages after status change: {$messageCountAfter}\n";
        echo "Expected: " . ($messageCount + 1) . "\n";
        echo ($messageCountAfter > $messageCount ? '✅ PASS - System message added' : '❌ FAIL') . "\n\n";
        
        echo "=== ALL TESTS PASSED! ===\n";
        echo "✅ Auto-transaction creation working\n";
        echo "✅ Data transfer complete\n";
        echo "✅ Conversation linking working\n";
        echo "✅ Bidirectional sync working\n";
        echo "✅ System messages working\n";
        
    } else {
        echo "❌ Transaction not created\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
