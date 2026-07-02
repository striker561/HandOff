@php
    $appName = config('app.name', 'HandOff');
    $pageTitle = filled($title ?? null) ? $title . ' - ' . $appName : $appName;
    $description = $metaDescription ?? __('A self-hosted client portal for agencies and freelancers — projects, deliverables, credentials, and client collaboration in one place.');
    $canonicalUrl = url()->current();
    $ogImage = asset('logo-512.png');
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $description }}" />
<meta name="application-name" content="{{ $appName }}" />
<meta name="theme-color" content="#3d4f7c" />

<link rel="icon" href="{{ asset('favicon-32x32.png') }}" type="image/png" sizes="32x32" />
<link rel="icon" href="{{ asset('logo.png') }}" type="image/png" sizes="any" />
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}" />

<link rel="canonical" href="{{ $canonicalUrl }}" />

<meta property="og:type" content="website" />
<meta property="og:site_name" content="{{ $appName }}" />
<meta property="og:title" content="{{ $pageTitle }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:url" content="{{ $canonicalUrl }}" />
<meta property="og:image" content="{{ $ogImage }}" />

<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="{{ $pageTitle }}" />
<meta name="twitter:description" content="{{ $description }}" />
<meta name="twitter:image" content="{{ $ogImage }}" />

<link rel="preconnect" href="https://fonts.bunny.net" />
<link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance