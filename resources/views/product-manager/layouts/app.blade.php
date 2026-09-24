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
            background-image: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
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
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 4px 12px rgba(99,102,241,0.45);
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
        
        .sidebar .nav-link .nav-badge {
            margin-left: auto;
            flex-shrink: 0;
            font-size: 0.68rem;
            padding: 0.2em 0.55em;
            line-height: 1.2;
            font-weight: 700;
        }
        
        .sidebar .nav-link.has-submenu .nav-badge {
            margin-right: 0.35rem;
            margin-left: auto;
        }

        .sidebar .nav-link.has-submenu::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            font-size: 0.72rem;
            transition: transform 0.25s ease;
            opacity: 0.6;
            margin-left: auto;
            flex-shrink: 0;
        }
        .sidebar .nav-link.has-submenu:not(.collapsed)::after,
        .sidebar .nav-link.has-submenu[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .sidebar .nav-header {
            padding: 1.25rem 1.25rem 0.35rem;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.08em;
        }

        .sidebar .submenu {
            padding-left: 0.75rem;
            margin-top: 2px;
            margin-bottom: 4px;
        }
        .sidebar .submenu .nav-link {
            font-size: 0.8rem;
            padding: 0.5rem 0.85rem;
            color: #94a3b8;
            margin: 1px 0.75rem 1px 0;
            gap: 0.6rem;
        }
        .sidebar .submenu .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }
        .sidebar .submenu .nav-link.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #ffffff !important;
            box-shadow: 0 3px 10px rgba(99,102,241,0.35);
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

        .btn-primary, .btn-pm-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #ffffff !important;
            border: none;
            font-weight: 600;
            letter-spacing: -0.2px;
            transition: all 0.2s ease;
        }

        .btn-primary:hover, .btn-pm-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: #ffffff !important;
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
        <!-- Brand Logo -->
        <a href="{{ route('product-manager.dashboard') }}" class="sidebar-brand">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: rgba(99, 102, 241, 0.25); border: 1px solid rgba(99, 102, 241, 0.4); color: #818cf8;">
                <i class="bi bi-box-seam fs-5"></i>
            </div>
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="fw-bold">ShopCalm</span>
                    <span class="badge rounded-pill" style="font-size: 0.62rem; background: #6366f1; color: #fff;">PM</span>
                </div>
                <span class="text-white-50" style="font-size: 0.68rem; letter-spacing: 0.02em;">Product Manager</span>
            </div>
        </a>

        <!-- Navigation Links -->
        <div class="sidebar-sticky">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.dashboard') ? 'active' : '' }}" href="{{ route('product-manager.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>

                <!-- CATALOG SECTION -->
                <li class="nav-header">Catalog Management</li>
                @php
                    $isCatalogActive = request()->routeIs('product-manager.products.*') || request()->routeIs('product-manager.categories.*') || request()->routeIs('product-manager.brands.*');
                    $catalogAlertCount = ($pmPendingApprovals ?? 0) + ($pmRejectedProducts ?? 0);
                @endphp
                <li class="nav-item">
                    <a class="nav-link has-submenu {{ $isCatalogActive ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" href="#catalogSubmenu" role="button"
                       aria-expanded="{{ $isCatalogActive ? 'true' : 'false' }}">
                        <i class="bi bi-grid"></i>
                        <span class="nav-label">Catalog</span>
                        @if($catalogAlertCount > 0)
                            <span class="badge rounded-pill bg-warning text-dark nav-badge">
                                {{ $catalogAlertCount }}
                            </span>
                        @endif
                    </a>
                    <div class="collapse {{ $isCatalogActive ? 'show' : '' }}" id="catalogSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.products.index') || request()->routeIs('product-manager.products.edit') ? 'active' : '' }}" href="{{ route('product-manager.products.index') }}">
                                    <span class="nav-label">All Products</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.products.pending') ? 'active' : '' }}" href="{{ route('product-manager.products.pending') }}">
                                    <span class="nav-label">Pending Approvals</span>
                                    @if(($pmPendingApprovals ?? 0) > 0)
                                        <span class="badge bg-warning text-dark rounded-pill nav-badge">{{ $pmPendingApprovals }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.products.rejected') ? 'active' : '' }}" href="{{ route('product-manager.products.rejected') }}">
                                    <span class="nav-label">Rejected Items</span>
                                    @if(($pmRejectedProducts ?? 0) > 0)
                                        <span class="badge bg-danger text-white rounded-pill nav-badge">{{ $pmRejectedProducts }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.products.create') ? 'active' : '' }}" href="{{ route('product-manager.products.create') }}">
                                    <span class="nav-label">Add New Product</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.categories.*') ? 'active' : '' }}" href="{{ route('product-manager.categories.index') }}">
                                    <span class="nav-label">Categories</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.brands.*') ? 'active' : '' }}" href="{{ route('product-manager.brands.index') }}">
                                    <span class="nav-label">Brands</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- INVENTORY SECTION -->
                <li class="nav-header">Inventory</li>
                @php
                    $isStockActive = request()->routeIs('product-manager.stock.*');
                @endphp
                <li class="nav-item">
                    <a class="nav-link has-submenu {{ $isStockActive ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" href="#stockSubmenu" role="button"
                       aria-expanded="{{ $isStockActive ? 'true' : 'false' }}">
                        <i class="bi bi-boxes"></i>
                        <span class="nav-label">Inventory</span>
                        @if(($pmLowStockCount ?? 0) > 0)
                            <span class="badge rounded-pill bg-danger text-white nav-badge">
                                {{ $pmLowStockCount }} Low
                            </span>
                        @endif
                    </a>
                    <div class="collapse {{ $isStockActive ? 'show' : '' }}" id="stockSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.stock.dashboard') || request()->routeIs('product-manager.stock.form') ? 'active' : '' }}" href="{{ route('product-manager.stock.dashboard') }}">
                                    <span class="nav-label">Stock Management</span>
                                    @if(($pmLowStockCount ?? 0) > 0)
                                        <span class="badge bg-danger text-white rounded-pill nav-badge">{{ $pmLowStockCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('product-manager.stock.history') ? 'active' : '' }}" href="{{ route('product-manager.stock.history') }}">
                                    <span class="nav-label">Stock History</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- QUALITY & INTELLIGENCE -->
                <li class="nav-header">Quality & Intelligence</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.reviews.*') ? 'active' : '' }}" href="{{ route('product-manager.reviews.index') }}">
                        <i class="bi bi-star"></i>
                        <span class="nav-label">Reviews</span>
                        @if(($pmPendingReviews ?? 0) > 0)
                            <span class="badge bg-primary text-white rounded-pill nav-badge">
                                {{ $pmPendingReviews }} New
                            </span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('product-manager.reports.*') ? 'active' : '' }}" href="{{ route('product-manager.reports.index') }}">
                        <i class="bi bi-graph-up"></i>
                        <span class="nav-label">Reports</span>
                    </a>
                </li>

                <!-- LOGOUT -->
                <li class="nav-item mt-4 pt-2 border-top" style="border-color: rgba(255,255,255,0.06) !important;">
                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('pm-logout-form').submit();">
                        <i class="bi bi-box-arrow-right text-danger"></i>
                        <span class="nav-label">Logout</span>
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
                    <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center" style="font-size: 0.78rem; gap: 0.4rem !important;">
                        <i class="bi bi-box-arrow-up-right"></i> Live Store
                    </a>
                </li>
                <li class="nav-item">
                    @php $pmUser = Auth::guard('product_manager')->user() ?? Auth::guard('admin')->user(); @endphp
                    <div class="d-flex align-items-center ms-2 px-3 py-1.5 rounded-3" style="background:#f1f5f9; border:1px solid #e2e8f0; gap: 0.65rem !important;">
                        <div style="width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr($pmUser->name ?? 'PM', 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:0.8rem;font-weight:600;color:#0f172a;line-height:1.1;">{{ $pmUser->name ?? 'Product Manager' }}</div>
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

    @include('components.staff-live-poller', ['endpoint' => route('admin.live-orders'), 'portal' => 'admin'])

    @stack('scripts')
</body>
</html>
