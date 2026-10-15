<?php

namespace App\Models\Concerns;

use App\Services\SiteDataService;

/**
 * Any model that feeds the public website clears the cached public payload
 * when it is saved or deleted, so the site never serves stale content.
 */
trait FlushesSiteCache
{
    protected static function bootFlushesSiteCache(): void
    {
        $flush = static fn () => app(SiteDataService::class)->forget();

        static::saved($flush);
        static::deleted($flush);
    }
}
