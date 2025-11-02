<?php

require __DIR__ . '/vendor/autoload.php';

use App\Events\MessageSent;
use App\Models\Message;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the latest message from conversation 5
$latestMessage = Message::where('conversation_id', 5)
    ->with('sender')
    ->orderBy('id', 'desc')
    ->first();

if (!$latestMessage) {
    echo "❌ No messages found in conversation 5\n";
    exit(1);
}

echo "📨 Found message: {$latestMessage->id}\n";
echo "   Sender: {$latestMessage->sender->name}\n";
echo "   Content: " . substr($latestMessage->content, 0, 50) . "\n";
echo "   Channel: conversation.{$latestMessage->conversation_id}\n\n";

echo "🚀 Broadcasting MessageSent event...\n";

// Broadcast the event
broadcast(new MessageSent($latestMessage));

echo "✅ Broadcast sent!\n";
echo "\n📋 Check your browser console for: 📨 New message received via Echo\n";
