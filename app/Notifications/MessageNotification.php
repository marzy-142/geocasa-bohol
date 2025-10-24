<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class MessageNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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
            if ($preferences->shouldSendEmail('messages')) {
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
        $conversation = $this->message->conversation;
        $sender = $this->message->sender;
        
        return (new MailMessage)
            ->subject('New Message - ' . $conversation->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have received a new message.')
            ->line('**From:** ' . $sender->name)
            ->line('**Conversation:** ' . $conversation->title)
            ->line('**Message:** ' . substr($this->message->content, 0, 100) . (strlen($this->message->content) > 100 ? '...' : ''))
            ->action('View Conversation', route('conversations.show', $conversation))
            ->line('Please respond to continue the conversation.');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'type' => 'App\\Notifications\\MessageNotification',
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_name' => $this->message->sender->name,
            'conversation_title' => $this->message->conversation->title,
            'message' => 'New message from ' . $this->message->sender->name,
            'created_at' => now()->toISOString()
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_name' => $this->message->sender->name,
            'conversation_title' => $this->message->conversation->title,
            'message' => 'New message from ' . $this->message->sender->name
        ];
    }
}