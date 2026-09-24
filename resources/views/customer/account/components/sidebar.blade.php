@php
    $currentUser = Auth::user();
    $ordersCount = $currentUser ? $currentUser->orders()->count() : 0;
    $wishlistCount = $currentUser && method_exists($currentUser, 'wishlists') ? $currentUser->wishlists()->count() : 0;
@endphp

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-3.5 p-md-4">
        
        {{-- User Mini Profile Card --}}
        @if($currentUser)
        <div class="p-3 mb-4 rounded-4 d-flex align-items-center" style="gap: 1rem; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe;">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bolder shadow-xs flex-shrink-0"
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.25rem; border: 2.5px solid #ffffff; box-shadow: 0 4px 12px rgba(99,102,241,0.25);">
                {{ strtoupper(substr($currentUser->name ?? 'C', 0, 1)) }}
            </div>
            <div class="overflow-hidden flex-grow-1">
                <div class="fw-bolder text-dark text-truncate mb-0.5" style="font-size: 0.94rem; letter-spacing: -0.01em;">{{ $currentUser->name }}</div>
                <div class="text-muted small text-truncate" style="font-size: 0.76rem;">{{ $currentUser->email }}</div>
            </div>
        </div>
        @endif

        {{-- Section 1: Orders & Activity --}}
        <div class="px-2 pt-1 pb-2.5">
            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.08em;">Orders & Shopping</span>
        </div>

        <nav class="nav flex-column mb-4" style="gap: 0.5rem !important;">
            {{-- Dashboard --}}
            @php $isDash = request()->routeIs('dashboard'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isDash ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('dashboard') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isDash ? 'menu-icon-active' : '' }}" style="color: #6366f1; background: {{ $isDash ? 'rgba(255,255,255,0.22)' : '#ede9fe' }};">
                        <i class="bi bi-speedometer2"></i>
                    </div>
                    <span>Dashboard</span>
                </div>
                <i class="bi bi-chevron-right small {{ $isDash ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
            </a>

            {{-- My Orders --}}
            @php $isOrders = request()->routeIs('account.orders.*') || request()->is('account/orders*'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isOrders ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('account.orders.index') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isOrders ? 'menu-icon-active' : '' }}" style="color: #3b82f6; background: {{ $isOrders ? 'rgba(255,255,255,0.22)' : '#dbeafe' }};">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <span>My Orders</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                    @if($ordersCount > 0)
                        <span class="badge rounded-pill {{ $isOrders ? 'bg-white text-primary' : 'bg-light text-secondary border' }}" style="font-size: 0.7rem; padding: 0.3em 0.65em;">{{ $ordersCount }}</span>
                    @endif
                    <i class="bi bi-chevron-right small {{ $isOrders ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
                </div>
            </a>

            {{-- Wishlist --}}
            @php $isWish = request()->routeIs('wishlist.index') || request()->is('wishlist*'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isWish ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('wishlist.index') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isWish ? 'menu-icon-active' : '' }}" style="color: #ec4899; background: {{ $isWish ? 'rgba(255,255,255,0.22)' : '#fce7f3' }};">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                    <span>Wishlist</span>
                </div>
                <i class="bi bi-chevron-right small {{ $isWish ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
            </a>

            {{-- My Wallet & Referrals --}}
            @php $isWallet = request()->routeIs('account.wallet*'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isWallet ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('account.wallet') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isWallet ? 'menu-icon-active' : '' }}" style="color: #10b981; background: {{ $isWallet ? 'rgba(255,255,255,0.22)' : '#d1fae5' }};">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <span>My Wallet & Referrals</span>
                </div>
                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                    @php $userWalletBalance = $currentUser?->wallet?->balance ?? 0; @endphp
                    @if($userWalletBalance > 0)
                        <span class="badge rounded-pill {{ $isWallet ? 'bg-white text-success' : 'bg-success text-white' }}" style="font-size: 0.7rem; padding: 0.3em 0.65em;">₹{{ number_format($userWalletBalance, 0) }}</span>
                    @endif
                    <i class="bi bi-chevron-right small {{ $isWallet ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
                </div>
            </a>

            {{-- My Reviews --}}
            @php $isRev = request()->routeIs('account.reviews'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isRev ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('account.reviews') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isRev ? 'menu-icon-active' : '' }}" style="color: #f59e0b; background: {{ $isRev ? 'rgba(255,255,255,0.22)' : '#fef3c7' }};">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <span>My Reviews</span>
                </div>
                <i class="bi bi-chevron-right small {{ $isRev ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
            </a>
        </nav>

        {{-- Section 2: Account Settings --}}
        <div class="px-2 pt-3 pb-2.5 border-top">
            <span class="text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.08em;">Account & Settings</span>
        </div>

        <nav class="nav flex-column" style="gap: 0.5rem !important;">
            {{-- Saved Addresses --}}
            @php $isAddr = request()->routeIs('account.addresses.*'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isAddr ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('account.addresses.index') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isAddr ? 'menu-icon-active' : '' }}" style="color: #06b6d4; background: {{ $isAddr ? 'rgba(255,255,255,0.22)' : '#cffafe' }};">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <span>Saved Addresses</span>
                </div>
                <i class="bi bi-chevron-right small {{ $isAddr ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
            </a>

            {{-- My Profile --}}
            @php $isProf = request()->routeIs('profile.edit') || request()->is('profile*'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isProf ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('profile.edit') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isProf ? 'menu-icon-active' : '' }}" style="color: #10b981; background: {{ $isProf ? 'rgba(255,255,255,0.22)' : '#d1fae5' }};">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <span>Personal Profile</span>
                </div>
                <i class="bi bi-chevron-right small {{ $isProf ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Change Password --}}
            @php $isPass = request()->routeIs('account.change-password'); @endphp
            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold {{ $isPass ? 'active-menu-pill text-white' : 'text-dark hover-menu-item' }}" 
               href="{{ route('account.change-password') }}" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box {{ $isPass ? 'menu-icon-active' : '' }}" style="color: #8b5cf6; background: {{ $isPass ? 'rgba(255,255,255,0.22)' : '#ede9fe' }};">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <span>Change Password</span>
                </div>
                <i class="bi bi-chevron-right small {{ $isPass ? 'text-white' : 'text-muted opacity-50' }}" style="font-size: 0.75rem;"></i>
            </a>

            {{-- Logout Action --}}
            <div class="my-2.5 border-bottom"></div>

            <a class="nav-link rounded-3 px-3 py-2.5 d-flex align-items-center justify-content-between text-decoration-none fw-semibold text-danger hover-danger-pill" 
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();" style="font-size: 0.88rem;">
                <div class="d-flex align-items-center" style="gap: 1rem !important;">
                    <div class="menu-icon-box" style="color: #ef4444; background: #fee2e2;">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <span>Sign Out</span>
                </div>
                <i class="bi bi-power small text-danger" style="font-size: 0.85rem;"></i>
            </a>
            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </nav>
    </div>
