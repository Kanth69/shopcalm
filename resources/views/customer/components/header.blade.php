@php
    $liveMegaSale = app(\App\Services\OfferService::class)->getLiveMegaSale();
    $isCategoriesPage = request()->routeIs('categories.index');
    $isBrandsPage = request()->routeIs('brands.index');
    $isCheckoutFlow = request()->routeIs('checkout.*') || request()->is('checkout*') || request()->is('order-success*');

    $headerSearchPlaceholder = 'Search products, brands, categories, tags...';
    $headerSearchAction = route('shop');
    if ($isCategoriesPage) {
        $headerSearchPlaceholder = 'Search categories...';
        $headerSearchAction = route('categories.index');
    } elseif ($isBrandsPage) {
        $headerSearchPlaceholder = 'Search brands...';
        $headerSearchAction = route('brands.index');
    }
@endphp

@if($liveMegaSale)
<div class="py-2 text-white text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, {{ $liveMegaSale->theme_color ?? '#6366f1' }} 0%, #0f172a 100%); font-size: 0.88rem; z-index: 1030;">
    <div class="container d-flex align-items-center justify-content-center flex-wrap gap-2">
        <span class="badge bg-warning text-dark fw-bold text-uppercase px-2.5 py-1 rounded-pill me-1">
            {{ $liveMegaSale->badge_text ?? '🔥 SALE' }}
        </span>
        <strong class="fw-bold">{{ $liveMegaSale->title }} IS LIVE!</strong>
        <span class="d-none d-md-inline text-white-50">{{ $liveMegaSale->description ?? 'Special discounts across all categories' }}</span>
        
        @if($liveMegaSale->end_time)
            <div class="d-inline-flex align-items-center bg-black bg-opacity-40 text-white px-2.5 py-0.5 rounded-pill font-monospace small ms-md-2">
                <i class="bi bi-clock me-1 text-warning"></i>
                <span id="mega-sale-timer" class="text-warning fw-bold" data-endtime="{{ $liveMegaSale->end_time->toISOString() }}">Ends Soon</span>
            </div>
        @endif
        
        <a href="{{ route('offers.index') }}" class="btn btn-sm btn-light text-dark fw-bold rounded-pill px-3 py-0.5 ms-2 text-decoration-none" style="font-size: 0.8rem;">
            Shop Deals &rarr;
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timerEl = document.getElementById('mega-sale-timer');
    if (!timerEl) return;

    const endTime = new Date(timerEl.dataset.endtime).getTime();

    function updateTimer() {
        const now = new Date().getTime();
        const diff = endTime - now;

        if (diff <= 0) {
            timerEl.textContent = "Ends Soon";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        let str = "";
        if (days > 0) str += `${days}d : `;
        str += `${String(hours).padStart(2, '0')}h : ${String(minutes).padStart(2, '0')}m : ${String(seconds).padStart(2, '0')}s`;
        timerEl.textContent = str;
    }

    updateTimer();
    setInterval(updateTimer, 1000);
});
</script>
@endif

