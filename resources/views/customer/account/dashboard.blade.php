@extends('customer.account.layout')

@section('title', 'My Dashboard')

@section('account_content')

{{-- ======================================================== --}}
{{-- 🖥️ 1. DESKTOP DASHBOARD VIEW (Visible on >= 768px screens) --}}
{{-- ======================================================== --}}
<div class="d-none d-md-block">
    {{-- Desktop Welcome Banner Header --}}
    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4 position-relative" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); color: #ffffff;">
        <div class="position-absolute w-100 h-100"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 20px 20px; top: 0; left: 0; pointer-events: none;"></div>
        
        <div class="card-body p-4 position-relative">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bolder shadow flex-shrink-0" 
                        style="width: 48px; height: 48px; min-width: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.25rem; color: #fff; border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 4px 14px rgba(99,102,241,0.4);">
                        {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                            <h5 class="fw-bolder mb-0 text-white text-truncate" style="font-size: 1.25rem; letter-spacing: -0.02em;">Welcome back, {{ $user->name }}! 👋</h5>
                            <span class="badge rounded-pill px-2.5 py-0.5 fw-bold d-inline-flex align-items-center gap-1" 
                                  style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); font-size: 0.68rem;">
                                <i class="bi bi-patch-check-fill"></i> Verified Member
                            </span>
                        </div>
                        <p class="text-white-50 small mb-0 d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.76rem;">
                            <span><i class="bi bi-calendar3 me-1 opacity-75"></i>{{ date('d M, Y') }}</span>
                            <span>•</span>
                            <span><i class="bi bi-shield-check me-1 text-success opacity-75"></i>Account Secure</span>
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm shadow-xs text-center"
                       style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none; font-size: 0.8rem;">
                        <i class="bi bi-bag-plus me-1"></i> Browse Shop
                    </a>
                    <a href="{{ route('account.orders.index') }}" class="btn btn-outline-light rounded-pill px-3 py-1.5 fw-semibold btn-sm text-center"
                       style="font-size: 0.8rem; border-color: rgba(255,255,255,0.3);">
                        <i class="bi bi-box-seam me-1"></i> Orders
                    </a>
                    <a href="{{ route('account.wallet') }}" class="btn btn-success rounded-pill px-3 py-1.5 fw-semibold btn-sm text-center" 
                       style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; font-size: 0.8rem;">
                        <i class="bi bi-wallet2 me-1"></i> Wallet (₹{{ number_format($user->wallet?->balance ?? 0, 0) }})
                    </a>
                    <a href="{{ route('account.tickets') }}" class="btn btn-warning rounded-pill px-3 py-1.5 fw-semibold btn-sm text-dark text-center" 
                       style="background: #fbbf24; border: none; font-size: 0.8rem;">
                        <i class="bi bi-ticket-perforated me-1"></i> Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary KPI Cards --}}
    @include('customer.account.components.summary-cards', ['stats' => $stats])

    {{-- Main Dashboard Desktop Grid --}}
    <div class="row g-4">
        <div class="col-md-7 col-xl-8">
            @include('customer.account.components.recent-orders', ['recentOrders' => $recentOrders])
        </div>
        <div class="col-md-5 col-xl-4">
            @include('customer.account.components.profile-card', ['user' => $user])
            @include('customer.account.components.recent-wishlist', ['recentWishlistItems' => $recentWishlistItems])
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- 📱 2. SIMPLE, CLEAN, APP-LIKE MOBILE DASHBOARD (< 768px)  --}}
{{-- ======================================================== --}}
<div class="d-block d-md-none pb-4">
    {{-- Simple Mobile Header with Rich Vibrant Color & Spacious Layout --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 position-relative" 
         style="background: linear-gradient(135deg, #4338ca 0%, #6366f1 50%, #7c3aed 100%); color: #ffffff;">
        <div class="position-absolute w-100 h-100"
             style="background-image: radial-gradient(circle, rgba(255,255,255,0.12) 1px, transparent 1px); background-size: 16px 16px; top: 0; left: 0; pointer-events: none;"></div>
        
        <div class="card-body position-relative p-3 p-sm-4">
            {{-- Top Row: Avatar + Name + Edit Button (Clean, Strictly Fitted - Zero Overflow) --}}
            <div class="d-flex align-items-center justify-content-between gap-2 w-100 mb-3">
                <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1 overflow-hidden">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bolder shadow-sm flex-shrink-0"
                         style="width: 44px; height: 44px; min-width: 44px; background: #ffffff; color: #4338ca; font-size: 1.25rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                        {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-grow-1 overflow-hidden">
                        <div class="fw-bolder text-white text-truncate" style="font-size: 1.12rem; letter-spacing: -0.01em; line-height: 1.25;">
                            Hi, {{ $user->name }} 👋
                        </div>
                        <div class="text-white-50 small text-truncate" style="font-size: 0.74rem;">
                            {{ $user->mobile_number ?: $user->email }}
                        </div>
                    </div>
                </div>
                
                {{-- Edit Button: Guaranteed Visibility, No-wrap, Flex-shrink-0 --}}
                <a href="{{ route('profile.edit') }}" 
                   class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-white flex-shrink-0 text-nowrap d-inline-flex align-items-center gap-1 shadow-2xs" 
                   style="background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.4); backdrop-filter: blur(8px); font-size: 0.75rem;">
                    <i class="bi bi-pencil-square"></i>
                    <span>Edit</span>
                </a>
            </div>

            {{-- Bottom Micro Bar: Verified Status & Date / Security --}}
            <div class="pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" 
                 style="border-color: rgba(255,255,255,0.18) !important;">
                <span class="badge rounded-pill fw-semibold d-inline-flex align-items-center gap-1" 
                      style="background: rgba(16, 185, 129, 0.28); color: #a7f3d0; border: 1px solid rgba(167, 243, 208, 0.35); font-size: 0.68rem; padding: 0.28em 0.65em;">
                    <i class="bi bi-patch-check-fill"></i> Verified Member
                </span>
                <span class="text-white-50 small d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                    <i class="bi bi-shield-check opacity-75 text-light"></i>
                    <span>Account Secure</span>
                </span>
            </div>
        </div>
    </div>

    {{-- Spacious 4 Quick Action Grid --}}
    <div class="row g-3 mb-4">
        {{-- 1. Your Orders --}}
        <div class="col-6">
            <a href="{{ route('account.orders.index') }}" 
               class="card border-0 rounded-4 text-decoration-none mobile-action-box" 
               style="background: linear-gradient(145deg, #ede9fe 0%, #ddd6fe 100%); border: 1.5px solid #c4b5fd !important; padding: 1.25rem 1.1rem; min-height: 104px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.12);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                    @if($stats['orders'] > 0)
                        <span class="badge rounded-pill fw-bold font-monospace shadow-2xs" 
                              style="background: #4f46e5; color: #ffffff; font-size: 0.75rem; padding: 0.38em 0.7em;">
                            {{ $stats['orders'] }}
                        </span>
                    @endif
                </div>
                <div class="fw-bold" style="font-size: 0.96rem; color: #312e81; letter-spacing: -0.01em;">Your Orders</div>
            </a>
        </div>

        {{-- 2. Wishlist --}}
        <div class="col-6">
            <a href="{{ route('wishlist.index') }}" 
               class="card border-0 rounded-4 text-decoration-none mobile-action-box" 
               style="background: linear-gradient(145deg, #ffe4e6 0%, #fecdd3 100%); border: 1.5px solid #fda4af !important; padding: 1.25rem 1.1rem; min-height: 104px; box-shadow: 0 4px 14px rgba(225, 29, 72, 0.12);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 42px; height: 42px; background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%); color: #ffffff; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                    @if($stats['wishlist'] > 0)
                        <span class="badge rounded-pill fw-bold font-monospace shadow-2xs" 
                              style="background: #e11d48; color: #ffffff; font-size: 0.75rem; padding: 0.38em 0.7em;">
                            {{ $stats['wishlist'] }}
                        </span>
                    @endif
                </div>
                <div class="fw-bold" style="font-size: 0.96rem; color: #881337; letter-spacing: -0.01em;">Wishlist</div>
            </a>
        </div>

        {{-- 3. Cart --}}
        <div class="col-6">
            <a href="{{ route('cart.index') }}" 
               class="card border-0 rounded-4 text-decoration-none mobile-action-box" 
               style="background: linear-gradient(145deg, #d1fae5 0%, #a7f3d0 100%); border: 1.5px solid #6ee7b7 !important; padding: 1.25rem 1.1rem; min-height: 104px; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.12);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 42px; height: 42px; background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                    @if($stats['cart'] > 0)
                        <span class="badge rounded-pill fw-bold font-monospace shadow-2xs" 
                              style="background: #059669; color: #ffffff; font-size: 0.75rem; padding: 0.38em 0.7em;">
                            {{ $stats['cart'] }}
                        </span>
                    @endif
                </div>
                <div class="fw-bold" style="font-size: 0.96rem; color: #064e3b; letter-spacing: -0.01em;">Shopping Cart</div>
            </a>
        </div>

        {{-- 4. Help & Support --}}
        <div class="col-6">
            <a href="{{ route('account.tickets') }}" 
               class="card border-0 rounded-4 text-decoration-none mobile-action-box" 
               style="background: linear-gradient(145deg, #fef3c7 0%, #fde68a 100%); border: 1.5px solid #fcd34d !important; padding: 1.25rem 1.1rem; min-height: 104px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.12);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 42px; height: 42px; background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color: #ffffff; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);">
                        <i class="bi bi-headset"></i>
                    </div>
                    <span class="badge rounded-pill fw-bold shadow-2xs" 
                          style="background: #d97706; color: #ffffff; font-size: 0.7rem; padding: 0.38em 0.65em;">
                        24/7
                    </span>
                </div>
                <div class="fw-bold" style="font-size: 0.96rem; color: #78350f; letter-spacing: -0.01em;">Help Center</div>
            </a>
        </div>
    </div>

    {{-- Recent Order Snapshot (Clean & Strictly Fitted - Zero Overflow) --}}
    @if($recentOrders->isNotEmpty())
        @php
            $latestOrder = $recentOrders->first();
            $s = strtolower(str_replace([' ', '-'], '_', $latestOrder->status));
            $statusStyle = match($s) {
                'delivered'        => 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;',
                'out_for_delivery' => 'background:#fef3c7; color:#b45309; border:1px solid #fcd34d;',
                'shipped'          => 'background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe;',
                'packed', 'confirmed', 'processing' => 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
                'cancelled'        => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
                default            => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;'
            };
            $statusLabel = ucwords(str_replace('_', ' ', $latestOrder->status));
            $firstItem = $latestOrder->items->first();
            $product = $firstItem?->product;
        @endphp
        <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4 bg-white w-100" style="border: 1px solid #e2e8f0 !important;">
            {{-- Card Header: Title, Status Badge & View All Link --}}
            <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <span class="fw-bold text-dark" style="font-size: 0.9rem;">Latest Order</span>
                    <span class="badge rounded-pill fw-semibold" style="{{ $statusStyle }} font-size: 0.7rem; padding: 0.28em 0.65em;">
                        {{ $statusLabel }}
                    </span>
                </div>
                <a href="{{ route('account.orders.index') }}" class="small fw-semibold text-primary text-decoration-none flex-shrink-0" style="font-size: 0.8rem;">
                    All Orders ({{ $stats['orders'] }}) &rarr;
                </a>
            </div>

            <div class="card-body p-3.5">
                {{-- Product Thumbnail & Details Row: Perfectly Center-Aligned --}}
                <div class="d-flex align-items-center gap-3 mb-3 w-100 overflow-hidden">
                    <div class="flex-shrink-0">
                        @if($product && $product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $firstItem->product_name }}" 
                                 class="rounded-3 border p-1 bg-white" width="60" height="60" style="object-fit: contain;">
                        @else
                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                <i class="bi bi-box-seam opacity-50 fs-4"></i>
                            </div>
                        @endif
                    </div>
                    <div class="min-w-0 flex-grow-1 overflow-hidden">
                        {{-- 1. Product Title --}}
                        <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.92rem; line-height: 1.3;">
                            {{ $firstItem ? $firstItem->product_name : 'Order Items' }}
                        </div>
                        {{-- 2. Order # & Date Metadata --}}
                        <div class="text-muted small text-truncate mb-1" style="font-size: 0.74rem;">
                            <span>#{{ $latestOrder->order_number }}</span>
                            <span class="mx-1">•</span>
                            <span>{{ $latestOrder->created_at ? $latestOrder->created_at->format('d M, Y') : '—' }}</span>
                        </div>
                        {{-- 3. Price & Item Count --}}
                        <div class="d-flex align-items-baseline gap-1.5">
                            <span class="fw-bolder text-dark font-monospace" style="font-size: 1rem; color: #1e1b4b !important;">
                                ₹{{ number_format($latestOrder->total_amount, 2) }}
                            </span>
                            <span class="text-muted small" style="font-size: 0.72rem;">
                                ({{ $latestOrder->items->count() }} item{{ $latestOrder->items->count() !== 1 ? 's' : '' }})
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Button: Clean Pill with Icon & Chevron --}}
                <a href="{{ route('account.orders.show', $latestOrder) }}" 
                   class="btn btn-light border rounded-pill w-100 py-2.5 px-3 fw-semibold text-primary d-flex align-items-center justify-content-between shadow-2xs" 
                   style="font-size: 0.82rem; background: #f8fafc; border-color: #e2e8f0 !important;">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-primary"></i>
                        <span>Track Order & View Details</span>
                    </span>
                    <i class="bi bi-chevron-right text-muted small"></i>
                </a>
            </div>
        </div>
    @endif

    {{-- Clean Simple Account Menu (Single-line list - No over-texting) --}}
    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4 bg-white w-100" style="border: 1.5px solid #e2e8f0 !important;">
        <div class="list-group list-group-flush">
            {{-- Wallet --}}
            @php $walletBal = $user->wallet?->balance ?? 0; @endphp
            <a href="{{ route('account.wallet') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #d1fae5; color: #059669; font-size: 1.05rem;">
                         <i class="bi bi-wallet2"></i>
                    </div>
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">ShopCalm Wallet</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill bg-light text-success border fw-bold font-monospace" style="font-size: 0.76rem; padding: 0.35em 0.65em;">
                        ₹{{ number_format($walletBal, 2) }}
                    </span>
                    <i class="bi bi-chevron-right text-muted small" style="font-size: 0.75rem;"></i>
                </div>
            </a>

            {{-- Delivery Addresses --}}
            <a href="{{ route('account.addresses.index') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #cffafe; color: #0891b2; font-size: 1.05rem;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">Saved Addresses</span>
                </div>
                <i class="bi bi-chevron-right text-muted small" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Reviews --}}
            <a href="{{ route('account.reviews') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #fef3c7; color: #d97706; font-size: 1.05rem;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">My Reviews</span>
                </div>
                <i class="bi bi-chevron-right text-muted small" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Profile Settings --}}
            <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #ede9fe; color: #7c3aed; font-size: 1.05rem;">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">Profile Settings</span>
                </div>
                <i class="bi bi-chevron-right text-muted small" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Change Password --}}
            <a href="{{ route('account.change-password') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #f1f5f9; color: #475569; font-size: 1.05rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">Change Password</span>
                </div>
                <i class="bi bi-chevron-right text-muted small" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Customer Support --}}
            <a href="{{ route('page.contact') }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border-bottom text-decoration-none" style="border-color: #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #e0f2fe; color: #0284c7; font-size: 1.05rem;">
                        <i class="bi bi-chat-dots-fill"></i>
                    </div>
                    <span class="fw-semibold text-dark" style="font-size: 0.9rem;">Customer Support</span>
                </div>
                <i class="bi bi-chevron-right text-muted small" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Logout --}}
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" 
               class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 text-decoration-none text-danger">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 36px; height: 36px; background: #fee2e2; color: #dc2626; font-size: 1.05rem;">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <span class="fw-semibold text-danger" style="font-size: 0.9rem;">Sign Out</span>
                </div>
                <i class="bi bi-power small text-danger" style="font-size: 0.85rem;"></i>
            </a>
            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>

<style>
.mobile-action-box {
    transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
}
.mobile-action-box:active {
    transform: scale(0.97);
    filter: brightness(0.95);
}
</style>
@endsection

@section('full_width_account_content')
{{-- Recommended Products Section --}}
@include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
