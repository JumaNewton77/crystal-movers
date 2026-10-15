<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TestimonialSubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes (prefixed with /api). Every response uses the envelope
| { success, message, data } or { success: false, message, errors }.
|--------------------------------------------------------------------------
*/

Route::get('/health', fn () => response()->json(['status' => 'ok']));

// --- Public ---------------------------------------------------------------
Route::get('/site', [SiteController::class, 'show']);

// Rate limiters are defined in AppServiceProvider.
Route::post('/quote-requests', [QuoteRequestController::class, 'store'])->middleware('throttle:quote-submissions');
Route::post('/testimonials', [TestimonialSubmissionController::class, 'store'])->middleware('throttle:contact-submissions');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact-submissions');

// --- Admin authentication (Sanctum session cookie) ------------------------
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:admin-login');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me']);
});

// --- Admin (authenticated + authorised) -----------------------------------
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class);

    Route::get('/quotes', [Admin\QuoteController::class, 'index']);
    Route::get('/quotes/{quote}', [Admin\QuoteController::class, 'show']);
    Route::patch('/quotes/{quote}', [Admin\QuoteController::class, 'update']);
    Route::get('/quotes/{quote}/attachments/{attachment}', [Admin\QuoteController::class, 'attachment']);

    Route::get('/messages', [Admin\MessageController::class, 'index']);
    Route::patch('/messages/{message}', [Admin\MessageController::class, 'update']);
    Route::delete('/messages/{message}', [Admin\MessageController::class, 'destroy']);

    Route::get('/lookups/service-categories', [Admin\LookupController::class, 'serviceCategories']);
    Route::get('/lookups/services', [Admin\LookupController::class, 'services']);
    Route::get('/lookups/gallery-categories', [Admin\LookupController::class, 'galleryCategories']);
    Route::get('/lookups/quote-options', [Admin\LookupController::class, 'quoteOptions']);

    // Services, FAQs, testimonials and gallery share one CRUD shape.
    foreach ([
        'services' => Admin\ServiceController::class,
        'service-categories' => Admin\ServiceCategoryController::class,
        'faqs' => Admin\FaqController::class,
        'testimonials' => Admin\TestimonialController::class,
        'gallery' => Admin\GalleryController::class,
        'blog-posts' => Admin\BlogPostController::class,
    ] as $uri => $controller) {
        Route::post("/{$uri}/reorder", [$controller, 'reorder']);
        Route::apiResource($uri, $controller)->parameters([$uri => 'id']);
    }

    Route::get('/settings', [Admin\SettingController::class, 'index']);
    Route::put('/settings', [Admin\SettingController::class, 'update']);
    Route::post('/settings/{key}/image', [Admin\SettingController::class, 'image'])->where('key', '[a-z_]+');
    Route::delete('/settings/{key}/image', [Admin\SettingController::class, 'removeImage'])->where('key', '[a-z_]+');

    Route::get('/content', [Admin\ContentController::class, 'index']);
    Route::put('/content', [Admin\ContentController::class, 'update']);
    Route::post('/content/{key}/image', [Admin\ContentController::class, 'image'])->where('key', '[a-z0-9_.\-]+');
    Route::delete('/content/{key}/image', [Admin\ContentController::class, 'removeImage'])->where('key', '[a-z0-9_.\-]+');
});
