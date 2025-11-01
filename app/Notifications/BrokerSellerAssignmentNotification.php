<?php

namespace App\Notifications;

use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BrokerSellerAssignmentNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $sellerRequest;
    protected $assignedBy;
    protected $action; // 'assigned' or 'reassigned'

    /**
     * Create a new notification instance.
     */
    public function __construct(SellerRequest $sellerRequest, ?User $assignedBy = null, string $action = 'assigned')
    {
        $this->sellerRequest = $sellerRequest;
        $this->assignedBy = $assignedBy;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        
        $preferences = $notifiable->getNotificationPreferences();
        
        // Check if it's within quiet hours
        if (!$preferences->isWithinQuietHours()) {
            if ($preferences->shouldSendEmail('broker_assignments')) {
                $channels[] = 'mail';
            }
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $actionText = $this->action === 'reassigned' ? 'reassigned to you' : 'assigned to you';
        
        // Build friendly location and area from available fields
        $locationParts = array_filter([
            $this->sellerRequest->address ?? null,
            $this->sellerRequest->city ?? null,
            $this->sellerRequest->province ?? null,
        ]);
        $location = implode(', ', $locationParts);

        $area = $this->sellerRequest->lot_area
            ? number_format((float) $this->sellerRequest->lot_area) . ' sqm'
            : 'N/A';

        return (new MailMessage)
            ->subject('New Seller Request Assignment - ' . $this->sellerRequest->property_title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A seller request has been ' . $actionText . '.')
            ->line('**Property Details:**')
            ->line('Title: ' . $this->sellerRequest->property_title)
            ->line('Location: ' . ($location ?: ''))
            ->line('Asking Price: ₱' . number_format((float) ($this->sellerRequest->asking_price ?? 0)))
            ->line('Property Area: ' . $area)
            ->line('**Seller Contact:**')
            ->line('Name: ' . ($this->sellerRequest->name ?? ''))
            ->line('Email: ' . ($this->sellerRequest->email ?? ''))
            ->line('Phone: ' . ($this->sellerRequest->phone ?? 'Not provided'))
            ->line('Assigned by: ' . ($this->assignedBy ? $this->assignedBy->name : 'System (Auto-assignment)'))
            ->action('View Seller Request', route('seller-requests.show', $this->sellerRequest->id))
            ->line('Please reach out to the seller as soon as possible to begin assisting them with their property listing.');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'broker_seller_assignment',
            'seller_request_id' => $this->sellerRequest->id,
            'seller_name' => $this->sellerRequest->name,
            'property_title' => $this->sellerRequest->property_title,
            'asking_price' => $this->sellerRequest->asking_price,
            'assigned_by' => $this->assignedBy ? $this->assignedBy->name : 'System',
            'action' => $this->action,
            'message' => 'Seller request "' . $this->sellerRequest->property_title . '" has been ' . $this->action . ' to you'
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'broker_seller_assignment',
            'seller_request_id' => $this->sellerRequest->id,
            'seller_name' => $this->sellerRequest->name,
            'seller_email' => $this->sellerRequest->email,
            'seller_phone' => $this->sellerRequest->phone,
            'property_title' => $this->sellerRequest->property_title,
            'property_location' => $location ?? null,
            'asking_price' => $this->sellerRequest->asking_price,
            'assigned_by' => $this->assignedBy ? $this->assignedBy->name : 'System',
            'assigned_by_id' => $this->assignedBy ? $this->assignedBy->id : null,
            'action' => $this->action,
            'message' => 'Seller request "' . $this->sellerRequest->property_title . '" has been ' . $this->action . ' to you'
        ];
    }
}