<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /** POST /api/contact */
    public function store(StoreContactMessageRequest $request, Notifier $notifier): JsonResponse
    {
        $message = 'Thank you. Your message has been received. Our team will get back to you.';

        // Honeypot: pretend success, save nothing.
        if ($request->filled('website')) {
            return $this->ok(null, $message, 201);
        }

        $contact = ContactMessage::create([
            ...$request->safe()->only(['name', 'phone', 'email', 'subject', 'message']),
            'contact_consent' => true,
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        $notifier->contactReceived($contact);

        return $this->ok(null, $message, 201);
    }
}
