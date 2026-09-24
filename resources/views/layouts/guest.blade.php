<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' | ' : '' }}{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    <body class="font-sans text-gray-900 antialiased" style="background-color: #f8fafc;">
        <div class="min-h-screen flex flex-col justify-center items-center p-2 sm:p-6" style="min-height: 100dvh;">
            <div class="w-full sm:max-w-md p-3.5 sm:p-6 bg-white shadow-sm border border-slate-200/80 rounded-3xl" style="max-width: 440px;">
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
