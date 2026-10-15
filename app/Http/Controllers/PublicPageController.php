<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\PrerenderRepository;
use App\Services\SeoService;
use App\Services\SiteDataService;
use App\Support\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Serves every public page as: server-injected SEO <head> + the site data (window.__SITE__)
 * + prerendered body (only when it is still fresh) + the React bundle that hydrates it.
 */
class PublicPageController extends Controller
{
    public function __construct(
        private SeoService $seo,
        private PrerenderRepository $prerender,
        private SiteDataService $site,
    ) {}

    public function home(Request $request): Response
    {
        return $this->render($request, $this->seo->page('home'));
    }

    public function services(Request $request): Response
    {
        return $this->render($request, $this->seo->page('services'));
    }

    public function category(Request $request, string $slug): Response
    {
        $category = ServiceCategory::query()->where('is_published', true)->where('slug', $slug)->firstOrFail();

        return $this->render($request, $this->seo->category($category));
    }

    public function service(Request $request, string $slug): Response
    {
        $service = Service::published()->where('slug', $slug)->firstOrFail();

        return $this->render($request, $this->seo->service($service));
    }

    public function about(Request $request): Response
    {
        return $this->render($request, $this->seo->page('about'));
    }

    public function howItWorks(Request $request): Response
    {
        return $this->render($request, $this->seo->page('how_it_works'));
    }

    public function gallery(Request $request): Response
    {
        return $this->render($request, $this->seo->page('gallery'));
    }

    public function blog(Request $request): Response
    {
        return $this->render($request, $this->seo->page('blog'));
    }

    public function blogPost(Request $request, string $slug): Response
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        return $this->render($request, $this->seo->blogPost($post));
    }

    public function faq(Request $request): Response
    {
        return $this->render($request, $this->seo->page('faq'));
    }

    public function contact(Request $request): Response
    {
        return $this->render($request, $this->seo->page('contact'));
    }

    public function location(Request $request, string $city): Response
    {
        $location = Content::location($city);
        abort_if($location === null, 404);

        return $this->render($request, $this->seo->location($location));
    }

    public function notFound(Request $request): Response
    {
        abort_if($request->is('api/*'), 404); // JSON 404 is rendered by ApiExceptionRenderer

        return $this->render($request, $this->seo->notFound(), 404, prerender: false);
    }

    private function render(Request $request, array $seo, int $status = 200, bool $prerender = true): Response
    {
        return response()->view('public', [
            'seo' => $seo,
            'site' => $this->site->payload(),
            // Prerendered HTML is used only if it was built from the CURRENT database content.
            'prerendered' => $prerender ? $this->prerender->get($request->path(), $this->site->hash()) : '',
        ], $status);
    }
}
