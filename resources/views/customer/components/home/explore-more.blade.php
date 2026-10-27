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
            $hasMoreExplore = $totalCount > $initialCount;
        @endphp

        <div id="home-load-more-wrap" class="mt-3 pt-2 text-center" 
             data-has-more="{{ $hasMoreExplore ? '1' : '0' }}" 
             data-page="2" 
             data-url="{{ route('home.explore-more') }}">
            @if($hasMoreExplore)
                <div id="home-infinite-loader" class="py-4 text-center">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <span class="text-muted small fw-semibold">Loading more products...</span>
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
    let isHomeLoading = false;
    let homeObserver = null;
    const wrap = document.getElementById('home-load-more-wrap');
    const footer = document.getElementById('site-main-footer');
    const newsletter = document.getElementById('home-newsletter-section');

    // Amazon Experience: Keep footer & newsletter hidden while more products are streaming
    const hasMoreInitially = wrap && wrap.getAttribute('data-has-more') === '1';
    if (hasMoreInitially) {
        if (footer) footer.style.display = 'none';
        if (newsletter) newsletter.style.display = 'none';
    }

    function triggerHomeExploreLoad() {
        if (!wrap || isHomeLoading) return;
        if (wrap.getAttribute('data-has-more') !== '1') return;

        const page = parseInt(wrap.getAttribute('data-page') || '2', 10);
        const url = wrap.getAttribute('data-url');
        if (!url) return;

        isHomeLoading = true;
        const loader = document.getElementById('home-infinite-loader');
        if (loader) loader.classList.remove('d-none');

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

            if (data.hasMore) {
                wrap.setAttribute('data-page', data.nextPage);
                isHomeLoading = false;
            } else {
                isHomeLoading = false;
                wrap.setAttribute('data-has-more', '0');
                if (homeObserver) {
                    homeObserver.disconnect();
                    homeObserver = null;
                }
                wrap.innerHTML = `
                    <div class="text-muted small py-3">
                        <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill" style="font-size: 0.78rem;">
                            <i class="bi bi-check2-all text-success me-1"></i> You've explored all ${data.total} products
                        </span>
                    </div>
                `;

                // All products explored: reveal newsletter and footer seamlessly
                if (newsletter) {
                    newsletter.style.display = '';
                    newsletter.style.opacity = '0';
                    newsletter.style.transition = 'opacity 0.4s ease';
                    setTimeout(() => newsletter.style.opacity = '1', 50);
                }
                if (footer) {
                    footer.style.display = '';
                    footer.style.opacity = '0';
                    footer.style.transition = 'opacity 0.4s ease';
                    setTimeout(() => footer.style.opacity = '1', 50);
                }
            }
        })
        .catch(err => {
            console.error('Error loading explore products:', err);
            isHomeLoading = false;
        });
    }

    // Amazon-Style Auto Prefetch: triggers 1000px before user hits bottom so products load seamlessly
    function initHomeInfiniteScroll() {
        if (!wrap || wrap.getAttribute('data-has-more') !== '1') return;

        if (homeObserver) {
            homeObserver.disconnect();
            homeObserver = null;
        }

        if ('IntersectionObserver' in window) {
            homeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !isHomeLoading) {
                        triggerHomeExploreLoad();
                    }
                });
            }, {
                root: null,
                rootMargin: '1000px',
                threshold: 0.01
            });
            homeObserver.observe(wrap);
        }
    }

    // High-performance scroll listener fallback
    window.addEventListener('scroll', function() {
        if (isHomeLoading) return;
        if (!wrap || wrap.getAttribute('data-has-more') !== '1') return;

        const rect = wrap.getBoundingClientRect();
        if (rect.top <= window.innerHeight + 800) {
            triggerHomeExploreLoad();
        }
    }, { passive: true });

    initHomeInfiniteScroll();
});
</script>
@endpush
@endif
