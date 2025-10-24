<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OverdueApprovalNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $transaction;
    protected $approval;
    protected $escalationType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaction $transaction, array $approval, string $escalationType)
    {
        $this->transaction = $transaction->load(['property', 'client']);
        $this->approval = $approval;
        $this->escalationType = $escalationType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];
        
        // Always send email for overdue approvals
        $channels[] = 'mail';
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->getSubject();
        $greeting = "Urgent: Client Approval Overdue";
        
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("A client approval for transaction #{$this->transaction->transaction_number} has passed its deadline.")
            ->line("Property: {$this->transaction->property->title}")
            ->line("Client: {$this->transaction->client->name}")
            ->line("Approval Type: {$this->getApprovalTypeDisplay()}")
            ->line("Deadline: " . \Carbon\Carbon::parse($this->approval['deadline'])->format('M j, Y \a\t g:i A'))
            ->line("Days Overdue: " . $this->getDaysOverdue());

        // Add escalation-specific content
        switch ($this->escalationType) {
            case 'offer_submission_overdue':
                $mailMessage->line("The client has not responded to an offer submission. Consider following up directly with the client.")
                    ->action('View Transaction', route('transactions.show', $this->transaction))
                    ->line('Recommended Actions:')
                    ->line('• Contact client directly')
                    ->line('• Review offer terms')
                    ->line('• Consider offer modification');
                break;
                
            case 'final_approval_overdue':
                $mailMessage->line("Final approval is overdue. This is a critical step that requires immediate attention.")
                    ->action('View Transaction', route('transactions.show', $this->transaction))
                    ->line('Critical Actions Required:')
                    ->line('• Contact client immediately')
                    ->line('• Check for any issues')
                    ->line('• Consider administrative intervention');
                break;
                
            default:
                $mailMessage->line("Please follow up with the client regarding this overdue approval.")
                    ->action('View Transaction', route('transactions.show', $this->transaction));
        }

        return $mailMessage;
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'overdue_approval',
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'approval_type' => $this->approval['type'],
            'escalation_type' => $this->escalationType,
            'days_overdue' => $this->getDaysOverdue(),
            'deadline' => $this->approval['deadline'],
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'overdue_approval',
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'client_name' => $this->transaction->client->name,
            'approval_type' => $this->approval['type'],
            'approval_id' => $this->approval['id'],
            'escalation_type' => $this->escalationType,
            'days_overdue' => $this->getDaysOverdue(),
            'deadline' => $this->approval['deadline'],
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get the notification subject
     */
    private function getSubject(): string
    {
        return match($this->escalationType) {
            'offer_submission_overdue' => "Urgent: Offer Approval Overdue - {$this->transaction->transaction_number}",
            'final_approval_overdue' => "CRITICAL: Final Approval Overdue - {$this->transaction->transaction_number}",
            default => "Client Approval Overdue - {$this->transaction->transaction_number}"
        };
    }

    /**
     * Get display name for approval type
     */
    private function getApprovalTypeDisplay(): string
    {
        return match($this->approval['type']) {
            'offer_submission' => 'Offer Submission',
            'contract_review' => 'Contract Review',
            'final_approval' => 'Final Approval',
            'price_negotiation' => 'Price Negotiation',
            default => ucwords(str_replace('_', ' ', $this->approval['type']))
        };
    }

    /**
     * Calculate days overdue
     */
    private function getDaysOverdue(): int
    {
        $deadline = \Carbon\Carbon::parse($this->approval['deadline']);
        return max(0, $deadline->diffInDays(now()));
    }
}
