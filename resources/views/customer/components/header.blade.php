@php
    $liveMegaSale = app(\App\Services\OfferService::class)->getLiveMegaSale();
    $isCategoriesPage = request()->routeIs('categories.*') || request()->is('categories*');
    $isBrandsPage = request()->routeIs('brands.*') || request()->is('brands*');
    $isCheckoutFlow = request()->routeIs('checkout.*') || request()->is('checkout*') || request()->is('order-success*');
    $hideHeaderSearch = $isCheckoutFlow || $isCategoriesPage || $isBrandsPage;

    $headerSearchPlaceholder = 'Search products, brands, categories, tags...';
    $headerSearchAction = route('shop');
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

            @if(!$hideHeaderSearch)
            <!-- Center: Amazon-Style Unified Search Command Center -->
            <div class="flex-grow-1 d-none d-md-block mx-2 mx-xl-3 position-relative" style="max-width: 720px; z-index: 1061;">
                <form action="{{ $headerSearchAction }}" method="GET" class="position-relative w-100" id="mainSearchForm" autocomplete="off">
                    <div class="amazon-search-container d-flex align-items-stretch rounded-3" 
                         style="height: 42px; background: #ffffff; border: 2px solid #cbd5e1; transition: border-color 0.2s, box-shadow 0.2s;">
                        
                        <!-- 1. Left: Scope Selector (Custom Dropdown Menu) -->
                        <div class="dropdown flex-shrink-0 h-100">
                            @php
                                $selectedCatId = request('category')[0] ?? null;
                                $selectedCat = $selectedCatId ? collect($menuCategories ?? [])->firstWhere('id', $selectedCatId) : null;
                                if (!$selectedCat && isset($category) && $category instanceof \App\Models\Category) {
                                    $selectedCat = $category;
                                    $selectedCatId = $category->id;
                                }
                            @endphp
                            <button type="button" 
                                    class="btn amazon-search-scope d-flex align-items-center h-100 border-0 rounded-start-3 rounded-end-0 px-2.5 text-secondary" 
                                    data-bs-toggle="dropdown" 
                                    data-bs-display="static"
                                    aria-expanded="false" 
                                    id="searchCategoryDropdownBtn"
                                    style="background: #f1f5f9; border-right: 1px solid #cbd5e1 !important; font-size: 0.8rem; font-weight: 600; cursor: pointer; user-select: none;"
                                    title="Select department">
                                <span id="searchCategoryLabel" class="text-truncate me-1 text-dark" style="max-width: 85px;">
                                    {{ $selectedCat ? $selectedCat->name : 'All' }}
                                </span>
                                <i class="bi bi-caret-down-fill text-muted" style="font-size: 0.6rem;"></i>
                            </button>

                            <!-- Hidden Category ID Input for Form Submission -->
                            <input type="hidden" name="category[]" id="searchCategoryInput" value="{{ $selectedCatId ?? '' }}">

                            <!-- Custom High-End Dropdown Menu -->
                            <ul class="dropdown-menu shadow-lg border rounded-3 p-1.5 mt-1" 
                                style="min-width: 220px; max-height: 380px; overflow-y: auto; z-index: 1080; border-color: #e2e8f0 !important;" 
                                aria-labelledby="searchCategoryDropdownBtn">
                                <li class="px-2.5 py-1 text-uppercase fw-bold text-muted border-bottom mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                                    Departments
                                </li>
                                <li>
                                    <button type="button" 
                                            class="dropdown-item d-flex align-items-center justify-content-between py-2 px-2.5 rounded-2 {{ empty($selectedCatId) ? 'active fw-bold' : '' }}" 
                                            onclick="selectSearchCategory('', 'All', '{{ route('shop') }}', this)" 
                                            style="font-size: 0.84rem;">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="bi bi-grid text-muted"></i>
                                            <span>All Departments</span>
                                        </span>
                                        @if(empty($selectedCatId))
                                            <i class="bi bi-check2 text-primary fw-bold fs-6 cat-check-icon"></i>
                                        @endif
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                @foreach($menuCategories ?? [] as $mCat)
                                    @php
                                        $isCatSelected = ($selectedCatId == $mCat->id);
                                        $catLower = strtolower($mCat->name);
                                        $catIcon = 'bi-tag';
                                        if (str_contains($catLower, 'mobile') || str_contains($catLower, 'phone')) $catIcon = 'bi-phone';
                                        elseif (str_contains($catLower, 'electr')) $catIcon = 'bi-laptop';
                                        elseif (str_contains($catLower, 'fashion') || str_contains($catLower, 'cloth')) $catIcon = 'bi-bag';
                                        elseif (str_contains($catLower, 'kitchen') || str_contains($catLower, 'home')) $catIcon = 'bi-house';
                                        elseif (str_contains($catLower, 'sport')) $catIcon = 'bi-trophy';
                                        elseif (str_contains($catLower, 'grocer')) $catIcon = 'bi-basket';
                                        elseif (str_contains($catLower, 'beaut')) $catIcon = 'bi-stars';
                                        elseif (str_contains($catLower, 'book')) $catIcon = 'bi-book';
                                    @endphp
                                    <li>
                                        <button type="button" 
                                                class="dropdown-item d-flex align-items-center justify-content-between py-2 px-2.5 rounded-2 {{ $isCatSelected ? 'active fw-bold' : '' }}" 
                                                onclick="selectSearchCategory('{{ $mCat->id }}', '{{ addslashes($mCat->name) }}', '{{ route('category.products', $mCat->slug) }}', this)" 
                                                style="font-size: 0.84rem;">
                                            <span class="d-flex align-items-center gap-2 text-truncate">
                                                <i class="bi {{ $catIcon }} text-muted"></i>
                                                <span class="text-truncate">{{ $mCat->name }}</span>
                                            </span>
                                            @if($isCatSelected)
                                                <i class="bi bi-check2 text-primary fw-bold fs-6 cat-check-icon"></i>
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- 2. Center: Input Field (Spacious & Clean, Zero Ctrl K) -->
                        <div class="position-relative flex-grow-1 d-flex align-items-center">
                            <input
                                type="text"
                                name="q"
                                id="mainSearchInput"
                                class="form-control border-0 shadow-none px-3 py-0 search-core-input"
                                placeholder="{{ $headerSearchPlaceholder }}"
                                value="{{ request('q') }}"
                                style="font-size: 0.9rem; font-weight: 500; height: 100%; color: #0f172a; background: #ffffff;"
                                autocomplete="off"
                                spellcheck="false"
                            >
                            <!-- Clear Button ✕ -->
                            <button type="button" class="btn btn-link text-secondary p-0 d-none btn-clear-search" aria-label="Clear search" style="text-decoration: none; cursor: pointer; z-index: 5; margin-right: 8px;">
                                <i class="bi bi-x-circle-fill text-muted" style="font-size: 1.05rem;"></i>
                            </button>

                            <!-- Desktop Voice Search Button (Mic Icon with comfortable breathing room) -->
                            <button type="button" 
                                    class="btn btn-link text-muted p-0 d-flex align-items-center justify-content-center border-0 btn-voice-search" 
                                    id="btnDesktopVoiceSearch" 
                                    aria-label="Voice Search" 
                                    title="Search by voice" 
                                    style="text-decoration: none; cursor: pointer; z-index: 5; margin-right: 14px; width: 30px; height: 30px; border-radius: 50%;">
                                <i class="bi bi-mic-fill fs-6" style="color: #64748b;"></i>
                            </button>
                        </div>

                        <!-- 3. Right: Solid Amazon Amber Search Button -->
                        <button type="submit" 
                                class="btn amazon-search-btn d-flex align-items-center justify-content-center flex-shrink-0 border-0 rounded-start-0 rounded-end-3" 
                                style="background: #febd69; color: #111827; width: 48px; transition: background 0.15s ease;" 
                                title="Search">
                            <i class="bi bi-search fs-6 fw-bold"></i>
                        </button>
                    </div>

                    <!-- Live Suggestions Dropdown -->
                    <div id="searchSuggestions" class="position-absolute bg-white border rounded-3 shadow-xl mt-1.5 d-none search-suggestions-box" 
                         style="z-index: 1070; top: 100%; left: 0; right: 0; width: 100%; max-height: 560px; overflow-y: auto; -webkit-overflow-scrolling: touch;">
                    </div>
                </form>
            </div>
            @elseif($isCheckoutFlow)
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

        @if(!$hideHeaderSearch)
        <!-- Mobile Search Bar (Amazon Style with Amber Search Button + Voice Search) -->
        <div class="d-block d-md-none pt-1.5 pb-0.5 position-relative" style="z-index: 1061;">
            <form action="{{ $headerSearchAction }}" method="GET" class="position-relative" id="mobileSearchForm" autocomplete="off">
                <input type="hidden" name="category[]" id="mobileSearchCategoryInput" value="{{ $selectedCatId ?? '' }}">
                <div class="amazon-mobile-search-container d-flex align-items-stretch rounded-3 overflow-hidden" 
                     style="height: 42px; background: #ffffff; border: 2px solid #cbd5e1; box-shadow: 0 1px 3px rgba(15,23,42,0.06); transition: border-color 0.2s, box-shadow 0.2s;">
                    
                    <div class="position-relative flex-grow-1 d-flex align-items-center">
                        <input type="text" name="q" id="mobileSearchInput" class="form-control bg-transparent border-0 shadow-none ps-3 pe-2 py-0 search-core-input" 
                               placeholder="{{ $headerSearchPlaceholder }}" value="{{ request('q') }}" style="font-size: 0.88rem; font-weight: 500; height: 100%; color: #0f172a;" autocomplete="off" spellcheck="false">
                        
                        <!-- Clear button -->
                        <button type="button" class="btn btn-link text-secondary p-0 d-none btn-clear-search" aria-label="Clear search" style="text-decoration: none; cursor: pointer; z-index: 5; margin-right: 6px;">
                            <i class="bi bi-x-circle-fill text-muted" style="font-size: 1rem;"></i>
                        </button>

                        <!-- Voice Search Button (Mic Icon) -->
                        <button type="button" 
                                class="btn btn-link text-muted p-0 d-flex align-items-center justify-content-center border-0 btn-voice-search" 
                                id="btnVoiceSearch" 
                                aria-label="Voice Search" 
                                title="Search by voice" 
                                style="text-decoration: none; cursor: pointer; z-index: 5; margin-right: 8px; width: 30px; height: 30px; border-radius: 50%;">
                            <i class="bi bi-mic-fill fs-6" style="color: #64748b;"></i>
                        </button>
                    </div>

                    <!-- Right: Solid Amazon Amber Search Button -->
                    <button type="submit" 
                            class="btn amazon-search-btn d-flex align-items-center justify-content-center flex-shrink-0 border-0 rounded-0" 
                            style="background: #febd69; color: #111827; width: 46px; transition: background 0.15s ease;" 
                            title="Search">
                        <i class="bi bi-search fs-6 fw-bold"></i>
                    </button>
                </div>

                <!-- Mobile Live Suggestions Dropdown -->
                <div id="mobileSearchSuggestions" class="position-absolute bg-white border rounded-3 shadow-xl mt-1.5 d-none search-suggestions-box" 
                     style="z-index: 1070; top: 100%; left: 0; right: 0; width: 100%; max-height: 75vh; overflow-y: auto; -webkit-overflow-scrolling: touch;">
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

    /* Amazon-Style Unified Search Command Center */
    .amazon-search-container {
        border-color: #cbd5e1 !important;
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }
    .amazon-search-container:hover {
        border-color: #94a3b8 !important;
    }
    .amazon-search-container:focus-within {
        border-color: #f3a847 !important;
        box-shadow: 0 0 0 3px rgba(243, 168, 71, 0.35) !important;
    }
    .amazon-search-scope {
        transition: background-color 0.15s ease;
    }
    .amazon-search-scope:hover {
        background-color: #e2e8f0 !important;
    }
    .amazon-search-btn:hover {
        background-color: #f3a847 !important;
    }
    .amazon-mobile-search-container {
        transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
    }
    .amazon-mobile-search-container:hover {
        border-color: #94a3b8 !important;
    }
    .amazon-mobile-search-container:focus-within {
        border-color: #f3a847 !important;
        box-shadow: 0 0 0 3px rgba(243, 168, 71, 0.3) !important;
    }
    .search-suggestions-box {
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 35px -10px rgba(15, 23, 42, 0.16), 0 8px 12px -4px rgba(15, 23, 42, 0.05) !important;
        border-radius: 0.85rem !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch !important;
    }
    .search-suggestions-box,
    .search-suggestions-box * {
        user-select: none;
    }
    .search-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.38rem 0.8rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 9999px;
        font-size: 0.82rem;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        white-space: nowrap;
    }
    .search-chip:hover {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #4338ca;
        transform: translateY(-1px);
    }
    .search-chip-trending {
        background: #fff7ed;
        border-color: #ffedd5;
        color: #c2410c;
    }
    .search-chip-trending:hover {
        background: #ffedd5;
        border-color: #fed7aa;
        color: #9a3412;
    }
    .brand-spotlight-card {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #dbeafe;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .brand-spotlight-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
        transform: translateY(-1px);
    }
    .preview-product-card {
        padding: 0.45rem 0.6rem;
        border-radius: 0.65rem;
        transition: background-color 0.15s ease, border-color 0.15s ease;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        border: 1px solid transparent;
    }
    .preview-product-card:hover {
        background-color: #f8fafc;
        border-color: #e2e8f0;
    }
    .preview-product-card img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 0.55rem;
        border: 1px solid #f1f5f9;
        background: #f8fafc;
        flex-shrink: 0;
    }
    .search-query-row {
        padding: 0.55rem 0.75rem;
        border-radius: 0.5rem;
        transition: background-color 0.12s ease;
        cursor: pointer;
    }
    .search-query-row:hover, .search-query-row.active {
        background-color: #f8fafc;
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
    .btn-voice-search {
        transition: color 0.15s ease, background-color 0.15s ease, transform 0.15s ease;
    }
    .btn-voice-search:hover {
        background-color: #f1f5f9 !important;
    }
    .btn-voice-search:hover i {
        color: #d97706 !important;
        transform: scale(1.1);
    }
    @keyframes mic-pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.25); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-mic-pulse {
        animation: mic-pulse 1.2s infinite ease-in-out;
        display: inline-block;
    }
    .voice-mic-wave-container {
        position: relative;
        width: 104px;
        height: 104px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .voice-wave-ring {
        position: absolute;
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: rgba(14, 165, 233, 0.28);
        animation: voiceRipple 1.8s infinite ease-out;
    }
    .voice-wave-ring.ring-2 {
        animation-delay: 0.6s;
    }
    @keyframes voiceRipple {
        0% { transform: scale(0.9); opacity: 0.85; }
        100% { transform: scale(1.65); opacity: 0; }
    }
</style>

<script>
(function () {
    const STORAGE_KEY = 'wk_recent_searches';

    // 1. Animated Typewriter/Rolling Placeholder
    const placeholderPhrases = [
        'Search "Smartphones & 5G"...',
        'Search "Running Shoes"...',
        'Search "Wireless Earbuds"...',
        'Search "Summer T-Shirts"...',
        'Search "Smartwatches"...',
        'Search "Laptops & Accessories"...'
    ];
    let phraseIdx = 0;
    setInterval(() => {
        const desktopInp = document.getElementById('mainSearchInput');
        const mobileInp  = document.getElementById('mobileSearchInput');
        phraseIdx = (phraseIdx + 1) % placeholderPhrases.length;
        const phrase = placeholderPhrases[phraseIdx];

        if (desktopInp && document.activeElement !== desktopInp && !desktopInp.value.trim()) {
            desktopInp.setAttribute('placeholder', phrase);
        }
        if (mobileInp && document.activeElement !== mobileInp && !mobileInp.value.trim()) {
            mobileInp.setAttribute('placeholder', phrase);
        }
    }, 3200);

    // 2. Global Hotkey (⌘K / Ctrl+K)
    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    document.querySelectorAll('.hotkey-symbol').forEach(el => {
        el.textContent = isMac ? '⌘' : 'Ctrl ';
    });
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const mainInp = document.getElementById('mainSearchInput');
            if (mainInp) {
                mainInp.focus();
                mainInp.select();
            }
        }
    });

    function closeAllSearchBoxes() {
        const dBox = document.getElementById('searchSuggestions');
        const mBox = document.getElementById('mobileSearchSuggestions');
        if (dBox) dBox.classList.add('d-none');
        if (mBox) mBox.classList.add('d-none');
    }

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
        if (list.length > 6) list = list.slice(0, 6);
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

    // 3. High-Converting Empty State (Trending + Recent + Top Categories)
    function renderEmptyState(box, input, form) {
        const recent = getRecentSearches();

        let html = `
            <div class="p-3.5 px-3 px-md-4 bg-white">
                <!-- Trending Searches -->
                <div class="mb-3.5">
                    <div class="d-flex align-items-center gap-1.5 mb-2">
                        <i class="bi bi-fire text-danger" style="font-size: 0.95rem;"></i>
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Trending Right Now</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5">
                        <span class="search-chip search-chip-trending" onclick="applySearchTerm('iPhone 15')">
                            <i class="bi bi-graph-up-arrow" style="font-size: 0.75rem;"></i> iPhone 15
                        </span>
                        <span class="search-chip search-chip-trending" onclick="applySearchTerm('Wireless Earbuds')">
                            <i class="bi bi-graph-up-arrow" style="font-size: 0.75rem;"></i> Wireless Earbuds
                        </span>
                        <span class="search-chip" onclick="applySearchTerm('Smartwatches')">Smartwatches</span>
                        <span class="search-chip" onclick="applySearchTerm('Running Shoes')">Running Shoes</span>
                        <span class="search-chip" onclick="applySearchTerm('T-Shirts')">T-Shirts</span>
                        <span class="search-chip" onclick="applySearchTerm('5G Mobiles')">5G Mobiles</span>
                    </div>
                </div>
        `;

        if (recent.length > 0) {
            html += `
                <!-- Recent Searches -->
                <div class="pt-3 border-top mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="bi bi-clock-history text-primary" style="font-size: 0.9rem;"></i>
                            <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Recent Searches</span>
                        </div>
                        <button type="button" class="btn btn-link p-0 text-muted small text-decoration-none fw-semibold" style="font-size: 0.76rem;" id="clear-all-recent-btn">
                            Clear All
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5">
                        ${recent.map(term => `
                            <span class="search-chip pe-2">
                                <span onclick="applySearchTerm('${escapeJs(term)}')">${escapeHtml(term)}</span>
                                <i class="bi bi-x ms-1 text-muted opacity-75" onclick="removeSingleRecent(event, '${escapeJs(term)}')" style="font-size: 1rem; cursor: pointer;"></i>
                            </span>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        html += `
                <!-- Quick Category Discovery -->
                <div class="pt-3 border-top">
                    <div class="d-flex align-items-center gap-1.5 mb-2">
                        <i class="bi bi-grid-fill text-indigo" style="font-size: 0.88rem; color: #6366f1;"></i>
                        <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Explore Categories</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5">
                        <a href="{{ route('category.products', 'mobiles') }}" class="search-chip"><i class="bi bi-phone"></i> Mobiles</a>
                        <a href="{{ route('category.products', 'electronics') }}" class="search-chip"><i class="bi bi-laptop"></i> Electronics</a>
                        <a href="{{ route('category.products', 'fashion') }}" class="search-chip"><i class="bi bi-bag"></i> Fashion</a>
                        <a href="{{ route('category.products', 'sports') }}" class="search-chip"><i class="bi bi-trophy"></i> Sports</a>
                        <a href="{{ route('category.products', 'grocery') }}" class="search-chip"><i class="bi bi-basket"></i> Grocery</a>
                    </div>
                </div>
            </div>
        `;

        box.innerHTML = html;
        box.classList.remove('d-none');

        const clearBtn = box.querySelector('#clear-all-recent-btn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
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
                    closeAllSearchBoxes();
                    return;
                }
                renderEmptyState(box, input, form);
            });
        }

        form.addEventListener('submit', function (e) {
            const val = input.value.trim();
            const catInp = form.querySelector('input[name="category[]"]');
            const catVal = catInp ? catInp.value.trim() : '';

            // Nothing typed and no category selected:
            // Do NOT reload or navigate! Stay on that page only.
            if (!val && !catVal) {
                e.preventDefault();
                input.focus();
                return;
            }

            // Category selected but text is empty:
            // Submit category filter cleanly without empty ?q=
            if (!val && catVal) {
                input.removeAttribute('name');
                return;
            }

            saveRecentSearch(val);
            if (typeof window.onHeaderSearchSubmit === 'function') {
                const handled = window.onHeaderSearchSubmit(val);
                if (handled) {
                    e.preventDefault();
                    closeAllSearchBoxes();
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
                    closeAllSearchBoxes();
                    return;
                }
            }

            if (q.length === 0) {
                renderEmptyState(box, input, form);
                return;
            }

            timer = setTimeout(() => {
                const catInp = form.querySelector('input[name="category[]"]');
                const catVal = catInp ? catInp.value.trim() : '';
                let fetchUrl = `{{ route('shop') }}?q=${encodeURIComponent(q)}&suggestion=1`;
                if (catVal) {
                    fetchUrl += `&category%5B%5D=${encodeURIComponent(catVal)}`;
                }

                fetch(fetchUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    const totalCount = data.total_count || 0;
                    const products   = data.products || [];
                    const categories = data.categories || [];
                    const scopes     = data.category_scopes || [];
                    const brand      = data.brand_spotlight || null;
                    const querySuggestions = data.suggestions || [];

                    if (products.length === 0 && categories.length === 0 && !brand && querySuggestions.length === 0) {
                        box.innerHTML = `
                            <div class="p-4 text-center">
                                <i class="bi bi-search display-6 text-muted opacity-25 mb-2 d-block"></i>
                                <div class="fw-bold text-dark mb-1">No products found for "${escapeHtml(q)}"</div>
                                <div class="text-muted small">Try checking your spelling or use general keywords like "mobiles", "samsung", "t-shirt".</div>
                            </div>
                        `;
                    } else {
                        let html = '<div class="py-2.5 px-3 px-md-4 bg-white">';

                        // 1. Brand Spotlight Card (If query matches an official brand)
                        if (brand) {
                            html += `
                                <div class="p-2.5 mb-3 brand-spotlight-card d-flex align-items-center justify-content-between" onclick="window.location.href='${escapeJs(brand.url)}'">
                                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                                        <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 38px; height: 38px; overflow: hidden;">
                                            ${brand.logo_url 
                                                ? `<img src="${brand.logo_url}" style="width: 100%; height: 100%; object-fit: contain;">` 
                                                : `<i class="bi bi-patch-check-fill text-primary fs-5"></i>`}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size: 0.88rem;">
                                                <span>${escapeHtml(brand.name)} Official Store</span>
                                                <span class="badge bg-primary text-white" style="font-size: 9px; padding: 2px 6px;">VERIFIED</span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.76rem; line-height: 1.25;">Explore authentic ${escapeHtml(brand.name)} collections & exclusive deals</div>
                                        </div>
                                    </div>
                                    <span class="text-primary fw-semibold small d-flex align-items-center gap-1 flex-shrink-0 ms-2" style="font-size: 0.8rem;">
                                        Visit Store <i class="bi bi-arrow-right"></i>
                                    </span>
                                </div>
                            `;
                        }

                        // 2. Category Filter Chips (If category matches exist)
                        if (categories.length > 0) {
                            html += `
                                <div class="mb-3">
                                    <div class="small fw-bold text-muted text-uppercase mb-1.5" style="font-size: 0.7rem; letter-spacing: 0.5px;">Categories Matching "${escapeHtml(q)}"</div>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        ${categories.map(c => `
                                            <a href="${c.url}" class="search-chip">
                                                <i class="bi bi-grid-fill text-primary" style="font-size: 0.78rem;"></i> ${escapeHtml(c.name)}
                                            </a>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        // 3. Balanced Two-Column Layout (50/50 Desktop Split)
                        html += '<div class="row g-3">';

                        // Left Column: Direct Search, Categories & Keyword Matches (No cutting off with ...)
                        html += '<div class="col-12 col-md-6 border-end-0 border-md-end pe-md-3">';
                        
                        // Direct Query Row
                        html += `
                            <div class="search-query-row d-flex align-items-center gap-2 mb-1" onclick="applySearchTerm('${escapeJs(q)}')">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary flex-shrink-0" style="width: 28px; height: 28px;">
                                    <i class="bi bi-search" style="font-size: 0.8rem;"></i>
                                </div>
                                <div class="min-w-0 flex-grow-1" style="font-size: 0.88rem; line-height: 1.3;">
                                    <span>Search for <strong class="text-primary">"${escapeHtml(q)}"</strong></span>
                                </div>
                                <i class="bi bi-arrow-up-right text-muted small flex-shrink-0"></i>
                            </div>
                        `;

                        // In-Category Scope Rows (Fully readable, no ...)
                        if (scopes.length > 0) {
                            scopes.forEach(sc => {
                                html += `
                                    <div class="search-query-row d-flex align-items-center gap-2.5 mb-1" onclick="window.location.href='${escapeJs(sc.url)}'">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-light text-secondary flex-shrink-0" style="width: 30px; height: 30px;">
                                            <i class="bi bi-funnel" style="font-size: 0.82rem;"></i>
                                        </div>
                                        <div class="min-w-0 flex-grow-1" style="font-size: 0.86rem; line-height: 1.35;">
                                            <span>${escapeHtml(sc.text)}</span> <span class="text-muted" style="font-size: 0.78rem;">in</span> <strong class="text-dark">${escapeHtml(sc.category_name)}</strong>
                                        </div>
                                        <i class="bi bi-arrow-right text-muted small flex-shrink-0"></i>
                                    </div>
                                `;
                            });
                        }

                        // Query keyword autocompletions (Full product name visible, NO text-truncate dots ...)
                        if (querySuggestions.length > 0) {
                            querySuggestions.slice(0, 4).forEach(sText => {
                                if (sText.toLowerCase() !== q.toLowerCase()) {
                                    html += `
                                        <div class="search-query-row d-flex align-items-center gap-2.5 mb-0.5" onclick="applySearchTerm('${escapeJs(sText)}')">
                                            <i class="bi bi-search text-muted opacity-50 ms-1.5 me-1" style="font-size: 0.78rem;"></i>
                                            <div class="flex-grow-1" style="font-size: 0.86rem; color: #334155; line-height: 1.35;">
                                                ${highlightMatch(sText, q)}
                                            </div>
                                        </div>
                                    `;
                                }
                            });
                        }

                        html += '</div>'; // End Left Column

                        // Right Column: Instant Product Cards with Real Photos & Prices
                        html += '<div class="col-12 col-md-6 ps-md-3">';
                        html += `
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                <span class="small fw-bold text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                    Instant Product Matches
                                </span>
                                <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.7rem;">${products.length} of ${totalCount}</span>
                            </div>
                        `;

                        if (products.length > 0) {
                            html += '<div class="d-flex flex-column gap-1">';
                            products.forEach(p => {
                                const sellingPriceRounded = Math.round(p.selling_price);
                                const mrpRounded = Math.round(p.mrp);
                                const sellingPriceFormatted = sellingPriceRounded.toLocaleString('en-IN');
                                const mrpFormatted = mrpRounded.toLocaleString('en-IN');
                                const hasDiscount = p.discount_percent > 0 && mrpRounded > sellingPriceRounded;
                                const priceHtml = hasDiscount 
                                    ? `<span class="fw-bold text-dark" style="font-size: 0.88rem;">₹${sellingPriceFormatted}</span>
                                       <span class="text-muted text-decoration-line-through small ms-1" style="font-size: 0.75rem;">₹${mrpFormatted}</span>
                                       <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.68rem; font-weight: 700; padding: 2px 6px;">${p.discount_percent}% OFF</span>`
                                    : `<span class="fw-bold text-dark" style="font-size: 0.88rem;">₹${sellingPriceFormatted}</span>`;

                                html += `
                                    <a href="${escapeJs(p.url)}" class="preview-product-card d-flex align-items-center gap-2.5" onclick="saveRecentSearch('${escapeJs(p.name)}')">
                                        <img src="${escapeHtml(p.image_url)}" alt="${escapeHtml(p.name)}" onerror="this.src='/images/product-placeholder.png'" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; flex-shrink: 0; background: #f8fafc; border: 1px solid #e2e8f0;">
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="fw-semibold text-dark mb-0.5" style="font-size: 0.85rem; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="${escapeHtml(p.name)}">
                                                ${highlightMatch(p.name, q)}
                                            </div>
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                ${priceHtml}
                                            </div>
                                        </div>
                                    </a>
                                `;
                            });
                            html += '</div>';
                        } else {
                            html += `<div class="text-muted small py-2">No direct product matches. Check query suggestions.</div>`;
                        }

                        html += '</div>'; // End Right Column
                        html += '</div>'; // End Row
                        html += '</div>'; // End Container

                        // 4. View All Results Footer
                        html += `
                            <div class="py-2.5 px-3 px-md-4 text-center cursor-pointer suggestion-footer-btn d-flex align-items-center justify-content-center gap-2 border-top" onclick="applySearchTerm('${escapeJs(q)}')">
                                <span class="fw-semibold text-primary" style="font-size: 0.9rem;">
                                    View all <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 mx-1" style="font-weight: 700;">${totalCount}</span> results for "${escapeHtml(q)}"
                                </span>
                                <i class="bi bi-arrow-right fs-6 text-primary fw-bold footer-arrow-icon"></i>
                            </div>
                        `;

                        box.innerHTML = html;
                    }

                    box.classList.remove('d-none');
                })
                .catch(() => box.classList.add('d-none'));
            }, 220);
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
                closeAllSearchBoxes();
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
        const catInp = document.getElementById('searchCategoryInput');
        const catVal = catInp ? catInp.value.trim() : '';
        let targetUrl = `{{ route('shop') }}?q=${encodeURIComponent(term)}`;
        if (catVal) {
            targetUrl += `&category%5B%5D=${encodeURIComponent(catVal)}`;
        }
        window.location.href = targetUrl;
    };

    window.removeSingleRecent = function (event, term) {
        event.stopPropagation();
        removeRecentSearch(term);
        const desktopBox = document.getElementById('searchSuggestions');
        const mobileBox  = document.getElementById('mobileSearchSuggestions');
        if (desktopBox && !desktopBox.classList.contains('d-none')) {
            renderEmptyState(desktopBox, document.getElementById('mainSearchInput'), document.getElementById('mainSearchForm'));
        }
        if (mobileBox && !mobileBox.classList.contains('d-none')) {
            renderEmptyState(mobileBox, document.getElementById('mobileSearchInput'), document.getElementById('mobileSearchForm'));
        }
    };

    // Amazon-Style Voice Search Modal & Engine (Desktop & Mobile)
    let activeSpeechRec = null;
    let isVoiceListening = false;

    function getAmazonVoiceModal() {
        const modalEl = document.getElementById('amazonVoiceModal');
        if (!modalEl) return null;
        if (window.bootstrap && window.bootstrap.Modal) {
            return window.bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        return null;
    }

    function showAmazonVoiceModal() {
        const modalEl = document.getElementById('amazonVoiceModal');
        const bsModal = getAmazonVoiceModal();
        if (bsModal) {
            bsModal.show();
        } else if (modalEl) {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }

    function hideAmazonVoiceModal() {
        const modalEl = document.getElementById('amazonVoiceModal');
        const bsModal = getAmazonVoiceModal();
        if (bsModal) {
            bsModal.hide();
        } else if (modalEl) {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
        }
    }

    function setAmazonVoiceState(state) {
        const heading = document.getElementById('amazonVoiceHeading');
        const subheading = document.getElementById('amazonVoiceSubheading');
        const transcript = document.getElementById('amazonVoiceTranscript');
        const circleBtn = document.getElementById('amazonVoiceCircleBtn');
        const circleIcon = document.getElementById('amazonVoiceCircleIcon');
        const wave1 = document.getElementById('voiceWave1');
        const wave2 = document.getElementById('voiceWave2');
        const actionArea = document.getElementById('amazonVoiceActionArea');

        if (state === 'listening') {
            isVoiceListening = true;
            if (heading) heading.textContent = 'Listening...';
            if (subheading) subheading.textContent = 'Speak into your microphone now';
            if (transcript) transcript.innerHTML = '<span class="text-muted fst-italic">"..."</span>';
            if (circleBtn) {
                circleBtn.style.background = 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)';
                circleBtn.style.boxShadow = '0 10px 25px -5px rgba(14, 165, 233, 0.4)';
            }
            if (circleIcon) circleIcon.className = 'bi bi-mic-fill fs-2';
            if (wave1) wave1.style.display = 'block';
            if (wave2) wave2.style.display = 'block';
            if (actionArea) actionArea.classList.add('d-none');
        } else if (state === 'searching') {
            isVoiceListening = false;
            if (heading) heading.textContent = 'Searching...';
            if (subheading) subheading.textContent = 'Finding matches for you';
            if (circleBtn) {
                circleBtn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                circleBtn.style.boxShadow = '0 10px 25px -5px rgba(16, 185, 129, 0.4)';
            }
            if (circleIcon) circleIcon.className = 'bi bi-check-lg fs-2';
            if (wave1) wave1.style.display = 'none';
            if (wave2) wave2.style.display = 'none';
            if (actionArea) actionArea.classList.add('d-none');
        } else if (state === 'no-speech') {
            isVoiceListening = false;
            if (heading) heading.textContent = "Didn't catch that";
            if (subheading) subheading.textContent = 'Tap the microphone to try speaking again';
            if (transcript) transcript.innerHTML = '<span class="text-muted small">No voice detected</span>';
            if (circleBtn) {
                circleBtn.style.background = 'linear-gradient(135deg, #64748b 0%, #475569 100%)';
                circleBtn.style.boxShadow = '0 10px 25px -5px rgba(100, 116, 139, 0.3)';
            }
            if (circleIcon) circleIcon.className = 'bi bi-arrow-clockwise fs-2';
            if (wave1) wave1.style.display = 'none';
            if (wave2) wave2.style.display = 'none';
            if (actionArea) actionArea.classList.add('d-none');
        } else if (state === 'blocked') {
            isVoiceListening = false;
            if (heading) heading.textContent = 'Microphone Access Needed';
            if (subheading) subheading.innerHTML = 'Please tap <strong>Allow</strong> when your browser prompts, or tap the 🔒 icon in the address bar to enable microphone.';
            if (transcript) transcript.innerHTML = '<span class="text-warning small fw-semibold"><i class="bi bi-shield-lock me-1"></i>Microphone permission required</span>';
            if (circleBtn) {
                circleBtn.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                circleBtn.style.boxShadow = '0 10px 25px -5px rgba(245, 158, 11, 0.4)';
            }
            if (circleIcon) circleIcon.className = 'bi bi-mic-mute-fill fs-2';
            if (wave1) wave1.style.display = 'none';
            if (wave2) wave2.style.display = 'none';
            if (actionArea) actionArea.classList.remove('d-none');
        } else if (state === 'unsupported') {
            isVoiceListening = false;
            if (heading) heading.textContent = 'Voice Search Not Supported';
            if (subheading) subheading.textContent = 'Your current browser does not support voice search. Please try Google Chrome or Microsoft Edge.';
            if (transcript) transcript.innerHTML = '<span class="text-danger small">Browser unsupported</span>';
            if (circleBtn) {
                circleBtn.style.background = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)';
                circleBtn.style.boxShadow = 'none';
            }
            if (circleIcon) circleIcon.className = 'bi bi-x-circle fs-2';
            if (wave1) wave1.style.display = 'none';
            if (wave2) wave2.style.display = 'none';
            if (actionArea) actionArea.classList.add('d-none');
        }
    }

    async function startAmazonVoiceListening() {
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRec) {
            showAmazonVoiceModal();
            setAmazonVoiceState('unsupported');
            return;
        }

        // Show Amazon Modal in listening state immediately for responsive feedback
        showAmazonVoiceModal();
        setAmazonVoiceState('listening');

        // Stop any previous active speech session
        if (activeSpeechRec) {
            try { activeSpeechRec.abort(); } catch (e) {}
            activeSpeechRec = null;
        }

        // Request browser/OS microphone permission via getUserMedia
        // This is standard across Chrome mobile & desktop to invoke the native OS/browser permission prompt
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                // Immediately release raw media stream so SpeechRecognition has exclusive audio input
                stream.getTracks().forEach(t => t.stop());
            } catch (permErr) {
                console.warn('Microphone permission not granted:', permErr);
                setAmazonVoiceState('blocked');
                return;
            }
        }

        try {
            // Fresh SpeechRecognition instance
            const recognition = new SpeechRec();
            activeSpeechRec = recognition;
            recognition.continuous = false;
            recognition.interimResults = true;
            recognition.maxAlternatives = 1;
            recognition.lang = navigator.language || 'en-IN';

            let finalTranscript = '';
            let transcriptEl = document.getElementById('amazonVoiceTranscript');

            recognition.onstart = function () {
                setAmazonVoiceState('listening');
            };

            recognition.onresult = function (event) {
                let interim = '';
                for (let i = event.resultIndex; i < event.results.length; ++i) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    } else {
                        interim += event.results[i][0].transcript;
                    }
                }

                const currentText = finalTranscript || interim;
                if (currentText && transcriptEl) {
                    transcriptEl.innerHTML = `<span class="text-primary fw-bold">"${escapeHtml(currentText)}"</span>`;
                }

                // If final sentence is ready
                if (finalTranscript.trim().length > 0) {
                    const cleanVal = finalTranscript.trim();
                    setAmazonVoiceState('searching');
                    if (transcriptEl) {
                        transcriptEl.innerHTML = `<span class="text-success fw-bold">"${escapeHtml(cleanVal)}"</span>`;
                    }

                    // Fill search inputs on desktop and mobile
                    const dInp = document.getElementById('mainSearchInput');
                    const mInp = document.getElementById('mobileSearchInput');
                    if (dInp) dInp.value = cleanVal;
                    if (mInp) mInp.value = cleanVal;

                    setTimeout(() => {
                        hideAmazonVoiceModal();
                        window.applySearchTerm(cleanVal);
                    }, 450);
                }
            };

            recognition.onerror = function (event) {
                console.warn('SpeechRecognition error:', event.error);
                if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                    setAmazonVoiceState('blocked');
                } else if (event.error === 'no-speech') {
                    setAmazonVoiceState('no-speech');
                } else {
                    setAmazonVoiceState('no-speech');
                }
            };

            recognition.onend = function () {
                if (isVoiceListening && !finalTranscript.trim()) {
                    setAmazonVoiceState('no-speech');
                }
                activeSpeechRec = null;
            };

            recognition.start();
        } catch (e) {
            console.error('Speech recognition exception:', e);
            setAmazonVoiceState('no-speech');
        }
    }

    // Bind Voice Search buttons (Desktop & Mobile)
    document.querySelectorAll('.btn-voice-search').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            startAmazonVoiceListening();
        });
    });

    // Tap mic circle inside modal to speak or retry
    const circleBtn = document.getElementById('amazonVoiceCircleBtn');
    if (circleBtn) {
        circleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            startAmazonVoiceListening();
        });
    }

    // Blocked retry / allow button
    const retryPermissionBtn = document.getElementById('btnVoiceRetryPermission');
    if (retryPermissionBtn) {
        retryPermissionBtn.addEventListener('click', function (e) {
            e.preventDefault();
            startAmazonVoiceListening();
        });
    }

    // Modal Close Cleanup
    const amazonModalEl = document.getElementById('amazonVoiceModal');
    if (amazonModalEl) {
        amazonModalEl.addEventListener('hidden.bs.modal', function () {
            if (activeSpeechRec) {
                try { activeSpeechRec.abort(); } catch (e) {}
                activeSpeechRec = null;
            }
            isVoiceListening = false;
        });
        const closeBtn = document.getElementById('btnAmazonVoiceClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                hideAmazonVoiceModal();
                if (activeSpeechRec) {
                    try { activeSpeechRec.abort(); } catch (e) {}
                    activeSpeechRec = null;
                }
                isVoiceListening = false;
            });
        }
    }

    window.applyVoiceQuickChip = function(phrase) {
        hideAmazonVoiceModal();
        const dInp = document.getElementById('mainSearchInput');
        const mInp = document.getElementById('mobileSearchInput');
        if (dInp) dInp.value = phrase;
        if (mInp) mInp.value = phrase;
        window.applySearchTerm(phrase);
    };

    // Custom Category Scope Dropdown Selection (Approach A)
    window.selectSearchCategory = function (id, name, url, btn) {
        const label = document.getElementById('searchCategoryLabel');
        const input = document.getElementById('searchCategoryInput');
        const mainInp = document.getElementById('mainSearchInput');
        
        if (label) label.textContent = name || 'All';
        if (input) input.value = id || '';

        // Update active class in menu
        const menu = btn ? btn.closest('.dropdown-menu') : null;
        if (menu && btn) {
            menu.querySelectorAll('.dropdown-item').forEach(item => {
                item.classList.remove('active', 'fw-bold');
                const check = item.querySelector('.cat-check-icon');
                if (check) check.remove();
            });
            btn.classList.add('active', 'fw-bold');
            btn.insertAdjacentHTML('beforeend', '<i class="bi bi-check2 text-primary fw-bold fs-6 cat-check-icon"></i>');
        }

        // APPROACH A:
        const queryText = mainInp ? mainInp.value.trim() : '';
        if (!queryText) {
            // Search input is empty -> navigate immediately to category's product page or shop
            if (url) {
                window.location.href = url;
            }
            return;
        }

        // Search input has text -> stay on page, scope search & re-dispatch live suggestions
        if (mainInp) {
            mainInp.focus();
            mainInp.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    // Initialize both Desktop and Mobile search bars
    setupSearch('mainSearchInput', 'mainSearchForm', 'searchSuggestions');
    setupSearch('mobileSearchInput', 'mobileSearchForm', 'mobileSearchSuggestions');
})();
</script>

