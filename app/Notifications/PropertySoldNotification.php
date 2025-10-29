<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PropertySoldNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Property Sold - Transaction Completed')
            ->greeting('New Property Sale!')
            ->line("A property has been successfully sold through the GeoCasa Bohol platform.")
            ->line("Sale Details:")
            ->line("• Property: " . ($this->transaction->property->title ?? 'Unknown Property'))
            ->line("• Location: " . ($this->transaction->property->municipality ?? 'Unknown Location'))
            ->line("• Buyer: " . ($this->transaction->client->name ?? 'Unknown Client'))
            ->line("• Broker: " . ($this->transaction->broker->name ?? 'Unknown Broker'))
            ->line("• Sale Price: " . number_format(($this->transaction->final_price ?? $this->transaction->offered_price) ?? 0, 2) . " PHP")
            ->line("• Transaction Number: " . $this->transaction->transaction_number)
            ->line("• Completion Date: " . now()->format('F j, Y'))
            ->action('View Transaction Details', route('admin.transactions.show', $this->transaction->id))
            ->line('This sale has been automatically processed and all parties have been notified.');
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'property_sold',
            'title' => 'Property Sold!',
            'message' => "Property '{$this->transaction->property->title}' sold for ₱" . number_format(($this->transaction->final_price ?? $this->transaction->offered_price) ?? 0, 2),
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title ?? 'Unknown Property',
            'broker_name' => $this->transaction->broker->name ?? 'Unknown Broker',
            'final_price' => $this->transaction->final_price ?? $this->transaction->offered_price,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'property_sold',
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title ?? 'Unknown Property',
            'property_location' => $this->transaction->property->municipality ?? 'Unknown Location',
            'client_name' => $this->transaction->client->name ?? 'Unknown Client',
            'broker_name' => $this->transaction->broker->name ?? 'Unknown Broker',
            'final_price' => $this->transaction->final_price ?? $this->transaction->offered_price,
            'title' => 'Property Sold!',
            'message' => "Property '{$this->transaction->property->title}' sold for ₱" . number_format(($this->transaction->final_price ?? $this->transaction->offered_price) ?? 0, 2),
        ];
    }
}
