@extends('layouts.customer')

@if($page->slug === 'about-us')
    @include('pages.about_corporate', ['page' => $page])
@else

@php
    $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
@endphp

@section('title', $page->meta_title ?? $page->title . ' - ' . $storeName)
@section('meta_description', $page->meta_description)

@php
    $legalPages = [
        'terms-and-conditions'  => ['title' => 'Terms & Conditions', 'icon' => 'bi-file-earmark-text', 'desc' => 'Rules, policies, and guidelines for using our store services.'],
        'privacy-policy'        => ['title' => 'Privacy Policy',      'icon' => 'bi-shield-lock',       'desc' => 'How we securely collect, protect, and handle your data.'],
        'shipping-policy'       => ['title' => 'Shipping Policy',     'icon' => 'bi-truck',             'desc' => 'Delivery timelines, courier partners, and tracking info.'],
        'return-refund-policy'  => ['title' => 'Return Policy',     'icon' => 'bi-arrow-left-right',  'desc' => 'Strict No Return & No Replacement policy guidelines.'],
        'cancellation-policy'   => ['title' => 'Cancellation Policy', 'icon' => 'bi-x-circle',          'desc' => 'Order cancellation terms and refund turnaround.'],
    ];

    $isLegal = array_key_exists($page->slug, $legalPages);
    $isFaq = $page->slug === 'faq';
@endphp

