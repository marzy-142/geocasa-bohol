<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ClientApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $approval;
    public $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $approval, Transaction $transaction)
    {
        $this->approval = $approval;
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $approvalType = $this->getApprovalTypeLabel($this->approval['type']);
        $deadline = \Carbon\Carbon::parse($this->approval['deadline'])->format('M j, Y g:i A');
        $propertyTitle = $this->transaction->property?->title ?? 'Unknown Property';
        
        return (new MailMessage)
            ->subject("Action Required: {$approvalType} - {$propertyTitle}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your broker needs your approval for: **{$approvalType}**")
            ->line("**Property:** {$propertyTitle}")
            ->line("**Transaction:** {$this->transaction->transaction_number}")
            ->line("**Response Deadline:** {$deadline}")
            ->line($this->getApprovalDescription($this->approval['type']))
            ->action('Review & Approve', route('client.transactions.show', $this->transaction))
            ->line('Please log in to your account to review the details and provide your approval.')
            ->line('If you have any questions, please contact your broker directly.')
            ->salutation('Best regards, GeoCasa Bohol Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $propertyTitle = $this->transaction->property?->title ?? 'Unknown Property';
        return [
            'title' => 'Approval Required',
            'message' => "Your approval is needed for {$this->getApprovalTypeLabel($this->approval['type'])}",
            'type' => 'approval_required',
            'transaction_id' => $this->transaction->id,
            'approval_id' => $this->approval['id'],
            'deadline' => $this->approval['deadline'],
            'data' => [
                'approval' => $this->approval,
                'transaction' => [
                    'id' => $this->transaction->id,
                    'number' => $this->transaction->transaction_number,
                    'property_title' => $propertyTitle,
                ],
            ],
        ];
    }

    /**
     * Get approval type label
     */
    private function getApprovalTypeLabel(string $type): string
    {
        return match ($type) {
            'offer_submission' => 'Offer Submission',
            'contract_review' => 'Contract Review',
            'price_negotiation' => 'Price Negotiation',
            'property_viewing' => 'Property Viewing',
            'final_approval' => 'Final Approval',
            'document_approval' => 'Document Approval',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Get approval description
     */
    private function getApprovalDescription(string $type): string
    {
        return match ($type) {
            'offer_submission' => 'Your broker has prepared an offer for your consideration. Please review the terms and approve to proceed.',
            'contract_review' => 'The purchase contract is ready for your review. Please review all terms before approval.',
            'price_negotiation' => 'A counter-offer has been received. Please review and approve the new terms.',
            'property_viewing' => 'A property viewing has been scheduled. Please confirm your availability.',
            'final_approval' => 'Final approval is required to proceed with closing. Please review all documents.',
            'document_approval' => 'Additional documents are required. Please review and approve the document requests.',
            default => 'Please review the details and provide your approval to proceed.',
        };
    }
}