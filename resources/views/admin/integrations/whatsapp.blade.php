@extends('admin.layouts.app')

@section('header', 'Meta WhatsApp OTP Authentication Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">WhatsApp OTP Authentication</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #16a34a 0%, #059669 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-whatsapp me-2"></i>Meta WhatsApp Authentication OTP Control Center</h4>
                    <p class="mb-0 text-white-50 small">Manage Meta WhatsApp Cloud API credentials and the official Authentication OTP template used exclusively for Customer Registration and Password Resets.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.integrations.index') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-dark shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Integrations Hub
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: API Configuration & OTP Template -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-key-fill text-success me-2"></i>Meta WhatsApp Cloud API Credentials</h6>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Authentication Service</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.whatsapp.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">WhatsApp Business Phone Number ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="whatsapp_phone_number_id" class="form-control font-monospace" value="{{ $settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID', '') }}" placeholder="e.g. 1251262481410456" required>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.71rem;">Meta App Console &rarr; WhatsApp &rarr; API Setup &rarr; Phone Number ID</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">Permanent System User Access Token <span class="text-danger">*</span></label>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>Encrypted in DB
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                            <input type="password" name="whatsapp_api_token" id="whatsapp_api_token" class="form-control font-monospace" value="{{ $settings['whatsapp_api_token'] ?? env('WHATSAPP_API_TOKEN', '') }}" placeholder="EAAO2o..." required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('whatsapp_api_token', this)" title="Toggle view token"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.71rem;">Original token shown decrypted here. Stored safely encrypted in DB.</div>
                    </div>

                    <hr class="my-3 border-secondary-subtle">
                    <div class="fw-bold text-dark mb-3 small"><i class="bi bi-file-earmark-code text-primary me-1"></i> Approved Meta Template Registry:</div>

                    <!-- 1. Authentication OTP Template -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">1. Authentication OTP Template</label>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                Category: AUTHENTICATION
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                            <input type="text" name="whatsapp_template_name" class="form-control font-monospace" value="{{ $settings['whatsapp_template_name'] ?? 'shopcalm_otp' }}" placeholder="shopcalm_otp" required>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">
                            Used for customer <strong>Registration OTP</strong> & <strong>Password Reset OTP</strong>.
                        </div>
                    </div>

                    <!-- 2. Order Confirmation Template -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">2. Order Confirmation Template</label>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                Category: UTILITY
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-bag-check"></i></span>
                            <input type="text" name="whatsapp_template_order_confirmed" class="form-control font-monospace" value="{{ $settings['whatsapp_template_order_confirmed'] ?? 'shopcalm_order_confirmed' }}" placeholder="shopcalm_order_confirmed">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">
                            Compulsory notification sent to customer when order is confirmed (COD, Wallet, or Online UPI).
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save WhatsApp API Credentials
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent OTP Logs Activity -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-success me-2"></i>Recent OTP Verifications Activity</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Customer Registration & Password Reset Requests</span>
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    {{ count($recentOtpLogs) }} Recent Logs
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Mobile Number</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Requested At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOtpLogs as $otpLog)
                                <tr>
                                    <td class="ps-3 font-monospace fw-bold text-dark">
                                        {{ $otpLog->mobile_number }}
                                    </td>
                                    <td>
                                        @if($otpLog->purpose === 'REGISTER')
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-0.5 fw-bold">Customer Registration</span>
                                        @elseif($otpLog->purpose === 'RESET_PASSWORD')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-0.5 fw-bold">Password Reset</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2 py-0.5 fw-bold">{{ $otpLog->purpose }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($otpLog->verified_at)
                                            <span class="badge bg-success rounded-pill px-2 py-0.5">Verified ✓</span>
                                        @elseif($otpLog->expires_at && $otpLog->expires_at->isPast())
                                            <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill px-2 py-0.5">Expired</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-0.5">Active (Pending)</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 text-muted">
                                        {{ $otpLog->created_at ? $otpLog->created_at->diffForHumans() : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <i class="bi bi-shield-x fs-4 d-block mb-1 text-secondary"></i>
                                        No recent OTP verification requests recorded.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
@endpush
