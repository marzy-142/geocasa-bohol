<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncompleteBrokerApplicationsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $incompleteCount;

    public function __construct(int $incompleteCount)
    {
        $this->incompleteCount = $incompleteCount;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Incomplete Broker Applications Require Attention')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('There are incomplete broker applications that may require follow-up.')
            ->line('**Summary:**')
            ->line('• **Incomplete Applications:** ' . $this->incompleteCount)
            ->line('**Recommended Actions:**')
            ->line('1. Review incomplete applications')
            ->line('2. Consider sending reminder emails')
            ->line('3. Clean up abandoned applications')
            ->line('4. Follow up with promising candidates')
            ->action('Review Applications', url('/admin/broker-applications?status=incomplete'))
            ->line('Regular follow-up helps improve conversion rates and user experience.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'incomplete_broker_applications',
            'incomplete_count' => $this->incompleteCount,
            'sent_at' => now(),
            'message' => 'There are ' . $this->incompleteCount . ' incomplete broker applications requiring follow-up.',
            'priority' => 'low',
            'action_url' => '/admin/broker-applications?status=incomplete'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'incomplete_broker_applications',
            'incomplete_count' => $this->incompleteCount,
            'sent_at' => now(),
            'message' => 'There are ' . $this->incompleteCount . ' incomplete broker applications requiring follow-up.',
            'priority' => 'low'
        ];
    }
}

