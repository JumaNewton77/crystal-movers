<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ServiceController extends CrudController
{
    protected function model(): string
    {
        return Service::class;
    }

    protected function imageField(): ?string
    {
        return 'image';
    }

    protected function directory(): string
    {
        return 'services';
    }

    protected function with(): array
    {
        return ['category'];
    }

    protected function rules(?Model $record): array
    {
        return [
            'service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'slug' => [
                'required', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('services', 'slug')->ignore($record?->getKey()),
            ],
            'title' => ['required', 'string', 'max:160'],
            'short_description' => ['required', 'string', 'max:400'],
            'description' => ['nullable', 'string', 'max:5000'],
            'benefits' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', Rule::enum(ServiceStatus::class)],
            'is_published' => ['boolean'],
            'image_alt' => ['nullable', 'string', 'max:190'],
            'price_text' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:60'],
            'sort_order' => ['integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
