<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WebsiteContent;
use App\Support\Content;
use App\Support\SiteImages;
use Illuminate\Support\Facades\Cache;

/**
 * The single, public view of the database for the website (PostgreSQL is the
 * source of truth). It feeds:
 *   - GET /api/site                         (client-side data)
 *   - window.__SITE__ in the page shell      (first render, no extra request)
 *   - `php artisan site:export`              (input for the build-time prerender)
 *
 * The payload only ever contains public data: approved testimonials, published
 * services/FAQs/gallery, and a whitelist of settings. The hash lets the server
 * detect when prerendered HTML is stale (see PrerenderRepository).
 */
class SiteDataService
{
    private const CACHE_KEY = 'site.data.v2';

    /** Settings that may be exposed publicly. Anything else stays private. */
    private const PUBLIC_SETTINGS = [
        'company_name', 'tagline', 'phone', 'whatsapp_number', 'whatsapp_default_message',
        'email', 'address', 'working_hours', 'google_maps_embed_url',
        'social_facebook', 'social_instagram', 'social_x', 'social_linkedin', 'social_youtube', 'social_tiktok',
        'google_rating', 'google_review_count', 'google_business_url',
        'insurance_heading', 'insurance_text',
        'seo_default_title', 'seo_default_description',
    ];

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->cached()['data'];
    }

    public function hash(): string
    {
        return $this->cached()['hash'];
    }

    public function forget(): void
    {
        Cache::forget($this->cacheKey());
    }

    /** Stored paths are either site-relative defaults ("/images/...") or uploads on the public disk. */
    public static function mediaUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, '/') || str_starts_with($path, 'http')) {
            return $path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    /** Includes a fingerprint of public/images/site so adding or replacing a file refreshes the data. */
    private function cacheKey(): string
    {
        return self::CACHE_KEY.'.'.SiteImages::signature();
    }

    /** @return array{data: array<string, mixed>, hash: string} */
    private function cached(): array
    {
        return Cache::rememberForever($this->cacheKey(), function () {
            $data = $this->build();

            return [
                'data' => $data,
                'hash' => sha1(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            ];
        });
    }

    /** @return array<string, mixed> */
    private function build(): array
    {
        $stored = Setting::map();
        $settings = [];
        foreach (self::PUBLIC_SETTINGS as $key) {
            $settings[$key] = $this->clean($stored[$key] ?? null);
        }
        $settings['logo'] = SiteImages::existing(self::mediaUrl($this->clean($stored['logo_path'] ?? null))) ?? SiteImages::find('logo');
        $settings['favicon'] = SiteImages::existing(self::mediaUrl($this->clean($stored['favicon_path'] ?? null))) ?? SiteImages::find('favicon');
        $settings['logo_header'] = $settings['logo'] !== null && str_starts_with($settings['logo'], '/images/site/')
            ? SiteImages::find('logo-header')
            : null; // compact logo (no tagline) for the header; only when the logo comes from public/images/site
        ksort($settings);

        $content = collect(WebsiteContent::map())
            ->reject(fn ($value, $key) => str_starts_with($key, 'seo.'))
            ->map(fn ($value, $key) => str_ends_with($key, '.image')
                ? SiteImages::existing(self::mediaUrl($this->clean($value)))
                : $this->clean($value))
            ->all();
        foreach (SiteImages::CONTENT as $key => $file) {
            $content[$key] = $content[$key] ?? SiteImages::find($file);
        }
        ksort($content);

        return [
            'settings' => $settings,
            'content' => $content,
            // Only published categories that still have at least one published service.
            'service_categories' => ServiceCategory::published()->get()
                ->filter(fn (ServiceCategory $c) => Service::published()->where('service_category_id', $c->id)->exists())
                ->map(fn (ServiceCategory $c) => [
                    'slug' => $c->slug,
                    'name' => $c->name,
                    'description' => $c->description,
                    'icon' => $c->icon,
                    'image' => SiteImages::existing(self::mediaUrl($c->image_path)) ?? SiteImages::find('categories/'.$c->slug),
                    'image_alt' => $c->image_alt ?: $c->name,
                ])->values()->all(),
            'services' => Service::published()->with('category:id,slug,is_published')->get()
                ->filter(fn (Service $s) => $s->category === null || $s->category->is_published)
                ->map(fn (Service $s) => [
                'id' => $s->id,
                'slug' => $s->slug,
                'title' => $s->title,
                'short_description' => $s->short_description,
                'description' => $s->description,
                'benefits' => $this->lines($s->benefits),
                'status' => $s->status->value,
                'image' => SiteImages::existing(self::mediaUrl($s->image_path)) ?? SiteImages::find('services/'.$s->slug)
                    ?? SiteImages::find('categories/'.($s->category?->slug ?? '-')),
                'image_alt' => $s->image_alt ?: $s->title,
                'price_text' => $this->clean($s->price_text),
                'icon' => $this->clean($s->icon),
                'category' => $s->category?->slug,
                'seo_title' => $s->seo_title,
                'seo_description' => $s->seo_description,
            ])->values()->all(),
            'faqs' => Faq::published()->get()->map(fn (Faq $f) => [
                'id' => $f->id,
                'question' => $f->question,
                'answer' => $f->answer,
            ])->values()->all(),
            // Approved only. This is the one place public testimonials are read.
            'testimonials' => Testimonial::approved()->get()->map(fn (Testimonial $t) => [
                'id' => $t->id,
                'customer_name' => $t->customer_name,
                'location' => $t->location,
                'rating' => $t->rating,
                'review' => $t->review,
                'photo' => self::mediaUrl($t->photo_path),
            ])->values()->all(),
            'gallery' => GalleryItem::published()->with('category:id,slug')->get()->map(fn (GalleryItem $g) => [
                'id' => $g->id,
                'title' => $g->title,
                'description' => $g->description,
                'alt' => $g->alt_text ?: $g->title,
                'image' => self::mediaUrl($g->image_path),
                'category' => $g->category?->slug,
            ])->values()->all(),
            'gallery_categories' => GalleryCategory::orderBy('sort_order')->orderBy('id')->get()->map(fn (GalleryCategory $c) => [
                'slug' => $c->slug,
                'name' => $c->name,
            ])->values()->all(),
            'blog_posts' => BlogPost::published()->get()->map(fn (BlogPost $p) => [
                'slug' => $p->slug,
                'title' => $p->title,
                'excerpt' => $p->excerpt,
                'body' => $p->body,
                'image' => self::mediaUrl($p->image_path),
                'image_alt' => $p->image_alt ?: $p->title,
                'meta_title' => $this->clean($p->meta_title),
                'meta_description' => $this->clean($p->meta_description),
                'published_at' => $p->published_at?->toDateString(),
            ])->values()->all(),
            'locations' => Content::locations(),
        ];
    }

    /** @return array<int, string> */
    private function lines(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R/', $text) ?: [])));
    }

    private function clean(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
