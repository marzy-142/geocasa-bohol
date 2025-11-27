<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedPRCVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicationData;
    protected $verificationResult;

    public function __construct(array $applicationData, array $verificationResult)
    {
        $this->applicationData = $applicationData;
        $this->verificationResult = $verificationResult;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('URGENT: PRC Verification Failed - Immediate Review Required')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('⚠️ **URGENT ATTENTION REQUIRED**')
            ->line('A broker application has failed PRC license verification and requires immediate review.')
            ->line('**Application Details:**')
            ->line('• **Name:** ' . ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''))
            ->line('• **Email:** ' . ($this->applicationData['email'] ?? 'N/A'))
            ->line('• **Phone:** ' . ($this->applicationData['phone'] ?? 'N/A'))
            ->line('• **PRC License:** ' . ($this->applicationData['license_number'] ?? 'N/A'))
            ->line('• **Submitted:** ' . now()->format('M d, Y H:i'))
            ->line('**Verification Error:**')
            ->line($this->verificationResult['error'] ?? 'Unknown error')
            ->action('Review Application', url('/admin/broker-applications/' . ($this->applicationData['id'] ?? 'unknown')))
            ->line('**Action Required:**')
            ->line('1. Review the application manually')
            ->line('2. Contact the applicant if needed')
            ->line('3. Update application status')
            ->line('This requires immediate attention to maintain service quality.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'failed_prc_verification',
            'application_id' => $this->applicationData['id'] ?? null,
            'applicant_name' => ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''),
            'license_number' => $this->applicationData['license_number'] ?? null,
            'verification_error' => $this->verificationResult['error'] ?? 'Unknown error',
            'sent_at' => now(),
            'message' => 'PRC verification failed: ' . ($this->verificationResult['error'] ?? 'Unknown error'),
            'priority' => 'urgent',
            'action_url' => '/admin/broker-applications/' . ($this->applicationData['id'] ?? 'unknown')
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'failed_prc_verification',
            'application_id' => $this->applicationData['id'] ?? null,
            'applicant_name' => ($this->applicationData['first_name'] ?? '') . ' ' . ($this->applicationData['last_name'] ?? ''),
            'license_number' => $this->applicationData['license_number'] ?? null,
            'verification_error' => $this->verificationResult['error'] ?? 'Unknown error',
            'sent_at' => now(),
            'message' => 'PRC verification failed: ' . ($this->verificationResult['error'] ?? 'Unknown error'),
            'priority' => 'urgent'
        ];
    }
}

