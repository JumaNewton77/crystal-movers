<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * Add / edit / approve / hide / delete. Approving or hiding is just an update of `status`;
 * only "approved" testimonials reach the public site.
 */
class TestimonialController extends CrudController
{
    protected function model(): string
    {
        return Testimonial::class;
    }

    protected function imageField(): ?string
    {
        return 'photo';
    }

    protected function imageColumn(): string
    {
        return 'photo_path';
    }

    protected function directory(): string
    {
        return 'testimonials';
    }

    protected function rules(?Model $record): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(TestimonialStatus::class)],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
