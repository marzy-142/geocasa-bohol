<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientDocumentRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Transaction $transaction;
    public array $requestData;

    public function __construct(Transaction $transaction, array $requestData)
    {
        $this->transaction = $transaction->load(['property', 'broker']);
        $this->requestData = $requestData;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = "Document Request: Transaction #{$this->transaction->transaction_number}";
        $greeting = "Hello {$notifiable->name},";
        $line1 = "We need additional documents for your transaction regarding **{$this->transaction->property->title}**.";
        $line2 = "Please upload the following documents:";
        
        $documentsList = '';
        foreach ($this->requestData['requested_documents'] as $document) {
            $documentsList .= "• " . ucwords(str_replace('_', ' ', $document)) . "\n";
        }

        $deadlineText = '';
        if ($this->requestData['deadline']) {
            $deadlineText = "\n\n**Deadline:** " . \Carbon\Carbon::parse($this->requestData['deadline'])->format('M d, Y H:i A');
        }

        $actionText = "Upload Documents";
        $actionUrl = route('client.transactions.documents.index', $this->transaction->id);

        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($line1)
                    ->line($line2)
                    ->line($documentsList)
                    ->line($deadlineText)
                    ->action($actionText, $actionUrl)
                    ->line('Please ensure all documents are clear and legible before uploading.')
                    ->salutation('Regards, The GeoCasa Bohol Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'transaction_id' => $this->transaction->id,
            'transaction_number' => $this->transaction->transaction_number,
            'property_title' => $this->transaction->property->title,
            'requested_documents' => $this->requestData['requested_documents'],
            'deadline' => $this->requestData['deadline'],
            'timestamp' => $this->requestData['timestamp'],
            'message' => "Document request for transaction #{$this->transaction->transaction_number}: " . implode(', ', $this->requestData['requested_documents']),
            'link' => route('client.transactions.documents.index', $this->transaction->id),
        ];
    }
}
