<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Old Smart Movers wording that was seeded into the database becomes Crystal Movers.
 * Text only: image paths, slugs and enquiry references are never touched. Safe to run more than once.
 */
return new class extends Migration
{
    private const PAIRS = [
        'Smart Movers' => 'Crystal Movers',
        'SmartLife Movers' => 'Crystal Movers',
        'Smart movers' => 'Crystal Movers',
    ];

    public function up(): void
    {
        $targets = [
            'settings' => ['value'],
            'website_content' => ['value'],
            'faqs' => ['question', 'answer'],
            'services' => ['title', 'short_description', 'description', 'benefits', 'seo_title', 'seo_description'],
            'service_categories' => ['name', 'description'],
            'gallery' => ['title', 'description', 'alt_text'],
        ];

        foreach ($targets as $table => $columns) {
            foreach ($columns as $column) {
                foreach (self::PAIRS as $from => $to) {
                    DB::table($table)
                        ->where($column, 'like', '%'.$from.'%')
                        ->when($table === 'settings', fn ($q) => $q->whereNotIn('key', ['logo_path', 'favicon_path', 'seo_og_image']))
                        ->update([$column => DB::raw("replace({$column}, '{$from}', '{$to}')")]);
                }
            }
        }

        // Old seeded wording that listed packing among the services.
        DB::table('website_content')->where('value', 'like', '%office relocation, packing and%')
            ->update(['value' => DB::raw("replace(value, 'office relocation, packing and', 'office relocation and')")]);
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
