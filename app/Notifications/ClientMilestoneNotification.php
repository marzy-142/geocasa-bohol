<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientMilestoneNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Transaction $transaction;
    public array $milestoneData;

    public function __construct(Transaction $transaction, array $milestoneData)
    {
        $this->transaction = $transaction->load(['property', 'broker']);
        $this->milestoneData = $milestoneData;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $milestone = ucwords(str_replace('_', ' ', $this->milestoneData['milestone']));
        $subject = "🎉 Milestone Achieved: {$milestone} - Transaction #{$this->transaction->transaction_number}";
        
        $greeting = "Congratulations {$notifiable->name}!";
        $line1 = "You have successfully reached the **{$milestone}** milestone in your transaction for **{$this->transaction->property->title}**.";
        $line2 = "This is a significant step forward in your property journey!";
        
        $message = $this->getMilestoneMessage($this->milestoneData['milestone']);
        $actionText = "View Transaction Progress";
        $actionUrl = route('client.transactions.show', $this->transaction->id);

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($line1)
                    ->line($line2)
                    ->line($message)
                    ->action($actionText, $actionUrl)
                    ->line('We are committed to making your property transaction smooth and successful.')
                    ->salutation('Regards, The GeoCasa Bohol Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'milestone' => $this->milestoneData['milestone'],
            'data' => $this->milestoneData['data'],
            'timestamp' => $this->milestoneData['timestamp'],
            'message' => "Milestone achieved: {$this->milestoneData['milestone']} for transaction #{$this->transaction->transaction_number}",
            'link' => route('client.transactions.show', $this->transaction->id),
        ];
    }

    protected function getMilestoneMessage(string $milestone): string
    {
        return match ($milestone) {
            'initial_contact' => 'You have successfully made initial contact with your broker. The journey begins!',
            'property_viewing' => 'You have scheduled your first property viewing. This is an exciting step!',
            'offer_made' => 'You have made an offer on the property. We will keep you updated on the response.',
            'offer_accepted' => 'Congratulations! Your offer has been accepted. The negotiation phase begins.',
            'contract_signed' => 'Excellent! The contract has been signed. You are now officially under contract.',
            'due_diligence' => 'The due diligence period has begun. This is your time to thoroughly inspect the property.',
            'financing_approved' => 'Great news! Your financing has been approved. You are one step closer to ownership.',
            'closing_scheduled' => 'Your closing date has been scheduled. The final steps are in progress.',
            'transaction_completed' => 'Congratulations! Your transaction has been completed successfully. Welcome to your new property!',
            default => 'You have reached an important milestone in your property transaction.',
        };
    }
}
