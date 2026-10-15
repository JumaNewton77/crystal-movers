<?php

namespace App\Models;

use App\Enums\TestimonialStatus;
use App\Models\Concerns\FlushesSiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use FlushesSiteCache;

    protected $fillable = ['customer_name', 'location', 'rating', 'review', 'photo_path', 'status', 'sort_order'];

    protected function casts(): array
    {
        return [
            'status' => TestimonialStatus::class,
            'rating' => 'integer',
        ];
    }

    /** The only scope public pages may use: approved reviews. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', TestimonialStatus::Approved->value)->orderBy('sort_order');
    }
}
