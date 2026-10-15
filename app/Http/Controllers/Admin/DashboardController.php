<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactMessageStatus;
use App\Enums\QuoteStatus;
use App\Enums\TestimonialStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageResource;
use App\Http\Resources\QuoteRequestResource;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use App\Models\QuoteRequest;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $today = now(config('crystalmovers.timezone'))->toDateString();

        // Requests still in progress (not completed or cancelled).
        $open = [
            QuoteStatus::New->value, QuoteStatus::Contacted->value, QuoteStatus::Assessment->value,
            QuoteStatus::QuotationSent->value, QuoteStatus::Confirmed->value,
        ];

        $upcoming = QuoteRequest::query()
            ->whereDate('preferred_date', '>=', $today)
            ->whereIn('status', $open);

        return $this->ok([
            'counts' => [
                'quote_requests' => QuoteRequest::count(),
                'new_quote_requests' => QuoteRequest::where('status', QuoteStatus::New->value)->count(),
                'new_messages' => ContactMessage::where('status', ContactMessageStatus::New->value)->count(),
                'upcoming_moves' => (clone $upcoming)->count(),
                'testimonials' => Testimonial::count(),
                'pending_testimonials' => Testimonial::where('status', TestimonialStatus::Pending->value)->count(),
                'gallery_images' => GalleryItem::count(),
            ],
            'recent_quotes' => QuoteRequestResource::collection(QuoteRequest::latest()->limit(5)->get())->resolve(),
            'recent_messages' => ContactMessageResource::collection(ContactMessage::latest()->limit(5)->get())->resolve(),
            'upcoming_moves' => QuoteRequestResource::collection(
                (clone $upcoming)->orderBy('preferred_date')->limit(5)->get()
            )->resolve(),
        ]);
    }
}
