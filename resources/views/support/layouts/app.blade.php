<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Customer Support') — ShopCalm</title>
    @php
        $supFavicon = \App\Models\Setting::get('favicon');
        $hasSupFav = $supFavicon && file_exists(storage_path('app/public/' . $supFavicon));
        $supFavUrl = $hasSupFav
            ? asset('storage/' . $supFavicon) . '?v=' . (@filemtime(storage_path('app/public/' . $supFavicon)) ?: time())
            : asset('favicon.png') . '?v=' . (@filemtime(public_path('favicon.png')) ?: '2');
    @endphp
    <link rel="icon" type="image/png" href="{{ $supFavUrl }}">
    <link rel="shortcut icon" type="image/png" href="{{ $supFavUrl }}">

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
            --support-primary: #8b5cf6;
            --support-primary-hover: #7c3aed;
            --support-sidebar-bg: #120e26;
            --support-sidebar-hover: rgba(255, 255, 255, 0.07);
            --support-sidebar-active: #8b5cf6;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            padding: 0;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.05);
            background: var(--support-sidebar-bg);
            width: 260px;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        .sidebar-brand .brand-logo {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, #8b5cf6, #c084fc);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 800;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
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
            color: #8b7ca6;
            padding: 0.75rem 0.85rem 0.35rem 0.85rem;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #a497bd;
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
            color: #f8fafc;
            background: var(--support-sidebar-hover);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: var(--support-primary);
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(139, 92, 246, 0.4);
        }

        .sidebar .nav-badge {
            margin-left: auto;
            font-size: 0.68rem;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-weight: 700;
        }

        /* Top Navbar */
        .top-navbar {
            margin-left: 260px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: calc(100vh - 65px);
        }

        .card {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.03);
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
            background: #f5f3ff;
            color: #7c3aed;
            border-color: #ddd6fe;
        }
        .action-btn-view:hover {
            background: #ede9fe;
            color: #6d28d9;
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
    $unreadEnquiriesCount = \App\Models\ContactEnquiry::where('status', 'unread')->count();
@endphp

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-logo">
                <i class="bi bi-headset"></i>
            </div>
            <div>
                <div class="brand-text">ShopCalm</div>
                <div class="text-white-50" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em;">Customer Support</div>
            </div>
        </div>
        <span class="badge bg-purple bg-opacity-20 text-light border border-light border-opacity-25" style="font-size: 0.65rem; background: rgba(168, 85, 247, 0.25);">Helpdesk</span>
    </div>

    <div class="sidebar-nav-container">
        <ul class="nav flex-column">
            <li class="nav-header">Helpdesk Overview</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('support.dashboard') ? 'active' : '' }}" href="{{ route('support.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-header">Customer Inquiries</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('support.enquiries.index') && !request()->filled('status') ? 'active' : '' }}" href="{{ route('support.enquiries.index') }}">
                    <i class="bi bi-envelope"></i> All Inquiries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->get('status') === 'unread' ? 'active' : '' }}" href="{{ route('support.enquiries.index', ['status' => 'unread']) }}">
                    <i class="bi bi-envelope-exclamation"></i> Unread / Open
                    @if($unreadEnquiriesCount > 0)
                        <span class="nav-badge bg-danger text-white">{{ $unreadEnquiriesCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->get('status') === 'in_progress' ? 'active' : '' }}" href="{{ route('support.enquiries.index', ['status' => 'in_progress']) }}">
                    <i class="bi bi-hourglass-split"></i> In Progress
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->get('status') === 'resolved' ? 'active' : '' }}" href="{{ route('support.enquiries.index', ['status' => 'resolved']) }}">
                    <i class="bi bi-check-circle"></i> Resolved
                </a>
            </li>

            <li class="nav-header">Audience & Directory</li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('support.customers.*') ? 'active' : '' }}" href="{{ route('support.customers.index') }}">
                    <i class="bi bi-people"></i> Customer 360° Directory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('support.subscribers.*') ? 'active' : '' }}" href="{{ route('support.subscribers.index') }}">
                    <i class="bi bi-envelope-paper-heart"></i> Newsletter Subscribers
                </a>
            </li>
        </ul>
    </div>

    <!-- User Profile & Logout -->
    <div class="p-3 border-top border-white border-opacity-10">
        @php $supUser = Auth::guard('support')->user() ?? Auth::guard('admin')->user(); @endphp
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.8rem; background: #8b5cf6; color: #ffffff;">
                    {{ strtoupper(substr($supUser->name ?? 'S', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate" style="font-size: 0.8rem;">{{ $supUser->name ?? 'Support Agent' }}</div>
                    <div class="text-white-50 small" style="font-size: 0.68rem;">Customer Helpdesk</div>
                </div>
            </div>
            <form action="{{ route('support.logout') }}" method="POST" class="m-0">
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
        <button class="btn btn-sm btn-light d-lg-none" type="button" onclick="document.querySelector('.sidebar').classList.toggle('show')">
            <i class="bi bi-list fs-5"></i>
        </button>
        <div>
            <h5 class="mb-0 fw-bold text-dark" style="letter-spacing: -0.3px;">@yield('header', 'Customer Support Hub')</h5>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2.5">
        @yield('actions')

        <a href="{{ route('shop') }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.8rem;">
            <i class="bi bi-box-arrow-up-right me-1"></i> Live Store
        </a>

        @php $supUser = Auth::guard('support')->user() ?? Auth::guard('admin')->user(); @endphp
        <div class="d-flex align-items-center gap-2 px-3 py-1 rounded-3 ms-1" style="background: #f1f5f9; border: 1px solid #e2e8f0;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #a855f7, #c084fc); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: #fff; flex-shrink: 0;">
                {{ strtoupper(substr($supUser->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #0f172a; line-height: 1.1;">{{ $supUser->name ?? 'Support Agent' }}</div>
                <div style="font-size: 0.65rem; color: #a855f7; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Staff &bull; Customer Support</div>
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
