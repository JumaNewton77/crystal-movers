<?php

namespace App\Models;

use App\Enums\ServiceStatus;
use App\Models\Concerns\FlushesSiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use FlushesSiteCache;

    protected $fillable = [
        'service_category_id', 'slug', 'title', 'short_description', 'description', 'benefits',
        'status', 'is_published', 'image_path', 'image_alt', 'price_text', 'icon', 'sort_order',
        'seo_title', 'seo_description', 'og_image_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceStatus::class,
            'is_published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('sort_order');
    }

    public function isComingSoon(): bool
    {
        return $this->status === ServiceStatus::ComingSoon;
    }
}
