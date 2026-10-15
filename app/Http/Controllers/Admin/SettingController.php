<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Company details, contact info, social links, Google rating, insurance wording and default SEO.
 * Only settings that already exist (seeded) can be changed; unknown keys are ignored.
 */
class SettingController extends Controller
{
    /** Image settings are changed through the upload endpoint, not the text form. */
    private const IMAGE_KEYS = ['logo_path', 'favicon_path', 'seo_og_image'];

    public function index(): JsonResponse
    {
        return $this->ok(
            Setting::query()->orderBy('group')->orderBy('id')->get(['key', 'value', 'group'])
        );
    }

    public function update(Request $request): JsonResponse
    {
        $input = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:2000'],
        ])['settings'];

        $known = Setting::query()->pluck('key')->all();
        $clean = [];
        $errors = [];

        foreach ($input as $key => $value) {
            $key = (string) $key;
            if (! in_array($key, $known, true) || in_array($key, self::IMAGE_KEYS, true)) {
                continue;
            }

            $value = $this->normalise($key, $value);
            $validator = Validator::make(['value' => $value], ['value' => $this->rulesFor($key)]);

            if ($validator->fails()) {
                $errors["settings.{$key}"] = $validator->errors()->get('value');

                continue;
            }

            $clean[$key] = $value;
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        foreach ($clean as $key => $value) {
            Setting::query()->where('key', $key)->firstOrFail()->update(['value' => $value]);
        }

        return $this->ok($this->index()->getData(true)['data'], 'Settings saved.');
    }

    /** POST /settings/{key}/image: logo and the default social-sharing image. */
    public function image(Request $request, string $key, ImageOptimizer $images): JsonResponse
    {
        abort_unless(in_array($key, self::IMAGE_KEYS, true), 404);
        $setting = Setting::query()->where('key', $key)->firstOrFail();

        $request->validate(['image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192']]);

        $path = $images->store($request->file('image'), 'branding');
        $images->delete($setting->value);
        $setting->update(['value' => $path]);

        return $this->ok(['key' => $key, 'value' => $path], 'Image saved.');
    }

    /** DELETE /settings/{key}/image: removes the uploaded file and returns the site to the neutral fallback. */
    public function removeImage(string $key, ImageOptimizer $images): JsonResponse
    {
        abort_unless(in_array($key, self::IMAGE_KEYS, true), 404);
        $setting = Setting::query()->where('key', $key)->firstOrFail();

        $images->delete($setting->value);
        $setting->update(['value' => null]);

        return $this->ok(['key' => $key, 'value' => null], 'Image removed.');
    }

    private function normalise(string $key, ?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($key === 'whatsapp_number') {
            $digits = preg_replace('/\D+/', '', $value) ?? '';
            // 0712 345 678 -> 254712345678. Placeholders (too few digits) are kept as typed.
            if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
                $digits = '254'.substr($digits, 1);
            }

            return strlen($digits) >= 9 && strlen($digits) <= 15 ? $digits : $value;
        }

        // Allow pasting the full Google Maps <iframe> embed code: keep only its src.
        if ($key === 'google_maps_embed_url' && str_contains($value, '<iframe')) {
            return preg_match('/src="([^"]+)"/i', $value, $m) ? html_entity_decode($m[1]) : $value;
        }

        return $value;
    }

    /** @return array<int, string> */
    private function rulesFor(string $key): array
    {
        return match (true) {
            $key === 'google_maps_embed_url' => ['nullable', 'url:https', 'starts_with:https://www.google.com/maps,https://maps.google.com', 'max:1000'],
            str_ends_with($key, '_url'), str_starts_with($key, 'social_') => ['nullable', 'url:http,https', 'max:500'],
            $key === 'google_rating' => ['nullable', 'numeric', 'between:0,5'],
            $key === 'google_review_count' => ['nullable', 'integer', 'min:0'],
            in_array($key, ['email', 'notification_email'], true) => ['nullable', 'email', 'max:190'],
            default => ['nullable', 'string', 'max:2000'],
        };
    }
}
