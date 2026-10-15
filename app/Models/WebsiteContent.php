<?php

namespace App\Models;

use App\Services\SiteDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Editable page text, images and per-page SEO overrides (keys like "seo.home.title").
 * Keys ending in ".image" hold an image path (uploaded from the admin area).
 */
class WebsiteContent extends Model
{
    private const CACHE_KEY = 'website_content.map';

    protected $table = 'website_content';

    protected $fillable = ['key', 'value', 'group'];

    protected static function booted(): void
    {
        $flush = static function () {
            Cache::forget(self::CACHE_KEY);
            app(SiteDataService::class)->forget();
        };
        static::saved($flush);
        static::deleted($flush);
    }

    /** @return array<string, string|null> */
    public static function map(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('value', 'key')->all());
    }
}
