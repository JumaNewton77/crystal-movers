<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\Setting;
use App\Models\WebsiteContent;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Covers the Crystal Movers content brief: official defaults, new quote details, blog, apply-copy command. */
class MasterBriefTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_defaults_follow_the_brief(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame('Move Smarter. Live Better.', Setting::read('tagline'));
        $this->assertSame('+254 779 606 100', Setting::read('phone'));
        $this->assertSame('Monday–Saturday, 7:00 AM–6:00 PM', Setting::read('working_hours'));
        $this->assertSame('Safety and Care', Setting::read('insurance_heading'));
        $this->assertEmpty(Setting::read('email'), 'No invented business email may be seeded.');
        $this->assertSame(6, WebsiteContent::query()->where('key', 'like', 'why.%.title')->count());
        $this->assertSame(11, Faq::count());
        $this->assertSame(0, Faq::query()->where('question', 'like', '%storage%')->count());

        $body = $this->getJson('/api/site')->assertOk()->getContent();
        $this->assertStringNotContainsString('smartmovers', strtolower($body));
        $this->assertStringNotContainsString('info@crystalmovers', strtolower($body));
    }

    public function test_blog_posts_are_seeded_as_drafts_and_hidden(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(6, BlogPost::count());
        $this->assertSame(0, BlogPost::where('is_published', true)->count());
        $this->get('/blog/how-to-prepare-for-a-house-move')->assertNotFound();
        $this->assertSame([], $this->getJson('/api/site')->json('data.blog_posts'));
    }

    public function test_blog_admin_crud_and_public_visibility(): void
    {
        $admin = $this->admin();

        $this->postJson('/api/admin/blog-posts', [])->assertStatus(401);

        $id = $this->actingAs($admin)->postJson('/api/admin/blog-posts', [
            'title' => 'Wi-Fi tips', 'slug' => 'wifi-tips', 'body' => "Para one.\n\n## Heading\n\nPara two.", 'is_published' => false,
        ])->assertCreated()->json('data.id');

        $this->get('/blog/wifi-tips')->assertNotFound();

        // Duplicate slug rejected, bad slug rejected.
        $this->actingAs($admin)->postJson('/api/admin/blog-posts', ['title' => 'x', 'slug' => 'wifi-tips', 'body' => 'b'])->assertStatus(422)->assertJsonValidationErrors(['slug']);
        $this->actingAs($admin)->postJson('/api/admin/blog-posts', ['title' => 'x', 'slug' => 'Bad Slug', 'body' => 'b'])->assertStatus(422)->assertJsonValidationErrors(['slug']);

        $this->actingAs($admin)->putJson("/api/admin/blog-posts/{$id}", ['is_published' => true, 'meta_title' => 'Better Wi-Fi at home'])->assertOk();
        $this->assertNotNull(BlogPost::find($id)->published_at, 'Publishing without a date sets today.');

        $this->get('/blog')->assertOk()->assertSee('<title>', false);
        $this->get('/blog/wifi-tips')->assertOk()->assertSee('Better Wi-Fi at home', false)->assertSee('"@type":"Article"', false)->assertSee('og:type" content="article"', false);
        $this->get('/sitemap.xml')->assertSee('/blog/wifi-tips', false);
        $this->assertCount(1, $this->getJson('/api/site')->json('data.blog_posts'));

        $this->actingAs($admin)->deleteJson("/api/admin/blog-posts/{$id}")->assertOk();
        $this->get('/blog/wifi-tips')->assertNotFound();
    }

    public function test_quote_details_for_new_service_types_are_kept_and_unknown_ones_dropped(): void
    {
        $service = $this->makeService('furniture-assembly');

        $response = $this->postJson('/api/quote-requests', $this->quotePayload([
            'service_id' => $service->id,
            'location' => 'Westlands, Nairobi',
            'details' => ['furniture_type' => 'Wardrobe', 'quantity' => '3', 'assembly_type' => 'Assembly', 'evil' => '<script>'],
        ]))->assertCreated();

        $this->assertMatchesRegularExpression('/^CM-Q-\d{4}-\d{4}$/', $response->json('data.reference'));
        $details = \App\Models\QuoteRequest::query()->latest('id')->first()->details;
        $this->assertSame('Wardrobe', $details['furniture_type']);
        $this->assertSame('3', $details['quantity']);
        $this->assertArrayNotHasKey('evil', $details);
    }

    public function test_apply_copy_command_updates_existing_data_and_clears_placeholder_email(): void
    {
        $this->seed(DatabaseSeeder::class);
        Setting::where('key', 'email')->update(['value' => 'info@smartmovers.co.ke']);
        Setting::where('key', 'phone')->update(['value' => '+254 XXX XXX XXX']);
        WebsiteContent::where('key', 'cta.title')->update(['value' => 'Old']);
        Faq::create(['question' => 'Old FAQ?', 'answer' => 'x', 'is_published' => true, 'sort_order' => 99]);

        $this->artisan('site:apply-copy', ['--force' => true, '--retire-old-faqs' => true])->assertSuccessful();

        $this->assertEmpty(Setting::read('email'));
        $this->assertSame('+254 779 606 100', Setting::read('phone'));
        $this->assertSame('Need Help with a Move or Installation?', WebsiteContent::where('key', 'cta.title')->value('value'));
        $this->assertFalse((bool) Faq::where('question', 'Old FAQ?')->value('is_published'));
        $this->assertSame(11, Faq::where('is_published', true)->count());
    }

    public function test_homepage_and_contact_render(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertOk()->assertSee('Professional Moving and Installation Services You Can Rely On', false);
        $this->get('/contact')->assertOk()->assertSee('Contact Crystal Movers', false);
    }
}
