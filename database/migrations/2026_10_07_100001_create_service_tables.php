<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->string('slug', 120)->unique();
            $table->string('title', 160);
            $table->text('short_description');
            $table->text('description')->nullable();
            // active = fully offered; coming_soon = display only (International Moving, Storage).
            $table->string('status', 20)->default('active');
            $table->boolean('is_published')->default(true);
            $table->string('image_path')->nullable();
            $table->string('icon', 60)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // Per-service SEO (falls back to sensible defaults when empty).
            $table->string('seo_title', 190)->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('og_image_path')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};
