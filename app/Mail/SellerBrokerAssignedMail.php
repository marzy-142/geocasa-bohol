<?php

namespace App\Mail;

use App\Models\SellerRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerBrokerAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $sellerRequest;
    protected $broker;
    protected $assignedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(SellerRequest $sellerRequest, User $broker, User $assignedBy)
    {
        $this->sellerRequest = $sellerRequest;
        $this->broker = $broker;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Property Listing Request - Broker Assigned',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.seller-broker-assigned',
            with: [
                'sellerRequest' => $this->sellerRequest,
                'broker' => $this->broker,
                'assignedBy' => $this->assignedBy,
            ]
        );
    }

    /**
     * Build the message (alternative method for older Laravel versions).
     */
    public function build()
    {
        return $this->subject('Your Property Listing Request - Broker Assigned')
                    ->view('emails.seller-broker-assigned')
                    ->with([
                        'sellerName' => $this->sellerRequest->seller_name,
                        'propertyTitle' => $this->sellerRequest->property_title,
                        'propertyLocation' => $this->sellerRequest->property_location,
                        'askingPrice' => number_format($this->sellerRequest->asking_price),
                        'brokerName' => $this->broker->name,
                        'brokerEmail' => $this->broker->email,
                        'brokerPhone' => $this->broker->phone ?? 'Not provided',
                        'brokerLicense' => $this->broker->license_number ?? 'Not provided',
                        'assignedBy' => $this->assignedBy->name,
                        'requestId' => $this->sellerRequest->id,
                    ]);
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}