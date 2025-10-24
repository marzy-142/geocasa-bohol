<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TransactionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $transaction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $isBroker = $notifiable->id === $this->transaction->broker_id;
        
        return (new MailMessage)
            ->subject($isBroker ? 'Transaction Completed - Commission Earned!' : 'Property Purchase Completed!')
            ->greeting($isBroker ? 'Congratulations!' : 'Congratulations on your new property!')
            ->line($isBroker 
                ? "You have successfully completed a transaction and earned a commission of " . 
                  number_format($this->transaction->commission_amount ?? 0, 2) . " PHP."
                : "Your property purchase has been completed successfully."
            )
            ->line("Transaction Details:")
            ->line("• Property: " . ($this->transaction->property->title ?? 'Unknown Property'))
            ->line("• Final Price: " . number_format(($this->transaction->final_price ?? $this->transaction->offered_price) ?? 0, 2) . " PHP")
            ->line("• Transaction Number: " . $this->transaction->transaction_number)
            ->line("• Completion Date: " . now()->format('F j, Y'))
            ->action($isBroker ? 'View Transaction' : 'View Property', 
                route($isBroker ? 'transactions.show' : 'properties.show', 
                    $isBroker ? $this->transaction->id : $this->transaction->property->slug))
            ->line('Thank you for using GeoCasa Bohol!');
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        $isBroker = $notifiable->id === $this->transaction->broker_id;
        
        return new BroadcastMessage([
            'type' => 'transaction_completed',
            'title' => $isBroker ? 'Transaction Completed!' : 'Property Purchase Completed!',
            'message' => $isBroker 
                ? "You earned ₱" . number_format($this->transaction->commission_amount ?? 0, 2) . " commission!"
                : "Your property purchase is complete!",
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title ?? 'Unknown Property',
            'final_price' => $this->transaction->final_price ?? $this->transaction->offered_price,
            'commission_amount' => $this->transaction->commission_amount,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $isBroker = $notifiable->id === $this->transaction->broker_id;
        
        return [
            'type' => 'transaction_completed',
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title ?? 'Unknown Property',
            'final_price' => $this->transaction->final_price ?? $this->transaction->offered_price,
            'commission_amount' => $this->transaction->commission_amount,
            'is_broker' => $isBroker,
            'title' => $isBroker ? 'Transaction Completed!' : 'Property Purchase Completed!',
            'message' => $isBroker 
                ? "You earned ₱" . number_format($this->transaction->commission_amount ?? 0, 2) . " commission!"
                : "Your property purchase is complete!",
        ];
    }
}
