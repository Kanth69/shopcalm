@extends('admin.layouts.app')

@section('header', 'Brevo Email REST API Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">Brevo Email Hub</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-envelope-paper-fill me-2"></i>Brevo Transactional Email Service Control Center</h4>
                    <p class="mb-0 text-white-50 small">Monitor live Brevo daily email credit quotas, test email delivery, and manage sender credentials.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-dark shadow-sm" onclick="refreshBrevoCredits(this)">
                        <i class="bi bi-arrow-clockwise me-1"></i> Sync Live Credits
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Remaining Quota</div>
            <div class="fs-4 fw-black text-dark" id="brevoCreditsVal">{{ $brevoQuota['credits_label'] ?? '0' }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Live Daily Limit</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-award-fill text-primary me-1"></i>Active Plan</div>
            <div class="fs-5 fw-bold text-dark text-capitalize" id="brevoPlanVal">{{ $brevoQuota['plan_type'] ?? 'Free' }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Brevo Subscription</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-shield-check text-success me-1"></i>SMTP Relay</div>
            <div class="fs-5 fw-bold {{ ($brevoQuota['smtp_enabled'] ?? false) ? 'text-success' : 'text-muted' }}" id="brevoSmtpVal">
                {{ ($brevoQuota['smtp_enabled'] ?? false) ? 'Active ✓' : 'Disabled' }}
            </div>
            <div class="text-muted" style="font-size: 0.7rem;">Transport Relay Status</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-person-bounding-box text-info me-1"></i>Owner Profile</div>
            <div class="small fw-bold text-dark text-truncate" id="brevoAccountEmailVal" title="{{ $brevoQuota['account_email'] ?? 'N/A' }}">{{ $brevoQuota['account_email'] ?? 'N/A' }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Brevo Account Email</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Settings & Test Tool -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-key-fill text-primary me-2"></i>Brevo REST API Credentials & Sender Profile</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.brevo.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">Brevo v3 REST API Key <span class="text-danger">*</span></label>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>Stored Encrypted in DB
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                            <input type="text" name="brevo_api_key" id="brevo_api_key" class="form-control font-monospace" value="{{ $settings['brevo_api_key'] ?? env('BREVO_API_KEY', '') }}" placeholder="xkeysib-...">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('brevo_api_key', this)" title="Toggle hide/mask key"><i class="bi bi-eye-slash"></i></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="copyCredential('brevo_api_key', this)" title="Copy key"><i class="bi bi-clipboard"></i></button>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Original API key shown in plain text here. Safely stored encrypted at rest in the database.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Default Sender Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="brevo_sender_email" class="form-control" value="{{ $settings['brevo_sender_email'] ?? (config('mail.from.address') ?: 'support@shopcalm.in') }}" placeholder="support@shopcalm.in">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Must be a verified sender in Brevo Senders list.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark small">Default Sender Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="brevo_sender_name" class="form-control" value="{{ $settings['brevo_sender_name'] ?? 'ShopCalm Orders' }}" placeholder="ShopCalm Orders">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save Brevo Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Dispatch Live Test Email Tool -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-send-fill text-dark me-2"></i>Dispatch Verification Test Email</h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">Send a live test email to verify that Brevo REST API delivery and SMTP fallback are operational.</p>
                <div class="input-group mb-2">
                    <input type="email" id="test_email_recipient" class="form-control" value="{{ auth('admin')->user()?->email ?? 'support@shopcalm.in' }}" placeholder="receiver@example.com">
                    <button type="button" class="btn btn-dark fw-bold px-3" onclick="sendBrevoTestEmail(this)">
                        <i class="bi bi-send-fill me-1"></i> Send Test Email
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Email Activity History -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Recent Email Dispatches</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Customer Invoices</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-muted fw-bold">
                            <tr>
                                <th class="ps-3 py-3">Order #</th>
                                <th>Recipient</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEmailOrders as $ord)
                                <tr>
                                    <td class="ps-3 fw-bold">
                                        <a href="{{ route('admin.orders.show', $ord) }}" class="text-decoration-none text-primary">#{{ $ord->order_number }}</a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $ord->shipping_name }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $ord->shipping_email }}</div>
                                    </td>
                                    <td class="fw-bold text-dark">₹{{ number_format($ord->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.7rem;">
                                            <i class="bi bi-check-circle me-1"></i> Invoice Emailed
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No recent email invoice records.</td>
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

function refreshBrevoCredits(btn) {
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

    fetch("{{ route('admin.settings.brevo-credits') }}", {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        const creditsVal = document.getElementById('brevoCreditsVal');
        const planVal = document.getElementById('brevoPlanVal');
        const smtpVal = document.getElementById('brevoSmtpVal');
        const emailVal = document.getElementById('brevoAccountEmailVal');

        if (creditsVal) creditsVal.innerText = data.credits_label || '0';
        if (planVal) planVal.innerText = data.plan_type || 'Free';
        if (emailVal) {
            emailVal.innerText = data.account_email || 'N/A';
            emailVal.title = data.account_email || 'N/A';
        }
        if (smtpVal) {
            smtpVal.innerText = data.smtp_enabled ? 'Active ✓' : 'Disabled';
            smtpVal.className = 'fs-5 fw-bold ' + (data.smtp_enabled ? 'text-success' : 'text-muted');
        }
        if (window.toast) {
            window.toast({ type: data.connected ? 'success' : 'warning', title: 'Brevo Credits Synced', message: data.connected ? `Live Credits: ${data.credits_label}` : data.message });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Failed to sync Brevo credits. Please check API Key configuration.');
    });
}

function sendBrevoTestEmail(btn) {
    const recipient = document.getElementById('test_email_recipient').value.trim();
    if (!recipient) {
        alert('Please enter a recipient email address for testing.');
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

    fetch("{{ route('admin.settings.test-email') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: recipient })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.success) {
            alert(`Success! ${data.message}`);
        } else {
            alert(`Test Email Failed: ${data.message}`);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Error dispatching test email. Please check server logs.');
    });
}

function copyCredential(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input || !input.value) return;
    navigator.clipboard.writeText(input.value).then(() => {
        const icon = btn.querySelector('i');
        if (icon) {
            const origClass = icon.className;
            icon.className = 'bi bi-check2 text-success';
            setTimeout(() => { icon.className = origClass; }, 2000);
        }
    });
}
</script>
@endpush
