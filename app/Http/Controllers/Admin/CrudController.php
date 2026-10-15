<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizer;
use App\Services\SiteDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Small generic CRUD base for the simple content resources (services, FAQs,
 * testimonials, gallery). Subclasses only declare their model, rules and (optionally) an image field.
 *
 * Updates are PARTIAL: only fields present in the request are validated and changed.
 * Multipart (image) updates are sent as POST with `_method=PUT`.
 */
abstract class CrudController extends Controller
{
    public function __construct(protected ImageOptimizer $images) {}

    /** @return class-string<Model> */
    abstract protected function model(): string;

    /** @return array<string, mixed> */
    abstract protected function rules(?Model $record): array;

    /** Request field carrying an uploaded image (e.g. "image"), or null if the resource has none. */
    protected function imageField(): ?string
    {
        return null;
    }

    /** Database column that stores the image path. */
    protected function imageColumn(): string
    {
        return 'image_path';
    }

    protected function imageRequiredOnCreate(): bool
    {
        return false;
    }

    protected function directory(): string
    {
        return 'uploads';
    }

    /** @return array<int, string> */
    protected function with(): array
    {
        return [];
    }

    public function index(): JsonResponse
    {
        $records = ($this->model())::query()->with($this->with())->orderBy('sort_order')->orderBy('id')->get();

        return $this->ok($records);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, null);

        if (! array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) ($this->model())::query()->max('sort_order') + 1;
        }

        $record = ($this->model())::create($data);

        return $this->ok($record->fresh($this->with()), 'Created.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $record = ($this->model())::query()->findOrFail($id);
        $record->update($this->validated($request, $record));

        return $this->ok($record->fresh($this->with()), 'Saved.');
    }

    public function destroy(int $id): JsonResponse
    {
        $record = ($this->model())::query()->findOrFail($id);

        if ($this->imageField()) {
            $this->images->delete($record->{$this->imageColumn()});
        }

        $record->delete();

        return $this->ok(null, 'Deleted.');
    }

    /** POST .../reorder  { ids: [3, 1, 2] }: the order of ids becomes the new sort order. */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['integer'],
        ])['ids'];

        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $position => $id) {
                ($this->model())::query()->whereKey($id)->update(['sort_order' => $position + 1]);
            }
        });

        // Bulk updates skip model events, so clear the public cache explicitly.
        app(SiteDataService::class)->forget();

        return $this->ok(null, 'Order saved.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Model $record): array
    {
        $rules = $this->rules($record);

        if ($record !== null) {
            $rules = array_map(
                fn ($rule) => is_array($rule) ? ['sometimes', ...$rule] : 'sometimes|'.$rule,
                $rules,
            );
        }

        $field = $this->imageField();
        if ($field !== null) {
            $rules[$field] = ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:8192'];
            $rules["remove_{$field}"] = ['sometimes', 'boolean'];
        }

        $data = $request->validate($rules);

        if ($field === null) {
            return $data;
        }

        unset($data[$field], $data["remove_{$field}"]);
        $column = $this->imageColumn();

        if ($request->hasFile($field)) {
            $data[$column] = $this->images->store($request->file($field), $this->directory(), $field);

            if ($record !== null) {
                $this->images->delete($record->{$column});
            }
        } elseif ($record === null && $this->imageRequiredOnCreate()) {
            throw ValidationException::withMessages([$field => 'An image is required.']);
        } elseif ($record !== null && $request->boolean("remove_{$field}")) {
            $this->images->delete($record->{$column});
            $data[$column] = null;
        }

        return $data;
    }
}
