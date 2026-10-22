@if(isset($exploreProducts) && $exploreProducts->isNotEmpty())
<section id="explore-more-section" class="py-3 py-md-5" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
    <div class="container px-2 px-md-4">
        
        {{-- Section Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <div>
                <div class="mb-1 d-none d-md-block">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1" 
                          style="background: rgba(99, 102, 241, 0.12); color: #6366f1; font-size: 0.72rem; letter-spacing: 0.05em;">
                        <i class="bi bi-compass-fill me-1"></i> More To Explore
                    </span>
                </div>
                <h4 class="fw-bolder mb-0 text-dark" style="letter-spacing: -0.02em; font-size: clamp(1.15rem, 2.5vw, 1.6rem);">
                    Explore More Products
                </h4>
            </div>
            
            <a href="{{ route('shop') }}" class="btn btn-sm btn-white border rounded-pill px-3 py-1.5 fw-semibold text-primary shadow-xs d-inline-flex align-items-center gap-1 hover-elevate" style="background: #ffffff; font-size: 0.8rem; border-color: #cbd5e1 !important;">
                <span>View Full Catalog</span> <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        {{-- Dynamic Product Grid --}}
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2 g-md-3" id="home-explore-grid">
            @foreach($exploreProducts as $product)
                <div class="col d-flex">
                    @include('customer.components.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>

        {{-- Set Loading Action Bar --}}
        @php
            $initialCount = $exploreProducts->count();
            $totalCount = $totalExploreCount ?? $initialCount;
        @endphp

        <div id="home-load-more-wrap" class="mt-4 pt-2 text-center">
            @if($totalCount > $initialCount)
                <div class="d-flex flex-column align-items-center gap-2">
                    <button type="button" id="homeLoadMoreBtn" 
                            data-page="2" 
                            data-url="{{ route('home.explore-more') }}"
                            class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-bold shadow-xs hover-elevate d-inline-flex align-items-center gap-2 transition-all"
                            style="font-size: 0.88rem; border-color: #cbd5e1; background: #ffffff;">
                        <span class="btn-text">Load More Products</span>
                        <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>
                    </button>
                    <span class="text-muted small" id="home-progress-text" style="font-size: 0.76rem;">
                        Showing <span id="home-showing-count">{{ $initialCount }}</span> of <span id="home-total-count">{{ $totalCount }}</span> products
                    </span>
                </div>
            @else
                <div class="text-muted small py-2">
                    <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                        <i class="bi bi-check2-all text-success me-1"></i> You've explored all products
                    </span>
                </div>
            @endif
        </div>

    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const homeLoadMoreBtn = document.getElementById('homeLoadMoreBtn');
    if (homeLoadMoreBtn) {
        homeLoadMoreBtn.addEventListener('click', function() {
            const btn = this;
            const page = parseInt(btn.getAttribute('data-page') || '2', 10);
            const url = btn.getAttribute('data-url');
            const originalHtml = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span> Loading products...`;

            fetch(`${url}?page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed to load products');
                return res.json();
            })
            .then(data => {
                const grid = document.getElementById('home-explore-grid');
                if (grid && data.html) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const newCols = Array.from(tempDiv.children);

                    newCols.forEach(col => {
                        col.style.opacity = '0';
                        col.style.transform = 'translateY(12px)';
                        col.style.transition = 'all 0.35s ease';
                        grid.appendChild(col);
                        requestAnimationFrame(() => {
                            col.style.opacity = '1';
                            col.style.transform = 'translateY(0)';
                        });
                    });
                }

                const wrap = document.getElementById('home-load-more-wrap');
                if (data.hasMore) {
                    btn.setAttribute('data-page', data.nextPage);
                    btn.disabled = false;
                    btn.innerHTML = `<span class="btn-text">Load More Products</span> <i class="bi bi-chevron-down" style="font-size: 0.8rem;"></i>`;
                    const showingSpan = document.getElementById('home-showing-count');
                    if (showingSpan) showingSpan.textContent = data.loadedCount;
                } else {
                    if (wrap) {
                        wrap.innerHTML = `
                            <div class="text-muted small py-2">
                                <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                                    <i class="bi bi-check2-all text-success me-1"></i> You've explored all ${data.total} products
                                </span>
                            </div>
                        `;
                    }
                }
            })
            .catch(err => {
                console.error('Error loading explore products:', err);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
        });
    }
});
</script>
@endpush
@endif
