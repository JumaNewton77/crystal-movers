<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Content;
use Illuminate\Database\Seeder;

/**
 * First-install starter catalogue only (database/seeders/data/services.json).
 * Once seeded, PostgreSQL is the source of truth: admin edits and uploaded images are never overwritten,
 * because every row is created with firstOrCreate. No images are seeded: upload your own in Admin.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $data = Content::seed('services');

        $categories = [];
        foreach ($data['categories'] as $item) {
            $categories[$item['slug']] = ServiceCategory::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'icon' => $item['icon'] ?? null,
                    'is_published' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );
        }

        foreach ($data['services'] as $item) {
            Service::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'service_category_id' => $categories[$item['category']]->id,
                    'title' => $item['title'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'benefits' => implode("\n", $item['benefits'] ?? []),
                    'status' => $item['status'],
                    'is_published' => true,
                    'sort_order' => $item['sort_order'],
                    'seo_title' => $item['seo_title'],
                    'seo_description' => $item['seo_description'],
                ],
            );
        }
    }
}
