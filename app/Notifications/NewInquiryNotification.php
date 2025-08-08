<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewInquiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $inquiry;

    /**
     * Create a new notification instance.
     */
    public function __construct(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Property Inquiry - ' . $this->inquiry->property->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new inquiry for your property.')
            ->line('**Property:** ' . $this->inquiry->property->title)
            ->line('**Client:** ' . $this->inquiry->client->name)
            ->line('**Email:** ' . $this->inquiry->client->email)
            ->line('**Phone:** ' . ($this->inquiry->client->phone ?? 'Not provided'))
            ->line('**Message:** ' . $this->inquiry->message)
            ->action('View Inquiry', route('inquiries.show', $this->inquiry))
            ->line('Please respond to this inquiry as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_inquiry',
            'inquiry_id' => $this->inquiry->id,
            'property_id' => $this->inquiry->property_id,
            'client_name' => $this->inquiry->client->name,
            'property_title' => $this->inquiry->property->title,
            'message' => 'New inquiry received for ' . $this->inquiry->property->title
        ];
    }
}
