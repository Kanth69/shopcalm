@extends('layouts.customer')

@section('title', 'Order Confirmed #' . $order->order_number . ' - ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
@endpush

@section('content')
<div class="container my-3 my-md-5 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center" 
                 style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                
                {{-- Celebration Top Header Strip --}}
                <div class="p-4 p-md-5 position-relative" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #ffffff 100%); border-bottom: 1px solid #d1fae5;">
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" 
                         style="width: 72px; height: 72px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);">
                        <i class="bi bi-check-lg display-5 fw-bold"></i>
                    </div>

                    <span class="badge rounded-pill px-3 py-1 fw-bold text-uppercase d-inline-flex align-items-center gap-1 mb-2" 
                          style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.76rem; letter-spacing: 0.05em;">
                        <i class="bi bi-stars"></i> ORDER PLACED SUCCESSFULLY
                    </span>

                    <h1 class="fw-extrabold text-dark mb-2" style="font-size: clamp(1.4rem, 4vw, 2.1rem); letter-spacing: -0.02em;">
                        Thank You For Your Order!
                    </h1>
                    <p class="text-secondary small mb-0 mx-auto" style="max-width: 460px; font-size: 0.88rem; line-height: 1.5;">
                        We have received your order and sent a confirmation email to <strong>{{ $order->shipping_email }}</strong>.
                    </p>
                </div>

                {{-- Order Meta Summary Box --}}
                <div class="card-body p-3 p-sm-4 p-md-5">
                    <div class="rounded-4 p-3 p-sm-4 mb-4 text-start" style="background: #f8fafc; border: 1.5px solid #e2e8f0;">
                        <div class="row g-3 align-items-center">
                            <div class="col-sm-6">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Order Reference</span>
                                <span class="fw-bolder text-dark font-monospace fs-5">#{{ $order->order_number }}</span>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em;">Total Amount</span>
                                <span class="fw-bolder text-primary font-monospace fs-5">₹{{ number_format($order->total_amount, 2) }}</span>
                            </div>
                            <div class="col-12 border-top pt-2.5 mt-2">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-secondary small" style="font-size: 0.8rem;">
                                    <span><i class="bi bi-credit-card me-1.5 text-primary"></i>Payment: <strong>{{ strtoupper($order->payment_method) }}</strong></span>
                                    <span><i class="bi bi-geo-alt-fill me-1 text-danger"></i>Delivering to: <strong>{{ $order->shipping_city }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action CTA Buttons (Full width on mobile, inline on desktop) --}}
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-center gap-2.5">
                        <a href="{{ route('account.orders.show', $order) }}" 
                           class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-2" 
                           style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; font-size: 0.88rem;">
                            <i class="bi bi-box-seam-fill"></i> <span>View Order Details</span>
                        </a>
                        <a href="{{ route('shop') }}" 
                           class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1.5" 
                           style="border-color: #cbd5e1; font-size: 0.88rem;">
                            <i class="bi bi-bag"></i> <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof confetti === 'function') {
        confetti({
            particleCount: 80,
            spread: 70,
            origin: { y: 0.6 }
        });
    }
});
</script>
@endsection
