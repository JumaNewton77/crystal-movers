<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_eight_categories_and_no_storage_service(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertSame(8, ServiceCategory::count());
        $this->assertSame(0, Service::where('slug', 'like', '%storage%')->count());
        $this->assertSame('coming_soon', Service::where('slug', 'international-moving')->value('status')->value);
    }

    public function test_reseeding_never_overwrites_admin_edits(): void
    {
        $this->seed(ServiceSeeder::class);
        Service::where('slug', 'international-moving')->update(['title' => 'Edited by admin', 'image_path' => '/storage/services/x.webp']);
        ServiceCategory::where('slug', 'moving')->update(['name' => 'Renamed']);

        $this->seed(ServiceSeeder::class);

        $this->assertSame('Edited by admin', Service::where('slug', 'international-moving')->value('title'));
        $this->assertSame('/storage/services/x.webp', Service::where('slug', 'international-moving')->value('image_path'));
        $this->assertSame('Renamed', ServiceCategory::where('slug', 'moving')->value('name'));
    }

    public function test_seeded_services_have_no_invented_prices_or_images(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->assertSame(0, Service::whereNotNull('price_text')->count());
        $this->assertSame(0, Service::whereNotNull('image_path')->count());
    }

    public function test_unpublished_service_and_category_are_hidden_from_the_site_payload(): void
    {
        $this->seed(ServiceSeeder::class);
        Service::where('slug', 'international-moving')->update(['is_published' => false]);

        $this->getJson('/api/site')->assertOk()->assertDontSee('international-moving');
    }

    public function test_admin_can_create_edit_reorder_and_delete_categories_and_services(): void
    {
        $admin = $this->admin();

        $catId = $this->actingAs($admin)->postJson('/api/admin/service-categories', [
            'name' => 'Painting', 'slug' => 'painting', 'is_published' => true,
        ])->assertCreated()->json('data.id');
        $this->actingAs($admin)->postJson('/api/admin/service-categories', ['name' => 'X', 'slug' => 'painting'])
            ->assertStatus(422)->assertJsonValidationErrors(['slug']);

        $serviceId = $this->actingAs($admin)->postJson('/api/admin/services', [
            'service_category_id' => $catId, 'slug' => 'wall-painting', 'title' => 'Wall painting',
            'short_description' => 'Interior walls.', 'status' => 'active', 'is_published' => true,
            'price_text' => 'Quote on request', 'image_alt' => 'A painted wall',
        ])->assertCreated()->json('data.id');

        $this->assertDatabaseHas('services', ['id' => $serviceId, 'price_text' => 'Quote on request']);

        $this->actingAs($admin)->postJson('/api/admin/service-categories/reorder', ['ids' => [$catId]])->assertOk();
        $this->actingAs($admin)->deleteJson("/api/admin/services/{$serviceId}")->assertOk();
        $this->actingAs($admin)->deleteJson("/api/admin/service-categories/{$catId}")->assertOk();
        $this->assertDatabaseMissing('service_categories', ['id' => $catId]);
    }

    public function test_service_and_category_images_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $category = ServiceCategory::create(['name' => 'Moving', 'slug' => 'moving', 'is_published' => true, 'sort_order' => 1]);

        $upload = fn () => $this->actingAs($admin)->post("/api/admin/service-categories/{$category->id}", [
            '_method' => 'PUT', 'name' => 'Moving', 'slug' => 'moving', 'is_published' => '1',
            'image' => UploadedFile::fake()->image('c.jpg', 1200, 800),
        ], ['Accept' => 'application/json'])->assertOk();

        $upload();
        $first = $category->fresh()->image_path;
        $this->assertNotNull($first);

        $upload();
        $second = $category->fresh()->image_path;
        $this->assertNotSame($first, $second);

        $this->actingAs($admin)->post("/api/admin/service-categories/{$category->id}", [
            '_method' => 'PUT', 'name' => 'Moving', 'slug' => 'moving', 'is_published' => '1', 'remove_image' => '1',
        ], ['Accept' => 'application/json'])->assertOk();
        $this->assertNull($category->fresh()->image_path);
    }

    public function test_logo_and_favicon_upload_and_removal(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        Setting::create(['key' => 'logo_path', 'value' => null, 'group' => 'branding']);

        $this->actingAs($admin)->post('/api/admin/settings/logo_path/image', [
            'image' => UploadedFile::fake()->image('logo.png', 600, 300),
        ], ['Accept' => 'application/json'])->assertOk();
        $path = Setting::where('key', 'logo_path')->value('value');
        $this->assertNotEmpty($path);

        $this->actingAs($admin)->deleteJson('/api/admin/settings/logo_path/image')->assertOk();
        $this->assertNull(Setting::where('key', 'logo_path')->value('value'));

        $this->actingAs($admin)->deleteJson('/api/admin/settings/company_name/image')->assertNotFound();
    }

    public function test_enquiry_can_be_filtered_by_category_and_service_in_admin(): void
    {
        Mail::fake();
        $cctv = $this->makeService('cctv');
        $this->postJson('/api/quote-requests', $this->quotePayload(['name' => 'Moving Customer']))->assertCreated();
        $this->postJson('/api/quote-requests', $this->quotePayload([
            'name' => 'Camera Customer', 'service_id' => $cctv->id, 'location' => 'Nairobi', 'from_location' => null, 'to_location' => null,
        ]))->assertCreated();

        $admin = $this->admin();
        $byService = $this->actingAs($admin)->getJson('/api/admin/quotes?service_id='.$cctv->id)->assertOk();
        $this->assertSame(['Camera Customer'], collect($byService->json('data.items'))->pluck('name')->all());

        $byCategory = $this->actingAs($admin)->getJson('/api/admin/quotes?category_id='.$cctv->service_category_id)->assertOk();
        $this->assertCount(1, $byCategory->json('data.items'));
        $this->assertSame(2, QuoteRequest::count());
    }

    public function test_sitemap_lists_published_services_only(): void
    {
        $live = $this->makeService('moving', ['slug' => 'live-one']);
        $this->makeService('moving', ['slug' => 'hidden-one', 'is_published' => false]);

        $this->get('/sitemap.xml')->assertOk()->assertSee('/services/'.$live->slug, false)->assertDontSee('hidden-one', false);
    }

    public function test_category_page_renders_and_unpublished_category_is_404(): void
    {
        $service = $this->makeService('cctv');
        $this->get('/services/category/cctv')->assertOk()->assertSee('application/ld+json', false);
        $this->get('/sitemap.xml')->assertSee('/services/category/cctv', false);

        $service->category->update(['is_published' => false]);
        $this->get('/services/category/cctv')->assertNotFound();
    }

    public function test_images_dropped_into_public_images_site_are_used_and_admin_upload_wins(): void
    {
        // Uses its own service slug and only removes files this test created, so a real project's images are never touched.
        $slug = 'zz-test-service-image';
        $service = $this->makeService('cctv', ['slug' => $slug]);
        $dir = public_path('images/site');
        @mkdir($dir.'/services', 0777, true);
        $created = [];
        $write = function (string $file, string $content) use (&$created): void {
            if (! file_exists($file)) {
                file_put_contents($file, $content);
                $created[] = $file;
            }
        };
        $hasLogo = (bool) glob($dir.'/logo.{webp,jpg,jpeg,png,svg}', GLOB_BRACE);
        if (! $hasLogo) {
            $write($dir.'/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
        }
        $write($dir."/services/{$slug}.png", 'x');
        Storage::fake('public');
        Storage::disk('public')->put('services/own.webp', 'x');

        try {
            \Illuminate\Support\Facades\Cache::flush();
            $data = $this->getJson('/api/site')->assertOk()->json('data');
            $this->assertStringStartsWith('/images/site/logo.', $data['settings']['logo']);
            $this->assertStringStartsWith("/images/site/services/{$slug}.png", collect($data['services'])->firstWhere('slug', $slug)['image']);

            $service->update(['image_path' => '/storage/services/own.webp']);
            \Illuminate\Support\Facades\Cache::flush();
            $data = $this->getJson('/api/site')->json('data');
            $this->assertSame('/storage/services/own.webp', collect($data['services'])->firstWhere('slug', $slug)['image']);
        } finally {
            foreach ($created as $file) {
                @unlink($file);
            }
        }
    }

    public function test_stale_logo_path_pointing_to_a_missing_file_is_ignored(): void
    {
        Setting::create(['key' => 'logo_path', 'value' => '/images/brand/old-logo.svg', 'group' => 'branding']);

        $logo = $this->getJson('/api/site')->json('data.settings.logo');

        // The stale database path is ignored; only a real file from public/images/site (if any) may be used.
        $this->assertTrue($logo === null || str_starts_with($logo, '/images/site/logo.'));
        $this->assertStringNotContainsString('old-logo', (string) $logo);
    }
}
