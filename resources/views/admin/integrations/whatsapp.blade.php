@extends('admin.layouts.app')

@section('header', 'WhatsApp Integration')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations</a></li>
    <li class="breadcrumb-item active" aria-current="page">WhatsApp</li>
@endsection

@section('content')
<div class="row g-4 mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #15803d 0%, #047857 100%);">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0.5"><i class="bi bi-whatsapp me-2"></i>Meta WhatsApp API Integration</h5>
                    <p class="mb-0 text-white-50 small">Configure your WhatsApp numbers for Customer Login OTP and Delivery WhatsApp Alerts.</p>
                </div>
                <a href="{{ route('admin.integrations.index') }}" class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-dark shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Integrations
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Simple Clean Form -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders text-success me-2"></i>WhatsApp Configuration</h6>
                @php
                    $hasToken = !empty($settings['whatsapp_api_token'] ?? env('WHATSAPP_API_TOKEN', ''));
                @endphp
                @if($hasToken)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Connected
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 small fw-bold">
                        <i class="bi bi-exclamation-circle me-1"></i> Not Connected
                    </span>
                @endif
            </div>

            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.whatsapp.update') }}" method="POST">
                    @csrf

                    <!-- 1. Permanent Access Token -->
                    <div class="mb-3.5">
                        <label class="form-label fw-bold text-dark small mb-1">
                            System User Access Token <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="whatsapp_api_token" id="whatsapp_api_token" class="form-control font-monospace form-control-sm"
                                   placeholder="{{ $hasToken ? '•••••••••••••••• (Configured — leave blank to keep)' : 'Paste EAAG... access token' }}" autocomplete="new-password">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePasswordVisibility('whatsapp_api_token', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small" style="font-size: 0.72rem;">Meta Business Manager permanent System User Token.</div>
                    </div>

                    <hr class="my-3 text-muted opacity-25">

                    <!-- 2. Phone Numbers -->
                    <h6 class="fw-bold text-dark small mb-2.5"><i class="bi bi-telephone-fill text-primary me-1"></i> Phone Numbers (Sender IDs)</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small mb-1">
                            Channel 1: Auth / Login OTP Phone ID <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-lock"></i></span>
                            <input type="text" name="whatsapp_phone_number_id" class="form-control font-monospace form-control-sm"
                                   value="{{ $settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID', '') }}"
                                   placeholder="e.g. 1251262481410456" required>
                        </div>
                        <div class="form-text text-muted small" style="font-size: 0.72rem;">Primary number for Customer Login &amp; Register OTPs.</div>
                    </div>

                    <div class="mb-3.5">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-semibold text-dark small mb-0">
                                Channel 2: Orders &amp; Delivery Phone ID
                            </label>
                            <span class="text-muted" style="font-size: 0.70rem;">Optional</span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-bicycle"></i></span>
                            <input type="text" name="whatsapp_updates_phone_number_id" class="form-control font-monospace form-control-sm"
                                   value="{{ $settings['whatsapp_updates_phone_number_id'] ?? env('WHATSAPP_UPDATES_PHONE_NUMBER_ID', '') }}"
                                   placeholder="Leave blank to automatically use Channel 1">
                        </div>
                        <div class="form-text text-muted small" style="font-size: 0.72rem;">If left blank, delivery alerts safely route through Channel 1.</div>
                    </div>

                    <hr class="my-3 text-muted opacity-25">

                    <!-- 3. Approved Meta Template Names -->
                    <h6 class="fw-bold text-dark small mb-2.5"><i class="bi bi-chat-quote-fill text-success me-1"></i> Meta Approved Templates</h6>

                    <div class="row g-2.5 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label text-dark small fw-semibold mb-1">Channel 1 Template (OTP)</label>
                            <input type="text" name="whatsapp_template_name" class="form-control form-control-sm font-monospace"
                                   value="{{ $settings['whatsapp_template_name'] ?? 'shopcalm_otp' }}" placeholder="shopcalm_otp" required>
                            <div class="form-text text-muted" style="font-size: 0.68rem;">Customer Authentication</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label text-dark small fw-semibold mb-1">Channel 2 Template (Delivery)</label>
                            <input type="text" name="whatsapp_template_out_for_delivery" class="form-control form-control-sm font-monospace"
                                   value="{{ $settings['whatsapp_template_out_for_delivery'] ?? 'shopcalm_out_for_delivery' }}" placeholder="shopcalm_out_for_delivery">
                            <div class="form-text text-muted" style="font-size: 0.68rem;">Out for Delivery Alert</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold py-2 mt-2">
                        <i class="bi bi-check-circle me-1"></i> Save WhatsApp Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Clean OTP Verification Activity Logs -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-success me-2"></i>Recent OTP Verifications</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Live registration &amp; login requests</span>
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    {{ count($recentOtpLogs) }} Logs
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
                                <th class="text-end pe-3">Time</th>
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
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-semibold">Register</span>
                                        @elseif($otpLog->purpose === 'RESET_PASSWORD')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5 fw-semibold">Reset</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5 fw-semibold">{{ $otpLog->purpose }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($otpLog->verified_at)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">Verified ✓</span>
                                        @elseif($otpLog->expires_at && $otpLog->expires_at->isPast())
                                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5">Expired</span>
                                        @else
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5">Pending</span>
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
