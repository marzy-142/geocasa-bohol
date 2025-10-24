<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BrokerApplicationStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $applicationData;
    protected $status;
    protected $message;

    public function __construct(array $applicationData, string $status, string $message = null)
    {
        $this->applicationData = $applicationData;
        $this->status = $status;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $statusMessages = [
            'approved' => [
                'subject' => 'Congratulations! Your Broker Application Has Been Approved',
                'greeting' => 'Dear ' . ($this->applicationData['first_name'] ?? 'Applicant') . ',',
                'message' => 'We are pleased to inform you that your broker application has been approved! You are now officially part of our broker network.',
                'next_steps' => [
                    'Complete your profile setup',
                    'Upload your professional photo',
                    'Start accepting client inquiries',
                    'Review our broker guidelines and policies'
                ]
            ],
            'rejected' => [
                'subject' => 'Broker Application Status Update',
                'greeting' => 'Dear ' . ($this->applicationData['first_name'] ?? 'Applicant') . ',',
                'message' => 'After careful review, we regret to inform you that your broker application was not approved at this time.',
                'next_steps' => [
                    'Review the feedback provided',
                    'Address any issues mentioned',
                    'Consider reapplying in the future',
                    'Contact support if you have questions'
                ]
            ],
            'under_review' => [
                'subject' => 'Your Broker Application is Under Review',
                'greeting' => 'Dear ' . ($this->applicationData['first_name'] ?? 'Applicant') . ',',
                'message' => 'Your broker application is currently under review by our team.',
                'next_steps' => [
                    'We will contact you if additional information is needed',
                    'You will receive an update within 3-5 business days',
                    'Please ensure your contact information is up to date'
                ]
            ],
            'requires_documents' => [
                'subject' => 'Additional Documents Required for Your Broker Application',
                'greeting' => 'Dear ' . ($this->applicationData['first_name'] ?? 'Applicant') . ',',
                'message' => 'Your broker application requires additional documents to proceed.',
                'next_steps' => [
                    'Review the required documents list',
                    'Upload the missing documents',
                    'Submit the updated application',
                    'Contact support if you need assistance'
                ]
            ]
        ];

        $config = $statusMessages[$this->status] ?? [
            'subject' => 'Broker Application Status Update',
            'greeting' => 'Dear ' . ($this->applicationData['first_name'] ?? 'Applicant') . ',',
            'message' => 'Your broker application status has been updated.',
            'next_steps' => []
        ];

        $mailMessage = (new MailMessage)
            ->subject($config['subject'])
            ->greeting($config['greeting'])
            ->line($config['message']);

        if ($this->message) {
            $mailMessage->line('**Additional Information:**')
                       ->line($this->message);
        }

        if (!empty($config['next_steps'])) {
            $mailMessage->line('**Next Steps:**');
            foreach ($config['next_steps'] as $step) {
                $mailMessage->line('• ' . $step);
            }
        }

        $mailMessage->line('**Application Details:**')
                   ->line('• **Application ID:** ' . ($this->applicationData['id'] ?? 'N/A'))
                   ->line('• **Status:** ' . ucfirst(str_replace('_', ' ', $this->status)))
                   ->line('• **Updated:** ' . now()->format('M d, Y H:i'));

        if ($this->status === 'approved') {
            $mailMessage->action('Complete Your Profile', url('/broker/dashboard'))
                       ->line('Welcome to our broker network!');
        } elseif ($this->status === 'rejected') {
            $mailMessage->action('Contact Support', url('/contact'))
                       ->line('If you have any questions, please don\'t hesitate to contact our support team.');
        } else {
            $mailMessage->action('View Application Status', url('/broker/application-status'))
                       ->line('Thank you for your patience during the review process.');
        }

        return $mailMessage;
    }
}

