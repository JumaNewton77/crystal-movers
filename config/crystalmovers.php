<?php

/*
|--------------------------------------------------------------------------
| Crystal Movers business configuration
|--------------------------------------------------------------------------
| Values that are code-level decisions live here. Values the business owner
| edits (phone, WhatsApp, address, rating, wording...) live in the `settings`
| table and are managed from the admin area.
*/

return [

    // Enquiry references look like CM-Q-2026-0001 (year uses this timezone).
    'timezone' => 'Africa/Nairobi',
    'quote_reference_prefix' => 'CM-Q',

    // First admin account created by `php artisan db:seed`.
    // Read here (not via env() in the seeder) so it also works with config:cache.
    'admin' => [
        'name' => env('ADMIN_NAME', 'Crystal Movers Admin'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Optional first-install values for the Settings seeder (the database stays the source of truth after install).
    'seed' => [
        'phone' => env('COMPANY_PHONE'),
        'whatsapp' => env('COMPANY_WHATSAPP'),
        'email' => env('COMPANY_EMAIL'),
    ],

    'notifications' => [
        'admin_email' => env('ADMIN_NOTIFICATION_EMAIL'),
    ],

    // Optional customer uploads on the quote form (enforced by StoreQuoteRequest).
    'uploads' => [
        'max_files' => 5,
        'max_image_kb' => 5120,   // 5 MB per photo
        'max_video_kb' => 51200,  // 50 MB per short video
        'image_mimes' => ['image/jpeg', 'image/png', 'image/webp'],
        'video_mimes' => ['video/mp4', 'video/quicktime', 'video/webm'],
    ],

];
