<?php

namespace App\Notifications;

use App\Models\Inquiry;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class FollowUpReminderNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $subject;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($subject, string $type)
    {
        $this->subject = $subject;
        $this->type = $type;
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
            if ($preferences->shouldSendEmail('reminders')) {
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
        switch ($this->type) {
            case 'unanswered_inquiry':
                return $this->buildUnansweredInquiryMail($notifiable);
            case 'stale_conversation':
                return $this->buildStaleConversationMail($notifiable);
            default:
                return $this->buildGenericReminderMail($notifiable);
        }
    }

    /**
     * Build mail for unanswered inquiry reminder
     */
    private function buildUnansweredInquiryMail(object $notifiable): MailMessage
    {
        $inquiry = $this->subject;
        $hoursOld = $inquiry->created_at->diffInHours(now());
        
        return (new MailMessage)
            ->subject('Follow-up Reminder: Unanswered Inquiry')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have an unanswered inquiry that needs your attention.')
            ->line('**Property:** ' . $inquiry->property->title)
            ->line('**Client:** ' . $inquiry->client->name)
            ->line('**Inquiry Date:** ' . $inquiry->created_at->format('M j, Y g:i A'))
            ->line('**Time Since Inquiry:** ' . $hoursOld . ' hours ago')
            ->line('**Client Message:** ' . substr($inquiry->message, 0, 150) . (strlen($inquiry->message) > 150 ? '...' : ''))
            ->action('Respond to Inquiry', route('inquiries.show', $inquiry))
            ->line('Prompt responses help maintain client satisfaction and increase conversion rates.');
    }

    /**
     * Build mail for stale conversation reminder
     */
    private function buildStaleConversationMail(object $notifiable): MailMessage
    {
        $conversation = $this->subject;
        $lastMessage = $conversation->messages->first();
        $hoursOld = $lastMessage ? $lastMessage->created_at->diffInHours(now()) : 0;
        
        return (new MailMessage)
            ->subject('Follow-up Reminder: Conversation Needs Attention')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have a conversation that hasn\'t had activity in a while.')
            ->line('**Conversation:** ' . $conversation->title)
            ->line('**Last Activity:** ' . $hoursOld . ' hours ago')
            ->when($lastMessage, function ($mail) use ($lastMessage) {
                return $mail->line('**Last Message From:** ' . $lastMessage->sender->name)
                           ->line('**Message:** ' . substr($lastMessage->content, 0, 150) . (strlen($lastMessage->content) > 150 ? '...' : ''));
            })
            ->action('Continue Conversation', route('conversations.show', $conversation))
            ->line('Keeping conversations active helps build stronger client relationships.');
    }

    /**
     * Build generic reminder mail
     */
    private function buildGenericReminderMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Follow-up Reminder')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have items that need your attention.')
            ->action('View Dashboard', route('dashboard'))
            ->line('Thank you for using GeoCasa Bohol!');
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $data = [
            'id' => $this->id,
            'type' => 'App\\Notifications\\FollowUpReminderNotification',
            'reminder_type' => $this->type,
            'created_at' => now()->toISOString()
        ];

        switch ($this->type) {
            case 'unanswered_inquiry':
                $data['inquiry_id'] = $this->subject->id;
                $data['property_title'] = $this->subject->property->title;
                $data['client_name'] = $this->subject->client->name;
                $data['message'] = 'Reminder: Unanswered inquiry for ' . $this->subject->property->title;
                break;
            case 'stale_conversation':
                $data['conversation_id'] = $this->subject->id;
                $data['conversation_title'] = $this->subject->title;
                $data['message'] = 'Reminder: Conversation needs attention - ' . $this->subject->title;
                break;
            default:
                $data['message'] = 'You have items that need follow-up';
        }

        return new BroadcastMessage($data);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'type' => 'follow_up_reminder',
            'reminder_type' => $this->type,
        ];

        switch ($this->type) {
            case 'unanswered_inquiry':
                $data['inquiry_id'] = $this->subject->id;
                $data['property_title'] = $this->subject->property->title;
                $data['client_name'] = $this->subject->client->name;
                $data['message'] = 'Reminder: Unanswered inquiry for ' . $this->subject->property->title;
                break;
            case 'stale_conversation':
                $data['conversation_id'] = $this->subject->id;
                $data['conversation_title'] = $this->subject->title;
                $data['message'] = 'Reminder: Conversation needs attention - ' . $this->subject->title;
                break;
            default:
                $data['message'] = 'You have items that need follow-up';
        }

        return $data;
    }
}