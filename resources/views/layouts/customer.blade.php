<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'ShopCalm') . ' - Shop More. Worry Less.')</title>

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
