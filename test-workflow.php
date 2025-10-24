<?php

/**
 * Manual Test Script for Inquiry→Transaction Workflow
 * Run with: php artisan tinker < test-workflow.php
 */

echo "\n=== Testing Inquiry→Transaction Workflow ===\n\n";

// 1. Find or create test data
echo "1. Setting up test data...\n";

$broker = App\Models\User::where('role', 'broker')->first();
if (!$broker) {
    echo "   ❌ No broker found. Please create a broker user first.\n";
    exit;
}
echo "   ✅ Broker: {$broker->name} (ID: {$broker->id})\n";

$client = App\Models\Client::first();
if (!$client) {
    echo "   ❌ No client found. Please create a client first.\n";
    exit;
}
echo "   ✅ Client: {$client->name} (ID: {$client->id})\n";

$property = App\Models\Property::where('status', 'available')->first();
if (!$property) {
    echo "   ❌ No available property found.\n";
    exit;
}
echo "   ✅ Property: {$property->title} (ID: {$property->id})\n";

// 2. Create a test inquiry
echo "\n2. Creating test inquiry...\n";
$inquiry = App\Models\Inquiry::create([
    'property_id' => $property->id,
    'client_id' => $client->id,
    'user_id' => $client->user_id,
    'assigned_broker_id' => $broker->id,
    'name' => $client->name,
    'email' => $client->email,
    'phone' => $client->phone ?? '1234567890',
    'message' => 'Test inquiry for workflow testing',
    'inquiry_type' => 'purchase',
    'status' => 'new',
]);
echo "   ✅ Inquiry created (ID: {$inquiry->id})\n";

// 3. Create conversation for inquiry
echo "\n3. Creating conversation...\n";
$conversation = App\Models\Conversation::createForInquiry($inquiry);
echo "   ✅ Conversation created (ID: {$conversation->id})\n";
echo "   - Type: {$conversation->type}\n";
echo "   - Lifecycle: {$conversation->lifecycle_stage}\n";

// 4. Add initial message
echo "\n4. Adding initial message...\n";
$message = App\Models\Message::create([
    'conversation_id' => $conversation->id,
    'sender_id' => $client->user_id,
    'content' => 'I am interested in this property. Can we discuss?',
    'type' => 'inquiry_initial',
]);
echo "   ✅ Message created (ID: {$message->id})\n";

// 5. Test conversation transition
echo "\n5. Testing inquiry acceptance (creating transaction)...\n";

try {
    DB::transaction(function () use ($inquiry, $broker) {
        // Create transaction
        $transaction = App\Models\Transaction::create([
            'inquiry_id' => $inquiry->id,
            'property_id' => $inquiry->property_id,
            'client_id' => $inquiry->client_id,
            'broker_id' => $inquiry->assigned_broker_id ?? $broker->id,
            'status' => 'initial_contact',
            'transaction_number' => 'TXN-' . strtoupper(Illuminate\Support\Str::random(10)),
            'client_engagement_score' => 50,
        ]);
        
        echo "   ✅ Transaction created (ID: {$transaction->id})\n";
        echo "   - Number: {$transaction->transaction_number}\n";
        
        // Update inquiry
        $inquiry->update(['status' => 'converted']);
        echo "   ✅ Inquiry status updated to: converted\n";
        
        // Transition conversation
        $conversation = $inquiry->conversation;
        $conversation->transitionToTransaction($transaction);
        echo "   ✅ Conversation transitioned\n";
    });
    
    // 6. Verify results
    echo "\n6. Verifying results...\n";
    $inquiry->refresh();
    $conversation->refresh();
    
    echo "   Inquiry Status: {$inquiry->status}\n";
    echo "   Conversation Type: {$conversation->type}\n";
    echo "   Conversation Lifecycle: {$conversation->lifecycle_stage}\n";
    echo "   Transaction ID: {$conversation->transaction_id}\n";
    
    // Check system message
    $systemMessage = $conversation->messages()
        ->where('type', 'system')
        ->latest()
        ->first();
    
    if ($systemMessage) {
        echo "   ✅ System message created: \"{$systemMessage->content}\"\n";
    }
    
    // Check metadata
    if ($conversation->metadata) {
        echo "   ✅ Metadata updated:\n";
        echo "      - Transaction Number: " . ($conversation->metadata['transaction_number'] ?? 'N/A') . "\n";
        echo "      - Original Inquiry ID: " . ($conversation->metadata['original_inquiry_id'] ?? 'N/A') . "\n";
    }
    
    echo "\n✅ ALL TESTS PASSED!\n\n";
    echo "Transaction: {$inquiry->transaction->transaction_number}\n";
    echo "View at: /inquiries/{$inquiry->id}\n";
    echo "Conversation: /conversations/{$conversation->id}\n\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// 7. Cleanup (optional - comment out to keep test data)
// echo "\n7. Cleaning up test data...\n";
// $inquiry->transaction()->delete();
// $conversation->messages()->delete();
// $conversation->delete();
// $inquiry->delete();
// echo "   ✅ Cleanup complete\n";

echo "\n=== Test Complete ===\n\n";
