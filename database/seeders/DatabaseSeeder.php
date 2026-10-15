<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Every seeder here is idempotent (firstOrCreate): re-running `db:seed`
     * never overwrites anything the business owner has edited in the admin area.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SettingsSeeder::class,
            WebsiteContentSeeder::class,
            ServiceSeeder::class,
            FaqSeeder::class,
            GalleryCategorySeeder::class,
            BlogPostSeeder::class,
        ]);

        // Deliberately NOT seeded: testimonials and the Google rating.
        // Those must be real, entered by the administrator.
    }
}
