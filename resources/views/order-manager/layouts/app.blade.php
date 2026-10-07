<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Order Operations') — ShopCalm Staff</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.min.css">

    <style>
        :root {
            --om-sidebar-width: 260px;
            --om-navbar-height: 78px;
            --om-sidebar-bg: #091322;
            --om-primary: #0284c7;
            --om-primary-hover: #0369a1;
            --om-text-dark: #0f172a;
            --om-text-muted: #475569;
            --om-border: #e2e8f0;
            --om-bg: #f8fafc;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--om-bg);
            color: var(--om-text-dark);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: var(--om-sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #091322 0%, #0d1e34 100%);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            transition: transform 0.25s ease-in-out;
            color: #ffffff;
        }

        .sidebar-brand {
            height: var(--om-navbar-height);
            padding: 0 1.25rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.02);
            flex-shrink: 0;
        }

        .sidebar-brand .brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.2rem;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            flex-shrink: 0;
            margin-right: 2px;
        }

        .sidebar-sticky {
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 0.85rem 0.75rem 2rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.25) transparent;
        }
        .sidebar-sticky::-webkit-scrollbar { width: 5px; }
        .sidebar-sticky::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.1); }
        .sidebar-sticky::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.25); border-radius: 4px; }
        .sidebar-sticky::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.5); }

        .nav-header {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            font-weight: 700;
            color: #64748b;
            padding: 1.25rem 0.85rem 0.4rem;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #cbd5e1;
            padding: 0.7rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.95rem;
            font-size: 0.84rem;
            transition: all 0.18s ease;
            text-decoration: none;
            margin: 3px 0.25rem;
            letter-spacing: 0.01em;
        }

        .sidebar .nav-link i {
            font-size: 1.15rem;
            color: #94a3b8;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
            margin-right: 2px;
            transition: color 0.18s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .sidebar .nav-link:hover i {
            color: #38bdf8;
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
        }

        .sidebar .nav-link.active i {
            color: #ffffff !important;
        }

        .sidebar .nav-badge {
            margin-left: auto;
            font-size: 0.68rem;
            padding: 0.22em 0.6em;
            border-radius: 9999px;
            font-weight: 700;
            line-height: 1.2;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 0.85rem 1.15rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }

        /* ── TOP NAVBAR ── */
        .top-navbar {
            margin-left: var(--om-sidebar-width);
            background: #ffffff;
            border-bottom: 1px solid var(--om-border);
            padding: 0.85rem 2.5rem;
            height: var(--om-navbar-height);
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
            margin-left: var(--om-sidebar-width);
            padding: 2rem 2.25rem;
            min-height: calc(100vh - var(--om-navbar-height));
        }

        .card {
            border: 1px solid var(--om-border);
            border-radius: 1rem;
            background-color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        /* Action Buttons */
        .action-btn-circle {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            transition: all 0.2s cubic-bezier(.4,0,.2,1);
            text-decoration: none;
            font-size: 0.85rem;
        }
        .action-btn-circle:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }
        .action-btn-view {
            background: #f0f9ff;
            color: #0284c7;
            border-color: #bae6fd;
        }
        .action-btn-view:hover {
            background: #0284c7;
            color: #ffffff;
        }
        .action-btn-invoice {
            background: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }
        .action-btn-invoice:hover {
            background: #334155;
            color: #ffffff;
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
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Left Navigation Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('order-manager.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none text-white w-100">
            <div class="brand-logo me-1">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div class="d-flex flex-column" style="line-height: 1.15;">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="fw-bolder text-white" style="font-size: 1.15rem; letter-spacing: -0.4px;">Shop<span style="color: #38bdf8;">Calm</span></span>
                    <span class="badge rounded-pill" style="background: #0284c7; color: #ffffff; font-size: 0.6rem; font-weight: 800; padding: 0.2rem 0.45rem;">STAFF</span>
                </div>
                <span style="font-size: 0.68rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 2px;">Logistics Engine</span>
            </div>
        </a>
    </div>

    @php
        $omUser = Auth::guard('order_manager')->user() ?? Auth::guard('admin')->user();
        $activeExceptionsCount = \App\Models\OrderFulfillment::activeExceptions()
            ->whereHas('order', function($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled', 'returned']);
            })->count();

        $recentExceptionsList = \App\Models\OrderFulfillment::with(['order', 'rider'])
            ->activeExceptions()
            ->whereHas('order', function($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled', 'returned']);
            })
            ->latest('delivery_issue_at')
            ->take(5)
            ->get();
    @endphp

    <div class="sidebar-sticky">
        <div class="nav-header">Command Center</div>
        <a href="{{ route('order-manager.dashboard') }}" class="nav-link {{ request()->routeIs('order-manager.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Operations Dashboard</span>
        </a>

        <div class="nav-header">Live Fleet Monitor</div>
        <a href="{{ route('order-manager.alerts') }}" class="nav-link {{ request()->routeIs('order-manager.alerts') ? 'active' : '' }}">
            <i class="bi bi-exclamation-octagon-fill text-danger"></i>
            <span>Delivery Alerts</span>
            @if($activeExceptionsCount > 0)
                <span class="nav-badge" style="background: #e11d48; color: #fff;">{{ $activeExceptionsCount }}</span>
            @endif
        </a>

        <div class="nav-header">Fleet Finances</div>
        <a href="{{ route('order-manager.settlements.index') }}" class="nav-link {{ request()->routeIs('order-manager.settlements.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack text-success"></i>
            <span>COD Settlements</span>
            <span class="nav-badge" style="background: #10b981; color: #fff;">Hub Desk</span>
        </a>

        <div class="nav-header">Fulfillment Queues</div>
        <a href="{{ route('order-manager.orders.index', ['zone' => 'bengaluru']) }}" class="nav-link {{ request()->get('zone') === 'bengaluru' ? 'active' : '' }}">
            <i class="bi bi-geo-fill text-info"></i>
            <span>Bengaluru Fleet Hub</span>
            <span class="nav-badge" style="background: #0284c7; color: #fff;">Local</span>
        </a>

        <a href="{{ route('order-manager.orders.index', ['zone' => 'courier']) }}" class="nav-link {{ request()->get('zone') === 'courier' ? 'active' : '' }}">
            <i class="bi bi-truck text-primary"></i>
            <span>National Courier Hub</span>
            <span class="nav-badge" style="background: #4f46e5; color: #fff;">Pan-India</span>
        </a>

        <a href="{{ route('order-manager.orders.index') }}" class="nav-link {{ request()->routeIs('order-manager.orders.index') && !request()->filled('zone') && !request()->filled('status') ? 'active' : '' }}">
            <i class="bi bi-boxes"></i>
            <span>All Orders Catalog</span>
        </a>

        <div class="nav-header">Analytics</div>
        <a href="{{ route('order-manager.rider-performance.index') }}" class="nav-link {{ request()->routeIs('order-manager.rider-performance.*') ? 'active' : '' }}">
            <i class="bi bi-star-fill text-warning"></i>
            <span>Rider Ratings &amp; Feedbacks</span>
        </a>

        <div class="nav-header">Customer Payouts</div>
        <a href="{{ route('order-manager.refunds.index') }}" class="nav-link {{ request()->routeIs('order-manager.refunds.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2 text-warning"></i>
            <span>Customer Refunds</span>
            @php $pendingUpiCount = \App\Models\OrderCancellation::where('refund_method', 'bank_upi')->where('refund_status', 'pending')->count(); @endphp
            @if($pendingUpiCount > 0)
                <span class="nav-badge" style="background: #f59e0b; color: #fff;">{{ $pendingUpiCount }}</span>
            @else
                <span class="nav-badge" style="background: #334155; color: #cbd5e1;">UPI Desk</span>
            @endif
        </a>
    </div>

    <!-- Staff User Footer -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.8rem; background: #0284c7;">
                    {{ strtoupper(substr($omUser->name ?? 'O', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate" style="font-size: 0.82rem; line-height: 1.1;">{{ $omUser->name ?? 'Order Manager' }}</div>
                    <div class="text-white-50 small" style="font-size: 0.68rem;">Logistics Staff</div>
                </div>
            </div>
            <form action="{{ route('order-manager.logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link text-white-50 p-1 text-decoration-none" title="Sign Out">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Top Navbar Header -->
<header class="top-navbar">
    <div class="d-flex align-items-center gap-4">
        <button class="btn btn-sm btn-light d-lg-none" type="button" onclick="document.querySelector('.sidebar').classList.toggle('show')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <div class="d-flex align-items-center gap-2.5">
                <h5 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.3px; font-size: 1.15rem;">@yield('header', 'Order Operations')</h5>
                <span class="badge rounded-pill bg-light text-secondary border px-2.5 py-1 small fw-bold" style="font-size: 0.7rem;">Central Hub #01</span>
            </div>
        </div>
        <div class="ms-3">
            @yield('actions')
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Live Alerts Bell Dropdown -->
        <div class="dropdown">
            <button class="btn btn-sm btn-light border rounded-circle position-relative p-0 d-flex align-items-center justify-content-center shadow-xs" 
                    type="button" id="alertsDropdown" data-bs-toggle="dropdown" aria-expanded="false" 
                    style="width: 36px; height: 36px;" title="Delivery Alerts & Exceptions">
                <i class="bi bi-bell-fill {{ $activeExceptionsCount > 0 ? 'text-danger' : 'text-secondary' }}"></i>
                @if($activeExceptionsCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.25rem 0.4rem;">
                        {{ $activeExceptionsCount }}
                    </span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border rounded-4 p-0 mt-2" aria-labelledby="alertsDropdown" style="width: 340px; border-color: #e2e8f0 !important;">
                <div class="p-3 bg-light border-bottom d-flex align-items-center justify-content-between rounded-top-4">
                    <div class="fw-bold text-dark small">
                        <i class="bi bi-exclamation-octagon-fill text-danger me-1"></i> Delivery Alerts
                    </div>
                    <span class="badge bg-danger rounded-pill px-2 py-0.5 small">{{ $activeExceptionsCount }} Pending</span>
                </div>
                <div class="p-2" style="max-height: 280px; overflow-y: auto;">
                    @forelse($recentExceptionsList as $exc)
                        <a href="{{ $exc->order ? route('order-manager.orders.show', $exc->order) : '#' }}" class="dropdown-item rounded-3 p-2.5 mb-1 text-wrap border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-primary font-monospace small" style="font-size: 0.78rem;">#{{ $exc->order?->order_number ?? 'N/A' }}</span>
                                <span class="text-secondary small" style="font-size: 0.65rem;">{{ $exc->delivery_issue_at?->diffForHumans() ?? $exc->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-danger small fw-bold mt-1" style="font-size: 0.72rem; line-height: 1.3;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $exc->delivery_issue ?? $exc->notes }}
                            </div>
                            <div class="text-secondary small mt-0.5" style="font-size: 0.68rem;">
                                Customer: <strong>{{ $exc->order?->shipping_name ?? 'Customer' }}</strong> &bull; Rider: {{ $exc->rider?->name ?? ($exc->rider_name ?? 'Rider') }}
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="bi bi-check-circle-fill text-success d-block mb-1 fs-4"></i>
                            No delivery issues reported.
                        </div>
                    @endforelse
                </div>
                <div class="p-2.5 bg-light border-top text-center rounded-bottom-4">
                    <a href="{{ route('order-manager.alerts') }}" class="small fw-bold text-decoration-none text-dark" style="font-size: 0.75rem;">
                        View All Delivery Alerts ({{ $activeExceptionsCount }}) &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Live Operations Pulse & Sound Notification Indicator -->
        <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-light border" style="font-size: 0.78rem;">
            <span class="pulse-dot"></span>
            <span class="fw-bold text-dark" style="font-size: 0.72rem;">LIVE PULSE</span>
            <button type="button" class="btn btn-link p-0 text-decoration-none border-start ps-2 ms-1 text-dark staff-sound-toggle-btn" onclick="toggleStaffSound()" title="Toggle Notification Sound">
                <i id="staffSoundIcon" class="bi bi-volume-up-fill text-success fs-5"></i>
                <span id="staffSoundText" class="text-success small fw-bold ms-1" style="font-size: 0.7rem;">Sound ON</span>
            </button>
        </div>

        <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
            <i class="bi bi-box-arrow-up-right me-1"></i> Storefront
        </a>

        <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 bg-light border">
            <div style="width: 26px; height: 26px; border-radius: 50%; background: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; color: #fff; flex-shrink: 0;">
                {{ strtoupper(substr($omUser->name ?? 'O', 0, 1)) }}
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #0f172a; line-height: 1.1;">{{ $omUser->name ?? 'Order Manager' }}</div>
                <div style="font-size: 0.65rem; color: #0284c7; font-weight: 700; text-transform: uppercase;">Staff &bull; Logistics</div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Area -->
<main class="main-content">
    @include('components.toast')

    @yield('content')
</main>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.20/dist/sweetalert2.all.min.js"></script>

@include('components.staff-live-poller', ['endpoint' => route('order-manager.live-orders'), 'portal' => 'order_manager'])

@stack('scripts')
</body>
</html>
