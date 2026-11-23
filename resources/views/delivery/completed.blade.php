@extends('delivery.layouts.app')

@section('title', 'Completed Deliveries')

@section('content')

<!-- ── 1. Page Title & Counter ── -->
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div>
        <h4 class="fw-bolder text-dark mb-0.5" style="letter-spacing: -0.02em; font-size: 1.25rem;">
            Delivery History ✅
        </h4>
        <div class="text-secondary small fw-medium" style="font-size: 0.78rem;">
            Fulfilled orders archive
        </div>
    </div>
    <span class="badge rounded-pill px-3 py-1.5 fw-bold font-monospace shadow-xs" 
          style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; font-size: 0.76rem;">
        {{ $completedOrders->total() }} Delivered
    </span>
</div>

<!-- ── 2. Completed Orders List ── -->
@forelse($completedOrders as $order)
    <div class="card border-0 rounded-4 mb-3.5 bg-white shadow-xs overflow-hidden" style="border: 1.5px solid #e2e8f0 !important;">
        <!-- Top Bar: Order ID & Delivered Tag -->
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-50">
            <div class="d-flex align-items-center gap-1.5 font-monospace fw-bold text-dark fs-6">
                <span>#{{ $order->order_number }}</span>
                <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: #10b981; font-size: 0.68rem;">
                    <i class="bi bi-check-lg"></i> Handover Verified
                </span>
            </div>
            <div class="fw-bolder text-dark font-monospace fs-5">
                ₹{{ number_format($order->total_amount, 2) }}
            </div>
        </div>

        <div class="p-3.5">
            <!-- Customer Details -->
            <div class="d-flex align-items-start gap-2.5 mb-2.5">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-success fw-bold" 
                     style="width: 38px; height: 38px; background: #ecfdf5; font-size: 0.95rem;">
                    <i class="bi bi-person-check-fill fs-5"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <h5 class="fw-bolder text-dark mb-0.5 fs-6">{{ $order->shipping_name }}</h5>
                    <div class="text-secondary small" style="line-height: 1.4; font-size: 0.8rem;">
                        <i class="bi bi-geo-alt me-0.5 text-danger"></i>{{ $order->shipping_address }}, {{ $order->shipping_city }} - <strong>{{ $order->shipping_zip }}</strong>
                    </div>
                </div>
            </div>

            <!-- Delivered Timestamp & Payment Status -->
            <div class="d-flex align-items-center justify-content-between pt-2.5 border-top text-secondary small" style="font-size: 0.78rem;">
                <div class="text-muted fw-semibold d-flex align-items-center gap-1">
                    <i class="bi bi-clock-history text-primary"></i> 
                    <span>{{ $order->delivered_at?->format('d M Y, h:i A') ?? 'Delivered' }}</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    <span class="badge rounded-pill px-2.5 py-1 text-uppercase fw-bold border" 
                          style="background: #f1f5f9; color: #1e293b; font-size: 0.7rem;">
                        {{ $order->payment_method }}
                    </span>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" 
                          style="background: #10b981; font-size: 0.72rem;">
                        ✓ PAID
                    </span>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card border-0 rounded-4 p-5 bg-white text-center my-4 shadow-xs" style="border: 1.5px solid #e2e8f0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-muted mb-3" style="width: 52px; height: 52px; background: #f8fafc; font-size: 1.6rem;">
            <i class="bi bi-clock-history"></i>
        </div>
        <h5 class="fw-bolder text-dark mb-1 fs-5">No Completed Deliveries Yet</h5>
        <p class="text-secondary small mb-0" style="font-size: 0.82rem;">Once you deliver orders to customers, they will be archived here.</p>
    </div>
@endforelse

@if($completedOrders->hasPages())
    <div class="mt-3">
        {{ $completedOrders->links() }}
    </div>
@endif

@endsection
