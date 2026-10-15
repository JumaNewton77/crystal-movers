<?php

namespace App\Http\Controllers;

use App\Services\SiteDataService;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    /** Public website data: approved testimonials, published services/FAQs/gallery, public settings. */
    public function show(SiteDataService $site): JsonResponse
    {
        return $this->ok($site->payload());
    }
}
