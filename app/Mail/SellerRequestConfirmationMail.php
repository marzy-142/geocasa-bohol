<?php

namespace App\Mail;

use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerRequestConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sellerRequest;
    public $assignedBroker;
    public $assignmentMethod;

    /**
     * Create a new message instance.
     */
    public function __construct(SellerRequest $sellerRequest, ?User $assignedBroker = null, string $assignmentMethod = 'auto')
    {
        $this->sellerRequest = $sellerRequest;
        $this->assignedBroker = $assignedBroker;
        $this->assignmentMethod = $assignmentMethod;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Property Listing Request Confirmed - GeoCasa Bohol',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.seller-request-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
