<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cookie-based SPA authentication for the admin area (Laravel Sanctum).
        $middleware->statefulApi();

        // Behind a tunnel or reverse proxy the real scheme (https) arrives in X-Forwarded-* headers. Trusting them is OFF
        // by default; set TRUSTED_PROXIES=* (tunnel/demo) or a comma-separated list of proxy IPs in .env to turn it on.
        $proxies = trim((string) env('TRUSTED_PROXIES', ''));
        if ($proxies !== '') {
            $middleware->trustProxies(at: $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        $middleware->alias(['admin' => EnsureUserIsAdmin::class]);
        $middleware->append(SecurityHeaders::class);

        // API calls get a 401 JSON response, never a redirect.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/admin/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        // One consistent JSON envelope for the whole API (see ApiExceptionRenderer).
        $exceptions->render(
            fn (Throwable $e, Request $request) => $request->is('api/*') ? ApiExceptionRenderer::render($e) : null
        );
    })->create();
