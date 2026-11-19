<form action="{{ route('shop') }}" method="GET" class="search-header w-100" autocomplete="off">
    <div class="amazon-search-container d-flex align-items-stretch rounded-3 overflow-hidden border" style="height: 42px; background: #ffffff; border-color: #cbd5e1 !important;">
        <input type="text" class="form-control border-0 shadow-none px-3 py-0 search-core-input" name="q" placeholder="Search products, brands and categories..." value="{{ request('q') ?? request('search') }}" style="font-size: 0.88rem; font-weight: 500; height: 100%;">
        <button class="btn amazon-search-btn d-flex align-items-center justify-content-center flex-shrink-0 px-3.5 border-0" type="submit" style="background: #febd69; color: #111827;">
            <i class="bi bi-search fs-6 fw-bold"></i>
        </button>
    </div>
</form>
