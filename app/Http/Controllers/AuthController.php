<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Admin sign-in using Laravel's session cookie (Sanctum SPA authentication):
 * no tokens are issued and nothing is kept in localStorage.
 */
class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $guard = Auth::guard('web');

        // Inactive accounts are rejected by the is_active condition.
        if (! $guard->attempt([...$request->only('email', 'password'), 'is_active' => true])) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        /** @var User $user */
        $user = $guard->user();

        if (! $user->canAccessAdmin()) {
            $guard->logout();

            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate(); // prevents session fixation
        }

        return $this->ok(['user' => $this->present($user)], 'Signed in.');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->ok(null, 'Signed out.');
    }

    /** Returns the signed-in admin, or `user: null` (HTTP 200) so the admin app can show the login screen. */
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        return $this->ok(['user' => $user?->canAccessAdmin() ? $this->present($user) : null]);
    }

    /** @return array{id: int, name: string, email: string} */
    private function present(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
    }
}
