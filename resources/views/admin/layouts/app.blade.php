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
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
            --sidebar-active: #3b82f6;
            --navbar-height: 60px;
            --primary-color: #3b82f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-size: .875rem;
            background-color: #f8fafc;
            overflow-x: hidden;
        }

        /* Fixed Top Navbar */
        .navbar-admin {
            height: var(--navbar-height);
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            z-index: 1030;
            padding-left: var(--sidebar-width);
            transition: padding-left 0.3s ease;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            z-index: 1040;
            transition: transform 0.3s ease;
            overflow-y: auto;
            color: #fff;
        }

        .sidebar-brand {
            height: var(--navbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            background: rgba(0,0,0,0.1);
            font-weight: 700;
            font-size: 1.25rem;
            color: #fff;
            text-decoration: none;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            border-radius: 0.5rem;
            margin: 0.2rem 0.75rem;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: var(--sidebar-active);
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }

        .sidebar .nav-link i {
            font-size: 1.25rem;
            margin-right: 0.75rem;
        }

        .sidebar .nav-header {
            padding: 1.5rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
        }

        /* Submenu */
        .sidebar .submenu {
            padding-left: 1rem;
        }

        .sidebar .nav-link.has-submenu::after {
            content: "\F282";
            font-family: "bootstrap-icons";
            margin-left: auto;
            font-size: 0.8rem;
            transition: transform 0.3s;
        }

        .sidebar .nav-link.has-submenu[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        /* Main Content */
        main.main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--navbar-height) + 1.5rem);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
        }

        /* Responsive Sidebar */
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

        /* Breadcrumb Styling */
        .breadcrumb-item + .breadcrumb-item::before {
            content: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E");
        }

        /* Stats Card Styles */
        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
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
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">Banners</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-header">Promotions & Sales</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}" href="{{ route('admin.offers.index') }}">
                        <i class="bi bi-fire text-warning"></i> Mega Sales & Offers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">
                        <i class="bi bi-ticket-perforated"></i> Coupons
                    </a>
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
                    <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">
                        <i class="bi bi-people"></i> Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                        <i class="bi bi-star"></i> Reviews
                    </a>
                </li>
                @if(auth()->user()->isSuperAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">
                        <i class="bi bi-shield-lock"></i> Roles & Admins
                    </a>
                </li>
                @endif

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
                    <div class="d-flex align-items-center ms-2">
                        <div class="text-end me-2 d-none d-sm-block">
                            <div class="fw-bold small">{{ Auth::guard('admin')->user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ Auth::guard('admin')->user()->role->name }}</div>
                        </div>
                        <img src="{{ Auth::guard('admin')->user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::guard('admin')->user()->name).'&background=3b82f6&color=fff' }}"
                             alt="Admin" class="rounded-circle shadow-sm" style="width: 35px; height: 35px; object-fit: cover;">
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
    <script src="{{ asset('js/ui-interactions.js') }}"></script>

    <script>
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
