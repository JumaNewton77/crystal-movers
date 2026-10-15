<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $o = []): array
    {
        return array_merge(['customer_name' => 'Grace M.', 'location' => 'Nakuru', 'rating' => 5, 'review' => 'The crew was careful and on time.'], $o);
    }

    public function test_public_review_is_saved_as_pending_and_not_shown(): void
    {
        $this->postJson('/api/testimonials', $this->payload())->assertCreated();

        $this->assertDatabaseHas('testimonials', ['customer_name' => 'Grace M.', 'status' => 'pending']);
        $this->assertStringNotContainsString('Grace M.', $this->getJson('/api/site')->getContent());
    }

    public function test_review_is_validated(): void
    {
        $this->postJson('/api/testimonials', ['rating' => 9, 'review' => 'short'])
            ->assertStatus(422)->assertJsonValidationErrors(['customer_name', 'rating', 'review']);
        $this->assertSame(0, Testimonial::count());
    }

    public function test_honeypot_saves_nothing(): void
    {
        $this->postJson('/api/testimonials', $this->payload(['website' => 'spam']))->assertCreated();
        $this->assertSame(0, Testimonial::count());
    }

    public function test_admin_create_command_makes_a_working_admin(): void
    {
        $this->artisan('admin:create', ['email' => 'Boss@Example.test', 'password' => 'a-strong-pass-1'])->assertSuccessful();

        $user = User::where('email', 'boss@example.test')->firstOrFail();
        $this->assertTrue($user->canAccessAdmin());
    }

    public function test_admin_create_rejects_short_password(): void
    {
        $this->artisan('admin:create', ['email' => 'a@example.test', 'password' => 'short'])->assertFailed();
    }
}
