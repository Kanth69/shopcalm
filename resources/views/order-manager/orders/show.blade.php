@extends('order-manager.layouts.app')

@section('title', 'Order #' . $order->order_number)
@section('header', 'Order Management & Fulfillment')

@section('content')

@php
    $stages = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered'];
    $currentStatusIndex = array_search($order->status, $stages);
    $isCancelled = in_array($order->status, ['cancelled', 'returned']);
@endphp

<!-- ── 1. Master Order Header & Actions Toolbar ── -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white" style="border-top: 4px solid #4f46e5 !important;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2.5 flex-wrap mb-1.5">
                <h3 class="fw-bolder text-dark mb-0 font-monospace" style="letter-spacing: -0.5px; font-size: 1.5rem;">
                    #{{ $order->order_number }}
                </h3>
                <span id="topStatusBadge" class="badge rounded-pill px-3 py-1.5 fw-bold shadow-xs" 
                      style="background: #eef2ff; color: #4338ca; border: 1.5px solid #c7d2fe; font-size: 0.78rem;">
                    Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                </span>
                <span id="topFulfillmentBadge">{!! $order->fulfillment_badge !!}</span>
            </div>
            <div class="text-secondary small d-flex align-items-center gap-2 flex-wrap" style="font-size: 0.82rem;">
                <span>Customer: <strong class="text-dark">{{ $order->shipping_name }}</strong></span>
                <span>&bull;</span>
                <span>Placed: <strong class="text-dark">{{ $order->created_at->format('d M Y, h:i A') }}</strong> ({{ $order->created_at->diffForHumans() }})</span>
            </div>
        </div>

        <!-- Quick Action Buttons Toolbar -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('order-manager.orders.index') }}" class="btn btn-light border btn-sm rounded-pill px-3.5 py-2 fw-bold text-dark shadow-xs" style="font-size: 0.82rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Queue
            </a>
            @if(!in_array($order->status, ['cancelled', 'delivered', 'returned']))
                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-xs bg-white" data-bs-toggle="modal" data-bs-target="#staffCancelOrderModal" style="font-size: 0.82rem;">
                    <i class="bi bi-x-circle me-1"></i> Cancel Order
                </button>
            @endif
            @if($order->isLocalBengaluruDelivery())
                <a href="{{ route('order-manager.orders.manifest', $order) }}" target="_blank" class="btn text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-xs" style="background: #0284c7; font-size: 0.82rem;">
                    <i class="bi bi-file-earmark-text me-1"></i> Run-Sheet
                </a>
            @endif
            <a href="{{ route('order-manager.orders.shipping-label', $order) }}" target="_blank" class="btn text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-xs" style="background: #0f172a; font-size: 0.82rem;">
                <i class="bi bi-tag-fill me-1 text-warning"></i> Shipping Label (4x6)
            </a>
            <a href="{{ route('order-manager.orders.packing-slip', $order) }}" target="_blank" class="btn text-white btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-xs" style="background: #4f46e5; font-size: 0.82rem;">
                <i class="bi bi-printer me-1"></i> Packing Slip
            </a>
        </div>
    </div>

    <!-- Logistics & Payment Channel Strip -->
    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2.5 mb-3">
        <div class="d-flex align-items-center gap-2.5">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                 style="width: 32px; height: 32px; background: #4f46e5; font-size: 0.88rem;">
                <i class="bi bi-truck"></i>
            </div>
            <div id="topLogisticsInfo" class="text-dark small" style="font-size: 0.84rem;">
                @if($order->isLocalBengaluruDelivery())
                    Bengaluru Local Fleet: Rider <strong>{{ $order->rider_name ?? 'Unassigned' }}</strong> {{ $order->rider_phone ? ' (' . $order->rider_phone . ')' : '' }}
                @else
                    National Courier: <strong>{{ $order->courier_partner ?? 'Pending Courier Partner' }}</strong> &bull; AWB: <strong class="font-monospace text-primary">{{ $order->tracking_number ?? 'Unassigned' }}</strong>
                @endif
            </div>
        </div>

        <div class="small text-secondary d-flex align-items-center gap-1.5">
            <span>Payment:</span>
            <span class="badge bg-white text-dark border fw-bold text-uppercase px-2.5 py-1" style="font-size: 0.72rem;">
                {{ $order->payment_method }}
            </span>
            <span class="badge rounded-pill px-2.5 py-1 fw-bold {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.72rem;">
                {{ ucfirst($order->payment_status ?? 'pending') }}
            </span>
        </div>
    </div>

    <!-- Status Stepper Progress Bar -->
    @if(!$isCancelled)
        <div class="position-relative my-4 px-2" id="trackerContainer">
            <div class="progress" style="height: 6px; background-color: #e2e8f0; border-radius: 9999px;">
                @php
                    $pct = $currentStatusIndex !== false ? (($currentStatusIndex) / (count($stages) - 1)) * 100 : 0;
                @endphp
                <div id="trackerProgressBar" class="progress-bar" role="progressbar" 
                     style="width: {{ $pct }}%; background: linear-gradient(90deg, #4f46e5, #10b981); transition: width 0.4s ease;" 
                     aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>

            <div class="d-flex justify-content-between position-relative mt-2 text-center" style="margin-top: -16px !important;">
                @foreach($stages as $idx => $st)
                    @php
                        $isPast = $currentStatusIndex !== false && $idx <= $currentStatusIndex;
                        $isCurrent = $currentStatusIndex !== false && $idx === $currentStatusIndex;
                    @endphp
                    <div style="width: 14%;" class="stage-step" data-stage="{{ $st }}" data-index="{{ $idx }}">
                        <div class="stage-dot rounded-circle d-inline-flex align-items-center justify-content-center shadow-xs" 
                             style="width: 28px; height: 28px; font-size: 0.75rem; font-weight: bold; background: {{ $isPast ? '#10b981' : '#ffffff' }}; color: {{ $isPast ? '#ffffff' : '#64748b' }}; border: 2px solid {{ $isCurrent ? '#4f46e5' : ($isPast ? '#10b981' : '#cbd5e1') }}; transition: all 0.3s ease;">
                            @if($isPast)
                                <i class="bi bi-check-lg"></i>
                            @else
                                {{ $idx + 1 }}
                            @endif
                        </div>
                        <div class="stage-label small fw-semibold mt-1.5 d-none d-md-block {{ $isCurrent ? 'text-primary fw-bold' : ($isPast ? 'text-dark' : 'text-muted') }}" 
                             style="font-size: 0.72rem; text-transform: capitalize;">
                            {{ $st }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        @php
            $cancellation = $order->cancellation;
            $isAdminCancelled = false;

            if ($cancellation && $cancellation->cancelled_by_type === 'admin') {
                $isAdminCancelled = true;
            } elseif ($cancellation && $cancellation->cancelledBy && $cancellation->cancelledBy->isSuperAdmin()) {
                $isAdminCancelled = true;
            } else {
                $cancelHistory = $order->statusHistories()->where('current_status', 'cancelled')->latest()->first();
                if ($cancelHistory) {
                    $notesLower = strtolower($cancelHistory->notes ?? '');
                    if (str_contains($notesLower, 'admin') || str_contains($notesLower, 'store') || str_contains($notesLower, 'staff') || str_contains($notesLower, 'management')) {
                        $isAdminCancelled = true;
                    } elseif ($cancelHistory->changedBy && $cancelHistory->changedBy->isSuperAdmin()) {
                        $isAdminCancelled = true;
                    }
                }
            }
        @endphp
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #fecaca !important;">
            <div class="card-header py-3 px-4 border-bottom border-danger-subtle d-flex align-items-center justify-content-between flex-wrap gap-2"
                 style="background: linear-gradient(135deg, #fef2f2 0%, #fff1f1 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 40px; height: 40px;">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-danger mb-0.5" style="font-size: 0.96rem;">Order Cancellation Audit & Refund Summary</h6>
                        <div class="text-secondary small" style="font-size: 0.76rem;">
                            Cancelled on <span class="fw-semibold text-dark">{{ $cancellation ? $cancellation->created_at->format('d M, Y \a\t h:i A') : $order->updated_at->format('d M, Y \a\t h:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div>
                    @if($isAdminCancelled)
                        <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="font-size: 0.74rem;">
                            <i class="bi bi-shield-x me-1"></i> Cancelled by Store Management (Admin)
                        </span>
                    @else
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bold shadow-xs" style="font-size: 0.74rem;">
                            <i class="bi bi-person-x me-1"></i> Cancelled by Customer
                        </span>
                    @endif
                </div>
            </div>

            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Cancellation Reason</div>
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                {{ $cancellation->cancellation_reason ?? ($isAdminCancelled ? 'Cancelled by Store Staff' : 'Cancelled by Customer') }}
                            </div>
                            @if($cancellation && $cancellation->admin_notes)
                                <div class="mt-2 text-muted small p-2 rounded bg-white border" style="font-size: 0.75rem;">
                                    <strong>Admin Remarks:</strong> {{ $cancellation->admin_notes }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="text-uppercase text-muted fw-bold mb-1" style="font-size: 0.68rem;">Refund Status & Audit Ledger</div>
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    Refund Amount: <span class="font-monospace text-success">₹{{ number_format($cancellation->refund_amount ?? ($isAdminCancelled ? $order->total_amount : 0), 2) }}</span>
                                </span>
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                    {{ ucfirst($cancellation->refund_status ?? 'Processed') }}
                                </span>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.73rem;">
                                Cancellation Fee Collected: <strong class="text-dark">₹{{ number_format($cancellation->cancellation_fee ?? 0, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="row g-4">
    <!-- ── Left Column: Items & Financials (8 Cols) ── -->
    <div class="col-lg-8">
        <!-- Order Items Table Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.96rem;">
                    <i class="bi bi-box-seam text-primary me-2"></i>Ordered Products ({{ $order->items->count() }})
                </h6>
                <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill small fw-bold">
                    {{ $order->items->sum('quantity') }} Total Units
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.74rem; letter-spacing: 0.04em;">
                        <tr>
                            <th class="ps-4 py-3 text-uppercase fw-bold">Product Name</th>
                            <th class="py-3 text-uppercase fw-bold">SKU</th>
                            <th class="py-3 text-center text-uppercase fw-bold">Qty</th>
                            <th class="py-3 text-end text-uppercase fw-bold">Unit Price</th>
                            <th class="pe-4 py-3 text-end text-uppercase fw-bold">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="ps-4 py-3.5">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($item->product && $item->product->main_image)
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product_name }}" 
                                                 class="rounded-3 border object-fit-cover shadow-xs flex-shrink-0" style="width: 48px; height: 48px;">
                                        @else
                                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted shadow-xs flex-shrink-0" style="width: 48px; height: 48px;">
                                                <i class="bi bi-box-seam fs-5"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark small mb-0.5" style="font-size: 0.88rem;">{{ $item->product_name }}</div>
                                            @if($item->selected_option)
                                                <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace fw-bold px-2 py-0.5 me-1" style="font-size: 0.68rem;">
                                                    <i class="bi bi-tag-fill me-1"></i> Option: {{ $item->selected_option }}
                                                </span>
                                            @endif
                                            @if($item->product)
                                                <a href="{{ route('admin.products.show', $item->product_id) }}" target="_blank" class="text-secondary text-decoration-none" style="font-size: 0.72rem;">
                                                    Specs <i class="bi bi-box-arrow-up-right" style="font-size: 0.65rem;"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="font-monospace small text-secondary">
                                    {{ $item->product?->sku ?? 'SKU-N/A' }}
                                </td>

                                <td class="text-center fw-bold text-dark">
                                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.76rem;">
                                        {{ $item->quantity }}x
                                    </span>
                                </td>

                                <td class="text-end small text-secondary font-monospace">
                                    ₹{{ number_format($item->unit_price ?? $item->price ?? 0, 2) }}
                                </td>

                                <td class="pe-4 text-end fw-bold text-dark font-monospace" style="font-size: 0.92rem;">
                                    ₹{{ number_format($item->total_price ?? (($item->unit_price ?? $item->price ?? 0) * $item->quantity), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary Footer -->
            <div class="card-footer bg-light border-top p-4">
                <div class="row justify-content-end">
                    <div class="col-md-6 col-lg-5">
                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Subtotal Amount:</span>
                            <span class="fw-bold text-dark font-monospace">₹{{ number_format($order->subtotal_amount, 2) }}</span>
                        </div>

                        @if($order->coupon_discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2 small text-success">
                                <span>Coupon Discount ({{ $order->coupon?->code ?? 'PROMO' }}):</span>
                                <span class="fw-bold font-monospace">- ₹{{ number_format($order->coupon_discount_amount, 2) }}</span>
                            </div>
                        @endif

                        @if($order->wallet_amount_used > 0)
                            <div class="d-flex justify-content-between mb-2 small text-primary">
                                <span><i class="bi bi-wallet2 me-1"></i>Wallet Applied:</span>
                                <span class="fw-bold font-monospace">- ₹{{ number_format($order->wallet_amount_used, 2) }}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Shipping Fee:</span>
                            <span class="text-success fw-bold">FREE (Complimentary)</span>
                        </div>

                        <hr class="my-2.5">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">Grand Total:</span>
                            <span class="h4 fw-bolder text-primary mb-0 font-monospace" style="font-size: 1.3rem;">
                                ₹{{ number_format($order->total_amount, 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline / Activity Log -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3.5 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.96rem;">
                    <i class="bi bi-clock-history text-secondary me-2"></i>Status History & Fulfillment Timeline
                </h6>
            </div>
            <div class="card-body p-4">
                <div id="timelineContainer" class="timeline position-relative ps-3">
                    @forelse($order->statusHistories->sortByDesc('created_at') as $history)
                        <div class="position-relative pb-4 ps-4 border-start border-2 border-primary timeline-item">
                            <div class="position-absolute start-0 top-0 translate-middle-x rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" 
                                 style="width: 20px; height: 20px; font-size: 0.65rem; left: -1px;">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">
                                    {{ ucfirst(str_replace('_', ' ', $history->current_status)) }}
                                </span>
                                <span class="text-secondary small" style="font-size: 0.72rem;">
                                    {{ $history->created_at->format('d M Y, h:i A') }} ({{ $history->created_at->diffForHumans() }})
                                </span>
                            </div>
                            <p class="text-dark small mb-0 mt-1" style="font-size: 0.84rem; line-height: 1.4;">
                                {{ $history->notes }}
                            </p>
                            @if($history->user)
                                <span class="text-secondary small mt-0.5 d-inline-block" style="font-size: 0.7rem;">By: {{ $history->user->name }}</span>
                            @endif
                        </div>
                    @empty
                        <div id="emptyTimelineMsg" class="text-center py-4 text-secondary small">No timeline events recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- ── Right Column: Operations & Customer (4 Cols) ── -->
    <div class="col-lg-4">
        @php
            $hasActiveException = $order->fulfillment?->hasActiveDeliveryIssue() && !in_array($order->status, ['delivered', 'cancelled', 'returned']);
        @endphp

        @if($hasActiveException)
            <!-- Active Delivery Exception Warning Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1.5px solid #fecdd3 !important; border-top: 4px solid #e11d48 !important; background: #ffffff;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 28px; height: 28px; font-size: 0.8rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-danger" style="font-size: 0.92rem;">Active Delivery Issue</h6>
                    </div>
                    <span class="badge bg-danger rounded-pill px-2.5 py-1 text-white small fw-bold">Action Needed</span>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 rounded-3 p-3 mb-3">
                        <div class="fw-bold text-danger small">
                            <i class="bi bi-megaphone-fill me-1"></i> Reported Issue:
                        </div>
                        <div class="text-dark fw-bold mt-1" style="font-size: 0.88rem;">
                            {{ $order->fulfillment->delivery_issue }}
                        </div>
                        <div class="text-secondary small mt-1 font-monospace" style="font-size: 0.72rem;">
                            Reported by <strong>{{ $order->fulfillment->rider?->name ?? ($order->rider_name ?? 'Fleet Rider') }}</strong> &bull; {{ $order->fulfillment->delivery_issue_at?->diffForHumans() ?? 'Recently' }}
                        </div>
                    </div>

                    <!-- Customer Contact Action Buttons -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <a href="tel:{{ $order->shipping_phone }}" class="btn btn-sm btn-success rounded-pill px-3 py-2 flex-grow-1 fw-bold text-center shadow-xs" style="font-size: 0.8rem;">
                            <i class="bi bi-telephone-fill me-1"></i> Call ({{ $order->shipping_phone }})
                        </a>
                        @php
                            $cleanP = preg_replace('/[^0-9]/', '', $order->shipping_phone);
                            if (strlen($cleanP) === 10) $cleanP = '91' . $cleanP;
                        @endphp
                        <a href="https://wa.me/{{ $cleanP }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-2 flex-grow-1 fw-bold text-center shadow-xs" style="font-size: 0.8rem;">
                            <i class="bi bi-whatsapp me-1"></i> WhatsApp
                        </a>
                    </div>

                    <!-- Quick Resolution Form -->
                    <form action="{{ route('order-manager.orders.resolve-exception', $order) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Resolve Exception Action</label>
                            <select name="resolution_action" class="form-select small" required>
                                <option value="re-attempt">🚀 1. Re-attempt Delivery Today (Customer Reachable Now)</option>
                                <option value="reschedule">📅 2. Reschedule Delivery Slot (Tomorrow / Evening)</option>
                                <option value="return_warehouse">🔄 3. Return Parcel to Warehouse Inventory</option>
                                <option value="cancel">❌ 4. Customer Refused — Cancel Order</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Resolution Notes</label>
                            <input type="text" name="resolution_notes" class="form-control small" placeholder="e.g. Spoke with customer, opening door now">
                        </div>
                        <button type="submit" class="btn btn-danger w-100 fw-bold rounded-pill py-2 shadow-xs" style="font-size: 0.84rem;">
                            <i class="bi bi-check2-circle me-1"></i> Apply Exception Resolution
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- COD Order Quick Approval Banner --}}
        @if($order->status === 'pending' && $order->payment_method === 'cod')
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #86efac !important; border-top: 4px solid #16a34a !important;">
                <div class="p-3.5 p-md-4">
                    <div class="d-flex align-items-center gap-2.5 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 38px; height: 38px; background: #dcfce7; color: #16a34a;">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bolder text-dark mb-0" style="font-size: 0.94rem;">Pending COD Approval</h6>
                            <span class="text-muted small" style="font-size: 0.74rem;">Customer selected Cash on Delivery (₹{{ number_format($order->total_amount, 2) }})</span>
                        </div>
                    </div>
                    <p class="text-secondary small mb-3" style="font-size: 0.8rem; line-height: 1.4;">
                        Click below to accept and confirm this order so the warehouse team can begin packing and logistics assignment.
                    </p>
                    <form action="{{ route('order-manager.orders.update-status', $order) }}" method="POST">
                        @csrf
                        <input type="hidden" name="status" value="confirmed">
                        <input type="hidden" name="notes" value="Cash on Delivery order approved & confirmed by Order Manager.">
                        <button type="submit" class="btn btn-success w-100 fw-bold rounded-pill py-2.5 shadow-sm d-flex align-items-center justify-content-center gap-2" style="font-size: 0.86rem;">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Accept & Confirm COD Order</span>
                        </button>
                    </form>
                </div>
            </div>
        @elseif($order->status === 'pending' && $order->payment_method === 'online' && $order->payment_status !== 'paid')
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1.5px solid #fed7aa !important; border-top: 4px solid #ea580c !important;">
                <div class="p-3.5 p-md-4">
                    <div class="d-flex align-items-center gap-2.5 mb-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 38px; height: 38px; background: #ffedd5; color: #ea580c;">
                            <i class="bi bi-shield-exclamation fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bolder text-dark mb-0" style="font-size: 0.94rem;">Awaiting Online Payment</h6>
                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.68rem;">Payment Pending</span>
                        </div>
                    </div>
                    <p class="text-danger small fw-semibold mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                        ⚠️ Do NOT pack or dispatch this order yet. The customer has not completed their online payment.
                    </p>
                </div>
            </div>
        @endif

        <!-- 1. Order Status Advancement Action Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border-top: 4px solid #4f46e5 !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">
                    <i class="bi bi-arrow-repeat text-primary me-2"></i>Advance Fulfillment Status
                </h6>
            </div>
            <div class="card-body p-4">
                <form id="formUpdateStatus" action="{{ route('order-manager.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Advance Status to:</label>
                        <select id="orderStatusSelect" name="status" class="form-select fw-bold small">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ 1. Pending</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>✅ 2. Confirmed</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>📦 3. Processing (Warehouse)</option>
                            <option value="packed" {{ $order->status === 'packed' ? 'selected' : '' }}>🏷️ 4. Packed & Ready</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 5. Shipped (In-Transit)</option>
                            <option value="out for delivery" {{ $order->status === 'out for delivery' ? 'selected' : '' }}>📍 6. Out for Delivery</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>🎉 7. Delivered</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Internal Fulfillment Notes</label>
                        <textarea id="statusNotesInput" name="notes" rows="2" class="form-control small" placeholder="e.g. Package picked from warehouse rack and labeled."></textarea>
                    </div>

                    <button type="submit" id="btnSubmitStatus" class="btn text-white w-100 fw-bold rounded-pill shadow-xs py-2" style="background: #4f46e5; border: none; font-size: 0.84rem;">
                        <i class="bi bi-check2-circle me-1"></i> Update Status (Instant)
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. Logistics & Dispatch Card (Context-Aware) -->
        @if($order->isLocalBengaluruDelivery())
            <!-- Bengaluru Local Fleet Assignment Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1.5px solid #bae6fd !important; border-top: 4px solid #0284c7 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.94rem;">
                        <i class="bi bi-geo-fill text-info me-1.5"></i>Bengaluru Fleet Dispatch
                    </h6>
                    <span class="badge rounded-pill px-2.5 py-1 small fw-bold text-white shadow-xs" style="background: #0284c7; font-size: 0.68rem;">
                        Local Fleet
                    </span>
                </div>
                <div class="card-body p-4">
                    <form id="formAssignRider" action="{{ route('order-manager.orders.assign-rider', $order) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Select Delivery Partner</label>
                            <select id="selectRegisteredRider" name="rider_id" class="form-select small">
                                <option value="">-- Choose In-House Delivery Partner --</option>
                                @foreach($deliveryPartners as $dp)
                                    <option value="{{ $dp->id }}" 
                                            data-name="{{ $dp->name }}" 
                                            data-phone="{{ $dp->mobile_number }}"
                                            {{ $order->rider_id === $dp->id ? 'selected' : '' }}>
                                        🛵 {{ $dp->name }} ({{ $dp->mobile_number ?? 'No Phone' }}) &bull; {{ $dp->assigned_deliveries_count }} active
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Rider Name <span class="text-danger">*</span></label>
                            <input type="text" id="inputRiderName" name="rider_name" class="form-control small" 
                                   value="{{ old('rider_name', $order->rider_name) }}" required placeholder="e.g. Karthik">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Rider Phone <span class="text-danger">*</span></label>
                            <input type="text" id="inputRiderPhone" name="rider_phone" class="form-control small" 
                                   value="{{ old('rider_phone', $order->rider_phone) }}" required placeholder="e.g. +91 9123456789">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Delivery Window</label>
                            <select id="selectDeliverySlot" name="delivery_slot" class="form-select small">
                                <option value="Next-Day Express Delivery (1-2 Days)" {{ ($order->delivery_slot === 'Next-Day Express Delivery (1-2 Days)' || !$order->delivery_slot) ? 'selected' : '' }}>⚡ Next-Day Express Delivery (1-2 Days)</option>
                                <option value="Morning Slot (10:00 AM - 02:00 PM)" {{ $order->delivery_slot === 'Morning Slot (10:00 AM - 02:00 PM)' ? 'selected' : '' }}>🌅 Morning Slot (10:00 AM - 02:00 PM)</option>
                                <option value="Evening Slot (04:00 PM - 08:00 PM)" {{ $order->delivery_slot === 'Evening Slot (04:00 PM - 08:00 PM)' ? 'selected' : '' }}>🌆 Evening Slot (04:00 PM - 08:00 PM)</option>
                                <option value="Standard Local Delivery (2-3 Days)" {{ $order->delivery_slot === 'Standard Local Delivery (2-3 Days)' ? 'selected' : '' }}>📦 Standard Local Delivery (2-3 Days)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Rider Delivery Notes (Optional)</label>
                            <input type="text" id="inputRiderNotes" name="notes" class="form-control small" 
                                   value="{{ old('notes', $order->fulfillment?->notes) }}" placeholder="e.g. Gate security call before delivery">
                        </div>

                        <button type="submit" id="btnSubmitRider" class="btn text-white w-100 fw-bold rounded-pill shadow-xs py-2" style="background: #0284c7; border: none; font-size: 0.84rem;">
                            <i class="bi bi-send-fill me-1.5"></i> Assign Rider & Dispatch (Out for Delivery)
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- National 3rd-Party Courier Logistics Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1.5px solid #ddd6fe !important; border-top: 4px solid #4f46e5 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.94rem;">
                        <i class="bi bi-truck me-1.5 text-primary"></i>National Courier Logistics
                    </h6>
                    <span class="badge rounded-pill px-2.5 py-1 small fw-bold text-white shadow-xs" style="background: #4f46e5; font-size: 0.68rem;">
                        Pan-India AWB
                    </span>
                </div>
                <div class="card-body p-4">
                    <form id="formUpdateTracking" action="{{ route('order-manager.orders.update-tracking', $order) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Courier Partner <span class="text-danger">*</span></label>
                            <select id="selectCourierPartner" name="courier_partner" class="form-select small" required>
                                <option value="Delhivery" {{ $order->courier_partner === 'Delhivery' ? 'selected' : '' }}>Delhivery Express</option>
                                <option value="BlueDart" {{ $order->courier_partner === 'BlueDart' ? 'selected' : '' }}>BlueDart Aviation</option>
                                <option value="DTDC" {{ $order->courier_partner === 'DTDC' ? 'selected' : '' }}>DTDC Courier</option>
                                <option value="Ecom Express" {{ $order->courier_partner === 'Ecom Express' ? 'selected' : '' }}>Ecom Express</option>
                                <option value="FedEx" {{ $order->courier_partner === 'FedEx' ? 'selected' : '' }}>FedEx India</option>
                                <option value="India Post" {{ $order->courier_partner === 'India Post' ? 'selected' : '' }}>India Post SpeedPost</option>
                                <option value="Shadowfax" {{ $order->courier_partner === 'Shadowfax' ? 'selected' : '' }}>Shadowfax</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">AWB / Tracking Number <span class="text-danger">*</span></label>
                            <input type="text" id="inputTrackingNumber" name="tracking_number" class="form-control font-monospace small" 
                                   value="{{ old('tracking_number', $order->tracking_number) }}" required placeholder="e.g. BD983471092">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">Tracking URL (Optional)</label>
                            <input type="url" id="inputTrackingUrl" name="tracking_url" class="form-control small" 
                                   value="{{ old('tracking_url', $order->tracking_url) }}" placeholder="https://www.bluedart.com/track/...">
                        </div>

                        <button type="submit" id="btnSubmitTracking" class="btn text-white w-100 fw-bold rounded-pill shadow-xs py-2" style="background: #4f46e5; border: none; font-size: 0.84rem;">
                            <i class="bi bi-box-seam-fill me-1.5"></i> Save Tracking & Dispatch (Shipped)
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- 3. Customer & Shipping Address Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.94rem;">
                    <i class="bi bi-person-fill text-primary me-2"></i>Customer & Shipping Details
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Customer Name</div>
                    <div class="fw-bold text-dark" style="font-size: 0.96rem;">{{ $order->shipping_name }}</div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Contact Details</div>
                    <div class="small text-dark mt-0.5"><i class="bi bi-envelope me-1 text-secondary"></i>{{ $order->shipping_email }}</div>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="small text-dark"><i class="bi bi-telephone me-1 text-secondary"></i>{{ $order->shipping_phone }}</span>
                        <a href="tel:{{ $order->shipping_phone }}" class="badge bg-light text-success border text-decoration-none py-1">Call</a>
                        @php
                            $cleanPhone = preg_replace('/[^0-9]/', '', $order->shipping_phone);
                            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                        @endphp
                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="badge bg-success bg-opacity-10 text-success border text-decoration-none py-1">WhatsApp</a>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Shipping Destination</div>
                    <div class="p-2.5 rounded-3 bg-light border mt-1 small text-dark" style="line-height: 1.45;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $order->shipping_address }}<br>
                        <strong>{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_zip }}</strong><br>
                        <span class="text-secondary">{{ $order->shipping_country ?? 'India' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-top">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <span class="text-secondary small">Payment Method:</span>
                        <span class="badge bg-light text-dark border fw-bold text-uppercase" style="font-size: 0.72rem;">
                            {{ $order->payment_method }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary small">Payment Status:</span>
                        <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.72rem;">
                            {{ ucfirst($order->payment_status ?? 'pending') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Payment Gateway & Bank Audit Card -->
        @php
            $payment = $order->primaryPayment ?? $order->latestPayment;
        @endphp
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="border: 1.5px solid #e2e8f0 !important; border-top: 4px solid {{ $order->payment_method === 'online' ? '#10b981' : '#64748b' }} !important;">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.94rem;">
                    <i class="bi bi-credit-card-2-front-fill text-success me-1.5"></i>Payment & Gateway Audit
                </h6>
                {!! $payment?->status_badge ?? ($order->payment_status === 'paid' ? '<span class="badge bg-success rounded-pill px-2.5 py-1 text-white">Paid</span>' : '<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1">Pending</span>') !!}
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Payment Channel</div>
                    <div class="fw-bold text-dark mt-0.5" style="font-size: 0.92rem;">
                        @if($order->payment_method === 'online')
                            ⚡ Razorpay PG ({{ $payment?->method_display ?? 'Online UPI / Card' }})
                        @else
                            💵 Cash on Delivery (COD)
                        @endif
                    </div>
                </div>

                @if($payment && $payment->bank_reference)
                    <div class="mb-3 p-2.5 rounded-3 bg-light border">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Bank Reference (UTR / RRN)</div>
                        <div class="fw-bold font-monospace text-primary" style="font-size: 0.94rem;">
                            {{ $payment->bank_reference }}
                        </div>
                    </div>
                @endif

                @if($payment && $payment->gateway_payment_id)
                    <div class="mb-3">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Gateway Transaction ID</div>
                        <div class="text-dark font-monospace small">
                            #{{ $payment->gateway_payment_id }}
                        </div>
                    </div>
                @endif

                @if($payment && $payment->gateway_order_id)
                    <div class="mb-3">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Razorpay Order Ref</div>
                        <div class="text-secondary font-monospace small">
                            {{ $payment->gateway_order_id }}
                        </div>
                    </div>
                @endif

                @if($payment && $payment->payment_time)
                    <div class="mb-0">
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Payment Timestamp</div>
                        <div class="text-dark small mt-0.5">
                            <i class="bi bi-clock-history me-1 text-muted"></i>{{ $payment->payment_time->format('d M Y, h:i A') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stages = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out for delivery', 'delivered'];

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function updateProgressTracker(newStatus) {
        const topBadge = document.getElementById('topStatusBadge');
        if (topBadge) {
            topBadge.textContent = 'Status: ' + newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ');
        }

        const idx = stages.indexOf(newStatus);
        const progressBar = document.getElementById('trackerProgressBar');
        if (progressBar && idx !== -1) {
            const pct = (idx / (stages.length - 1)) * 100;
            progressBar.style.width = pct + '%';
        }

        document.querySelectorAll('.stage-step').forEach((step, sIdx) => {
            const dot = step.querySelector('.stage-dot');
            const label = step.querySelector('.stage-label');
            const isPast = (idx !== -1 && sIdx <= idx);
            const isCurrent = (idx !== -1 && sIdx === idx);

            if (dot) {
                dot.style.background = isPast ? '#10b981' : '#ffffff';
                dot.style.color = isPast ? '#ffffff' : '#64748b';
                dot.style.border = isCurrent ? '2px solid #4f46e5' : (isPast ? '2px solid #10b981' : '2px solid #cbd5e1');
                dot.innerHTML = isPast ? '<i class="bi bi-check-lg"></i>' : (sIdx + 1);
            }
            if (label) {
                label.className = 'stage-label small fw-semibold mt-1.5 d-none d-md-block ' + (isCurrent ? 'text-primary fw-bold' : (isPast ? 'text-dark' : 'text-muted'));
            }
        });

        const statusSelect = document.getElementById('orderStatusSelect');
        if (statusSelect) {
            statusSelect.value = newStatus;
        }
    }

    function prependTimeline(status, notes) {
        const container = document.getElementById('timelineContainer');
        const emptyMsg = document.getElementById('emptyTimelineMsg');
        if (emptyMsg) emptyMsg.remove();

        if (container) {
            const item = document.createElement('div');
            item.className = 'position-relative pb-4 ps-4 border-start border-2 border-primary timeline-item';
            item.innerHTML = `
                <div class="position-absolute start-0 top-0 translate-middle-x rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-xs" 
                     style="width: 20px; height: 20px; font-size: 0.65rem; left: -1px;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">
                        ${status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ')}
                    </span>
                    <span class="text-secondary small" style="font-size: 0.72rem;">Just now</span>
                </div>
                <p class="text-dark small mb-0 mt-1" style="font-size: 0.84rem; line-height: 1.4;">
                    ${notes || 'Status updated via Order Operations'}
                </p>
                <span class="text-secondary small mt-0.5 d-inline-block" style="font-size: 0.7rem;">By: Staff Manager</span>
            `;
            container.insertBefore(item, container.firstChild);
        }
    }

    // 1. AJAX Status Update Form
    const formStatus = document.getElementById('formUpdateStatus');
    if (formStatus) {
        formStatus.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitStatus');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating...';

            const formData = new FormData(formStatus);

            try {
                const res = await fetch(formStatus.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Order status updated successfully!'
                    });
                    updateProgressTracker(data.status);
                    prependTimeline(data.status, formData.get('notes'));
                    document.getElementById('statusNotesInput').value = '';
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Could not update status. Please try again.'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({
                    icon: 'error',
                    title: 'Network error. Please try again.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }
        });
    }

    // Auto-populate Rider details when selecting a registered delivery partner
    const selectRegisteredRider = document.getElementById('selectRegisteredRider');
    if (selectRegisteredRider) {
        selectRegisteredRider.addEventListener('change', function () {
            const selectedOpt = selectRegisteredRider.options[selectRegisteredRider.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const name = selectedOpt.getAttribute('data-name');
                const phone = selectedOpt.getAttribute('data-phone');
                if (name) document.getElementById('inputRiderName').value = name;
                if (phone) document.getElementById('inputRiderPhone').value = phone;
            }
        });
    }

    // 2. AJAX Rider Assignment Form
    const formRider = document.getElementById('formAssignRider');
    if (formRider) {
        formRider.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitRider');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Assigning Rider...';

            const formData = new FormData(formRider);

            try {
                const res = await fetch(formRider.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Rider assigned & dispatched successfully!'
                    });
                    updateProgressTracker(data.status || 'out for delivery');
                    const riderInfo = document.getElementById('topLogisticsInfo');
                    if (riderInfo) {
                        riderInfo.innerHTML = `Bengaluru Local Fleet: Rider <strong>${data.rider_name}</strong> (${data.rider_phone})`;
                    }
                    prependTimeline('out for delivery', `Assigned rider: ${data.rider_name} (${data.rider_phone}) [${data.delivery_slot}]`);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Could not assign rider. Please try again.'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({
                    icon: 'error',
                    title: 'Network error. Please try again.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }
        });
    }

    // 3. AJAX Courier Tracking Form
    const formTracking = document.getElementById('formUpdateTracking');
    if (formTracking) {
        formTracking.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnSubmitTracking');
            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving Tracking...';

            const formData = new FormData(formTracking);

            try {
                const res = await fetch(formTracking.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Tracking saved and order marked as Shipped!'
                    });
                    updateProgressTracker(data.status || 'shipped');
                    const info = document.getElementById('topLogisticsInfo');
                    if (info) {
                        info.innerHTML = `National Logistics: <strong>${data.courier_partner}</strong> &bull; AWB: <strong class="font-monospace">${data.tracking_number}</strong>`;
                    }
                    prependTimeline('shipped', `Dispatched via ${data.courier_partner}. AWB: ${data.tracking_number}`);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Could not save tracking details.'
                    });
                }
            } catch (err) {
                console.error(err);
                Toast.fire({
                    icon: 'error',
                    title: 'Network error. Please try again.'
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }
        });
    }
});
</script>
@endpush

{{-- ── Staff / Admin Cancel Order Modal ── --}}
@if(!in_array($order->status, ['cancelled', 'delivered', 'returned']))
<div class="modal fade" id="staffCancelOrderModal" tabindex="-1" aria-hidden="true">
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
                        <label class="form-label fw-bold text-dark small">Admin Cancellation Reason Topic <span class="text-danger">*</span></label>
                        <select name="cancellation_reason" class="form-select fw-semibold" required>
                            <option value="" selected disabled>Select Cancellation Topic...</option>
                            <option value="Item Out of Stock / Inventory Unavailable">📦 Item Out of Stock / Inventory Unavailable</option>
                            <option value="Product Damaged in Warehouse / QC Failure">⚠️ Product Damaged in Warehouse / Quality Control Failure</option>
                            <option value="Delivery Location Unserviceable by Logistics Partner">🚚 Delivery Location Unserviceable by Logistics Partner</option>
                            <option value="Customer Requested Cancellation via Phone / Support Chat">📞 Customer Requested Cancellation via Phone / Support Chat</option>
                            <option value="Incorrect Pricing / Technical System Issue">🏷️ Incorrect Pricing / Technical System Issue</option>
                            <option value="Suspected Fraudulent Transaction / Duplicate Order">🚫 Suspected Fraudulent Transaction / Duplicate Order</option>
                            <option value="Other Store Admin Reason">📝 Other Store Admin Reason</option>
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
                        <label class="form-label fw-bold text-dark small">Admin Internal Remarks / Customer Note (Optional)</label>
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
