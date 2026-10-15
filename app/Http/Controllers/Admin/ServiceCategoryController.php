<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/** Service categories (Moving & Relocation, CCTV & Security ...): add, edit, publish, reorder, image, remove. */
class ServiceCategoryController extends CrudController
{
    protected function model(): string
    {
        return ServiceCategory::class;
    }

    protected function imageField(): ?string
    {
        return 'image';
    }

    protected function directory(): string
    {
        return 'categories';
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('service_categories', 'slug')->ignore($record?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_alt' => ['nullable', 'string', 'max:190'],
            'icon' => ['nullable', 'string', 'max:60'],
            'is_published' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
