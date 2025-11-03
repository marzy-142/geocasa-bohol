<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Conversation;

echo "=== Checking Nina Tejada's Conversation Access ===\n\n";

// Find Nina's user account
$nina = User::where('email', 'like', '%nina%')->orWhere('name', 'like', '%nina%')->first();

if (!$nina) {
    echo "❌ No user found with 'nina' in email or name\n";
    echo "\nLet's check all clients named Nina:\n";
    $clients = Client::where('name', 'like', '%nina%')->get();
    foreach ($clients as $client) {
        echo "\nClient ID: {$client->id}\n";
        echo "Name: {$client->name}\n";
        echo "Email: {$client->email}\n";
        echo "User ID: " . ($client->user_id ?? 'NULL') . "\n";
        
        if ($client->user_id) {
            $user = User::find($client->user_id);
            echo "Linked User: " . ($user ? $user->email : 'NOT FOUND') . "\n";
        }
    }
    exit;
}

echo "✓ Found Nina's user account:\n";
echo "User ID: {$nina->id}\n";
echo "Email: {$nina->email}\n";
echo "Name: {$nina->name}\n";
echo "Role: {$nina->role}\n\n";

// Find Nina's client record
$client = Client::where('user_id', $nina->id)
    ->orWhere('email', $nina->email)
    ->first();

if ($client) {
    echo "✓ Found Nina's client record:\n";
    echo "Client ID: {$client->id}\n";
    echo "Email: {$client->email}\n";
    echo "User ID: " . ($client->user_id ?? 'NULL') . "\n\n";
} else {
    echo "❌ No client record found for Nina\n\n";
}

// Find Nina's inquiries
echo "=== Nina's Inquiries ===\n";
$inquiries = Inquiry::where('email', $nina->email)
    ->orWhere('user_id', $nina->id)
    ->orWhere(function($q) use ($client) {
        if ($client) {
            $q->where('client_id', $client->id);
        }
    })
    ->with(['conversation', 'property'])
    ->get();

if ($inquiries->isEmpty()) {
    echo "❌ No inquiries found for Nina\n";
} else {
    foreach ($inquiries as $inquiry) {
        echo "\n--- Inquiry #{$inquiry->id} ---\n";
        echo "Property: " . ($inquiry->property->title ?? 'N/A') . "\n";
        echo "Email: {$inquiry->email}\n";
        echo "User ID: " . ($inquiry->user_id ?? 'NULL') . "\n";
        echo "Client ID: " . ($inquiry->client_id ?? 'NULL') . "\n";
        echo "Status: {$inquiry->status}\n";
        echo "Broker Response: " . (mb_strlen($inquiry->broker_response ?? '') > 50 ? mb_substr($inquiry->broker_response, 0, 50) . '...' : ($inquiry->broker_response ?? 'NULL')) . "\n";
        
        if ($inquiry->conversation) {
            echo "\n✓ Has Conversation:\n";
            $conv = $inquiry->conversation;
            echo "  Conversation ID: {$conv->id}\n";
            echo "  Participants (JSON): " . json_encode($conv->participants) . "\n";
            
            $pivotParticipants = $conv->participantUsers()->pluck('user_id')->toArray();
            echo "  Participants (Pivot): " . json_encode($pivotParticipants) . "\n";
            
            echo "  Nina in JSON? " . (in_array($nina->id, $conv->participants ?? []) ? 'YES' : 'NO') . "\n";
            echo "  Nina in Pivot? " . (in_array($nina->id, $pivotParticipants) ? 'YES' : 'NO') . "\n";
            
            $messageCount = $conv->messages()->count();
            echo "  Total Messages: {$messageCount}\n";
            
            if ($messageCount > 0) {
                echo "\n  Recent Messages:\n";
                $messages = $conv->messages()->with('sender')->latest()->take(5)->get();
                foreach ($messages as $msg) {
                    $sender = $msg->sender ? $msg->sender->name : 'System';
                    $content = mb_strlen($msg->content) > 50 ? mb_substr($msg->content, 0, 50) . '...' : $msg->content;
                    echo "    - [{$sender}]: {$content}\n";
                }
            }
        } else {
            echo "\n❌ No conversation found for this inquiry\n";
        }
    }
}

// Check if Nina can see conversations
echo "\n\n=== Conversations Visible to Nina ===\n";
$conversations = Conversation::forUser($nina->id)
    ->with(['inquiry.property', 'messages'])
    ->get();

echo "Total conversations: " . $conversations->count() . "\n\n";

foreach ($conversations as $conv) {
    echo "Conversation #{$conv->id}\n";
    echo "  Type: {$conv->type}\n";
    echo "  Property: " . ($conv->inquiry->property->title ?? 'N/A') . "\n";
    echo "  Messages: " . $conv->messages->count() . "\n";
    echo "  Last Message: {$conv->last_message_at}\n\n";
}
