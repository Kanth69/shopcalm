<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shopcalm') }} - Product Manager</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css">

    <style>
        /* ============================================
           SHOPCALM PRODUCT MANAGER — PREMIUM UI LAYER
        ============================================ */
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300..900;1,14..32,300..900&display=swap');

        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(99, 102, 241, 0.15);
            --sidebar-active: #6366f1;
            --navbar-height: 62px;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --surface: #ffffff;
            --bg: #f1f5f9;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
            --radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -1px rgba(0,0,0,0.04);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.04);
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
            background-image: linear-gradient(180deg, #0f172a 0%, #1a1040 100%);
            z-index: 1040;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: #fff;
            border-right: 1px solid rgba(255,255,255,0.04);
        }

        .sidebar-brand {
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            background: rgba(255,255,255,0.03);
            font-weight: 700;
            font-size: 1.2rem;
            color: #fff;
            text-decoration: none;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            letter-spacing: -0.3px;
        }

        .sidebar-sticky {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0.75rem 0 2rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        .sidebar-sticky::-webkit-scrollbar { width: 3px; }
        .sidebar-sticky::-webkit-scrollbar-track { background: transparent; }
        .sidebar-sticky::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.6rem 1.25rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.82rem;
            border-radius: 8px;
            margin: 1px 0.75rem;
            transition: all 0.18s ease;
            text-decoration: none;
            gap: 0.6rem;
            letter-spacing: 0.01em;
        }
        .sidebar .nav-link:hover {
            color: #e2e8f0;
            background: var(--sidebar-hover);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }
        .sidebar .nav-link i {
            font-size: 1.05rem;
            flex-shrink: 0;
            width: 20px;
            text-align: center;
        }
        .sidebar .nav-header {
            padding: 1.25rem 1.5rem 0.35rem;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.08em;
        }
        .sidebar .submenu {
            padding-left: 0.75rem;
        }
        .sidebar .submenu .nav-link {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            color: #64748b;
        }
        .sidebar .submenu .nav-link.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
        }
        .sidebar .submenu .nav-link::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
            flex-shrink: 0;
            opacity: 0.5;
        }
        .sidebar .submenu .nav-link.active::before { opacity: 1; }

        .sidebar .nav-link.has-submenu::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform 0.25s;
            opacity: 0.5;
        }
        .sidebar .nav-link.has-submenu[aria-expanded="true"]::after { transform: rotate(180deg); }

        /* ── NAVBAR ── */
        .navbar-admin {
            height: var(--navbar-height);
            background: rgba(255,255,255,0.92);
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

        /* ── PAGE HEADER ── */
        .d-flex.justify-content-between h1.h3 {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.4px;
            color: var(--text);
        }

        /* ── CARDS ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            font-weight: 600;
            letter-spacing: -0.2px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99,102,241,0.35);
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
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('product-manager.dashboard') }}" class="sidebar-brand">
            <i class="bi bi-box-seam me-2 text-primary" style="color: #818cf8 !important;"></i>
            <span>Shopcalm <span class="badge rounded-pill bg-primary ms-1" style="font-size: 0.62rem; background: #6366f1 !important;">PM</span></span>
        </a>

        <div class="sidebar-sticky">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.dashboard') ? 'active' : '' }}" href="{{ route('product-manager.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <li class="nav-header">Catalog</li>
                
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.products.index') || request()->routeIs('product-manager.products.edit') ? 'active' : '' }}" href="{{ route('product-manager.products.index') }}">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.products.pending') ? 'active' : '' }}" href="{{ route('product-manager.products.pending') }}">
                        <i class="bi bi-hourglass-split"></i> Pending Approvals
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.products.rejected') ? 'active' : '' }}" href="{{ route('product-manager.products.rejected') }}">
                        <i class="bi bi-x-octagon"></i> Rejected Items
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.products.create') ? 'active' : '' }}" href="{{ route('product-manager.products.create') }}">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.categories.*') ? 'active' : '' }}" href="{{ route('product-manager.categories.index') }}">
                        <i class="bi bi-grid"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.brands.*') ? 'active' : '' }}" href="{{ route('product-manager.brands.index') }}">
                        <i class="bi bi-tag"></i> Brands
                    </a>
                </li>

                <li class="nav-header">Inventory</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.stock.dashboard') || request()->routeIs('product-manager.stock.form') ? 'active' : '' }}" href="{{ route('product-manager.stock.dashboard') }}">
                        <i class="bi bi-boxes"></i> Stock Management
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.stock.history') ? 'active' : '' }}" href="{{ route('product-manager.stock.history') }}">
                        <i class="bi bi-clock-history"></i> Stock History
                    </a>
                </li>

                <li class="nav-header">Quality & Intelligence</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.reviews.*') ? 'active' : '' }}" href="{{ route('product-manager.reviews.index') }}">
                        <i class="bi bi-star"></i> Reviews
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.reports.*') ? 'active' : '' }}" href="{{ route('product-manager.reports.index') }}">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                </li>

                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('pm-logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                    <form id="pm-logout-form" action="{{ route('product-manager.logout') }}" method="POST" class="d-none">
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
                    <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.78rem;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Live Store
                    </a>
                </li>
                <li class="nav-item">
                    <div class="d-flex align-items-center gap-2 ms-2 px-3 py-1 rounded-3" style="background:#f1f5f9; border:1px solid #e2e8f0;">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'PM', 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:0.8rem;font-weight:600;color:#0f172a;line-height:1.1;">{{ Auth::guard('admin')->user()->name ?? 'Product Manager' }}</div>
                            <div style="font-size:0.65rem;color:#6366f1;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Product Manager</div>
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
                <small>&copy; {{ date('Y') }} Shopcalm Product Manager. All rights reserved.</small>
            </footer>
        </div>
    </main>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('js/ui-interactions.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/admin-filters.js') }}?v={{ time() }}"></script>

    @stack('scripts')
</body>
</html>
