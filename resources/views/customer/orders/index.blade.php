@extends('customer.account.layout')

@section('title', 'My Orders')

@section('account_content')

{{-- ── Hero ──────────────────────────────────────────────── --}}
<div class="rounded-4 mb-4 overflow-hidden position-relative"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); min-height:120px;">
    <div class="position-absolute w-100 h-100"
         style="background-image:radial-gradient(circle,rgba(255,255,255,0.04) 1px,transparent 1px); background-size:24px 24px; top:0; left:0;"></div>
    <div class="position-relative p-4 d-flex align-items-center gap-4 flex-wrap">
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:60px; height:60px; background:rgba(99,102,241,0.25); border:1.5px solid rgba(99,102,241,0.5);">
            <i class="bi bi-box-seam-fill text-white fs-3"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="fw-bold text-white mb-1">My Orders</h5>
            <p class="text-white-50 small mb-0">Track shipments, view receipts &amp; manage your purchases</p>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.1);">
                <div class="fw-bold text-white fs-5 lh-1">{{ $totalCount }}</div>
                <div class="text-white-50" style="font-size:0.7rem; margin-top:2px;">Total</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3);">
                <div class="fw-bold lh-1" style="color:#6ee7b7; font-size:1.2rem;">{{ $deliveredCount }}</div>
                <div style="font-size:0.7rem; color:#6ee7b7; margin-top:2px; opacity:0.8;">Delivered</div>
            </div>
            <div class="text-center px-3 py-2 rounded-3"
                 style="background:rgba(251,191,36,0.15); border:1px solid rgba(251,191,36,0.3);">
                <div class="fw-bold lh-1" style="color:#fcd34d; font-size:1.2rem;">{{ $activeCount }}</div>
                <div style="font-size:0.7rem; color:#fcd34d; margin-top:2px; opacity:0.8;">Active</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Status Tab Pills ─────────────────────────────────── --}}
<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
    @php
        $tabs = [
            ''          => ['label'=>'All',       'icon'=>'bi-grid-3x3-gap'],
            'pending'   => ['label'=>'Pending',   'icon'=>'bi-clock'],
            'confirmed' => ['label'=>'Confirmed', 'icon'=>'bi-check2'],
            'packed'    => ['label'=>'Packed',    'icon'=>'bi-box'],
            'shipped'   => ['label'=>'Shipped',   'icon'=>'bi-truck'],
            'delivered' => ['label'=>'Delivered', 'icon'=>'bi-check-circle'],
            'cancelled' => ['label'=>'Cancelled', 'icon'=>'bi-x-circle'],
        ];
        $activeTab = request('status', '');
    @endphp
    @foreach($tabs as $val => $tab)
    <a href="{{ route('account.orders.index', array_merge(request()->only('search'), $val ? ['status'=>$val] : [])) }}"
       class="btn btn-sm rounded-pill fw-semibold px-3"
       style="font-size:0.78rem; transition:all 0.2s;
              {{ $activeTab === $val
                ? 'background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; box-shadow:0 2px 8px rgba(99,102,241,0.35);'
                : 'background:#fff; color:#64748b; border:1px solid #e2e8f0;' }}">
        <i class="bi {{ $tab['icon'] }} me-1"></i>{{ $tab['label'] }}
    </a>
    @endforeach
</div>

{{-- ── Search Bar ───────────────────────────────────────── --}}
<div class="card border-0 shadow-sm rounded-4 mb-4" style="border:1px solid #e2e8f0 !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('account.orders.index') }}" class="d-flex gap-2 align-items-center">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="input-group flex-grow-1">
                <span class="input-group-text bg-white border-end-0 text-muted"
                      style="border-radius:10px 0 0 10px; border-color:#e2e8f0;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search"
                       class="form-control border-start-0 ps-0"
                       placeholder="Search by order number…"
                       value="{{ request('search') }}"
                       style="border-radius:0 10px 10px 0; border-color:#e2e8f0; font-size:0.85rem;">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold"
                    style="font-size:0.85rem; white-space:nowrap;">
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('account.orders.index', request('status') ? ['status'=>request('status')] : []) }}"
               class="btn btn-light border rounded-pill px-3" style="font-size:0.85rem;" title="Clear">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
    </div>
