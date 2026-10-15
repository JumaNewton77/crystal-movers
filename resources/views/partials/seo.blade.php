<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="robots" content="{{ $seo['robots'] }}">
@if (! empty($seo['canonical']))
<link rel="canonical" href="{{ $seo['canonical'] }}">
@endif

<meta property="og:type" content="{{ $seo['og_type'] }}">
<meta property="og:site_name" content="{{ $seo['og_site_name'] }}">
<meta property="og:locale" content="{{ $seo['og_locale'] }}">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
@if (! empty($seo['canonical']))
<meta property="og:url" content="{{ $seo['canonical'] }}">
@endif
@if (! empty($seo['og_image']))
<meta property="og:image" content="{{ $seo['og_image'] }}">
@endif
<meta name="twitter:card" content="{{ ! empty($seo['og_image']) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
@if (! empty($seo['og_image']))
<meta name="twitter:image" content="{{ $seo['og_image'] }}">
@endif

@foreach ($seo['json_ld'] as $schema)
{{-- JSON_HEX_* flags stop "</script>" or "&" in admin-edited text from breaking out of the tag. --}}
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endforeach
