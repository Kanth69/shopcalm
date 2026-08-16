@php
    $liveMegaSale = app(\App\Services\OfferService::class)->getLiveMegaSale();
@endphp

@if($liveMegaSale)
<div class="py-2 text-white text-center position-relative overflow-hidden" style="background: linear-gradient(135deg, {{ $liveMegaSale->theme_color ?? '#6366f1' }} 0%, #0f172a 100%); font-size: 0.88rem; z-index: 1050;">
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
<header class="header-main bg-white border-bottom sticky-top py-2">
    <div class="container-fluid px-3 px-lg-5">
        <div class="d-flex align-items-center justify-content-between gap-4 py-1">

            <!-- Left: Menu Trigger + Logo + Nav Links -->
            <div class="d-flex align-items-center gap-4 flex-shrink-0">
                <!-- Simple Clean Menu Button -->
                <button class="btn btn-light border-0 rounded-circle p-2 d-flex align-items-center justify-content-center text-dark menu-trigger-btn" 
                        type="button" data-bs-toggle="offcanvas" data-bs-target="#siteMenu" aria-controls="siteMenu" title="Open Menu" style="width: 40px; height: 40px;">
                    <i class="bi bi-list fs-4"></i>
                </button>

                <!-- Brand Logo -->
                <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center flex-shrink-0">
                    <x-logo height="32" />
                </a>

                <!-- Desktop Primary Links -->
                <nav class="d-none d-lg-flex align-items-center gap-4 ms-2">
                    <a href="{{ route('home') }}" class="nav-clean-link {{ request()->routeIs('home') ? 'active text-primary' : 'text-dark' }}">Home</a>
                    <a href="{{ route('shop') }}" class="nav-clean-link {{ request()->routeIs('shop') ? 'active text-primary' : 'text-dark' }}">Shop</a>
                    <a href="{{ route('offers.index') }}" class="nav-clean-link {{ request()->routeIs('offers.*') ? 'active text-primary' : 'text-dark' }}">Offers</a>
                </nav>
            </div>

            <!-- Center: Clean Search Bar -->
            <div class="flex-grow-1 d-none d-md-block" style="max-width: 460px;">
                <form action="{{ route('shop') }}" method="GET" class="position-relative" id="mainSearchForm" autocomplete="off">
                    <div class="input-group search-input-group">
                        <span class="input-group-text bg-light border-0 ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            name="q"
                            id="mainSearchInput"
                            class="form-control bg-light border-0 shadow-none ps-2 py-2"
                            placeholder="Search products, brands…"
                            value="{{ request('q') }}"
                            style="font-size: 0.88rem;"
                            autocomplete="off"
                        >
                    </div>

                    <!-- Live Suggestions Dropdown -->
                    <div id="searchSuggestions" class="position-absolute bg-white border rounded-3 shadow-lg mt-2 d-none overflow-hidden" 
                         style="z-index: 1060; top: 100%; left: 0; right: 0; max-height: 360px; overflow-y: auto;">
                    </div>
                </form>
            </div>

            <!-- Right Actions: Wishlist + Cart + Profile Avatar (with generous gaps) -->
            <div class="d-flex align-items-center gap-3 gap-lg-4 flex-shrink-0 ms-2">

                <!-- Wishlist Icon -->
                @auth('customer')
                    <a href="{{ route('wishlist.index') }}"
                       class="text-dark text-decoration-none position-relative p-2 header-icon-link d-flex align-items-center justify-content-center"
                       title="Wishlist">
                        <i class="bi bi-heart fs-5"></i>
                        @if(isset($wishlistedProductIds) && count($wishlistedProductIds) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; min-width: 17px; padding: 2px 4px;">
                                {{ count($wishlistedProductIds) }}
                            </span>
                        @endif
                    </a>
                @endauth

                <!-- Cart Icon -->
                <a href="{{ route('cart.index') }}"
                   class="text-dark text-decoration-none position-relative p-2 header-icon-link d-flex align-items-center justify-content-center"
                   title="Shopping Cart">
                    <i class="bi bi-bag fs-5"></i>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary cart-badge-count" 
                          style="font-size: 9px; min-width: 17px; padding: 2px 4px; display: {{ (isset($cartItemCount) && $cartItemCount > 0) ? 'inline-block' : 'none' }};">
                        {{ $cartItemCount ?? 0 }}
                    </span>
                </a>

                <!-- Profile Avatar Dropdown / Sign In Button (with generous left separation) -->
                <div class="ps-3 border-start border-light-subtle">
                    @auth('customer')
                        @php
                            $userObj = Auth::guard('customer')->user();
                            $firstInitial = strtoupper(substr($userObj->name ?? 'C', 0, 1));
                        @endphp
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm avatar-circle" 
                                     style="width: 38px; height: 38px; background: #0d6efd; font-size: 0.95rem;">
                                    {{ $firstInitial }}
                                </div>
                                <i class="bi bi-chevron-down text-muted small d-none d-sm-inline" style="font-size: 0.7rem;"></i>
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
                        <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold" style="font-size: 0.85rem;">
                            Sign In
                        </a>
                    @endauth
                </div>

            </div>
        </div>

        <!-- Mobile Search Bar -->
        <div class="d-block d-md-none mt-2 pt-1 pb-1">
            <form action="{{ route('shop') }}" method="GET" class="position-relative">
                <div class="input-group search-input-group">
                    <span class="input-group-text bg-light border-0 ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control bg-light border-0 shadow-none ps-2 py-1.5" placeholder="Search products, brands…" value="{{ request('q') }}" style="font-size: 0.88rem;">
                </div>
            </form>
        </div>
    </div>
</header>

<!-- Minimalist Clean Slide-Out Menu Drawer -->
<div class="offcanvas offcanvas-start border-0 shadow" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel" style="width: 320px;">
    <!-- Drawer Header -->
    <div class="offcanvas-header px-4 py-3.5 border-bottom d-flex align-items-center justify-content-between">
        <x-logo height="28" />
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <!-- Drawer Content -->
    <div class="offcanvas-body px-4 py-3.5 d-flex flex-column justify-content-between">
        <div>
            <!-- Main Navigation Links -->
            <div class="mb-4">
                <div class="text-uppercase small text-muted fw-bold mb-2.5 px-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Navigation</div>
                <div class="d-flex flex-column gap-1.5">
                    <a href="{{ route('home') }}" class="drawer-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="bi bi-house drawer-icon me-3"></i> <span>Home</span>
                    </a>
                    <a href="{{ route('shop') }}" class="drawer-link {{ request()->routeIs('shop') ? 'active' : '' }}">
                        <i class="bi bi-grid drawer-icon me-3"></i> <span>Shop All Products</span>
                    </a>
                    <a href="{{ route('offers.index') }}" class="drawer-link {{ request()->routeIs('offers.*') ? 'active' : '' }}">
                        <i class="bi bi-percent drawer-icon me-3 text-danger"></i> <span>Special Offers & Deals</span>
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
                        <span class="badge bg-light text-dark border font-monospace">{{ count($wishlistedProductIds ?? []) }}</span>
                    </a>
                    @endauth
                </div>
            </div>

            <!-- Categories Dropdown Accordion (Hidden by default, shown on click) -->
            <div class="mb-3">
                <a href="#drawerCategoriesCollapse" class="drawer-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="drawerCategoriesCollapse">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-tags drawer-icon me-3 text-primary"></i> <span>Categories</span>
                    </div>
                    <i class="bi bi-chevron-down small text-muted chevron-rotate"></i>
                </a>
                <div class="collapse mt-1 ps-4" id="drawerCategoriesCollapse">
                    <div class="d-flex flex-column gap-1.5 py-1 border-start ps-3 ms-2">
                        @forelse($menuCategories ?? [] as $cat)
                            <a href="{{ route('category.products', $cat->slug) }}" class="drawer-sublink">
                                {{ $cat->name }}
                            </a>
                        @empty
                            <span class="drawer-sublink text-muted">No categories</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Brands Dropdown Accordion (Hidden by default, shown on click) -->
            <div class="mb-4">
                <a href="#drawerBrandsCollapse" class="drawer-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="drawerBrandsCollapse">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-award drawer-icon me-3 text-primary"></i> <span>Brands</span>
                    </div>
                    <i class="bi bi-chevron-down small text-muted chevron-rotate"></i>
                </a>
                <div class="collapse mt-1 ps-4" id="drawerBrandsCollapse">
                    <div class="d-flex flex-column gap-1.5 py-1 border-start ps-3 ms-2">
                        @forelse($menuBrands ?? [] as $brand)
                            <a href="{{ route('brand.products', $brand->slug) }}" class="drawer-sublink">
                                {{ $brand->name }}
                            </a>
                        @empty
                            <span class="drawer-sublink text-muted">No brands</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Support & Legal Links with generous spacing -->
            <div class="pt-4 border-top mt-4 mb-4">
                <div class="text-uppercase small text-muted fw-bold mb-3 px-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Help & Information</div>
                <div class="d-flex flex-column gap-2 px-1">
                    <a href="{{ url('/about-us') }}" class="drawer-sublink py-1.5">
                        <i class="bi bi-info-circle drawer-icon me-3 text-muted"></i> <span>About Us</span>
                    </a>
                    <a href="{{ url('/contact-us') }}" class="drawer-sublink py-1.5">
                        <i class="bi bi-headset drawer-icon me-3 text-muted"></i> <span>Contact & Support</span>
                    </a>
                    <a href="{{ url('/faq') }}" class="drawer-sublink py-1.5">
                        <i class="bi bi-question-circle drawer-icon me-3 text-muted"></i> <span>FAQs</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Drawer Footer: Single Clean Sign In button for guest, or Account for logged in with generous top margin -->
        <div class="pt-4 border-top mt-4 pb-2">
            @auth('customer')
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark small">{{ Auth::guard('customer')->user()->name }}</div>
                        <a href="{{ route('dashboard') }}" class="text-primary text-decoration-none small" style="font-size: 0.78rem;">Account Dashboard &rarr;</a>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">Logout</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary rounded-pill w-100 py-2.5 fw-semibold shadow-xs">
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
        font-size: 1.1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .drawer-link {
        display: flex;
        align-items: center;
        padding: 0.6rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.92rem;
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
        display: block;
        padding: 0.35rem 0.75rem;
        font-size: 0.88rem;
        color: #64748b;
        text-decoration: none;
        transition: color 0.15s, transform 0.15s;
    }
    .drawer-sublink:hover {
        color: #0d6efd;
        transform: translateX(3px);
    }

    #searchSuggestions .suggestion-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        cursor: pointer;
        transition: background 0.15s;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.88rem;
        color: #1e293b;
    }
    #searchSuggestions .suggestion-item:last-child {
        border-bottom: none;
    }
    #searchSuggestions .suggestion-item:hover {
        background: #f8fafc;
    }
