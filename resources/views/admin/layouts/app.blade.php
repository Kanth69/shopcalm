<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Shopcalm') }} - Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css">

    <style>
        /* ============================================
           SHOPCALM ADMIN — PREMIUM UI LAYER
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
            border: 1px solid var(--border) !important;
            border-radius: var(--radius) !important;
            box-shadow: var(--shadow-sm);
            background: var(--surface);
            transition: box-shadow 0.2s;
        }
        .card:hover { box-shadow: var(--shadow); }
        .card-header {
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }
        .card-footer {
            border-top: 1px solid var(--border);
        }

        /* ── TABLES ── */
        .table {
            margin-bottom: 0;
            font-size: 0.84rem;
        }
        .table thead th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1rem;
            white-space: nowrap;
        }
        .table tbody td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: var(--text);
        }
        .table-hover tbody tr {
            transition: background 0.15s;
        }
        .table-hover tbody tr:hover {
            background: #f8fafc !important;
        }
        .table tbody tr:last-child td { border-bottom: none; }

        /* ── BADGES ── */
        .badge {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            padding: 0.3em 0.75em;
            border-radius: 999px;
        }
        .bg-success { background: #dcfce7 !important; color: #16a34a !important; }
        .bg-danger  { background: #fee2e2 !important; color: #dc2626 !important; }
        .bg-warning { background: #fef9c3 !important; color: #d97706 !important; }
        .bg-info    { background: #cffafe !important; color: #0891b2 !important; }
        .bg-primary { background: #ede9fe !important; color: #7c3aed !important; }
        .bg-secondary { background: #f1f5f9 !important; color: #64748b !important; }

        /* ── BUTTONS ── */
        .btn {
            font-size: 0.82rem;
            font-weight: 500;
            border-radius: 8px;
            padding: 0.45rem 1rem;
            transition: all 0.18s ease;
            letter-spacing: 0.01em;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            color: #fff;
            box-shadow: 0 2px 8px rgba(99,102,241,0.35);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 4px 12px rgba(99,102,241,0.45);
            transform: translateY(-1px);
            color: #fff;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            color: #fff;
            box-shadow: 0 2px 8px rgba(239,68,68,0.3);
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: #fff;
            transform: translateY(-1px);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            color: #fff;
        }
        .btn-success:hover { background: linear-gradient(135deg, #059669, #047857); color: #fff; transform: translateY(-1px); }
        .btn-light {
            background: #f8fafc;
            border: 1px solid var(--border);
            color: var(--text-muted);
        }
        .btn-light:hover { background: #f1f5f9; color: var(--text); }
        .btn-outline-primary {
            border: 1px solid #6366f1;
            color: #6366f1;
            background: transparent;
        }
        .btn-outline-primary:hover { background: #6366f1; color: #fff; }
        .btn-outline-danger {
            border: 1px solid #ef4444;
            color: #ef4444;
            background: transparent;
        }
        .btn-outline-danger:hover { background: #ef4444; color: #fff; }
        .btn-sm { padding: 0.3rem 0.7rem; font-size: 0.78rem; border-radius: 6px; }

        /* ── FORMS ── */
        .form-control, .form-select {
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.84rem;
            padding: 0.5rem 0.85rem;
            transition: border-color 0.18s, box-shadow 0.18s;
            background: var(--surface);
            color: var(--text);
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.12);
            outline: none;
        }
        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.4rem;
        }
        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        /* ── PAGINATION ── */
        .pagination .page-link {
            border-radius: 8px !important;
            margin: 0 2px;
            border: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.82rem;
            padding: 0.4rem 0.75rem;
            transition: all 0.15s;
        }
        .pagination .page-link:hover { background: #f1f5f9; color: var(--text); }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 2px 8px rgba(99,102,241,0.35);
        }

        /* ── ALERTS ── */
        .alert {
            border-radius: var(--radius);
            border: none;
            font-size: 0.84rem;
        }
        .alert-success { background: #f0fdf4; color: #15803d; }
        .alert-danger  { background: #fef2f2; color: #b91c1c; }
        .alert-warning { background: #fffbeb; color: #b45309; }
        .alert-info    { background: #f0f9ff; color: #0369a1; }

        /* ── STAT CARD ICON ── */
        .stat-card-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }

        /* ── BREADCRUMB ── */
        .breadcrumb-item + .breadcrumb-item::before {
            content: '/';
            color: var(--border);
        }
        .breadcrumb-item a { color: var(--text-muted); text-decoration: none; font-size: 0.8rem; }
        .breadcrumb-item.active { font-size: 0.8rem; color: var(--text-muted); }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .navbar-admin { padding-left: 0; }
            main.main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand px-3 py-2 d-flex align-items-center">
            <x-logo variant="light" height="28" />
        </a>

        <div class="sidebar-sticky pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>

                <li class="nav-header">Catalog</li>
                <li class="nav-item">
                    <a class="nav-link has-submenu {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.banners.*') ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" href="#catalogSubmenu" role="button"
                       aria-expanded="{{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.banners.*') ? 'true' : 'false' }}">
                        <i class="bi bi-grid"></i> Catalog
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.products.*') || request()->routeIs('admin.categories.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.banners.*') ? 'show' : '' }}" id="catalogSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Products</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">Categories</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">Brands</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-header">Sales & Orders</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                        <i class="bi bi-cart-check"></i> Orders
                    </a>
                </li>

                <li class="nav-header">Promotions</li>
                <li class="nav-item">
                    <a class="nav-link has-submenu {{ request()->routeIs('admin.offers.*') || request()->routeIs('admin.coupons.*') || request()->routeIs('admin.banners.*') ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" href="#promotionsSubmenu" role="button"
                       aria-expanded="{{ request()->routeIs('admin.offers.*') || request()->routeIs('admin.coupons.*') || request()->routeIs('admin.banners.*') ? 'true' : 'false' }}">
                        <i class="bi bi-megaphone"></i> Promotions
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.offers.*') || request()->routeIs('admin.coupons.*') || request()->routeIs('admin.banners.*') ? 'show' : '' }}" id="promotionsSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}" href="{{ route('admin.offers.index') }}">Mega Sales & Offers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">Coupons</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">Banners</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-header">Inventory</li>
                <li class="nav-item">
                    <a class="nav-link has-submenu {{ request()->routeIs('admin.stock.*') ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" href="#stockSubmenu" role="button"
                       aria-expanded="{{ request()->routeIs('admin.stock.*') ? 'true' : 'false' }}">
                        <i class="bi bi-boxes"></i> Inventory
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.stock.*') ? 'show' : '' }}" id="stockSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.stock.dashboard') ? 'active' : '' }}" href="{{ route('admin.stock.dashboard') }}">Stock Management</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.stock.history') ? 'active' : '' }}" href="{{ route('admin.stock.history') }}">Stock History</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-header">User Management</li>
                <li class="nav-item">
                    <a class="nav-link has-submenu {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.roles.*') ? '' : 'collapsed' }}"
                       data-bs-toggle="collapse" href="#userSubmenu" role="button"
                       aria-expanded="{{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.roles.*') ? 'true' : 'false' }}">
                        <i class="bi bi-people"></i> Users
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.customers.*') || request()->routeIs('admin.reviews.*') || request()->routeIs('admin.roles.*') ? 'show' : '' }}" id="userSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">Customers</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}" href="{{ route('admin.subscribers.index') }}">Newsletter Subscribers</a>
                            </li>
                            @if(auth()->user()->isSuperAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">Roles & Admins</a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>

                <li class="nav-header">Content</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">
                        <i class="bi bi-file-earmark-text"></i> Pages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}" href="{{ route('admin.enquiries.index') }}">
                        <i class="bi bi-chat-left-text"></i> Contact Enquiries
                        @php
                            $unreadCount = \App\Models\ContactEnquiry::where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-auto rounded-pill">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>

                <li class="nav-header">Other</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.profile.index') ? 'active' : '' }}" href="{{ route('admin.profile.index') }}">
                        <i class="bi bi-person-circle"></i> Profile
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link text-danger" href="#" onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                    <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
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
                <li class="nav-item">
                    <div class="d-flex align-items-center gap-2 ms-2 px-3 py-1 rounded-3" style="background:#f1f5f9; border:1px solid #e2e8f0;">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:#fff;flex-shrink:0;">
                            {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:0.8rem;font-weight:600;color:#0f172a;line-height:1.1;">{{ Auth::guard('admin')->user()->name }}</div>
                            <div style="font-size:0.65rem;color:#6366f1;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">{{ Auth::guard('admin')->user()->role->name }}</div>
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
                <small>&copy; {{ date('Y') }} Shopcalm Admin. All rights reserved.</small>
            </footer>
        </div>
    </main>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/ui-interactions.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/admin-filters.js') }}?v={{ time() }}"></script>

    <script>
        function initTinyMCE() {
            if (document.getElementById('page-content')) {
                // Remove existing instances if navigating via AJAX
                tinymce.remove('#page-content');
                tinymce.init({
                    selector: '#page-content',
                    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                    height: 500,
                    menubar: false,
                    branding: false,
                    promotion: false,
                    setup: function (editor) {
                        editor.on('change', function () {
                            editor.save();
                        });
                    }
                });
            }
        }

        document.addEventListener('DOMContentLoaded', initTinyMCE);
        document.addEventListener('adminFiltersUpdated', initTinyMCE);
        
        // Handle Corporate Page JSON Compilation on Form Submit via Delegation
        document.body.addEventListener('submit', function(e) {
            const form = e.target;
            const jsonField = form.querySelector('#page-content-json');
            
            if (jsonField) {
                // Check if it's the About Us page
                if (document.getElementById('about_hero_title')) {
                    const formatText = (text) => text.split('\n').filter(p => p.trim() !== '').map(p => `<p>${p}</p>`).join('');
                    
                    const data = {
                        hero_title: document.getElementById('about_hero_title')?.value || '',
                        tagline: document.getElementById('about_tagline')?.value || '',
                        supporting_text: formatText(document.getElementById('about_supporting_text')?.value || ''),
                        mission: formatText(document.getElementById('about_mission')?.value || ''),
                        focus_areas: []
                    };
                    
                    document.querySelectorAll('.focus-item').forEach(item => {
                        data.focus_areas.push({
                            title: item.querySelector('.focus-title')?.value || '',
                            desc: item.querySelector('.focus-desc')?.value || ''
                        });
                    });
                    
                    jsonField.value = JSON.stringify(data);
                } 
                // Check if it's the Contact Us page
                else if (document.getElementById('contact_hero_title')) {
                    const data = {
                        hero_title: document.getElementById('contact_hero_title')?.value || '',
                        hero_subtitle: document.getElementById('contact_hero_subtitle')?.value || '',
                        info_title: document.getElementById('contact_info_title')?.value || '',
                        info_subtitle: document.getElementById('contact_info_subtitle')?.value || '',
                        form_title: document.getElementById('contact_form_title')?.value || ''
                    };
                    
                    jsonField.value = JSON.stringify(data);
                } 
                // Check if it's a Long Form Legal page
                else if (document.getElementById('legal_intro')) {
                    const formatText = (text) => text.split('\n').filter(p => p.trim() !== '').map(p => `<p>${p}</p>`).join('');
                    
                    const data = {
                        intro: formatText(document.getElementById('legal_intro')?.value || ''),
                        sections: []
                    };
                    
                    for (let i = 1; i <= 10; i++) {
                        const title = document.getElementById(`legal_section_${i}_title`)?.value || '';
                        const content = document.getElementById(`legal_section_${i}_content`)?.value || '';
                        
                        if (title.trim() !== '' || content.trim() !== '') {
                            data.sections.push({
                                title: title,
                                content: formatText(content)
                            });
                        }
                    }
                    
                    jsonField.value = JSON.stringify(data);
                }
                // Check if it's the FAQ page
                else if (document.getElementById('faq_hero_title')) {
                    const formatText = (text) => text.split('\n').filter(p => p.trim() !== '').map(p => `<p>${p}</p>`).join('');
                    
                    const data = {
                        hero_title: document.getElementById('faq_hero_title')?.value || '',
                        hero_subtitle: document.getElementById('faq_hero_subtitle')?.value || '',
                        faqs: []
                    };
                    
                    for (let i = 1; i <= 30; i++) {
                        const question = document.getElementById(`faq_${i}_question`)?.value || '';
                        const answer = document.getElementById(`faq_${i}_answer`)?.value || '';
                        
                        if (question.trim() !== '' || answer.trim() !== '') {
                            data.faqs.push({
                                question: question,
                                answer: formatText(answer)
                            });
                        }
                    }
                    
                    jsonField.value = JSON.stringify(data);
                }
            }
        });
        
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
    @stack('scripts')
</body>
</html>
