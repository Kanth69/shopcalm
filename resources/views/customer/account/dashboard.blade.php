@extends('customer.account.layout')

@section('title', 'My Dashboard')

@section('account_content')

{{-- Welcome Banner Header --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow flex-shrink-0" 
                    style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.3rem; color: #fff; border: 3px solid rgba(255,255,255,0.2);">
                    {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-white">Welcome back, {{ $user->name }}! 👋</h4>
                    <p class="text-white-50 small mb-0">Here is what's happening with your ShopCalm account today ({{ date('F j, Y') }}).</p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold btn-sm shadow-sm">
                    <i class="bi bi-bag-plus me-1"></i> Browse Shop
                </a>
                <a href="{{ route('account.orders.index') }}" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold btn-sm">
                    <i class="bi bi-box-seam me-1"></i> My Orders
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Summary KPI Cards --}}
@include('customer.account.components.summary-cards', ['stats' => $stats])

<div class="row g-4">
    <div class="col-lg-8 col-md-12">
        {{-- Recent Orders Table --}}
        @include('customer.account.components.recent-orders', ['recentOrders' => $recentOrders])
    </div>
    <div class="col-lg-4 col-md-12">
        {{-- Profile Summary & Wishlist Preview --}}
        @include('customer.account.components.profile-card', ['user' => $user])
        @include('customer.account.components.recent-wishlist', ['recentWishlistItems' => $recentWishlistItems])
    </div>
@endsection

@section('full_width_account_content')
{{-- Recommended Products Full Container Width --}}
@include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
