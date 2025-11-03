<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Conversation;
use App\Models\Inquiry;

echo "=== Repairing Conversation Participants ===\n\n";

// Find inquiry #40 (Nina's inquiry)
$inquiry = Inquiry::with(['conversation', 'client.user', 'user'])->find(40);

if (!$inquiry) {
    echo "❌ Inquiry #40 not found\n";
    exit;
}

echo "Found Inquiry #40\n";
echo "Client: " . ($inquiry->client->name ?? 'N/A') . "\n";
echo "Email: {$inquiry->email}\n";
echo "User ID: " . ($inquiry->user_id ?? 'NULL') . "\n";
echo "Client User ID: " . ($inquiry->client->user_id ?? 'NULL') . "\n\n";

if (!$inquiry->conversation) {
    echo "❌ No conversation found for this inquiry\n";
    exit;
}

$conversation = $inquiry->conversation;
echo "Conversation ID: {$conversation->id}\n";
echo "Current Participants (JSON): " . json_encode($conversation->participants) . "\n";

$pivotParticipants = $conversation->participantUsers()->pluck('user_id')->toArray();
echo "Current Participants (Pivot): " . json_encode($pivotParticipants) . "\n\n";

// Determine the client's user ID
$clientUserId = null;

if ($inquiry->user_id) {
    $clientUserId = $inquiry->user_id;
    echo "✓ Using inquiry->user_id: {$clientUserId}\n";
} elseif ($inquiry->client && $inquiry->client->user_id) {
    $clientUserId = $inquiry->client->user_id;
    echo "✓ Using client->user_id: {$clientUserId}\n";
}

if (!$clientUserId) {
    echo "❌ Cannot determine client's user ID\n";
    exit;
}

// Check if already a participant
$participants = $conversation->participants ?? [];
if (in_array($clientUserId, $participants)) {
    echo "✓ Client already in JSON participants\n";
} else {
    echo "Adding client (user #{$clientUserId}) to JSON participants...\n";
    $participants[] = $clientUserId;
    $conversation->participants = array_values(array_unique($participants));
    $conversation->save();
    echo "✓ Updated JSON participants: " . json_encode($conversation->participants) . "\n";
}

// Check pivot table
if (in_array($clientUserId, $pivotParticipants)) {
    echo "✓ Client already in pivot participants\n";
} else {
    echo "Adding client (user #{$clientUserId}) to pivot participants...\n";
    $conversation->participantUsers()->syncWithoutDetaching([$clientUserId]);
    echo "✓ Updated pivot participants\n";
}

// Verify
$conversation->refresh();
$newPivotParticipants = $conversation->participantUsers()->pluck('user_id')->toArray();
echo "\n=== Final State ===\n";
echo "JSON Participants: " . json_encode($conversation->participants) . "\n";
echo "Pivot Participants: " . json_encode($newPivotParticipants) . "\n";

// Test if Nina can now see the conversation
$ninaUserId = 13;
$canSee = Conversation::forUser($ninaUserId)->where('id', $conversation->id)->exists();
echo "\nCan Nina see this conversation? " . ($canSee ? 'YES ✓' : 'NO ❌') . "\n";