<!-- Clean Minimalist Header -->
<header class="header-main bg-white border-bottom sticky-top py-2" style="z-index: 1040;">
    <div class="container px-2 px-sm-3 px-md-4">
        {{-- Main Navigation Row --}}
        <div class="d-flex align-items-center justify-content-between gap-1 gap-md-4 py-1">

            <!-- Left: Menu Trigger + Logo + Nav Links -->
            <div class="d-flex align-items-center gap-1.5 gap-sm-2 gap-lg-4 flex-shrink-0">
                <!-- Simple Clean Menu Button -->
                <button class="btn btn-light border-0 rounded-circle p-0 d-flex align-items-center justify-content-center text-dark menu-trigger-btn" 
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#siteMenu" aria-controls="siteMenu" title="Open Menu" style="width: 36px; height: 36px;">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center flex-shrink-0">
                    <x-logo height="28" />
                </a>

                <!-- Desktop Primary Links -->
                <nav class="d-none d-md-flex align-items-center gap-2.5 gap-lg-4 ms-1 ms-lg-2">
                    <a href="{{ route('home') }}" class="nav-clean-link {{ request()->routeIs('home') ? 'active text-primary fw-bold' : 'text-dark' }}" style="font-size: 0.88rem;">Home</a>
                    <a href="{{ route('shop') }}" class="nav-clean-link {{ request()->routeIs('shop') ? 'active text-primary fw-bold' : 'text-dark' }}" style="font-size: 0.88rem;">Shop</a>
                    <a href="{{ route('categories.index') }}" class="nav-clean-link {{ request()->routeIs('categories.*') ? 'active text-primary fw-bold' : 'text-dark' }}" style="font-size: 0.88rem;">Categories</a>
                    <a href="{{ route('offers.index') }}" class="nav-clean-link {{ request()->routeIs('offers.*') ? 'active text-primary fw-bold' : 'text-dark' }}" style="font-size: 0.88rem;">Offers</a>
                </nav>

                @php
                    $deliveryLocation = app(\App\Services\DeliveryService::class)->getSessionLocation();
                @endphp
                <!-- Desktop Delivery Location Selector Pill -->
                <button type="button" class="btn btn-light bg-transparent border-0 d-none d-md-flex align-items-center text-start p-1.5 rounded-3 header-location-btn flex-shrink-0" 
                        data-bs-toggle="modal" data-bs-target="#deliveryLocationModal" style="cursor: pointer; gap: 0.5rem !important;" title="Select delivery location">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                         style="width: 34px; height: 34px; background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="d-flex flex-column" style="line-height: 1.15;">
                        <span class="text-muted small" style="font-size: 0.7rem;">Deliver to</span>
                        <span class="fw-bold text-dark text-truncate" id="header-delivery-text" style="max-width: 140px; font-size: 0.82rem;">
                            {{ $deliveryLocation['location_text'] ?? 'Select Pincode' }}
                        </span>
                    </div>
                </button>
            </div>

            @if(!$isCheckoutFlow)
            <!-- Center: Desktop Clean Search Bar (App-Style Live Search) -->
            <div class="flex-grow-1 d-none d-md-block mx-2 mx-xl-4 position-relative" style="max-width: 520px;">
                <form action="{{ $headerSearchAction }}" method="GET" class="position-relative" id="mainSearchForm" autocomplete="off">
                    <div class="input-group rounded-pill overflow-hidden border shadow-xs search-input-group position-relative" style="background: #f8fafc; border-color: #cbd5e1 !important; transition: border-color 0.2s ease, box-shadow 0.2s ease;">
                        <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                            <i class="bi bi-search text-primary" style="font-size: 0.9rem;"></i>
                        </span>
                        <input
                            type="text"
                            name="q"
                            id="mainSearchInput"
                            class="form-control bg-transparent border-0 shadow-none ps-2 py-2"
                            placeholder="{{ $headerSearchPlaceholder }}"
                            value="{{ request('q') }}"
                            style="font-size: 0.88rem;"
                            autocomplete="off"
                        >
                        <button type="button" class="btn btn-link text-muted p-0 pe-3 d-none btn-clear-search" aria-label="Clear search" style="text-decoration: none;">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <!-- Live Suggestions Dropdown -->
                    <div id="searchSuggestions" class="position-absolute bg-white border rounded-4 shadow-lg mt-2 d-none overflow-hidden search-suggestions-box" 
                         style="z-index: 1060; top: 100%; left: 0; right: 0; max-height: 540px; overflow-y: auto;">
                    </div>
                </form>
            </div>
            @else
            <!-- Checkout Header Badge (Distraction-Free) -->
            <div class="flex-grow-1 d-none d-md-flex align-items-center justify-content-center text-muted">
                <span class="d-inline-flex align-items-center gap-1.5 px-3 py-1 bg-light rounded-pill border text-secondary fw-semibold" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                    <i class="bi bi-shield-check text-success fs-6"></i> 100% SECURE ENCRYPTED CHECKOUT
                </span>
            </div>
            @endif

            <!-- Right Actions: Wishlist + Cart + Profile Avatar -->
            <div class="d-flex align-items-center gap-1.5 gap-md-3 gap-lg-4 flex-shrink-0">

                <!-- Mobile Location Quick Pill -->
                <button type="button" class="btn btn-sm btn-light border-0 d-flex d-md-none align-items-center gap-1 px-2 py-1 rounded-pill text-primary fw-semibold flex-shrink-0" 
                        data-bs-toggle="modal" data-bs-target="#deliveryLocationModal" style="font-size: 0.72rem; background: rgba(99, 102, 241, 0.08);">
                    <i class="bi bi-geo-alt-fill text-primary" style="font-size: 0.76rem;"></i>
                    <span class="text-truncate" id="header-mobile-delivery-text" style="max-width: 72px;">{{ $deliveryLocation['location_text'] ?? 'Pincode' }}</span>
                </button>

                <!-- Desktop Wishlist Icon -->
                @auth('customer')
                    <a href="{{ route('wishlist.index') }}"
                       class="text-dark text-decoration-none position-relative p-2 header-icon-link d-none d-md-flex align-items-center justify-content-center"
                       title="Wishlist">
                        <i class="bi bi-heart fs-5"></i>
                        @php
                            $wCount = isset($wishlistedProductIds) ? count($wishlistedProductIds) : 0;
                        @endphp
                        <span id="wishlist-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger wishlist-badge-count" 
                              style="font-size: 9px; min-width: 17px; padding: 2px 4px; display: {{ $wCount > 0 ? 'inline-block' : 'none' }};">
                            {{ $wCount }}
                        </span>
                    </a>
                @endauth

                <!-- Desktop Cart Icon -->
                <a href="{{ route('cart.index') }}"
                   class="text-dark text-decoration-none position-relative p-2 header-icon-link d-none d-md-flex align-items-center justify-content-center"
                   title="Shopping Cart">
                    <i class="bi bi-bag fs-5"></i>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary cart-badge-count" 
                          style="font-size: 9px; min-width: 17px; padding: 2px 4px; display: {{ (isset($cartItemCount) && $cartItemCount > 0) ? 'inline-block' : 'none' }};">
                        {{ $cartItemCount ?? 0 }}
                    </span>
                </a>

                <!-- Profile Avatar Dropdown / Sign In Button -->
                <div class="ps-0 ps-md-3 border-start-0 border-md-start border-light-subtle flex-shrink-0">
                    @auth('customer')
                        @php
                            $userObj = Auth::guard('customer')->user();
                            $firstInitial = strtoupper(substr($userObj->name ?? 'C', 0, 1));
                        @endphp
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center gap-1 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs avatar-circle flex-shrink-0" 
                                     style="width: 34px; height: 34px; background: #0d6efd; font-size: 0.88rem;">
                                    {{ $firstInitial }}
                                </div>
                                <i class="bi bi-chevron-down text-muted small d-none d-sm-inline" style="font-size: 0.68rem;"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2 mt-2" style="min-width: 220px;">
                                <div class="px-3 py-2 border-bottom mb-2">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">{{ $userObj->name }}</div>
                                    <div class="text-muted text-truncate small" style="font-size: 0.75rem;">{{ $userObj->email }}</div>
                                </div>
                                <a class="dropdown-item py-2 px-3 rounded-2 text-dark" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
                                </a>
                                <a class="dropdown-item py-2 px-3 rounded-2 text-dark" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2 text-success"></i> My Profile
                                </a>
                                <a class="dropdown-item py-2 px-3 rounded-2 text-dark" href="{{ route('account.orders.index') }}">
                                    <i class="bi bi-box-seam me-2 text-info"></i> My Orders
                                </a>
                                <a class="dropdown-item py-2 px-3 rounded-2 text-dark" href="{{ route('account.tickets') }}">
                                    <i class="bi bi-ticket-perforated me-2 text-warning"></i> My Tickets
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 px-3 rounded-2 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- Desktop: Pill with text & icon --}}
                        <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-none d-sm-inline-flex align-items-center gap-1.5 shadow-2xs" style="font-size: 0.82rem;">
                            <i class="bi bi-person fs-6"></i> <span>Sign In</span>
                        </a>
                        {{-- Mobile: Perfectly aligned 34px circular button --}}
                        <a href="{{ route('login') }}" class="btn btn-light rounded-circle p-0 d-inline-flex d-sm-none align-items-center justify-content-center text-primary border shadow-2xs flex-shrink-0" 
                           style="width: 34px; height: 34px; background: rgba(99, 102, 241, 0.08); border-color: rgba(99, 102, 241, 0.25) !important;" 
                           title="Sign In">
                            <i class="bi bi-person fs-5"></i>
                        </a>
                    @endauth
                </div>

            </div>
        </div>

        @if(!$isCheckoutFlow)
        <!-- Mobile Search Bar (Clean Full Width App-Style Live Search) -->
        <div class="d-block d-md-none pt-1.5 pb-0.5 position-relative">
            <form action="{{ $headerSearchAction }}" method="GET" class="position-relative" id="mobileSearchForm" autocomplete="off">
                <div class="input-group rounded-pill overflow-hidden border shadow-xs search-input-group position-relative" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                    <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                        <i class="bi bi-search text-primary" style="font-size: 0.85rem;"></i>
                    </span>
                    <input type="text" name="q" id="mobileSearchInput" class="form-control bg-transparent border-0 shadow-none ps-1 py-2" placeholder="{{ $headerSearchPlaceholder }}" value="{{ request('q') }}" style="font-size: 0.86rem;" autocomplete="off">
                    <button type="button" class="btn btn-link text-muted p-0 pe-3 d-none btn-clear-search" aria-label="Clear search" style="text-decoration: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>

                <!-- Mobile Live Suggestions Dropdown -->
                <div id="mobileSearchSuggestions" class="position-absolute bg-white border rounded-4 shadow-lg mt-2 d-none overflow-hidden search-suggestions-box" 
                     style="z-index: 1070; top: 100%; left: 0; right: 0; max-height: 75vh; overflow-y: auto;">
                </div>
            </form>
        </div>
        @endif
    </div>
