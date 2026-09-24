<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Rider Portal') — ShopCalm Fleet</title>

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
            --app-text-secondary: #64748b;
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
            margin: 0;
            display: flex;
            justify-content: center;
        }

        /* Mobile App Frame Container (Centered on desktop, full-width on mobile) */
        .mobile-app-shell {
            width: 100%;
            max-width: 480px;
            min-height: 100vh;
            background-color: var(--app-bg);
            display: flex;
            flex-direction: column;
            position: relative;
            box-shadow: 0 0 60px rgba(0, 0, 0, 0.6);
            padding-bottom: 80px;
        }

        /* Top Rider Header Bar */
        .app-topbar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 0.9rem 1.1rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .online-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px #10b981;
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
            height: 68px;
            background: #ffffff;
            border-top: 1px solid var(--app-border);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1030;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
            padding: 0 0.5rem;
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
            padding: 0.35rem 0.85rem;
            border-radius: 1rem;
            transition: all 0.2s ease;
            position: relative;
        }
        .app-nav-link i {
            font-size: 1.3rem;
            margin-bottom: 2px;
            transition: transform 0.2s ease;
        }
        .app-nav-link.active {
            color: #4f46e5;
            background: #eef2ff;
        }
        .app-nav-link.active i {
            transform: translateY(-1px);
        }

        .app-nav-badge {
            position: absolute;
            top: 2px;
            right: 12px;
            padding: 0.15rem 0.4rem;
            font-size: 0.62rem;
            font-weight: 800;
            border-radius: 999px;
        }

        /* Generic Utility & Card styles */
        .order-task-card {
            background: #ffffff;
            border-radius: 1.1rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 0.85rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .order-task-card:active {
            transform: scale(0.995);
        }

        .btn-rider-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            height: 44px;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.84rem;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.15s ease;
        }
        .btn-rider-action:active {
            transform: scale(0.97);
        }
        .btn-action-nav {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #bfdbfe;
        }
        .btn-action-phone {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $currentRider = Auth::guard('delivery_partner')->user() ?? Auth::guard('admin')->user();
@endphp

<div class="mobile-app-shell">
    <!-- Top App Bar -->
    <header class="app-topbar" style="padding: 1.05rem 1.25rem;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-xs flex-shrink-0" 
                 style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 1.15rem;">
                <i class="bi bi-bicycle"></i>
            </div>
            <div>
                <div class="fw-bolder text-white text-truncate mb-1" style="font-size: 0.96rem; line-height: 1.25; max-width: 220px;">
                    {{ $currentRider->name ?? 'Delivery Partner' }}
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="online-dot"></span>
                    <span style="font-size: 0.68rem; color: #a5b4fc; font-weight: 800; letter-spacing: 0.06em;">FLEET ONLINE</span>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('delivery.logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5 d-flex align-items-center gap-1.5 text-white border-white-50 shadow-xs" style="font-size: 0.74rem;">
                    <i class="bi bi-box-arrow-right"></i> <span>Exit</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Mobile Content Area -->
    <main class="flex-grow-1 p-3">
        @if(session('toast'))
            <div class="alert alert-{{ session('toast.type') === 'error' ? 'danger' : (session('toast.type') === 'warning' ? 'warning' : 'success') }} border-0 rounded-4 p-3 mb-3 shadow-xs d-flex align-items-center gap-2.5">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div class="small fw-semibold">{{ session('toast.message') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom App Navigation Bar -->
    <nav class="app-bottom-nav">
        <a href="{{ route('delivery.dashboard') }}" class="app-nav-link {{ request()->routeIs('delivery.dashboard') || request()->routeIs('delivery.orders.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i>
            <span>Run-Sheet</span>
        </a>

        <a href="{{ route('delivery.completed') }}" class="app-nav-link {{ request()->routeIs('delivery.completed') ? 'active' : '' }}">
            <i class="bi bi-check2-all"></i>
            <span>Completed</span>
        </a>

        <a href="{{ route('delivery.settlements') }}" class="app-nav-link {{ request()->routeIs('delivery.settlements') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>
            <span>Cash Handover</span>
        </a>
    </nav>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

@stack('scripts')
</body>
</html>
