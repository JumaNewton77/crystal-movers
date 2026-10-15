<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Crystal Movers does not list Packing or Storage as services. Removes any such starter rows and FAQs.
 * Customer enquiries are never deleted (quote_requests.service_id is nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')->where(function ($q) {
            $q->where('slug', 'like', '%storage%')
                ->orWhere('slug', 'packing-and-unpacking')
                ->orWhere('slug', 'like', 'packing%');
        })->delete();

        DB::table('faqs')->where(function ($q) {
            $q->where('question', 'like', '%storage%')->orWhere('question', 'like', 'Do you provide packing%');
        })->delete();

        foreach (DB::table('service_categories')->where(function ($q) {
            $q->where('slug', 'like', '%storage%')->orWhere('slug', 'like', 'packing%');
        })->get() as $category) {
            DB::table('services')->where('service_category_id', $category->id)->update(['service_category_id' => null]);
            DB::table('service_categories')->where('id', $category->id)->delete();
        }

        DB::table('service_categories')->where('slug', 'moving')
            ->where('description', 'like', '%with packing and unpacking%')
            ->update(['description' => DB::raw("replace(description, ', with packing and unpacking', '')")]);
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
