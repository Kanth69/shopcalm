@php
    $b1_title = $settings['trust_badge_1_title'] ?? 'Free Express Delivery';
    $b1_desc  = $settings['trust_badge_1_desc']  ?? ('Orders ₹' . ($settings['free_shipping_min'] ?? '499') . '+');
    $b2_title = $settings['trust_badge_2_title'] ?? '100% Secure Payments';
    $b2_desc  = $settings['trust_badge_2_desc']  ?? 'UPI, Cards & NetBanking';
    $b3_title = $settings['trust_badge_3_title'] ?? '100% Genuine Warranty';
    $b3_desc  = $settings['trust_badge_3_desc']  ?? 'Direct from Top Brands';
    $b4_title = $settings['trust_badge_4_title'] ?? '24/7 Dedicated Support';
    $b4_desc  = $settings['trust_badge_4_desc']  ?? 'Instant WhatsApp & Phone';

    $badges = [
        [
            'icon' => 'bi-truck',
            'title' => $b1_title,
            'desc' => $b1_desc,
            'gradient' => 'linear-gradient(135deg, #6366f1 0%, #4f46e5 100%)',
            'light_bg' => '#eef2ff',
            'color' => '#4f46e5',
        ],
        [
            'icon' => 'bi-shield-check',
            'title' => $b2_title,
            'desc' => $b2_desc,
            'gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
            'light_bg' => '#ecfdf5',
            'color' => '#059669',
        ],
        [
            'icon' => 'bi-patch-check-fill',
            'title' => $b3_title,
            'desc' => $b3_desc,
            'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
            'light_bg' => '#fffbeb',
            'color' => '#d97706',
        ],
        [
            'icon' => 'bi-headset',
            'title' => $b4_title,
            'desc' => $b4_desc,
            'gradient' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
            'light_bg' => '#ecfeff',
            'color' => '#0891b2',
        ],
    ];
@endphp

<section class="trust-bar-section container px-2 px-sm-3 px-md-4 my-2.5 my-md-4">
    {{-- Unified Glassmorphic Trust Panel --}}
    <div class="card border-0 rounded-4 shadow-sm bg-white overflow-hidden p-2 p-md-3" 
         style="border: 1px solid #e8ecf2 !important; box-shadow: 0 4px 24px rgba(15, 23, 42, 0.04) !important;">
        
        {{-- 📱 Mobile: Clean 2x2 Grid (< 768px) --}}
        <div class="row g-2 d-md-none">
            @foreach($badges as $b)
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 h-100" 
                         style="background: #f8fafc; border: 1px solid #edf2f7;">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs" 
                             style="width: 34px; height: 34px; background: {{ $b['light_bg'] }}; color: {{ $b['color'] }};">
                            <i class="bi {{ $b['icon'] }}" style="font-size: 0.95rem;"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.75rem; letter-spacing: -0.2px;">{{ $b['title'] }}</h6>
                            <div class="text-muted small text-truncate" style="font-size: 0.67rem; color: #64748b !important;">{{ $b['desc'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 💻 Desktop & Tablet: Seamless 4-Col Strip with Divider Lines (≥ 768px) --}}
        <div class="row g-0 align-items-center d-none d-md-flex py-2">
            @foreach($badges as $index => $b)
                <div class="col-md-6 col-lg-3 px-3 py-1.5 {{ $index < 3 ? 'border-end border-light-subtle' : '' }}">
                    <div class="d-flex align-items-center gap-3 trust-item-hover p-2 rounded-3 transition-all">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs transition-transform" 
                             style="width: 44px; height: 44px; background: {{ $b['light_bg'] }}; color: {{ $b['color'] }}; font-size: 1.15rem;">
                            <i class="bi {{ $b['icon'] }}"></i>
                        </div>
                        <div class="overflow-hidden">
                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.86rem; letter-spacing: -0.2px;">{{ $b['title'] }}</h6>
                            <div class="text-muted small text-truncate" style="font-size: 0.74rem; color: #64748b !important;">{{ $b['desc'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</section>

<style>
.trust-item-hover:hover {
    background-color: #f8fafc;
}
.trust-item-hover:hover .transition-transform {
    transform: scale(1.08);
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
