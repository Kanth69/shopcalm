@if(!request()->routeIs('checkout.*'))
<!-- ============================================================ -->
<!-- 📱 APP-LIKE STICKY MOBILE BOTTOM NAVIGATION BAR -->
<!-- ============================================================ -->
<nav class="mobile-bottom-nav d-block d-md-none position-fixed bottom-0 start-0 end-0 bg-white border-top shadow-lg" style="z-index: 1040; height: 60px; backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.96) !important;">
    <div class="container-fluid h-100 px-2">
        <div class="row h-100 g-0 align-items-center text-center">
            {{-- 1. Home --}}
            <div class="col">
                <a href="{{ route('home') }}" class="mobile-nav-item d-flex flex-column align-items-center justify-content-center text-decoration-none py-1 {{ request()->routeIs('home') ? 'active text-primary' : 'text-secondary' }}">
                    <i class="bi {{ request()->routeIs('home') ? 'bi-house-door-fill' : 'bi-house-door' }} fs-5"></i>
                    <span class="small fw-semibold" style="font-size: 0.68rem; margin-top: 1px;">Home</span>
                </a>
            </div>

            {{-- 2. Categories --}}
            <div class="col">
                <a href="{{ route('categories.index') }}" class="mobile-nav-item d-flex flex-column align-items-center justify-content-center text-decoration-none py-1 {{ request()->routeIs('categories.*') || request()->routeIs('category.*') ? 'active text-primary' : 'text-secondary' }}">
                    <i class="bi {{ request()->routeIs('categories.*') || request()->routeIs('category.*') ? 'bi-grid-fill' : 'bi-grid' }} fs-5"></i>
                    <span class="small fw-semibold" style="font-size: 0.68rem; margin-top: 1px;">Categories</span>
                </a>
            </div>

            {{-- 3. Offers / Deals --}}
            <div class="col">
                <a href="{{ route('offers.index') }}" class="mobile-nav-item d-flex flex-column align-items-center justify-content-center text-decoration-none py-1 {{ request()->routeIs('offers.*') ? 'active text-primary' : 'text-secondary' }}">
                    <i class="bi {{ request()->routeIs('offers.*') ? 'bi-tag-fill' : 'bi-tag' }} fs-5"></i>
                    <span class="small fw-semibold" style="font-size: 0.68rem; margin-top: 1px;">Deals</span>
                </a>
            </div>

            {{-- 4. Cart with Badge --}}
            <div class="col">
                <a href="{{ route('cart.index') }}" class="mobile-nav-item d-flex flex-column align-items-center justify-content-center text-decoration-none py-1 position-relative {{ request()->routeIs('cart.*') ? 'active text-primary' : 'text-secondary' }}">
                    <div class="position-relative d-inline-block">
                        <i class="bi {{ request()->routeIs('cart.*') ? 'bi-bag-fill' : 'bi-bag' }} fs-5"></i>
                        <span id="mobile-cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge-count" 
                              style="font-size: 8px; min-width: 15px; height: 15px; padding: 1px 3px; display: {{ (isset($cartItemCount) && $cartItemCount > 0) ? 'inline-flex' : 'none' }}; align-items: center; justify-content: center;">
                            {{ $cartItemCount ?? 0 }}
                        </span>
                    </div>
                    <span class="small fw-semibold" style="font-size: 0.68rem; margin-top: 1px;">Cart</span>
                </a>
            </div>

            {{-- 5. Account / Profile --}}
            <div class="col">
                @auth('customer')
                    <a href="{{ route('dashboard') }}" class="mobile-nav-item d-flex flex-column align-items-center justify-content-center text-decoration-none py-1 {{ request()->routeIs('dashboard') || request()->routeIs('account.*') || request()->routeIs('profile.*') ? 'active text-primary' : 'text-secondary' }}">
                        <i class="bi {{ request()->routeIs('dashboard*') || request()->routeIs('account.*') ? 'bi-person-fill' : 'bi-person' }} fs-5"></i>
                        <span class="small fw-semibold" style="font-size: 0.68rem; margin-top: 1px;">Account</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="mobile-nav-item d-flex flex-column align-items-center justify-content-center text-decoration-none py-1 {{ request()->routeIs('login') ? 'active text-primary' : 'text-secondary' }}">
                        <i class="bi bi-person-lock fs-5"></i>
                        <span class="small fw-semibold" style="font-size: 0.68rem; margin-top: 1px;">Sign In</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>
@endif

<style>
.mobile-nav-item {
    transition: transform 0.15s ease, color 0.15s ease;
}
.mobile-nav-item:active {
    transform: scale(0.92);
}
.mobile-nav-item.active {
    color: #1e40af !important;
}
@media (max-width: 767.98px) {
    body {
        padding-bottom: 68px !important;
    }
}
</style>
