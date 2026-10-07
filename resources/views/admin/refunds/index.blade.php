@extends(request()->routeIs('order-manager.*') ? 'order-manager.layouts.app' : 'admin.layouts.app')

@section('title', 'Customer Refund Requests')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-primary"></i> Customer Refund Requests &amp; GST Retention
            </h4>
            <p class="text-muted small mb-0">Manage customer order cancellations, process manual Bank UPI refunds, and track retained GST fees.</p>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between gap-2 mb-4 alert-dismissible fade show" role="alert" id="autoDismissAlert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid #fde68a !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-warning-emphasis fw-semibold small">Pending Bank UPI Refunds</div>
                        <div class="fs-3 fw-bold text-dark mt-1">₹{{ number_format($pendingUpiAmount, 2) }}</div>
                        <div class="badge bg-warning text-dark rounded-pill mt-1" style="font-size: 0.72rem;">{{ $pendingUpiCount }} Requests Pending</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-bank fs-4 text-warning-emphasis"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #a7f3d0 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-success-emphasis fw-semibold small">Processed Refunds</div>
                        <div class="fs-3 fw-bold text-dark mt-1">₹{{ number_format($processedAmount, 2) }}</div>
                        <div class="badge bg-success rounded-pill mt-1" style="font-size: 0.72rem;">{{ $processedCount }} Completed</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-check2-circle fs-4 text-success-emphasis"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%); border: 1px solid #a5b4fc !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-indigo-emphasis fw-semibold small">Total Retained GST Tax Fees</div>
                        <div class="fs-3 fw-bold text-dark mt-1">₹{{ number_format($totalGstRetained, 2) }}</div>
                        <div class="badge bg-indigo text-white rounded-pill mt-1" style="font-size: 0.72rem; background:#4338ca;">Non-Refundable Fee Revenue</div>
                    </div>
                    <div class="rounded-circle bg-indigo bg-opacity-25 d-flex align-items-center justify-content-center" style="width:48px; height:48px;">
                        <i class="bi bi-receipt-cutoff fs-4 text-indigo"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ url()->current() }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search Order #, Customer, or UPI ID..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Refund Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Refunds</option>
                        <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed Refunds</option>
                        <option value="none" {{ request('status') === 'none' ? 'selected' : '' }}>None (COD / Fee Only)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="method" class="form-select" onchange="this.form.submit()">
                        <option value="">All Refund Methods</option>
                        <option value="bank_upi" {{ request('method') === 'bank_upi' ? 'selected' : '' }}>🏦 Bank UPI / Account</option>
                        <option value="wallet" {{ request('method') === 'wallet' ? 'selected' : '' }}>⚡ Store Wallet</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <a href="{{ url()->current() }}" class="btn btn-light border w-100 fw-semibold"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr class="text-nowrap">
                        <th class="ps-4">Ticket No</th>
                        <th>Order Details</th>
                        <th>Customer</th>
                        <th>Cancellation Reason</th>
                        <th class="text-end">Order Total</th>
                        <th class="text-end">GST Retained</th>
                        <th class="text-end">Net Refund</th>
                        <th>Destination</th>
                        <th>Refund Status</th>
                        <th class="pe-4 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cancellations as $item)
                    @php
                        $ord = $item->order;
                        $orderNumber = $ord?->order_number ?? $item->order_id;
                        $ticketNo = 'CNL-' . $orderNumber;
                        $orderShowRoute = request()->routeIs('order-manager.*') ? 'order-manager.orders.show' : 'admin.orders.show';
                        $orderShowUrl = $ord ? route($orderShowRoute, $ord->order_number ?? $ord->id) : '#';

                        $walletUsed = (float) ($ord?->wallet_amount_used ?? 0);
                        $totalAmt = (float) ($ord?->total_amount ?? 0);
                        $payMethod = strtolower($ord?->payment_method ?? '');

                        if (($payMethod === 'wallet' || $payMethod === 'store_wallet' || $walletUsed > 0) && $totalAmt == 0) {
                            $payModeBadgeClass = 'bg-success text-white';
                            $payModeIcon = 'bi-wallet2';
                            $payModeText = '100% Wallet';
                        } elseif ($walletUsed > 0 && $totalAmt > 0) {
                            if ($payMethod === 'cod') {
                                $payModeBadgeClass = 'bg-secondary text-white';
                                $payModeIcon = 'bi-wallet2';
                                $payModeText = 'Wallet + COD';
                            } else {
                                $payModeBadgeClass = 'bg-primary text-white';
                                $payModeIcon = 'bi-wallet2';
                                $payModeText = 'Wallet + Prepaid';
                            }
                        } elseif ($payMethod === 'cod') {
                            $payModeBadgeClass = 'bg-dark text-white';
                            $payModeIcon = 'bi-cash-stack';
                            $payModeText = 'COD';
                        } else {
                            $payModeBadgeClass = 'bg-primary text-white';
                            $payModeIcon = 'bi-credit-card';
                            $payModeText = 'Prepaid (Online)';
                        }
                    @endphp
                    <tr>
                        <td class="ps-4 text-nowrap">
                            <div class="d-flex align-items-center gap-1.5">
                                <code class="text-primary bg-primary bg-opacity-10 px-2 py-1 rounded border border-primary border-opacity-25 font-monospace fw-bold" style="font-size: 0.76rem;">{{ $ticketNo }}</code>
                                <button type="button" class="btn btn-link p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $ticketNo }}')" title="Copy Ticket No">
                                    <i class="bi bi-copy" style="font-size: 0.8rem;"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            @if($ord)
                                <a href="{{ $orderShowUrl }}" target="_blank" class="fw-bold text-primary text-decoration-none" title="View Full Order Details">
                                    #{{ $orderNumber }} <i class="bi bi-box-arrow-up-right opacity-75 ms-0.5" style="font-size: 0.68rem;"></i>
                                </a>
                            @else
                                <span class="fw-bold text-dark">#{{ $orderNumber }}</span>
                            @endif
                            <div class="text-muted small mb-1" style="font-size: 0.74rem;">
                                {{ $item->created_at ? $item->created_at->format('d M Y, h:i A') : '—' }}
                            </div>
                            <span class="badge {{ $payModeBadgeClass }} rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.67rem;">
                                <i class="bi {{ $payModeIcon }} me-1"></i>{{ $payModeText }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->user->name ?? 'Guest' }}</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">{{ $item->user->phone ?? $item->user->email ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <span class="badge bg-light text-dark border px-2 py-1 text-wrap text-start" style="font-size: 0.75rem; max-width: 200px;">
                                    {{ Str::limit($item->cancellation_reason, 40) }}
                                </span>
                                @if($item->cancelled_by_type === 'admin')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1.5 py-0.5" style="font-size: 0.65rem; width: fit-content;">
                                        <i class="bi bi-shield-x me-1"></i>By Store Admin
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-1.5 py-0.5" style="font-size: 0.65rem; width: fit-content;">
                                        <i class="bi bi-person me-1"></i>By Customer
                                    </span>
                                @endif
                                @if($item->admin_notes)
                                    <div class="text-muted small fst-italic" style="font-size: 0.7rem;">Note: {{ Str::limit($item->admin_notes, 30) }}</div>
                                @endif
                            </div>
                        </td>
                        @php
                            $walletAmt = (float) ($item->wallet_refund_amount ?? $item->order->wallet_amount_used ?? 0);
                            $onlineAmt = (float) ($item->online_refund_amount ?? 0);
                            $orderTotalVal = ($item->order->total_amount ?? 0) > 0 ? ($item->order->total_amount + $walletAmt) : ($walletAmt > 0 ? $walletAmt : ($item->order->subtotal_amount ?? 0));
                            $totalNetRefund = $item->refund_amount > 0 ? $item->refund_amount : ($walletAmt + $onlineAmt);
                            $refundProof = $item->razorpay_refund_id ?: $item->payment_reference;
                        @endphp
                        <td class="text-end fw-bold text-dark">
                            ₹{{ number_format($orderTotalVal, 2) }}
                        </td>
                        <td class="text-end fw-bold text-danger">
                            -₹{{ number_format($item->cancellation_fee, 2) }}
                        </td>
                        <td class="text-end fw-bold text-success" style="font-size: 0.95rem;">
                            ₹{{ number_format($totalNetRefund, 2) }}
                        </td>
                        <td>
                            @if($item->refund_method === 'dual' || ($walletAmt > 0 && $onlineAmt > 0))
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge bg-success bg-opacity-25 text-success-emphasis border border-success px-2 py-0.5" style="font-size: 0.68rem;">
                                        ⚡ Wallet: ₹{{ number_format($walletAmt, 2) }}
                                    </span>
                                    <span class="badge bg-primary bg-opacity-25 text-primary-emphasis border border-primary px-2 py-0.5" style="font-size: 0.68rem;">
                                        💳 PG: ₹{{ number_format($onlineAmt, 2) }}
                                    </span>
                                </div>
                            @elseif($item->refund_method === 'wallet' || ($walletAmt > 0 && $onlineAmt <= 0))
                                <span class="badge bg-success bg-opacity-25 text-success-emphasis border border-success px-2.5 py-1">
                                    ⚡ ShopCalm Wallet (Instant)
                                </span>
                            @elseif($item->refund_method === 'original_source' || $item->refund_method === 'online')
                                <span class="badge bg-primary bg-opacity-25 text-primary-emphasis border border-primary px-2.5 py-1">
                                    💳 Original Source (Razorpay PG)
                                </span>
                                @if($refundProof)
                                    <div class="font-monospace text-muted small mt-1 d-flex align-items-center gap-1" style="font-size: 0.71rem;">
                                        <span>Ref: {{ $refundProof }}</span>
                                        <button type="button" class="btn btn-link p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $refundProof }}')" title="Copy Ref">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                @endif
                            @elseif($item->refund_method === 'bank_upi')
                                <span class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning px-2.5 py-1">
                                    🏦 Bank UPI
                                </span>
                                @if($item->refund_upi_id)
                                    <div class="font-monospace fw-bold small text-dark mt-1 d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                        <span>{{ $item->refund_upi_id }}</span>
                                        <button type="button" class="btn btn-link p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $item->refund_upi_id }}')" title="Copy UPI ID">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                @endif
                            @else
                                <span class="badge bg-light text-muted border px-2.5 py-1">
                                    None (COD)
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($item->refund_status === 'processed' || $item->online_refund_status === 'processed')
                                <span class="badge bg-success rounded-pill px-3 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i> Processed
                                </span>
                                @if($refundProof)
                                    <div class="text-muted small mt-1 d-flex align-items-center gap-1 font-monospace" style="font-size: 0.71rem;">
                                        <span>Ref: {{ $refundProof }}</span>
                                        <button type="button" class="btn btn-link p-0 text-muted" onclick="navigator.clipboard.writeText('{{ $refundProof }}')" title="Copy Reference">
                                            <i class="bi bi-copy"></i>
                                        </button>
                                    </div>
                                @endif
                            @elseif($item->refund_status === 'pending')
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                    <i class="bi bi-clock-history me-1"></i> Pending Payout
                                </span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-1">None</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            @if($item->refund_status === 'pending' && $item->refund_method === 'bank_upi')
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="openProcessRefundModal({{ $item->id }}, '{{ $item->order->order_number ?? '' }}', '{{ $item->refund_upi_id }}', {{ $item->refund_amount }})">
                                    ⚡ Process Refund
                                </button>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                            No cancellation or refund requests found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cancellations->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $cancellations->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>

