<?php

namespace App\Http\Controllers\Admin;

use App\Models\GalleryItem;
use Illuminate\Database\Eloquent\Model;

class GalleryController extends CrudController
{
    protected function model(): string
    {
        return GalleryItem::class;
    }

    protected function imageField(): ?string
    {
        return 'image';
    }

    protected function imageRequiredOnCreate(): bool
    {
        return true;
    }

    protected function directory(): string
    {
        return 'gallery';
    }

    protected function with(): array
    {
        return ['category'];
    }

    protected function rules(?Model $record): array
    {
        return [
            'gallery_category_id' => ['nullable', 'integer', 'exists:gallery_categories,id'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'is_published' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
