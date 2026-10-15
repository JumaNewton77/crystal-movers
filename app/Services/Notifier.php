<?php

namespace App\Services;

use App\Mail\ContactReceivedAdmin;
use App\Mail\QuoteReceivedAdmin;
use App\Mail\QuoteReceivedCustomer;
use App\Models\ContactMessage;
use App\Models\QuoteRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * All outgoing email goes through here. The provider is chosen by MAIL_* variables
 * in .env, and the admin recipient comes from the admin "Settings" (notification_email)
 * or ADMIN_NOTIFICATION_EMAIL. Nothing is hard-coded. Mail failures are logged, never thrown.
 */
class Notifier
{
    public function quoteReceived(QuoteRequest $quote): void
    {
        if ($quote->email) {
            $this->send($quote->email, new QuoteReceivedCustomer($quote, $this->company()), 'quote customer');
        }

        if ($admin = $this->adminAddress()) {
            $this->send($admin, new QuoteReceivedAdmin($quote, $this->company()), 'quote admin');
        } else {
            Log::warning('No admin notification email configured; new quote email not sent.', ['reference' => $quote->reference_number]);
        }
    }

    public function contactReceived(ContactMessage $message): void
    {
        if ($admin = $this->adminAddress()) {
            $this->send($admin, new ContactReceivedAdmin($message, $this->company()), 'contact admin');
        } else {
            Log::warning('No admin notification email configured; contact message email not sent.', ['id' => $message->id]);
        }
    }

    private function adminAddress(): ?string
    {
        return Setting::read('notification_email') ?: config('crystalmovers.notifications.admin_email');
    }

    /** @return array{name: string, phone: string, email: string} */
    private function company(): array
    {
        return [
            'name' => Setting::read('company_name', 'Crystal Movers'),
            'phone' => Setting::read('phone', ''),
            'email' => Setting::read('email', ''),
        ];
    }

    private function send(string $to, \Illuminate\Contracts\Mail\Mailable $mailable, string $label): void
    {
        try {
            Mail::to($to)->send($mailable);
        } catch (Throwable $e) {
            Log::error("Failed to send {$label} email: ".$e->getMessage());
        }
    }
}
