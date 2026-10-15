<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\WebsiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_crud_and_reorder(): void
    {
        $admin = $this->admin();

        $id = $this->actingAs($admin)->postJson('/api/admin/faqs', ['question' => 'Q1?', 'answer' => 'A1', 'is_published' => true])
            ->assertCreated()->json('data.id');
        $second = $this->actingAs($admin)->postJson('/api/admin/faqs', ['question' => 'Q2?', 'answer' => 'A2', 'is_published' => true])
            ->json('data.id');

        $this->actingAs($admin)->putJson("/api/admin/faqs/{$id}", ['answer' => 'Updated'])->assertOk();
        $this->assertDatabaseHas('faqs', ['id' => $id, 'answer' => 'Updated', 'question' => 'Q1?']);

        $this->actingAs($admin)->postJson('/api/admin/faqs/reorder', ['ids' => [$second, $id]])->assertOk();
        $this->assertTrue(Faq::find($second)->sort_order < Faq::find($id)->sort_order);

        $this->actingAs($admin)->deleteJson("/api/admin/faqs/{$id}")->assertOk();
        $this->assertDatabaseMissing('faqs', ['id' => $id]);
    }

    public function test_faq_validation(): void
    {
        $this->actingAs($this->admin())->postJson('/api/admin/faqs', ['question' => ''])->assertStatus(422)->assertJsonValidationErrors(['question', 'answer']);
    }

    public function test_service_crud_and_unique_slug(): void
    {
        $admin = $this->admin();
        $payload = [
            'slug' => 'office-moving', 'title' => 'Office Moving', 'short_description' => 'Moves offices.',
            'status' => 'active', 'is_published' => true, 'benefits' => "Fast\nSafe",
        ];

        $id = $this->actingAs($admin)->postJson('/api/admin/services', $payload)->assertCreated()->json('data.id');
        $this->actingAs($admin)->postJson('/api/admin/services', $payload)->assertStatus(422)->assertJsonValidationErrors(['slug']);

        $this->actingAs($admin)->putJson("/api/admin/services/{$id}", ['title' => 'Office Relocation', 'status' => 'coming_soon'])->assertOk();
        $this->assertDatabaseHas('services', ['id' => $id, 'title' => 'Office Relocation', 'status' => 'coming_soon', 'slug' => 'office-moving']);

        $this->actingAs($admin)->deleteJson("/api/admin/services/{$id}")->assertOk();
        $this->assertNull(Service::find($id));
    }

    public function test_gallery_requires_image_and_stores_optimised_upload(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->postJson('/api/admin/gallery', ['title' => 'Loading'])->assertStatus(422)->assertJsonValidationErrors(['image']);

        $response = $this->actingAs($admin)->post('/api/admin/gallery', [
            'title' => 'Loading day', 'alt_text' => 'Truck being loaded', 'is_published' => '1',
            'image' => UploadedFile::fake()->image('truck.jpg', 1200, 800),
        ], ['Accept' => 'application/json'])->assertCreated();

        $item = GalleryItem::find($response->json('data.id'));
        $this->assertNotNull($item->image_path);
        Storage::disk('public')->assertExists($item->image_path);

        $this->actingAs($admin)->deleteJson("/api/admin/gallery/{$item->id}")->assertOk();
        $this->assertDatabaseMissing('gallery', ['id' => $item->id]);
    }

    public function test_gallery_rejects_non_images(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/api/admin/gallery', [
            'title' => 'Bad', 'image' => UploadedFile::fake()->create('x.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertStatus(422);
    }

    public function test_testimonial_approval_controls_public_visibility(): void
    {
        $admin = $this->admin();

        $id = $this->actingAs($admin)->postJson('/api/admin/testimonials', [
            'customer_name' => 'Real Customer', 'rating' => 5, 'review' => 'Great service.', 'status' => 'pending',
        ])->assertCreated()->json('data.id');

        $this->assertStringNotContainsString('Real Customer', $this->getJson('/api/site')->getContent());

        $this->actingAs($admin)->patchJson("/api/admin/testimonials/{$id}", ['status' => 'approved'])->assertOk();
        $this->assertSame('approved', Testimonial::find($id)->status->value);
        $this->assertStringContainsString('Real Customer', $this->getJson('/api/site')->getContent());

        $this->actingAs($admin)->patchJson("/api/admin/testimonials/{$id}", ['status' => 'hidden'])->assertOk();
        $this->assertStringNotContainsString('Real Customer', $this->getJson('/api/site')->getContent());
    }

    public function test_testimonial_rating_must_be_1_to_5(): void
    {
        $this->actingAs($this->admin())->postJson('/api/admin/testimonials', [
            'customer_name' => 'X', 'rating' => 9, 'review' => 'Hi', 'status' => 'pending',
        ])->assertStatus(422)->assertJsonValidationErrors(['rating']);
    }

    public function test_settings_update_and_whatsapp_normalisation(): void
    {
        Setting::create(['key' => 'whatsapp_number', 'value' => '+254 XXX XXX XXX', 'group' => 'contact']);
        Setting::create(['key' => 'company_name', 'value' => 'Crystal Movers', 'group' => 'general']);

        $this->actingAs($this->admin())->putJson('/api/admin/settings', ['settings' => [
            'whatsapp_number' => '0712 345 678', 'company_name' => 'Crystal Movers Ltd', 'unknown_key' => 'ignored',
        ]])->assertOk();

        $this->assertDatabaseHas('settings', ['key' => 'whatsapp_number', 'value' => '254712345678']);
        $this->assertDatabaseHas('settings', ['key' => 'company_name', 'value' => 'Crystal Movers Ltd']);
        $this->assertDatabaseMissing('settings', ['key' => 'unknown_key']);
    }

    public function test_website_content_text_and_image_can_be_changed(): void
    {
        Storage::fake('public');
        WebsiteContent::create(['key' => 'hero.title', 'value' => 'Old', 'group' => 'hero']);
        WebsiteContent::create(['key' => 'home.hero.image', 'value' => '/storage/website/old-hero.webp', 'group' => 'images']);
        $admin = $this->admin();

        $this->actingAs($admin)->putJson('/api/admin/content', ['content' => ['hero.title' => 'New headline']])->assertOk();
        $this->assertDatabaseHas('website_content', ['key' => 'hero.title', 'value' => 'New headline']);

        $this->actingAs($admin)->post('/api/admin/content/home.hero.image/image', [
            'image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
        ], ['Accept' => 'application/json'])->assertOk();

        $path = WebsiteContent::where('key', 'home.hero.image')->value('value');
        $this->assertStringNotContainsString('old-hero', $path);
        Storage::disk('public')->assertExists($path);
    }
}
