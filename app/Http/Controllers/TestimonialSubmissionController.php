<?php

namespace App\Http\Controllers;

use App\Enums\TestimonialStatus;
use App\Http\Requests\StoreTestimonialSubmissionRequest;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class TestimonialSubmissionController extends Controller
{
    /** POST /api/testimonials: public review submission, saved as pending for admin approval. */
    public function store(StoreTestimonialSubmissionRequest $request): JsonResponse
    {
        $message = 'Thank you for your review. It will appear on the website once it has been approved.';

        // Honeypot: pretend success, save nothing.
        if ($request->filled('website')) {
            return $this->ok(null, $message, 201);
        }

        Testimonial::create([
            ...$request->safe()->only(['customer_name', 'location', 'rating', 'review']),
            'status' => TestimonialStatus::Pending,
            'sort_order' => (int) Testimonial::max('sort_order') + 1,
        ]);

        return $this->ok(null, $message, 201);
    }
}
