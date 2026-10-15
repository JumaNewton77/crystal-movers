<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // --- Company ---
            'general' => [
                'company_name' => 'Crystal Movers',
                'tagline' => 'Move Smarter. Live Better.',
                'logo_path' => null, // upload your logo in Admin > Settings
                'favicon_path' => null, // optional browser-tab icon
            ],

            // --- Contact: PLACEHOLDERS until the business confirms real details ---
            'contact' => [
                'phone' => config('crystalmovers.seed.phone') ?: '+254 779 606 100',
                'whatsapp_number' => config('crystalmovers.seed.whatsapp') ?: '+254 779 606 100', // CONFIRM with the owner; change in Admin > Settings
                'whatsapp_default_message' => 'Hello Crystal Movers, I would like to request a quote.',
                'email' => config('crystalmovers.seed.email') ?: null, // owner must confirm the real business email; hidden until set
                'notification_email' => null, // where quote / contact notifications are sent (private)
                'address' => 'Nairobi, Kenya',
                'working_hours' => 'Monday–Saturday, 7:00 AM–6:00 PM',
                'google_maps_embed_url' => null,
            ],

            // --- Social links (empty until real accounts exist) ---
            'social' => [
                'social_facebook' => null,
                'social_instagram' => null,
                'social_x' => null,
                'social_linkedin' => null,
                'social_youtube' => null,
                'social_tiktok' => null,
            ],

            // --- Google rating: NEVER fabricated. Empty = the rating block is hidden. ---
            'reviews' => [
                'google_rating' => null,
                'google_review_count' => null,
                'google_business_url' => null,
            ],

            // --- Insurance & Safety: wording is admin-editable. Do not claim insurance unless it is true. ---
            'trust' => [
                'insurance_heading' => 'Safety and Care',
                'insurance_text' => null, // optional highlighted line; only add a claim that is verified
            ],

            // --- Default SEO (used where a page has no override) ---
            'seo' => [
                'seo_default_title' => 'Crystal Movers: Moving, Installation & Repair Services in Kenya',
                'seo_default_description' => 'Home and office moving, CCTV, Wi-Fi and networking, TV and satellite, washing machine, lighting, shower and furniture assembly services. Request a quote from Crystal Movers.',
                'seo_og_image' => null,
            ],
        ];

        foreach ($settings as $group => $items) {
            foreach ($items as $key => $value) {
                Setting::firstOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
            }
        }
    }
}
