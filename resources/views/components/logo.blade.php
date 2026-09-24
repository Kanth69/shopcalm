@props([
    'variant' => 'dark', // 'dark' (for light bg) or 'light' (for dark bg)
    'height' => 36,
    'showTagline' => false,
    'onlyIcon' => false,
])

@php
    $uid = uniqid('sc_logo_');
    $textColorShop = $variant === 'light' ? '#ffffff' : '#0a1128';
    $taglineColor  = $variant === 'light' ? 'rgba(255,255,255,0.75)' : '#1e293b';
    $ruleColor     = $variant === 'light' ? 'rgba(255,255,255,0.3)' : '#cbd5e1';
    $iconSize      = round($height * 0.95);
    $storeName     = \App\Models\Setting::get('store_name', 'ShopCalm');
    $customLogo    = \App\Models\Setting::get('logo');
    $taglineText   = \App\Models\Setting::get('tagline', 'Shop More. Worry Less.');
    
    $darkAccentColor = $variant === 'light' ? 'rgba(255,255,255,0.88)' : '#0a1128';
    
    // Split store name into 2 parts for styled gradient rendering
    $nameLen = strlen($storeName);
    $splitPoint = (stripos($storeName, 'Kart') !== false) ? stripos($storeName, 'Kart') : ((stripos($storeName, 'Calm') !== false) ? stripos($storeName, 'Calm') : (int)ceil($nameLen / 2));
    $part1 = $splitPoint > 0 ? substr($storeName, 0, $splitPoint) : $storeName;
    $part2 = $splitPoint > 0 ? substr($storeName, $splitPoint) : '';
@endphp

@if($customLogo && !$onlyIcon)
    <div class="d-inline-flex align-items-center logo-custom-container" style="user-select: none;">
        <img src="{{ asset('storage/' . $customLogo) }}" height="{{ $height }}" alt="{{ $storeName }}" style="max-height: {{ $height }}px; object-fit: contain;">
    </div>
