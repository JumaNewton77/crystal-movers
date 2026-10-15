<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReceivedCustomer extends Mailable
{
    use SerializesModels;

    /** @param array{name: string, phone: string, email: string} $company */
    public function __construct(public QuoteRequest $quote, public array $company) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Crystal Movers request has been received');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-customer');
    }
}
