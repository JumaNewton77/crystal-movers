<?php

namespace App\Http\Controllers;

use App\Enums\ServiceStatus;
use App\Models\BlogPost;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Content;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $urls = [];

        foreach (config('seo.pages') as $key => $page) {
            $urls[] = [
                'loc' => $base.$page['path'],
                'priority' => $key === 'home' ? '1.0' : '0.7',
                'lastmod' => null,
            ];
        }

        // Only live services. "Coming soon" pages are noindex and kept out of the sitemap.
        $services = Service::published()->where('status', ServiceStatus::Active->value)->get();
        foreach ($services as $service) {
            $urls[] = [
                'loc' => $base.'/services/'.$service->slug,
                'priority' => '0.8',
                'lastmod' => $service->updated_at?->toAtomString(),
            ];
        }

        foreach (ServiceCategory::query()->where('is_published', true)->get() as $category) {
            $urls[] = ['loc' => $base.'/services/category/'.$category->slug, 'priority' => '0.8', 'lastmod' => null];
        }

        foreach (BlogPost::published()->get() as $post) {
            $urls[] = ['loc' => $base.'/blog/'.$post->slug, 'priority' => '0.6', 'lastmod' => $post->updated_at?->toAtomString()];
        }

        foreach (Content::locations() as $location) {
            $urls[] = ['loc' => $base.'/movers-in-'.$location['slug'], 'priority' => '0.8', 'lastmod' => null];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n    <loc>".htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc>\n";
            if ($url['lastmod']) {
                $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            }
            $xml .= "    <priority>{$url['priority']}</priority>\n  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $base = rtrim((string) config('app.url'), '/');

        $body = "User-agent: *\n"
            ."Disallow: /admin\n"
            ."Disallow: /api/\n"
            ."Allow: /\n\n"
            ."Sitemap: {$base}/sitemap.xml\n";

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