</div>

{{-- ── Orders ───────────────────────────────────────────── --}}
@if($orders->isNotEmpty())

<p class="text-muted small mb-3 px-1">
    Showing <strong class="text-dark">{{ $orders->firstItem() }}–{{ $orders->lastItem() }}</strong>
    of <strong class="text-dark">{{ $orders->total() }}</strong> order(s)
</p>

<div class="d-flex flex-column gap-3">
@foreach($orders as $order)
@php
    $s = strtolower($order->status);
    $map = [
        'delivered'  => ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#6ee7b7','stripe'=>'#10b981','icon'=>'bi-check-circle-fill'],
        'shipped'    => ['bg'=>'#f3e8ff','color'=>'#6b21a8','border'=>'#c4b5fd','stripe'=>'#8b5cf6','icon'=>'bi-truck'],
        'packed'     => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-box-seam-fill'],
        'confirmed'  => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-check2-square'],
        'processing' => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd','stripe'=>'#3b82f6','icon'=>'bi-gear-fill'],
        'cancelled'  => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','stripe'=>'#ef4444','icon'=>'bi-x-circle-fill'],
        'pending'    => ['bg'=>'#fef3c7','color'=>'#92400e','border'=>'#fde68a','stripe'=>'#f59e0b','icon'=>'bi-clock-fill'],
    ];
    $st = $map[$s] ?? $map['pending'];

    $steps    = ['pending','confirmed','packed','shipped','delivered'];
    $stepIdx  = array_search($s, $steps);

    $thumbs     = $order->items->take(3)->map(fn($i) => $i->product?->main_image)->filter();
    $extraCount = max(0, $order->items->count() - 3);
@endphp

