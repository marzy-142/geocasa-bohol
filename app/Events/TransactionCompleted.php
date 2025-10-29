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

class TransactionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;
    public $completionData;

    /**
     * Create a new event instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->completionData = [
            'transaction_id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'property_title' => $transaction->property->title ?? 'Unknown Property',
            'client_name' => $transaction->client->name ?? 'Unknown Client',
            'broker_name' => $transaction->broker->name ?? 'Unknown Broker',
            'final_price' => $transaction->final_price ?? $transaction->offered_price,
            'completion_date' => now()->toISOString(),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transaction.' . $this->transaction->id),
            new PrivateChannel('broker.' . $this->transaction->broker_id),
            new Channel('admin.transactions'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'transaction.completed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return $this->completionData;
    }
}
