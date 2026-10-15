<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111827">
    @php($favicon = $site['settings']['favicon'] ?? null)
    @if ($favicon)
    <link rel="icon" href="{{ $favicon }}">
    @else
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    @endif

    {{-- Server-injected, page-specific SEO (title, description, canonical, Open Graph, JSON-LD) --}}
    @include('partials.seo', ['seo' => $seo])

    {{-- Public site data from PostgreSQL. HEX flags make </script> and quotes safe inside the tag. --}}
    <script>window.__SITE__ = {!! json_encode($site, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};</script>

    @viteReactRefresh
    @vite('resources/js/public/main.tsx')
</head>
<body>
{{-- Prerendered markup (built by `php artisan site:prerender`). Kept on ONE line: whitespace here would break React hydration. --}}
<div id="root">{!! $prerendered !!}</div>
</body>
</html>