</div>

{{-- Process Refund Modal --}}
<div class="modal fade" id="processRefundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="bi bi-bank text-primary me-2"></i>Process Bank UPI Refund
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="processRefundForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 p-3 mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Order Number:</span>
                            <strong id="modalOrderNo" class="text-dark"></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>Customer UPI VPA Handle:</span>
                            <div class="d-flex align-items-center gap-1">
                                <span class="font-monospace fw-bold text-dark" id="modalUpiId"></span>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1.5" onclick="copyUpiToClipboard()" title="Copy UPI Handle">
                                    <i class="bi bi-copy" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                        </div>
                        <hr class="my-1.5">
                        <div class="d-flex justify-content-between align-items-center fw-bold">
                            <span>Net Refund Amount to Transfer:</span>
                            <span class="text-success fs-6 font-monospace" id="modalRefundAmt">₹0.00</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Payout Method / Channel Used <span class="text-danger">*</span></label>
                        <select name="payout_channel" class="form-select fw-semibold" required>
                            <option value="razorpay_direct" selected>Razorpay Direct Refund API</option>
                            <option value="manual_upi">Manual Bank UPI (GPay / PhonePe / Paytm)</option>
                            <option value="hdfc_netbanking">HDFC / ICICI NetBanking Portal</option>
                            <option value="phonepe_business">PhonePe Business Payout</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Bank UTR / Payout Reference No. <span class="text-danger">*</span></label>
                        <input type="text" name="payment_reference" class="form-control font-monospace fw-bold" placeholder="e.g. UTR9988221104 / PAYOUT-1234" required>
                        <div class="form-text" style="font-size: 0.72rem;">Enter the bank UTR reference or transaction receipt ID from your bank statement.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Internal Admin Notes (Optional)</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Transferred via HDFC NetBanking to verified customer UPI handle"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-2 px-4">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold">
                        Mark Refund Processed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let activeUpiHandle = '';

