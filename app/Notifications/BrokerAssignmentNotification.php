<?php

namespace App\Notifications;

use App\Models\Client;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BrokerAssignmentNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $client;
    protected $assignedBy;
    protected $action; // 'assigned' or 'reassigned'

    /**
     * Create a new notification instance.
     */
    public function __construct(Client $client, User $assignedBy, string $action = 'assigned')
    {
        $this->client = $client;
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
        
        return (new MailMessage)
            ->subject('New Client Assignment - ' . $this->client->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A client has been ' . $actionText . '.')
            ->line('**Client Details:**')
            ->line('Name: ' . $this->client->name)
            ->line('Email: ' . $this->client->email)
            ->line('Phone: ' . ($this->client->phone ?? 'Not provided'))
            ->line('Preferred Location: ' . ($this->client->preferred_location ?? 'Not specified'))
            ->line('Budget Range: ₱' . number_format($this->client->budget_min ?? 0) . ' - ₱' . number_format($this->client->budget_max ?? 0))
            ->line('Assigned by: ' . $this->assignedBy->name)
            ->action('View Client Details', route('clients.show', $this->client->id))
            ->line('Please reach out to the client as soon as possible to begin assisting them with their property needs.');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'broker_assignment',
            'client_id' => $this->client->id,
            'client_name' => $this->client->name,
            'assigned_by' => $this->assignedBy->name,
            'action' => $this->action,
            'message' => 'Client ' . $this->client->name . ' has been ' . $this->action . ' to you'
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'broker_assignment',
            'client_id' => $this->client->id,
            'client_name' => $this->client->name,
            'client_email' => $this->client->email,
            'client_phone' => $this->client->phone,
            'assigned_by' => $this->assignedBy->name,
            'assigned_by_id' => $this->assignedBy->id,
            'action' => $this->action,
            'message' => 'Client ' . $this->client->name . ' has been ' . $this->action . ' to you'
        ];
    }
}