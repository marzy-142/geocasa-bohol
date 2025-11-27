<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokerApplicationStatusChangeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicationData;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(array $applicationData, string $oldStatus, string $newStatus)
    {
        $this->applicationData = $applicationData;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $statusMessages = [
            'pending' => 'is pending review',
            'under_review' => 'is under review',
            'approved' => 'has been approved',
            'rejected' => 'has been rejected',
            'requires_documents' => 'requires additional documents'
        ];

        $message = $statusMessages[$this->newStatus] ?? 'status has changed';

        return (new MailMessage)
            ->subject('Broker Application Status Update')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A broker application status has been updated.')
            ->line('**Application Details:**')
            ->line('• **Name:** ' . ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''))
            ->line('• **Email:** ' . ($this->applicationData['email'] ?? 'N/A'))
            ->line('• **PRC License:** ' . ($this->applicationData['license_number'] ?? 'N/A'))
            ->line('• **Status Change:** ' . ucfirst($this->oldStatus) . ' → ' . ucfirst($this->newStatus))
            ->line('• **Updated:** ' . now()->format('M d, Y H:i'))
            ->line('The application ' . $message . '.')
            ->action('View Application', url('/admin/broker-applications/' . ($this->applicationData['id'] ?? 'unknown')))
            ->line('Thank you for your attention.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'broker_application_status_change',
            'application_id' => $this->applicationData['id'] ?? null,
            'applicant_name' => ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''),
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_at' => now(),
            'message' => 'Broker application status changed: ' . ucfirst($this->oldStatus) . ' → ' . ucfirst($this->newStatus),
            'priority' => $this->newStatus === 'approved' || $this->newStatus === 'rejected' ? 'high' : 'medium',
            'action_url' => '/admin/broker-applications/' . ($this->applicationData['id'] ?? 'unknown')
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'broker_application_status_change',
            'application_id' => $this->applicationData['id'] ?? null,
            'applicant_name' => ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''),
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'updated_at' => now(),
            'message' => 'Broker application status changed: ' . ucfirst($this->oldStatus) . ' → ' . ucfirst($this->newStatus),
            'priority' => $this->newStatus === 'approved' || $this->newStatus === 'rejected' ? 'high' : 'medium'
        ];
    }
}

