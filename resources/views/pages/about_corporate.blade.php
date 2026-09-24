@php
    $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
@endphp

@section('title', 'About Us - ' . $storeName)
@section('meta_description', $page->meta_description ?? 'Learn about ' . $storeName . ', our mission, vision, and customer-first shopping promise.')

@php
    $data = json_decode($page->content, true);
    if (!$data) {
        $data = [];
    }
    
    $title = $data['hero_title'] ?? 'About ' . $storeName;
    $tagline = $data['tagline'] ?? 'Simple. Transparent. Trustworthy.';
    $supportingText = $data['supporting_text'] ?? 'Your everyday shopping destination built on curated product authenticity, fast doorstep delivery, and 24/7 customer care.';
    $missionText = $data['mission'] ?? '<p>Our mission is to build a shopping destination where customers can shop comfortably, confidently, and without unnecessary complexity or hidden fees.</p><p class="fw-bold text-primary mb-0">' . $storeName . ' — Shop More. Worry Less.</p>';
    
    $focusItems = $data['focus_areas'] ?? [
        ['title' => 'Curated Authenticity', 'desc' => 'Carefully verified products with transparent specifications and genuine warranty.'],
        ['title' => 'Frictionless Checkout', 'desc' => 'Lightning-fast, simple browsing with smart filters, live search, and 1-click ordering.'],
        ['title' => 'Bank-Grade Security', 'desc' => 'Your payment details and personal data are encrypted with state-of-the-art security.'],
        ['title' => 'Fast Dispatch & Tracking', 'desc' => 'Reliable courier partners providing live parcel tracking right to your doorstep.'],
        ['title' => '30-Day Hassle-Free Returns', 'desc' => 'Simple, transparent return, cancellation, and instant refund processing.'],
        ['title' => '24/7 Dedicated Helpdesk', 'desc' => 'Friendly support team ready to assist you anytime via live helpdesk and email.']
    ];

    $ourStory = $data['our_story'] ?? [
        'badge'     => 'Our Journey',
        'title'     => 'How ' . $storeName . ' Came to Life',
        'subtitle'  => 'Born out of the desire to eliminate shopping anxiety and clutter.',
        'paragraphs' => [
            'Online shopping should be an exciting and stress-free experience, yet modern e-commerce is often crowded with confusing pricing, unverified sellers, and difficult return procedures.',
            'We founded ' . $storeName . ' to bring peace of mind back to online retail. Every product in our catalog undergoes rigorous authenticity screening, and our operations are designed around customer transparency, fair pricing, and dependable logistics.',
            'Today, ' . $storeName . ' proudly serves thousands of happy shoppers nationwide, delivering top electronics, fashion, lifestyle, and home essentials with speed and reliability.'
        ]
    ];

    $trustCommitments = $data['trust_commitments'] ?? [
        'badge'     => 'Customer Guarantee',
        'title'     => 'Our 4 Core Customer Commitments',
        'subtitle'  => 'Every order placed on ' . $storeName . ' is backed by our strict quality standard.',
        'items' => [
            ['title' => '100% Genuine Products', 'desc' => 'Direct from verified brand partners and licensed distributors.', 'icon' => 'bi-patch-check-fill', 'color' => '#10b981'],
            ['title' => 'Zero Hidden Fees', 'desc' => 'What you see is what you pay. Transparent pricing at every step.', 'icon' => 'bi-tag-fill', 'color' => '#6366f1'],
            ['title' => 'Insured Safe Delivery', 'desc' => 'Every package is insured and tracked with trusted national couriers.', 'icon' => 'bi-shield-fill-check', 'color' => '#06b6d4'],
            ['title' => 'Instant Refund Assurance', 'desc' => 'Prompt refund processing directly to your original payment method.', 'icon' => 'bi-arrow-clockwise', 'color' => '#8b5cf6']
        ]
    ];
@endphp

