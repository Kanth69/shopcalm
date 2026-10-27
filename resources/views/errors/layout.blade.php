<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') - ShopCalm</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Google Fonts & Bootstrap 5 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --dark: #0f172a;
            --muted: #64748b;
            --light-bg: #f8fafc;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .error-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            max-width: 580px;
            width: 100%;
            margin: auto;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        @media (max-width: 576px) {
            .error-card {
                padding: 2rem 1.25rem;
                border-radius: 16px;
                border: none;
                box-shadow: none;
                background: transparent;
            }
        }
        .error-code-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            font-weight: 800;
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
        }
        .btn-primary-custom {
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            padding: 0.65rem 1.6rem;
            font-weight: 600;
            border-radius: 9999px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary-custom:hover, .btn-primary-custom:focus {
            background-color: var(--primary-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
        }
        .btn-outline-custom {
            background-color: #ffffff;
            color: var(--dark);
            border: 1.5px solid #cbd5e1;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
            border-radius: 9999px;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-outline-custom:hover {
            background-color: #f1f5f9;
            color: var(--dark);
            border-color: #94a3b8;
        }

        /* Live Offline Banner */
        #network-status-toast {
            position: fixed;
            top: 16px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            z-index: 9999;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }
        #network-status-toast.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Offline Notification Banner -->
    <div id="network-status-toast" class="shadow-lg">
        <div id="network-status-badge" class="badge rounded-pill px-3.5 py-2 fs-6 d-flex align-items-center gap-2" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(8px); color: #fff;">
            <i id="network-status-icon" class="bi bi-wifi-off text-warning"></i>
            <span id="network-status-text">No internet connection</span>
        </div>
    </div>

    <!-- Header Brand -->
    <header class="py-3 px-4 border-bottom bg-white d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <span class="fs-4 fw-extrabold text-dark tracking-tight" style="font-weight: 800; letter-spacing: -0.03em;">
                Shop<span style="color: var(--primary);">Calm</span>
            </span>
        </a>
        <a href="{{ url('/') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 font-monospace" style="font-size: 0.8rem;">
            <i class="bi bi-house-door me-1"></i> Home
        </a>
    </header>

    <!-- Content -->
    <main class="flex-grow-1 d-flex align-items-center justify-content-center p-3 p-md-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="py-3 px-4 text-center text-muted border-top bg-white small">
        <p class="mb-1">&copy; {{ date('Y') }} <strong>ShopCalm</strong>. All rights reserved.</p>
        <p class="mb-0 text-secondary" style="font-size: 0.8rem;">
            Need urgent help? Reach out at <a href="mailto:support@shopcalm.in" class="text-decoration-none text-primary">support@shopcalm.in</a>
        </p>
    </footer>

    <!-- Offline / Online Event Listeners -->
    <script>
        (function() {
            const toast = document.getElementById('network-status-toast');
            const icon = document.getElementById('network-status-icon');
            const text = document.getElementById('network-status-text');

            function updateOnlineStatus() {
                if (!navigator.onLine) {
                    icon.className = 'bi bi-wifi-off text-warning';
                    text.textContent = 'You are offline. Please check your internet connection.';
                    toast.classList.add('show');
                } else {
                    icon.className = 'bi bi-check-circle-fill text-success';
                    text.textContent = 'Back online!';
                    setTimeout(() => {
                        toast.classList.remove('show');
                    }, 2200);
                }
            }

            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);

            if (!navigator.onLine) {
                updateOnlineStatus();
            }
        })();
    </script>
    @yield('scripts')
</body>
</html>