<!-- Amazon-Style Voice Search Modal (Desktop & Mobile) -->
<div class="modal fade" id="amazonVoiceModal" tabindex="-1" aria-labelledby="amazonVoiceModalLabel" aria-hidden="true" style="z-index: 1099;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden text-center p-4 position-relative" style="background: #ffffff;">
            <!-- Top Right Close Button -->
            <button type="button" class="btn-close position-absolute top-0 end-0 m-3 shadow-none" data-bs-dismiss="modal" aria-label="Close" id="btnAmazonVoiceClose"></button>

            <!-- Brand Header -->
            <div class="d-flex align-items-center justify-content-center gap-1.5 mb-2 text-muted small" style="font-size: 0.72rem; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 700;">
                <i class="bi bi-mic text-primary"></i> Voice Search
            </div>

            <!-- Pulsing Mic Animation Area -->
            <div class="amazon-voice-wave-wrapper mx-auto my-2 position-relative" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center;">
                <div class="voice-wave-ring ring-1" id="voiceWave1"></div>
                <div class="voice-wave-ring ring-2" id="voiceWave2"></div>
                <button type="button" class="btn p-0 border-0 shadow-lg position-relative d-flex align-items-center justify-content-center text-white" 
                        id="amazonVoiceCircleBtn" 
                        style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); z-index: 3; transition: transform 0.2s, background 0.2s;" 
                        title="Tap to speak">
                    <i class="bi bi-mic-fill fs-2" id="amazonVoiceCircleIcon"></i>
                </button>
            </div>

            <!-- Title & Status -->
            <h5 class="fw-bold text-dark mb-1 mt-1" id="amazonVoiceHeading">Listening...</h5>
            <p class="text-muted small mb-3" id="amazonVoiceSubheading" style="font-size: 0.82rem; min-height: 20px;">Speak into your microphone</p>

            <!-- Live Spoken Words Transcript Card -->
            <div class="p-3 rounded-3 border mb-3 d-flex align-items-center justify-content-center text-center" 
                 style="min-height: 72px; border-color: #e2e8f0 !important; background: #f8fafc !important;">
                <span class="fs-5 fw-semibold text-break" id="amazonVoiceTranscript" style="color: #0f172a;">"..."</span>
            </div>

            <!-- Blocked State Action Area (Hidden by default, shown if permission blocked) -->
            <div id="amazonVoiceActionArea" class="d-none mb-3">
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-xs d-inline-flex align-items-center gap-2" id="btnVoiceRetryPermission" style="font-size: 0.88rem;">
                    <i class="bi bi-mic-fill"></i> <span>Allow Microphone</span>
                </button>
                <div class="text-muted mt-2" style="font-size: 0.72rem; line-height: 1.4;">
                    If blocked: tap <strong>🔒</strong> in address bar &rarr; <strong>Permissions</strong> &rarr; turn on <strong>Microphone</strong>
                </div>
            </div>

            <!-- Suggestion Quick Pills -->
            <div class="pt-2 border-top">
                <div class="text-muted small mb-1.5" style="font-size: 0.72rem;">Try saying:</div>
                <div class="d-flex align-items-center justify-content-center flex-wrap gap-1.5">
                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="cursor: pointer; font-size: 0.74rem;" onclick="applyVoiceQuickChip('Mobiles')">Mobiles</span>
                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="cursor: pointer; font-size: 0.74rem;" onclick="applyVoiceQuickChip('Samsung Galaxy')">Samsung Galaxy</span>
                    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1" style="cursor: pointer; font-size: 0.74rem;" onclick="applyVoiceQuickChip('Running Shoes')">Running Shoes</span>
                </div>
            </div>
        </div>
    </div>
</div>

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
