<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReceivedAdmin extends Mailable
{
    use SerializesModels;

    /** @param array{name: string, phone: string, email: string} $company */
    public function __construct(public QuoteRequest $quote, public array $company) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Crystal Movers enquiry '.$this->quote->reference_number,
            // Replying to this email reaches the customer directly.
            replyTo: $this->quote->email ? [new Address($this->quote->email, $this->quote->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-admin');
    }
}
