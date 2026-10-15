<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use App\Services\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SecurityAndImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_attachments_are_private_and_admin_only(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/api/quote-requests', $this->quotePayload([
            'attachments' => [UploadedFile::fake()->image('sofa.jpg', 800, 600)],
        ]), ['Accept' => 'application/json'])->assertCreated();

        $quote = QuoteRequest::with('attachments')->firstOrFail();
        $attachment = $quote->attachments->first();
        $url = "/api/admin/quotes/{$quote->id}/attachments/{$attachment->id}";

        // Not reachable by guests, not on the public disk, no public URL.
        $this->getJson($url)->assertStatus(401);
        Storage::disk('public')->assertMissing($attachment->path);
        $this->assertStringNotContainsString('/storage/', json_encode($attachment->toArray()));

        $this->actingAs($this->admin())->get($url)->assertOk()->assertHeader('Content-Security-Policy');
    }

    public function test_attachment_cannot_be_read_through_another_quote(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/api/quote-requests', $this->quotePayload([
            'attachments' => [UploadedFile::fake()->image('a.jpg', 800, 600)],
        ]), ['Accept' => 'application/json'])->assertCreated();
        $this->postJson('/api/quote-requests', $this->quotePayload(['name' => 'Other']))->assertCreated();

        $first = QuoteRequest::with('attachments')->where('name', 'Jane Wanjiku')->firstOrFail();
        $other = QuoteRequest::where('name', 'Other')->firstOrFail();

        $this->actingAs($this->admin())
            ->get("/api/admin/quotes/{$other->id}/attachments/{$first->attachments->first()->id}")
            ->assertNotFound();
    }

    public function test_default_images_and_traversal_paths_are_never_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('secret.txt', 'x');

        $optimizer = new ImageOptimizer;
        $optimizer->delete('/images/defaults/example.webp');
        $optimizer->delete('../secret.txt');
        $optimizer->delete('https://example.com/a.jpg');

        Storage::disk('public')->assertExists('secret.txt');
    }

    public function test_tiny_images_are_rejected(): void
    {
        Storage::fake('public');

        $this->expectException(ValidationException::class);
        (new ImageOptimizer)->store(UploadedFile::fake()->image('tiny.png', 50, 50), 'gallery');
    }

    public function test_replacing_an_uploaded_image_removes_the_old_one(): void
    {
        Storage::fake('public');
        $optimizer = new ImageOptimizer;

        $old = $optimizer->store(UploadedFile::fake()->image('a.jpg', 1000, 700), 'content');
        Storage::disk('public')->assertExists($old);

        $optimizer->delete($old);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_json_ld_is_present_and_no_og_image_is_invented(): void
    {
        $this->get('/')->assertSee('LocalBusiness', false)->assertDontSee('/images/defaults', false);
    }
}
