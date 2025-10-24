<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientEngagementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Client $client;
    public array $engagementData;

    public function __construct(Client $client, array $engagementData)
    {
        $this->client = $client;
        $this->engagementData = $engagementData;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Engagement notifications are typically in-app only
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Engagement notifications are typically not sent via email
        // This method is here for completeness but won't be used
        return (new MailMessage)
                    ->subject('Engagement Update')
                    ->greeting('Hello!')
                    ->line('Your engagement status has been updated.')
                    ->salutation('Regards, The GeoCasa Bohol Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'client_id' => $this->client->id,
            'type' => $this->engagementData['type'],
            'data' => $this->engagementData,
            'timestamp' => $this->engagementData['timestamp'],
            'message' => $this->getEngagementMessage($this->engagementData['type']),
            'link' => route('client.transactions.dashboard'),
        ];
    }

    protected function getEngagementMessage(string $type): string
    {
        return match ($type) {
            'low_engagement' => 'We noticed you haven\'t been active recently. Is there anything we can help you with?',
            'high_engagement' => 'Thank you for your active participation in your transactions!',
            'document_uploaded' => 'Great job! You have uploaded documents for your transaction.',
            'approval_submitted' => 'Thank you for your prompt response to the approval request.',
            'feedback_provided' => 'Thank you for providing feedback on your transaction experience.',
            'milestone_achieved' => 'Congratulations on reaching this milestone in your transaction!',
            'meeting_attended' => 'Thank you for attending the scheduled meeting.',
            'transaction_completed' => 'Congratulations on completing your transaction successfully!',
            default => 'Your engagement with the platform has been noted.',
        };
    }
}
