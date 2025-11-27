<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokerApplicationDailySummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $summary;

    public function __construct(array $summary)
    {
        $this->summary = $summary;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Daily Broker Application Summary - ' . $this->summary['date'])
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Here is your daily summary of broker applications:')
            ->line('**Today\'s Activity:**')
            ->line('• **New Applications:** ' . $this->summary['new_applications'])
            ->line('• **Pending Applications:** ' . $this->summary['pending_applications'])
            ->line('• **Approved Yesterday:** ' . $this->summary['approved_applications'])
            ->line('• **Rejected Yesterday:** ' . $this->summary['rejected_applications'])
            ->line('**Action Required:**')
            ->line('Please review pending applications and take necessary actions.')
            ->action('View Applications', url('/admin/broker-applications'))
            ->line('Thank you for your attention.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'daily_broker_summary',
            'date' => $this->summary['date'],
            'new_applications' => $this->summary['new_applications'],
            'pending_applications' => $this->summary['pending_applications'],
            'approved_applications' => $this->summary['approved_applications'],
            'rejected_applications' => $this->summary['rejected_applications'],
            'sent_at' => now(),
            'message' => 'Daily broker application summary: ' . $this->summary['new_applications'] . ' new, ' . $this->summary['pending_applications'] . ' pending.',
            'priority' => 'medium',
            'action_url' => '/admin/broker-applications'
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'daily_broker_summary',
            'date' => $this->summary['date'],
            'new_applications' => $this->summary['new_applications'],
            'pending_applications' => $this->summary['pending_applications'],
            'approved_applications' => $this->summary['approved_applications'],
            'rejected_applications' => $this->summary['rejected_applications'],
            'sent_at' => now(),
            'message' => 'Daily broker application summary: ' . $this->summary['new_applications'] . ' new, ' . $this->summary['pending_applications'] . ' pending.',
            'priority' => 'medium'
        ];
    }
}

