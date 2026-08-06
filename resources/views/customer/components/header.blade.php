@php
    $liveMegaSale = app(\App\Services\OfferService::class)->getLiveMegaSale();
@endphp

@if($liveMegaSale)
<div class="py-2 text-white text-center position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, {{ $liveMegaSale->theme_color ?? '#7c3aed' }} 0%, #0f172a 100%); font-size: 0.88rem; z-index: 1050;">
    <div class="container d-flex align-items-center justify-content-center flex-wrap gap-2">
        <span class="badge bg-warning text-dark fw-bold text-uppercase px-2.5 py-1 rounded-pill me-1">
            {{ $liveMegaSale->badge_text ?? '🔥 MEGA SALE' }}
        </span>
        <strong class="fw-bolder">{{ $liveMegaSale->title }} IS LIVE!</strong>
        <span class="d-none d-md-inline opacity-90">{{ $liveMegaSale->description ?? 'Special discounts across all categories' }}</span>
        
        @if($liveMegaSale->end_time)
            <div class="d-inline-flex align-items-center gap-1.5 bg-dark text-white border border-warning border-opacity-50 px-3 py-1 rounded-pill fw-bold ms-md-2 shadow-sm" style="background-color: #0f172a !important; font-family: monospace; letter-spacing: 0.5px;">
                <i class="bi bi-clock-fill text-warning me-1"></i>
                <span id="mega-sale-timer" class="text-warning fw-bolder" data-endtime="{{ $liveMegaSale->end_time->toISOString() }}">Ends Soon</span>
            </div>
        @endif
        
        <a href="{{ route('offers.index') }}" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3 py-1 ms-2 shadow-sm text-decoration-none">
            Shop Deals <i class="bi bi-arrow-right small"></i>
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

<header class="header-main bg-white shadow-sm sticky-top">
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex align-items-center gap-3 py-3">

            <!-- Left: Menu + Logo -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <button class="btn btn-outline-primary border-2 rounded-pill px-2 px-sm-3 py-2 fw-bold d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#siteMenu" aria-controls="siteMenu">
                    <i class="bi bi-list fs-5"></i>
                    <span class="d-none d-sm-inline">Menu</span>
                </button>
                <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center flex-shrink-0 ms-1">
                    <x-logo height="34" />
                </a>
            </div>

            <!-- Home, Shop & Offers Links (desktop only) -->
            <nav class="d-none d-lg-flex align-items-center gap-3 flex-shrink-0">
                <a href="{{ route('home') }}" class="nav-link fw-semibold px-1 {{ request()->routeIs('home') ? 'text-primary' : 'text-dark' }}">Home</a>
                <a href="{{ route('shop') }}" class="nav-link fw-semibold px-1 {{ request()->routeIs('shop') ? 'text-primary' : 'text-dark' }}">Shop</a>
                <a href="{{ route('offers.index') }}" class="nav-link fw-semibold px-1 {{ request()->routeIs('offers.*') ? 'text-primary' : 'text-dark' }}"><i class="bi bi-fire text-warning me-1"></i> Offers</a>
            </nav>

            <!-- Center: Search Bar -->
            <form action="{{ route('shop') }}" method="GET" class="flex-grow-1" id="mainSearchForm" autocomplete="off">
                <div class="input-group" style="height: 40px; max-width: 540px; margin: 0 auto;">
                    <input
                        type="text"
                        name="q"
                        id="mainSearchInput"
                        class="form-control border-2 border-end-0 shadow-none"
                        placeholder="Search products, brands…"
                        value="{{ request('q') }}"
                        style="border-color: #3b82f6; border-radius: 8px 0 0 8px; font-size: 0.875rem;"
                        autocomplete="off"
                    >
                    <button class="btn btn-primary px-3 d-flex align-items-center gap-1" type="submit" style="border-radius: 0 8px 8px 0; font-size: 0.875rem;">
                        <i class="bi bi-search"></i>
                        <span class="d-none d-sm-inline fw-semibold">Search</span>
                    </button>
                </div>

                <!-- Live Suggestions Dropdown -->
                <div id="searchSuggestions" class="position-absolute bg-white border rounded-3 shadow-lg mt-1 d-none" style="z-index: 1060; top: 100%; left: 0; right: 0; max-width: 540px; margin: 0 auto; max-height: 360px; overflow-y: auto;">
                </div>
            </form>

            <!-- Right: Wishlist + Cart + Profile -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">

                <!-- Wishlist (logged in only) -->
                @auth('customer')
                    <a href="{{ route('wishlist.index') }}"
                       class="btn btn-light border-0 rounded-circle p-0 d-flex align-items-center justify-content-center position-relative flex-shrink-0"
                       style="width: 40px; height: 40px;" title="Wishlist">
                        <i class="bi bi-heart fs-5"></i>
                        @if(isset($wishlistedProductIds) && count($wishlistedProductIds) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; min-width: 17px; padding: 3px 4px;">
                                {{ count($wishlistedProductIds) }}
                            </span>
                        @endif
                    </a>
                @endauth

                <!-- Cart -->
                <a href="{{ route('cart.index') }}"
                   class="btn btn-light border-0 rounded-circle p-0 d-flex align-items-center justify-content-center position-relative flex-shrink-0"
                   style="width: 40px; height: 40px;" title="Cart">
                    <i class="bi bi-bag fs-5"></i>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary cart-badge-count" style="font-size: 9px; min-width: 17px; padding: 3px 4px; display: {{ (isset($cartItemCount) && $cartItemCount > 0) ? 'inline-block' : 'none' }};">
                        {{ $cartItemCount ?? 0 }}
                    </span>
                </a>

                <!-- Profile / Sign In -->
                @auth('customer')
                    <div class="dropdown">
                        <a href="#" class="btn btn-light border-0 rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0"
                           style="width: 40px; height: 40px;" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
                            <i class="bi bi-person-circle fs-5"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 py-2 mt-2" style="min-width: 200px;">
                            <li><h6 class="dropdown-header fw-bold text-primary">{{ Auth::guard('customer')->user()->name }}</h6></li>
                            <li><a class="dropdown-item py-2" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-2 text-muted"></i>Dashboard</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('account.orders.index') }}"><i class="bi bi-box-seam me-2 text-muted"></i>My Orders</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}"><i class="bi bi-person-gear me-2 text-muted"></i>Profile</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary rounded-pill px-3 px-md-4 fw-bold shadow-sm flex-shrink-0" style="white-space: nowrap; font-size: 0.875rem;">Sign In</a>
                @endauth

            </div>
        </div>
    </div>
