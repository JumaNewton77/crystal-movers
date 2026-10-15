<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_payload_contains_only_published_content(): void
    {
        Faq::create(['question' => 'Visible?', 'answer' => 'Yes', 'is_published' => true, 'sort_order' => 1]);
        Faq::create(['question' => 'Secret?', 'answer' => 'No', 'is_published' => false, 'sort_order' => 2]);
        Service::create(['slug' => 'hidden-service', 'title' => 'Hidden', 'short_description' => 'x', 'status' => 'active', 'is_published' => false, 'sort_order' => 1]);

        $body = $this->getJson('/api/site')->assertOk()->assertJsonPath('success', true)->getContent();

        $this->assertStringContainsString('Visible?', $body);
        $this->assertStringNotContainsString('Secret?', $body);
        $this->assertStringNotContainsString('hidden-service', $body);
    }

    public function test_pages_render_with_seo_tags(): void
    {
        $this->get('/')->assertOk()->assertSee('<title>', false)->assertSee('rel="canonical"', false)->assertSee('application/ld+json', false);
        $this->get('/movers-in-nairobi')->assertOk()->assertSee('Movers in Nairobi', false);
    }

    public function test_unknown_page_returns_404_noindex(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('noindex', false);
    }

    public function test_sitemap_and_robots(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', false)->assertSee('/movers-in-nairobi', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap:', false)->assertSee('Disallow: /admin', false);
    }

    public function test_admin_shell_is_noindex_and_private_api_not_exposed(): void
    {
        $this->get('/admin')->assertOk()->assertSee('noindex', false);
        $this->getJson('/api/admin/quotes')->assertStatus(401);
    }

    public function test_health_endpoint(): void
    {
        $this->getJson('/api/health')->assertOk();
    }
}
