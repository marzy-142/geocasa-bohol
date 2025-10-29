<?php

namespace App\Events;

use App\Models\Inquiry;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InquiryStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inquiry;
    public $previousStatus;
    public $newStatus;
    public $updatedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Inquiry $inquiry, $previousStatus, $newStatus, $updatedBy = null)
    {
        $this->inquiry = $inquiry;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->updatedBy = $updatedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('inquiries'),
            new PrivateChannel('inquiry.' . $this->inquiry->id),
        ];

        // Notify the client on their private channel if available
        if (!empty($this->inquiry->client_id)) {
            $channels[] = new PrivateChannel('client.' . $this->inquiry->client_id);
        }

        // Notify the assigned broker as well when available
        $brokerId = $this->inquiry->assigned_broker_id
            ?? ($this->inquiry->property->broker_id ?? null);
        if (!empty($brokerId)) {
            $channels[] = new PrivateChannel('broker.' . $brokerId);
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'inquiry.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'inquiry_id' => $this->inquiry->id,
            'previous_status' => $this->previousStatus,
            'new_status' => $this->newStatus,
            'updated_by' => $this->updatedBy,
            'updated_at' => now()->toISOString(),
            'inquiry' => [
                'id' => $this->inquiry->id,
                'name' => $this->inquiry->name,
                'email' => $this->inquiry->email,
                'status' => $this->inquiry->status,
                'property' => [
                    'id' => $this->inquiry->property->id,
                    'title' => $this->inquiry->property->title,
                ],
            ],
        ];
    }
}