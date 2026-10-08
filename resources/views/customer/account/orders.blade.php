@extends('customer.account.layout')

@section('title', 'My Orders — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@section('account_content')

{{-- ── Hero Banner Header ──────────────────────────────────── --}}
<div class="rounded-4 mb-3 mb-md-4 overflow-hidden position-relative shadow-xs"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); color: #ffffff;">
    <div class="position-absolute w-100 h-100"
         style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 20px 20px; top: 0; left: 0; pointer-events: none;"></div>
    <div class="position-relative p-3 p-md-4 d-flex align-items-center justify-content-between gap-2.5 flex-wrap">
        <div class="d-flex align-items-center gap-2.5 min-w-0">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width: 42px; height: 42px; background: rgba(99,102,241,0.25); border: 1.5px solid rgba(99,102,241,0.5);">
                <i class="bi bi-box-seam-fill text-white fs-5"></i>
            </div>
            <div class="min-w-0">
                <h5 class="fw-bold text-white mb-0.5 text-truncate" style="font-size: clamp(1.05rem, 3vw, 1.25rem);">My Orders</h5>
                <p class="text-white-50 small mb-0 text-truncate" style="font-size: 0.76rem;">Track your shipments, receipts & delivery status</p>
            </div>
        </div>
        
        {{-- Stats Badges --}}
        <div class="d-flex gap-1.5 flex-wrap ms-auto">
            <div class="text-center px-2.5 py-1 rounded-3" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); min-width: 50px;">
                <div class="fw-bold text-white font-monospace" style="font-size: 0.95rem;">{{ $totalCount }}</div>
                <div class="text-white-50" style="font-size: 0.62rem; margin-top: 1px;">Total</div>
            </div>
            <div class="text-center px-2.5 py-1 rounded-3" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); min-width: 50px;">
                <div class="fw-bold font-monospace" style="color: #6ee7b7; font-size: 0.95rem;">{{ $deliveredCount }}</div>
                <div style="font-size: 0.62rem; color: #6ee7b7; margin-top: 1px;">Delivered</div>
            </div>
            <div class="text-center px-2.5 py-1 rounded-3" style="background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.3); min-width: 50px;">
                <div class="fw-bold font-monospace" style="color: #fcd34d; font-size: 0.95rem;">{{ $activeCount }}</div>
                <div style="font-size: 0.62rem; color: #fcd34d; margin-top: 1px;">Active</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Status Filter Chips (Horizontal Swipe on Mobile) ─────── --}}
<div class="mb-3">
    <div class="d-flex align-items-center gap-2 overflow-x-auto pb-1.5 text-nowrap no-scrollbar" style="scrollbar-width: none; -ms-overflow-style: none;">
        @php
            $tabs = [
                ''                 => ['label'=>'All Orders',        'icon'=>'bi-grid-3x3-gap'],
                'pending'          => ['label'=>'Pending',           'icon'=>'bi-clock'],
                'confirmed'        => ['label'=>'Confirmed',         'icon'=>'bi-check2-square'],
                'packed'           => ['label'=>'Packed',            'icon'=>'bi-box-seam'],
                'shipped'          => ['label'=>'Shipped',           'icon'=>'bi-truck'],
                'out_for_delivery' => ['label'=>'Out for Delivery',  'icon'=>'bi-bicycle'],
                'delivered'        => ['label'=>'Delivered',         'icon'=>'bi-check-circle-fill'],
                'cancelled'        => ['label'=>'Cancelled',         'icon'=>'bi-x-circle-fill'],
            ];
            $activeTab = request('status', '');
        @endphp
        @foreach($tabs as $val => $tab)
        <a href="{{ route('account.orders.index', array_merge(request()->only('search'), $val ? ['status'=>$val] : [])) }}"
           class="btn btn-sm rounded-pill fw-semibold px-3 py-1.5 text-decoration-none d-inline-flex align-items-center gap-1.5 {{ $activeTab === $val ? 'text-white shadow-xs' : 'bg-white border text-dark shadow-xs' }}"
           style="font-size: 0.8rem; transition: all 0.2s;
                  {{ $activeTab === $val
                    ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;'
                    : 'border-color: #e2e8f0 !important;' }}">
            <i class="bi {{ $tab['icon'] }} {{ $activeTab === $val ? 'text-white' : 'text-secondary' }}" style="font-size: 0.82rem;"></i>
            <span>{{ $tab['label'] }}</span>
        </a>
        @endforeach
    </div>
