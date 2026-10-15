<?php

namespace Tests\Feature;

use App\Mail\ContactReceivedAdmin;
use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $o = []): array
    {
        return array_merge([
            'name' => 'John Otieno',
            'email' => 'john@example.com',
            'phone' => '0712345678',
            'subject' => 'Question',
            'message' => 'Do you move offices in Nakuru?',
            'contact_consent' => '1',
        ], $o);
    }

    public function test_valid_message_is_saved_and_admin_notified(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', $this->payload())->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('contact_messages', ['email' => 'john@example.com', 'status' => 'new', 'contact_consent' => true]);
        Mail::assertSent(ContactReceivedAdmin::class);
    }

    public function test_invalid_message_is_rejected(): void
    {
        $this->postJson('/api/contact', ['email' => 'not-an-email'])
            ->assertStatus(422)->assertJsonPath('success', false)->assertJsonValidationErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_consent_is_required(): void
    {
        $this->postJson('/api/contact', $this->payload(['contact_consent' => '']))
            ->assertStatus(422)->assertJsonValidationErrors(['contact_consent']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_honeypot_saves_nothing(): void
    {
        Mail::fake();

        $this->postJson('/api/contact', $this->payload(['website' => 'spam']))->assertCreated();

        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }
}
