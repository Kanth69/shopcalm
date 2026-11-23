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
            <button type="button" class="btn btn-success rounded-pill px-3.5 py-2 fw-bold shadow-xs d-flex align-items-center gap-2" style="font-size: 0.82rem;" data-bs-toggle="modal" data-bs-target="#scanQrModal">
                <i class="bi bi-qr-code-scan"></i> Scan Rider QR / PIN
            </button>
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
                        <th>Undelivered in Bag</th>
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
                                @if($rider->undelivered_count > 0)
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 rider-return-badge-{{ $rider->id }}" style="background: #fef2f2; color: #b91c1c; border: 1.5px solid #fca5a5; font-size: 0.74rem;">
                                        <i class="bi bi-box-seam-fill text-danger"></i> {{ $rider->undelivered_count }} Return Box(es)
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 rider-return-badge-{{ $rider->id }}" style="background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; font-size: 0.72rem;">
                                        <i class="bi bi-check2-all text-success"></i> Bag Empty
                                    </span>
                                @endif
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
                                @if($rider->needs_settlement)
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                                            data-bs-toggle="modal" data-bs-target="#modalDeposit{{ $rider->id }}" style="font-size: 0.78rem;">
                                        <i class="bi bi-shield-check"></i> Settle Shift
                                    </button>

                                    <!-- Cash Deposit & Return Parcels Handshake Modal -->
                                    <div class="modal fade text-start" id="modalDeposit{{ $rider->id }}" tabindex="-1" aria-labelledby="modalDepositLabel{{ $rider->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                                <div class="modal-header bg-light py-3 px-4 border-bottom">
                                                    <div>
                                                        <span class="badge bg-success rounded-pill px-2.5 py-0.5 text-white small fw-bold">Hub Counter Handshake</span>
                                                        <h6 class="modal-title fw-bolder text-dark mb-0 mt-1" id="modalDepositLabel{{ $rider->id }}">
                                                            Shift Reconciliation for 🛵 {{ $rider->name }}
                                                        </h6>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>

                                                <form action="{{ route('order-manager.settlements.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="rider_id" value="{{ $rider->id }}">
                                                    
                                                    <div class="modal-body p-4">
                                                        <!-- Top 2-Card Summary Banner: Cash Due + Undelivered Returns -->
                                                        <div class="row g-2.5 mb-3">
                                                            <div class="col-sm-6">
                                                                <div class="p-3 rounded-4 bg-success bg-opacity-10 border border-success border-opacity-30 h-100">
                                                                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Verified Cash To Receive</div>
                                                                    <div class="h3 fw-bolder text-success mb-0 font-monospace mt-0.5">
                                                                        ₹{{ number_format($rider->pending_cod_amount, 2) }}
                                                                    </div>
                                                                    <span class="text-secondary small mt-1 d-block" style="font-size: 0.72rem;">
                                                                        {{ $rider->pending_cod_count }} Delivered COD Orders
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <div class="p-3 rounded-4 {{ $rider->undelivered_count > 0 ? 'bg-danger bg-opacity-10 border border-danger border-opacity-30' : 'bg-light border' }} h-100">
                                                                    <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Undelivered Boxes in Bag</div>
                                                                    <div class="h3 fw-bolder {{ $rider->undelivered_count > 0 ? 'text-danger' : 'text-success' }} mb-0 font-monospace mt-0.5">
                                                                        {{ $rider->undelivered_count }} Parcel(s)
                                                                    </div>
                                                                    <span class="text-secondary small mt-1 d-block" style="font-size: 0.72rem;">
                                                                        {{ $rider->undelivered_count > 0 ? 'Scan & accept boxes into hub shelf' : 'Bag physically empty ✓' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Section A: Physical Return Parcels Intake -->
                                                        <div class="p-3 rounded-3 bg-light border mb-3">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <span class="fw-bold text-dark small">
                                                                    <i class="bi bi-box-arrow-in-down text-danger me-1"></i> Physical Return Parcels Intake
                                                                </span>
                                                                <span class="badge {{ $rider->undelivered_count > 0 ? 'bg-danger' : 'bg-success' }} rounded-pill px-2.5 py-1 small">
                                                                    {{ $rider->undelivered_count }} Boxes in Bag
                                                                </span>
                                                            </div>

                                                            @if($rider->undelivered_count > 0)
                                                                <!-- Barcode Scanner Gun Quick Input -->
                                                                <div class="input-group input-group-sm mb-2 shadow-xs">
                                                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-upc-scan text-secondary"></i></span>
                                                                    <input type="text" class="form-control form-control-sm border-start-0 font-monospace box-barcode-input" 
                                                                           data-rider-id="{{ $rider->id }}" placeholder="Scan returned box barcode or type order# to intake...">
                                                                    <button class="btn btn-dark btn-sm fw-bold px-3 btn-scan-intake" type="button" data-rider-id="{{ $rider->id }}">
                                                                        <i class="bi bi-arrow-return-left"></i> Intake Box
                                                                    </button>
                                                                </div>

                                                                <div class="table-responsive rounded-2 border bg-white" style="max-height: 180px; overflow-y: auto;">
                                                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.78rem;">
                                                                        <thead class="bg-light text-secondary text-uppercase fw-bold" style="font-size: 0.65rem;">
                                                                            <tr>
                                                                                <th class="ps-3">Box Order #</th>
                                                                                <th>Customer</th>
                                                                                <th>Reported Reason</th>
                                                                                <th class="text-end pe-3">Shelf Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="undeliveredTbody{{ $rider->id }}">
                                                                            @foreach($rider->undelivered_orders as $uOrder)
                                                                                <tr id="rowBox-{{ $rider->id }}-{{ $uOrder->order_number }}">
                                                                                    <td class="ps-3 font-monospace fw-bold text-danger">#{{ $uOrder->order_number }}</td>
                                                                                    <td>{{ $uOrder->shipping_name }}</td>
                                                                                    <td>
                                                                                        <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.65rem;">
                                                                                            {{ $uOrder->fulfillment?->delivery_issue ?? 'Undelivered' }}
                                                                                        </span>
                                                                                    </td>
                                                                                    <td class="text-end pe-3">
                                                                                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-1 fw-bold btn-receive-box"
                                                                                                data-rider-id="{{ $rider->id }}" data-order-number="{{ $uOrder->order_number }}" style="font-size: 0.72rem;">
                                                                                            <i class="bi bi-box-arrow-in-down me-1"></i> Accept into Hub
                                                                                        </button>
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @else
                                                                <div class="p-2.5 rounded-2 bg-white border text-muted small d-flex align-items-center gap-2">
                                                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                                                    <span class="fw-semibold text-success">All packages delivered! Rider has 0 returned boxes in bag.</span>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <!-- Section B: Cash Handover Audit & Form -->
                                                        @if($rider->pending_cod_count > 0)
                                                            <label class="form-label fw-bold text-dark small mb-1.5">Delivered COD Cash Audit</label>
                                                            <div class="table-responsive rounded-3 border mb-3 bg-white" style="max-height: 180px; overflow-y: auto;">
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
                                                        @endif

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
                                                                <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Cash verified & all returned boxes checked into Hub Shelf B-02.">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer bg-light py-2.5 px-4 border-top">
                                                        <button type="button" class="btn btn-light border bg-white fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success fw-bold px-4">
                                                            <i class="bi bi-check2-circle me-1"></i> Accept Cash & Close Shift
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <button type="button" class="btn btn-sm btn-light border text-muted rounded-pill px-3 py-1 fw-bold" disabled style="font-size: 0.75rem;">
                                        <i class="bi bi-check-all text-success me-1"></i> Shift Cleared
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
                                <div class="fw-bold text-dark">🛵 {{ $sett->rider?->name ?? 'Delivery Partner' }}</div>
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

<!-- Modal: Scan Rider QR or Enter PIN -->
<div class="modal fade" id="scanQrModal" tabindex="-1" aria-labelledby="scanQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-qr-code-scan text-warning fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0" id="scanQrModalLabel">Rider Handover Quick Scan</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Nav Tabs: Camera vs PIN / Barcode Gun -->
                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3" id="scanTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold small py-1.5" id="tab-webcam" data-bs-toggle="pill" data-bs-target="#pane-webcam" type="button" role="tab">
                            <i class="bi bi-camera-video me-1"></i> Laptop Camera
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small py-1.5" id="tab-pin" data-bs-toggle="pill" data-bs-target="#pane-pin" type="button" role="tab">
                            <i class="bi bi-keyboard me-1"></i> 6-Digit PIN / USB Gun
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="scanTabsContent">
                    <!-- Tab 1: Webcam Scanner -->
                    <div class="tab-pane fade show active" id="pane-webcam" role="tabpanel">
                        <div id="qr-reader" class="rounded-3 overflow-hidden border mb-2" style="width: 100%; min-height: 250px; background: #000;"></div>
                        <div class="text-secondary small text-center">
                            Hold the rider's phone in front of your laptop camera.
                        </div>
                    </div>

                    <!-- Tab 2: 6-Digit PIN or USB Gun Input -->
                    <div class="tab-pane fade" id="pane-pin" role="tabpanel">
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <label class="form-label fw-bold text-dark small mb-1">Enter 6-Digit Handover PIN or Scan Barcode</label>
                            <div class="input-group">
                                <input type="text" id="manualPinInput" class="form-control form-control-lg font-monospace fw-bold text-center" placeholder="e.g. 834192" maxlength="50" autofocus>
                                <button class="btn btn-primary fw-bold px-3" type="button" id="btnSubmitPin">
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                            <div class="text-muted small mt-1.5" style="font-size: 0.75rem;">
                                If using a USB handheld scanner, click inside the box and pull the trigger.
                            </div>
                        </div>
                    </div>
                </div>

                <div id="scanStatusAlert" class="alert alert-danger py-2 px-3 small d-none mt-3 mb-0"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let html5QrCode = null;

    const scanQrModal = document.getElementById('scanQrModal');
    const alertBox = document.getElementById('scanStatusAlert');
    const pinInput = document.getElementById('manualPinInput');
    const btnSubmitPin = document.getElementById('btnSubmitPin');

    function showError(msg) {
        alertBox.textContent = msg;
        alertBox.classList.remove('d-none');
    }

    function hideError() {
        alertBox.classList.add('d-none');
    }

    async function processCode(query) {
        hideError();
        try {
            const url = '{{ route("order-manager.settlements.lookup") }}?query=' + encodeURIComponent(query);
            const res = await fetch(url);
            const data = await res.json();

            if (data.success && data.rider) {
                // Stop camera scanner
                if (html5QrCode) {
                    try { await html5QrCode.stop(); } catch(e) {}
                }
                // Close Scan Modal
                const scanModalInst = bootstrap.Modal.getInstance(scanQrModal);
                if (scanModalInst) scanModalInst.hide();

                // Open Target Rider Deposit Modal
                const targetModalEl = document.getElementById('modalDeposit' + data.rider.id);
                if (targetModalEl) {
                    const depositModal = new bootstrap.Modal(targetModalEl);
                    depositModal.show();
                } else {
                    showError('Rider #' + data.rider.id + ' (' + data.rider.name + ') has no pending orders on today\'s screen.');
                }
            } else {
                showError(data.message || 'Rider not found for this scan.');
            }
        } catch (e) {
            showError('Network error checking rider: ' + e.message);
        }
    }

    // Modal shown -> start camera if on webcam tab
    scanQrModal.addEventListener('shown.bs.modal', function () {
        hideError();
        const activeTab = document.querySelector('#scanTabs .active');
        if (activeTab && activeTab.id === 'tab-webcam') {
            startCamera();
        } else if (pinInput) {
            pinInput.focus();
        }
    });

    // Modal hidden -> stop camera
    scanQrModal.addEventListener('hidden.bs.modal', function () {
        if (html5QrCode) {
            try { html5QrCode.stop(); } catch(e) {}
        }
    });

    // Tab change
    document.getElementById('tab-webcam').addEventListener('shown.bs.tab', function () {
        startCamera();
    });
    document.getElementById('tab-pin').addEventListener('shown.bs.tab', function () {
        if (html5QrCode) {
            try { html5QrCode.stop(); } catch(e) {}
        }
        setTimeout(() => pinInput.focus(), 100);
    });

    function startCamera() {
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }
        html5QrCode.start(
            { facingMode: "user" },
            { fps: 10, qrbox: { width: 220, height: 220 } },
            (decodedText) => {
                processCode(decodedText);
            },
            (error) => {}
        ).catch(err => {
            showError('Camera access denied or unavailable: ' + err);
        });
    }

    btnSubmitPin.addEventListener('click', function () {
        const val = pinInput.value.trim();
        if (val) processCode(val);
    });

    pinInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnSubmitPin.click();
        }
    });

    // ----------------------------------------------------
    // Section A: RTO Parcel Handover (Barcode Gun / Button)
    // ----------------------------------------------------
    const csrfToken = '{{ csrf_token() }}';
    const receiveBoxUrl = '{{ route("order-manager.settlements.receive-box") }}';

    // Simple audio feedback for warehouse barcode gun
    const audioContext = window.AudioContext || window.webkitAudioContext ? new (window.AudioContext || window.webkitAudioContext)() : null;
    function playBeep(success = true) {
        if (!audioContext) return;
        try {
            if (audioContext.state === 'suspended') audioContext.resume();
            const osc = audioContext.createOscillator();
            const gain = audioContext.createGain();
            osc.connect(gain);
            gain.connect(audioContext.destination);
            osc.type = 'sine';
            if (success) {
                osc.frequency.setValueAtTime(880, audioContext.currentTime); // A5
                gain.gain.setValueAtTime(0.15, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);
                osc.start();
                osc.stop(audioContext.currentTime + 0.15);
            } else {
                osc.frequency.setValueAtTime(300, audioContext.currentTime);
                gain.gain.setValueAtTime(0.2, audioContext.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                osc.start();
                osc.stop(audioContext.currentTime + 0.3);
            }
        } catch(e) {}
    }

    async function submitReceiveBox(riderId, orderNumber, inputEl = null) {
        const cleanOrder = (orderNumber || '').replace(/^#/, '').trim();
        if (!cleanOrder) return;

        const rowEl = document.getElementById(`rowBox-${riderId}-${cleanOrder}`);
        const btn = rowEl ? rowEl.querySelector('.btn-receive-box') : null;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
        }

        try {
            const resp = await fetch(receiveBoxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ rider_id: riderId, order_number: cleanOrder })
            });

            const res = await resp.json();

            if (res.success) {
                playBeep(true);
                if (rowEl) {
                    const actionCell = rowEl.querySelector('td:last-child');
                    if (actionCell) {
                        actionCell.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2.5 py-1 small fw-bold"><i class="bi bi-check2-circle me-1"></i> In Hub Stock</span>';
                    }
                    rowEl.classList.add('table-success', 'bg-opacity-25');
                }

                // Update remaining counts in modal & table
                const modalEl = document.getElementById(`modalDeposit${riderId}`);
                if (modalEl && typeof res.remaining_count !== 'undefined') {
                    // Update header badge
                    const headerBadges = modalEl.querySelectorAll('.modal-body .badge');
                    headerBadges.forEach(b => {
                        if (b.textContent.includes('Boxes in Bag')) {
                            b.textContent = `${res.remaining_count} Boxes in Bag`;
                            if (res.remaining_count === 0) {
                                b.classList.remove('bg-danger');
                                b.classList.add('bg-success');
                            }
                        }
                    });

                    // Update stat card in modal
                    const statCard = modalEl.querySelector('.text-secondary.small.fw-bold.text-uppercase');
                    if (statCard && statCard.textContent.includes('UNDELIVERED')) {
                        const parent = statCard.parentElement;
                        const countEl = parent.querySelector('.h3');
                        const subtitleEl = parent.querySelector('.text-secondary.small.mt-1');
                        if (countEl) {
                            countEl.textContent = `${res.remaining_count} Parcel(s)`;
                            if (res.remaining_count === 0) {
                                countEl.classList.remove('text-danger');
                                countEl.classList.add('text-success');
                                parent.className = 'p-3 rounded-4 bg-light border h-100';
                            }
                        }
                        if (subtitleEl) {
                            subtitleEl.textContent = res.remaining_count === 0 ? 'Bag physically empty ✓' : 'Scan & accept boxes into hub shelf';
                        }
                    }
                }

                // Update row badge on main table
                const tableBadge = document.querySelector(`.rider-return-badge-${riderId}`);
                if (tableBadge && typeof res.remaining_count !== 'undefined') {
                    if (res.remaining_count === 0) {
                        tableBadge.className = `badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-2.5 py-1 small fw-bold rider-return-badge-${riderId}`;
                        tableBadge.innerHTML = '<i class="bi bi-check2 me-1"></i> Bag Empty';
                    } else {
                        tableBadge.textContent = `${res.remaining_count} Return Box(es)`;
                    }
                }

                if (inputEl) {
                    inputEl.value = '';
                    inputEl.focus();
                }
            } else {
                playBeep(false);
                alert(res.message || 'Could not verify return box.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-box-arrow-in-down me-1"></i> Accept into Hub';
                }
            }
        } catch (e) {
            playBeep(false);
            alert('Network error communicating with Hub server: ' + e.message);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-box-arrow-in-down me-1"></i> Accept into Hub';
            }
        }
    }

    // Bind click on Accept into Hub buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-receive-box');
        if (btn) {
            e.preventDefault();
            const riderId = btn.getAttribute('data-rider-id');
            const orderNumber = btn.getAttribute('data-order-number');
            submitReceiveBox(riderId, orderNumber);
        }

        const scanBtn = e.target.closest('.btn-scan-intake');
        if (scanBtn) {
            e.preventDefault();
            const riderId = scanBtn.getAttribute('data-rider-id');
            const inputEl = scanBtn.closest('.input-group').querySelector('.box-barcode-input');
            if (inputEl) {
                submitReceiveBox(riderId, inputEl.value, inputEl);
            }
        }
    });

    // Barcode input Enter key press
    document.addEventListener('keypress', function(e) {
        if (e.target && e.target.classList.contains('box-barcode-input') && e.key === 'Enter') {
            e.preventDefault();
            const inputEl = e.target;
            const riderId = inputEl.getAttribute('data-rider-id');
            submitReceiveBox(riderId, inputEl.value, inputEl);
        }
    });
});
</script>

@endsection