</header>

<!-- Slide-out Menu (Offcanvas Drawer) -->
<div class="offcanvas offcanvas-start border-0 shadow-lg" tabindex="-1" id="siteMenu" aria-labelledby="siteMenuLabel" style="width: 320px;">
    <div class="offcanvas-header p-4 border-bottom bg-light">
        <div class="d-flex align-items-center">
            <x-logo height="28" />
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-4 d-flex flex-column justify-content-between">
        <div class="menu-links">
            <h6 class="text-uppercase small text-muted fw-bold mb-3">Navigation</h6>
            <ul class="nav flex-column gap-2 mb-4">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link py-2 px-3 rounded-3 fw-bold {{ request()->routeIs('home') ? 'bg-primary text-white' : 'text-dark bg-light-subtle' }}">
                        <i class="bi bi-house-door me-2"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('shop') }}" class="nav-link py-2 px-3 rounded-3 fw-bold {{ request()->routeIs('shop') ? 'bg-primary text-white' : 'text-dark bg-light-subtle' }}">
                        <i class="bi bi-shop me-2"></i> Shop Store
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cart.index') }}" class="nav-link py-2 px-3 rounded-3 fw-bold text-dark bg-light-subtle">
                        <i class="bi bi-bag me-2"></i> Shopping Cart
                    </a>
                </li>
                @auth('customer')
                <li class="nav-item">
                    <a href="{{ route('wishlist.index') }}" class="nav-link py-2 px-3 rounded-3 fw-bold text-dark bg-light-subtle">
                        <i class="bi bi-heart me-2"></i> Wishlist
                    </a>
                </li>
                @endauth
            </ul>

            <!-- Categories -->
            <div class="mb-3">
                <button class="btn btn-light-subtle w-100 text-start py-2 px-3 rounded-3 fw-bold text-dark d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#menuCategoriesCollapse" aria-expanded="true">
                    <span><i class="bi bi-grid-fill me-2 text-primary"></i>Categories</span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <div class="collapse show mt-2 ps-3" id="menuCategoriesCollapse">
                    @foreach($menuCategories ?? [] as $cat)
                        <a href="{{ route('category.products', $cat->slug) }}" class="d-block py-1 text-secondary text-decoration-none small"><i class="bi bi-tag me-2"></i>{{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>

            <!-- Brands -->
            <div class="mb-4">
                <button class="btn btn-light-subtle w-100 text-start py-2 px-3 rounded-3 fw-bold text-dark d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#menuBrandsCollapse" aria-expanded="true">
                    <span><i class="bi bi-award-fill me-2 text-primary"></i>Brands</span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <div class="collapse show mt-2 ps-3" id="menuBrandsCollapse">
                    @foreach($menuBrands ?? [] as $brand)
                        <a href="{{ route('brand.products', $brand->slug) }}" class="d-block py-1 text-secondary text-decoration-none small"><i class="bi bi-patch-check me-2"></i>{{ $brand->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="menu-account-footer pt-3 border-top">
            @auth('customer')
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <small class="text-muted d-block">Signed in as</small>
                        <strong class="text-dark">{{ Auth::guard('customer')->user()->name }}</strong>
                    </div>
                    <a href="{{ route('account.orders.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Account</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm py-2"><i class="bi bi-person-fill me-2"></i>Sign In / Register</a>
            @endauth
        </div>
    </div>
</div>

<style>
    .header-main { border-bottom: 1px solid #e2e8f0; }
    #mainSearchForm { position: relative; }
    #mainSearchInput:focus { box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
    #searchSuggestions .suggestion-item {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 14px; cursor: pointer; transition: background 0.15s;
        border-bottom: 1px solid #f1f5f9; font-size: 0.875rem; color: #1e293b;
    }
    #searchSuggestions .suggestion-item:last-child { border-bottom: none; }
    #searchSuggestions .suggestion-item:hover { background: #f8fafc; }
    #searchSuggestions .suggestion-item i { color: #94a3b8; flex-shrink: 0; }
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
                    box.innerHTML = '<div class="p-3 text-muted text-center small">No results found</div>';
                } else {
                    box.innerHTML = data.suggestions.map(s =>
                        `<div class="suggestion-item" onclick="window.location='${s.url}'">
                            <i class="bi bi-search small"></i>
                            <span>${s.name}</span>
                            <small class="text-muted ms-auto text-nowrap">${s.category}</small>
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
