<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive, backward-compatible changes for the wider Crystal Movers catalogue:
 *  - service categories get a description, image, icon and a publish flag
 *  - services get image alt text and optional price text
 *  - quote_requests become general service enquiries (service, location, time, extra details, consent)
 *    while every existing moving quote keeps its data (move type / from / to simply become optional).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->string('image_path')->nullable()->after('description');
            $table->string('image_alt', 190)->nullable()->after('image_path');
            $table->string('icon', 60)->nullable()->after('image_alt');
            $table->boolean('is_published')->default(true)->after('icon');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('image_alt', 190)->nullable()->after('image_path');
            $table->string('price_text', 120)->nullable()->after('image_alt');
        });

        Schema::table('quote_requests', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('email')->constrained('services')->nullOnDelete();
            $table->string('location', 190)->nullable()->after('to_location');
            $table->string('preferred_time', 20)->nullable()->after('preferred_date');
            $table->json('details')->nullable()->after('message');
            $table->boolean('contact_consent')->default(false)->after('details');

            $table->string('move_type', 30)->nullable()->change();
            $table->string('from_location', 190)->nullable()->change();
            $table->string('to_location', 190)->nullable()->change();

            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn(['location', 'preferred_time', 'details', 'contact_consent']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['image_alt', 'price_text']);
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'image_path', 'image_alt', 'icon', 'is_published']);
        });
        // move_type / from_location / to_location stay nullable: restoring NOT NULL could fail on newer enquiries.
    }
};
