<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Crystal Movers does not offer a Storage service. Removes the starter "storage" service and
 * the old "Packing & Storage" category if nothing else uses it. Customer enquiries are never touched
 * (quote_requests.service_id is nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->where('slug', 'storage')->delete();

        DB::table('faqs')->where('question', 'like', 'Do you provide storage%')->delete();

        $category = DB::table('service_categories')->where('slug', 'packing-storage')->first();
        if ($category && DB::table('services')->where('service_category_id', $category->id)->doesntExist()) {
            DB::table('service_categories')->where('id', $category->id)->delete();
        }
    }

    public function down(): void
    {
        // Intentionally empty: the Storage service is not restored.
    }
};