</header>

<!-- Minimalist Clean Slide-Out Menu Drawer -->
<div class="offcanvas offcanvas-start border-0 shadow" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel" style="width: 300px; z-index: 1095 !important;">
    <!-- Drawer Header -->
    <div class="offcanvas-header px-3.5 py-2.5 border-bottom d-flex align-items-center justify-content-between">
        <x-logo height="26" />
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <!-- Drawer Content -->
    <div class="offcanvas-body px-3 py-2.5 d-flex flex-column justify-content-between">
        <div>
            <!-- Main Navigation Links -->
            <div class="mb-2.5">
                <div class="text-uppercase small text-muted fw-bold mb-1.5 px-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Navigation</div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('home') }}" class="drawer-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="bi bi-house drawer-icon me-3"></i> <span>Home</span>
                    </a>
                    <a href="{{ route('shop') }}" class="drawer-link {{ request()->routeIs('shop') ? 'active' : '' }}">
                        <i class="bi bi-grid drawer-icon me-3"></i> <span>Shop All Products</span>
                    </a>
                    <a href="{{ route('offers.index') }}" class="drawer-link {{ request()->routeIs('offers.*') ? 'active' : '' }}">
                        <i class="bi bi-percent drawer-icon me-3 text-success"></i> <span>Special Offers & Deals</span>
                    </a>
                    <a href="{{ route('cart.index') }}" class="drawer-link d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-bag drawer-icon me-3"></i> <span>Shopping Cart</span>
                        </div>
                        <span class="badge bg-light text-dark border font-monospace">{{ $cartItemCount ?? 0 }}</span>
                    </a>
                    @auth('customer')
                    <a href="{{ route('wishlist.index') }}" class="drawer-link d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-heart drawer-icon me-3 text-danger"></i> <span>Saved Wishlist</span>
                        </div>
                        <span id="drawer-wishlist-badge" class="badge bg-light text-dark border font-monospace wishlist-badge-count">{{ count($wishlistedProductIds ?? []) }}</span>
                    </a>
                    @endauth
                </div>
            </div>

            <!-- Categories Dropdown Accordion (Hidden by default, shown on click) -->
            <div class="mb-2">
                <a href="#drawerCategoriesCollapse" class="drawer-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="drawerCategoriesCollapse">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tags drawer-icon me-3 text-primary"></i> <span>Categories</span>
                    </div>
                    <i class="bi bi-chevron-down small text-muted chevron-rotate"></i>
                </a>
                <div class="collapse mt-1 ps-3" id="drawerCategoriesCollapse">
                    <div class="d-flex flex-column gap-1 py-1 border-start ps-2.5 ms-2">
                        @forelse($menuCategories ?? [] as $cat)
                            <a href="{{ route('category.products', $cat->slug) }}" class="drawer-sublink">
                                {{ $cat->name }}
                            </a>
                        @empty
                            <span class="drawer-sublink text-muted">No categories</span>
                        @endforelse
                        @if(count($menuCategories ?? []) > 0)
                            <a href="{{ route('categories.index') }}" class="drawer-sublink text-primary fw-bold pt-1 d-inline-flex align-items-center gap-1">
                                <span>All Categories</span> <i class="bi bi-arrow-right small"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Brands Dropdown Accordion (Hidden by default, shown on click) -->
            <div class="mb-2.5">
                <a href="#drawerBrandsCollapse" class="drawer-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="drawerBrandsCollapse">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-award drawer-icon me-3 text-primary"></i> <span>Brands</span>
                    </div>
                    <i class="bi bi-chevron-down small text-muted chevron-rotate"></i>
                </a>
                <div class="collapse mt-1 ps-3" id="drawerBrandsCollapse">
                    <div class="d-flex flex-column gap-1 py-1 border-start ps-2.5 ms-2">
                        @forelse($menuBrands ?? [] as $brand)
                            <a href="{{ route('brand.products', $brand->slug) }}" class="drawer-sublink">
                                {{ $brand->name }}
                            </a>
                        @empty
                            <span class="drawer-sublink text-muted">No brands</span>
                        @endforelse
                        @if(count($menuBrands ?? []) > 0)
                            <a href="{{ route('brands.index') }}" class="drawer-sublink text-primary fw-bold pt-1 d-inline-flex align-items-center gap-1">
                                <span>All Brands</span> <i class="bi bi-arrow-right small"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Support & Legal Links with compact spacing -->
            <div class="pt-2.5 border-top mt-2.5 mb-2">
                <div class="text-uppercase small text-muted fw-bold mb-1.5 px-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">Help & Information</div>
                <div class="d-flex flex-column gap-1 px-1">
                    <a href="{{ url('/about-us') }}" class="drawer-sublink py-1">
                        <i class="bi bi-info-circle drawer-icon me-3 text-muted"></i> <span>About Us</span>
                    </a>
                    <a href="{{ url('/contact-us') }}" class="drawer-sublink py-1">
                        <i class="bi bi-headset drawer-icon me-3 text-muted"></i> <span>Contact & Support</span>
                    </a>
                    @auth('customer')
                    <a href="{{ route('account.tickets') }}" class="drawer-sublink py-1">
                        <i class="bi bi-ticket-perforated drawer-icon me-3 text-warning"></i> <span class="fw-semibold text-dark">My Tickets</span>
                    </a>
                    @endauth
                    <a href="{{ url('/faq') }}" class="drawer-sublink py-1">
                        <i class="bi bi-question-circle drawer-icon me-3 text-muted"></i> <span>FAQs</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Drawer Footer: Single Clean Sign In button for guest, or Account for logged in -->
        <div class="pt-2.5 border-top mt-2 pb-1">
            @auth('customer')
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark small" style="font-size: 0.84rem;">{{ Auth::guard('customer')->user()->name }}</div>
                        <a href="{{ route('dashboard') }}" class="text-primary text-decoration-none small" style="font-size: 0.74rem;">Account Dashboard &rarr;</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" style="font-size: 0.75rem;">Logout</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary rounded-pill w-100 py-2 fw-semibold shadow-xs" style="font-size: 0.86rem;">
                    Sign In
                </a>
            @endauth
        </div>
    </div>
