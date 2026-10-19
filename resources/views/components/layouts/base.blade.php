{{--
    Base HTML document. Every page sets a unique title and description; private pages pass
    :noindex="true" so search engines never index dashboards, exams or account data.
--}}
@props(['title' => null, 'description' => null, 'noindex' => false, 'canonical' => null])
@php
    $siteUrl = config('academy.site_url');
    $fullTitle = $title ? "{$title} | AMANIQ Academy" : 'AMANIQ Academy: practical cybersecurity education';
    $description ??= 'Practice-oriented cybersecurity programmes with hands-on simulated labs, fair server-graded examinations and verifiable certificates. A division of AMANIQ Projects Limited.';
    $canonical ??= $siteUrl.(request()->path() === '/' ? '' : '/'.request()->path());
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#0a1020">
    <meta name="color-scheme" content="dark">
    @if ($noindex || ! app()->isProduction())
        <meta name="robots" content="noindex, nofollow">
    @else
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AMANIQ Academy">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $siteUrl }}/brand/amaniq-academy-lockup-white-4k.png">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body {{ $attributes->merge(['class' => 'min-h-dvh']) }}>
    <a href="#main" class="sr-only rounded-md bg-accent px-3 py-2 font-semibold text-accent-ink focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-50">Skip to content</a>
    {{ $slot }}
</body>
</html>