@push('styles')
<style>
    /* Ultra-clean, borderless Hero */
    .about-hero-clean {
        padding: clamp(1.75rem, 4vw, 3rem) 0.5rem clamp(1.25rem, 3vw, 2rem);
        text-align: center;
        position: relative;
    }

    .about-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: clamp(1rem, 2.5vw, 1.75rem) 0.75rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .about-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }

    /* Story Card */
    .about-story-card {
        background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: clamp(1.5rem, 4vw, 3.25rem) clamp(1.25rem, 3.5vw, 2.75rem);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }

    /* Mission Box */
    .about-mission-box {
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 60%, #e0e7ff 100%);
        border: 1px solid #ddd6fe;
        border-radius: 24px;
        padding: clamp(2rem, 5vw, 3.5rem) clamp(1.25rem, 3.5vw, 2.5rem);
        position: relative;
    }

    /* Trust Commitment Cards */
    .about-trust-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: clamp(1rem, 2.5vw, 1.75rem) clamp(0.85rem, 2vw, 1.5rem);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .about-trust-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }
    .about-trust-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    /* Focus Feature Cards */
    .about-feature-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: clamp(1rem, 2.5vw, 1.75rem) clamp(0.85rem, 2vw, 1.5rem);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .about-feature-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border-color: #cbd5e1;
    }
    .about-feature-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #ede9fe;
        color: #7c3aed;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    @media (max-width: 991.98px) {
        .about-story-card {
            padding: 2rem 1.5rem;
            border-radius: 18px;
        }
        .about-mission-box {
            padding: 2.25rem 1.35rem;
            border-radius: 18px;
        }
    }

    @media (max-width: 575.98px) {
        .about-story-card {
            padding: 1.5rem 1.15rem;
        }
        .about-trust-icon-box, .about-feature-icon {
            width: 36px;
            height: 36px;
            font-size: 1.1rem;
            border-radius: 10px;
            margin-bottom: 0.65rem;
        }
        .about-trust-card h5, .about-feature-card h5 {
            font-size: 0.86rem !important;
            margin-bottom: 0.25rem !important;
        }
        .about-trust-card p, .about-feature-card p {
            font-size: 0.74rem !important;
            line-height: 1.4 !important;
        }
        .about-stat-card {
            padding: 1rem 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Company</span></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">About Us</li>
        </ol>
    </nav>

    <!-- 1. Hero Section (Clean Minimalist Design with Generous Breathing Room) -->
    <div class="about-hero-clean mb-4 mb-md-5 pb-lg-2">
        <div class="mx-auto" style="max-width: 760px;">
            <div class="mb-3.5 d-inline-block">
                <x-logo height="48" :showTagline="true" />
            </div>

            <h1 class="fw-bolder mb-3 text-dark" style="letter-spacing: -0.03em; font-size: clamp(1.6rem, 4vw, 2.4rem); color: #0f172a; line-height: 1.25;">
                {{ $tagline }}
            </h1>

            <p class="text-muted fs-5 mb-4 mx-auto" style="line-height: 1.65; max-width: 640px; font-size: clamp(0.92rem, 2.5vw, 1.15rem) !important;">
                {!! strip_tags($supportingText) !!}
            </p>

            <div class="d-flex align-items-center justify-content-center gap-2.5 flex-wrap pt-1">
                <div class="row g-2 justify-content-center w-100" style="max-width: 420px;">
                    <div class="col-6">
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.88rem;">
                            <i class="bi bi-bag"></i> <span>Catalog</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('page.contact') }}" class="btn btn-light border rounded-pill w-100 py-2.5 fw-semibold text-dark shadow-xs d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.88rem;">
                            <i class="bi bi-chat-dots text-primary"></i> <span>Contact</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Core Stats Counter Grid -->
    <div class="row g-3 g-lg-4 mb-4 mb-md-5 pb-lg-2">
        <div class="col-6 col-md-3">
            <div class="about-stat-card">
                <div class="text-primary fw-bolder mb-1.5" style="font-size: clamp(1.6rem, 4.5vw, 2.3rem); line-height: 1; letter-spacing: -0.03em; color: #6366f1 !important;">50K+</div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Happy Customers</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="about-stat-card">
                <div class="text-success fw-bolder mb-1.5" style="font-size: clamp(1.6rem, 4.5vw, 2.3rem); line-height: 1; letter-spacing: -0.03em; color: #10b981 !important;">10K+</div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Verified Products</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="about-stat-card">
                <div class="text-info fw-bolder mb-1.5" style="font-size: clamp(1.6rem, 4.5vw, 2.3rem); line-height: 1; letter-spacing: -0.03em; color: #0284c7 !important;">99.8%</div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">On-Time Delivery</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="about-stat-card">
                <div class="text-warning fw-bolder mb-1.5" style="font-size: clamp(1.6rem, 4.5vw, 2.3rem); line-height: 1; letter-spacing: -0.03em; color: #f59e0b !important;">4.9★</div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">Customer Rating</div>
            </div>
        </div>
    </div>

    <!-- 3. Section: Our Story & Journey (with Generous Spacing & High-Contrast Visuals) -->
    <div class="about-story-card mb-5 pb-lg-2">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-7">
                <div class="mb-3">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1.5 shadow-xs" style="background: linear-gradient(135deg, #ede9fe, #e0e7ff); color: #4f46e5; font-size: 0.76rem; border: 1px solid #c7d2fe; letter-spacing: 0.05em;">
                        <i class="bi bi-compass-fill text-primary"></i> <span>Our Journey</span>
                    </span>
                </div>

                <h2 class="fw-bolder mb-2.5" style="letter-spacing: -0.03em; color: #0f172a; font-size: clamp(1.4rem, 3.5vw, 2rem); line-height: 1.25;">
                    How <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $storeName }}</span> Came to Life
                </h2>

                <p class="fw-semibold mb-4" style="color: #4f46e5; font-size: 1.05rem; letter-spacing: -0.01em;">
                    {{ $ourStory['subtitle'] ?? 'Born out of the desire to eliminate shopping anxiety and clutter.' }}
                </p>

                <div style="line-height: 1.85; font-size: 0.96rem; color: #1e293b;">
                    <p class="mb-3.5">
                        Online shopping should be an exciting and stress-free experience, yet modern e-commerce is often crowded with confusing pricing, unverified sellers, and difficult return procedures.
                    </p>
                    <p class="mb-3.5">
                        We founded <strong style="color: #6366f1; font-weight: 700;">{{ $storeName }}</strong> to bring <span style="background: #ede9fe; color: #7c3aed; padding: 3px 9px; border-radius: 6px; font-weight: 600;">peace of mind</span> back to online retail. Every product in our catalog undergoes <strong style="color: #0f172a; font-weight: 700;">rigorous authenticity screening</strong>, and our operations are designed around customer transparency, fair pricing, and dependable logistics.
                    </p>
                    <p class="mb-0">
                        Today, {{ $storeName }} proudly serves thousands of happy shoppers nationwide, delivering top electronics, fashion, lifestyle, and home essentials with speed and reliability.
                    </p>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="rounded-4 p-4 p-xl-5 text-center shadow-sm h-100 d-flex flex-column justify-content-center align-items-center" style="background: linear-gradient(135deg, #ede9fe 0%, #f5f3ff 50%, #e0e7ff 100%); border: 1px solid #ddd6fe;">
                    <!-- Attractive Glowing Dual-Ring Circular Structure -->
                    <div class="mb-3.5 d-inline-flex align-items-center justify-content-center p-2 rounded-circle" style="background: rgba(99, 102, 241, 0.12); border: 2px dashed rgba(99, 102, 241, 0.35);">
                        <div class="d-flex align-items-center justify-content-center rounded-circle text-white" style="width: 68px; height: 68px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #7c3aed 100%); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35), inset 0 2px 4px rgba(255, 255, 255, 0.45);">
                            <i class="bi bi-shield-heart-fill fs-2"></i>
                        </div>
                    </div>

                    <h4 class="fw-bolder mb-2.5 text-dark" style="color: #1e1b4b !important; letter-spacing: -0.02em;">Built for Peace of Mind</h4>

                    <p class="mb-4 text-center" style="color: #334155; font-size: 0.95rem; line-height: 1.65; max-width: 320px;">
                        Every decision we make starts with one guiding question: 
                        <span class="d-block mt-2 fw-bold fst-italic" style="color: #1e1b4b;">"Does this make shopping simpler and calmer for our customers?"</span>
                    </p>

                    <div class="d-inline-flex align-items-center justify-content-center gap-2 bg-white px-4 py-2.5 rounded-pill shadow-xs border border-primary border-opacity-25" style="white-space: nowrap; line-height: 1;">
                        <i class="bi bi-patch-check-fill text-success fs-5"></i> 
                        <span class="fw-bold" style="color: #4338ca; font-size: 0.85rem; letter-spacing: 0.01em;">Customer-First Guarantee</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Our Mission Statement (with Generous Spacing & High-Contrast Headline) -->
    <div class="about-mission-box text-center mb-4 mb-md-5 pb-lg-2 shadow-xs">
        <div class="mb-3">
            <span class="badge rounded-pill px-3.5 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1.5 shadow-xs" style="background: linear-gradient(135deg, #ede9fe, #e0e7ff); color: #4f46e5; font-size: 0.76rem; border: 1px solid #c7d2fe; letter-spacing: 0.06em;">
                <i class="bi bi-bullseye text-primary"></i> <span>Our Core Mission</span>
            </span>
        </div>
        <h3 class="fw-bolder mb-3.5" style="letter-spacing: -0.03em; font-size: clamp(1.4rem, 4vw, 2rem); color: #0f172a; line-height: 1.25;">
            Why We Built <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $storeName }}</span>
        </h3>
        <div class="text-dark-emphasis lead mx-auto" style="max-width: 820px; font-size: clamp(0.95rem, 2.5vw, 1.12rem); line-height: 1.8; color: #1e293b !important;">
            {!! $missionText !!}
        </div>
    </div>

    <!-- 5. The 4 Core Customer Commitments -->
    <div class="mb-4 mb-md-5 pb-lg-2">
        <div class="text-center mb-4 pb-2">
            <div class="mb-2">
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                    {{ $trustCommitments['badge'] ?? 'Customer Guarantee' }}
                </span>
            </div>
            <h2 class="fw-bolder text-dark mb-1.5" style="letter-spacing: -0.03em; font-size: clamp(1.35rem, 3.5vw, 1.85rem);">{{ $trustCommitments['title'] ?? 'Our 4 Core Customer Commitments' }}</h2>
            <p class="text-muted small mb-0">{{ $trustCommitments['subtitle'] ?? 'Every order placed on ' . $storeName . ' is backed by our strict quality standard.' }}</p>
        </div>

        <div class="row g-2.5 g-sm-3 g-lg-4">
            @foreach($trustCommitments['items'] ?? [] as $commit)
            <div class="col-6 col-lg-3">
                <div class="about-trust-card">
                    <div>
                        <div class="about-trust-icon-box" style="background: {{ $commit['color'] }}18; color: {{ $commit['color'] }};">
                            <i class="bi {{ $commit['icon'] }}"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1 mb-sm-2">{{ $commit['title'] }}</h5>
                        <p class="text-muted small mb-0">{{ $commit['desc'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 6. Focus Areas Section (3x2 Grid) -->
    <div class="mb-4 mb-md-5 pb-lg-2">
        <div class="text-center mb-4 pb-2">
            <div class="mb-2">
                <span class="text-uppercase fw-bold small text-primary" style="letter-spacing: 0.08em; font-size: 0.75rem;">The {{ $storeName }} Standard</span>
            </div>
            <h2 class="fw-bolder text-dark mb-1.5" style="letter-spacing: -0.03em; font-size: clamp(1.35rem, 3.5vw, 1.85rem);">What We Focus On</h2>
            <p class="text-muted small mb-0">Built from the ground up for reliable, premium online retail.</p>
        </div>

        <div class="row g-2.5 g-sm-3 g-lg-4">
            @php
                $icons = ['bi-award-fill', 'bi-lightning-charge-fill', 'bi-shield-lock-fill', 'bi-truck', 'bi-arrow-repeat', 'bi-headset'];
            @endphp
            @foreach($focusItems as $idx => $item)
            <div class="col-6 col-lg-4">
                <div class="about-feature-card">
                    <div>
                        <div class="about-feature-icon">
                            <i class="bi {{ $icons[$idx % count($icons)] }}"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1 mb-sm-2">{{ $item['title'] }}</h5>
                        <p class="text-muted small mb-0">{{ $item['desc'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 7. Call to Action Banner -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center mb-4" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #e0e7ff 100%); border: 1px solid #ddd6fe !important;">
        <div class="mx-auto" style="max-width: 650px;">
            <h3 class="fw-bold text-dark mb-2" style="letter-spacing: -0.02em; font-size: clamp(1.25rem, 3.5vw, 1.65rem);">Ready to Experience Great Shopping?</h3>
            <p class="text-muted mb-4 small">Discover thousands of verified products with fast delivery and guaranteed satisfaction.</p>
            <div class="d-flex align-items-center justify-content-center gap-2.5 flex-wrap">
                <div class="row g-2 justify-content-center w-100" style="max-width: 420px;">
                    <div class="col-6">
                        <a href="{{ route('shop') }}" class="btn btn-primary rounded-pill w-100 py-2.5 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.88rem;">
                            <span>Shop Now</span> <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('page.contact') }}" class="btn btn-light border rounded-pill w-100 py-2.5 fw-semibold text-dark shadow-xs d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.88rem;">
                            <i class="bi bi-headset text-primary"></i> <span>Support</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