</div>

<style>
    /* Clean Minimalist Header Styles */
    .header-main {
        background-color: #ffffff;
        border-color: #f1f5f9 !important;
    }
    
    .nav-clean-link {
        font-weight: 500;
        font-size: 0.92rem;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .nav-clean-link:hover {
        color: #0d6efd !important;
    }
    .nav-clean-link.active {
        font-weight: 600;
    }

    .search-input-group {
        border-radius: 50rem;
        overflow: hidden;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .search-input-group:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
        background-color: #ffffff;
    }
    .search-input-group input:focus {
        background-color: #ffffff !important;
    }

    .header-icon-link {
        transition: color 0.15s, transform 0.15s;
    }
    .header-icon-link:hover {
        color: #0d6efd !important;
        transform: translateY(-1px);
    }

    .menu-trigger-btn {
        transition: background-color 0.15s;
    }
    .menu-trigger-btn:hover {
        background-color: #f1f5f9 !important;
    }

    /* Drawer Styles */
    .drawer-icon {
        width: 22px;
        text-align: center;
        font-size: 1.08rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-right: 0.85rem !important;
    }

    .drawer-link {
        display: flex;
        align-items: center;
        padding: 0.45rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.89rem;
        font-weight: 500;
        color: #1e293b;
        text-decoration: none;
        transition: background-color 0.15s, color 0.15s;
    }
    .drawer-link:hover {
        background-color: #f8fafc;
        color: #0d6efd;
    }
    .drawer-link.active {
        background-color: #eff6ff;
        color: #0d6efd;
        font-weight: 600;
    }

    /* Chevron rotation on collapse toggle */
    [data-bs-toggle="collapse"][aria-expanded="true"] .chevron-rotate {
        transform: rotate(180deg);
        transition: transform 0.2s ease;
    }
    [data-bs-toggle="collapse"] .chevron-rotate {
        transition: transform 0.2s ease;
    }

    .drawer-sublink {
        display: flex;
        align-items: center;
        padding: 0.28rem 0.65rem;
        font-size: 0.83rem;
        color: #64748b;
        text-decoration: none;
        transition: color 0.15s, transform 0.15s;
    }
    .drawer-sublink:hover {
        color: #0d6efd;
        transform: translateX(3px);
    }

    /* App-Style Product Search Dropdown Styling */
    .search-input-group:focus-within {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
        background: #ffffff !important;
    }
    .search-suggestions-box {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        border-radius: 1rem !important;
    }
    .search-suggestions-box,
    .search-suggestions-box * {
        user-select: none;
    }
    .suggestion-query-row,
    .suggestion-query-row * {
        cursor: pointer !important;
    }
    .suggestion-query-row {
        transition: all 0.15s ease;
    }
    .suggestion-query-row:hover, .suggestion-query-row.active {
        background-color: #f5f3ff !important;
    }
    .suggestion-row-item,
    .suggestion-row-item * {
        cursor: pointer !important;
    }
    .suggestion-row-item {
        transition: all 0.15s ease;
    }
    .suggestion-row-item:hover, .suggestion-row-item.active {
        background-color: #f8fafc;
    }
    .suggestion-action-icon {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.82rem;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }
    .suggestion-row-item:hover .suggestion-action-icon,
    .suggestion-query-row:hover .suggestion-action-icon {
        background-color: #e0e7ff;
        color: #4338ca;
        transform: scale(1.08);
    }
    .suggestion-footer-btn,
    .suggestion-footer-btn * {
        cursor: pointer !important;
    }
    .suggestion-footer-btn {
        transition: all 0.2s ease;
        background-color: #f8fafc;
    }
    .suggestion-footer-btn:hover {
        background-color: #eef2ff !important;
    }
    .suggestion-footer-btn:hover .footer-arrow-icon {
        transform: translateX(5px);
    }
    .recent-search-item,
    .recent-search-item * {
        cursor: pointer !important;
    }
    .recent-search-item {
        padding: 8px 12px;
        border-radius: 8px;
        transition: background 0.15s ease;
    }
    .recent-search-item:hover {
        background: #f8fafc;
    }
</style>

<script>
(function () {
    const STORAGE_KEY = 'wk_recent_searches';

    function getRecentSearches() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveRecentSearch(term) {
        if (!term || term.trim().length < 2) return;
        term = term.trim();
        let list = getRecentSearches().filter(item => item.toLowerCase() !== term.toLowerCase());
        list.unshift(term);
        if (list.length > 5) list = list.slice(0, 5);
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        } catch (e) {}
    }

    function removeRecentSearch(term) {
        let list = getRecentSearches().filter(item => item.toLowerCase() !== term.toLowerCase());
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        } catch (e) {}
    }

    function clearAllRecentSearches() {
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch (e) {}
    }

    function renderEmptyState(box, input, form) {
        const recent = getRecentSearches();
        if (recent.length === 0) {
            box.classList.add('d-none');
            box.innerHTML = '';
            return;
        }

        box.innerHTML = `
            <div class="p-3.5 px-3 px-md-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-1">
                    <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.74rem; letter-spacing: 0.6px;">
                        <i class="bi bi-clock-history me-1.5 text-primary"></i> Recent Searches
                    </span>
                    <button type="button" class="btn btn-link p-0 text-muted small text-decoration-none fw-semibold" style="font-size: 0.78rem;" id="clear-all-recent-btn">
                        Clear All
                    </button>
                </div>
                <div class="d-flex flex-column gap-1.5">
                    ${recent.map(term => `
                        <div class="recent-search-item d-flex align-items-center justify-content-between text-dark" data-term="${escapeHtml(term)}">
                            <div class="d-flex align-items-center gap-2.5 cursor-pointer flex-grow-1" onclick="applySearchTerm('${escapeJs(term)}')">
                                <i class="bi bi-search text-muted opacity-50" style="font-size: 0.8rem;"></i>
                                <span class="fw-medium" style="font-size: 0.88rem;">${escapeHtml(term)}</span>
                            </div>
                            <button type="button" class="btn btn-link text-muted p-1 px-2 border-0" onclick="removeSingleRecent(event, '${escapeJs(term)}')" title="Remove">
                                <i class="bi bi-x fs-5"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        box.classList.remove('d-none');

        const clearAllBtn = box.querySelector('#clear-all-recent-btn');
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                clearAllRecentSearches();
                renderEmptyState(box, input, form);
            });
        }
    }

    function setupSearch(inputId, formId, boxId) {
        const input = document.getElementById(inputId);
        const form  = document.getElementById(formId);
        const box   = document.getElementById(boxId);
        if (!input || !form || !box) return;

        const clearBtn = form.querySelector('.btn-clear-search');
        let timer;

        function updateClearBtn() {
            if (clearBtn) {
                if (input.value.trim().length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                }
            }
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                updateClearBtn();
                input.focus();
                if (typeof window.onHeaderSearchInput === 'function') {
                    window.onHeaderSearchInput('');
                    box.classList.add('d-none');
                    return;
                }
                renderEmptyState(box, input, form);
            });
        }

        form.addEventListener('submit', function (e) {
            const val = input.value.trim();
            saveRecentSearch(val);
            if (typeof window.onHeaderSearchSubmit === 'function') {
                const handled = window.onHeaderSearchSubmit(val);
                if (handled) {
                    e.preventDefault();
                    box.classList.add('d-none');
                    return;
                }
            }
        });

        input.addEventListener('input', function () {
            clearTimeout(timer);
            updateClearBtn();
            const q = this.value.trim();

            if (typeof window.onHeaderSearchInput === 'function') {
                const handled = window.onHeaderSearchInput(q);
                if (handled) {
                    box.classList.add('d-none');
                    return;
                }
            }

            if (q.length === 0) {
                renderEmptyState(box, input, form);
                return;
            }

            timer = setTimeout(() => {
                fetch(`{{ route('shop') }}?q=${encodeURIComponent(q)}&suggestion=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    const structured = data.structured || [];
                    const totalCount = data.total_count || structured.length;
                    const suggestions = structured.filter(s => s.type !== 'tag');
                    const currentLower = q.toLowerCase();
                    const matchingRecent = getRecentSearches().filter(term => term.toLowerCase().includes(currentLower));

                    if (suggestions.length === 0 && matchingRecent.length === 0) {
                        box.innerHTML = `
                            <div class="p-4 text-center">
                                <i class="bi bi-search display-6 text-muted opacity-25 mb-2 d-block"></i>
                                <div class="fw-bold text-dark mb-1">No results found for "${escapeHtml(q)}"</div>
                                <div class="text-muted small">Try checking your spelling or use general keywords like "mobiles", "samsung", "5g".</div>
                            </div>
                        `;
                    } else {
                        let html = '';

                        // 1. Direct Search Query Row
                        html += `
                            <div class="suggestion-query-row py-3 px-3 px-md-4 border-bottom d-flex align-items-center gap-3 text-dark" onclick="applySearchTerm('${escapeJs(q)}')">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 32px; height: 32px;">
                                    <i class="bi bi-search" style="font-size: 0.85rem;"></i>
                                </div>
                                <span style="font-size: 0.92rem;" class="min-w-0 text-truncate">Search for <strong class="text-primary">"${escapeHtml(q)}"</strong></span>
                                <span class="suggestion-action-icon ms-auto" aria-hidden="true">
                                    <i class="bi bi-arrow-up-right"></i>
                                </span>
                            </div>
                        `;

                        // 2. Matching Recent Searches
                        if (matchingRecent.length > 0) {
                            html += `
                                <div class="py-2.5 px-3 border-bottom">
                                    <div class="px-3 pt-1 pb-2">
                                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.6px;">
                                            <i class="bi bi-clock-history me-1.5 text-primary"></i> Recent History
                                        </span>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        ${matchingRecent.slice(0, 3).map(term => `
                                            <div class="d-flex align-items-center justify-content-between py-2 px-3 rounded-3 cursor-pointer suggestion-row-item" onclick="applySearchTerm('${escapeJs(term)}')">
                                                <div class="d-flex align-items-center gap-2.5 gap-md-3 min-w-0">
                                                    <i class="bi bi-clock-history text-muted" style="font-size: 0.88rem;"></i>
                                                    <span class="fw-semibold text-dark text-truncate" style="font-size: 0.88rem;">${escapeHtml(term)}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                                                    <span class="badge rounded-pill" style="background: #eef2ff; color: #4338ca; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px;">RECENT</span>
                                                    <button type="button" class="btn btn-link p-0 text-muted border-0 d-inline-flex align-items-center justify-content-center" onclick="removeSingleRecent(event, '${escapeJs(term)}')" title="Remove" style="width: 24px; height: 24px;">
                                                        <i class="bi bi-x fs-5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        // 3. Autocomplete Rows (Matching Product Names, Brands, Categories)
                        if (suggestions.length > 0) {
                            html += `
                                <div class="py-2">
                                    <div class="d-flex flex-column gap-0.5">
                                        ${suggestions.slice(0, 8).map(s => {
                                            let iconClass = 'bi bi-search text-muted';
                                            let badgeBg = '#f1f5f9';
                                            let badgeFg = '#475569';
                                            let clickHandler = s.url ? `selectSearchProduct('${escapeJs(s.url)}', '${escapeJs(q)}')` : `applySearchTerm('${escapeJs(s.text)}')`;

                                            if (s.type === 'category') {
                                                iconClass = 'bi bi-grid-fill text-info';
                                                badgeBg = '#e0f2fe';
                                                badgeFg = '#0369a1';
                                            } else if (s.type === 'brand') {
                                                iconClass = 'bi bi-patch-check-fill text-warning';
                                                badgeBg = '#fef3c7';
                                                badgeFg = '#b45309';
                                            } else if (s.type === 'product') {
                                                iconClass = 'bi bi-box-seam text-primary';
                                                badgeBg = '#eef2ff';
                                                badgeFg = '#4338ca';
                                            }

                                            return `
                                                <div class="d-flex align-items-center justify-content-between py-2.5 px-3 px-md-4 cursor-pointer suggestion-row-item" onclick="${clickHandler}">
                                                    <div class="d-flex align-items-center gap-2.5 gap-md-3 min-w-0">
                                                        <i class="${iconClass} fs-6 flex-shrink-0"></i>
                                                        <span class="fw-medium text-dark text-truncate" style="font-size: 0.9rem;">${highlightMatch(s.text, q)}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2">
                                                        <span class="badge rounded-pill" style="background: ${badgeBg}; color: ${badgeFg}; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; padding: 4px 8px;">${s.type.toUpperCase()}</span>
                                                        <span class="suggestion-action-icon" aria-hidden="true" title="Open ${s.type}">
                                                            <i class="bi bi-arrow-up-right"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        // 4. View All Results Footer (Spacious, prominent, clean)
                        html += `
                            <div class="py-3 px-3 px-md-4 text-center cursor-pointer suggestion-footer-btn d-flex align-items-center justify-content-center gap-2.5 border-top" onclick="applySearchTerm('${escapeJs(q)}')">
                                <span class="fw-semibold text-primary" style="font-size: 0.94rem;">
                                    See all <span class="badge bg-primary rounded-pill px-2.5 py-1 mx-1 shadow-xs" style="font-size: 0.82rem; font-weight: 700;">${totalCount}</span> results for "${escapeHtml(q)}"
                                </span>
                                <i class="bi bi-arrow-right fs-6 text-primary fw-bold footer-arrow-icon"></i>
                            </div>
                        `;

                        box.innerHTML = html;
                    }
                    box.classList.remove('d-none');
                })
                .catch(() => box.classList.add('d-none'));
            }, 250);
        });

        input.addEventListener('focus', function () {
            if (typeof window.onHeaderSearchInput === 'function') {
                box.classList.add('d-none');
                return;
            }
            if (this.value.trim().length === 0) {
                renderEmptyState(box, input, form);
            } else if (this.value.trim().length >= 1) {
                box.classList.remove('d-none');
            }
        });

        document.addEventListener('click', function (e) {
            if (!form.contains(e.target)) {
                box.classList.add('d-none');
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                box.classList.add('d-none');
                this.blur();
            }
        });

        updateClearBtn();
    }

    function escapeHtml(str) {
        return (str || '').replace(/[&<>"']/g, m => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[m]);
    }

    function escapeJs(str) {
        return (str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }

    function highlightMatch(text, term) {
        if (!term || !text) return escapeHtml(text);
        const safeTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${safeTerm})`, 'gi');
        return escapeHtml(text).replace(regex, '<strong class="text-primary">$1</strong>');
    }

    window.applySearchTerm = function (term) {
        saveRecentSearch(term);
        window.location.href = `{{ route('shop') }}?q=${encodeURIComponent(term)}`;
    };

    window.selectSearchProduct = function (url, term) {
        saveRecentSearch(term);
        window.location.href = url;
    };

    window.removeSingleRecent = function (event, term) {
        event.stopPropagation();
        removeRecentSearch(term);
        const desktopBox = document.getElementById('searchSuggestions');
        const mobileBox = document.getElementById('mobileSearchSuggestions');
        if (desktopBox && !desktopBox.classList.contains('d-none')) {
            renderEmptyState(desktopBox, document.getElementById('mainSearchInput'), document.getElementById('mainSearchForm'));
        }
        if (mobileBox && !mobileBox.classList.contains('d-none')) {
            renderEmptyState(mobileBox, document.getElementById('mobileSearchInput'), document.getElementById('mobileSearchForm'));
        }
    };

    // Initialize both Desktop and Mobile search bars
    setupSearch('mainSearchInput', 'mainSearchForm', 'searchSuggestions');
    setupSearch('mobileSearchInput', 'mobileSearchForm', 'mobileSearchSuggestions');
})();
</script>

<!-- Delivery Location Selector Modal -->
<div class="modal fade" id="deliveryLocationModal" tabindex="-1" aria-labelledby="deliveryLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3 pt-sm-4 px-3 px-sm-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                         style="width: 44px; height: 44px; background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%); color: #6366f1; font-size: 1.2rem;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-1" id="deliveryLocationModalLabel" style="font-size: 1.05rem;">Choose Delivery Location</h6>
                        <small class="text-muted" style="font-size: 0.8rem;">Check delivery speed & payment availability</small>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3 p-sm-4">
                <!-- Pincode Input Box -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-2" style="font-size: 0.84rem;">Enter a 6-digit Indian PIN code</label>
                    <div class="input-group shadow-xs rounded-pill overflow-hidden border p-1" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                        <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-primary" style="font-size: 1.1rem;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <input type="text" id="modalPincodeInput" maxlength="6" 
                               class="form-control bg-transparent border-0 font-monospace fw-bold ps-1 text-dark shadow-none" 
                               placeholder="e.g. 500081" value="{{ $deliveryLocation['pincode'] ?? '' }}"
                               style="font-size: 0.95rem; letter-spacing: 0.08em;">
                        <button type="button" class="btn btn-primary rounded-pill px-3 px-sm-4 fw-semibold shadow-xs d-inline-flex align-items-center gap-1" 
                                style="font-size: 0.88rem;" id="modalApplyPincodeBtn">
                            <span>Apply</span> <i class="bi bi-arrow-right-short fs-5"></i>
                        </button>
                    </div>
                    <div id="modalPincodeFeedback" class="mt-3 p-3 rounded-3 small d-none"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const applyBtn = document.getElementById('modalApplyPincodeBtn');
    const input = document.getElementById('modalPincodeInput');
    const feedback = document.getElementById('modalPincodeFeedback');
    const headerText = document.getElementById('header-delivery-text');
    const mobileHeaderText = document.getElementById('header-mobile-delivery-text');

    function checkAndSaveLocation(pincode) {
        if (!pincode || pincode.length !== 6) {
            feedback.className = 'mt-3 p-3 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger border-opacity-25 small fw-semibold d-flex align-items-center gap-2';
            feedback.innerHTML = '<i class="bi bi-exclamation-circle fs-6 flex-shrink-0"></i> <span>Please enter a valid 6-digit PIN code.</span>';
            feedback.classList.remove('d-none');
            return;
        }

        applyBtn.disabled = true;
        applyBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        fetch('{{ route("delivery.save") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ pincode: pincode })
        })
        .then(r => r.json())
        .then(data => {
            applyBtn.disabled = false;
            applyBtn.innerHTML = '<span>Apply</span> <i class="bi bi-arrow-right-short fs-5"></i>';

            if (data.success && data.location && data.location.is_serviceable) {
                feedback.className = 'mt-3 p-3 bg-success bg-opacity-10 text-success rounded-3 border border-success border-opacity-25 small fw-semibold d-flex align-items-center gap-2';
                feedback.innerHTML = `<i class="bi bi-check-circle-fill text-success fs-6 flex-shrink-0"></i> <span>Delivering to <strong>${data.location.location_text}</strong> • ${data.location.estimated_delivery}</span>`;
                feedback.classList.remove('d-none');

                if (headerText) {
                    headerText.textContent = data.location.location_text;
                }
                if (mobileHeaderText) {
                    mobileHeaderText.textContent = data.location.city || data.location.pincode;
                }

                // If product details page has delivery widget, update it too
                if (typeof window.onDeliveryPincodeUpdated === 'function') {
                    window.onDeliveryPincodeUpdated(data.location);
                }

                setTimeout(() => {
                    const modalEl = document.getElementById('deliveryLocationModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                }, 1000);
            } else {
                feedback.className = 'mt-3 p-3 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger border-opacity-25 small fw-semibold d-flex align-items-center gap-2';
                feedback.innerHTML = `<i class="bi bi-x-circle-fill fs-6 flex-shrink-0"></i> <span>${data.message || 'Location not serviceable.'}</span>`;
                feedback.classList.remove('d-none');
            }
        })
        .catch(err => {
            applyBtn.disabled = false;
            applyBtn.innerHTML = '<span>Apply</span> <i class="bi bi-arrow-right-short fs-5"></i>';
            feedback.className = 'mt-3 p-3 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger border-opacity-25 small fw-semibold d-flex align-items-center gap-2';
            feedback.innerHTML = '<i class="bi bi-exclamation-circle fs-6 flex-shrink-0"></i> <span>Network error. Please try again.</span>';
            feedback.classList.remove('d-none');
        });
    }

    if (applyBtn && input) {
        applyBtn.addEventListener('click', () => checkAndSaveLocation(input.value.trim()));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkAndSaveLocation(input.value.trim());
            }
        });
    }
});
</script>