</div>

{{-- ── Search Bar ──────────────────────────────────────────── --}}
<div class="card border-0 shadow-xs rounded-4 mb-3 mb-md-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-2.5 p-md-3">
        <form method="GET" action="{{ route('account.orders.index') }}" class="d-flex gap-2 align-items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white border-end-0 text-muted"
                      style="border-radius: 999px 0 0 999px; border-color: #e2e8f0; font-size: 0.85rem;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search"
                       class="form-control border-start-0 ps-1"
                       placeholder="Search by order #..."
                       value="{{ request('search') }}"
                       style="border-radius: 0 999px 999px 0; border-color: #e2e8f0; font-size: 0.85rem;">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs flex-shrink-0" style="font-size: 0.82rem; background: #4f46e5; border: none;">
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('account.orders.index', request('status') ? ['status'=>request('status')] : []) }}"
               class="btn btn-light border rounded-pill px-2.5 py-1.5" style="font-size: 0.82rem;" title="Clear Search">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
    </div>
</div>

{{-- ── Orders Stream ───────────────────────────────────────── --}}
@if($orders->isNotEmpty())

<div class="d-flex align-items-center justify-content-between mb-2.5 px-1">
    <span class="text-muted small" style="font-size: 0.78rem;">
        Showing <strong class="text-dark">{{ $orders->firstItem() }}–{{ $orders->lastItem() }}</strong> of <strong class="text-dark">{{ $orders->total() }}</strong> orders
    </span>
</div>

<div class="d-flex flex-column gap-3">
@foreach($orders as $order)
@php
    $s = strtolower(str_replace([' ', '-'], '_', $order->status));
    $map = [
        'delivered'        => ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#6ee7b7','stripe'=>'#10b981','icon'=>'bi-check-circle-fill','label'=>'Delivered'],
        'out_for_delivery' => ['bg'=>'#fef3c7','color'=>'#b45309','border'=>'#fcd34d','stripe'=>'#f59e0b','icon'=>'bi-bicycle','label'=>'Out for Delivery'],
        'shipped'          => ['bg'=>'#f3e8ff','color'=>'#6b21a8','border'=>'#c4b5fd','stripe'=>'#8b5cf6','icon'=>'bi-truck','label'=>'Shipped'],
        'packed'           => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-box-seam-fill','label'=>'Packed'],
        'confirmed'        => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-check2-square','label'=>'Confirmed'],
        'processing'       => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-gear-fill','label'=>'Processing'],
        'cancelled'        => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','stripe'=>'#ef4444','icon'=>'bi-x-circle-fill','label'=>'Cancelled'],
        'pending'          => ['bg'=>'#fef3c7','color'=>'#92400e','border'=>'#fde68a','stripe'=>'#f59e0b','icon'=>'bi-clock-fill','label'=>'Pending'],
    ];
    $st = $map[$s] ?? $map['pending'];

    $stageList = [
        ['key' => 'pending',          'label' => 'Placed'],
        ['key' => 'confirmed',        'label' => 'Confirmed'],
        ['key' => 'packed',           'label' => 'Packed'],
        ['key' => 'shipped',          'label' => 'Shipped'],
        ['key' => 'out_for_delivery', 'label' => 'Out for Delivery'],
        ['key' => 'delivered',        'label' => 'Delivered'],
    ];

    $stageRankMap = [
        'pending'          => 0,
        'placed'           => 0,
        'confirmed'        => 1,
        'processing'       => 1,
        'packed'           => 2,
        'shipped'          => 3,
        'out_for_delivery' => 4,
        'delivered'        => 5,
    ];

    $stepIdx = $stageRankMap[$s] ?? false;

    // First 3 product images
    $thumbs = $order->items->take(3)->map(fn($i) => $i->product?->main_image)->filter();
    $extraCount = max(0, $order->items->count() - 3);
