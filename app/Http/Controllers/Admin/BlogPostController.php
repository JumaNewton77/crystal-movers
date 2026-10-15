<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Blog articles: add, edit, publish/unpublish, featured image, safe delete. Newest first. */
class BlogPostController extends CrudController
{
    protected function model(): string
    {
        return BlogPost::class;
    }

    protected function imageField(): ?string
    {
        return 'image';
    }

    protected function directory(): string
    {
        return 'blog';
    }

    public function index(): JsonResponse
    {
        return $this->ok(BlogPost::query()->orderByDesc('published_at')->orderByDesc('id')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge($this->withPublishDate($request));

        return parent::store($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::query()->findOrFail($id);
        $request->merge($this->withPublishDate($request, $post));

        return parent::update($request, $id);
    }

    /** A post that is published without a date gets today's date. */
    private function withPublishDate(Request $request, ?BlogPost $post = null): array
    {
        $publishing = $request->has('is_published') ? $request->boolean('is_published') : (bool) $post?->is_published;
        $hasDate = $request->filled('published_at') || ($post?->published_at !== null && ! $request->has('published_at'));

        return $publishing && ! $hasDate ? ['published_at' => now()->toDateString()] : [];
    }

    protected function rules(?Model $record): array
    {
        return [
            'title' => ['required', 'string', 'max:190'],
            'slug' => [
                'required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug')->ignore($record?->getKey()),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:30000'],
            'image_alt' => ['nullable', 'string', 'max:190'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