@push('styles')
<style>
    /* Reading Progress Bar */
    .reading-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        background: linear-gradient(90deg, #6366f1, #8b5cf6, #3b82f6);
        z-index: 1060;
        width: 0%;
        transition: width 0.1s ease;
    }

    /* Clean Minimalist Hero */
    .policy-hero-clean {
        padding: clamp(1.25rem, 3.5vw, 2.75rem) 0.5rem clamp(1rem, 2.5vw, 1.75rem);
        text-align: center;
        position: relative;
    }

    /* Policy Navigation Pills */
    .policy-nav-pill {
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0.5rem 1.1rem;
        border-radius: 999px;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(.4,0,.2,1);
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .policy-nav-pill:hover {
        background: #f5f3ff;
        color: #4f46e5;
        border-color: #c7d2fe;
        transform: translateY(-1px);
    }
    .policy-nav-pill.active {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff !important;
        border-color: transparent;
        box-shadow: 0 4px 14px rgba(99,102,241,0.32);
    }

    /* Print Policy Button with Premium Hover */
    .btn-print-policy {
        background: #ffffff;
        color: #334155;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        padding: 0.45rem 1.15rem;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(.4,0,.2,1);
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .btn-print-policy:hover {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff !important;
        border-color: transparent;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99,102,241,0.3);
    }
    .btn-print-policy:active {
        transform: translateY(0);
    }

    /* Section Cards (High contrast & calibrated padding) */
    .policy-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 2.25rem 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .policy-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.04);
    }
    .policy-card-title {
        font-size: 1.22rem;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.15rem;
        letter-spacing: -0.02em;
    }
    .policy-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        background: linear-gradient(135deg, #ede9fe, #e0e7ff);
        color: #4f46e5;
        border: 1px solid #c7d2fe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.92rem;
        font-weight: 800;
        flex-shrink: 0;
    }
    .policy-card-body {
        color: #1e293b;
        font-size: 0.95rem;
        line-height: 1.8;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .policy-card-body p {
        margin-bottom: 0.8rem;
    }
    .policy-card-body p:last-child {
        margin-bottom: 0;
    }
    .policy-card-body ul, .policy-card-body ol {
        padding-left: 1.25rem;
        margin-bottom: 0.8rem;
    }
    .policy-card-body li {
        margin-bottom: 0.4rem;
    }
    .policy-card-body table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }
    .policy-card-body table th,
    .policy-card-body table td {
        padding: 0.65rem 0.85rem;
        border: 1px solid #e2e8f0;
        font-size: 0.88rem;
    }
    .policy-card-body table th {
        background: #f8fafc;
        font-weight: 700;
        color: #0f172a;
    }

    /* Sticky Table of Contents */
    .toc-sticky {
        position: sticky;
        top: 85px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.5rem 1.25rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .toc-link {
        font-size: 0.88rem;
        font-weight: 500;
        color: #334155;
        text-decoration: none;
        display: block;
        padding: 0.55rem 0.85rem;
        border-radius: 10px;
        transition: all 0.2s ease;
        line-height: 1.4;
        border-left: 3px solid transparent;
    }
    .toc-link:hover, .toc-link.active {
        color: #312e81 !important;
        background: #ede9fe !important;
        border-left: 3px solid #6366f1 !important;
        font-weight: 700;
        padding-left: 1rem;
    }

    /* FAQ Specific Styling */
    .faq-accordion-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px !important;
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .faq-accordion-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        transform: translateY(-1px);
    }
    .faq-accordion-button {
        font-size: 0.98rem;
        font-weight: 700;
        color: #0f172a;
        padding: 1.1rem 1.35rem;
        background: #ffffff;
        border: none;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }
    .faq-accordion-button:not(.collapsed) {
        color: #4f46e5;
        background: linear-gradient(135deg, #f8fafc 0%, #fdfcff 100%);
        border-left: 4px solid #6366f1;
    }
    .faq-accordion-body {
        padding: 0.4rem 1.35rem 1.25rem 1.35rem;
        color: #334155;
        font-size: 0.92rem;
        line-height: 1.75;
    }
    .faq-accordion-body p {
        margin-bottom: 0.65rem;
    }
    .faq-accordion-body p:last-child {
        margin-bottom: 0;
    }
    .faq-filter-chip {
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }
    .faq-filter-chip:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .faq-filter-chip.active {
        background: #6366f1;
        color: #ffffff;
        border-color: #6366f1;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
    }

    /* Mobile Table of Contents Chips */
    .mobile-section-chip {
        font-size: 0.76rem;
        font-weight: 600;
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #475569;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        transition: all 0.2s ease;
    }
    .mobile-section-chip:hover, .mobile-section-chip:active {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
    }
    .mobile-section-chip .chip-num {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ede9fe;
        color: #6366f1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 800;
    }

    /* Mobile Precision Responsive Overrides */
    @media (max-width: 991.98px) {
        .policy-card {
            padding: 1.5rem 1.25rem;
            border-radius: 16px;
        }
        .policy-hero-clean {
            padding: 1rem 0.25rem 1.25rem;
        }
    }

    @media (max-width: 575.98px) {
        .policy-card {
            padding: 1.15rem 1rem !important;
            border-radius: 14px !important;
            margin-bottom: 0.85rem !important;
        }
        .policy-card-title {
            font-size: 1.02rem !important;
            gap: 0.6rem !important;
            margin-bottom: 0.75rem !important;
        }
        .policy-icon-badge {
            width: 28px !important;
            height: 28px !important;
            font-size: 0.8rem !important;
            border-radius: 8px !important;
        }
        .policy-card-body {
            font-size: 0.88rem !important;
            line-height: 1.65 !important;
        }
        .policy-nav-pill {
            font-size: 0.76rem !important;
            padding: 0.38rem 0.85rem !important;
            gap: 0.35rem !important;
        }
        .faq-accordion-button {
            padding: 0.85rem 0.95rem !important;
            font-size: 0.9rem !important;
        }
        .faq-accordion-body {
            padding: 0.25rem 0.95rem 1rem 0.95rem !important;
            font-size: 0.85rem !important;
        }
        .faq-filter-chip {
            font-size: 0.74rem !important;
            padding: 0.32rem 0.75rem !important;
        }
    }
</style>
@endpush
@push('styles')
<style>
@media (max-width: 576px) {
    .policy-card-body { font-size: 0.78rem; line-height: 1.6; }
    .policy-card { padding: 1rem 0.8rem; }
    .policy-nav-pill { font-size: 0.7rem; padding: 0.35rem 0.8rem; }
}
</style>
@endpush

@section('content')

<!-- Reading Progress Bar -->
<div class="reading-progress-bar" id="readingProgressBar"></div>

<div class="container py-3 py-md-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Customer Service</span></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ $page->title }}</li>
        </ol>
    </nav>

    <!-- 1. Hero Header (Clean Minimalist Design with Color Accent) -->
    <div class="policy-hero-clean mb-3 mb-md-4">
        <div class="mx-auto" style="max-width: 760px;">
            <div class="mb-2 mb-md-3">
                <span class="badge rounded-pill px-3 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1.5 shadow-2xs" style="background: linear-gradient(135deg, #ede9fe, #e0e7ff); color: #4f46e5; font-size: 0.72rem; border: 1px solid #c7d2fe; letter-spacing: 0.05em;">
                    <i class="bi {{ $isLegal ? ($legalPages[$page->slug]['icon'] ?? 'bi-shield-check') : 'bi-question-circle' }}"></i>
                    <span>{{ $isFaq ? 'Help & FAQ Center' : 'Official Store Policy' }}</span>
                </span>
            </div>

            @php
                $titleWords = explode(' ', $page->title);
                $lastWord = array_pop($titleWords);
                $firstPart = implode(' ', $titleWords);
            @endphp
            <h1 class="fw-bolder mb-2 text-dark" style="letter-spacing: -0.03em; font-size: clamp(1.35rem, 4vw, 2.4rem); color: #0f172a; line-height: 1.25;">
                {{ $firstPart }} <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $lastWord }}</span>
            </h1>

            @php
                $pageContentDecoded = json_decode($page->content, true) ?: [];
                $heroDesc = $pageContentDecoded['desc'] ?? $pageContentDecoded['hero_subtitle'] ?? $page->meta_description ?? ($isLegal ? ($legalPages[$page->slug]['desc'] ?? 'Official guidelines and terms for ' . $storeName . ' customers.') : 'Frequently asked questions and quick assistance guide.');
            @endphp
            <p class="text-muted mb-3 mx-auto" style="line-height: 1.6; max-width: 640px; font-size: clamp(0.85rem, 2vw, 1.05rem) !important;">
                {{ $heroDesc }}
            </p>

            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                <div class="d-inline-flex align-items-center gap-1.5 bg-light px-2.5 py-1 rounded-pill border small text-muted" style="font-size: 0.72rem;">
                    <i class="bi bi-calendar-check text-primary"></i> 
                    <span>Last Updated: {{ $page->updated_at ? $page->updated_at->format('F d, Y') : date('F d, Y') }}</span>
                </div>

                <button type="button" onclick="window.print()" class="btn-print-policy d-none d-sm-inline-flex">
                    <i class="bi bi-printer"></i> 
                    <span>Print Policy</span>
                </button>
            </div>
        </div>
    </div>

    @if($isLegal)
    <!-- 2. Policy Switcher Tabs Bar (Touch friendly & Swipeable) -->
    <div class="d-flex align-items-center justify-content-start justify-content-md-center gap-1.5 overflow-x-auto pb-2.5 mb-3 mb-md-4 px-1" style="-webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
        @foreach($legalPages as $slugKey => $meta)
            <a href="{{ url('/' . $slugKey) }}" class="policy-nav-pill {{ $page->slug === $slugKey ? 'active' : '' }}">
                <i class="bi {{ $meta['icon'] }}"></i>
                <span>{{ $meta['title'] }}</span>
            </a>
        @endforeach
    </div>
    @endif

    <!-- 3. Content Layout -->
    @if($isFaq)
        @php 
            $faqData = json_decode($page->content, true) ?: []; 
            $faqs = $faqData['faqs'] ?? [];
        @endphp

        <!-- FAQ Search & Category Filter Section -->
        <div class="card border-0 shadow-2xs rounded-4 p-3 p-md-4 mb-4" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
            <div class="row g-2.5 align-items-center mb-3">
                <div class="col-12 col-md-8">
                    <div class="input-group rounded-pill overflow-hidden border p-1" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                        <span class="input-group-text bg-transparent border-0 ps-3 pe-2 text-primary" style="font-size: 1.05rem;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="faqSearchInput" class="form-control bg-transparent border-0 shadow-none ps-1 py-1 text-dark" 
                               placeholder="Search questions (shipping, refund, payment, tracking)..." style="font-size: 0.9rem;">
                        <button type="button" id="faqSearchClear" class="btn btn-sm btn-link text-muted pe-3 text-decoration-none d-none" title="Clear search">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-4 text-md-end text-muted small ps-2 ps-md-0">
                    Showing <strong id="faqCount" class="text-dark fw-bold">{{ count($faqs) }}</strong> questions
                </div>
            </div>

            <!-- Quick Topic Category Chips (Swipeable Horizontal Scroll) -->
            <div class="d-flex align-items-center gap-1.5 overflow-x-auto pb-1 pt-2 border-top no-scrollbar" style="-webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
                <button type="button" class="faq-filter-chip active" data-category="all">
                    <span>All Questions</span>
                </button>
                <button type="button" class="faq-filter-chip" data-category="Shipping & Delivery">
                    <i class="bi bi-truck text-primary"></i> <span>Shipping & Delivery</span>
                </button>
                <button type="button" class="faq-filter-chip" data-category="Returns & Quality">
                    <i class="bi bi-patch-check text-success"></i> <span>Returns & Quality</span>
                </button>
                <button type="button" class="faq-filter-chip" data-category="Orders & Tracking">
                    <i class="bi bi-box-seam text-info"></i> <span>Orders & Tracking</span>
                </button>
                <button type="button" class="faq-filter-chip" data-category="Payments & COD">
                    <i class="bi bi-credit-card-2-front text-warning"></i> <span>Payments & COD</span>
                </button>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="accordion" id="faqAccordion">
                    @forelse($faqs as $idx => $item)
                        <div class="faq-accordion-item faq-item-block" id="faq-item-{{ $idx }}" data-category="{{ $item['category'] ?? '' }}">
                            <h2 class="accordion-header">
                                <button class="accordion-button faq-accordion-button {{ $idx > 0 ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-{{ $idx }}" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}">
                                    <div class="d-flex align-items-center justify-content-between w-100 pe-2 gap-2">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs" 
                                                 style="width: 32px; height: 32px; background: #ede9fe; color: #6366f1; font-size: 0.95rem;">
                                                <i class="bi {{ $item['icon'] ?? 'bi-question-circle' }}"></i>
                                            </div>
                                            <span class="faq-question-text text-dark fw-bold text-start" style="font-size: clamp(0.9rem, 2.5vw, 1rem); line-height: 1.35;">{{ $item['question'] ?? 'Question' }}</span>
                                        </div>
                                        @if(!empty($item['category']))
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 fw-semibold flex-shrink-0 d-none d-sm-inline-block" style="font-size: 0.7rem;">
                                                {{ $item['category'] }}
                                            </span>
                                        @endif
                                    </div>
                                </button>
                            </h2>
                            <div id="faq-collapse-{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" data-bs-parent="#faqAccordion">
                                <div class="faq-accordion-body">
                                    <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #edf2f7; font-size: 0.9rem; line-height: 1.7;">
                                        {!! $item['answer'] ?? '' !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-question-circle fs-1 text-muted opacity-50 mb-2 d-block"></i>
                            <h6>No questions currently available.</h6>
                        </div>
                    @endforelse

                    <!-- No Search Results Found Block -->
                    <div id="faqNoResults" class="card border-0 shadow-xs rounded-4 p-5 text-center my-3" style="display: none; background: #f8fafc; border: 1px dashed #cbd5e1 !important;">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-2xs" style="width: 52px; height: 52px; color: #94a3b8;">
                            <i class="bi bi-search fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">No matching questions found</h5>
                        <p class="text-muted small mb-3">Try searching for other terms like <em>shipping, return, order, or payment</em>.</p>
                        <div>
                            <button type="button" id="faqResetBtn" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-semibold">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Filter & View All
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Still Have Questions Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mt-4 mt-md-5 text-center" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #e0e7ff 100%); border: 1px solid #ddd6fe !important;">
                    <div class="mx-auto" style="max-width: 580px;">
                        <div class="rounded-circle bg-white d-inline-flex align-items-center justify-content-center shadow-xs mb-3" style="width: 50px; height: 50px; color: #6366f1;">
                            <i class="bi bi-headset fs-3"></i>
                        </div>
                        <h4 class="fw-bolder text-dark mb-2" style="letter-spacing: -0.02em; font-size: clamp(1.2rem, 3vw, 1.5rem);">Still have questions?</h4>
                        <p class="text-muted small mb-3.5">Our customer support team is available 24/7 to assist you with any inquiries.</p>
                        <a href="{{ route('page.contact') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center" style="gap: 0.4rem !important; font-size: 0.88rem;">
                            <i class="bi bi-chat-dots"></i> <span>Contact Support Team</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @elseif($isLegal)
        @php
            $legalData = json_decode($page->content, true) ?: [];
            $intro = $legalData['intro'] ?? '';
            $sections = $legalData['sections'] ?? [];
        @endphp

        <!-- Mobile Table of Contents Chips (Touch friendly horizontal bar) -->
        @if(count($sections) > 1)
        <div class="d-block d-lg-none mb-3">
            <div class="d-flex align-items-center gap-1.5 overflow-x-auto pb-1 px-0.5" style="-webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none;">
                @if(!empty($intro))
                    <a href="#section-overview" class="mobile-section-chip">
                        <i class="bi bi-info-circle text-primary"></i> <span>Overview</span>
                    </a>
                @endif
                @foreach($sections as $i => $sec)
                    <a href="#section-{{ $i + 1 }}" class="mobile-section-chip">
                        <span class="chip-num">{{ $i + 1 }}</span>
                        <span>{{ $sec['title'] ?? 'Section ' . ($i + 1) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        <div class="row g-4 g-lg-5">
            <!-- Sidebar Table of Contents (Desktop) -->
            @if(count($sections) > 1)
            <div class="col-lg-4 d-none d-lg-block">
                <div class="toc-sticky">
                    <div class="d-flex align-items-center gap-2 pb-2.5 mb-2.5 border-bottom">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; background: #ede9fe; color: #6366f1;">
                            <i class="bi bi-list-nested"></i>
                        </div>
                        <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.06em; font-size: 0.76rem;">Table of Contents</span>
                    </div>
                    <nav class="d-flex flex-column gap-1" id="tocNav">
                        @if(!empty($intro))
                            <a href="#section-overview" class="toc-link">Overview & Scope</a>
                        @endif
                        @foreach($sections as $i => $sec)
                            <a href="#section-{{ $i + 1 }}" class="toc-link text-truncate">
                                {{ $sec['title'] ?? 'Section ' . ($i + 1) }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="pt-3 mt-3 border-top">
                        <a href="{{ route('page.contact') }}" class="btn btn-light border w-100 rounded-pill fw-semibold text-dark d-inline-flex align-items-center justify-content-center shadow-xs" style="font-size: 0.84rem; gap: 0.4rem !important; padding: 0.5rem 1rem;">
                            <i class="bi bi-question-circle text-primary"></i> <span>Policy Questions?</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Main Legal Content -->
            <div class="{{ count($sections) > 1 ? 'col-lg-8' : 'col-lg-10 mx-auto' }}">
                @if(!empty($intro))
                <div class="policy-card mb-4" id="section-overview" style="border-left: 5px solid #6366f1 !important; background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
                    <div class="policy-card-title">
                        <div class="policy-icon-badge">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <span>Overview & Applicability</span>
                    </div>
                    <div class="policy-card-body">
                        {!! $intro !!}
                    </div>
                </div>
                @endif

                @forelse($sections as $i => $sec)
                <div class="policy-card mb-4" id="section-{{ $i + 1 }}">
                    <div class="policy-card-title">
                        <div class="policy-icon-badge">
                            <span>{{ $i + 1 }}</span>
                        </div>
                        <span>{{ $sec['title'] ?? 'Section ' . ($i + 1) }}</span>
                    </div>
                    <div class="policy-card-body">
                        {!! $sec['content'] ?? '' !!}
                    </div>
                </div>
                @empty
                <div class="policy-card">
                    <div class="policy-card-body">
                        {!! $page->content !!}
                    </div>
                </div>
                @endforelse

                <!-- Assistance Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mt-4" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #e0e7ff 100%); border: 1px solid #ddd6fe !important;">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center p-1.5 rounded-circle flex-shrink-0" style="background: rgba(16, 185, 129, 0.12); border: 2px dashed rgba(16, 185, 129, 0.35);">
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-xs" style="width: 44px; height: 44px; color: #10b981;">
                                    <i class="bi bi-shield-check fs-4"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark" style="font-size: 1rem;">Need clarification on this policy?</h6>
                                <p class="text-muted small mb-0">Our legal and customer support team is happy to assist you.</p>
                            </div>
                        </div>
                        <a href="{{ route('page.contact') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" style="font-size: 0.85rem;">
                            Contact Us &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

    @else
        <!-- Standard CMS Page View -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="policy-card">
                    <div class="policy-card-body">
                        {!! $page->content !!}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Reading Progress Indicator
window.addEventListener('scroll', function() {
    const docEl = document.documentElement;
    const scrollTotal = docEl.scrollHeight - docEl.clientHeight;
    const progress = (docEl.scrollTop / scrollTotal) * 100;
    const bar = document.getElementById('readingProgressBar');
    if (bar) {
        bar.style.width = Math.min(progress, 100) + '%';
    }
});

// FAQ Live Search & Category Filter
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('faqSearchInput');
    const searchClear = document.getElementById('faqSearchClear');
    const filterChips = document.querySelectorAll('.faq-filter-chip');
    const items = document.querySelectorAll('.faq-item-block');
    const countEl = document.getElementById('faqCount');
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

        if (countEl) {
            countEl.textContent = visibleCount;
        }

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
            filterChips.forEach(c => c.classList.remove('active'));
            const allBtn = document.querySelector('.faq-filter-chip[data-category="all"]');
            if (allBtn) allBtn.classList.add('active');
            applyFAQFilters();
        });
    }

    if (filterChips.length > 0) {
        filterChips.forEach(chip => {
            chip.addEventListener('click', function() {
                filterChips.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                activeCategory = this.getAttribute('data-category') || 'all';
                applyFAQFilters();
            });
        });
    }

    // Smooth Scroll for TOC & Mobile Chips
    document.querySelectorAll('.mobile-section-chip, .toc-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const targetEl = document.querySelector(targetId);
                if (targetEl) {
                    e.preventDefault();
                    const yOffset = -75;
                    const y = targetEl.getBoundingClientRect().top + window.pageYOffset + yOffset;
                    window.scrollTo({ top: y, behavior: 'smooth' });
                    history.pushState(null, null, targetId);
                }
            }
        });
    });
});
</script>
@endpush

@endsection
@endif