<div class="card border-0 rounded-4 overflow-hidden order-row"
     style="box-shadow:0 1px 8px rgba(15,23,42,0.07); transition:box-shadow 0.2s,transform 0.2s; border:1.5px solid #f1f5f9 !important;">
    <div class="d-flex">
        {{-- Colored left accent --}}
        <div class="flex-shrink-0" style="width:5px; background:{{ $st['stripe'] }}; border-radius:16px 0 0 16px;"></div>

        <div class="flex-grow-1 p-4">
            {{-- Top row --}}
            <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">

                {{-- Product thumbnails + order meta --}}
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    {{-- Stacked thumbs --}}
                    <div class="d-flex align-items-center" style="height:52px;">
                        @forelse($thumbs as $ti => $img)
                        <div class="rounded-3 border-2 border-white overflow-hidden flex-shrink-0"
                             style="width:50px; height:50px; margin-left:{{ $ti > 0 ? '-14px' : '0' }}; z-index:{{ 10-$ti }}; position:relative; border:2px solid white; box-shadow:0 1px 4px rgba(0,0,0,0.12);">
                            <img src="{{ asset('storage/'.$img) }}" alt="product"
                                 style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        @empty
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:50px; height:50px; background:#f1f5f9; border:2px solid #e2e8f0;">
                            <i class="bi bi-bag text-muted fs-5"></i>
                        </div>
                        @endforelse
                        @if($extraCount > 0)
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:50px; height:50px; background:#e2e8f0; margin-left:-14px; z-index:1; position:relative; font-size:0.7rem; font-weight:700; color:#64748b; border:2px solid white;">
                            +{{ $extraCount }}
                        </div>
                        @endif
                    </div>

                    {{-- Order meta --}}
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <span class="fw-bold text-dark" style="font-size:0.9rem;">#{{ $order->order_number }}</span>
                            <span class="badge rounded-pill fw-semibold px-2 py-1"
                                  style="background:{{ $st['bg'] }}; color:{{ $st['color'] }}; border:1px solid {{ $st['border'] }}; font-size:0.68rem;">
                                <i class="bi {{ $st['icon'] }} me-1"></i>{{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3 flex-wrap" style="font-size:0.78rem; color:#64748b;">
                            <span><i class="bi bi-calendar3 me-1 opacity-75"></i>{{ $order->created_at ? $order->created_at->format('d M Y') : '—' }}</span>
                            <span><i class="bi bi-bag me-1 opacity-75"></i>{{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</span>
                            @if($order->payment_method)
                            <span><i class="bi bi-credit-card me-1 opacity-75"></i>{{ strtoupper($order->payment_method) }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Amount + CTA --}}
                <div class="d-flex align-items-center gap-3 ms-auto flex-wrap">
                    <div class="text-end">
                        <div class="fw-bold text-dark" style="font-size:1.05rem;">₹{{ number_format($order->total_amount, 2) }}</div>
                        <div class="text-muted" style="font-size:0.72rem;">Total paid</div>
                    </div>
                    <a href="{{ route('account.orders.show', $order) }}"
                       class="btn btn-sm fw-semibold rounded-pill px-4 py-2"
                       style="background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; font-size:0.8rem; white-space:nowrap; box-shadow:0 2px 8px rgba(99,102,241,0.3);">
                        View Details <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

            </div>

            {{-- ── Progress Tracker ── --}}
            @if($s !== 'cancelled' && $stepIdx !== false)
            <div class="mt-4 pt-3" style="border-top:1px dashed #e2e8f0;">
                <div class="position-relative d-flex align-items-center justify-content-between" style="padding:0 2px;">
                    <div class="position-absolute" style="top:13px; left:14px; right:14px; height:3px; background:#e2e8f0; border-radius:99px; z-index:0;"></div>
                    @php $fillPct = $stepIdx > 0 ? round(($stepIdx / (count($steps)-1)) * 100) : 0; @endphp
                    <div class="position-absolute" style="top:13px; left:14px; width:calc({{ $fillPct }}% - 0px); max-width:calc(100% - 28px); height:3px; background:{{ $st['stripe'] }}; border-radius:99px; z-index:1;"></div>

                    @foreach($steps as $si => $step)
                    @php $done = $si <= $stepIdx; $current = $si === $stepIdx; @endphp
                    <div class="d-flex flex-column align-items-center position-relative flex-fill" style="z-index:2;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px; height:28px;
                                    background:{{ $done ? $st['stripe'] : '#e2e8f0' }};
                                    border:2px solid {{ $done ? $st['stripe'] : '#e2e8f0' }};
                                    box-shadow:{{ $current ? '0 0 0 4px '.$st['bg'] : 'none' }};
                                    transition:all 0.3s;">
                            @if($done)
                                <i class="bi bi-check text-white" style="font-size:0.7rem;"></i>
                            @else
                                <div style="width:7px;height:7px;border-radius:50%;background:#cbd5e1;"></div>
                            @endif
                        </div>
                        <span class="mt-1 text-center"
                              style="font-size:0.62rem; line-height:1.2; white-space:nowrap;
                                     color:{{ $done ? $st['color'] : '#94a3b8' }};
                                     font-weight:{{ $current ? '700' : ($done ? '600' : '400') }};">
                            {{ ucfirst($step) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            @elseif($s === 'cancelled')
            <div class="mt-3 pt-3 d-flex align-items-center gap-2" style="border-top:1px dashed #e2e8f0;">
                <div class="rounded-pill px-3 py-1 d-inline-flex align-items-center gap-2"
                     style="background:#fee2e2; border:1px solid #fca5a5;">
                    <i class="bi bi-x-circle-fill" style="color:#dc2626; font-size:0.8rem;"></i>
                    <span style="color:#991b1b; font-size:0.78rem; font-weight:600;">Order Cancelled</span>
                </div>
            </div>
            @endif

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
    box-shadow: 0 8px 32px rgba(99,102,241,0.13) !important;
    transform: translateY(-2px);
}
</style>
@endpush

@endsection
