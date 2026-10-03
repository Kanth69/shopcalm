@extends('layouts.customer')

@section('title', 'Payment Incomplete — #' . $order->order_number)

@section('content')
<div class="container py-4 py-md-5" style="max-width: 720px;">

    {{-- ⚠️ Status Banner --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1px solid #fed7aa !important;">
        <div class="p-4 text-center" style="background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-xs" 
                 style="width: 68px; height: 68px; background: #ea580c; color: #ffffff; font-size: 2rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <h4 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.02em;">Payment Incomplete</h4>
            <p class="text-muted small mb-2" style="font-size: 0.88rem; max-width: 480px; margin: 0 auto;">
                We could not verify your online payment for order <strong class="text-dark font-monospace">#{{ $order->order_number }}</strong>. 
                Don't worry, your ordered items are safely reserved.
            </p>
            @php $lastPayment = $order->payments()->latest()->first(); @endphp
            @if($lastPayment && !empty($lastPayment->gateway_message) && !str_contains(strtolower($lastPayment->gateway_message), 'initiated'))
            <div class="mb-2 p-2 px-3 rounded-3 d-inline-flex align-items-center gap-1.5 bg-danger bg-opacity-10 border border-danger border-opacity-25 text-danger" style="font-size: 0.78rem;">
                <i class="bi bi-info-circle-fill"></i>
                <span class="fw-semibold">Reason: {{ $lastPayment->gateway_message }}</span>
            </div>
            <br>
            @endif
            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning border-opacity-50 rounded-pill px-3 py-1 fw-bold font-monospace" style="font-size: 0.76rem;">
                Amount Due: ₹{{ number_format($order->total_amount, 2) }}
            </span>
        </div>

        {{-- Recovery Action Buttons --}}
        <div class="card-body p-3 p-md-4 border-top" style="background: #ffffff;">
            <div class="d-flex flex-column gap-2.5">
                
                {{-- Action 1: Retry Online Payment --}}
                <button type="button" id="btn-retry-payment" class="btn btn-primary btn-lg rounded-pill py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm"
                        style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none; font-size: 0.95rem;">
                    <i class="bi bi-arrow-repeat fs-5"></i>
                    <span>Retry Online Payment (UPI / Cards)</span>
                </button>

                {{-- Action 2: Switch to Cash on Delivery --}}
                @if(isset($isCodAvailable) && !$isCodAvailable)
                    <div class="alert alert-warning py-2 px-3 small rounded-3 mb-0 text-center border-0" style="font-size: 0.8rem; background: #fffbe6; color: #856404;">
                        <i class="bi bi-info-circle me-1"></i> Cash on Delivery (COD) is not available for your PIN code. Please retry online payment.
                    </div>
                @else
                    <form action="{{ route('checkout.switch_to_cod', $order) }}" method="POST" class="w-100">
                        @csrf
                        <button type="submit" class="btn btn-outline-dark btn-lg rounded-pill py-3 fw-bold w-100 d-flex align-items-center justify-content-center gap-2"
                                style="font-size: 0.95rem; border-color: #cbd5e1;">
                            <i class="bi bi-cash-stack fs-5 text-success"></i>
                            <span>Switch to Cash on Delivery (COD) @if(isset($resolvedCodFee) && $resolvedCodFee > 0)<span class="text-muted fw-normal">(+ ₹{{ number_format($resolvedCodFee, 2) }} Fee)</span>@endif</span>
                        </button>
                    </form>
                @endif

                <div class="text-center mt-2">
                    <a href="{{ route('account.orders.show', $order) }}" class="text-decoration-none text-muted small fw-semibold" style="font-size: 0.82rem;">
                        <i class="bi bi-box-seam me-1"></i> View in My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Summary Details --}}
    <div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">
                <i class="bi bi-bag-check me-1.5 text-primary"></i> Order Items ({{ $order->items->count() }})
            </h6>
            <span class="text-muted small font-monospace">#{{ $order->order_number }}</span>
        </div>
        <div class="card-body p-0">
            <div class="d-flex flex-column">
                @foreach($order->items as $item)
                @php $product = $item->product; @endphp
                <div class="p-3 border-bottom d-flex align-items-center gap-3" style="border-color: #f1f5f9 !important;">
                    <div class="flex-shrink-0">
                        @if($product && $product->main_image)
                            <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $item->product_name }}" 
                                 class="rounded-3 border p-0.5 bg-white" width="48" height="48" style="object-fit: contain;">
                        @else
                            <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                <i class="bi bi-image opacity-25"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark text-truncate" style="font-size: 0.86rem;">{{ $item->product_name }}</div>
                        <div class="text-muted small" style="font-size: 0.74rem;">Qty: {{ $item->quantity }} × ₹{{ number_format($item->unit_price, 2) }}</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="fw-bolder text-dark font-monospace" style="font-size: 0.92rem;">₹{{ number_format($item->total_price, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="p-3 bg-light d-flex justify-content-between align-items-center">
                <span class="text-muted fw-semibold small">Grand Total</span>
                <span class="fw-bolder text-dark fs-5 font-monospace">₹{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

</div>

{{-- Razorpay SDK Loader --}}
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnRetry = document.getElementById('btn-retry-payment');
    if (!btnRetry) return;

    btnRetry.addEventListener('click', async function() {
        btnRetry.disabled = true;
        btnRetry.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Initializing Gateway...';

        try {
            const response = await fetch("{{ route('checkout.retry_payment', $order) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            });

            const data = await response.json();

            if (data.success && data.razorpay_order_id) {
                const options = {
                    key: data.key_id,
                    amount: data.amount,
                    currency: data.currency || "INR",
                    name: data.name || "ShopCalm",
                    description: data.description || "Order #" + (data.order_number || ""),
                    order_id: data.razorpay_order_id,
                    prefill: data.prefill || {},
                    theme: { color: "#4f46e5" },
                    handler: function (res) {
                        window.location.href = data.callback_url + "?razorpay_payment_id=" + res.razorpay_payment_id + "&razorpay_order_id=" + res.razorpay_order_id + "&razorpay_signature=" + res.razorpay_signature;
                    },
                    modal: {
                        ondismiss: function () {
                            btnRetry.disabled = false;
                            btnRetry.innerHTML = '<i class="bi bi-arrow-repeat fs-5"></i><span>Retry Online Payment</span>';
                        }
                    }
                };
                const rzp = new Razorpay(options);
                rzp.open();
            } else {
                alert(data.message || "Failed to initialize payment retry.");
                btnRetry.disabled = false;
                btnRetry.innerHTML = '<i class="bi bi-arrow-repeat fs-5"></i><span>Retry Online Payment</span>';
            }
        } catch (err) {
            console.error("Retry payment error:", err);
            alert("Error communicating with payment gateway. Please try again.");
            btnRetry.disabled = false;
            btnRetry.innerHTML = '<i class="bi bi-arrow-repeat fs-5"></i><span>Retry Online Payment</span>';
        }
    });
});
</script>
@endsection
