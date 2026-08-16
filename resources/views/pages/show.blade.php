@extends('layouts.customer')

@if($page->slug === 'about-us')
    @include('pages.about_corporate', ['page' => $page])
@else

@section('title', $page->meta_title ?? $page->title . ' - ShopCalm')
@section('meta_description', $page->meta_description)

@section('content')

@php
    $longFormPages = ['terms-and-conditions', 'privacy-policy', 'shipping-policy', 'return-refund-policy', 'cancellation-policy'];
    $isLongForm = in_array($page->slug, $longFormPages);
@endphp

@if($isLongForm)
    <div id="reading-progress-container" class="reading-progress-container d-print-none">
        <div id="reading-progress-bar" class="reading-progress-bar"></div>
    </div>
@endif

<main class="cms-page-wrapper">
    <!-- Breadcrumbs -->
    <nav class="cms-breadcrumbs d-print-none" aria-label="breadcrumb">
        <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li class="separator">/</li>
            <li aria-current="page">{{ $page->title }}</li>
        </ol>
    </nav>

    <div class="cms-layout">
        <!-- Main Content Area -->
        <article class="cms-article {{ $isLongForm ? 'cms-article-full' : '' }}">
            <!-- Hero Header -->
            @if($page->slug === 'faq')
                @php $faqData = json_decode($page->content, true) ?: []; @endphp
                <header class="cms-hero rounded-4 mb-4 mb-md-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, var(--cms-primary) 0%, #1a4b8c 100%);">
                    <div class="cms-hero-bg"></div>
                    <div class="position-relative z-1 p-4 p-md-5 text-center">
                        <h1 class="cms-title text-white mb-2 mb-md-3">{{ $faqData['hero_title'] ?? 'Frequently Asked Questions' }}</h1>
                        <p class="cms-meta text-white-50 mb-0 fs-5 d-print-none">{{ $faqData['hero_subtitle'] ?? '' }}</p>
                    </div>
                </header>
            @else
                <header class="cms-hero rounded-4 mb-4 mb-md-5 position-relative overflow-hidden">
                    <div class="cms-hero-bg"></div>
                    <div class="position-relative z-1 p-4 p-md-5 text-center">
                        <h1 class="cms-title text-white mb-2 mb-md-3">{{ $page->title }}</h1>
                        <p class="cms-meta text-white-50 mb-0 d-print-none">Last updated: {{ $page->updated_at->format('F d, Y') }}</p>
                    </div>
                </header>
            @endif
            
            <section class="cms-content {{ $isLongForm ? 'cms-content-cards' : '' }}" id="cms-content-body">
                @if($isLongForm)
                    @php $legalData = json_decode($page->content, true) ?: []; @endphp
                    
                    @if(!empty($legalData['intro']))
                        <div class="vanilla-card intro-card">
                            {!! $legalData['intro'] !!}
                        </div>
                    @endif
                    
                    @if(!empty($legalData['sections']))
                        <div class="{{ $page->slug === 'terms-and-conditions' ? 'vanilla-stack' : 'vanilla-grid' }}">
                            @foreach($legalData['sections'] as $index => $section)
                                @php 
                                    $slug = Str::slug($section['title'] ?? "section-$index");
                                @endphp
                                <div class="vanilla-card vanilla-flex-col" id="card-{{ $slug }}">
                                    <div class="vanilla-card-header vanilla-flex-row">
                                        <i class="bi bi-shield-check vanilla-icon"></i>
                                        <h2 class="vanilla-card-title" id="{{ $slug }}">{{ $section['title'] }}</h2>
                                    </div>
                                    <div class="vanilla-card-body">
                                        {!! $section['content'] !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @elseif($page->slug === 'faq')
                    @if(!empty($faqData['faqs']))
                        <div class="vanilla-card faq-container">
                            <div class="vanilla-accordion">
                                @foreach($faqData['faqs'] as $index => $faq)
                                    <details class="vanilla-details" {{ $index === 0 ? 'open' : '' }}>
                                        <summary class="vanilla-summary">
                                            {{ $faq['question'] }}
                                        </summary>
                                        <div class="vanilla-details-content">
                                            {!! $faq['answer'] !!}
                                        </div>
                                    </details>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    {!! $page->content !!}
                @endif
            </section>
        </article>

        <!-- Sidebar (Table of Contents) -->
        @if(!$isLongForm)
        <aside class="cms-sidebar d-print-none" id="cms-sidebar" style="display: none;">
            <div class="cms-toc-sticky">
                <h4 class="cms-toc-title">On this page</h4>
                <nav id="cms-toc-nav" class="cms-toc-nav"></nav>
            </div>
        </aside>
        @endif
    </div>
</main>

<style>
    /* =========================================
       ShopCalm Premium CMS Layout 
       ========================================= */

    /* Variables using ShopCalm Brand Colors */
    :root {
        --cms-primary: #0d6efd; /* Bootstrap primary blue */
        --cms-text: #334155;
        --cms-heading: #0f172a;
        --cms-bg: #f8fafc;
        --cms-surface: #ffffff;
        --cms-border: #e2e8f0;
        --cms-muted: #64748b;
        --cms-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    /* Progress Bar */
    .reading-progress-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background-color: transparent;
        z-index: 1060; /* Above bootstrap sticky-top header */
    }
    
    .reading-progress-bar {
        height: 100%;
        background-color: var(--cms-primary);
        width: 0%;
        transition: width 0.1s ease-out;
    }

    /* Layout & Wrapper */
    .cms-page-wrapper {
        background-color: var(--cms-surface);
        font-family: var(--cms-font-sans);
        color: var(--cms-text);
        padding: 40px 20px 80px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .cms-layout {
        display: flex;
        align-items: flex-start;
        gap: 60px;
        position: relative;
    }

    .cms-article {
        flex: 1;
        min-width: 0; /* Prevent flex overflow */
        max-width: 800px;
        margin: 0 auto; /* Center if sidebar is hidden */
    }
    
    .cms-article-full {
        max-width: 100%;
    }

    /* Breadcrumbs */
    .cms-breadcrumbs ol {
        list-style: none;
        padding: 0;
        margin: 0 0 30px 0;
        display: flex;
        flex-wrap: wrap;
        font-size: 0.875rem;
        color: var(--cms-muted);
    }
    
    .cms-breadcrumbs li { display: inline-flex; align-items: center; }
    .cms-breadcrumbs .separator { margin: 0 10px; opacity: 0.5; }
    .cms-breadcrumbs a {
        color: var(--cms-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .cms-breadcrumbs a:hover { color: var(--cms-primary); }

    /* Hero Section */
    .cms-hero {
        background: linear-gradient(135deg, var(--cms-heading) 0%, var(--cms-primary) 100%);
        box-shadow: 0 10px 30px -10px rgba(13, 110, 253, 0.3);
    }
    
    .cms-hero-bg {
        position: absolute;
        top: -50%;
        left: -10%;
        width: 120%;
        height: 200%;
        background: radial-gradient(circle at 50% 0%, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 60%);
        pointer-events: none;
    }
    
    .cms-title {
        font-size: 3rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.02em;
    }

    @media (max-width: 768px) {
        .cms-title { font-size: 2.25rem; }
    }
    
    @media (max-width: 576px) {
        .cms-title { font-size: 1.75rem; }
    }

    .cms-meta {
        font-size: 0.9rem;
        color: var(--cms-muted);
        margin: 0;
    }

    /* Content Typography */
    .cms-content {
        font-size: 1.125rem;
        line-height: 1.8;
        color: var(--cms-text);
    }

    .cms-content p { margin-bottom: 1.5em; }

    .cms-content h1, .cms-content h2, .cms-content h3, .cms-content h4 {
        color: var(--cms-heading);
        font-weight: 700;
        margin-top: 2em;
        margin-bottom: 0.75em;
        line-height: 1.3;
        letter-spacing: -0.01em;
        scroll-margin-top: 100px; /* offset for fixed header */
    }

    .cms-content h2 { font-size: 1.75rem; border-bottom: 1px solid var(--cms-border); padding-bottom: 10px; }
    .cms-content h3 { font-size: 1.35rem; }

    .cms-content ul, .cms-content ol {
        margin-bottom: 1.5em;
        padding-left: 2em;
    }
    
    .cms-content li { margin-bottom: 0.5em; }

    .cms-content a {
        color: var(--cms-primary);
        text-decoration: none;
        box-shadow: inset 0 -2px 0 0 rgba(13, 110, 253, 0.2);
        transition: box-shadow 0.2s ease, color 0.2s ease;
        font-weight: 500;
    }

    .cms-content a:hover {
        color: #0a58ca;
        box-shadow: inset 0 -24px 0 0 rgba(13, 110, 253, 0.1);
    }

    .cms-content strong, .cms-content b {
        color: var(--cms-heading);
        font-weight: 600;
    }

    /* Table of Contents Sidebar */
    .cms-sidebar {
        flex-shrink: 0;
        width: 280px;
    }

    .cms-toc-sticky {
        position: sticky;
        top: 100px;
        max-height: calc(100vh - 120px);
        overflow-y: auto;
        padding-left: 24px;
        border-left: 1px solid var(--cms-border);
    }

    .cms-toc-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: var(--cms-heading);
        margin: 0 0 16px 0;
    }

    .cms-toc-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .cms-toc-nav li { margin-bottom: 12px; }

    .cms-toc-nav a {
        color: var(--cms-muted);
        text-decoration: none;
        font-size: 0.95rem;
        line-height: 1.4;
        display: block;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .cms-toc-nav a:hover, .cms-toc-nav a.active {
        color: var(--cms-primary);
        font-weight: 500;
        transform: translateX(4px);
    }
    
    /* Indent h3 tags in TOC */
    .cms-toc-nav .toc-h3 { padding-left: 16px; font-size: 0.9rem; }

    /* Selection Color */
    ::selection {
        background-color: rgba(13, 110, 253, 0.2);
        color: var(--cms-heading);
    }

    /* =========================================
       Vanilla CSS Replacements for Bootstrap
       ========================================= */

    /* Cards */
    .vanilla-card {
        background-color: var(--cms-surface);
        border: 1px solid var(--cms-border);
        border-radius: 1rem; /* rounded-4 */
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* shadow-sm */
        overflow: hidden;
    }
    .intro-card {
        padding: 3rem; /* p-5 */
        margin-bottom: 3rem; /* mb-5 */
    }
    
    /* Flex Utilities */
    .vanilla-flex-col {
        display: flex;
        flex-direction: column;
        height: 100%;
        width: 100%;
    }
    .vanilla-flex-row {
        display: flex;
        align-items: center;
    }

    /* Grid & Stack Layout */
    .vanilla-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    .vanilla-stack {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    /* Card Header & Body */
    .vanilla-card-header {
        background-color: rgba(248, 249, 250, 0.5); /* bg-light bg-opacity-50 */
        border-bottom: 1px solid var(--cms-border);
        padding: 1.5rem; /* p-4 */
    }
    .vanilla-card-body {
        padding: 1.5rem; /* p-4 */
        flex-grow: 1;
    }
    .vanilla-card-title {
        font-size: 1.25rem; /* h4 */
        font-weight: 700;
        margin: 0;
    }
    .vanilla-icon {
        color: var(--cms-primary);
        font-size: 1.5rem; /* fs-4 */
        margin-right: 1rem; /* me-3 */
    }

    /* FAQ Details & Summary */
    .faq-container {
        padding: 3rem;
        max-width: 900px;
        margin: 0 auto;
    }
    .vanilla-accordion {
        display: flex;
        flex-direction: column;
    }
    .vanilla-details {
        border-bottom: 1px solid var(--cms-border);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }
    .vanilla-summary {
        list-style: none; /* Hide default triangle */
        font-weight: 700;
        font-size: 1.25rem; /* fs-5 */
        cursor: pointer;
        padding: 1rem 0;
        color: var(--cms-heading);
        transition: color 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .vanilla-summary::-webkit-details-marker {
        display: none; /* Hide default triangle in Safari */
    }
    .vanilla-summary::after {
        content: '+';
        font-size: 1.5rem;
        color: var(--cms-primary);
        transition: transform 0.3s ease;
    }
    .vanilla-details[open] .vanilla-summary::after {
        content: '−';
    }
    .vanilla-summary:hover {
        color: var(--cms-primary);
    }
    .vanilla-details-content {
        padding: 0 0 1rem 0;
        color: var(--cms-muted);
        line-height: 1.7;
        font-size: 1.05rem;
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .cms-sidebar { display: none !important; }
        .cms-layout { gap: 0; }
    }

    @media (max-width: 576px) {
        .cms-page-wrapper { padding: 20px 10px 40px; }
        .cms-content { font-size: 1rem; }
        .intro-card, .legal-card-body { padding: 1.5rem !important; }
        .legal-card-header { padding: 1.25rem !important; }
    }

    /* Print Styles */
    @media print {
        .cms-page-wrapper { padding: 0; max-width: 100%; margin: 0; }
        .d-print-none, .header-main, .section-footer { display: none !important; }
        .cms-content a { box-shadow: none; text-decoration: underline; color: #000; }
        .cms-article { max-width: 100%; }
        body { background: #fff; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    /* =========================================
       Reading Progress Bar
       ========================================= */
    const progressBar = document.getElementById('reading-progress-bar');
    if (progressBar) {
        window.addEventListener('scroll', () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            progressBar.style.width = scrolled + "%";
        });
    }

    /* =========================================
       Dynamic Table of Contents
       ========================================= */
    const contentBody = document.getElementById('cms-content-body');
    const tocNav = document.getElementById('cms-toc-nav');
    const sidebar = document.getElementById('cms-sidebar');
    
    if (contentBody && tocNav && sidebar) {
        // Find all H2 and H3 tags
        const headings = contentBody.querySelectorAll('h2, h3');
        
        // Only generate TOC if there are more than 2 headings
        if (headings.length > 2) {
            sidebar.style.display = 'block'; // Show sidebar
            
            const ul = document.createElement('ul');
            
            headings.forEach((heading, index) => {
                // Ensure heading has an ID
                if (!heading.id) {
                    // Create URL friendly slug from heading text
                    const slug = heading.textContent.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
                    heading.id = slug || `section-${index}`;
                }
                
                const li = document.createElement('li');
                const a = document.createElement('a');
                a.href = `#${heading.id}`;
                a.textContent = heading.textContent;
                
                // Add class based on heading level for indentation
                if (heading.tagName.toLowerCase() === 'h3') {
                    a.classList.add('toc-h3');
                }
                
                // Smooth TOC Scrolling
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    let targetId = this.getAttribute('href').substring(1);
                    // Adjust targetId to scroll to the card if cards are active
                    if (contentBody.classList.contains('cms-content-cards')) {
                        targetId = 'card-' + targetId;
                    }
                    const targetEl = document.getElementById(targetId);
                    if(targetEl) {
                        window.scrollTo({
                            top: targetEl.offsetTop - 100,
                            behavior: 'smooth'
                        });
                    }
                    history.pushState(null, null, `#${heading.id}`);
                });
                
                li.appendChild(a);
                ul.appendChild(li);
            });
            
            tocNav.appendChild(ul);
            
            // Intersection Observer for highlighting active TOC links
            const observerOptions = {
                root: null,
                rootMargin: '-100px 0px -40% 0px', // Trigger point
                threshold: 0
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Remove active from all
                        document.querySelectorAll('.cms-toc-nav a').forEach(link => {
                            link.classList.remove('active');
                        });
                        // Add active to current
                        const activeLink = document.querySelector(`.cms-toc-nav a[href="#${entry.target.id}"]`);
                        if (activeLink) activeLink.classList.add('active');
                    }
                });
            }, observerOptions);
            
            headings.forEach(heading => observer.observe(heading));
        }
    }
});
</script>
@endsection
@endif
