<?php

namespace Database\Seeders;

use App\Models\WebsiteContent;
use App\Support\CopyDefaults;
use Illuminate\Database\Seeder;

class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        // Generic, non-factual starter copy. No invented history, years of experience,
        // fleet size, staff numbers, branches, certifications or statistics.
        // Keys ending in ".image" hold an uploaded image path. They start EMPTY: upload your own photos in
        // Admin > Website content & images. Until then the website shows clean neutral placeholders.
        $content = [
            'home' => [
                'home.hero.image' => null,
                'home.hero.image_alt' => null,
                'home.why.image' => null,
                'home.why.image_alt' => null,
                'home.safety.image' => null,
                'home.safety.image_alt' => null,
                'home.cta.image' => null,
                'home.cta.image_alt' => null,
            ],
            'about' => [
                'about.vision' => null,
                'about.image' => null,
                'about.image_alt' => null,
            ],
        ];

        // Official copy lives in one place (App\Support\CopyDefaults).
        foreach (CopyDefaults::content() as $group => $items) {
            $content[$group] = [...($content[$group] ?? []), ...$items];
        }

        // Per-page SEO overrides start empty: SeoService falls back to config/seo.php.
        foreach (['home', 'services', 'about', 'how_it_works', 'gallery', 'faq', 'contact'] as $page) {
            $content['seo']["seo.$page.title"] = null;
            $content['seo']["seo.$page.description"] = null;
        }

        foreach ($content as $group => $items) {
            foreach ($items as $key => $value) {
                WebsiteContent::firstOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            }
        }
    }
}
