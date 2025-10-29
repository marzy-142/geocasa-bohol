<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $previousStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction, string $previousStatus, string $newStatus)
    {
        $this->transaction = $transaction->load(['property', 'client', 'broker']);
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Broadcast to client's user channel
        if ($this->transaction->client && $this->transaction->client->user_id) {
            $channels[] = new PrivateChannel('user.' . $this->transaction->client->user_id);
        }
        
        // Broadcast to broker's user channel
        if ($this->transaction->broker) {
            $channels[] = new PrivateChannel('user.' . $this->transaction->broker->id);
        }
        
        // Also broadcast to transaction-specific channel for real-time updates
        $channels[] = new PrivateChannel('transaction.' . $this->transaction->id);
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'TransactionStatusUpdated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'transaction' => [
                'id' => $this->transaction->id,
                'transaction_number' => $this->transaction->transaction_number,
                'status' => $this->transaction->status,
                'status_label' => $this->transaction->status_label,
                'amount' => $this->transaction->amount,
                'formatted_amount' => $this->transaction->formatted_amount,
                // Commission removed from payload; use amount/final price for value displays
                'updated_at' => $this->transaction->updated_at->toISOString(),
                'property' => [
                    'id' => $this->transaction->property->id,
                    'title' => $this->transaction->property->title,
                    'slug' => $this->transaction->property->slug,
                    'address' => $this->transaction->property->address,
                ],
                'client' => [
                    'id' => $this->transaction->client->id,
                    'name' => $this->transaction->client->name,
                    'email' => $this->transaction->client->email,
                ],
                'broker' => [
                    'id' => $this->transaction->broker->id,
                    'name' => $this->transaction->broker->name,
                    'email' => $this->transaction->broker->email,
                ],
            ],
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'message' => "Transaction status updated from {$this->previousStatus} to {$this->newStatus}.",
            'type' => 'transaction_status_updated',
        ];
    }
}