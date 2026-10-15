<?php

use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SitemapController;
use App\Support\Content;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website (prerendered at build time + server-injected SEO)
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/services', [PublicPageController::class, 'services'])->name('services');
Route::get('/services/category/{slug}', [PublicPageController::class, 'category'])
    ->where('slug', '[a-z0-9\-]+')->name('services.category');
Route::get('/services/{slug}', [PublicPageController::class, 'service'])
    ->where('slug', '[a-z0-9\-]+')->name('services.show');
Route::get('/about-us', [PublicPageController::class, 'about'])->name('about');
Route::get('/how-it-works', [PublicPageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/gallery', [PublicPageController::class, 'gallery'])->name('gallery');
Route::get('/blog', [PublicPageController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [PublicPageController::class, 'blogPost'])
    ->where('slug', '[a-z0-9\-]+')->name('blog.show');
Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');

// /movers-in-nairobi, /movers-in-eldoret ... (cities come from resources/content/locations.json)
Route::get('/movers-in-{city}', [PublicPageController::class, 'location'])
    ->where('city', implode('|', array_column(Content::locations(), 'slug')))
    ->name('location');

/*
|--------------------------------------------------------------------------
| SEO files
|--------------------------------------------------------------------------
| NOTE: do not add a static public/robots.txt: the file would shadow this route.
*/
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

/*
|--------------------------------------------------------------------------
| Admin (client-side React app; every /api/admin route enforces authentication)
|--------------------------------------------------------------------------
*/
Route::view('/admin/{any?}', 'admin')->where('any', '.*')->name('admin');

// Unknown URLs get a proper 404 page (noindex). Unknown /api/* URLs get JSON 404.
Route::fallback([PublicPageController::class, 'notFound']);
