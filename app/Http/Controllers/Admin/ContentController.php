<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebsiteContent;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Selected editable website text and images (hero, about, why choose us, CTA, safety, per-page SEO).
 * Deliberately simple: a fixed set of keys, no page builder.
 */
class ContentController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->ok(
            WebsiteContent::query()->orderBy('group')->orderBy('id')->get(['key', 'value', 'group'])
        );
    }

    public function update(Request $request): JsonResponse
    {
        $input = $request->validate([
            'content' => ['required', 'array'],
            'content.*' => ['nullable', 'string', 'max:5000'],
        ])['content'];

        $rows = WebsiteContent::query()->whereIn('key', array_map('strval', array_keys($input)))->get();

        foreach ($rows as $row) {
            // Images are changed through the upload endpoint only.
            if (str_ends_with($row->key, '.image')) {
                continue;
            }

            $row->update(['value' => $input[$row->key] ?? null]);
        }

        return $this->index();
    }

    /** DELETE /content/{key}/image: removes the uploaded file; the website then shows a neutral placeholder. */
    public function removeImage(string $key, ImageOptimizer $images): JsonResponse
    {
        abort_unless(str_ends_with($key, '.image'), 404);
        $row = WebsiteContent::query()->where('key', $key)->firstOrFail();

        $images->delete($row->value);
        $row->update(['value' => null]);

        return $this->ok(['key' => $key, 'value' => null], 'Image removed.');
    }

    /** POST /content/{key}/image (keys ending in ".image", e.g. home.hero.image). */
    public function image(Request $request, string $key, ImageOptimizer $images): JsonResponse
    {
        abort_unless(str_ends_with($key, '.image'), 404);
        $row = WebsiteContent::query()->where('key', $key)->firstOrFail();

        $request->validate(['image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192']]);

        $path = $images->store($request->file('image'), 'content');
        $images->delete($row->value);
        $row->update(['value' => $path]);

        return $this->ok(['key' => $key, 'value' => $path], 'Image saved.');
    }
}
