
@section('title', 'About ShopCalm - Corporate Profile')
@section('meta_description', $page->meta_description)

@php
    $data = json_decode($page->content, true);
    if (!$data) {
        // Fallback for empty or corrupted json
        $data = [
            'hero_title' => $page->title,
            'tagline' => 'Welcome to ShopCalm',
            'supporting_text' => strip_tags($page->content),
            'mission' => '',
            'focus_areas' => []
        ];
    }
    
    $title = $data['hero_title'] ?? 'About ShopCalm';
    $tagline = $data['tagline'] ?? '';
    $supportingText = $data['supporting_text'] ?? '';
    $missionText = $data['mission'] ?? '';
    $focusItems = $data['focus_areas'] ?? [];
@endphp

@section('content')
<main class="corporate-page">
    
    <!-- Breadcrumbs -->
    <div class="corp-container corp-pt-4 corp-pb-2">
        <nav class="corp-breadcrumbs" aria-label="breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li class="separator">/</li>
                <li aria-current="page">Corporate Profile</li>
            </ol>
        </nav>
    </div>

    <!-- Hero Section -->
    <section class="corp-hero">
        <div class="corp-hero-bg"></div>
        <div class="corp-container corp-position-relative">
            <div class="corp-flex-center corp-text-center">
                <div class="corp-hero-content">
                    <div class="corp-mb-4">
                        <x-logo height="64" />
                    </div>
                    <h1 class="corp-hero-title">{!! strip_tags($tagline, '<strong><em>') !!}</h1>
                    <div class="corp-hero-subtitle">
                        {!! $supportingText !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="corp-mission">
        <div class="corp-container">
            <div class="corp-flex-center">
                <div class="corp-mission-wrapper">
                    <div class="corp-mission-box">
                        <div class="mission-label">Our Mission</div>
                        <div class="mission-statement">
                            {!! $missionText !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Focus Areas Section -->
    <section class="corp-focus">
        <div class="corp-container">
            <div class="corp-text-center corp-mb-5 corp-pb-2">
                <h2 class="corp-section-title">What We Focus On</h2>
                <div class="corp-divider"></div>
            </div>
            
            <div class="corp-grid">
                @foreach($focusItems as $index => $item)
                    @php 
                        $head = $item['title'] ?? '';
                        $body = $item['desc'] ?? '';
                        
                        // Assign some generic premium icons based on index
                        $icons = ['bi-star-fill', 'bi-cart-check-fill', 'bi-shield-lock-fill', 'bi-truck', 'bi-heart-fill'];
                        $icon = $icons[$index % count($icons)];
                    @endphp
                    <div class="corp-focus-card">
                        <div class="corp-focus-icon">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                        <h3 class="corp-focus-title">{{ $head }}</h3>
                        <p class="corp-focus-desc">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</main>

<style>
    /* =========================================
       ShopCalm Corporate About Page UI 
       ========================================= */

    :root {
        --corp-primary: #0d6efd;
        --corp-primary-soft: #eff6ff;
        --corp-dark: #0f172a;
        --corp-text: #334155;
        --corp-muted: #64748b;
        --corp-bg: #f8fafc;
        --corp-font: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .corporate-page {
        font-family: var(--corp-font);
        color: var(--corp-text);
        background-color: #ffffff;
        line-height: 1.7;
    }

    /* =========================================
       Vanilla CSS Utilities (Replacing Bootstrap)
       ========================================= */
    .corp-container {
        width: 100%;
        max-width: 1320px;
        margin-right: auto;
        margin-left: auto;
        padding-right: 1.5rem;
        padding-left: 1.5rem;
    }
    .corp-pt-4 { padding-top: 1.5rem; }
    .corp-pb-2 { padding-bottom: 0.5rem; }
    .corp-mb-4 { margin-bottom: 1.5rem; }
    .corp-mb-5 { margin-bottom: 3rem; }
    .corp-position-relative { position: relative; }
    .corp-text-center { text-align: center; }
    .corp-flex-center { display: flex; justify-content: center; }
    .corp-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 1.5rem;
    }
    
    .corp-hero-content {
        width: 100%;
        max-width: 900px; /* ~col-lg-9 */
    }
    .corp-mission-wrapper {
        width: 100%;
        max-width: 1000px; /* ~col-lg-10 */
    }

    /* Breadcrumbs */
    .corp-breadcrumbs ol {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--corp-muted);
    }
    .corp-breadcrumbs li { display: inline-flex; align-items: center; }
    .corp-breadcrumbs .separator { margin: 0 12px; opacity: 0.4; }
    .corp-breadcrumbs a { color: var(--corp-muted); text-decoration: none; transition: color 0.2s ease; }
    .corp-breadcrumbs a:hover { color: var(--corp-primary); }

    /* Hero Section */
    .corp-hero {
        position: relative;
        padding: 80px 0 100px;
        background-color: #ffffff;
        overflow: hidden;
    }

    .corp-hero-bg {
        position: absolute;
        top: -50%;
        left: -10%;
        width: 120%;
        height: 200%;
        background: radial-gradient(circle at 50% 0%, rgba(13, 110, 253, 0.08) 0%, rgba(255,255,255,0) 60%);
        pointer-events: none;
        z-index: 0;
    }

    .corp-hero-title {
        font-size: 3.5rem;
        font-weight: 800;
        color: var(--corp-dark);
        line-height: 1.15;
        letter-spacing: -0.03em;
        margin-bottom: 24px;
    }
    .corp-hero-title strong {
        color: var(--corp-primary);
    }

    .corp-hero-subtitle {
        font-size: 1.25rem;
        color: var(--corp-muted);
        max-width: 700px;
        margin: 0 auto;
    }
    .corp-hero-subtitle p { margin-bottom: 1rem; }

    /* Mission Section */
    .corp-mission {
        padding: 0 0 100px;
        background-color: #ffffff;
        position: relative;
        z-index: 2;
    }

    .corp-mission-box {
        background-color: var(--corp-dark);
        color: #ffffff;
        padding: 60px 80px;
        border-radius: 24px;
        box-shadow: 0 20px 40px -10px rgba(15, 23, 42, 0.2);
        text-align: center;
    }

    .mission-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 700;
        color: #94a3b8;
        margin-bottom: 24px;
    }

    .mission-statement {
        font-size: 2rem;
        font-weight: 600;
        line-height: 1.4;
        letter-spacing: -0.02em;
    }
    .mission-statement p { margin-bottom: 0.75em; }
    .mission-statement p:last-child { margin-bottom: 0; color: var(--corp-primary-soft); font-size: 1.5rem; }
    .mission-statement strong { font-weight: 800; color: #ffffff; }

    /* Focus Section */
    .corp-focus {
        padding: 80px 0 120px;
        background-color: var(--corp-bg);
    }

    .corp-section-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--corp-dark);
        letter-spacing: -0.02em;
        margin-bottom: 16px;
    }

    .corp-divider {
        height: 4px;
        width: 60px;
        background-color: var(--corp-primary);
        margin: 0 auto;
        border-radius: 2px;
    }

    .corp-focus-card {
        background: #ffffff;
        border: 1px solid var(--corp-muted);
        border-opacity: 0.2;
        border-radius: 1.5rem;
        padding: 3rem 2.5rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%; /* replaced h-100 */
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .corp-focus-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.08);
        border-color: rgba(13, 110, 253, 0.1);
    }

    .corp-focus-icon {
        width: 64px;
        height: 64px;
        background-color: var(--corp-primary-soft);
        color: var(--corp-primary);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        margin-bottom: 24px;
    }

    .corp-focus-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--corp-dark);
        margin-bottom: 12px;
        letter-spacing: -0.01em;
    }

    .corp-focus-desc {
        color: var(--corp-muted);
        font-size: 1rem;
        margin: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .corp-hero-title { font-size: 3rem; }
        .corp-mission-box { padding: 50px 40px; }
        .mission-statement { font-size: 1.75rem; }
    }
    
    @media (max-width: 768px) {
        .corp-hero { padding: 60px 0; }
        .corp-hero-title { font-size: 2.5rem; }
        .corp-hero-subtitle { font-size: 1.1rem; }
        .corp-mission-box { padding: 40px 24px; border-radius: 16px; }
        .mission-statement { font-size: 1.5rem; }
        .mission-statement p:last-child { font-size: 1.2rem; }
        .corp-focus { padding: 60px 0 80px; }
        .corp-section-title { font-size: 2rem; }
    }

    /* Print Styles */
    @media print {
        .corp-hero-bg { display: none; }
        .corp-mission-box { color: #000; background: transparent; border: 2px solid #000; box-shadow: none; }
        .mission-label, .mission-statement p:last-child { color: #555; }
        .corp-focus-card { border: 1px solid #ccc; box-shadow: none; }
        .corp-breadcrumbs { display: none; }
    }
</style>
@endsection
