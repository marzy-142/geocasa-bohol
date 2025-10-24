<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use App\Events\TransactionStatusUpdated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTransactionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the TransactionCreated event.
     */
    public function handleTransactionCreated(TransactionCreated $event): void
    {
        $transaction = $event->transaction;

        // Create notification for client
        Notification::create([
            'user_id' => $transaction->client->user_id,
            'type' => 'transaction_created',
            'title' => 'New Transaction Created',
            'message' => "A new transaction has been created for property: {$transaction->property->title}",
            'data' => [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'amount' => $transaction->formatted_amount,
                'broker_name' => $transaction->broker->name,
            ],
            'action_url' => route('transactions.show', $transaction->id),
        ]);

        // Create notification for broker
        Notification::create([
            'user_id' => $transaction->broker_id,
            'type' => 'transaction_created',
            'title' => 'Transaction Created Successfully',
            'message' => "You have successfully created a transaction for {$transaction->client->name}",
            'data' => [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'amount' => $transaction->formatted_amount,
                'client_name' => $transaction->client->name,
            ],
            'action_url' => route('transactions.show', $transaction->id),
        ]);
    }

    /**
     * Handle the TransactionStatusUpdated event.
     */
    public function handleTransactionStatusUpdated(TransactionStatusUpdated $event): void
    {
        $transaction = $event->transaction;

        // Create notification for client
        Notification::create([
            'user_id' => $transaction->client->user_id,
            'type' => 'transaction_status_updated',
            'title' => 'Transaction Status Updated',
            'message' => "Your transaction status has been updated to: {$transaction->status_label}",
            'data' => [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
                'broker_name' => $transaction->broker->name,
            ],
            'action_url' => route('transactions.show', $transaction->id),
        ]);

        // Create notification for broker
        Notification::create([
            'user_id' => $transaction->broker_id,
            'type' => 'transaction_status_updated',
            'title' => 'Transaction Status Updated',
            'message' => "Transaction status updated to: {$transaction->status_label}",
            'data' => [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'property_title' => $transaction->property->title,
                'previous_status' => $event->previousStatus,
                'new_status' => $event->newStatus,
                'client_name' => $transaction->client->name,
            ],
            'action_url' => route('transactions.show', $transaction->id),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, $exception): void
    {
        // Log the failure or handle it appropriately
        \Log::error('Failed to send transaction notification', [
            'event' => get_class($event),
            'exception' => $exception->getMessage(),
        ]);
    }
}