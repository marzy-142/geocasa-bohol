<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CriticalOverdueApprovalNotification extends Notification implements ShouldQueue, ShouldBroadcast
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
        $this->transaction = $transaction->load(['property', 'client', 'broker']);
        $this->approval = $approval;
        $this->escalationType = $escalationType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Always send all channels for critical notifications
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = "🚨 CRITICAL: Overdue Approval Requires Admin Intervention";
        
        return (new MailMessage)
            ->subject($subject)
            ->greeting("CRITICAL ALERT")
            ->line("A critical client approval has exceeded its deadline and requires immediate administrative attention.")
            ->line("")
            ->line("**Transaction Details:**")
            ->line("• Transaction #: {$this->transaction->transaction_number}")
            ->line("• Property: {$this->transaction->property->title}")
            ->line("• Client: {$this->transaction->client->name}")
            ->line("• Broker: {$this->transaction->broker->name}")
            ->line("• Approval Type: {$this->getApprovalTypeDisplay()}")
            ->line("• Deadline: " . \Carbon\Carbon::parse($this->approval['deadline'])->format('M j, Y \a\t g:i A'))
            ->line("• Days Overdue: " . $this->getDaysOverdue())
            ->line("")
            ->line("**Immediate Actions Required:**")
            ->line("1. Contact the client directly")
            ->line("2. Review the transaction status")
            ->line("3. Consider administrative intervention")
            ->line("4. Update transaction status if necessary")
            ->line("")
            ->action('View Transaction Details', route('transactions.show', $this->transaction))
            ->line("")
            ->line("This notification has been escalated to all administrators due to the critical nature of the overdue approval.")
            ->salutation('GeoCasa Bohol System Alert');
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'critical_overdue_approval',
            'priority' => 'critical',
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'client_name' => $this->transaction->client->name,
            'broker_name' => $this->transaction->broker->name,
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
            'type' => 'critical_overdue_approval',
            'priority' => 'critical',
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'client_name' => $this->transaction->client->name,
            'broker_name' => $this->transaction->broker->name,
            'approval_type' => $this->approval['type'],
            'approval_id' => $this->approval['id'],
            'escalation_type' => $this->escalationType,
            'days_overdue' => $this->getDaysOverdue(),
            'deadline' => $this->approval['deadline'],
            'requires_admin_action' => true,
            'timestamp' => now()->toISOString(),
        ];
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
