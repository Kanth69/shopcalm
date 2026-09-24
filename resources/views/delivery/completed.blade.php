@extends('delivery.layouts.app')

@section('title', 'Completed Deliveries')

@section('content')

<!-- Header with Total Delivered Counter -->
<div class="d-flex align-items-center justify-content-between mb-3 px-1">
    <div>
        <h6 class="fw-bolder text-dark mb-0" style="font-size: 1rem; letter-spacing: -0.01em;">
            <i class="bi bi-check-circle-fill text-success me-1"></i> Delivery History
        </h6>
        <div class="text-secondary small" style="font-size: 0.72rem;">Archive of your fulfilled customer orders</div>
    </div>
    <span class="badge rounded-pill px-3 py-1.5 fw-bold font-monospace shadow-xs" 
          style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; font-size: 0.75rem;">
        {{ $completedOrders->total() }} Delivered
    </span>
</div>

@forelse($completedOrders as $order)
    <div class="card border-0 rounded-4 mb-3 bg-white shadow-xs overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-40">
            <div class="d-flex align-items-center gap-1.5">
                <span class="fw-bolder text-dark font-monospace" style="font-size: 0.9rem;">#{{ $order->order_number }}</span>
                <span class="badge rounded-pill px-2.5 py-0.5 text-white fw-bold shadow-xs" style="background: #10b981; font-size: 0.68rem;">
                    <i class="bi bi-check-lg"></i> Handover Complete
                </span>
            </div>
            <div class="fw-bolder text-dark font-monospace" style="font-size: 1.05rem;">
                ₹{{ number_format($order->total_amount, 2) }}
            </div>
        </div>

        <div class="p-3">
            <div class="d-flex align-items-start gap-2.5 mb-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-success fw-bold" 
                     style="width: 34px; height: 34px; background: #ecfdf5; font-size: 0.9rem;">
                    <i class="bi bi-person-check-fill"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <h6 class="fw-bolder text-dark mb-0.5" style="font-size: 0.92rem;">{{ $order->shipping_name }}</h6>
                    <div class="text-secondary small" style="line-height: 1.35; font-size: 0.78rem;">
                        <i class="bi bi-geo-alt me-0.5 text-muted"></i>{{ $order->shipping_city }} - {{ $order->shipping_zip }}
                    </div>
                </div>
            </div>

            <!-- High-Contrast Date & Payment Status Badges -->
            <div class="d-flex align-items-center justify-content-between pt-2 border-top text-secondary small" style="font-size: 0.74rem;">
                <div class="text-muted fw-semibold">
                    <i class="bi bi-clock-history me-1 text-primary"></i> {{ $order->delivered_at?->format('d M Y, h:i A') ?? 'Delivered' }}
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    <span class="badge rounded-pill px-2.5 py-1 text-uppercase fw-bold border" 
                          style="background: #f1f5f9; color: #1e293b; font-size: 0.68rem; letter-spacing: 0.03em;">
                        {{ $order->payment_method }}
                    </span>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" 
                          style="background: #10b981; font-size: 0.7rem; letter-spacing: 0.03em;">
                        <i class="bi bi-check-circle-fill me-0.5"></i> PAID
                    </span>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card border-0 rounded-4 p-5 bg-white text-center my-4 shadow-xs" style="border: 1px solid #e2e8f0 !important;">
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-muted mb-3" style="width: 52px; height: 52px; background: #f8fafc; font-size: 1.6rem;">
            <i class="bi bi-clock-history"></i>
        </div>
        <h6 class="fw-bolder text-dark mb-1">No Completed Deliveries Yet</h6>
        <p class="text-secondary small mb-0" style="font-size: 0.8rem;">Once you deliver orders and verify customer OTPs, they will be archived here.</p>
    </div>
@endforelse

@if($completedOrders->hasPages())
    <div class="mt-3">
        {{ $completedOrders->links() }}
    </div>
@endif

@endsection
