<?php

namespace App\Models;

use App\Services\SiteDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value site settings (phone, WhatsApp, email, address, hours, social links,
 * Google rating, insurance wording, default SEO...). Edited from the admin area.
 *
 *   Setting::read('phone', '+254 XXX XXX XXX');
 *   Setting::map();   // all settings, cached
 */
class Setting extends Model
{
    private const CACHE_KEY = 'settings.map';

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

    public static function read(string $key, ?string $default = null): ?string
    {
        $value = static::map()[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    public static function write(string $key, ?string $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}
