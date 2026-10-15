<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactReceivedAdmin extends Mailable
{
    use SerializesModels;

    /** @param array{name: string, phone: string, email: string} $company */
    public function __construct(public ContactMessage $contact, public array $company) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Crystal Movers contact message: '.$this->contact->subject,
            replyTo: [new Address($this->contact->email, $this->contact->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-admin');
    }
}
