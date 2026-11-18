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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --navbar-height: 62px;
            --primary: #ec4899;
            --primary-hover: #db2777;
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 0.875rem;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            position: fixed;
            top: 0; bottom: 0; left: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            background-image: linear-gradient(180deg, #0f172a 0%, #1e112a 100%);
            z-index: 1040;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #fff;
            border-right: 1px solid rgba(255,255,255,0.06);
        }

        .sidebar-brand {
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            background: rgba(255,255,255,0.03);
            font-weight: 700;
            font-size: 1.1rem;
            color: #fff;
            text-decoration: none;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            letter-spacing: -0.3px;
            gap: 0.75rem;
        }

        .sidebar-sticky {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0.75rem 0 2rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .sidebar-sticky::-webkit-scrollbar { width: 4px; }
        .sidebar-sticky::-webkit-scrollbar-track { background: transparent; }
        .sidebar-sticky::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .sidebar .nav-header {
            padding: 1.25rem 1.25rem 0.35rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.08em;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.65rem 1rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.83rem;
            border-radius: 8px;
            margin: 2px 0.75rem;
            transition: all 0.18s ease;
            text-decoration: none;
            gap: 0.75rem;
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
            box-shadow: 0 4px 12px rgba(236, 72, 153, 0.4);
            font-weight: 600;
        }
        .sidebar .nav-link i {
            font-size: 1.1rem;
            flex-shrink: 0;
            width: 22px;
            text-align: center;
            color: #94a3b8;
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
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 1px 0 var(--border);
            z-index: 1030;
            padding-left: var(--sidebar-width);
            transition: padding-left 0.3s cubic-bezier(.4,0,.2,1);
            border-bottom: 1px solid var(--border);
        }

        /* ── MAIN CONTENT ── */
        main.main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--navbar-height) + 1.75rem);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(.4,0,.2,1);
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
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
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: rgba(236, 72, 153, 0.25); border: 1px solid rgba(236, 72, 153, 0.4); color: #f472b6;">
            <i class="bi bi-megaphone-fill fs-5"></i>
        </div>
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center gap-1.5">
                <span class="fw-bold">ShopCalm</span>
                <span class="badge rounded-pill" style="font-size: 0.62rem; background: #ec4899; color: #fff;">MKT</span>
            </div>
            <span class="text-white-50" style="font-size: 0.68rem; letter-spacing: 0.02em;">Marketing Manager</span>
        </div>
    </a>

    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('marketing.dashboard') ? 'active' : '' }}" href="{{ route('marketing.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            <li class="nav-header">Campaigns</li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('marketing.dashboard') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span class="nav-label">Push Notifications</span>
                </a>
            </li>

            <!-- LOGOUT -->
            <li class="nav-item mt-4 pt-2 border-top" style="border-color: rgba(255,255,255,0.06) !important;">
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
    <div class="container-fluid">
        <button class="btn border-0 d-lg-none" type="button" onclick="document.getElementById('sidebar').classList.toggle('show')">
            <i class="bi bi-list fs-4"></i>
        </button>

        <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb mb-0 ms-3">
                @yield('breadcrumb')
            </ol>
        </nav>

        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item me-2">
                <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center" style="font-size: 0.78rem; gap: 0.4rem !important;">
                    <i class="bi bi-box-arrow-up-right"></i> Live Store
                </a>
            </li>
            <li class="nav-item">
                <div class="d-flex align-items-center ms-2 px-3 py-1.5 rounded-3" style="background:#f1f5f9; border:1px solid #e2e8f0; gap: 0.65rem !important;">
                    <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#ec4899,#f43f5e);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:#fff;flex-shrink:0;">
                        {{ strtoupper(substr($mktUser->name ?? 'M', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:0.8rem;font-weight:600;color:#0f172a;line-height:1.1;">{{ $mktUser->name ?? 'Marketing Staff' }}</div>
                        <div style="font-size:0.65rem;color:#ec4899;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Marketing Manager</div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<main class="main-content">
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
            <h1 class="h3 fw-bold mb-0">@yield('header', 'Dashboard')</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                @yield('actions')
            </div>
        </div>

        @include('components.toast')

        @yield('content')

        <footer class="mt-5 mb-4 text-center text-muted">
            <small>&copy; {{ date('Y') }} Shopcalm Marketing Manager. All rights reserved.</small>
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