@endphp

<div class="card border-0 shadow-xs rounded-4 overflow-hidden order-row"
     style="background: #ffffff; border: 1px solid #e2e8f0 !important; transition: all 0.2s ease;">

    {{-- Left accent stripe --}}
    <div class="d-flex">
        <div class="flex-shrink-0" style="width: 4px; background: {{ $st['stripe'] }};"></div>

        <div class="flex-grow-1 p-3 p-md-4">
            {{-- Top row --}}
            <div class="d-flex align-items-start justify-content-between gap-2.5 flex-wrap">

                {{-- Product thumbnails + order info --}}
                <div class="d-flex align-items-center gap-2.5 gap-md-3 min-w-0">
                    {{-- Stacked product thumbs --}}
                    <div class="d-flex align-items-center position-relative flex-shrink-0" style="height: 48px;">
                        @forelse($thumbs as $ti => $img)
                        <div class="rounded-3 border border-2 border-white overflow-hidden flex-shrink-0"
                             style="width: 46px; height: 46px; margin-left: {{ $ti > 0 ? '-12px' : '0' }}; z-index: {{ 10 - $ti }}; position: relative; box-shadow: 0 1px 4px rgba(0,0,0,0.1); background: #ffffff;">
                            <img src="{{ asset('storage/'.$img) }}" alt="product"
                                 style="width: 100%; height: 100%; object-fit: contain;">
                        </div>
                        @empty
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width: 46px; height: 46px; background: #f1f5f9; border: 1.5px solid #e2e8f0;">
                            <i class="bi bi-bag text-muted fs-5"></i>
                        </div>
                        @endforelse
                        @if($extraCount > 0)
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width: 46px; height: 46px; background: #e2e8f0; margin-left: -12px; z-index: 1; position: relative; font-size: 0.68rem; font-weight: 700; color: #64748b; border: 2px solid white;">
                            +{{ $extraCount }}
                        </div>
                        @endif
                    </div>

                    {{-- Order meta --}}
                    <div class="min-w-0">
                        <div class="d-flex align-items-center gap-1.5 mb-1 flex-wrap">
                            <span class="fw-bold text-dark font-monospace" style="font-size: 0.88rem;">#{{ $order->order_number }}</span>
                            <span class="badge rounded-pill fw-semibold px-2 py-0.5"
                                  style="background: {{ $st['bg'] }}; color: {{ $st['color'] }}; border: 1px solid {{ $st['border'] }}; font-size: 0.68rem;">
                                <i class="bi {{ $st['icon'] }} me-0.5"></i> {{ $st['label'] ?? ucfirst($order->status) }}
                            </span>
                            @if($order->payment_status === 'failed' || ($order->status === 'pending' && $order->payment_method === 'online'))
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold font-monospace shadow-xs bg-warning text-dark border border-warning" style="font-size: 0.68rem;">
                                    <i class="bi bi-exclamation-triangle-fill text-dark me-0.5"></i>Payment Incomplete
                                </span>
                            @endif
                            @if($order->delivery_otp && $order->status !== 'delivered' && $order->status !== 'cancelled')
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold font-monospace shadow-xs" style="background: #0f172a; color: #fcd34d; border: 1px solid rgba(245,158,11,0.4); font-size: 0.68rem;">
                                    <i class="bi bi-shield-lock-fill text-warning me-0.5"></i>OTP: {{ $order->delivery_otp }}
                                </span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted small flex-wrap" style="font-size: 0.76rem;">
                            <span><i class="bi bi-calendar3 me-1 opacity-75"></i>{{ $order->created_at ? $order->created_at->format('d M, Y') : '—' }}</span>
                            <span>•</span>
                            <span>{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Amount + Desktop CTA --}}
                <div class="d-flex align-items-center gap-2 ms-auto ms-sm-0 flex-wrap justify-content-end">
                    <div class="text-end me-1">
                        <div class="fw-bolder text-dark font-monospace" style="font-size: 0.98rem;">₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="text-muted small" style="font-size: 0.68rem;">Total Amount</div>
                    </div>
                    @if(in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed')
                    <a href="{{ route('checkout.payment_failed', $order) }}"
                       class="btn btn-sm fw-semibold rounded-pill px-3 py-1.5 d-none d-sm-inline-flex align-items-center gap-1 shadow-xs text-white"
                       style="background: #ea580c; border: none; font-size: 0.78rem; white-space: nowrap;">
                        <i class="bi bi-arrow-repeat"></i> <span>Proceed / Pay</span>
                    </a>
                    <form action="{{ route('customer.orders.cancel', $order->id) }}" method="POST" class="d-none d-sm-inline" onsubmit="return confirm('Are you sure you want to cancel this pending order #{{ $order->order_number }}?');">
                        @csrf
                        <input type="hidden" name="cancellation_reason" value="Cancelled pending order from My Orders">
                        <button type="submit" class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1" style="font-size: 0.76rem; white-space: nowrap;">
                            <i class="bi bi-x-circle"></i> <span>Cancel</span>
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('account.orders.show', $order) }}"
                       class="btn btn-sm fw-semibold rounded-pill px-3 py-1.5 d-none d-sm-inline-flex align-items-center gap-1 shadow-xs text-white"
                       style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; font-size: 0.78rem; white-space: nowrap;">
                        <span>Details</span> <i class="bi bi-arrow-right"></i>
                    </a>
                </div>

            </div>

            {{-- Mobile Full-Width Action Button --}}
            <div class="d-block d-sm-none mt-3 pt-2.5 border-top">
                @if(in_array($order->status, ['pending', 'failed']) || $order->payment_status === 'failed')
                <div class="d-flex gap-2 mb-2">
                    <a href="{{ route('checkout.payment_failed', $order) }}"
                       class="btn btn-warning flex-grow-1 rounded-pill py-2 fw-bold text-dark d-flex align-items-center justify-content-center gap-1.5 shadow-xs" style="font-size: 0.82rem; background: #ea580c; color: #ffffff !important;">
                        <i class="bi bi-arrow-repeat"></i> <span>Proceed / Pay</span>
                    </a>
                    <form action="{{ route('customer.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this pending order #{{ $order->order_number }}?');">
                        @csrf
                        <input type="hidden" name="cancellation_reason" value="Cancelled pending order from My Orders">
                        <button type="submit" class="btn btn-outline-danger rounded-pill py-2 px-3 fw-bold d-flex align-items-center justify-content-center gap-1" style="font-size: 0.8rem;">
                            <i class="bi bi-x-circle"></i> <span>Cancel</span>
                        </button>
                    </form>
                </div>
                @endif
                <a href="{{ route('account.orders.show', $order) }}"
                   class="btn btn-light border w-100 rounded-pill py-2 fw-semibold text-primary d-flex align-items-center justify-content-center gap-1.5 shadow-xs" style="font-size: 0.8rem;">
                    <span>View Order Details</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>

        </div>
    </div>
</div>
@endforeach
</div>

{{-- Pagination --}}
@if($orders->hasPages())
<div class="mt-4">
    {{ $orders->links('pagination::bootstrap-5') }}
</div>
@endif

@else
    @include('customer.account.components.empty-state', [
        'icon'        => 'bi-box-seam',
        'title'       => 'No Orders Found',
        'message'     => "You haven't placed any orders matching your criteria.",
        'button_text' => 'Start Shopping',
        'button_url'  => route('shop')
    ])
@endif

@push('styles')
<style>
.order-row:hover {
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08) !important;
    transform: translateY(-1px);
}
</style>
@endpush

@endsection

@if(isset($recommendedProducts) && count($recommendedProducts) > 0)
@section('full_width_account_content')
@include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
@endif
