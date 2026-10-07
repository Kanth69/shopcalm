<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', \App\Models\Setting::get('store_name', 'ShopCalm') . ' - ' . \App\Models\Setting::get('tagline', 'Shop More. Worry Less.'))</title>
    <meta name="description" content="@yield('meta_description', \App\Models\Setting::get('tagline', 'Discover genuine electronics, smartphones, fashion, and lifestyle essentials at best prices with fast doorstep delivery.'))">
    <link rel="canonical" href="{{ url()->current() }}">
    @php
        $storeFavicon = \App\Models\Setting::get('favicon');
        $storeFaviconUrl = $storeFavicon ? asset('storage/' . $storeFavicon) . '?v=' . (@filemtime(storage_path('app/public/' . $storeFavicon)) ?: time()) : asset('favicon.ico');
    @endphp
    <link rel="icon" href="{{ $storeFaviconUrl }}">
    <link rel="shortcut icon" href="{{ $storeFaviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $storeFaviconUrl }}">

    <!-- Open Graph / Social & WhatsApp Link Sharing Preview -->
    <meta property="og:site_name" content="{{ \App\Models\Setting::get('store_name', 'ShopCalm') }}">
    <meta property="og:title" content="@yield('title', \App\Models\Setting::get('store_name', 'ShopCalm'))">
    <meta property="og:description" content="@yield('meta_description', \App\Models\Setting::get('tagline', 'Shop More. Worry Less.'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    @yield('og_tags')

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">

    @stack('styles')
</head>
<body class="bg-light">

    @include('customer.components.header')

    <main class="py-4">
        @yield('content')
    </main>

    @include('customer.components.footer')

    @include('customer.components.mobile-bottom-nav')

    @include('components.toast')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="{{ asset('js/customer.js') }}"></script>
    <script src="{{ asset('js/ui-interactions.js') }}"></script>
    <script src="{{ asset('js/cart-handler.js') }}"></script>

    @stack('scripts')
</body>
</html>
