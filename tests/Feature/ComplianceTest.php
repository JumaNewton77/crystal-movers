<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Brand, removed/postponed services, secrets hygiene and admin protection. */
class ComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_service_is_gone_everywhere_public(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, Service::query()->where('slug', 'like', '%storage%')->count());
        $this->get('/services/storage')->assertNotFound();
        $this->get('/services/storage-and-warehousing')->assertNotFound();

        $site = strtolower((string) $this->getJson('/api/site')->getContent());
        $this->assertStringNotContainsString('warehous', $site);
        $this->assertStringNotContainsString('"slug":"storage', $site);
        $this->assertStringNotContainsString('/services/storage', (string) $this->get('/sitemap.xml')->getContent());
    }

    public function test_international_moving_is_coming_soon_only_and_not_bookable(): void
    {
        $this->seed(DatabaseSeeder::class);
        $service = Service::where('slug', 'international-moving')->firstOrFail();

        $this->assertSame('coming_soon', $service->status->value);
        $this->get('/services/international-moving')->assertOk()->assertSee('noindex', false);
        $this->assertStringNotContainsString('/services/international-moving', (string) $this->get('/sitemap.xml')->getContent());

        $this->postJson('/api/quote-requests', $this->quotePayload(['service_id' => $service->id]))
            ->assertStatus(422)->assertJsonValidationErrors(['service_id']);
    }

    public function test_customer_emails_use_the_crystal_movers_brand(): void
    {
        $this->seed(DatabaseSeeder::class);
        \Illuminate\Support\Facades\Mail::fake();

        $this->postJson('/api/quote-requests', $this->quotePayload())->assertCreated();

        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\QuoteReceivedCustomer::class, function ($mail) {
            $html = strtolower($mail->render());

            return str_contains($html, 'crystal movers') && ! str_contains($html, 'smart movers') && ! str_contains($html, 'smartmovers');
        });
    }

    public function test_env_example_is_a_safe_template(): void
    {
        $example = (string) file_get_contents(base_path('.env.example'));

        $this->assertStringNotContainsStringIgnoringCase('smartmovers', $example);
        $this->assertStringNotContainsStringIgnoringCase('smart movers', $example);
        $this->assertStringNotContainsString('admin123', $example);
        $this->assertMatchesRegularExpression('/^DB_PASSWORD=\s*$/m', $example);
        $this->assertMatchesRegularExpression('/^ADMIN_PASSWORD=\s*$/m', $example);
        $this->assertMatchesRegularExpression('/^APP_KEY=\s*$/m', $example);
    }

    public function test_gitignore_excludes_secrets_and_runtime_data(): void
    {
        $ignore = (string) file_get_contents(base_path('.gitignore'));

        foreach (['.env', '/vendor', '/node_modules', '/storage/logs/*', '/storage/app/private/*', '/storage/app/site-data.json', '/public/hot'] as $entry) {
            $this->assertStringContainsString($entry, $ignore, "{$entry} must be git-ignored");
        }
    }

    public function test_weak_admin_passwords_are_refused(): void
    {
        $this->artisan('admin:create', ['email' => 'owner@example.test', 'password' => 'admin123'])->assertFailed();
        $this->artisan('admin:create', ['email' => 'owner@example.test', 'password' => 'short-pass'])->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'owner@example.test']);

        $this->artisan('admin:create', ['email' => 'owner@example.test', 'password' => 'a-long-unique-pass-9'])->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'owner@example.test', 'role' => 'admin']);
    }

    public function test_admin_seeder_refuses_a_weak_password(): void
    {
        config(['crystalmovers.admin.email' => 'seed@example.test', 'crystalmovers.admin.password' => 'admin123']);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->assertDatabaseMissing('users', ['email' => 'seed@example.test']);

        config(['crystalmovers.admin.password' => 'Strong-Unique-Pass-42']);
        $this->seed(\Database\Seeders\AdminUserSeeder::class);
        $this->assertDatabaseHas('users', ['email' => 'seed@example.test']);
    }

    public function test_every_admin_endpoint_rejects_guests(): void
    {
        foreach (['quotes', 'messages', 'services', 'service-categories', 'faqs', 'testimonials', 'gallery', 'blog-posts', 'settings', 'content', 'dashboard'] as $path) {
            $this->getJson("/api/admin/{$path}")->assertStatus(401);
        }

        $this->postJson('/api/admin/blog-posts', [])->assertStatus(401);
        $this->deleteJson('/api/admin/messages/1')->assertStatus(401);
    }

    public function test_errors_do_not_leak_internals(): void
    {
        $body = (string) $this->getJson('/api/no-such-endpoint')->assertNotFound()->getContent();

        $this->assertStringNotContainsString('vendor/', $body);
        $this->assertStringNotContainsString('Stack trace', $body);
        $this->assertStringNotContainsString(base_path(), $body);
    }
}
