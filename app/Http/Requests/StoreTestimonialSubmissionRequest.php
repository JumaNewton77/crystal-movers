<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** A customer's review submitted from the public website. It is saved as "pending" and never shown until an admin approves it. */
class StoreTestimonialSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = [];
        foreach (['customer_name', 'location', 'review'] as $field) {
            if (is_string($this->input($field))) {
                $clean[$field] = trim(strip_tags($this->input($field)));
            }
        }

        $this->merge($clean);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:10', 'max:1000'],
            'website' => ['nullable', 'string', 'max:255'], // honeypot
        ];
    }
}
