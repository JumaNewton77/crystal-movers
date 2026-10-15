<?php

namespace Tests\Feature;

use App\Mail\QuoteReceivedAdmin;
use App\Mail\QuoteReceivedCustomer;
use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_quote_is_saved_with_reference_and_emails_are_sent(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/quote-requests', $this->quotePayload());

        $response->assertCreated()->assertJsonPath('success', true);
        $reference = $response->json('data.reference');
        $this->assertStringStartsWith('CM-Q', $reference);

        $this->assertDatabaseHas('quote_requests', ['reference_number' => $reference, 'name' => 'Jane Wanjiku', 'status' => 'new']);
        $this->assertDatabaseHas('quote_status_history', ['to_status' => 'new']);

        Mail::assertSent(QuoteReceivedCustomer::class);
        Mail::assertSent(QuoteReceivedAdmin::class);
    }

    public function test_references_are_unique_and_sequential(): void
    {
        Mail::fake();

        $a = $this->postJson('/api/quote-requests', $this->quotePayload())->json('data.reference');
        $b = $this->postJson('/api/quote-requests', $this->quotePayload())->json('data.reference');

        $this->assertNotSame($a, $b);
        $this->assertSame(2, QuoteRequest::count());
    }

    public function test_required_fields_are_validated_with_structured_errors(): void
    {
        $this->postJson('/api/quote-requests', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['name', 'phone', 'service_id', 'contact_consent']);

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_past_date_and_bad_phone_are_rejected(): void
    {
        $this->postJson('/api/quote-requests', $this->quotePayload([
            'preferred_date' => now('Africa/Nairobi')->subDay()->toDateString(),
            'phone' => 'abc',
        ]))->assertStatus(422)->assertJsonValidationErrors(['preferred_date', 'phone']);
    }

    public function test_unknown_unpublished_and_coming_soon_services_are_rejected(): void
    {
        $hidden = $this->makeService('cctv', ['is_published' => false]);
        $soon = $this->makeService('moving', ['status' => 'coming_soon']);

        foreach ([99999, $hidden->id, $soon->id] as $id) {
            $this->postJson('/api/quote-requests', $this->quotePayload(['service_id' => $id]))
                ->assertStatus(422)->assertJsonValidationErrors(['service_id']);
        }
        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_moving_service_requires_from_and_to_locations(): void
    {
        $this->postJson('/api/quote-requests', $this->quotePayload(['from_location' => '', 'to_location' => '']))
            ->assertStatus(422)->assertJsonValidationErrors(['from_location', 'to_location']);
    }

    public function test_non_moving_service_requires_a_location_but_not_from_to(): void
    {
        Mail::fake();
        $cctv = $this->makeService('cctv');
        $base = ['service_id' => $cctv->id, 'from_location' => null, 'to_location' => null];

        $this->postJson('/api/quote-requests', $this->quotePayload($base))
            ->assertStatus(422)->assertJsonValidationErrors(['location']);

        $this->postJson('/api/quote-requests', $this->quotePayload($base + [
            'location' => 'Kileleshwa, Nairobi',
            'details' => ['property_type' => 'Apartment', 'camera_count' => '4'],
        ]))->assertCreated();

        $quote = QuoteRequest::firstOrFail();
        $this->assertSame($cctv->id, $quote->service_id);
        $this->assertSame('Kileleshwa, Nairobi', $quote->location);
    }

    public function test_consent_is_required(): void
    {
        $this->postJson('/api/quote-requests', $this->quotePayload(['contact_consent' => '']))
            ->assertStatus(422)->assertJsonValidationErrors(['contact_consent']);
    }

    public function test_html_is_stripped_from_text_fields(): void
    {
        Mail::fake();

        $this->postJson('/api/quote-requests', $this->quotePayload(['message' => 'Hello <script>alert(1)</script>there']))->assertCreated();

        $this->assertStringNotContainsString('<script>', QuoteRequest::first()->message);
    }

    public function test_photo_and_video_attachments_are_stored_privately(): void
    {
        Mail::fake();
        Storage::fake('local');

        $payload = $this->quotePayload([
            'attachments' => [
                UploadedFile::fake()->image('sofa.jpg', 800, 600),
                UploadedFile::fake()->create('room.mp4', 500, 'video/mp4'),
            ],
        ]);

        $this->post('/api/quote-requests', $payload, ['Accept' => 'application/json'])->assertCreated();

        $quote = QuoteRequest::with('attachments')->first();
        $this->assertCount(2, $quote->attachments);
        foreach ($quote->attachments as $attachment) {
            Storage::disk('local')->assertExists($attachment->path);
        }
    }

    public function test_disallowed_file_types_are_rejected(): void
    {
        Storage::fake('local');

        $payload = $this->quotePayload(['attachments' => [UploadedFile::fake()->create('shell.php', 10, 'application/x-php')]]);

        $this->post('/api/quote-requests', $payload, ['Accept' => 'application/json'])->assertStatus(422);
        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_more_than_five_files_are_rejected(): void
    {
        Storage::fake('local');

        $files = array_map(fn ($i) => UploadedFile::fake()->image("p{$i}.jpg"), range(1, 6));

        $this->post('/api/quote-requests', $this->quotePayload(['attachments' => $files]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors(['attachments']);
    }

    public function test_honeypot_returns_fake_success_without_saving(): void
    {
        Mail::fake();

        $this->postJson('/api/quote-requests', $this->quotePayload(['website' => 'http://spam.example']))->assertCreated();

        $this->assertSame(0, QuoteRequest::count());
        Mail::assertNothingSent();
    }
}
