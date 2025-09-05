<?php

namespace App\Notifications;

use App\Models\SellerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SellerRequestNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $sellerRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(SellerRequest $sellerRequest)
    {
        $this->sellerRequest = $sellerRequest;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Property Listing Request - ' . $this->sellerRequest->property_title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new property listing request has been submitted.')
            ->line('**Property:** ' . $this->sellerRequest->property_title)
            ->line('**Seller:** ' . $this->sellerRequest->seller_name)
            ->line('**Location:** ' . $this->sellerRequest->property_location)
            ->line('**Asking Price:** ₱' . number_format($this->sellerRequest->asking_price))
            ->line('**Area:** ' . $this->sellerRequest->property_area . ' ' . $this->sellerRequest->area_unit)
            ->action('Review Request', route('seller-requests.show', $this->sellerRequest))
            ->line('Please review and process this request.');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'App\\Notifications\\SellerRequestNotification',
            'seller_request_id' => $this->sellerRequest->id,
            'seller_name' => $this->sellerRequest->seller_name,
            'property_title' => $this->sellerRequest->property_title,
            'asking_price' => $this->sellerRequest->asking_price,
            'message' => 'New listing request from ' . $this->sellerRequest->seller_name,
            'created_at' => now()->toISOString()
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'seller_request',
            'seller_request_id' => $this->sellerRequest->id,
            'seller_name' => $this->sellerRequest->seller_name,
            'property_title' => $this->sellerRequest->property_title,
            'asking_price' => $this->sellerRequest->asking_price,
            'message' => 'New listing request from ' . $this->sellerRequest->seller_name
        ];
    }
}