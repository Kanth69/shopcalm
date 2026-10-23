@php
    $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
    $faqData = json_decode($page->content, true) ?: [];
    $faqs = $faqData['faqs'] ?? [];
@endphp

@section('title', 'FAQs - Frequently Asked Questions | ' . $storeName)
@section('meta_description', 'Find quick answers to common questions about orders, shipping, payments, returns, and wallet balance at ' . $storeName . '.')

@push('styles')
<style>
    /* FAQ Page Modern Clean Styling */
    .faq-container {
        max-width: 780px;
    }
    
    .faq-header-wrap {
        padding: 0.25rem 0.5rem 0.5rem;
        text-align: center;
    }

    /* Category Filter Pills */
    .faq-pill-btn {
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.38rem 0.85rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        user-select: none;
    }
    .faq-pill-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .faq-pill-btn.active {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #ffffff !important;
        border-color: transparent;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.28);
    }
    .faq-pill-btn.active i {
        color: #ffffff !important;
    }

    /* Accordion items */
    .faq-acc-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px !important;
        margin-bottom: 0.6rem;
        overflow: hidden;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .faq-acc-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .faq-acc-btn {
        padding: 0.85rem 1rem;
        font-size: 0.92rem;
        font-weight: 600;
        color: #0f172a;
        background: #ffffff;
        border: none;
        box-shadow: none !important;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .faq-acc-btn:not(.collapsed) {
        color: #4f46e5;
        background: #faf5ff;
        border-bottom: 1px solid #f1f5f9;
    }
    .faq-acc-btn::after {
        background-size: 0.8rem;
        transition: transform 0.2s ease;
    }
    .faq-acc-body {
        padding: 0.75rem 1rem 0.95rem;
        font-size: 0.86rem;
        line-height: 1.65;
        color: #334155;
        background: #ffffff;
    }
    .faq-acc-body p {
        margin-bottom: 0.45rem;
    }
    .faq-acc-body p:last-child {
        margin-bottom: 0;
    }
    .faq-acc-body a {
        color: #6366f1;
        text-decoration: underline;
    }

    /* Mobile specific adjustments */
    @media (max-width: 575.98px) {
        .faq-container {
            padding-bottom: 75px !important;
        }
        .faq-header-wrap {
            padding: 0.25rem 0.25rem 0.4rem;
        }
        .faq-acc-btn {
            padding: 0.75rem 0.85rem;
            font-size: 0.86rem;
        }
        .faq-acc-body {
            padding: 0.65rem 0.85rem 0.85rem;
            font-size: 0.82rem;
            line-height: 1.6;
        }
        .faq-pill-btn {
            font-size: 0.74rem;
            padding: 0.32rem 0.75rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-1 py-md-3 faq-container">

    {{-- 1. Compact Header (No Over-Text) --}}
    <div class="faq-header-wrap mb-2 mb-md-3">
        <h3 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.02em; font-size: clamp(1.2rem, 3.5vw, 1.7rem);">
            Frequently Asked <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Questions</span>
        </h3>
        <p class="text-muted small mb-0" style="font-size: clamp(0.78rem, 2vw, 0.86rem);">
            Quick answers about orders, delivery, returns & payments
        </p>
    </div>

    {{-- 2. Compact Search Bar --}}
    <div class="card border-0 shadow-2xs rounded-4 p-2.5 p-sm-3 mb-2.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="input-group input-group-sm rounded-pill overflow-hidden border" style="background: #f8fafc; border-color: #cbd5e1 !important;">
            <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-primary">
                <i class="bi bi-search" style="font-size: 0.85rem;"></i>
            </span>
            <input type="text" id="faqSearchInput" class="form-control bg-transparent border-0 shadow-none ps-1 py-1 text-dark" 
                   placeholder="Search questions (e.g. tracking, delivery, refund)..." style="font-size: 0.84rem;">
            <button type="button" id="faqSearchClear" class="btn btn-sm btn-link text-muted pe-3 text-decoration-none d-none" title="Clear">
                <i class="bi bi-x-circle-fill" style="font-size: 0.85rem;"></i>
            </button>
        </div>

        {{-- 3. Touch Friendly Category Filter Pills (Horizontal swipeable on mobile) --}}
        <div class="d-flex align-items-center gap-1.5 overflow-x-auto pt-2 mt-1 border-top" style="-webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
            <button type="button" class="faq-pill-btn active" data-category="all">
                <span>All ({{ count($faqs) }})</span>
            </button>
            <button type="button" class="faq-pill-btn" data-category="Orders & Tracking">
                <i class="bi bi-box-seam text-primary"></i> <span>Orders</span>
            </button>
            <button type="button" class="faq-pill-btn" data-category="Shipping & Delivery">
                <i class="bi bi-truck text-info"></i> <span>Delivery</span>
            </button>
            <button type="button" class="faq-pill-btn" data-category="Returns & Quality">
                <i class="bi bi-shield-check text-success"></i> <span>Returns</span>
            </button>
            <button type="button" class="faq-pill-btn" data-category="Payments & COD">
                <i class="bi bi-credit-card text-warning"></i> <span>Payments</span>
            </button>
        </div>
    </div>

    {{-- 4. FAQ Accordion List (All collapsed by default so everything is visible) --}}
    <div class="accordion" id="faqAccordion">
        @forelse($faqs as $idx => $item)
            <div class="faq-acc-item faq-item-block" id="faq-item-{{ $idx }}" data-category="{{ $item['category'] ?? '' }}">
                <h2 class="accordion-header m-0">
                    <button class="accordion-button faq-acc-btn collapsed" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#faq-collapse-{{ $idx }}" 
                            aria-expanded="false">
                        <div class="d-flex align-items-center gap-2 overflow-hidden pe-2">
                            <i class="bi {{ $item['icon'] ?? 'bi-question-circle' }} text-primary flex-shrink-0" style="font-size: 0.92rem;"></i>
                            <span class="text-dark fw-semibold" style="font-size: clamp(0.85rem, 2.5vw, 0.92rem); line-height: 1.35;">{{ $item['question'] ?? 'Question' }}</span>
                        </div>
                    </button>
                </h2>
                <div id="faq-collapse-{{ $idx }}" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="faq-acc-body">
                        {!! $item['answer'] ?? '' !!}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted small">
                <i class="bi bi-question-circle fs-3 text-muted opacity-50 mb-1 d-block"></i>
                No questions available.
            </div>
        @endforelse

        {{-- No Search Results Found --}}
        <div id="faqNoResults" class="card border-0 rounded-4 p-4 text-center my-2" style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1 !important;">
            <i class="bi bi-search text-muted fs-3 mb-1"></i>
            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">No matching questions</h6>
            <p class="text-muted small mb-2.5" style="font-size: 0.8rem;">Try searching for <em>order, refund, or delivery</em>.</p>
            <div>
                <button type="button" id="faqResetBtn" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> View All Questions
                </button>
            </div>
        </div>
    </div>

    {{-- 5. Compact Support Card at Bottom --}}
    <div class="card border-0 rounded-4 p-3 p-md-3.5 mt-3 text-center" style="background: linear-gradient(135deg, #f8fafc 0%, #ede9fe 100%); border: 1px solid #e0e7ff !important;">
        <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
            <div class="text-center text-sm-start">
                <h6 class="fw-bold text-dark mb-0.5" style="font-size: 0.92rem;">Still have questions?</h6>
                <div class="text-muted small" style="font-size: 0.78rem;">Our customer support team is always ready to assist you.</div>
            </div>
            <a href="{{ route('page.contact') }}" class="btn btn-primary rounded-pill px-3 py-1.5 fw-bold btn-sm shadow-xs d-inline-flex align-items-center gap-1 flex-shrink-0" style="font-size: 0.8rem;">
                <i class="bi bi-chat-dots"></i> <span>Contact Us</span>
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearchInput');
    const searchClear = document.getElementById('faqSearchClear');
    const filterPills = document.querySelectorAll('.faq-pill-btn');
    const items = document.querySelectorAll('.faq-item-block');
    const noResultsEl = document.getElementById('faqNoResults');
    const resetBtn = document.getElementById('faqResetBtn');
    let activeCategory = 'all';

    function applyFAQFilters() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visibleCount = 0;

        if (searchClear) {
            searchClear.classList.toggle('d-none', query === '');
        }

        items.forEach(function(item) {
            const itemText = item.textContent.toLowerCase();
            const itemCat = (item.getAttribute('data-category') || '').trim();

            const matchesSearch = query === '' || itemText.includes(query);
            const matchesCat = activeCategory === 'all' || itemCat.toLowerCase() === activeCategory.toLowerCase();

            if (matchesSearch && matchesCat) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        if (noResultsEl) {
            noResultsEl.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFAQFilters);
    }

    if (searchClear) {
        searchClear.addEventListener('click', function() {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                applyFAQFilters();
            }
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            activeCategory = 'all';
            filterPills.forEach(p => p.classList.remove('active'));
            const allBtn = document.querySelector('.faq-pill-btn[data-category="all"]');
            if (allBtn) allBtn.classList.add('active');
            applyFAQFilters();
        });
    }

    if (filterPills.length > 0) {
        filterPills.forEach(pill => {
            pill.addEventListener('click', function() {
                filterPills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                activeCategory = this.getAttribute('data-category') || 'all';
                applyFAQFilters();
            });
        });
    }
});
</script>
@endpush
