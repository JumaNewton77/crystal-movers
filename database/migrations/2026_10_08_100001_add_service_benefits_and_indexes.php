<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive migration (the original Phase 1 migrations are left untouched).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // One benefit per line; shown on the service page.
            $table->text('benefits')->nullable()->after('description');
        });

        Schema::table('gallery', function (Blueprint $table) {
            $table->index('gallery_category_id');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            // Dashboard "upcoming moves" and the quote list's default sort.
            $table->index(['status', 'preferred_date']);
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'preferred_date']);
        });

        Schema::table('gallery', function (Blueprint $table) {
            $table->dropIndex(['gallery_category_id']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('benefits');
        });
    }
};
