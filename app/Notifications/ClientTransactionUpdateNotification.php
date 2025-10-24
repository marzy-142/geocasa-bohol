<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientTransactionUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Transaction $transaction;
    public array $updateData;

    public function __construct(Transaction $transaction, array $updateData)
    {
        $this->transaction = $transaction->load(['property', 'broker']);
        $this->updateData = $updateData;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $updateType = $this->updateData['update_type'];
        $subject = "Transaction Update: {$this->transaction->transaction_number} - " . ucwords(str_replace('_', ' ', $updateType));
        
        $greeting = "Hello {$notifiable->name},";
        $line1 = "Your transaction for **{$this->transaction->property->title}** has been updated.";
        $line2 = "Update Type: **" . ucwords(str_replace('_', ' ', $updateType)) . "**";
        
        $message = $this->getUpdateMessage($updateType);
        $actionText = "View Transaction Details";
        $actionUrl = route('client.transactions.show', $this->transaction->id);

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($line1)
                    ->line($line2)
                    ->line($message)
                    ->action($actionText, $actionUrl)
                    ->line('Thank you for choosing GeoCasa Bohol for your property needs.')
                    ->salutation('Regards, The GeoCasa Bohol Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'update_type' => $this->updateData['update_type'],
            'data' => $this->updateData['data'],
            'timestamp' => $this->updateData['timestamp'],
            'message' => "Transaction #{$this->transaction->transaction_number} has been updated: " . ucwords(str_replace('_', ' ', $this->updateData['update_type'])),
            'link' => route('client.transactions.show', $this->transaction->id),
        ];
    }

    protected function getUpdateMessage(string $updateType): string
    {
        return match ($updateType) {
            'status_changed' => 'The status of your transaction has been updated. Please check your dashboard for the latest information.',
            'price_updated' => 'The transaction price has been updated. Please review the new details.',
            'contract_signed' => 'Congratulations! The contract has been signed and your transaction is progressing.',
            'closing_scheduled' => 'Your closing date has been scheduled. Please prepare the necessary documents.',
            'document_uploaded' => 'New documents have been uploaded to your transaction. Please review them.',
            'meeting_scheduled' => 'A new meeting has been scheduled for your transaction.',
            'milestone_achieved' => 'Congratulations! You have reached an important milestone in your transaction.',
            default => 'Your transaction has been updated with new information.',
        };
    }
}
