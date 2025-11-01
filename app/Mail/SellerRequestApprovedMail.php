<?php

namespace App\Mail;

use App\Models\SellerRequest;
use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerRequestApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public SellerRequest $sellerRequest;
    public ?Property $property;

    public function __construct(SellerRequest $sellerRequest, ?Property $property = null)
    {
        $this->sellerRequest = $sellerRequest;
        $this->property = $property;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Seller Request Has Been Approved',
        );
    }

    public function content(): Content
    {
        // Derive broker from assigned broker on the request, or from the property's broker
        $broker = null;
        try {
            $broker = $this->sellerRequest->assignedBroker;
            if (!$broker && $this->property) {
                $broker = $this->property->broker;
            }
        } catch (\Throwable $e) {
            $broker = null;
        }

        return new Content(
            view: 'emails.seller-request-approved',
            with: [
                'sellerRequest' => $this->sellerRequest,
                'property' => $this->property,
                'broker' => $broker,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
