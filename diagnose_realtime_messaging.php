<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n=== REAL-TIME MESSAGING DIAGNOSTICS ===\n\n";

// 1. Check broadcast configuration
echo "1. BROADCAST CONFIGURATION:\n";
echo str_repeat("-", 80) . "\n";

echo "   BROADCAST_DRIVER: " . config('broadcasting.default') . "\n";
echo "   REVERB_APP_ID: " . env('REVERB_APP_ID') . "\n";
echo "   REVERB_APP_KEY: " . env('REVERB_APP_KEY') . "\n";
echo "   REVERB_HOST: " . env('REVERB_HOST') . "\n";
echo "   REVERB_PORT: " . env('REVERB_PORT') . "\n";
echo "   REVERB_SCHEME: " . env('REVERB_SCHEME') . "\n";

echo "\n";

// 2. Check if Reverb server is reachable
echo "2. REVERB SERVER CHECK:\n";
echo str_repeat("-", 80) . "\n";

$reverbHost = env('REVERB_HOST', '127.0.0.1');
$reverbPort = env('REVERB_PORT', 8080);
$reverbScheme = env('REVERB_SCHEME', 'http');

$reverbUrl = "{$reverbScheme}://{$reverbHost}:{$reverbPort}/app/" . env('REVERB_APP_KEY');

echo "   Reverb URL: {$reverbUrl}\n";

$ch = curl_init($reverbUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($httpCode > 0) {
    if ($httpCode == 200 || $httpCode == 404) {
        echo "   ✅ Reverb server is reachable (HTTP {$httpCode})\n";
    } else {
        echo "   ⚠️  Reverb server responded with HTTP {$httpCode}\n";
    }
} else {
    echo "   ❌ Reverb server is NOT reachable\n";
    echo "   Error: {$error}\n";
    echo "\n   💡 To start Reverb, run: php artisan reverb:start\n";
}

echo "\n";

// 3. Check recent messages and broadcasting
echo "3. RECENT MESSAGES:\n";
echo str_repeat("-", 80) . "\n";

$recentMessages = \App\Models\Message::with('sender', 'conversation')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($recentMessages->isEmpty()) {
    echo "   No messages found in the database.\n";
} else {
    echo "   Found {$recentMessages->count()} recent messages:\n\n";
    
    foreach ($recentMessages as $msg) {
        echo "   Message #{$msg->id}:\n";
        echo "   - From: " . ($msg->sender ? $msg->sender->name : 'Unknown') . "\n";
        echo "   - Conversation: #{$msg->conversation_id}\n";
        echo "   - Content: " . substr($msg->content, 0, 50) . (strlen($msg->content) > 50 ? '...' : '') . "\n";
        echo "   - Created: {$msg->created_at}\n";
        echo "\n";
    }
}

echo "\n";

// 4. Check if MessageSent event exists
echo "4. EVENT CLASSES:\n";
echo str_repeat("-", 80) . "\n";

$messageSentExists = class_exists('App\Events\MessageSent');
echo "   MessageSent event: " . ($messageSentExists ? '✅ Exists' : '❌ Missing') . "\n";

if ($messageSentExists) {
    $testMsg = new \App\Models\Message(['conversation_id' => 1]);
    $testMsg->id = 1;
    $event = new \App\Events\MessageSent($testMsg);
    $channels = $event->broadcastOn();
    $channel = is_array($channels) ? $channels[0] : $channels;
    echo "   Broadcast channel: " . $channel->name . "\n";
    echo "   Broadcast as: " . $event->broadcastAs() . "\n";
}

echo "\n";

// 5. Test broadcast functionality
echo "5. BROADCAST TEST:\n";
echo str_repeat("-", 80) . "\n";

try {
    // Create a test message
    $testConversation = \App\Models\Conversation::first();
    
    if ($testConversation) {
        $testMessage = new \App\Models\Message([
            'conversation_id' => $testConversation->id,
            'sender_id' => 1,
            'content' => 'Test message for broadcast check',
            'type' => 'text',
        ]);
        $testMessage->id = 99999; // Fake ID for testing
        
        echo "   Creating test MessageSent event...\n";
        $event = new \App\Events\MessageSent($testMessage);
        
        echo "   ✅ Event created successfully\n";
        echo "   Broadcasting to: conversation.{$testConversation->id}\n";
        
        // Try to broadcast
        try {
            broadcast($event);
            echo "   ✅ broadcast() function executed (check Reverb server logs)\n";
        } catch (\Exception $e) {
            echo "   ❌ Broadcast failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ⚠️  No conversations found to test with\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error during broadcast test: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Check queue configuration
echo "6. QUEUE CONFIGURATION:\n";
echo str_repeat("-", 80) . "\n";

echo "   QUEUE_CONNECTION: " . config('queue.default') . "\n";

if (config('queue.default') !== 'sync') {
    echo "   ⚠️  Queue is not 'sync' - broadcasts might be queued\n";
    echo "   💡 Make sure queue worker is running: php artisan queue:work\n";
} else {
    echo "   ✅ Queue is 'sync' - broadcasts are immediate\n";
}

echo "\n=== END DIAGNOSTICS ===\n\n";
