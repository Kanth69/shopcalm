@extends('customer.account.layout')

@section('title', 'Order Details - #' . $order->order_number)

@section('account_content')

@php
    $statusStyle = match(strtolower($order->status)) {
        'delivered' => 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;',
        'shipped'   => 'background:#f3e8ff; color:#6b21a8; border:1px solid #d8b4fe;',
        'packed', 'processing', 'confirmed' => 'background:#dbeafe; color:#1e40af; border:1px solid #93c5fd;',
        'cancelled' => 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;',
        default     => 'background:#fef3c7; color:#92400e; border:1px solid #fde68a;'
    };
@endphp

{{-- Header Banner Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h4 class="fw-bold mb-0 text-white">Order #{{ $order->order_number }}</h4>
                    <span class="badge rounded-pill px-3 py-1 fw-bold ms-2" style="{{ $statusStyle }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-white-50 small mb-0">
                    <i class="bi bi-calendar3 me-1 opacity-75"></i>Placed on {{ $order->created_at ? $order->created_at->format('d M, Y h:i A') : 'N/A' }}
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('account.orders.index') }}" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Orders
                </a>
                <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-semibold btn-sm shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Order Details Grid --}}
<div class="row g-4 mb-4">
    {{-- Order Summary Card --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Order Summary</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted small">Order Reference:</span>
                    <span class="fw-bold text-dark small">#{{ $order->order_number }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted small">Order Date:</span>
                    <span class="fw-semibold text-dark small">{{ $order->created_at ? $order->created_at->format('d M, Y') : 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="text-muted small">Status:</span>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="{{ $statusStyle }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Amount:</span>
                    <span class="fw-bolder text-primary fs-6">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Shipping Address Card --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-geo-alt text-danger me-2"></i>Shipping Address</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-person-fill text-primary fs-5"></i>
                    <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem;">{{ $order->shipping_name }}</h6>
                </div>
                <p class="text-secondary small mb-2 ps-4" style="line-height: 1.5;">
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                    {{ $order->shipping_country }}
                </p>
                <div class="ps-4 text-muted small" style="font-size: 0.8rem;">
                    <div><i class="bi bi-envelope me-1 text-info"></i>{{ $order->shipping_email }}</div>
                    <div><i class="bi bi-telephone me-1 text-success"></i>{{ $order->shipping_phone }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Order Items Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bag-check text-primary me-2"></i>Purchased Items ({{ $order->items->count() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="d-flex flex-column">
            @foreach($order->items as $index => $item)
            @php $product = $item->product; @endphp
            <div class="d-flex align-items-center gap-3 p-4 flex-wrap {{ $index < $order->items->count()-1 ? 'border-bottom' : '' }}"
                 style="transition: background 0.15s;">
                {{-- Product Image --}}
                <div class="flex-shrink-0">
                    @if($product && $product->main_image)
                        <a href="{{ route('product.show', $product->slug) }}" target="_blank">
                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                 alt="{{ $item->product_name }}"
                                 class="rounded-3"
                                 style="width:72px; height:72px; object-fit:cover; border:1.5px solid #e2e8f0; transition:transform 0.2s;"
                                 onmouseover="this.style.transform='scale(1.05)'"
                                 onmouseout="this.style.transform='scale(1)'">
                        </a>
                    @else
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:72px; height:72px; background:#f1f5f9; border:1.5px solid #e2e8f0;">
                            <i class="bi bi-image text-muted fs-4"></i>
                        </div>
                    @endif
                </div>

                {{-- Product Name + Link --}}
                <div class="flex-grow-1 min-w-0">
                    @if($product)
                        <a href="{{ route('product.show', $product->slug) }}" target="_blank"
                           class="fw-bold text-dark text-decoration-none d-block mb-1"
                           style="font-size:0.92rem; line-height:1.3;"
                           title="View product">
                            {{ $item->product_name }}
                            <i class="bi bi-box-arrow-up-right ms-1 text-primary" style="font-size:0.7rem;"></i>
                        </a>
                        @if($product->category)
                        <span class="badge rounded-pill px-2 py-1 me-1" style="background:#f1f5f9; color:#64748b; font-size:0.68rem;">
                            <i class="bi bi-tag me-1"></i>{{ $product->category->name }}
                        </span>
                        @endif
                        @if($product->brand)
                        <span class="badge rounded-pill px-2 py-1" style="background:#f1f5f9; color:#64748b; font-size:0.68rem;">
                            <i class="bi bi-bookmark me-1"></i>{{ $product->brand->name }}
                        </span>
                        @endif
                    @else
                        <div class="fw-bold text-dark mb-1" style="font-size:0.92rem;">{{ $item->product_name }}</div>
                        <span class="text-muted small" style="font-size:0.75rem;">Product no longer available</span>
                    @endif
                </div>

                {{-- Price Breakdown --}}
                <div class="text-center flex-shrink-0" style="min-width:110px;">
                    <div class="fw-semibold text-dark" style="font-size:0.88rem;">₹{{ number_format($item->unit_price, 2) }}</div>
                    @if($item->original_price > $item->unit_price)
                        <div class="text-muted text-decoration-line-through" style="font-size:0.75rem;">₹{{ number_format($item->original_price, 2) }}</div>
                        <span class="badge rounded-pill px-2" style="background:#d1fae5; color:#065f46; font-size:0.65rem;">
                            Save ₹{{ number_format($item->offer_discount, 2) }}
                        </span>
                    @endif
                </div>

                {{-- Qty --}}
                <div class="flex-shrink-0 text-center" style="min-width:60px;">
                    <span class="badge bg-light text-dark border px-3 py-1 rounded-pill fw-bold" style="font-size:0.8rem;">
                        ×{{ $item->quantity }}
                    </span>
                    <div class="text-muted mt-1" style="font-size:0.65rem;">Qty</div>
                </div>

                {{-- Item Total --}}
                <div class="flex-shrink-0 text-end" style="min-width:90px;">
                    <div class="fw-bold text-dark" style="font-size:1rem;">₹{{ number_format($item->total_price, 2) }}</div>
                    <div class="text-muted" style="font-size:0.68rem;">Total</div>
                </div>

            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Payment & Total Receipt Summary Box --}}
<div class="row justify-content-end mb-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#f8fafc; border: 1px solid #e2e8f0 !important;">
            <div class="card-body p-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-receipt text-primary me-2"></i>Payment Summary</h6>
                
                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span>Subtotal after Offers:</span>
                    <span class="fw-semibold text-dark">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                </div>

                @if($order->coupon_discount_amount > 0)
                <div class="d-flex justify-content-between text-success small mb-2">
                    <span>Coupon Discount @if($order->coupon)({{ $order->coupon->code }})@endif:</span>
                    <span class="fw-bold">-₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                </div>
                @endif

                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span>Shipping Charges:</span>
                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 fw-bold">FREE</span>
                </div>

                <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark fs-6">Grand Total:</span>
                    <span class="fw-bolder text-primary fs-5">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@if(isset($recommendedProducts) && count($recommendedProducts) > 0)
@section('full_width_account_content')
{{-- Recommended Products Full Container Width --}}
@include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
@endif
