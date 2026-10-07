@extends('admin.layouts.app')

@section('header', 'Meta WhatsApp Business API Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">WhatsApp Cloud API</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #16a34a 0%, #059669 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-whatsapp me-2"></i>Meta WhatsApp Business API Control Center</h4>
                    <p class="mb-0 text-white-50 small">Configure unified Meta Cloud API token, phone number ID, and template registries for automated order lifecycle messaging.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: API Configuration & Template Registry -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-key-fill text-success me-2"></i>Meta WhatsApp Cloud API Credentials</h6>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Unified Cloud Token</span>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.integrations.whatsapp.update') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">WhatsApp Business Phone Number ID <span class="text-danger">*</span></label>
                            <input type="text" name="whatsapp_phone_number_id" class="form-control font-monospace" value="{{ $settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID', '') }}" placeholder="e.g. 104829104820194">
                            <div class="form-text text-muted" style="font-size: 0.71rem;">Meta App Console &rarr; WhatsApp &rarr; Phone Number ID</div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-bold text-dark small mb-0">Permanent Access Token <span class="text-danger">*</span></label>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    <i class="bi bi-shield-lock-fill me-1"></i>Stored Encrypted in DB
                                </span>
                            </div>
                            <div class="input-group">
                                <input type="password" name="whatsapp_api_token" id="whatsapp_api_token" class="form-control font-monospace" value="{{ $settings['whatsapp_api_token'] ?? env('WHATSAPP_API_TOKEN', '') }}" placeholder="EAAG.....">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('whatsapp_api_token', this)" title="Toggle view token"><i class="bi bi-eye"></i></button>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.71rem;">Original token shown decrypted here. Stored safely encrypted in DB.</div>
                        </div>
                    </div>

                    <hr class="my-3 border-secondary-subtle">
                    <div class="fw-bold text-dark mb-3 small"><i class="bi bi-file-earmark-code text-primary me-1"></i> Meta Approved Template Names Registry:</div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">1. Login OTP Template</label>
                            <input type="text" name="whatsapp_template_name" class="form-control font-monospace" value="{{ $settings['whatsapp_template_name'] ?? 'authentication_otp' }}" placeholder="authentication_otp">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">2. Order Confirmed Template</label>
                            <input type="text" name="whatsapp_template_order_confirmed" class="form-control font-monospace" value="{{ $settings['whatsapp_template_order_confirmed'] ?? 'order_confirmed' }}" placeholder="order_confirmed">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">3. Local Out For Delivery (OTP)</label>
                            <input type="text" name="whatsapp_template_out_for_delivery_local" class="form-control font-monospace" value="{{ $settings['whatsapp_template_out_for_delivery_local'] ?? 'out_for_delivery_otp' }}" placeholder="out_for_delivery_otp">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">4. Courier Dispatched (AWB Link)</label>
                            <input type="text" name="whatsapp_template_courier_dispatched" class="form-control font-monospace" value="{{ $settings['whatsapp_template_courier_dispatched'] ?? 'courier_dispatched' }}" placeholder="courier_dispatched">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">5. Order Delivered Template</label>
                            <input type="text" name="whatsapp_template_order_delivered" class="form-control font-monospace" value="{{ $settings['whatsapp_template_order_delivered'] ?? 'order_delivered' }}" placeholder="order_delivered">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">6. Order Cancelled Template</label>
                            <input type="text" name="whatsapp_template_order_cancelled" class="form-control font-monospace" value="{{ $settings['whatsapp_template_order_cancelled'] ?? 'order_cancelled' }}" placeholder="order_cancelled">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">7. Refund Processed Template (Bank UTR)</label>
                            <input type="text" name="whatsapp_template_refund_processed" class="form-control font-monospace" value="{{ $settings['whatsapp_template_refund_processed'] ?? 'refund_processed' }}" placeholder="refund_processed">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success rounded-pill w-100 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save WhatsApp Configurations
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Recent WhatsApp Notifications Activity -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-whatsapp text-success me-2"></i>Live Dispatch Activity Log</h6>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">Order Events</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.83rem;">
                        <thead class="bg-light text-muted fw-bold">
                            <tr>
                                <th class="ps-3 py-3">Order #</th>
                                <th>Recipient</th>
                                <th>Event Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWhatsAppLogs as $logOrd)
                                <tr>
                                    <td class="ps-3 fw-bold">
                                        <a href="{{ route('admin.orders.show', $logOrd) }}" class="text-decoration-none text-success">#{{ $logOrd->order_number }}</a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $logOrd->shipping_name }}</div>
                                        <div class="text-muted font-monospace" style="font-size: 0.72rem;">{{ $logOrd->shipping_phone }}</div>
                                    </td>
                                    <td>
                                        @if($logOrd->status === 'delivered')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 fw-bold">Delivered</span>
                                        @elseif($logOrd->status === 'out for delivery')
                                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-0.5 fw-bold">Out for Delivery</span>
                                        @elseif($logOrd->status === 'shipped')
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5 fw-bold">Courier Dispatched</span>
                                        @elseif($logOrd->status === 'cancelled')
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5 fw-bold">Cancelled & Refund</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-2 py-0.5 fw-bold">{{ ucfirst($logOrd->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No recent WhatsApp dispatch activity.</td>
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
