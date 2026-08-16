@extends('admin.layouts.app')

@section('header', 'View Customer Inquiry')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.enquiries.index') }}">Enquiries</a></li>
    <li class="breadcrumb-item active" aria-current="page">Message #{{ $enquiry->id }}</li>
@endsection

@section('actions')
    <a href="{{ route('admin.enquiries.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Enquiries
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3.5 border-bottom d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-envelope-open-fill text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Customer Support Message</h6>
                </div>
                <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                    <i class="bi bi-clock me-1"></i>{{ $enquiry->created_at->format('d M Y, h:i A') }}
                </span>
            </div>

            <div class="card-body p-4">
                <!-- Sender Contact Card -->
                <div class="p-3.5 bg-light rounded-4 border mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                             style="width: 48px; height: 48px; background: #0d6efd; font-size: 1rem;">
                            {{ strtoupper(substr($enquiry->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark">{{ $enquiry->name }}</div>
                            <div class="d-flex flex-wrap gap-3 small mt-1">
                                <a href="mailto:{{ $enquiry->email }}" class="text-decoration-none text-primary fw-semibold">
                                    <i class="bi bi-envelope me-1"></i>{{ $enquiry->email }}
                                </a>
                                @if($enquiry->mobile)
                                    <a href="tel:{{ $enquiry->mobile }}" class="text-decoration-none text-secondary">
                                        <i class="bi bi-telephone me-1"></i>{{ $enquiry->mobile }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject -->
                <div class="mb-4">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Inquiry Subject</div>
                    <div class="fs-6 fw-bold text-dark p-2.5 bg-white border rounded-3">{{ $enquiry->subject }}</div>
                </div>

                <!-- Message Body -->
                <div class="mb-0">
                    <div class="text-muted small fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">Message Details</div>
                    <div class="p-4 bg-light rounded-3 text-dark border" style="white-space: pre-wrap; font-size: 1rem; line-height: 1.7;">{{ $enquiry->message }}</div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="card-footer bg-white py-3.5 border-top d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.enquiries.index') }}" class="btn btn-light rounded-pill px-4">
                    Back to Inbox
                </a>
                <a href="mailto:{{ $enquiry->email }}?subject=Re: {{ urlencode($enquiry->subject) }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-reply-fill me-1"></i> Reply via Email
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
