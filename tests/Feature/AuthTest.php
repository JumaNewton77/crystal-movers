<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** Sanctum treats requests from a stateful domain (Origin) as SPA session requests. */
    private function spa(): static
    {
        return $this->withHeaders(['Origin' => 'http://localhost', 'Referer' => 'http://localhost/admin/login']);
    }

    public function test_admin_can_log_in_and_read_profile(): void
    {
        $this->admin();

        $this->spa()->postJson('/api/auth/login', ['email' => 'admin@example.test', 'password' => 'correct-password-123'])
            ->assertOk()->assertJsonPath('data.user.email', 'admin@example.test');

        $this->spa()->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.user.email', 'admin@example.test');
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->admin();

        $this->spa()->postJson('/api/auth/login', ['email' => 'admin@example.test', 'password' => 'wrong'])
            ->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_inactive_admin_cannot_log_in(): void
    {
        $this->admin(['is_active' => false]);

        $this->spa()->postJson('/api/auth/login', ['email' => 'admin@example.test', 'password' => 'correct-password-123'])
            ->assertStatus(422);
    }

    public function test_guest_gets_null_user_from_me(): void
    {
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('data.user', null);
    }

    public function test_logout_ends_the_session(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->spa()->postJson('/api/auth/logout')->assertOk();
    }

    public function test_admin_routes_require_authentication(): void
    {
        foreach (['/api/admin/dashboard', '/api/admin/quotes', '/api/admin/messages', '/api/admin/services', '/api/admin/settings'] as $uri) {
            $this->getJson($uri)->assertStatus(401)->assertJsonPath('success', false);
        }
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = $this->admin(['role' => 'staff', 'email' => 'staff@example.test']);

        $this->actingAs($user)->getJson('/api/admin/dashboard')->assertStatus(403);
    }
}
