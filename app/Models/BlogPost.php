<?php

namespace App\Models;

use App\Models\Concerns\FlushesSiteCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use FlushesSiteCache;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'image_path', 'image_alt',
        'meta_title', 'meta_description', 'is_published', 'published_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    /** Public pages may only use this scope: published posts, newest first. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderByDesc('published_at')->orderByDesc('id');
    }
}
