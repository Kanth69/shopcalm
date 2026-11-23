<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rider Portal') — WiseKart Logistics</title>
    @php
        $delFavicon = \App\Models\Setting::get('favicon');
        $hasDelFav = $delFavicon && file_exists(storage_path('app/public/' . $delFavicon));
        $delFavUrl = $hasDelFav
            ? asset('storage/' . $delFavicon) . '?v=' . (@filemtime(storage_path('app/public/' . $delFavicon)) ?: time())
            : asset('favicon.png') . '?v=' . (@filemtime(public_path('favicon.png')) ?: '2');
    @endphp
    <link rel="icon" type="image/png" href="{{ $delFavUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $delFavUrl }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css">

    <style>
        :root {
            --app-bg: #f8fafc;
            --app-surface: #ffffff;
            --app-text-primary: #0f172a;
            --app-text-secondary: #475569;
            --app-primary: #4f46e5;
            --app-primary-dark: #3730a3;
            --app-success: #10b981;
            --app-warning: #f59e0b;
            --app-danger: #ef4444;
            --app-border: #e2e8f0;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #0b1120;
            color: var(--app-text-primary);
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        /* Mobile App Frame (Strict 480px width, centered on desktop, 100% on phone) */
        .mobile-app-shell {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            min-height: 100dvh;
            background-color: var(--app-bg);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 0 60px rgba(0, 0, 0, 0.6);
            padding-bottom: 78px;
        }

        /* Top Rider Header Bar */
        .app-topbar {
            background: #0f172a;
            color: #ffffff;
            padding: 0.85rem 1.1rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
        }

        /* Prevent JavaScript from injecting overflow text into sound button */
        .staff-sound-toggle-btn span {
            display: none !important;
        }

        .online-dot {
            width: 7px;
            height: 7px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #10b981;
            animation: pulse-online 2s infinite;
        }
        @keyframes pulse-online {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.5; }
        }

        /* Bottom Fixed App Navigation */
        .app-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            height: 64px;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1030;
            box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.06);
            padding: 0 0.5rem;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .app-nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-decoration: none;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.35rem 1.15rem;
            border-radius: 0.85rem;
            transition: all 0.2s ease;
            position: relative;
        }
        .app-nav-link i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
        .app-nav-link.active {
            color: #4f46e5;
            background: #eef2ff;
        }

        .app-nav-badge {
            position: absolute;
            top: 2px;
            right: 14px;
            background: #4f46e5;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.1rem 0.35rem;
            border-radius: 999px;
            line-height: 1;
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $currentRider = Auth::guard('delivery_partner')->user() ?? Auth::guard('admin')->user();
    $riderFirstName = explode(' ', $currentRider->name ?? 'Partner')[0];
@endphp

<div class="mobile-app-shell">
    <!-- Top Bar -->
    <header class="app-topbar">
        <div class="d-flex align-items-center gap-2.5 min-w-0">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" 
                 style="width: 36px; height: 36px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 0.95rem;">
                {{ strtoupper(substr($riderFirstName, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <div class="fw-bold text-white text-truncate" style="font-size: 0.94rem; line-height: 1.2;">
                    {{ $riderFirstName }}
                </div>
                <div class="d-flex align-items-center gap-1.5 mt-0.5">
                    <span class="online-dot"></span>
                    <span style="font-size: 0.65rem; color: #34d399; font-weight: 800; letter-spacing: 0.04em;">FLEET ONLINE</span>
                </div>
            </div>
        </div>

        <!-- Right Side Icons -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <!-- Sound Toggle Button -->
            <button type="button" class="btn btn-sm btn-outline-light rounded-circle text-white border-white-50 p-0 d-flex align-items-center justify-content-center staff-sound-toggle-btn" 
                    style="width: 34px; height: 34px;" onclick="toggleStaffSound()" title="Sound Notifications">
                <i class="bi bi-volume-up-fill text-success" style="font-size: 0.9rem;"></i>
            </button>

            <!-- Sign Out -->
            <form action="{{ route('delivery.logout') }}" method="POST" class="d-inline mb-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light rounded-circle text-white border-white-50 p-0 d-flex align-items-center justify-content-center" 
                        style="width: 34px; height: 34px;" title="Exit / Logout">
                    <i class="bi bi-power" style="font-size: 0.9rem;"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Mobile Content Area with generous padding -->
    <main class="flex-grow-1 p-3">
        @yield('content')
    </main>

    <!-- Bottom Fixed App Navigation -->
    <nav class="app-bottom-nav">
        <a href="{{ route('delivery.dashboard') }}" class="app-nav-link {{ request()->routeIs('delivery.dashboard') || request()->routeIs('delivery.orders.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam-fill"></i>
            <span>Run-Sheet</span>
        </a>

        <a href="{{ route('delivery.completed') }}" class="app-nav-link {{ request()->routeIs('delivery.completed') ? 'active' : '' }}">
            <i class="bi bi-check2-circle"></i>
            <span>Completed</span>
        </a>

        <a href="{{ route('delivery.settlements') }}" class="app-nav-link {{ request()->routeIs('delivery.settlements') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>
            <span>Cash Desk</span>
        </a>
    </nav>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

@include('components.staff-live-poller', ['endpoint' => route('delivery.live-alerts'), 'portal' => 'delivery'])

@stack('scripts')
</body>
</html>
