<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
            $customFavicon = \App\Models\Setting::get('favicon');
        @endphp
        <title>{{ isset($title) ? $title . ' | ' : '' }}{{ ucfirst($storeName) }}</title>

        @if($customFavicon)
            @php
                $favPath = storage_path('app/public/' . $customFavicon);
                $favVer = file_exists($favPath) ? filemtime($favPath) : time();
                $favUrl = asset('storage/' . $customFavicon) . '?v=' . $favVer;
            @endphp
            <link rel="icon" href="{{ $favUrl }}">
            <link rel="shortcut icon" href="{{ $favUrl }}">
            <link rel="apple-touch-icon" href="{{ $favUrl }}">
        @else
            <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @endif

        <!-- Google Fonts: Plus Jakarta Sans & Figtree -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap 5 CSS & Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body.guest-auth-body {
                font-family: 'Plus Jakarta Sans', 'Figtree', system-ui, -apple-system, sans-serif;
                background-color: #f8fafc;
                background-image: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.08) 0%, rgba(248, 250, 252, 0) 65%);
                color: #0f172a;
                margin: 0;
                padding: 0;
            }
            .guest-auth-viewport {
                min-height: 100vh;
                min-height: 100dvh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 14px;
            }
            .guest-auth-card {
                width: 100%;
                max-width: 440px;
                background: #ffffff;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 20px;
                box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04);
                padding: 22px 18px;
            }
            @media (min-width: 576px) {
                .guest-auth-viewport {
                    padding: 24px;
                }
                .guest-auth-card {
                    padding: 28px 28px;
                    border-radius: 24px;
                }
            }
        </style>

        <script>
            function togglePasswordVisibility(inputId, btn) {
                const input = document.getElementById(inputId);
                if (!input) return;
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.className = 'bi bi-eye';
                    }
                    btn.setAttribute('title', 'Hide password');
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.className = 'bi bi-eye-slash';
                    }
                    btn.setAttribute('title', 'Show password');
                }
            }
        </script>
    </head>
    <body class="font-sans text-gray-900 antialiased guest-auth-body">
        <div class="guest-auth-viewport">
            <div class="guest-auth-card">
                {{ $slot }}
            </div>
        </div>

        <script>
            document.addEventListener('keydown', function(event) {
                if (event.altKey && event.shiftKey && (event.key === 'W' || event.key === 'w')) {
                    // Ignore if the user is typing in an input field
                    const activeElement = document.activeElement;
                    const isTyping = activeElement.tagName === 'INPUT' ||
                                     activeElement.tagName === 'TEXTAREA' ||
                                     activeElement.isContentEditable;

                    if (!isTyping) {
                        event.preventDefault();
                        window.location.href = "{{ route('admin.login') }}";
                    }
                }
            });
        </script>
        @stack('scripts')
    </body>
</html>
