<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Crystal Movers uploads its own images from the admin area.
 *  1. Adds the image/alt-text settings and content rows that did not exist before (existing rows are kept).
 *  2. Clears references to the temporary developer photos and placeholders that shipped in /public/images
 *     (website content, services, gallery). Uploaded images (stored on the public disk) are never touched.
 */
return new class extends Migration
{
    private const OLD_PREFIXES = ['/images/defaults/', '/images/placeholders/'];

    public function up(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore([
            ['key' => 'favicon_path', 'value' => null, 'group' => 'general', 'created_at' => $now, 'updated_at' => $now],
        ]);

        foreach ([
            'home.hero.image_alt', 'home.why.image_alt', 'home.safety.image_alt', 'home.cta.image_alt',
            'about.image', 'about.image_alt',
        ] as $key) {
            DB::table('website_content')->insertOrIgnore([
                ['key' => $key, 'value' => null, 'group' => str_starts_with($key, 'about.') ? 'about' : 'home', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        foreach (self::OLD_PREFIXES as $prefix) {
            DB::table('website_content')->where('value', 'like', $prefix.'%')->update(['value' => null]);
            DB::table('services')->where('image_path', 'like', $prefix.'%')->update(['image_path' => null]);
            DB::table('service_categories')->where('image_path', 'like', $prefix.'%')->update(['image_path' => null]);
            DB::table('gallery')->where('image_path', 'like', $prefix.'%')->delete();
        }

        Cache::forget('site.data.v1');
        Cache::forget('site.data.v2');
    }

    public function down(): void
    {
        // Intentionally empty: the temporary developer images are not restored.
    }
};
