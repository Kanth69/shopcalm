@props([
    'variant' => 'dark', // 'dark' (for light bg) or 'light' (for dark bg)
    'height' => 36,
    'showTagline' => false,
])

@php
    $textColorShop = $variant === 'light' ? '#ffffff' : '#0f172a';
    $textColorCalm = '#8b5cf6';
    $taglineColor  = $variant === 'light' ? 'rgba(255,255,255,0.7)' : '#64748b';
@endphp

<div class="d-inline-flex align-items-center gap-2 logo-shopcalm-container" style="user-select: none;">
    <svg width="{{ round($height * 1.1) }}" height="{{ $height }}" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient id="scSGradient" x1="20" y1="20" x2="100" y2="100" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#a855f7" />
                <stop offset="50%" stop-color="#8b5cf6" />
                <stop offset="100%" stop-color="#3b82f6" />
            </linearGradient>
            <linearGradient id="scTagGradient" x1="80" y1="30" x2="110" y2="60" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#9333ea" />
                <stop offset="100%" stop-color="#6366f1" />
            </linearGradient>
        </defs>

        <!-- Bag Handle -->
        <path d="M44 32 C44 14, 76 14, 76 32" stroke="#0f172a" stroke-width="8" stroke-linecap="round" fill="none"/>

        <!-- Left Dark Bag Backing & Motion Cutouts -->
        <path d="M40 32 L26 82 C25 86, 28 90, 32 90 L52 90 C45 78, 42 62, 54 48 Z" fill="#0f172a" />
        <path d="M12 72 H28 M16 80 H32 M20 88 H34" stroke="#8b5cf6" stroke-width="3" stroke-linecap="round" />

        <!-- Main "S" Curve Bag Body -->
        <path d="M72 32 H44 C34 32, 28 40, 32 52 C36 64, 52 66, 66 70 C80 74, 88 80, 84 94 C80 106, 64 110, 42 108 C58 108, 76 104, 84 94 C92 84, 88 68, 70 64 C52 60, 44 58, 48 46 C51 37, 60 32, 72 32 Z" fill="url(#scSGradient)"/>
        
        <!-- Smooth S Sweep Accent -->
        <path d="M76 32 C54 32, 42 42, 48 54 C54 66, 76 66, 82 78 C88 90, 78 104, 50 106 C72 106, 88 96, 86 82 C84 68, 64 64, 56 54 C48 44, 58 32, 76 32 Z" fill="url(#scSGradient)" opacity="0.9" />

        <!-- Tag Line & Tag -->
        <path d="M70 32 L88 44" stroke="#0f172a" stroke-width="4" stroke-linecap="round" />
        <rect x="84" y="38" width="26" height="32" rx="6" transform="rotate(15 84 38)" fill="url(#scTagGradient)" />
        
        <!-- Mini Shopping Cart on Tag -->
        <g transform="translate(86, 44) rotate(15) scale(0.65)">
            <path d="M2 2 H6 L9 15 H20 L22 6 H7" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <circle cx="10" cy="18" r="1.5" fill="#ffffff" />
            <circle cx="19" cy="18" r="1.5" fill="#ffffff" />
        </g>
    </svg>

    <div class="d-flex flex-column leading-none">
        <div class="d-flex align-items-baseline" style="font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: {{ max(1.1, $height * 0.038) }}rem; letter-spacing: -0.5px; line-height: 1;">
            <span class="fw-bolder" style="color: {{ $textColorShop }};">Shop</span><span class="fw-bolder" style="background: linear-gradient(135deg, #a855f7, #3b82f6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Calm</span>
        </div>
        @if($showTagline)
            <small class="fw-semibold text-uppercase mt-1" style="font-size: 0.55rem; letter-spacing: 1.2px; color: {{ $taglineColor }};">Shop More. Worry Less.</small>
        @endif
    </div>
</div>
