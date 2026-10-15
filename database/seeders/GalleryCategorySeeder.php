<?php

namespace Database\Seeders;

use App\Models\GalleryCategory;
use Illuminate\Database\Seeder;

class GalleryCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['slug' => 'moving', 'name' => 'Moving & Relocation'],
            ['slug' => 'installations', 'name' => 'Installations'],
            ['slug' => 'repairs', 'name' => 'Repairs & Maintenance'],
            ['slug' => 'team', 'name' => 'Our Team'],
            // One per service category, so photos can be assigned to a service area.
            ['slug' => 'cctv-security', 'name' => 'CCTV & Security'],
            ['slug' => 'internet-networking', 'name' => 'Internet, Wi-Fi & Networking'],
            ['slug' => 'tv-satellite', 'name' => 'TV, Satellite & Entertainment'],
            ['slug' => 'washing-machine', 'name' => 'Washing Machines'],
            ['slug' => 'lighting', 'name' => 'Lighting'],
            ['slug' => 'shower', 'name' => 'Showers'],
            ['slug' => 'furniture-assembly', 'name' => 'Furniture Assembly'],
        ] as $index => $category) {
            GalleryCategory::firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'sort_order' => $index + 1],
            );
        }
    }
}
