<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', \App\Models\Setting::get('store_name', 'ShopCalm') . ' - ' . \App\Models\Setting::get('tagline', 'Shop More. Worry Less.'))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('tagline', 'Discover genuine electronics, smartphones, fashion, and lifestyle essentials at best prices with fast doorstep delivery.'))">
    <link rel="canonical" href="{{ url()->current() }}">
    @if(\App\Models\Setting::get('google_site_verification'))
    <meta name="google-site-verification" content="{{ \App\Models\Setting::get('google_site_verification') }}">
    @endif
    @php
        $storeFavicon = \App\Models\Setting::get('favicon');
        $hasStorageFavicon = $storeFavicon && file_exists(storage_path('app/public/' . $storeFavicon));
        $storeFaviconUrl = $hasStorageFavicon
            ? asset('storage/' . $storeFavicon) . '?v=' . (@filemtime(storage_path('app/public/' . $storeFavicon)) ?: time())
            : asset('favicon.png') . '?v=' . (@filemtime(public_path('favicon.png')) ?: '2');
    @endphp
    <link rel="icon" type="image/png" href="{{ $storeFaviconUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $storeFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $storeFaviconUrl }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Open Graph / Social & WhatsApp Link Sharing Preview -->
    <meta property="og:site_name" content="{{ \App\Models\Setting::get('store_name', 'ShopCalm') }}">
    <meta property="og:title" content="@yield('title', \App\Models\Setting::get('store_name', 'ShopCalm'))">
    <meta property="og:description" content="@yield('meta_description', \App\Models\Setting::get('tagline', 'Shop More. Worry Less.'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    @yield('og_tags')

    <!-- Google Search Schema.org Organization Structured Data -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "{{ \App\Models\Setting::get('store_name', 'ShopCalm') }}",
        "legalName": "{{ \App\Models\Setting::get('invoice_company_name', \App\Models\Setting::get('store_name', 'ShopCalm')) }}",
        "url": "{{ config('app.url', url('/')) }}",
        "email": "{{ \App\Models\Setting::get('contact_email', 'support@shopcalm.in') }}",
        "description": "{{ addslashes(\App\Models\Setting::get('tagline', 'Shop More. Worry Less.')) }}"
    }
    </script>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}?v={{ @filemtime(public_path('css/customer.css')) ?: time() }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}?v={{ @filemtime(public_path('css/account.css')) ?: time() }}">

    @stack('styles')
</head>
<body class="bg-light d-flex flex-column min-vh-100">

    @include('customer.components.header')

    <main class="{{ request()->routeIs('home') ? 'flex-grow-1 p-0' : 'flex-grow-1 py-3 py-md-4' }}">
        @yield('content')
    </main>

    @include('customer.components.footer')

    @include('customer.components.mobile-bottom-nav')

    @include('components.toast')
    @include('components.center-alert')
    @include('components.offline-indicator')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/customer.js') }}"></script>
    <script src="{{ asset('js/ui-interactions.js') }}"></script>
    <script src="{{ asset('js/cart-handler.js') }}"></script>

    @stack('scripts')
</body>
</html>
