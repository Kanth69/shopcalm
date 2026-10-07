@extends('admin.layouts.app')

@section('header', 'Order Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $order->order_number }}</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        @if(!in_array($order->status, ['cancelled', 'delivered', 'returned']))
            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#adminCancelOrderModal">
                <i class="bi bi-x-circle me-1"></i> Cancel Order
            </button>
        @endif
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill px-3.5 fw-semibold shadow-xs">
            <i class="bi bi-file-earmark-pdf-fill me-1.5 text-danger"></i> Print Invoice
        </a>
        <a href="{{ route('admin.orders.packing-slip', $order) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold shadow-xs bg-white">
            <i class="bi bi-printer-fill me-1.5 text-primary"></i> Packing Slip
        </a>
    </div>
@endsection

@section('content')

{{-- 1. Full-Width Order Hero Banner --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3.5">
            <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(99, 102, 241, 0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <h4 class="mb-0 fw-bolder text-dark" style="letter-spacing: -0.3px;">Order #{{ $order->order_number }}</h4>
                    <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill" style="font-size: 0.72rem;">
                        <i class="bi bi-shield-check me-1 text-success"></i> Verified Order
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2.5 text-muted flex-wrap" style="font-size: 0.82rem;">
                    <span><i class="bi bi-calendar3 me-1 text-primary"></i> Placed on {{ $order->created_at->format('d M, Y \a\t h:i A') }}</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-credit-card me-1 text-success"></i> Payment: <strong class="text-dark">{{ ($order->payment_method === 'wallet' || ($order->total_amount <= 0 && $order->wallet_amount_used > 0)) ? 'WALLET (100% PAID)' : strtoupper($order->payment_method ?? 'COD') }}</strong></span>
                    <span>&bull;</span>
                    <span>Payment Status: <strong class="text-dark text-capitalize">{{ $order->payment_status ?? 'Paid' }}</strong></span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @include('customer.components.order-status-badge', ['status' => $order->status])
        </div>
    </div>
</div>

{{-- 2. Top Overview Row: 3 Equal Information Cards --}}
<div class="row g-3 mb-4">
    {{-- Card 1: Customer Information --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">
                    <i class="bi bi-person-circle me-2 text-primary"></i>Customer Profile
                </h6>
                <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.68rem;">Buyer</span>
            </div>
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #6366f1, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 700; color: #fff; flex-shrink: 0;">
                        {{ strtoupper(substr($order->shipping_name ?? ($order->user->name ?? 'C'), 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.92rem;">{{ $order->shipping_name ?? ($order->user->name ?? '—') }}</h6>
                        <div class="text-muted text-truncate" style="font-size: 0.78rem;">{{ $order->shipping_email ?? ($order->user->email ?? '—') }}</div>
                    </div>
                </div>

                <div class="p-2.5 rounded-3 mb-2" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center text-muted" style="font-size: 0.78rem;">
                        <i class="bi bi-telephone-fill me-2 text-primary"></i>
                        <span class="text-dark fw-semibold">{{ $order->shipping_phone ?? ($order->user->mobile_number ?? 'N/A') }}</span>
                    </div>
                </div>

                @if($order->user)
                <div class="d-flex align-items-center justify-content-between px-1 text-muted" style="font-size: 0.74rem;">
                    <span>Registered Customer</span>
                    <span class="text-dark fw-semibold">ID #{{ $order->user->id }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Card 2: Shipping & Delivery Address --}}
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">
                    <i class="bi bi-geo-alt-fill me-2 text-danger"></i>Delivery Address
                </h6>
                <span class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.68rem;">Shipping</span>
            </div>
            <div class="card-body p-3.5">
                <div class="p-3 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.82rem; line-height: 1.55; color: #334155;">
                    <div>
                        <strong class="text-dark d-block mb-1">{{ $order->shipping_name ?? ($order->user->name ?? '') }}</strong>
                        <div>{{ $order->shipping_address }}</div>
                        <div>{{ $order->shipping_city }}, {{ $order->shipping_state }}</div>
                    </div>
                    <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
                        <span class="fw-bold text-dark"><i class="bi bi-pin-map-fill me-1 text-danger"></i>PIN: {{ $order->shipping_zip }}</span>
                        <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 0.7rem;">{{ $order->shipping_country ?? 'India' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card 3: Logistics & Fulfillment Status --}}
    <div class="col-12 col-md-12 col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">
                    <i class="bi bi-truck me-2 text-primary"></i>Logistics & Fulfillment
                </h6>
                <div>{!! $order->fulfillment_badge !!}</div>
            </div>
            <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2.5" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.68rem;">Stage: <strong class="text-dark text-capitalize">{{ $order->status }}</strong></span>
                        @include('customer.components.order-status-badge', ['status' => $order->status])
                    </div>

                    @if($order->isLocalBengaluruDelivery())
                        <div class="d-flex align-items-center justify-content-between mb-2 text-muted" style="font-size: 0.78rem;">
                            <span>Delivery Mode:</span>
                            <span class="badge bg-info bg-opacity-10 text-info fw-bold"><i class="bi bi-geo-fill me-1"></i>Bengaluru Local Fleet</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2 text-muted" style="font-size: 0.78rem;">
                            <span>Assigned Rider:</span>
                            <span class="text-dark fw-bold">{{ $order->rider_name ?: 'Unassigned' }}</span>
                        </div>
                        @if($order->rider_phone)
                        <div class="d-flex align-items-center justify-content-between mb-2 text-muted" style="font-size: 0.78rem;">
                            <span>Rider Contact:</span>
                            <span class="text-primary fw-semibold">{{ $order->rider_phone }}</span>
                        </div>
                        @endif
                        <div class="d-flex align-items-center justify-content-between mb-3 text-muted" style="font-size: 0.78rem;">
                            <span>Delivery Schedule:</span>
                            <span class="text-secondary small">{{ $order->delivery_slot ?: 'Local Fleet Delivery (1-2 Days)' }}</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-between mb-2 text-muted" style="font-size: 0.78rem;">
                            <span>Courier Partner:</span>
                            <span class="text-dark fw-bold"><i class="bi bi-box-seam me-1 text-primary"></i>{{ $order->courier_partner ?: 'Not Assigned' }}</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3 text-muted" style="font-size: 0.78rem;">
                            <span>AWB Tracking:</span>
                            @if($order->tracking_number)
                                <span class="font-monospace fw-bold text-dark">{{ $order->tracking_number }}</span>
                            @else
                                <span class="text-muted small">Pending Dispatch</span>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="d-flex gap-2 pt-2 border-top">
                    <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn btn-sm btn-dark rounded-pill flex-fill fw-semibold" style="font-size: 0.76rem;">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Tax Invoice
                    </a>
                    <a href="{{ route('admin.orders.packing-slip', $order) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill flex-fill fw-semibold bg-white" style="font-size: 0.76rem;">
                        <i class="bi bi-printer me-1"></i> Packing Slip
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. Full-Width Ordered Items Table & Totals Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">
            <i class="bi bi-bag-check-fill me-2 text-primary"></i>Ordered Line Items ({{ $order->items->count() }})
        </h6>
        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill" style="font-size: 0.75rem;">
            {{ $order->items->sum('quantity') }} Total Units
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4 text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Product Item</th>
                        <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Base MRP</th>
                        <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Discount</th>
                        <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Paid Unit</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th class="text-end text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Cost (COGS)</th>
                        @endif
                        <th class="text-center text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Qty</th>
                        <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Line Total</th>
                        @if(auth()->user()->isSuperAdmin())
                            <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Profit</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ $item->product_name }}</div>
                            @if(isset($item->sku) && $item->sku)
                                <div class="text-muted font-monospace" style="font-size: 0.72rem;">SKU: {{ $item->sku }}</div>
                            @endif
                        </td>
                        <td class="text-end text-muted text-decoration-line-through" style="font-size: 0.82rem;">
                            ₹{{ number_format($item->original_price, 2) }}
                        </td>
                        <td class="text-end text-success fw-medium" style="font-size: 0.82rem;">
                            @if($item->offer_discount > 0)
                                -₹{{ number_format($item->offer_discount, 2) }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-dark" style="font-size: 0.85rem;">
                            ₹{{ number_format($item->unit_price, 2) }}
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td class="text-end text-muted fw-medium" style="font-size: 0.82rem;">
                                ₹{{ number_format($item->cost_price, 2) }}
                            </td>
                        @endif
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                                {{ $item->quantity }}
                            </span>
                        </td>
                        <td class="pe-4 text-end fw-bold text-dark" style="font-size: 0.9rem;">
                            ₹{{ number_format($item->total_price, 2) }}
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td class="pe-4 text-end">
                                <span class="fw-bold {{ $item->profit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.85rem;">
                                    {{ $item->profit >= 0 ? '+' : '' }}₹{{ number_format($item->profit, 2) }}
                                </span>
                                <div class="small text-muted" style="font-size: 0.68rem;">({{ $item->profit_margin }}%)</div>
                            </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Summary & Totals Box --}}
        <div class="p-4 border-top" style="background: #fafafa; border-radius: 0 0 16px 16px;">
            <div class="row justify-content-end">
                <div class="col-md-7 col-xl-5">
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.84rem;">
                        <span class="text-muted">Cart Subtotal:</span>
                        <span class="fw-semibold text-dark">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                    </div>
                    @if($order->coupon_discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.84rem;">
                        <span class="text-success fw-medium">
                            <i class="bi bi-tag-fill me-1"></i>Coupon Discount @if($order->coupon)({{ $order->coupon->code }})@endif:
                        </span>
                        <span class="fw-bold text-success">-₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2" style="font-size: 0.84rem;">
                        <span class="text-muted">Delivery Charges:</span>
                        <span class="fw-semibold text-success">FREE</span>
                    </div>
                    <hr class="my-2.5" style="border-color: #e2e8f0;">
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span class="fw-bold text-dark" style="font-size: 0.95rem;">Grand Total Paid:</span>
                        <span class="fw-bolder fs-4" style="color: #6366f1;">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isSuperAdmin())
{{-- 4. Full-Width Super Admin Exclusive Profit & Financial Analytics Widget --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-header border-0 py-3.5 px-4 d-flex align-items-center justify-content-between" style="background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.08) !important;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold rounded-pill" style="font-size: 0.68rem; letter-spacing: 0.5px;">
                <i class="bi bi-shield-lock-fill me-1"></i> SUPER ADMIN ONLY
            </span>
            <h6 class="mb-0 fw-bold text-white" style="font-size: 0.92rem;">Financial & Unit Economics Analytics</h6>
        </div>
        <span class="text-white-50 small">Order #{{ $order->order_number }}</span>
    </div>
    <div class="card-body p-4">
        @php
            $isRealizedIncomeOrder = in_array($order->status, \App\Models\Order::INCOME_STATUSES);
        @endphp
        <div class="row g-3 text-center text-sm-start">
            <div class="col-6 col-md-3">
                <div class="text-white-50 small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    {{ $isRealizedIncomeOrder ? 'Order Revenue' : 'Order Amount (' . ucfirst($order->status) . ')' }}
                </div>
                <div class="fs-4 fw-bolder {{ $isRealizedIncomeOrder ? 'text-white' : 'text-white-50' }}">
                    ₹{{ number_format($isRealizedIncomeOrder ? $order->total_amount : 0, 2) }}
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-white-50 small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Product Cost (COGS)</div>
                <div class="fs-4 fw-bolder text-white-50">₹{{ number_format($isRealizedIncomeOrder ? $order->total_cost : 0, 2) }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-white-50 small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Gross Profit</div>
                @if($isRealizedIncomeOrder)
                <div class="fs-4 fw-bolder {{ $order->gross_profit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $order->gross_profit >= 0 ? '+' : '' }}₹{{ number_format($order->gross_profit, 2) }}
                </div>
                @else
                <div class="fs-4 fw-bolder text-secondary">₹0.00</div>
                @endif
            </div>
            <div class="col-6 col-md-3">
                <div class="text-white-50 small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Profit Margin</div>
                <div class="d-inline-flex align-items-center gap-1.5 mt-1">
                    @if($isRealizedIncomeOrder)
                    <span class="badge rounded-pill px-3 py-1.5 fw-bolder {{ $order->profit_margin >= 20 ? 'bg-success text-white' : ($order->profit_margin >= 0 ? 'bg-warning text-dark' : 'bg-danger text-white') }}" style="font-size: 0.88rem;">
                        {{ $order->profit_margin }}%
                    </span>
                    @else
                    <span class="badge rounded-pill px-3 py-1.5 fw-bolder bg-secondary text-white" style="font-size: 0.82rem;">
                        Not Counted ({{ ucfirst($order->status) }})
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── Admin Cancel Order Modal ── --}}
@if(!in_array($order->status, ['cancelled', 'delivered', 'returned']))
<div class="modal fade" id="adminCancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="bi bi-shield-x me-2"></i> Cancel Order #{{ $order->order_number }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2.5 px-3 small rounded-3 mb-3 border-0 d-flex align-items-center gap-2" style="background: #fffbe6; color: #856404;">
                        <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
                        <span>Cancelling will automatically restock inventory items. 100% refund will be issued to the customer with zero fee.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Cancellation Reason <span class="text-danger">*</span></label>
                        <select name="cancellation_reason" class="form-select fw-semibold" required>
                            <option value="" selected disabled>Select reason...</option>
                            <option value="Item Out of Stock / Unavailable">Item Out of Stock / Unavailable</option>
                            <option value="Product Damaged in Warehouse">Product Damaged in Warehouse</option>
                            <option value="Courier / Delivery Logistics Unserviceable">Courier / Delivery Logistics Unserviceable</option>
                            <option value="Customer Requested Cancellation via Support">Customer Requested Cancellation via Support</option>
                            <option value="Incorrect Pricing / Technical System Issue">Incorrect Pricing / Technical System Issue</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Refund Destination Mode <span class="text-danger">*</span></label>
                        <select name="refund_method" class="form-select fw-semibold" required>
                            <option value="wallet" selected>⚡ Instant Store Wallet Credit (100% Refund)</option>
                            <option value="bank">🏦 Bank / Original Gateway Payout Queue</option>
                        </select>
                        <small class="text-muted d-block mt-1">Instant Store Wallet credit allows the customer to use funds immediately for reorders.</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Admin Internal Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal remarks for store log or customer notice..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="bi bi-x-circle me-1"></i> Confirm Cancellation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