@else
<div class="d-inline-flex {{ $showTagline ? 'flex-column align-items-center' : 'align-items-center gap-2.5' }} logo-shopcalm-container" style="user-select: none;">
    <div class="d-inline-flex align-items-center gap-2.5">
        {{-- ShopCalm Exact Reference Brand Mark (100% Vector SVG) --}}
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0; overflow: visible;">
            <defs>
                {{-- Dynamic linear gradient for the "S" body: Purple to Electric Blue --}}
                <linearGradient id="{{ $uid }}_s_gradient" x1="45%" y1="15%" x2="75%" y2="95%">
                    <stop offset="0%" stop-color="#a855f7" />
                    <stop offset="25%" stop-color="#9333ea" />
                    <stop offset="60%" stop-color="#6366f1" />
                    <stop offset="100%" stop-color="#2563eb" />
                </linearGradient>

                {{-- Tag Gradient --}}
                <linearGradient id="{{ $uid }}_tag_gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#9333ea" />
                    <stop offset="100%" stop-color="#6366f1" />
                </linearGradient>

                {{-- Speed Line Gradient --}}
                <linearGradient id="{{ $uid }}_speed_gradient" x1="0%" y1="50%" x2="100%" y2="50%">
                    <stop offset="0%" stop-color="#a855f7" stop-opacity="0.1" />
                    <stop offset="100%" stop-color="#9333ea" stop-opacity="1" />
                </linearGradient>
            </defs>

            {{-- 1. Top Handle --}}
            <path d="M 64 42 C 64 16, 96 16, 96 42" 
                  fill="none" 
                  stroke="{{ $darkAccentColor }}" 
                  stroke-width="7" 
                  stroke-linecap="round" />
            
            {{-- Handle Ring Grommets --}}
            <circle cx="64" cy="42" r="3.5" fill="{{ $darkAccentColor }}" />
            <circle cx="96" cy="42" r="3.5" fill="{{ $darkAccentColor }}" />

            {{-- 2. Speed Trails (3 Purple streaks on bottom-left) --}}
            <path d="M 32 108 L 58 108 M 24 115 L 62 115 M 34 122 L 56 122" 
                  stroke="url(#{{ $uid }}_speed_gradient)" 
                  stroke-width="3.2" 
                  stroke-linecap="round" />

            {{-- 3. Left Corner Wedge --}}
            <path d="M 58 38 L 52 110 C 52 120, 60 126, 68 126 L 82 126 C 70 120, 64 106, 70 94 C 74 86, 80 80, 84 76 L 68 62 C 60 52, 58 42, 58 38 Z" 
                  fill="{{ $darkAccentColor }}" />

            {{-- 4. Main "S" Gradient Shopping Bag Silhouette --}}
            <path d="M 58 38 
                     C 64 40, 70 42, 80 42 
                     C 90 42, 96 40, 102 38 
                     C 106 38, 108 42, 108 48 
                     L 106 66 
                     C 92 56, 82 56, 76 62 
                     C 68 70, 74 84, 88 92 
                     C 104 100, 110 110, 106 122 
                     C 102 132, 90 136, 72 136 
                     C 64 136, 56 132, 54 128 
                     C 60 126, 66 124, 74 120 
                     C 86 114, 86 104, 78 98 
                     C 66 90, 56 80, 60 66 
                     C 64 54, 74 48, 86 48 
                     C 94 48, 100 50, 104 54 
                     L 104 42 
                     C 100 40, 94 38, 80 38 
                     C 70 38, 64 40, 58 38 Z" 
                  fill="url(#{{ $uid }}_s_gradient)" />

            {{-- Smooth S Upper and Lower Curves for razor sharp fidelity --}}
            <path d="M 60 40 C 66 42, 74 44, 84 44 C 94 44, 100 42, 104 40 C 106 44, 106 50, 106 58 C 96 52, 86 52, 80 56 C 72 62, 72 72, 82 78 C 96 86, 108 96, 106 112 C 104 124, 94 132, 76 134 C 64 134, 56 130, 54 126 C 60 124, 68 122, 76 116 C 88 108, 86 98, 76 92 C 64 84, 58 74, 62 60 C 66 48, 76 44, 88 44 C 96 44, 102 46, 106 50" 
                  fill="url(#{{ $uid }}_s_gradient)" />

            {{-- 5. Hanging Price Tag with Curved String --}}
            {{-- Thread line hanging from top right --}}
            <path d="M 98 42 C 106 44, 114 50, 115 56" 
                  fill="none" 
                  stroke="{{ $darkAccentColor }}" 
                  stroke-width="2.5" 
                  stroke-linecap="round" />
            
            {{-- Grommet on Tag --}}
            <circle cx="115" cy="56" r="2.2" fill="#ffffff" stroke="{{ $darkAccentColor }}" stroke-width="1.5" />

            {{-- Angled Hanging Tag --}}
            <g transform="translate(111, 52) rotate(22)">
                {{-- Price Tag Body --}}
                <path d="M 6 0 L 14 0 L 20 8 L 20 25 C 20 27.2, 18.2 29, 16 29 L 4 29 C 1.8 29, 0 27.2, 0 25 L 0 8 Z" 
                      fill="url(#{{ $uid }}_tag_gradient)" />
                
                {{-- Shopping Cart Icon inside Tag --}}
                <g transform="translate(4, 11) scale(0.6)">
                    <path d="M 1 2 H 4 L 6.5 13 H 16 L 18 5 H 5.5" 
                          stroke="#ffffff" 
                          stroke-width="1.8" 
                          stroke-linecap="round" 
                          stroke-linejoin="round" 
                          fill="none" />
                    {{-- Grid lines in cart --}}
                    <path d="M 9 5 V 13 M 13 5 V 13 M 6 9 H 17" 
                          stroke="#ffffff" 
                          stroke-width="1.2" 
                          stroke-linecap="round" />
                    {{-- Wheels --}}
                    <circle cx="8" cy="16" r="1.5" fill="#ffffff" />
                    <circle cx="15" cy="16" r="1.5" fill="#ffffff" />
                </g>
            </g>
        </svg>

        @if(!$onlyIcon)
            <div class="d-flex align-items-baseline" style="font-family: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif; font-size: {{ max(1.2, $height * 0.046) }}rem; font-weight: 800; letter-spacing: -0.7px; line-height: 1;">
                <span style="color: {{ $textColorShop }};">{{ $part1 }}</span><span style="background: linear-gradient(135deg, #8b5cf6 0%, #2563eb 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $part2 }}</span>
            </div>
        @endif
    </div>

    @if($showTagline)
        {{-- Tagline bar matching exact reference: — 🛍️ Shop More. Worry Less. — --}}
        <div class="d-flex align-items-center justify-content-center gap-2 mt-1.5" style="width: 100%;">
            <div style="height: 1px; width: 28px; background: {{ $ruleColor }};"></div>
            <div class="d-flex align-items-center gap-1.5">
                {{-- Mini Purple Bag Icon --}}
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span class="fw-semibold" style="font-size: 0.64rem; letter-spacing: 0.3px; color: {{ $taglineColor }}; font-family: 'Inter', system-ui, sans-serif;">
                    {{ $taglineText }}
                </span>
            </div>
            <div style="height: 1px; width: 28px; background: {{ $ruleColor }};"></div>
        </div>
    @endif
</div>
@endif
