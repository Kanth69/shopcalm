@extends('delivery.layouts.app')

@section('title', 'Cash Settlement Desk')

@section('content')

<!-- ── 1. Page Title ── -->
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div>
        <h4 class="fw-bolder text-dark mb-0.5" style="letter-spacing: -0.02em; font-size: 1.25rem;">
            Cash Desk 💵
        </h4>
        <div class="text-secondary small fw-medium" style="font-size: 0.78rem;">
            Hub COD Settlements & Handover
        </div>
    </div>
    <span class="badge rounded-pill bg-dark text-white px-3 py-1.5 fw-bold" style="font-size: 0.72rem;">
        Bengaluru Fleet Hub #01
    </span>
</div>

<!-- ── 2. Floating Cash in Hand Hero Card ── -->
<div class="card border-0 rounded-4 p-4 mb-3.5 bg-white shadow-xs overflow-hidden position-relative" style="border: 1.5px solid #cbd5e1 !important;">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.04em;">
            Floating Cash in Pocket
        </span>
        <span class="badge rounded-pill px-2.5 py-1 fw-bold {{ $cashInHandPending > 0 ? 'bg-danger bg-opacity-10 text-danger border border-danger' : 'bg-success bg-opacity-10 text-success border border-success' }}" style="font-size: 0.7rem;">
            {{ $cashInHandPending > 0 ? 'Deposit Required' : 'All Settled ✓' }}
        </span>
    </div>

    <!-- Big Cash Amount -->
    <div class="h2 fw-bolder {{ $cashInHandPending > 0 ? 'text-danger' : 'text-success' }} mb-1 font-monospace" style="font-size: 2.2rem; letter-spacing: -0.02em;">
        ₹{{ number_format($cashInHandPending, 2) }}
    </div>

    <div class="text-secondary small mb-3" style="font-size: 0.8rem;">
        {{ $cashInHandPending > 0 ? 'Collected from doorstep COD orders. Deposit with hub cashier at shift end.' : 'You have zero cash liability. All past collections deposited.' }}
    </div>

    <!-- Total Settled Footer -->
    <div class="pt-2.5 border-top d-flex align-items-center justify-content-between text-secondary" style="font-size: 0.78rem;">
        <span>Total Lifetime Settled:</span>
        <span class="fw-bold text-dark font-monospace fs-6">₹{{ number_format($totalDeposited ?? 0, 2) }}</span>
    </div>
</div>

@if($cashInHandPending > 0)
    <!-- Hub Desk Handover Alert -->
    <div class="card border-0 rounded-4 p-3.5 mb-3.5 shadow-xs" style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
        <div class="d-flex align-items-start gap-2.5">
            <i class="bi bi-shop-window text-warning fs-4 flex-shrink-0 mt-0.5"></i>
            <div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">Shift-End Cash Handover</h6>
                <div class="text-secondary small" style="font-size: 0.8rem; line-height: 1.45;">
                    Visit the warehouse cashier desk at <strong>Bengaluru Fleet Hub #01</strong>. Hand over physical currency to receive an official digital voucher.
                </div>
            </div>
        </div>
    </div>
@endif

<!-- ── 3. Deposit Receipts Archive ── -->
<div class="d-flex align-items-center justify-content-between mb-2.5 px-1">
    <div class="fw-bold text-dark" style="font-size: 0.92rem;">Deposit Receipts Archive</div>
    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;">
        {{ $settlements->total() }} Receipts
    </span>
</div>

@forelse($settlements as $sett)
    <div class="card border-0 rounded-4 mb-3 bg-white shadow-xs overflow-hidden" style="border: 1.5px solid #e2e8f0 !important;">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-50">
            <div>
                <span class="fw-bold text-primary font-monospace" style="font-size: 0.88rem;">{{ $sett->settlement_number }}</span>
                <span class="text-secondary small ms-1" style="font-size: 0.74rem;">&bull; {{ $sett->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <span class="badge rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-xs" 
                  style="background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; font-size: 0.72rem;">
                <i class="bi bi-check-circle-fill text-success"></i> Settled
            </span>
        </div>

        <div class="p-3.5 d-flex align-items-center justify-content-between">
            <div>
                <div class="h5 fw-bolder text-dark mb-0 font-monospace fs-5">
                    ₹{{ number_format($sett->total_amount, 2) }}
                </div>
                <div class="text-secondary small mt-0.5" style="font-size: 0.78rem;">
                    Received by <strong>{{ $sett->receivedBy?->name ?? 'Hub Staff' }}</strong> &bull; {{ $sett->order_count }} order(s)
                </div>
                @if($sett->notes)
                    <div class="text-muted small mt-1 font-italic" style="font-size: 0.74rem;">
                        Note: "{{ $sett->notes }}"
                    </div>
                @endif
            </div>

            <a href="{{ route('order-manager.settlements.show', $sett) }}" target="_blank" 
               class="btn btn-sm btn-light border bg-white rounded-pill px-3 py-2 fw-bold text-dark shadow-xs flex-shrink-0" style="font-size: 0.8rem;">
                <i class="bi bi-receipt text-primary me-1"></i> Voucher
            </a>
        </div>
    </div>
@empty
    <div class="card border-0 rounded-4 p-5 bg-white text-center my-3 shadow-xs" style="border: 1.5px solid #e2e8f0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-secondary mb-2" style="width: 52px; height: 52px; background: #f8fafc; font-size: 1.6rem;">
            <i class="bi bi-receipt"></i>
        </div>
        <h6 class="fw-bolder text-dark mb-1">No Deposit Receipts Yet</h6>
        <p class="text-secondary small mb-0" style="font-size: 0.82rem;">Your past cash settlement vouchers will appear here once deposited at the hub desk.</p>
    </div>
@endforelse

@if($settlements->hasPages())
    <div class="mt-3">
        {{ $settlements->links() }}
    </div>
@endif

@endsection
