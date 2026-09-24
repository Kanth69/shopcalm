@php
    $s = strtolower(str_replace([' ', '-'], '_', $status ?? ''));
    $badgeStyle = match($s) {
        'delivered'        => 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;',
        'out_for_delivery' => 'background:#fef3c7; color:#b45309; border:1px solid #fcd34d;',
        'shipped'          => 'background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe;',
        'packed', 'confirmed', 'processing' => 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
        'cancelled'        => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
        default            => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;'
    };
    $label = ucwords(str_replace('_', ' ', $status ?? ''));
@endphp

<span class="badge rounded-pill px-2.5 py-1 fw-bold" style="{{ $badgeStyle }} font-size: 0.72rem;">
    {{ $label }}
</span>
