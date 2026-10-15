<?php

namespace Tests\Feature;

use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminQuoteTest extends TestCase
{
    use RefreshDatabase;

    private function makeQuote(): QuoteRequest
    {
        Mail::fake();
        $reference = $this->postJson('/api/quote-requests', $this->quotePayload())->json('data.reference');

        return QuoteRequest::where('reference_number', $reference)->firstOrFail();
    }

    public function test_admin_can_list_search_and_filter_quotes(): void
    {
        $quote = $this->makeQuote();
        $admin = $this->admin();

        $this->actingAs($admin)->getJson('/api/admin/quotes')
            ->assertOk()->assertJsonPath('data.meta.total', 1)->assertJsonPath('data.items.0.reference', $quote->reference_number);

        $this->actingAs($admin)->getJson('/api/admin/quotes?search=Wanjiku')->assertJsonPath('data.meta.total', 1);
        $this->actingAs($admin)->getJson('/api/admin/quotes?search=nobody-here')->assertJsonPath('data.meta.total', 0);
        $this->actingAs($admin)->getJson('/api/admin/quotes?status=completed')->assertJsonPath('data.meta.total', 0);
    }

    public function test_admin_can_change_status_and_history_is_recorded(): void
    {
        $quote = $this->makeQuote();

        $this->actingAs($this->admin())
            ->patchJson("/api/admin/quotes/{$quote->id}", ['status' => 'contacted', 'status_note' => 'Called the customer'])
            ->assertOk()->assertJsonPath('data.status', 'contacted');

        $this->assertDatabaseHas('quote_requests', ['id' => $quote->id, 'status' => 'contacted']);
        $this->assertDatabaseHas('quote_status_history', ['quote_request_id' => $quote->id, 'from_status' => 'new', 'to_status' => 'contacted']);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $quote = $this->makeQuote();

        $this->actingAs($this->admin())->patchJson("/api/admin/quotes/{$quote->id}", ['status' => 'banana'])->assertStatus(422);
    }

    public function test_admin_can_save_internal_notes_that_stay_private(): void
    {
        $quote = $this->makeQuote();

        $this->actingAs($this->admin())
            ->patchJson("/api/admin/quotes/{$quote->id}", ['admin_notes' => 'Needs a 7-tonne truck'])
            ->assertOk()->assertJsonPath('data.admin_notes', 'Needs a 7-tonne truck');

        $this->assertDatabaseHas('quote_requests', ['id' => $quote->id, 'admin_notes' => 'Needs a 7-tonne truck']);

        // Notes never appear in the public payload.
        $this->getJson('/api/site')->assertOk();
        $this->assertStringNotContainsString('7-tonne', $this->getJson('/api/site')->getContent());
    }

    public function test_guest_cannot_change_a_quote(): void
    {
        $quote = $this->makeQuote();

        $this->patchJson("/api/admin/quotes/{$quote->id}", ['status' => 'contacted'])->assertStatus(401);
        $this->assertDatabaseHas('quote_requests', ['id' => $quote->id, 'status' => 'new']);
    }

    public function test_dashboard_counts_new_requests_and_messages(): void
    {
        $this->makeQuote();

        $this->actingAs($this->admin())->getJson('/api/admin/dashboard')
            ->assertOk()->assertJsonPath('data.counts.new_quote_requests', 1)->assertJsonPath('data.counts.upcoming_moves', 1);
    }
}