</style>

<script>
(function () {
    const input = document.getElementById('mainSearchInput');
    const form  = document.getElementById('mainSearchForm');
    const box   = document.getElementById('searchSuggestions');
    let timer;

    if (!input) return;

    input.addEventListener('input', function () {
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { box.classList.add('d-none'); box.innerHTML = ''; return; }

        timer = setTimeout(() => {
            fetch(`{{ route('shop') }}?q=${encodeURIComponent(q)}&suggestion=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (!data.suggestions || data.suggestions.length === 0) {
                    box.innerHTML = '<div class="p-3 text-muted text-center small">No matching products found</div>';
                } else {
                    box.innerHTML = data.suggestions.map(s =>
                        `<div class="suggestion-item" onclick="window.location='${s.url}'">
                            <i class="bi bi-search small text-primary"></i>
                            <span class="fw-semibold">${s.name}</span>
                            <small class="text-muted ms-auto text-nowrap badge bg-light border">${s.category}</small>
                        </div>`
                    ).join('');
                }
                box.classList.remove('d-none');
            })
            .catch(() => box.classList.add('d-none'));
        }, 280);
    });

    input.addEventListener('focus', function () {
        if (this.value.trim().length >= 2) box.classList.remove('d-none');
    });

    document.addEventListener('click', function (e) {
        if (!form.contains(e.target)) box.classList.add('d-none');
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { box.classList.add('d-none'); this.blur(); }
    });
})();
</script>
