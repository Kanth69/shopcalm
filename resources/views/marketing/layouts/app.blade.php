<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Marketing Portal') — ShopCalm Staff</title>
    @php
        $mktFavicon = \App\Models\Setting::get('favicon');
        $hasMktFav = $mktFavicon && file_exists(storage_path('app/public/' . $mktFavicon));
        $mktFavUrl = $hasMktFav
            ? asset('storage/' . $mktFavicon) . '?v=' . (@filemtime(storage_path('app/public/' . $mktFavicon)) ?: time())
            : asset('favicon.png') . '?v=' . (@filemtime(public_path('favicon.png')) ?: '2');
    @endphp
    <link rel="icon" type="image/png" href="{{ $mktFavUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $mktFavUrl }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --mkt-primary: #ec4899;
            --mkt-primary-hover: #db2777;
            --mkt-sidebar-bg: #140e24;
            --mkt-sidebar-hover: rgba(255, 255, 255, 0.08);
            --mkt-sidebar-active: #ec4899;
            --mkt-sidebar-width: 260px;
            --mkt-navbar-height: 65px;
            --mkt-border: #e2e8f0;
            --mkt-bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--mkt-bg);
            color: #0f172a;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            padding: 0;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.06);
            background: var(--mkt-sidebar-bg);
            width: var(--mkt-sidebar-width);
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.15rem 1.4rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
            text-decoration: none;
        }

        .sidebar-brand .brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.05rem;
            box-shadow: 0 4px 14px rgba(236, 72, 153, 0.4);
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.3px;
        }

        .sidebar-nav-container {
            padding: 1rem 0.85rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .nav-header {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: #948aa9;
            padding: 0.75rem 0.85rem 0.35rem 0.85rem;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #b5a9c9;
            padding: 0.65rem 0.85rem;
            border-radius: 0.65rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-bottom: 0.2rem;
            position: relative;
        }

        .sidebar .nav-link i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background: var(--mkt-sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: var(--mkt-primary);
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35);
        }

        .sidebar .nav-badge {
            margin-left: auto;
            font-size: 0.68rem;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-weight: 700;
        }

        /* ── TOP NAVBAR ── */
        .top-navbar {
            margin-left: var(--mkt-sidebar-width);
            background: #ffffff;
            border-bottom: 1px solid var(--mkt-border);
            padding: 0.85rem 2rem;
            height: var(--mkt-navbar-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: var(--mkt-sidebar-width);
            padding: 2rem 2.25rem;
            min-height: calc(100vh - var(--mkt-navbar-height));
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .top-navbar, .main-content {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $mktUser = Auth::guard('marketing')->user() ?? Auth::guard('admin')->user() ?? Auth::user();
    $isSuper = $mktUser && method_exists($mktUser, 'isSuperAdmin') && $mktUser->isSuperAdmin();
@endphp

<!-- Sidebar -->
<aside class="sidebar" id="marketingSidebar">
    <a href="{{ route('marketing.dashboard') }}" class="sidebar-brand">
        <div class="d-flex align-items-center gap-2.5">
            <div class="brand-logo">
                <i class="bi bi-megaphone-fill"></i>
            </div>
            <div>
                <div class="brand-text">ShopCalm</div>
                <div class="text-white-50" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em;">Marketing Portal</div>
            </div>
        </div>
        <span class="badge text-white border border-white border-opacity-25" style="font-size: 0.65rem; background: rgba(236, 72, 153, 0.3);">MKT</span>
    </a>

    <div class="sidebar-nav-container">
        <ul class="nav flex-column">
            <li class="nav-header">Overview</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.dashboard') ? 'active' : '' }}" href="{{ route('marketing.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-header">App Engagement</li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('marketing.dashboard') }}#notifications-tab">
                    <i class="bi bi-bell-fill"></i> Push Notifications
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('marketing.dashboard') }}#demographics-anchor">
                    <i class="bi bi-people-fill"></i> Audience Target
                </a>
            </li>

            <li class="nav-header">Digital Marketing</li>
            <li class="nav-item">
                <a class="nav-link text-white-50" href="{{ route('marketing.dashboard') }}#urls-tab" onclick="document.getElementById('urls-tab')?.click();">
                    <i class="bi bi-link-45deg"></i> Smart URLs <span class="badge bg-secondary-subtle text-secondary rounded-pill ms-auto" style="font-size: 9px;">Paused</span>
                </a>
            </li>

            @if($isSuper)
            <li class="nav-header">Super Admin Controls</li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.notifications.index') }}" target="_blank">
                    <i class="bi bi-shield-check"></i> Approval Console <i class="bi bi-box-arrow-up-right small ms-auto text-white-50"></i>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dashboard') }}" target="_blank">
                    <i class="bi bi-grid-1x2"></i> Main Admin Panel <i class="bi bi-box-arrow-up-right small ms-auto text-white-50"></i>
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- User Profile & Logout -->
    <div class="p-3 border-top border-white border-opacity-10">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.8rem; background: #ec4899; color: #ffffff;">
                    {{ strtoupper(substr($mktUser->name ?? 'M', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate" style="font-size: 0.8rem;">{{ $mktUser->name ?? 'Marketing Staff' }}</div>
                    <div class="text-white-50 small" style="font-size: 0.68rem;">{{ $isSuper ? 'Super Admin' : 'Marketing Manager' }}</div>
                </div>
            </div>
            <form action="{{ route('marketing.logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link text-white-50 p-1 text-decoration-none" title="Sign Out">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Top Navbar -->
<header class="top-navbar">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light d-lg-none" type="button" onclick="document.getElementById('marketingSidebar').classList.toggle('show')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb mb-0 ms-2">
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>

    <div class="d-flex align-items-center gap-2.5">
        @yield('actions')

        <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.8rem;">
            <i class="bi bi-box-arrow-up-right me-1"></i> Live Store
        </a>

        <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 ms-1" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #ec4899, #f43f5e); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0;">
                {{ strtoupper(substr($mktUser->name ?? 'M', 0, 1)) }}
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #0f172a; line-height: 1.1;">{{ $mktUser->name ?? 'Marketing Staff' }}</div>
                <div style="font-size: 0.65rem; color: #ec4899; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Staff &bull; Marketing & Growth</div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Area -->
<main class="main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.3px;">@yield('header', 'Marketing Workspace')</h4>
            <small class="text-muted">Target audience filters, compose seasonal push notifications, and monitor delivery.</small>
        </div>
    </div>

    @include('components.toast')

    @yield('content')

    <footer class="mt-5 mb-4 text-center text-muted">
        <small>&copy; {{ date('Y') }} Shopcalm Marketing & Growth Operations. All rights reserved.</small>
    </footer>
</main>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

@stack('scripts')
</body>
</html>
