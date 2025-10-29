<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;
    public $brokerResponse;
    public $brokerName;

    /**
     * Create a new message instance.
     */
    public function __construct(Inquiry $inquiry, string $brokerResponse, string $brokerName)
    {
        $this->inquiry = $inquiry;
        $this->brokerResponse = $brokerResponse;
        $this->brokerName = $brokerName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Response to Your Property Inquiry - ' . $this->inquiry->property->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-response',
            text: 'emails.inquiry-response-text',
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
