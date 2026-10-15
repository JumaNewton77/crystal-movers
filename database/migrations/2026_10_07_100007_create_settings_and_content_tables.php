<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Site-wide settings: company name, phone, WhatsApp, email, address, hours,
        // social links, Google rating/link, insurance wording, default SEO...
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();
            $table->text('value')->nullable();
            $table->string('group', 40)->default('general');
            $table->timestamps();
        });

        // Editable page text (hero copy, About sections...) and per-page SEO overrides
        // using keys such as  home.hero.title  and  seo.home.title.
        Schema::create('website_content', function (Blueprint $table) {
            $table->id();
            $table->string('key', 160)->unique();
            $table->text('value')->nullable();
            $table->string('group', 40)->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_content');
        Schema::dropIfExists('settings');
    }
};
