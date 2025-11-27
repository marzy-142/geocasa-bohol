<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBrokerApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicationData;

    public function __construct(array $applicationData)
    {
        $this->applicationData = $applicationData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Broker Application - Immediate Review Required')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new broker application has been submitted and requires your review.')
            ->line('**Application Details:**')
            ->line('• **Name:** ' . ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''))
            ->line('• **Email:** ' . ($this->applicationData['email'] ?? 'N/A'))
            ->line('• **Phone:** ' . ($this->applicationData['phone'] ?? 'N/A'))
            ->line('• **PRC License:** ' . ($this->applicationData['license_number'] ?? 'N/A'))
            ->line('• **Experience:** ' . ($this->applicationData['experience_years'] ?? 'N/A') . ' years')
            ->line('• **Submitted:** ' . now()->format('M d, Y H:i'))
            ->action('Review Application', url('/admin/broker-applications/' . ($this->applicationData['id'] ?? 'unknown')))
            ->line('Please review this application promptly to maintain our service standards.')
            ->line('Thank you for your attention to this matter.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'new_broker_application',
            'application_id' => $this->applicationData['id'] ?? null,
            'applicant_name' => ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''),
            'applicant_email' => $this->applicationData['email'] ?? null,
            'license_number' => $this->applicationData['license_number'] ?? null,
            'submitted_at' => now(),
            // Added human-readable message so frontend dropdown & index views don't render blank
            'message' => 'New broker application submitted: ' . (($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? '')),            
            'priority' => 'high',
            'action_url' => '/admin/broker-applications/' . ($this->applicationData['id'] ?? 'unknown')
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_broker_application',
            'application_id' => $this->applicationData['id'] ?? null,
            'applicant_name' => ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''),
            'applicant_email' => $this->applicationData['email'] ?? null,
            'license_number' => $this->applicationData['license_number'] ?? null,
            'submitted_at' => now(),
            'message' => 'New broker application submitted: ' . (($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? '')),
            'priority' => 'high'
        ];
    }
}