</div>

{{-- Lower Support & Guarantees Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-4 text-center">
        <div class="d-inline-flex align-items-center justify-content-center mb-2.5 rounded-circle text-white shadow-xs" 
            style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
            <i class="bi bi-headset fs-5"></i>
        </div>
        <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem;">Need Assistance?</h6>
        <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.5;">Have questions about an order or delivery? Our support team is here to help 24/7!</p>
        <a href="{{ route('page.contact') }}" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold w-100 shadow-sm" style="font-size: 0.82rem;">
            <i class="bi bi-chat-dots me-1.5"></i> Contact Support
        </a>
    </div>
    <div class="card-footer bg-white border-top py-2.5 px-3">
        <div class="d-flex align-items-center justify-content-around text-muted small" style="font-size: 0.72rem;">
            <span title="Fast Shipping"><i class="bi bi-truck text-primary me-1"></i> Fast Dispatch</span>
            <span>•</span>
            <span title="100% Secure"><i class="bi bi-shield-check text-success me-1"></i> 100% Secure</span>
        </div>
    </div>
</div>

<style>
.menu-icon-box {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
    transition: all 0.2s ease;
}
.menu-icon-active {
    color: #ffffff !important;
}
.active-menu-pill {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35) !important;
}
.hover-menu-item {
    transition: all 0.15s ease;
}
.hover-menu-item:hover {
    background-color: #f8fafc !important;
    color: #4f46e5 !important;
    transform: translateX(4px);
}
.hover-danger-pill {
    transition: all 0.15s ease;
}
.hover-danger-pill:hover {
    background-color: #fef2f2 !important;
    transform: translateX(4px);
}
</style>
