@extends('support.layouts.app')

@section('title', 'Inquiry #' . $enquiry->id)
@section('header', 'Customer Inquiry #' . $enquiry->id)

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('support.enquiries.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Inquiries
        </a>
    </div>
@endsection

@section('content')

@php
    $statusBadge = match($enquiry->status) {
        'unread'      => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Unread / Open'],
        'in_progress' => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'In Progress'],
        'resolved'    => ['bg' => '#d1fae5', 'color' => '#059669', 'label' => 'Resolved'],
        default       => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => ucfirst($enquiry->status)],
    };
@endphp

<div class="row g-4">
    <!-- Left Column: Inquiry Content & Response (8 Cols) -->
    <div class="col-lg-8">
        <!-- Message Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-purple bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background: #f3e8ff; color: #7c3aed;">
                        {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $enquiry->name }}</h6>
                        <div class="text-muted small" style="font-size: 0.72rem;">{{ $enquiry->email }} &bull; {{ $enquiry->created_at->format('d M Y, h:i A') }} ({{ $enquiry->created_at->diffForHumans() }})</div>
                    </div>
                </div>
                <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.75rem;">
                    {{ $statusBadge['label'] }}
                </span>
            </div>

            <div class="card-body p-4">
                <div class="mb-3">
                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem;">Inquiry Subject</div>
                    <h5 class="fw-bold text-dark mb-0 mt-1">{{ $enquiry->subject }}</h5>
                </div>

                <div class="p-3.5 bg-light rounded-3 border mb-4">
                    <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">Customer Message</div>
                    <p class="text-dark small mb-0" style="line-height: 1.7; white-space: pre-wrap;">{{ $enquiry->message }}</p>
                </div>

                <!-- Direct Contact Actions -->
                <div class="d-flex gap-2 flex-wrap">
                    <a href="mailto:{{ $enquiry->email }}?subject=Re: {{ urlencode($enquiry->subject) }} [ShopCalm Support]" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-reply-fill me-1"></i> Send Official Email Reply
                    </a>
                    @if($enquiry->mobile)
                        <a href="tel:{{ $enquiry->mobile }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-telephone-fill me-1"></i> Call {{ $enquiry->mobile }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Resolution & Agent Notes Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white" style="border-left: 4px solid #8b5cf6 !important;">
            <div class="card-header bg-white py-3.5 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-shield-check text-primary me-2"></i>Update Inquiry Status & Resolution Notes
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('support.enquiries.update', $enquiry) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Resolution Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select fw-bold small" required>
                            <option value="unread" {{ $enquiry->status === 'unread' ? 'selected' : '' }}>🔴 1. Unread / Open</option>
                            <option value="in_progress" {{ $enquiry->status === 'in_progress' ? 'selected' : '' }}>🟡 2. In Progress (Agent Assigned)</option>
                            <option value="resolved" {{ $enquiry->status === 'resolved' ? 'selected' : '' }}>🟢 3. Resolved & Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Internal Agent Resolution Notes</label>
                        <textarea name="reply_notes" rows="4" class="form-control small" placeholder="e.g. Contacted customer via email regarding courier delay. Issue resolved.">{{ old('reply_notes', $enquiry->reply_notes) }}</textarea>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Internal record for other support agents.</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" style="background: #8b5cf6; border-color: #8b5cf6;">
                            <i class="bi bi-check2-circle me-1"></i> Save Resolution
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Customer 360 Context (4 Cols) -->
    <div class="col-lg-4">
        <!-- Customer Account Context -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-badge text-primary me-2"></i>Customer 360° Profile
                </h6>
            </div>
            <div class="card-body p-4">
                @if($customer)
                    <div class="text-center pb-3 border-bottom mb-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2" style="width: 48px; height: 48px; background: #8b5cf6; font-size: 1.25rem;">
                            {{ strtoupper(substr($customer->name, 0, 1)) }}
                        </div>
                        <h6 class="fw-bold text-dark mb-0">{{ $customer->name }}</h6>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-0.5 small mt-1">
                            Registered Customer
                        </span>
                    </div>

                    <div class="mb-2.5 d-flex justify-content-between small">
                        <span class="text-muted">Account Created:</span>
                        <span class="fw-bold text-dark">{{ $customer->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="mb-2.5 d-flex justify-content-between small">
                        <span class="text-muted">Total Orders:</span>
                        <span class="fw-bold text-dark">{{ $customer->orders->count() }} Orders</span>
                    </div>
                    <div class="mb-3 d-flex justify-content-between small">
                        <span class="text-muted">Lifetime Spend:</span>
                        <span class="fw-bold text-primary">₹{{ number_format($customer->orders->where('status', '!=', 'cancelled')->sum('total_amount'), 2) }}</span>
                    </div>

                    <a href="{{ route('support.customers.show', $customer) }}" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold">
                        <i class="bi bi-arrow-up-right me-1"></i> View Full Customer 360°
                    </a>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">Guest Inquiry</h6>
                        <p class="small text-muted mb-0">Sender email ({{ $enquiry->email }}) is not linked to a registered customer account.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Meta Audit -->
        @if($enquiry->resolved_at)
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3">
                <div class="small fw-bold text-dark mb-1">Resolution Audit</div>
                <div class="text-muted small" style="font-size: 0.72rem;">
                    Resolved on: {{ $enquiry->resolved_at->format('d M Y, h:i A') }}<br>
                    By: <strong>{{ $enquiry->resolver?->name ?? 'Support Agent' }}</strong>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
