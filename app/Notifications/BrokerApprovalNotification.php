<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokerApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $approved;

    /**
     * Create a new notification instance.
     */
    public function __construct(bool $approved = true)
    {
        $this->approved = $approved;
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
        if ($this->approved) {
            return (new MailMessage)
                ->subject('Broker Application Approved - Welcome to GeoCasa Bohol!')
                ->greeting('Congratulations ' . $notifiable->name . '!')
                ->line('Your broker application has been approved!')
                ->line('You now have full access to the GeoCasa Bohol broker platform.')
                ->line('You can now:')
                ->line('• List and manage properties')
                ->line('• Manage client relationships')
                ->line('• Track transactions and commissions')
                ->line('• Access advanced broker tools')
                ->action('Access Broker Dashboard', route('broker.dashboard'))
                ->line('Welcome to the GeoCasa Bohol team!');
        } else {
            return (new MailMessage)
                ->subject('Broker Application Status Update')
                ->greeting('Hello ' . $notifiable->name)
                ->line('Thank you for your interest in becoming a broker with GeoCasa Bohol.')
                ->line('After careful review, we are unable to approve your application at this time.')
                ->line('This decision may be based on various factors including current capacity or specific requirements.')
                ->line('You are welcome to reapply in the future.')
                ->line('If you have any questions, please contact our support team.');
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'broker_approval',
            'approved' => $this->approved,
            'message' => $this->approved 
                ? 'Your broker application has been approved!' 
                : 'Your broker application was not approved at this time.'
        ];
    }
}