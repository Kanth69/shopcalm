@extends('admin.layouts.app')

@section('header', 'Razorpay Payment Gateway Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">Razorpay Gateway</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front-fill me-2"></i>Razorpay Payment Gateway Control Center</h4>
                    <p class="mb-0 text-white-50 small">Manage Razorpay API Keys, review live online revenue & Doorstep Razorpay UPI POD collections, and inspect payment transaction ledgers.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-cash-stack text-success me-1"></i>Total Online Revenue</div>
            <div class="fs-4 fw-black text-dark">₹{{ number_format($stats['total_online_revenue'] ?? 0, 2) }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Cumulative Gateway Collections</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-calendar-check text-primary me-1"></i>Today's Revenue</div>
            <div class="fs-4 fw-black text-primary">₹{{ number_format($stats['today_online_revenue'] ?? 0, 2) }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Collected Today</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>Successful Checkout</div>
            <div class="fs-4 fw-black text-success">{{ $stats['successful_count'] ?? 0 }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Transactions Verified</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-qr-code text-info me-1"></i>Doorstep UPI POD</div>
            <div class="fs-4 fw-black text-info">{{ $stats['doorstep_upi_count'] ?? 0 }}</div>
            <div class="text-muted" style="font-size: 0.7rem;">Rider QR Collections</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Razorpay Credentials Form -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-key-fill text-primary me-2"></i>Razorpay API Credentials</h6>
                <span class="badge {{ str_starts_with($settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', ''), 'rzp_live') ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
                    {{ str_starts_with($settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', ''), 'rzp_live') ? 'LIVE Mode' : 'TEST Mode' }}
                </span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.razorpay.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Razorpay Key ID <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="razorpay_key_id" class="form-control font-monospace" value="{{ $settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', 'rzp_test_TiwC0gVacieKkn') }}" placeholder="rzp_test_... or rzp_live_...">
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Razorpay Dashboard &rarr; Account Settings &rarr; API Keys</div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">Razorpay Key Secret <span class="text-danger">*</span></label>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>Stored Encrypted in DB
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="razorpay_key_secret" id="razorpay_key_secret" class="form-control font-monospace" value="{{ $settings['razorpay_key_secret'] ?? env('RAZORPAY_KEY_SECRET', 'nuKs1b9OeDT5p0Jxr4pUKcUR') }}" placeholder="Key Secret...">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('razorpay_key_secret', this)" title="Toggle view secret"><i class="bi bi-eye"></i></button>
                        </div>
                        <div class="form-text text-muted" style="font-size: 0.72rem;">Original secret shown decrypted here. Stored safely encrypted in DB.</div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save Razorpay Credentials
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent Payments Ledger -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Online Payments Ledger</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Verified Gateway Ledger</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                        <thead class="bg-light text-muted fw-bold">
                            <tr>
                                <th class="ps-3 py-3">Order #</th>
                                <th>Customer</th>
                                <th>Method / Gateway</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPayments as $pay)
                                <tr>
                                    <td class="ps-3 fw-bold">
                                        <a href="{{ route('admin.orders.show', $pay->order_id) }}" class="text-decoration-none text-primary">#{{ $pay->order_number }}</a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $pay->order?->shipping_name ?? ($pay->user?->name ?? 'Customer') }}</div>
                                        <div class="text-muted font-monospace" style="font-size: 0.71rem;">Ref: {{ $pay->bank_reference ?: ($pay->gateway_payment_id ?: 'N/A') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace fw-bold">
                                            {{ strtoupper($pay->payment_method_group ?: $pay->gateway) }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">₹{{ number_format($pay->amount, 2) }}</td>
                                    <td>
                                        @if(in_array(strtolower($pay->status), ['success', 'paid']))
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold">
                                                <i class="bi bi-check-circle me-1"></i> SUCCESS
                                            </span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-dark rounded-pill px-2.5 py-1 fw-bold">
                                                {{ strtoupper($pay->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No recent online payment ledger records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($recentPayments->hasPages())
                <div class="card-footer bg-white py-2 border-top">
                    {{ $recentPayments->links() }}
                </div>
            @endif
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
