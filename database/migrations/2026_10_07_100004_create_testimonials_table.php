<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name', 120);
            $table->string('location', 120)->nullable();
            $table->unsignedSmallInteger('rating')->default(5);
            $table->text('review');
            $table->string('photo_path')->nullable();
            // Values of App\Enums\TestimonialStatus. Only "approved" is public.
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        // PostgreSQL: ratings are always 1-5.
        DB::statement('ALTER TABLE testimonials ADD CONSTRAINT testimonials_rating_range CHECK (rating BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
