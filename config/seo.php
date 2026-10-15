<?php

/*
|--------------------------------------------------------------------------
| SEO defaults
|--------------------------------------------------------------------------
| These are the fall-back titles/descriptions. The admin can override any of
| them from "Website Content" using the keys  seo.<page>.title  and
| seo.<page>.description  (see App\Services\SeoService).
| Service and location pages are generated from resources/content/*.json
| and the `services` table.
*/

return [

    'site_name' => 'Crystal Movers',

    'title_separator' => ' | ',

    'locale' => 'en_KE',

    'pages' => [

        'home' => [
            'path' => '/',
            'title' => 'Moving, Installation & Repair Services in Kenya',
            'description' => 'Crystal Movers offers home and office moving, CCTV, Wi-Fi and networking, TV and satellite, washing machine, lighting, shower and furniture assembly services. Request a quote today.',
        ],

        'services' => [
            'path' => '/services',
            'crumb' => 'Services',
            'title' => 'Our Services: Moving, Installations & Repairs',
            'description' => 'Moving and relocation, CCTV and security, Wi-Fi and networking, TV and satellite, washing machine, lighting, shower and furniture assembly services from Crystal Movers.',
        ],

        'about' => [
            'path' => '/about-us',
            'crumb' => 'About Us',
            'title' => 'About Us',
            'description' => 'Learn about Crystal Movers, our mission, our values and how we help homes and businesses with moving, installations and repairs.',
        ],

        'how_it_works' => [
            'path' => '/how-it-works',
            'crumb' => 'How It Works',
            'title' => 'How It Works: Simple Steps to Get Your Job Done',
            'description' => 'Send your request, get a response, agree the details and let our team do the work. See how Crystal Movers works from start to finish.',
        ],

        'gallery' => [
            'path' => '/gallery',
            'crumb' => 'Gallery',
            'title' => 'Gallery: Our Work',
            'description' => 'Photos of moves, installations and repairs by the Crystal Movers team.',
        ],

        'blog' => [
            'path' => '/blog',
            'crumb' => 'Blog',
            'title' => 'Blog: Moving and Installation Guides',
            'description' => 'Practical guides from Crystal Movers on preparing for a move, mounting a TV, Wi-Fi coverage, CCTV planning and furniture assembly.',
        ],

        'faq' => [
            'path' => '/faq',
            'crumb' => 'FAQ',
            'title' => 'FAQs: Moving, Installations & Booking',
            'description' => 'Answers to common questions about moving, installations, repairs, booking and quotations with Crystal Movers.',
        ],

        'contact' => [
            'path' => '/contact',
            'crumb' => 'Contact',
            'title' => 'Contact Crystal Movers: Get a Free Quote',
            'description' => 'Contact Crystal Movers by phone, WhatsApp, email or our online form to request a quotation for moving, installation or repair work.',
        ],

    ],

];
