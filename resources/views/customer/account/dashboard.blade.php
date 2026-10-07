@extends('customer.account.layout')

@section('title', 'My Dashboard')

@section('account_content')

{{-- 1. Welcome Banner Header (Spacious & Modern) --}}
<div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3 mb-md-4 position-relative" 
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); color: #ffffff;">
    <div class="position-absolute w-100 h-100"
         style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 20px 20px; top: 0; left: 0; pointer-events: none;"></div>
    
    <div class="card-body p-3 p-md-4 position-relative">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2.5">
            <div class="d-flex align-items-center gap-2.5 gap-md-3 min-w-0">
                {{-- Glowing Avatar Sphere --}}
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bolder shadow flex-shrink-0" 
                    style="width: 46px; height: 46px; min-width: 46px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.25rem; color: #fff; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 4px 14px rgba(99,102,241,0.4);">
                    {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                        <h5 class="fw-bolder mb-0 text-white text-truncate" style="font-size: clamp(1rem, 3.5vw, 1.25rem); letter-spacing: -0.02em;">Welcome back, {{ $user->name }}! 👋</h5>
                        <span class="badge rounded-pill px-2 py-0.5 fw-bold d-inline-flex align-items-center gap-1" 
                              style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); font-size: 0.68rem;">
                            <i class="bi bi-patch-check-fill"></i> Verified
                        </span>
                    </div>
                    <p class="text-white-50 small mb-0 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.76rem;">
                        <span><i class="bi bi-calendar3 me-1 opacity-75"></i>{{ date('d M, Y') }}</span>
                        <span>•</span>
                        <span><i class="bi bi-shield-check me-1 text-success opacity-75"></i>Account Secure</span>
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0 w-100 w-sm-auto justify-content-start justify-content-sm-end">
                <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm shadow-xs flex-fill flex-sm-grow-0 text-center"
                   style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; font-size: 0.78rem;">
                    <i class="bi bi-bag-plus me-1"></i> Browse Shop
                </a>
                <a href="{{ route('account.orders.index') }}" class="btn btn-outline-light rounded-pill px-3 py-1.5 fw-semibold btn-sm flex-fill flex-sm-grow-0 text-center"
                   style="font-size: 0.78rem; border-color: rgba(255,255,255,0.3);">
                    <i class="bi bi-box-seam me-1"></i> Orders
                </a>
                <a href="{{ url('account/wallet') }}" class="btn btn-success rounded-pill px-3 py-1.5 fw-semibold btn-sm text-center" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; font-size: 0.78rem;">
                    <i class="bi bi-wallet2 me-1"></i> Wallet & Referral
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. Summary KPI Cards --}}
@include('customer.account.components.summary-cards', ['stats' => $stats])

{{-- 3. Main Dashboard Grid --}}
<div class="row g-3 g-md-4">
    <div class="col-12 col-md-7 col-xl-8">
        {{-- Recent Orders Table --}}
        @include('customer.account.components.recent-orders', ['recentOrders' => $recentOrders])
    </div>
    <div class="col-12 col-md-5 col-xl-4">
        {{-- Profile Summary & Wishlist Preview --}}
        @include('customer.account.components.profile-card', ['user' => $user])
        @include('customer.account.components.recent-wishlist', ['recentWishlistItems' => $recentWishlistItems])
    </div>
</div>
    <style>
        /* Mobile spacing improvements */
        @media (max-width: 576px) {
            .row.g-3.g-md-4 {
                gap: 0.5rem !important;
            }
            .row.g-3.g-md-4 > .col-12 {
                padding: 0.5rem;
            }
            .card {
                padding: 0.75rem;
            }
            .card-body {
                padding: 0.75rem;
            }
            .btn {
                font-size: 0.75rem;
                padding: 0.4rem 0.8rem;
            }
        }
    </style>
@endsection

@section('full_width_account_content')
{{-- Recommended Products Section --}}
@include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
