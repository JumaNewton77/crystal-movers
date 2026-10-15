<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\WebsiteContent;
use App\Support\SiteImages;
use Illuminate\Support\Str;

/**
 * Builds the SEO data (title, description, canonical, robots, Open Graph and
 * JSON-LD) that Laravel injects into every public page's <head>.
 *
 * Titles/descriptions come from, in order:
 *   1. admin overrides (website_content: seo.<page>.title / .description,
 *      or the service's own seo_title / seo_description)
 *   2. the defaults in config/seo.php
 */
class SeoService
{
    /** A static page: home, services, about, how_it_works, gallery, faq, contact. */
    public function page(string $key): array
    {
        $page = config("seo.pages.$key");
        abort_if($page === null, 404);

        $content = WebsiteContent::map();

        // Home also honours the admin "Website title / description" settings before the config defaults.
        $defaultTitle = $key === 'home' ? Setting::read('seo_default_title') : null;
        $defaultDescription = $key === 'home' ? Setting::read('seo_default_description') : null;

        return $this->build(
            path: $page['path'],
            title: $this->filled($content["seo.$key.title"] ?? null) ?? $this->filled($defaultTitle) ?? $page['title'],
            description: $this->filled($content["seo.$key.description"] ?? null) ?? $this->filled($defaultDescription) ?? $page['description'],
            crumbs: $key === 'home' ? [] : [[$page['crumb'] ?? $page['title'], $page['path']]],
            extraSchemas: in_array($key, ['home', 'faq'], true) ? [$this->faqSchema()] : [],
        );
    }

    public function category(ServiceCategory $category): array
    {
        $path = '/services/category/'.$category->slug;

        return $this->build(
            path: $path,
            title: $category->name.' in Kenya',
            description: $category->description ?: $category->name.' services from '.$this->siteName().'.',
            crumbs: [['Services', '/services'], [$category->name, $path]],
            ogImage: $category->image_path,
        );
    }

