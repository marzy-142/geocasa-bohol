<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ClientApprovalResponseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $transaction;
    public $approvalId;
    public $approved;
    public $notes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaction $transaction, string $approvalId, bool $approved, string $notes = null)
    {
        $this->transaction = $transaction;
        $this->approvalId = $approvalId;
        $this->approved = $approved;
        $this->notes = $notes;
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
        $status = $this->approved ? 'approved' : 'rejected';
        $statusColor = $this->approved ? 'success' : 'danger';
        
        return (new MailMessage)
            ->subject("Client Response: {$status} - {$this->transaction->property->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your client has **{$status}** your request for: **{$this->getApprovalTypeLabel()}**")
            ->line("**Property:** {$this->transaction->property->title}")
            ->line("**Transaction:** {$this->transaction->transaction_number}")
            ->line("**Status:** " . ($this->approved ? '✅ Approved' : '❌ Rejected'))
            ->when($this->notes, function ($message) {
                return $message->line("**Client Notes:** {$this->notes}");
            })
            ->line($this->getNextStepsMessage())
            ->action('View Transaction', route('transactions.show', $this->transaction))
            ->line('Please proceed with the next steps in the transaction process.')
            ->salutation('Best regards, GeoCasa Bohol Team');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Client Response Received',
            'message' => "Client has " . ($this->approved ? 'approved' : 'rejected') . " your request",
            'type' => 'approval_response',
            'transaction_id' => $this->transaction->id,
            'approval_id' => $this->approvalId,
            'approved' => $this->approved,
            'data' => [
                'transaction' => [
                    'id' => $this->transaction->id,
                    'number' => $this->transaction->transaction_number,
                    'property_title' => $this->transaction->property->title,
                ],
                'approval_id' => $this->approvalId,
                'approved' => $this->approved,
                'notes' => $this->notes,
            ],
        ];
    }

    /**
     * Get approval type label
     */
    private function getApprovalTypeLabel(): string
    {
        // Find the approval in the transaction's client_approvals
        $approvals = $this->transaction->client_approvals ?? [];
        
        foreach ($approvals as $approval) {
            if ($approval['id'] === $this->approvalId) {
                return match ($approval['type']) {
                    'offer_submission' => 'Offer Submission',
                    'contract_review' => 'Contract Review',
                    'price_negotiation' => 'Price Negotiation',
                    'property_viewing' => 'Property Viewing',
                    'final_approval' => 'Final Approval',
                    'document_approval' => 'Document Approval',
                    default => ucwords(str_replace('_', ' ', $approval['type'])),
                };
            }
        }
        
        return 'Request';
    }

    /**
     * Get next steps message
     */
    private function getNextStepsMessage(): string
    {
        if ($this->approved) {
            return match ($this->getApprovalTypeLabel()) {
                'Offer Submission' => 'The offer has been approved. Please proceed with submitting it to the seller.',
                'Contract Review' => 'The contract has been approved. Please proceed with signing and submission.',
                'Price Negotiation' => 'The counter-offer has been approved. Please proceed with the negotiation.',
                'Property Viewing' => 'The viewing has been approved. Please confirm the appointment with the client.',
                'Final Approval' => 'Final approval received. Please proceed with closing preparation.',
                default => 'The request has been approved. Please proceed with the next steps.',
            };
        } else {
            return match ($this->getApprovalTypeLabel()) {
                'Offer Submission' => 'The offer was rejected. Please review the terms with the client and prepare a new offer.',
                'Contract Review' => 'The contract was rejected. Please address the client\'s concerns and prepare a revised contract.',
                'Price Negotiation' => 'The counter-offer was rejected. Please discuss alternatives with the client.',
                'Property Viewing' => 'The viewing was rejected. Please reschedule or discuss alternative arrangements.',
                'Final Approval' => 'Final approval was not given. Please address any remaining concerns.',
                default => 'The request was rejected. Please review and discuss alternatives with the client.',
            };
        }
    }
}