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

class NewInquiryReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inquiry;

    /**
     * Create a new event instance.
     */
    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inquiries'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'inquiry.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'inquiry' => [
                'id' => $this->inquiry->id,
                'name' => $this->inquiry->name,
                'email' => $this->inquiry->email,
                'phone' => $this->inquiry->phone,
                'message' => $this->inquiry->message,
                'inquiry_type' => $this->inquiry->inquiry_type,
                'status' => $this->inquiry->status,
                'created_at' => $this->inquiry->created_at->toISOString(),
                'property' => [
                    'id' => $this->inquiry->property->id,
                    'title' => $this->inquiry->property->title,
                    'municipality' => $this->inquiry->property->municipality,
                    'price' => $this->inquiry->property->price,
                ],
            ],
        ];
    }
}