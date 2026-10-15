<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Services\QuoteReferenceGenerator;
use App\Services\QuoteRequestService;
use Illuminate\Http\JsonResponse;

class QuoteRequestController extends Controller
{
    /** POST /api/quote-requests */
    public function store(StoreQuoteRequest $request, QuoteRequestService $quotes, QuoteReferenceGenerator $references): JsonResponse
    {
        $message = 'Quote request submitted successfully.';

        // Honeypot: bots fill the hidden "website" field. Pretend success, save nothing.
        if ($request->filled('website')) {
            return $this->ok(['reference' => $references->decoy()], $message, 201);
        }

        $quote = $quotes->create(
            $request->safe()->except(['attachments', 'website']),
            $request->file('attachments', []),
            $request->ip(),
        );

        return $this->ok(['reference' => $quote->reference_number], $message, 201);
    }
}
