@extends('admin.layouts.app')

@section('header', 'Integrations & API Services Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Integrations Hub</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-cpu me-2"></i>Integrations & Third-Party API Services Control Center</h4>
                    <p class="mb-0 text-white-50 small">Manage live transactional email delivery, WhatsApp Cloud notifications, and Razorpay online payment gateways.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-dark shadow-sm">
                        <i class="bi bi-gear-fill me-1"></i> All Store Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 justify-content-center">
    <!-- 1. Brevo Email Service Hub Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-shadow transition-all">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; width: 40px; height: 40px;">
                        <i class="bi bi-envelope-paper-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Brevo Email REST API</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">Transactional Invoices & Receipts</span>
                    </div>
                </div>
                <span class="badge {{ ($brevoQuota['connected'] ?? false) ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.71rem;">
                    {{ ($brevoQuota['connected'] ?? false) ? 'Connected ✓' : 'Setup Needed' }}
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-light rounded-3 border mb-3 text-center">
                        <div class="text-muted small fw-bold mb-1">Live Daily Quota Balance</div>
                        <div class="fs-4 fw-black text-dark">{{ $brevoQuota['credits_label'] ?? '0' }}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Plan: {{ $brevoQuota['plan_type'] ?? 'Free' }}</div>
                    </div>
                    <ul class="list-unstyled small text-muted mb-4 space-y-2">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Auto GST Tax Invoice PDF emails
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Automatic SMTP Mailer Fallback
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Sender: {{ $brevoQuota['sender_email'] ?? 'support@shopcalm.in' }}
                        </li>
                    </ul>
                </div>
                <a href="{{ route('admin.integrations.brevo') }}" class="btn btn-primary rounded-pill w-100 fw-bold">
                    Open Brevo Email Hub <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. WhatsApp Cloud API Hub Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-shadow transition-all">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(34, 197, 94, 0.1); color: #16a34a; width: 40px; height: 40px;">
                        <i class="bi bi-whatsapp fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Meta WhatsApp API</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">Automated Customer Alerts</span>
                    </div>
                </div>
                <span class="badge {{ $whatsAppStatus === 'ACTIVE' ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.71rem;">
                    {{ $whatsAppStatus === 'ACTIVE' ? 'Connected ✓' : 'Config Needed' }}
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-light rounded-3 border mb-3 text-center">
                        <div class="text-muted small fw-bold mb-1">Unified Meta Gateway Token</div>
                        <div class="fs-5 fw-bold text-dark font-monospace text-truncate">
                            {{ $hasWhatsAppPhoneId ? 'ID Configured ✓' : 'Phone ID Missing' }}
                        </div>
                        <div class="text-muted" style="font-size: 0.7rem;">7 Event Templates Registered</div>
                    </div>
                    <ul class="list-unstyled small text-muted mb-4 space-y-2">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Bengaluru Fleet Out for Delivery + OTP
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> National Courier AWB & Tracking Links
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Cancellation Ticket & Bank UTR Proofs
                        </li>
                    </ul>
                </div>
                <a href="{{ route('admin.integrations.whatsapp') }}" class="btn btn-success rounded-pill w-100 fw-bold">
                    Open WhatsApp Hub <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 3. Razorpay Payment Gateway Card -->
    <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden hover-shadow transition-all">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(14, 165, 233, 0.1); color: #0284c7; width: 40px; height: 40px;">
                        <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Razorpay PG Gateway</h6>
                        <span class="text-muted" style="font-size: 0.72rem;">Online Checkout & UPI POD</span>
                    </div>
                </div>
                <span class="badge {{ $isRazorpayConfigured ? 'bg-primary' : 'bg-secondary' }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.71rem;">
                    {{ $isRazorpayConfigured ? ($isRazorpayLive ? 'LIVE Mode' : 'TEST Mode') : 'Not Configured' }}
                </span>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="p-3 bg-light rounded-3 border mb-3 text-center">
                        <div class="text-muted small fw-bold mb-1">Today's Online Revenue</div>
                        <div class="fs-4 fw-black text-primary">₹{{ number_format($todayOnlinePayments, 2) }}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">UPI & Cards Collected Today</div>
                    </div>
                    <ul class="list-unstyled small text-muted mb-4 space-y-2">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Instant Checkout UPI, NetBanking & Cards
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Doorstep Razorpay QR Code Rider POD
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill text-success"></i> Automatic Direct PG Refund processing
                        </li>
                    </ul>
                </div>
                <a href="{{ route('admin.integrations.razorpay') }}" class="btn btn-info text-white rounded-pill w-100 fw-bold">
                    Open Razorpay Hub <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
