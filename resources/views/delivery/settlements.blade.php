@extends('delivery.layouts.app')

@section('title', 'Cash Settlement Vouchers')

@section('content')

<!-- Cash in Hand & Settled Balance Header -->
<div class="card border-0 rounded-4 p-3.5 mb-3 bg-white shadow-xs" style="border: 1px solid #e2e8f0 !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.03em;">Floating Cash in Hand</div>
            <div class="h3 fw-bolder {{ $cashInHandPending > 0 ? 'text-danger' : 'text-success' }} mb-0 font-monospace">
                ₹{{ number_format($cashInHandPending, 2) }}
            </div>
            <div class="text-secondary small mt-0.5" style="font-size: 0.74rem;">
                {{ $cashInHandPending > 0 ? 'Pending deposit at hub counter' : '✓ All collected cash deposited & settled' }}
            </div>
        </div>
        <div class="text-end">
            <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.03em;">Total Settled</div>
            <div class="h5 fw-bolder text-success mb-0 font-monospace">
                ₹{{ number_format($totalDeposited ?? 0, 2) }}
            </div>
        </div>
    </div>

    @if($cashInHandPending > 0)
        <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-between shadow-xs" 
             style="background: #fffbeb; border: 1.5px solid #fde68a;">
            <span class="text-dark small fw-bold" style="font-size: 0.76rem;">
                <i class="bi bi-shop-window text-warning me-1"></i> Visit Hub Counter to deposit
            </span>
            <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">Shift End</span>
        </div>
    @endif
</div>

<!-- Past Deposit Vouchers List -->
<div class="d-flex align-items-center justify-content-between mb-2 px-1">
    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Deposit Receipts Archive</div>
    <span class="badge bg-white text-secondary border rounded-pill px-2.5 py-0.5 small" style="font-size: 0.68rem;">
        {{ $settlements->total() }} Receipts
    </span>
</div>

@forelse($settlements as $sett)
    <div class="card border-0 rounded-4 mb-3 bg-white shadow-xs overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-40">
            <div>
                <span class="fw-bolder text-primary font-monospace" style="font-size: 0.84rem;">{{ $sett->settlement_number }}</span>
                <span class="text-secondary small ms-1" style="font-size: 0.7rem;">&bull; {{ $sett->created_at->format('d M, h:i A') }}</span>
            </div>
            <span class="badge rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                  style="background: #ecfdf5; color: #065f46; border: 1.5px solid #6ee7b7; font-size: 0.72rem;">
                <i class="bi bi-check-circle-fill text-success"></i> Settled
            </span>
        </div>

        <div class="p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="h5 fw-bolder text-dark mb-0 font-monospace" style="font-size: 1.1rem;">
                    ₹{{ number_format($sett->total_amount, 2) }}
                </div>
                <div class="text-secondary small mt-0.5" style="font-size: 0.74rem;">
                    Received by <strong>{{ $sett->receivedBy?->name ?? 'Hub Staff' }}</strong> &bull; {{ $sett->order_count }} order(s)
                </div>
                @if($sett->notes)
                    <div class="text-muted small mt-1 font-italic" style="font-size: 0.7rem;">
                        Note: "{{ $sett->notes }}"
                    </div>
                @endif
            </div>

            <a href="{{ route('order-manager.settlements.show', $sett) }}" target="_blank" 
               class="btn btn-sm btn-light border bg-white rounded-pill px-3 py-1.5 fw-bold text-dark shadow-xs" style="font-size: 0.76rem;">
                <i class="bi bi-file-earmark-text me-1 text-primary"></i> Receipt
            </a>
        </div>
    </div>
@empty
    <div class="card border-0 rounded-4 p-5 bg-white text-center my-3 shadow-xs" style="border: 1px solid #e2e8f0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-secondary mb-2" style="width: 48px; height: 48px; background: #f8fafc; font-size: 1.5rem;">
            <i class="bi bi-receipt"></i>
        </div>
        <h6 class="fw-bolder text-dark mb-1">No Deposit Receipts</h6>
        <p class="text-secondary small mb-0" style="font-size: 0.8rem;">Your past settlement vouchers will appear here once deposited.</p>
    </div>
@endforelse

@if($settlements->hasPages())
    <div class="mt-3">
        {{ $settlements->links() }}
    </div>
@endif

@endsection
