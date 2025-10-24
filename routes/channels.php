<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

// User private channel for notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    Log::info('User channel auth attempt', ['user_id' => $user->id, 'requested_id' => $id]);
    return (int) $user->id === (int) $id;
});

// User private channel for real-time updates (simplified)
Broadcast::channel('user.{userId}', function ($user, $userId) {
    Log::info('User channel auth attempt', ['user_id' => $user->id, 'requested_user_id' => $userId]);
    return (int) $user->id === (int) $userId;
});

// Client private channel for transaction updates
Broadcast::channel('client.{clientId}', function ($user, $clientId) {
    Log::info('Client channel auth attempt', ['user_id' => $user->id, 'client_id' => $clientId]);
    // Allow access if user is the client or a broker/admin
    return $user->role === 'client' && (int) $user->id === (int) $clientId ||
           $user->role === 'broker' || $user->role === 'admin';
});

// Broker private channel for transaction updates
Broadcast::channel('broker.{brokerId}', function ($user, $brokerId) {
    Log::info('Broker channel auth attempt', ['user_id' => $user->id, 'broker_id' => $brokerId]);
    // Allow access if user is the broker or an admin
    return $user->role === 'broker' && (int) $user->id === (int) $brokerId ||
           $user->role === 'admin';
});

// Transaction-specific channel for real-time updates
Broadcast::channel('transaction.{transactionId}', function ($user, $transactionId) {
    Log::info('Transaction channel auth attempt', ['user_id' => $user->id, 'transaction_id' => $transactionId]);
    
    // Get the transaction to check if user has access
    $transaction = \App\Models\Transaction::find($transactionId);
    if (!$transaction) {
        return false;
    }
    
    // Allow access if user is the client, broker, or admin
    return (int) $user->id === (int) $transaction->client_id ||
           (int) $user->id === (int) $transaction->broker_id ||
           $user->role === 'admin';
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