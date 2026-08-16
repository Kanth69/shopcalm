@extends('admin.layouts.app')

@section('header', 'Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
@endsection

@section('content')

{{-- Header Banner --}}
<div class="card mb-4" style="border-radius: 14px !important;">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm" style="border-radius: 10px; border: 1px solid #e2e8f0;" title="Back to Orders">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h4 class="mb-1 fw-bolder text-dark" style="letter-spacing:-0.3px;">Order #{{ $order->order_number }}</h4>
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.8rem;">
                    <span><i class="bi bi-calendar3 me-1"></i>Placed on {{ $order->created_at->format('d M, Y \a\t h:i A') }}</span>
                    <span>•</span>
                    <span><i class="bi bi-credit-card me-1"></i>Payment: {{ strtoupper($order->payment_method ?? 'COD') }}</span>
                </div>
            </div>
        </div>
        <div>
            @include('customer.components.order-status-badge', ['status' => $order->status])
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Order Items --}}
    <div class="col-lg-8">
        <div class="card h-100" style="border-radius: 14px !important;">
            <div class="card-header py-3" style="background:#fff; border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
                <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">
                    <i class="bi bi-basket me-2 text-primary"></i>Ordered Items ({{ $order->items->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                            <tr>
                                <th class="ps-4 text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Product</th>
                                <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Base Price</th>
                                <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Discount</th>
                                <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Unit Price</th>
                                <th class="text-center text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Qty</th>
                                <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark" style="font-size:0.85rem;">{{ $item->product_name }}</div>
                                    @if(isset($item->sku))
                                        <div style="font-size:0.7rem; color:#94a3b8;">SKU: {{ $item->sku }}</div>
                                    @endif
                                </td>
                                <td class="text-end text-muted text-decoration-line-through" style="font-size:0.82rem;">
                                    ₹{{ number_format($item->original_price, 2) }}
                                </td>
                                <td class="text-end text-success fw-medium" style="font-size:0.82rem;">
                                    -₹{{ number_format($item->offer_discount, 2) }}
                                </td>
                                <td class="text-end fw-semibold text-dark" style="font-size:0.85rem;">
                                    ₹{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2.5 py-1" style="font-size:0.75rem; border-radius:6px;">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="pe-4 text-end fw-bold text-dark" style="font-size:0.88rem;">
                                    ₹{{ number_format($item->total_price, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Box --}}
                <div class="p-4 border-top" style="background: #fafafa; border-radius: 0 0 14px 14px;">
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2" style="font-size: 0.83rem;">
                                <span class="text-muted">Subtotal (after product offers):</span>
                                <span class="fw-semibold text-dark">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                            </div>
                            @if($order->coupon_discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2" style="font-size: 0.83rem;">
                                <span class="text-success fw-medium">
                                    <i class="bi bi-tag-fill me-1"></i>Coupon Discount @if($order->coupon)({{ $order->coupon->code }})@endif:
                                </span>
                                <span class="fw-bold text-success">-₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                            </div>
                            @endif
                            <hr class="my-2" style="border-color:#e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center pt-1">
                                <span class="fw-bold text-dark" style="font-size:0.95rem;">Final Total Amount:</span>
                                <span class="fw-bolder" style="font-size:1.3rem; color:#6366f1;">₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">
        {{-- Status Update Card --}}
        <div class="card mb-4" style="border-radius: 14px !important;">
            <div class="card-header py-3" style="background:#fff; border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
                <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">
                    <i class="bi bi-gear-wide-connected me-2 text-primary"></i>Update Order Status
                </h6>
            </div>
            <div class="card-body p-3">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Current Status</label>
                        <select name="status" class="form-select mb-3" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.875rem;">
                            @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'packed' => 'Packed', 'shipped' => 'Shipped', 'out for delivery' => 'Out for Delivery', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $val => $lbl)
                                <option value="{{ $val }}" {{ $order->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.85rem;">
                        <i class="bi bi-check-circle me-1"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        {{-- Customer & Shipping Details Card --}}
        <div class="card" style="border-radius: 14px !important;">
            <div class="card-header py-3" style="background:#fff; border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
                <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">
                    <i class="bi bi-person-bounding-box me-2 text-primary"></i>Customer & Shipping Details
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:700; color:#fff; flex-shrink:0;">
                        {{ strtoupper(substr($order->shipping_name ?? ($order->user->name ?? 'C'), 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark" style="font-size:0.9rem;">{{ $order->shipping_name ?? ($order->user->name ?? '—') }}</h6>
                        <div style="font-size:0.75rem; color:#64748b;">{{ $order->shipping_email ?? ($order->user->email ?? '—') }}</div>
                    </div>
                </div>

                <div class="p-3 mb-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center text-muted mb-1" style="font-size: 0.75rem;">
                        <i class="bi bi-telephone me-2 text-primary"></i>
                        <span class="text-dark fw-medium">{{ $order->shipping_phone ?? 'N/A' }}</span>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-2" style="font-size:0.8rem; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">
                    <i class="bi bi-geo-alt me-1 text-primary"></i>Shipping Address
                </h6>
                <div class="p-3" style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0; font-size:0.83rem; line-height: 1.5; color:#334155;">
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

