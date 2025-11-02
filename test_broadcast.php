<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Message;
use App\Events\MessageSent;

echo "\n=== TESTING REAL-TIME BROADCAST ===\n\n";

// Get the most recent message
$message = Message::with('sender')->latest()->first();

if (!$message) {
    echo "❌ No messages found in database. Please send a message first.\n";
    exit;
}

echo "Found message:\n";
echo "  ID: {$message->id}\n";
echo "  From: " . ($message->sender ? $message->sender->name : 'Unknown') . "\n";
echo "  Content: " . substr($message->content, 0, 50) . "\n";
echo "  Conversation: #{$message->conversation_id}\n\n";

echo "Broadcasting MessageSent event...\n";

try {
    // Create and broadcast the event
    $event = new MessageSent($message);
    broadcast($event);
    
    echo "✅ Broadcast triggered successfully!\n";
    echo "   Channel: conversation.{$message->conversation_id}\n";
    echo "   Event: MessageSent\n\n";
    
    echo "If Reverb is running, any clients listening to this conversation\n";
    echo "should receive this message immediately.\n\n";
    
    echo "To test:\n";
    echo "1. Open conversation #{$message->conversation_id} in a browser\n";
    echo "2. Open browser console (F12)\n";
    echo "3. Look for MessageSent event in console logs\n";
    echo "4. The message should appear in the chat without refreshing\n";
    
} catch (\Exception $e) {
    echo "❌ Broadcast failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";
