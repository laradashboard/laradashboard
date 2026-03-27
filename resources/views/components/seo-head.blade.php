<title>{{ $title ?? config('app.name', 'LaraDashboard') }}</title>

<meta name="description" content="{{ $description ?? '' }}">
<meta name="keywords" content="{{ $keywords ?? '' }}">
<meta name="author" content="{{ $author ?? config('app.name') }}">

{{-- Canonical URL --}}
<link rel="canonical" href="{{ $canonical ?? url()->current() }}">

{{-- Open Graph / Social Media --}}
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
<meta property="og:description" content="{{ $description ?? '' }}">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
@if(isset($ogImage) && $ogImage)
    <meta property="og:image" content="{{ $ogImage }}">
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
<meta name="twitter:description" content="{{ $description ?? '' }}">
@if(isset($ogImage) && $ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}">
@endif

{{-- JSON-LD Structured Data --}}
@php
    $jsonLd = [
        json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('app.name'),
            'url' => url('/'),
        ], JSON_UNESCAPED_SLASHES),
        json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ], JSON_UNESCAPED_SLASHES),
    ];
@endphp
@foreach($jsonLd as $schema)
    <script type="application/ld+json">{!! $schema !!}</script>
@endforeach