function openProcessRefundModal(cancellationId, orderNumber, upiId, amount) {
    activeUpiHandle = upiId;
    document.getElementById('modalOrderNo').innerText = '#' + orderNumber;
    document.getElementById('modalUpiId').innerText = upiId;
    document.getElementById('modalRefundAmt').innerText = '₹' + amount.toFixed(2);

    const form = document.getElementById('processRefundForm');
    const isOrderManager = {{ request()->routeIs('order-manager.*') ? 'true' : 'false' }};
    const baseUrl = isOrderManager ? '/order-manager/refunds' : '/admin/refunds';
    form.action = `${baseUrl}/${cancellationId}/process`;

    const modal = new bootstrap.Modal(document.getElementById('processRefundModal'));
    modal.show();
}

function copyUpiToClipboard() {
    if (activeUpiHandle) {
        navigator.clipboard.writeText(activeUpiHandle);
        alert('UPI VPA handle (' + activeUpiHandle + ') copied to clipboard!');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const alertEl = document.getElementById('autoDismissAlert');
    if (alertEl) {
        setTimeout(function() {
            try {
                const bsAlert = new bootstrap.Alert(alertEl);
                bsAlert.close();
            } catch (e) {
                alertEl.style.display = 'none';
            }
        }, 4000);
    }
});
</script>
@endpush
@endsection
