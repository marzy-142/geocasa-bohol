<?php

namespace App\Mail;

use App\Models\SellerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerRequestRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public SellerRequest $sellerRequest;
    public ?string $rejectionReason;

    public function __construct(SellerRequest $sellerRequest, ?string $rejectionReason = null)
    {
        $this->sellerRequest = $sellerRequest;
        $this->rejectionReason = $rejectionReason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Seller Request',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.seller-request-rejected',
            with: [
                'sellerRequest' => $this->sellerRequest,
                'rejectionReason' => $this->rejectionReason,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