    public function service(Service $service): array
    {
        $path = '/services/'.$service->slug;
        $comingSoon = $service->isComingSoon();

        $schemas = [];
        if (! $comingSoon) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'Service',
                'name' => $service->title,
                'description' => $service->short_description,
                'serviceType' => $service->title,
                'provider' => ['@type' => 'LocalBusiness', 'name' => $this->siteName()],
                'areaServed' => ['@type' => 'Country', 'name' => 'Kenya'],
            ];
        }

        return $this->build(
            path: $path,
            title: $service->seo_title ?: $service->title.' in Kenya',
            description: $service->seo_description ?: $service->short_description,
            crumbs: [['Services', '/services'], [$service->title, $path]],
            extraSchemas: $schemas,
            // "Coming soon" pages stay out of search results until they are real services.
            noindex: $comingSoon,
            ogImage: $service->og_image_path,
        );
    }

    public function blogPost(BlogPost $post): array
    {
        $path = '/blog/'.$post->slug;
        $base = $this->baseUrl();
        $description = $post->meta_description ?: ($post->excerpt ?: Str::limit(trim(strip_tags($post->body)), 160, '…'));
        $image = $post->image_path ? $this->assetUrl($post->image_path) : null;

        $seo = $this->build(
            path: $path,
            title: $post->meta_title ?: $post->title,
            description: $description,
            crumbs: [['Blog', '/blog'], [$post->title, $path]],
            extraSchemas: [array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->title,
                'description' => Str::limit(trim(strip_tags($description)), 160, '…'),
                'datePublished' => $post->published_at?->toIso8601String(),
                'dateModified' => $post->updated_at?->toIso8601String(),
                'image' => $image,
                'mainEntityOfPage' => $base.$path,
                'author' => ['@type' => 'Organization', 'name' => $this->siteName()],
                'publisher' => ['@type' => 'Organization', 'name' => $this->siteName()],
            ])],
            ogImage: $post->image_path,
        );
        $seo['og_type'] = 'article';

        return $seo;
    }

    /** Custom 404 page: never indexed, no canonical URL. */
    public function notFound(): array
    {
        $seo = $this->build(
            path: '/',
            title: 'Page not found',
            description: 'The page you are looking for could not be found. Visit the Crystal Movers homepage to request a moving quote.',
            noindex: true,
        );

        $seo['canonical'] = null;
        $seo['json_ld'] = [];

        return $seo;
    }

    /** @param array{slug: string, name: string} $location */
    public function location(array $location): array
    {
        $path = '/movers-in-'.$location['slug'];
        $name = $location['name'];

        return $this->build(
            path: $path,
            title: "Movers in {$name}",
            description: "Looking for professional movers in {$name}? {$this->siteName()} handles home and office moves. Tell us about your move and request a quote.",
            crumbs: [["Movers in {$name}", $path]],
        );
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $crumbs  [[name, path], ...]
     * @param  array<int, array<string, mixed>|null>  $extraSchemas
     */
    private function build(
        string $path,
        string $title,
        string $description,
        array $crumbs = [],
        array $extraSchemas = [],
        bool $noindex = false,
        ?string $ogImage = null,
    ): array {
        $settings = Setting::map();
        $siteName = $this->siteName();
        $base = $this->baseUrl();

        $fullTitle = str_contains(mb_strtolower($title), mb_strtolower($siteName))
            ? $title
            : $title.config('seo.title_separator').$siteName;

        $ogImage = $this->filled($ogImage) ?? $this->filled($settings['seo_og_image'] ?? null) ?? $this->filled($settings['logo_path'] ?? null) ?? SiteImages::find('og') ?? SiteImages::find('logo');

        return [
            'title' => $fullTitle,
            'description' => Str::limit(trim(strip_tags($description)), 160, '…'),
            // Canonical uses the configured APP_URL, never the request Host header.
            'canonical' => $base.$path,
            'robots' => $noindex ? 'noindex,follow' : 'index,follow,max-image-preview:large',
            'og_type' => 'website',
            'og_site_name' => $siteName,
            'og_locale' => config('seo.locale'),
            'og_image' => $ogImage ? $this->assetUrl($ogImage) : null,
            'json_ld' => array_values(array_filter([
                $this->organizationSchema($settings, $siteName, $base),
                $this->breadcrumbSchema($crumbs, $base),
                ...$extraSchemas,
            ])),
        ];
    }

    private function organizationSchema(array $settings, string $siteName, string $base): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $siteName,
            'url' => $base.'/',
            'areaServed' => ['@type' => 'Country', 'name' => 'Kenya'],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => 'Nairobi',
                'addressCountry' => 'KE',
            ],
        ];

        // Only publish a phone number once it is a real one (placeholders contain "XXX").
        $phone = $this->filled($settings['phone'] ?? null);
        if ($phone && ! str_contains(strtoupper($phone), 'XXX')) {
            $schema['telephone'] = $phone;
        }

        $sameAs = array_values(array_filter(array_map(
            fn (string $key) => $this->filled($settings[$key] ?? null),
            ['social_facebook', 'social_instagram', 'social_x', 'social_linkedin', 'social_youtube', 'social_tiktok', 'google_business_url'],
        )));
        if ($sameAs) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /** @param array<int, array{0: string, 1: string}> $crumbs */
    private function breadcrumbSchema(array $crumbs, string $base): ?array
    {
        if (! $crumbs) {
            return null;
        }

        $items = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $base.'/']];
        foreach ($crumbs as $i => [$name, $path]) {
            $items[] = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => $name, 'item' => $base.$path];
        }

        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    private function faqSchema(): ?array
    {
        $faqs = Faq::published()->get();
        if ($faqs->isEmpty()) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqs->map(fn (Faq $faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq->answer)],
            ])->all(),
        ];
    }

    private function siteName(): string
    {
        return Setting::read('company_name', config('seo.site_name'));
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    /** Absolute URL for social-sharing images (site-relative defaults, uploads, or full URLs). */
    private function assetUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return $this->baseUrl().(str_starts_with($path, '/') ? $path : '/storage/'.ltrim($path, '/'));
    }

    /** Returns the trimmed string, or null when empty. */
    private function filled(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }
}
