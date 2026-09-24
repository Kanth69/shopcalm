@extends('order-manager.layouts.app')

@section('title', 'Fleet COD Cash Settlements')
@section('header', 'Fleet COD Cash Settlements')

@section('content')

<!-- Header Command Box -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-4 bg-white" style="border-top: 4px solid #10b981 !important;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1.5">
                <span class="badge rounded-pill px-3 py-1 fw-bold text-white shadow-xs" style="background: #10b981; font-size: 0.72rem;">
                    CENTRAL CASH VAULT
                </span>
                <span class="text-secondary small fw-semibold">&bull; Bengaluru Hub #01</span>
            </div>
            <h4 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.4px;">
                Rider COD Cash Handover & Shift Settlement
            </h4>
            <p class="text-secondary small mb-0" style="max-width: 600px;">
                Reconcile physical cash collected by in-house delivery partners at shift end. Instant balance resets with digital audit vouchers.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-dark rounded-pill px-3.5 py-2 fw-bold shadow-xs d-flex align-items-center gap-2" style="font-size: 0.82rem;" onclick="window.print()">
                <i class="bi bi-printer-fill"></i> Print Shift Summary
            </button>
        </div>
    </div>
</div>

<!-- Top Financial KPI Cards (3 Cards) -->
<div class="row g-3 mb-4">
    <!-- KPI 1: Floating Cash in Fleet -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 4px solid #f59e0b !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Floating Cash in Market</div>
                    <div class="h2 fw-bolder text-dark mb-0 mt-1 font-monospace" style="color: #b45309 !important;">
                        ₹{{ number_format($floatingCashTotal, 2) }}
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        {{ $pendingRidersCount }} rider(s) carrying un-deposited cash
                    </div>
                </div>
                <div class="rounded-4 d-flex align-items-center justify-content-center text-warning" style="width: 48px; height: 48px; background: #fef3c7; font-size: 1.5rem;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 2: Today's Deposited Cash -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 4px solid #10b981 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Deposited Today in Hub</div>
                    <div class="h2 fw-bolder text-success mb-0 mt-1 font-monospace">
                        ₹{{ number_format($todayDepositedTotal, 2) }}
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        Vault collections for {{ today()->format('d M Y') }}
                    </div>
                </div>
                <div class="rounded-4 d-flex align-items-center justify-content-center text-success" style="width: 48px; height: 48px; background: #ecfdf5; font-size: 1.5rem;">
                    <i class="bi bi-safe2-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI 3: Lifetime Total Reconciled -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden" style="border-left: 4px solid #0284c7 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">All-Time Settled Vault</div>
                    <div class="h2 fw-bolder text-primary mb-0 mt-1 font-monospace">
                        ₹{{ number_format($allTimeDepositedTotal, 2) }}
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                        Total verified COD receipts
                    </div>
                </div>
                <div class="rounded-4 d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px; background: #e0f2fe; font-size: 1.5rem;">
                    <i class="bi bi-bank2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section 1: Active Fleet Delivery Partners & Pending Cash Handover Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-person-lines-fill text-primary fs-5"></i>
            <div>
                <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">Rider End-of-Day Cash Handover Queue</h6>
                <span class="text-secondary small" style="font-size: 0.72rem;">Live floating cash in hand per delivery partner</span>
            </div>
        </div>
        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning rounded-pill px-2.5 py-1 small fw-bold">
            {{ $pendingRidersCount }} Pending Depositor(s)
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Rider Details</th>
                        <th>Mobile Contact</th>
                        <th>Delivered COD Orders</th>
                        <th>Cash in Hand (Pending)</th>
                        <th>Last Deposit Record</th>
                        <th class="text-end pe-4">Counter Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($riders as $rider)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 34px; height: 34px; background: #0284c7; font-size: 0.85rem;">
                                        {{ strtoupper(substr($rider->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $rider->name }}</div>
                                        <div class="text-secondary small font-monospace" style="font-size: 0.68rem;">Fleet ID: #{{ str_pad($rider->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="tel:{{ $rider->mobile_number }}" class="text-decoration-none text-dark small fw-semibold">
                                    <i class="bi bi-telephone text-success me-1"></i> {{ $rider->mobile_number ?? 'No Phone' }}
                                </a>
                            </td>
                            <td>
                                @if($rider->pending_cod_count > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">
                                        {{ $rider->pending_cod_count }} COD Order(s)
                                    </span>
                                @else
                                    <span class="text-muted small">0 Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($rider->pending_cod_amount > 0)
                                    <div class="h6 fw-bolder text-danger mb-0 font-monospace" style="font-size: 0.95rem;">
                                        ₹{{ number_format($rider->pending_cod_amount, 2) }}
                                    </div>
                                    <span class="text-secondary small" style="font-size: 0.68rem;">Requires deposit</span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; font-size: 0.75rem;">
                                        <i class="bi bi-check-circle-fill text-success"></i> All Settled (₹0.00)
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($rider->last_settlement)
                                    <div class="fw-bold text-dark small font-monospace">{{ $rider->last_settlement->settlement_number }}</div>
                                    <div class="text-secondary small" style="font-size: 0.68rem;">{{ $rider->last_settlement->created_at->diffForHumans() }} (₹{{ number_format($rider->last_settlement->total_amount, 2) }})</div>
                                @else
                                    <span class="text-muted small">No deposits yet</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                @if($rider->pending_cod_amount > 0)
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-xs" 
                                            data-bs-toggle="modal" data-bs-target="#modalDeposit{{ $rider->id }}" style="font-size: 0.78rem;">
                                        <i class="bi bi-cash-coin me-1"></i> Accept Deposit
                                    </button>

                                    <!-- Cash Deposit Handshake Modal -->
                                    <div class="modal fade text-start" id="modalDeposit{{ $rider->id }}" tabindex="-1" aria-labelledby="modalDepositLabel{{ $rider->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                                <div class="modal-header bg-light py-3 px-4 border-bottom">
                                                    <div>
                                                        <span class="badge bg-success rounded-pill px-2.5 py-0.5 text-white small fw-bold">Counter Cash Collection</span>
                                                        <h6 class="modal-title fw-bolder text-dark mb-0 mt-1" id="modalDepositLabel{{ $rider->id }}">
                                                            Cash Settlement for 🛵 {{ $rider->name }}
                                                        </h6>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <form action="{{ route('order-manager.settlements.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="rider_id" value="{{ $rider->id }}">
                                                    
                                                    <div class="modal-body p-4">
                                                        <!-- Top Balance Summary Banner -->
                                                        <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-30 mb-3 d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Verified Cash To Receive</div>
                                                                <div class="h3 fw-bolder text-success mb-0 font-monospace mt-0.5">
                                                                    ₹{{ number_format($rider->pending_cod_amount, 2) }}
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-success text-white rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.75rem;">
                                                                {{ $rider->pending_cod_count }} Delivered COD Orders
                                                            </span>
                                                        </div>

                                                        <!-- Itemized Delivered COD Orders Table -->
                                                        <label class="form-label fw-bold text-dark small mb-1.5">Itemized Order Audit Breakdown</label>
                                                        <div class="table-responsive rounded-3 border mb-3" style="max-height: 220px; overflow-y: auto;">
                                                            <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.78rem;">
                                                                <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                                    <tr>
                                                                        <th class="ps-3">Order Ref</th>
                                                                        <th>Customer</th>
                                                                        <th>Delivered At</th>
                                                                        <th class="text-end pe-3">COD Cash</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($rider->pending_cod_orders as $pOrder)
                                                                        <tr>
                                                                            <td class="ps-3 font-monospace fw-bold text-primary">#{{ $pOrder->order_number }}</td>
                                                                            <td>{{ $pOrder->shipping_name }}</td>
                                                                            <td>{{ $pOrder->delivered_at?->format('h:i A') ?? 'Delivered' }}</td>
                                                                            <td class="text-end pe-3 font-monospace fw-bold text-dark">₹{{ number_format($pOrder->total_amount, 2) }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold text-dark small">Physical Cash Counted (₹) <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                                                    <input type="number" step="0.01" name="amount" class="form-control fw-bold font-monospace" 
                                                                           value="{{ $rider->pending_cod_amount }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label fw-bold text-dark small">Handover Payment Mode <span class="text-danger">*</span></label>
                                                                <select name="payment_mode" class="form-select">
                                                                    <option value="cash">💵 Physical Cash Notes</option>
                                                                    <option value="upi">📱 Hub UPI / QR Code</option>
                                                                    <option value="bank_transfer">🏦 Direct Hub Bank Transfer</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label fw-bold text-dark small">Staff Verification Notes (Optional)</label>
                                                                <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Verified 7x500 notes at counter. Full amount received.">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer bg-light py-2.5 px-4 border-top">
                                                        <button type="button" class="btn btn-light border bg-white fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success fw-bold px-4">
                                                            <i class="bi bi-check2-circle me-1"></i> Accept Cash & Generate Receipt
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <button type="button" class="btn btn-sm btn-light border text-secondary rounded-pill px-3 py-1 fw-bold" disabled style="font-size: 0.75rem;">
                                        No Due Cash
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No registered delivery partners found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section 2: Recent Settlement History & Printable Vouchers -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-receipt text-success fs-5"></i>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">Vault Cash Deposit Receipts History</h6>
                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.65rem;">
                        {{ $settlements->total() }} Receipts
                    </span>
                </div>
                <span class="text-secondary small" style="font-size: 0.72rem;">Digital records of all counter cash handovers</span>
            </div>
        </div>

        <form action="{{ route('order-manager.settlements.index') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
            <div class="input-group input-group-sm shadow-xs" style="width: auto;">
                <span class="input-group-text bg-light border-end-0 px-2"><i class="bi bi-calendar3 text-secondary" style="font-size: 0.75rem;"></i></span>
                <input type="date" name="date_from" class="form-control border-start-0 px-2 fw-semibold text-secondary" 
                       value="{{ request('date_from') }}" style="max-width: 120px; font-size: 0.75rem;">
            </div>
            <span class="text-secondary small fw-bold" style="font-size: 0.7rem;">TO</span>
            <div class="input-group input-group-sm shadow-xs" style="width: auto;">
                <input type="date" name="date_to" class="form-control px-2 fw-semibold text-secondary" 
                       value="{{ request('date_to') }}" style="max-width: 120px; font-size: 0.75rem;">
                <button type="submit" class="btn btn-dark fw-bold px-3" style="font-size: 0.75rem;">Filter</button>
                @if(request('date_from') || request('date_to'))
                    <a href="{{ route('order-manager.settlements.index') }}" class="btn btn-light border text-secondary px-2" title="Clear Filters">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                    <tr>
                        <th class="ps-4">Receipt #</th>
                        <th>Rider Name</th>
                        <th>Amount Deposited</th>
                        <th>Payment Mode</th>
                        <th>Orders Reconciled</th>
                        <th>Received By</th>
                        <th>Settled Date & Time</th>
                        <th class="text-end pe-4">Voucher</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($settlements as $sett)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('order-manager.settlements.show', $sett) }}" class="fw-bolder text-primary font-monospace text-decoration-none">
                                    {{ $sett->settlement_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">🛵 {{ $sett->rider?->name ?? 'Fleet Rider' }}</div>
                                <div class="text-secondary small font-monospace" style="font-size: 0.68rem;">{{ $sett->rider?->mobile_number }}</div>
                            </td>
                            <td>
                                <div class="fw-bolder text-success font-monospace" style="font-size: 0.92rem;">
                                    ₹{{ number_format($sett->total_amount, 2) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small fw-bold text-uppercase">
                                    {{ $sett->payment_mode }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-2.5 py-1 small fw-bold">
                                    {{ $sett->order_count }} Order(s)
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $sett->receivedBy?->name ?? 'Order Manager' }}</div>
                                <div class="text-secondary small" style="font-size: 0.65rem;">Hub Staff</div>
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $sett->created_at->diffForHumans() }}</span>
                                <div class="text-muted small font-monospace" style="font-size: 0.68rem;">{{ $sett->created_at->format('d M Y, h:i A') }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('order-manager.settlements.show', $sett) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                                    <i class="bi bi-file-earmark-text me-1"></i> View Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox text-secondary d-block mb-1 fs-3"></i>
                                No settlement receipts recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($settlements->hasPages())
        <div class="card-footer bg-white py-3 px-4 border-top">
            {{ $settlements->links() }}
        </div>
    @endif
</div>

@endsection
