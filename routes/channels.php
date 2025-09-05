<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

// User private channel for notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    Log::info('User channel auth attempt', ['user_id' => $user->id, 'requested_id' => $id]);
    return (int) $user->id === (int) $id;
});

// Other channels as needed
Broadcast::channel('inquiries', function ($user) {
    Log::info('Inquiries channel auth attempt', ['user_id' => $user->id, 'role' => $user->role]);
    return $user->role === 'broker' || $user->role === 'admin';
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    Log::info('Conversation channel auth attempt', ['user_id' => $user->id, 'conversation_id' => $conversationId]);
    // Add your conversation access logic here
    return true; // Adjust based on your business logic
});