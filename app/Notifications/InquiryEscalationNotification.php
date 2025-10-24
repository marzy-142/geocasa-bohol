<?php

namespace App\Notifications;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InquiryEscalationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $inquiry;
    protected $newBroker;

    /**
     * Create a new notification instance.
     */
    public function __construct(Inquiry $inquiry, User $newBroker)
    {
        $this->inquiry = $inquiry;
        $this->newBroker = $newBroker;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inquiry Escalated - Action Required')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('An inquiry has been escalated to you and requires your immediate attention.')
            ->line('**Inquiry Details:**')
            ->line('- Property: ' . $this->inquiry->property->title)
            ->line('- Client: ' . $this->inquiry->name)
            ->line('- Email: ' . $this->inquiry->email)
            ->line('- Phone: ' . $this->inquiry->phone)
            ->line('- Message: ' . $this->inquiry->message)
            ->line('**Reason for Escalation:**')
            ->line('The original assigned broker did not respond within the expected timeframe.')
            ->action('View Inquiry', route('inquiries.show', $this->inquiry))
            ->line('Please respond to this inquiry as soon as possible to maintain our service standards.')
            ->line('Thank you for your attention to this matter.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'inquiry_escalated',
            'inquiry_id' => $this->inquiry->id,
            'inquiry_title' => 'Inquiry Escalated - ' . $this->inquiry->property->title,
            'client_name' => $this->inquiry->name,
            'client_email' => $this->inquiry->email,
            'property_title' => $this->inquiry->property->title,
            'escalation_reason' => 'Original broker did not respond within expected timeframe',
            'action_url' => route('inquiries.show', $this->inquiry),
            'priority' => 'high',
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'inquiry_escalated',
            'inquiry_id' => $this->inquiry->id,
            'inquiry_title' => 'Inquiry Escalated - ' . $this->inquiry->property->title,
            'client_name' => $this->inquiry->name,
            'client_email' => $this->inquiry->email,
            'property_title' => $this->inquiry->property->title,
            'escalation_reason' => 'Original broker did not respond within expected timeframe',
            'action_url' => route('inquiries.show', $this->inquiry),
            'priority' => 'high',
            'created_at' => now(),
        ];
    }
}

