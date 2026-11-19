<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Marketing Manager') — ShopCalm Staff</title>
    @php
        $mktFavicon = \App\Models\Setting::get('favicon');
        $hasMktFav = $mktFavicon && file_exists(storage_path('app/public/' . $mktFavicon));
        $mktFavUrl = $hasMktFav
            ? asset('storage/' . $mktFavicon) . '?v=' . (@filemtime(storage_path('app/public/' . $mktFavicon)) ?: time())
            : asset('favicon.png') . '?v=' . (@filemtime(public_path('favicon.png')) ?: '2');
    @endphp
    <link rel="icon" type="image/png" href="{{ $mktFavUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $mktFavUrl }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        /* ── ENTERPRISE DESIGN TOKENS & 8-POINT SPACING SCALE ── */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 64px;

            /* Brand Colors: Pink / Navy / White */
            --primary: #ec4899;
            --primary-hover: #db2777;
            --primary-subtle: #fdf2f8;
            --primary-border: #fbcfe8;
            --navy-900: #0f172a;
            --navy-800: #1e293b;
            --navy-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-400: #94a3b8;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --surface: #ffffff;
            --bg: #f8fafc;
            --border: #e2e8f0;

            /* 8-Point Spacing Scale */
            --space-1: 4px;   /* Tiny */
            --space-2: 8px;   /* Compact */
            --space-3: 12px;  /* Small */
            --space-4: 16px;  /* Standard */
            --space-5: 20px;  /* Medium */
            --space-6: 24px;  /* Section */
            --space-8: 32px;  /* Large */
            --space-10: 40px; /* Major */

            /* Radius Tokens */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-pill: 9999px;

            /* Component Shadows */
            --shadow-card: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            --shadow-card-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 0.875rem;
            line-height: 1.5;
            background: var(--bg);
            color: var(--navy-900);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0; bottom: 0; left: 0;
            width: var(--sidebar-width);
            background: var(--navy-900);
            background-image: linear-gradient(180deg, #0f172a 0%, #1a1026 100%);
            z-index: 1040;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #fff;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
        }

        .sidebar-brand {
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 var(--space-5);
            background: rgba(255, 255, 255, 0.02);
            font-weight: 700;
            font-size: 1.05rem;
            color: #fff;
            text-decoration: none;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            letter-spacing: -0.3px;
            gap: var(--space-3);
        }

        .sidebar-sticky {
            flex-grow: 1;
            overflow-y: auto;
            padding: var(--space-3) 0 var(--space-8);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }
        .sidebar-sticky::-webkit-scrollbar { width: 4px; }
        .sidebar-sticky::-webkit-scrollbar-track { background: transparent; }
        .sidebar-sticky::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 4px; }

        .sidebar .nav-header {
            padding: var(--space-4) var(--space-5) var(--space-1);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--slate-400);
            letter-spacing: 0.08em;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.65rem var(--space-3);
            display: flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.83rem;
            border-radius: var(--radius-sm);
            margin: 2px var(--space-3);
            transition: all 0.18s ease;
            text-decoration: none;
            gap: var(--space-3);
            letter-spacing: 0.01em;
            position: relative;
        }
        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }
        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #ec4899, #db2777);
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.35);
            font-weight: 600;
        }
        .sidebar .nav-link i {
            font-size: 1.1rem;
            flex-shrink: 0;
            width: 22px;
            text-align: center;
            color: var(--slate-400);
            transition: color 0.18s ease;
        }
        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            color: #ffffff !important;
        }

        .sidebar .nav-link .nav-label {
            flex: 1 1 auto;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── NAVBAR ── */
        .navbar-admin {
            height: var(--navbar-height);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 1030;
            padding-left: var(--sidebar-width);
            transition: padding-left 0.3s cubic-bezier(.4,0,.2,1);
            border-bottom: 1px solid var(--border);
        }

        /* ── MAIN CONTENT ── */
        main.main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--navbar-height) + var(--space-6));
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(.4,0,.2,1);
        }

        .main-container {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 var(--space-6) var(--space-8);
        }

        /* ── COMMON CARD SYSTEM ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .navbar-admin {
                padding-left: 0;
            }
            main.main-content {
                margin-left: 0;
            }
            .main-container {
                padding: 0 var(--space-4) var(--space-6);
            }
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $mktUser = Auth::guard('marketing')->user() ?? Auth::guard('admin')->user() ?? Auth::user();
@endphp

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <a href="{{ route('marketing.dashboard') }}" class="sidebar-brand">
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(236, 72, 153, 0.2); border: 1px solid rgba(236, 72, 153, 0.35); color: #f472b6;">
            <i class="bi bi-megaphone-fill fs-5"></i>
        </div>
        <div class="d-flex flex-column" style="gap: 3px;">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-white" style="font-size: 1.05rem; letter-spacing: -0.3px;">ShopCalm</span>
                <span class="badge rounded-pill" style="font-size: 0.62rem; background: var(--primary); color: #fff; padding: 2px 7px;">MKT</span>
            </div>
            <span class="text-white-50" style="font-size: 0.7rem; letter-spacing: 0.02em;">Marketing Manager</span>
        </div>
    </a>

    <div class="sidebar-sticky" style="padding-top: 1rem;">
        <ul class="nav flex-column">
            <li class="nav-header" style="padding-top: 0.5rem; padding-bottom: 0.5rem;">Overview</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.dashboard') ? 'active' : '' }}" href="{{ route('marketing.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-label">Analytics Dashboard</span>
                </a>
            </li>

            <li class="nav-header" style="margin-top: 0.75rem; padding-bottom: 0.5rem;">Campaigns &amp; Outreach</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.notifications*') ? 'active' : '' }}" href="{{ route('marketing.notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span class="nav-label">Push Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.automations*') ? 'active' : '' }}" href="{{ route('marketing.automations.index') }}">
                    <i class="bi bi-cpu-fill"></i>
                    <span class="nav-label">Automations Studio</span>
                </a>
            </li>

            <!-- LOGOUT -->
            <li class="nav-item mt-4 pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.06) !important;">
                <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('mkt-logout-form').submit();">
                    <i class="bi bi-box-arrow-right text-danger"></i>
                    <span class="nav-label">Logout</span>
                </a>
                <form id="mkt-logout-form" action="{{ route('marketing.logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
</aside>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light navbar-admin fixed-top">
    <div class="container-fluid px-4">
        <button class="btn border-0 d-lg-none" type="button" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="bi bi-list fs-4"></i>
        </button>

        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb mb-0 ms-2">
                @hasSection('breadcrumb')
                    @yield('breadcrumb')
                @else
                    <li class="breadcrumb-item"><a href="{{ route('marketing.dashboard') }}" class="text-decoration-none text-muted small">Marketing</a></li>
                    <li class="breadcrumb-item active text-dark small fw-semibold" aria-current="page">@yield('title', 'Dashboard')</li>
                @endif
            </ol>
        </nav>

        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item me-3">
                <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-2" style="font-size: 0.78rem;">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Live Store</span>
                </a>
            </li>
            <li class="nav-item">
                <div class="d-flex align-items-center px-3.5 py-2 rounded-3 border bg-white shadow-xs" style="gap: 12px;">
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #ec4899, #f43f5e); display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 700; color: #fff; flex-shrink: 0; box-shadow: 0 2px 6px rgba(236, 72, 153, 0.25);">
                        {{ strtoupper(substr($mktUser->name ?? 'M', 0, 1)) }}
                    </div>
                    <div class="d-flex flex-column" style="gap: 3px;">
                        <div style="font-size: 0.84rem; font-weight: 700; color: var(--navy-900); line-height: 1.2;">{{ $mktUser->name ?? 'Marketing Staff' }}</div>
                        <div style="font-size: 0.68rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; line-height: 1;">Marketing Manager</div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Content Area -->
<main class="main-content">
    <div class="main-container">
        <!-- Consistent Page Header with Title & Subtitle -->
        <div class="d-flex justify-content-between flex-wrap align-items-start gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1 text-dark">@yield('header', 'Dashboard')</h1>
                @hasSection('subheader')
                    <p class="text-muted small mb-0" style="line-height: 1.5;">@yield('subheader')</p>
                @endif
            </div>
            <div class="btn-toolbar">
                @yield('actions')
            </div>
        </div>

        @include('components.toast')

        @yield('content')

        <footer class="mt-5 mb-4 text-center text-muted">
            <small>&copy; {{ date('Y') }} ShopCalm Marketing Manager. All rights reserved.</small>
        </footer>
    </div>
</main>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

@stack('scripts')
</body>
</html>
