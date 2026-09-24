@extends('support.layouts.app')

@section('title', $customer->name . ' — 360° Profile & Access Control')
@section('header', 'Customer 360° Profile & Access Control')

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('support.customers.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Customers
        </a>
    </div>
@endsection

@section('content')

<!-- Customer Header Card with Status & Action Modal -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white" style="{{ $customer->status === 'Blocked' ? 'border-top: 4px solid #ef4444 !important;' : 'border-top: 4px solid #8b5cf6 !important;' }}">
    <div class="card-body p-4">
        <div class="row align-items-center justify-content-between g-3">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" 
                         style="width: 58px; height: 58px; font-size: 1.5rem; background: {{ $customer->status === 'Blocked' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'linear-gradient(135deg, #8b5cf6, #6d28d9)' }}; box-shadow: 0 4px 14px {{ $customer->status === 'Blocked' ? 'rgba(239, 68, 68, 0.4)' : 'rgba(139, 92, 246, 0.4)' }};">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold text-dark mb-0">{{ $customer->name }}</h4>
                            @if($customer->status === 'Blocked')
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" style="background: #fee2e2; color: #991b1b; border: 1.5px solid #fca5a5; font-size: 0.75rem;">
                                    <i class="bi bi-slash-circle-fill text-danger"></i> ACCOUNT BLOCKED
                                </span>
                            @else
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" style="background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; font-size: 0.75rem;">
                                    <i class="bi bi-check-circle-fill text-success"></i> ACTIVE ACCOUNT
                                </span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-2 text-muted small flex-wrap mt-1">
                            <span><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</span>
                            <span>&bull;</span>
                            <span><i class="bi bi-telephone me-1 text-success"></i>{{ $customer->mobile_number ?? 'No mobile recorded' }}</span>
                            <span>&bull;</span>
                            <span>Customer ID: #{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5 text-md-end d-flex align-items-center justify-content-md-end gap-3 flex-wrap">
                <!-- Status Toggle Button -->
                @if($customer->status === 'Blocked')
                    <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-xs d-flex align-items-center gap-1.5" 
                            data-bs-toggle="modal" data-bs-target="#modalToggleStatus" style="font-size: 0.82rem;">
                        <i class="bi bi-unlock-fill"></i> Unblock Customer Account
                    </button>
                @else
                    <button type="button" class="btn btn-outline-danger rounded-pill px-3.5 py-2 fw-bold shadow-xs d-flex align-items-center gap-1.5" 
                            data-bs-toggle="modal" data-bs-target="#modalToggleStatus" style="font-size: 0.82rem;">
                        <i class="bi bi-slash-circle"></i> Block Account
                    </button>
                @endif

                <div class="d-inline-flex gap-3 bg-light p-2.5 rounded-4 border">
                    <div class="text-center px-1">
                        <div class="text-muted small" style="font-size: 0.65rem; text-transform: uppercase;">Total Orders</div>
                        <div class="h5 fw-bolder text-dark mb-0 mt-0.5">{{ $customer->orders->count() }}</div>
                    </div>
                    <div class="vr"></div>
                    <div class="text-center px-1">
                        <div class="text-muted small" style="font-size: 0.65rem; text-transform: uppercase;">Lifetime Spend</div>
                        <div class="h5 fw-bolder text-primary mb-0 mt-0.5" style="color: #7c3aed !important;">₹{{ number_format($lifetimeSpend, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Blocking / Unblocking Customer -->
<div class="modal fade text-start" id="modalToggleStatus" tabindex="-1" aria-labelledby="modalToggleStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header {{ $customer->status === 'Blocked' ? 'bg-success text-white' : 'bg-danger text-white' }} py-3 px-4">
                <h6 class="modal-title fw-bold" id="modalToggleStatusLabel">
                    <i class="bi {{ $customer->status === 'Blocked' ? 'bi-unlock-fill' : 'bi-shield-slash-fill' }} me-1.5"></i>
                    {{ $customer->status === 'Blocked' ? 'Confirm Unblock Customer Account' : 'Confirm Block Customer Account' }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('support.customers.toggle-status', $customer) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    @if($customer->status === 'Blocked')
                        <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25 mb-3">
                            <div class="fw-bold text-success small mb-1">
                                <i class="bi bi-info-circle-fill me-1"></i> You are about to UNBLOCK {{ $customer->name }}.
                            </div>
                            <div class="text-secondary small" style="font-size: 0.78rem;">
                                This will instantly restore the customer's ability to log in, verify OTPs, and place new orders on ShopCalm.
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25 mb-3">
                            <div class="fw-bold text-danger small mb-1">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> You are about to BLOCK {{ $customer->name }}.
                            </div>
                            <div class="text-secondary small" style="font-size: 0.78rem;">
                                The customer will be immediately blocked from logging in or placing new orders.
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Support Staff Action Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" rows="3" class="form-control form-control-sm" required 
                                  placeholder="e.g. {{ $customer->status === 'Blocked' ? 'Customer verified identity over call. Unblocking as requested.' : 'Suspicious duplicate orders / fraud risk.' }}"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light border bg-white fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn {{ $customer->status === 'Blocked' ? 'btn-success' : 'btn-danger' }} fw-bold px-4">
                        {{ $customer->status === 'Blocked' ? 'Yes, Unblock Account' : 'Yes, Block Account' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Complete Order History (8 Cols) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-box2 text-primary me-2"></i>Order Purchase History ({{ $customer->orders->count() }})
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                        <tr>
                            <th class="ps-4 py-3">Order #</th>
                            <th class="py-3">Items</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($customer->orders as $ord)
                            @php
                                $statusBadge = match($ord->status) {
                                    'pending'          => ['bg' => '#fef3c7', 'color' => '#92400e', 'label' => 'Pending'],
                                    'confirmed'        => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'label' => 'Confirmed'],
                                    'processing'       => ['bg' => '#dbeafe', 'color' => '#1e40af', 'label' => 'Processing'],
                                    'packed'           => ['bg' => '#f3e8ff', 'color' => '#6b21a8', 'label' => 'Packed'],
                                    'shipped'          => ['bg' => '#cffafe', 'color' => '#155e75', 'label' => 'Shipped'],
                                    'out for delivery' => ['bg' => '#ffedd5', 'color' => '#9a3412', 'label' => 'Out for Delivery'],
                                    'delivered'        => ['bg' => '#d1fae5', 'color' => '#065f46', 'label' => 'Delivered'],
                                    default            => ['bg' => '#fee2e2', 'color' => '#991b1b', 'label' => ucfirst($ord->status)],
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark font-monospace small">
                                        #{{ $ord->order_number }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill small">
                                        {{ $ord->items->sum('quantity') }} items
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">₹{{ number_format($ord->total_amount, 2) }}</div>
                                    <span class="text-muted" style="font-size: 0.65rem; text-transform: uppercase;">{{ $ord->payment_method }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.7rem;">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>
                                <td class="text-muted small" style="font-size: 0.72rem;">
                                    {{ $ord->created_at->format('d M Y, h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted small">
                                    No purchase orders recorded for this customer yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inquiries / Support Tickets History -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3.5 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-chat-left-text text-primary me-2"></i>Customer Care Inquiries & Tickets ({{ $enquiries->count() }})
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                        <tr>
                            <th class="ps-4 py-3">Subject & Message</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Submitted At</th>
                            <th class="pe-4 py-3 text-end">Ticket Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($enquiries as $enq)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark small">{{ $enq->subject }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 340px; font-size: 0.72rem;">
                                        {{ $enq->message }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill small fw-bold">
                                        {{ ucfirst($enq->status ?? 'new') }}
                                    </span>
                                </td>
                                <td class="text-muted small" style="font-size: 0.72rem;">
                                    {{ $enq->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('support.enquiries.show', $enq) }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 small fw-bold" style="font-size: 0.72rem;">
                                        View Ticket
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">
                                    No contact inquiries logged by this customer.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Delivery Addresses (4 Cols) -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3.5 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-geo-alt text-primary me-2"></i>Saved Delivery Addresses
                </h6>
            </div>
            <div class="card-body p-4">
                @forelse($customer->addresses as $addr)
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-secondary rounded-pill px-2 py-0.5 small text-uppercase" style="font-size: 0.65rem;">
                                {{ $addr->type ?? 'Home' }}
                            </span>
                            @if($addr->is_default)
                                <span class="badge bg-success rounded-pill px-2 py-0.5 small" style="font-size: 0.65rem;">Default</span>
                            @endif
                        </div>
                        <div class="fw-bold text-dark small">{{ $addr->name ?? $customer->name }}</div>
                        <div class="text-muted small mt-1" style="line-height: 1.4; font-size: 0.75rem;">
                            {{ $addr->address_line_1 }}<br>
                            @if($addr->address_line_2) {{ $addr->address_line_2 }}<br> @endif
                            {{ $addr->city }}, {{ $addr->state }} - <strong>{{ $addr->postal_code }}</strong><br>
                            <i class="bi bi-telephone me-1 text-success"></i>{{ $addr->phone ?? $customer->mobile_number }}
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        <i class="bi bi-geo-alt text-muted fs-3 d-block mb-1"></i>
                        No saved delivery addresses found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
