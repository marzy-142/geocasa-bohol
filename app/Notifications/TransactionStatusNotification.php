<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $transaction;
    protected $oldStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaction $transaction, $oldStatus = null)
    {
        $this->transaction = $transaction;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the notification's delivery channels.
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
        $statusLabels = [
            'inquiry' => 'Initial Inquiry',
            'negotiation' => 'Under Negotiation',
            'offer_accepted' => 'Offer Accepted',
            'contract_signed' => 'Contract Signed',
            'due_diligence' => 'Due Diligence',
            'financing' => 'Financing',
            'closing_preparation' => 'Closing Preparation',
            'finalized' => 'Transaction Finalized',
            'cancelled' => 'Transaction Cancelled'
        ];

        $currentStatus = $statusLabels[$this->transaction->status] ?? $this->transaction->status;
        $previousStatus = $this->oldStatus ? ($statusLabels[$this->oldStatus] ?? $this->oldStatus) : null;

        $message = (new MailMessage)
            ->subject('Transaction Status Update - ' . $this->transaction->property->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The status of your transaction has been updated.');

        if ($previousStatus) {
            $message->line('**Previous Status:** ' . $previousStatus);
        }

        $message->line('**Current Status:** ' . $currentStatus)
            ->line('**Property:** ' . $this->transaction->property->title)
            ->line('**Client:** ' . $this->transaction->client->name);

        if ($this->transaction->status === 'finalized') {
            $message->line('**Final Price:** ₱' . number_format($this->transaction->final_price ?? $this->transaction->offered_price))
                ->line('**Commission:** ₱' . number_format($this->transaction->commission_amount))
                ->line('Congratulations on completing this transaction!');
        }

        return $message->action('View Transaction', route('transactions.show', $this->transaction))
            ->line('Thank you for using our platform!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'transaction_status',
            'transaction_id' => $this->transaction->id,
            'property_title' => $this->transaction->property->title,
            'client_name' => $this->transaction->client->name,
            'old_status' => $this->oldStatus,
            'new_status' => $this->transaction->status,
            'message' => 'Transaction status updated to ' . ucfirst(str_replace('_', ' ', $this->transaction->status))
        ];
    }
}