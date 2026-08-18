<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catalog & Inventory Workspace') - Product Manager Portal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --pm-primary: #7c3aed;
            --pm-primary-dark: #6d28d9;
            --pm-sidebar-bg: #0f172a;
            --pm-bg: #f8fafc;
            --pm-card-border: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--pm-bg);
            color: #1e293b;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        #pmSidebar {
            width: 260px;
            background: var(--pm-sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1030;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .pm-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .pm-brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }

        .pm-nav-section {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 20px 20px 8px;
        }

        .pm-nav-link {
            display: flex;
            align-items: center;
            padding: 10px 18px;
            margin: 3px 12px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .pm-nav-link i {
            font-size: 1.1rem;
            margin-right: 12px;
            width: 22px;
            text-align: center;
        }

        .pm-nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
        }

        .pm-nav-link.active {
            color: #ffffff;
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        }

        /* Main Content Area */
        #pmMain {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .pm-topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid var(--pm-card-border);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .pm-content {
            padding: 28px;
            flex-grow: 1;
        }

        .card {
            border: 1px solid var(--pm-card-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .btn-pm-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
            color: #ffffff;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s;
        }

        .btn-pm-primary:hover {
            opacity: 0.95;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
        }

        @media (max-width: 991.98px) {
            #pmSidebar {
                margin-left: -260px;
            }
            #pmSidebar.show {
                margin-left: 0;
            }
            #pmMain {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar -->
<aside id="pmSidebar">
    <!-- Brand -->
    <div class="pm-brand">
        <div class="pm-brand-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div>
            <div class="text-white fw-bold" style="font-size: 0.95rem; letter-spacing: -0.2px;">ShopCalm</div>
            <div class="badge rounded-pill text-uppercase" style="font-size: 0.62rem; background: #7c3aed; letter-spacing: 0.5px;">Product Portal</div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="py-2">
        <div class="pm-nav-section">Overview</div>
        <a href="{{ route('product-manager.dashboard') }}" class="pm-nav-link {{ request()->routeIs('product-manager.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
        </a>

        <div class="pm-nav-section">Catalog Management</div>
        <a href="{{ route('product-manager.products.index') }}" class="pm-nav-link {{ request()->routeIs('product-manager.products.index') || request()->routeIs('product-manager.products.edit') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i>
            <span>All Products</span>
        </a>
        <a href="{{ route('product-manager.products.pending') }}" class="pm-nav-link {{ request()->routeIs('product-manager.products.pending') ? 'active' : '' }}">
            <i class="bi bi-hourglass-split"></i>
            <span>Pending Approvals</span>
        </a>
        <a href="{{ route('product-manager.products.rejected') }}" class="pm-nav-link {{ request()->routeIs('product-manager.products.rejected') ? 'active' : '' }}">
            <i class="bi bi-x-octagon"></i>
            <span>Rejected Items</span>
        </a>
        <a href="{{ route('product-manager.products.create') }}" class="pm-nav-link {{ request()->routeIs('product-manager.products.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i>
            <span>Add Product</span>
        </a>
        <a href="{{ route('product-manager.categories.index') }}" class="pm-nav-link {{ request()->routeIs('product-manager.categories.*') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            <span>Categories</span>
        </a>
        <a href="{{ route('product-manager.brands.index') }}" class="pm-nav-link {{ request()->routeIs('product-manager.brands.*') ? 'active' : '' }}">
            <i class="bi bi-tag"></i>
            <span>Brands</span>
        </a>

        <div class="pm-nav-section">Inventory & Operations</div>
        <a href="{{ route('product-manager.stock.dashboard') }}" class="pm-nav-link {{ request()->routeIs('product-manager.stock.dashboard') || request()->routeIs('product-manager.stock.form') ? 'active' : '' }}">
            <i class="bi bi-stack"></i>
            <span>Inventory / Stock</span>
        </a>
        <a href="{{ route('product-manager.stock.history') }}" class="pm-nav-link {{ request()->routeIs('product-manager.stock.history') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Stock History</span>
        </a>

        <div class="pm-nav-section">Quality & Intelligence</div>
        <a href="{{ route('product-manager.reviews.index') }}" class="pm-nav-link {{ request()->routeIs('product-manager.reviews.*') ? 'active' : '' }}">
            <i class="bi bi-star"></i>
            <span>Product Reviews</span>
        </a>
        <a href="{{ route('product-manager.reports.index') }}" class="pm-nav-link {{ request()->routeIs('product-manager.reports.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i>
            <span>Inventory Reports</span>
        </a>
    </div>

    <!-- User Section Bottom -->
    <div class="p-3 mt-4 border-top border-secondary border-opacity-25">
        <div class="d-flex align-items-center gap-2 text-white-50 small mb-2 px-2">
            <i class="bi bi-shield-lock text-warning"></i>
            <span>Logged in as:</span>
        </div>
        <div class="d-flex align-items-center justify-content-between bg-dark bg-opacity-50 p-2.5 rounded-3">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle bg-purple text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; background: #7c3aed; font-size: 0.8rem;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'PM', 0, 2)) }}
                </div>
                <div class="text-truncate">
                    <div class="text-white small fw-bold text-truncate" style="font-size: 0.82rem;">{{ auth()->user()->name ?? 'Staff' }}</div>
                    <div class="text-muted text-truncate" style="font-size: 0.7rem;">Product Manager</div>
                </div>
            </div>
            <form action="{{ route('product-manager.logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Sign Out">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Main Workspace -->
<div id="pmMain">
    <!-- Topbar -->
    <header class="pm-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none rounded-3 p-2" type="button" onclick="document.getElementById('pmSidebar').classList.toggle('show')">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h5 class="fw-bold mb-0 text-dark">@yield('header', 'Catalog Workspace')</h5>
                <small class="text-muted">@yield('subheader', 'Product & Inventory Operations Console')</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Preview Customer Storefront">
                <i class="bi bi-box-arrow-up-right me-1"></i> Live Store
            </a>
            <form action="{{ route('product-manager.logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-light text-danger rounded-pill px-3 fw-semibold border">
                    <i class="bi bi-power me-1"></i> Logout
                </button>
            </form>
        </div>
    </header>

    <!-- Page Content -->
    <main class="pm-content">
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Flash Toast Notifications -->
@if(session('toast'))
<script>
    Swal.fire({
        icon: "{{ session('toast.type', 'info') }}",
        title: "{{ session('toast.title', '') }}",
        text: "{{ session('toast.message', '') }}",
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
    });
</script>
@endif

@stack('scripts')
</body>
</html>
