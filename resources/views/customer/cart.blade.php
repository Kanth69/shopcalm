@extends('layouts.customer')

@section('title', 'Your Shopping Bag - Shopcalm')

@section('content')
<div class="container my-5">
    <div class="d-flex align-items-center gap-3 mb-5">
        <h1 class="fw-bold mb-0">Shopping Bag</h1>
        @if($cart && $cart->items->count() > 0)
            <span class="badge bg-primary rounded-pill px-3" id="cart-page-badge">{{ $cart->items->sum('quantity') }} items</span>
        @endif
    </div>

    @if ($cart && $cart->items->count() > 0)
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="mb-0 fw-bold">Cart Items</h5>
                        <button type="button" class="btn btn-link text-danger text-decoration-none p-0 small fw-bold" onclick="clearCart()">
                            <i class="bi bi-trash3 me-1"></i> Clear Cart
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light small text-uppercase tracking-wider">
                                    <tr>
                                        <th class="ps-4 py-3">Product</th>
                                        <th class="py-3">Quantity</th>
                                        <th class="py-3 text-end">Price</th>
                                        <th class="py-3 text-end pe-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart->items as $item)
                                    <tr id="cart-row-{{ $item->id }}">
                                        <td class="ps-4 py-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <a href="{{ route('product.show', $item->product->slug) }}" class="flex-shrink-0">
                                                    <img src="{{ asset('storage/' . $item->product->main_image) }}" class="rounded-3 border shadow-sm" width="80" height="80" style="object-fit: cover;">
                                                </a>
                                                <div>
                                                    <a href="{{ route('product.show', $item->product->slug) }}" class="text-dark fw-bold text-decoration-none d-block mb-1">{{ $item->product->name }}</a>
                                                    <div class="small text-muted">{{ $item->product->brand->name }}</div>
                                                    <button type="button" class="btn btn-link text-muted p-0 small text-decoration-none hover-danger mt-2" onclick="removeItem({{ $item->id }})">Remove</button>
                                                </div>
                                            </div>
                                        </td>
                                        <td width="160">
                                            <div class="input-group input-group-sm border rounded-3 overflow-hidden" style="width: 120px;">
                                                <button type="button" class="btn btn-light border-0 shadow-none px-2" onclick="updateQty({{ $item->id }}, -1)">-</button>
                                                <input type="number" id="qty-{{ $item->id }}" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->product->stock }}" class="form-control text-center border-0 fw-bold shadow-none bg-white" readonly>
                                                <button type="button" class="btn btn-light border-0 shadow-none px-2" onclick="updateQty({{ $item->id }}, 1)">+</button>
                                            </div>
                                            <form id="update-form-{{ $item->id }}" action="{{ route('cart.update', $item->id) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="quantity" id="hidden-qty-{{ $item->id }}">
                                            </form>
                                        </td>
                                        <td class="text-end small">
                                            @if($item->product->price > $item->unit_price)
                                                <div class="text-muted text-decoration-line-through" style="font-size: 0.75rem;">₹{{ number_format($item->product->price, 2) }}</div>
                                                <div class="fw-bold text-dark">₹{{ number_format($item->unit_price, 2) }}</div>
                                            @else
                                                <div class="text-muted">₹{{ number_format($item->unit_price, 2) }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fw-bolder text-dark" id="item-total-{{ $item->id }}">₹{{ number_format($item->quantity * $item->unit_price, 2) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3">
                    <a href="{{ route('shop') }}" class="btn btn-light rounded-pill px-4 fw-bold">
                        <i class="bi bi-arrow-left me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 sticky-top" style="top: 100px; z-index: 1000;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Bag Summary</h5>

                        <div class="d-flex justify-content-between mb-3 text-secondary">
                            <span>Total MRP</span>
                            <span id="summary-mrp">₹{{ isset($totalMrp) ? number_format($totalMrp, 2) : number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-secondary">
                            <span>Discount on MRP</span>
                            <span class="text-success fw-bold" id="summary-discount">-₹{{ isset($totalDiscount) ? number_format($totalDiscount, 2) : '0.00' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-secondary">
                            <span>Estimated Shipping</span>
                            <span class="text-success fw-bold">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-secondary">
                            <span>Tax (GST)</span>
                            <span>Calculated at checkout</span>
                        </div>

                        @if(isset($offerDiscount) && $offerDiscount > 0)
                        <div class="d-flex justify-content-between mb-3 text-secondary">
                            <span>Extra Offer Discount</span>
                            <span class="text-success fw-bold" id="summary-offer-discount">-₹{{ number_format($offerDiscount, 2) }}</span>
                        </div>
                        @else
                        <div class="d-flex justify-content-between mb-3 text-secondary d-none" id="summary-offer-discount-container">
                            <span>Extra Offer Discount</span>
                            <span class="text-success fw-bold" id="summary-offer-discount">-₹0.00</span>
                        </div>
                        @endif

                        <hr class="opacity-50">

                        <div class="d-flex justify-content-between align-items-center my-4">
                            <span class="h5 fw-bold mb-0">Total Amount</span>
                            <span class="h4 fw-bolder text-primary mb-0" id="summary-total">₹{{ isset($grandTotal) ? number_format($grandTotal, 2) : number_format($subtotal, 2) }}</span>
                        </div>

                        <div class="d-grid gap-3">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg py-3 rounded-3 shadow-sm d-flex align-items-center justify-content-center">
                                Proceed to Checkout <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <i class="bi bi-shield-check fs-5 text-success"></i>
                                <span>Secure encrypted checkout process</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 py-5 text-center fade-in">
            <div class="card-body py-5">
                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 120px; height: 120px;">
                    <i class="bi bi-bag-x display-3 text-muted opacity-50"></i>
                </div>
                <h2 class="fw-bold text-dark">Your bag is empty</h2>
                <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">Looks like you haven't added anything to your shopping bag yet. Start exploring our amazing collections!</p>
                <a href="{{ route('shop') }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow">Start Shopping Now</a>
            </div>
        </div>
    @endif
</div>

<script>
    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotalsAndBadges(data) {
        if (data.cart_count === 0) {
            window.location.reload(); // Reload to show empty cart state
            return;
        }

        const badge = document.getElementById('cart-page-badge');
        if (badge) badge.textContent = data.cart_count + ' items';

        const mrpEl = document.getElementById('summary-mrp');
        if (mrpEl && data.total_mrp !== undefined) mrpEl.textContent = formatCurrency(data.total_mrp);

        const discountEl = document.getElementById('summary-discount');
        if (discountEl && data.total_discount !== undefined) discountEl.textContent = '-' + formatCurrency(data.total_discount);

        const offerDiscountContainer = document.getElementById('summary-offer-discount-container');
        const offerDiscountEl = document.getElementById('summary-offer-discount');
        if (offerDiscountEl && data.offer_discount !== undefined) {
            offerDiscountEl.textContent = '-' + formatCurrency(data.offer_discount);
            if (offerDiscountContainer) {
                if (data.offer_discount > 0) offerDiscountContainer.classList.remove('d-none');
                else offerDiscountContainer.classList.add('d-none');
            }
        }

        const totalEl = document.getElementById('summary-total');
        if (totalEl && data.grand_total !== undefined) {
            totalEl.textContent = formatCurrency(data.grand_total);
        } else if (totalEl) {
            totalEl.textContent = formatCurrency(data.subtotal);
        }
        
        // Also try to update the navbar badge if it exists
        const navBadge = document.getElementById('cart-badge');
        if (navBadge) {
            navBadge.textContent = data.cart_count;
            if (data.cart_count > 0) {
                navBadge.style.display = 'inline-block';
            } else {
                navBadge.style.display = 'none';
            }
        }
    }

    function updateQty(itemId, delta) {
        const input = document.getElementById('qty-' + itemId);
        const max = parseInt(input.max);
        let newVal = parseInt(input.value) + delta;

        if (newVal >= 1 && newVal <= max) {
            input.value = newVal; // Optimistic update
            
            fetch(`/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newVal })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const itemTotalEl = document.getElementById('item-total-' + itemId);
                    if (itemTotalEl && data.item_total !== undefined) {
                        itemTotalEl.textContent = formatCurrency(data.item_total);
                    }
                    updateTotalsAndBadges(data);
                } else {
                    alert(data.message || 'Error updating cart');
                    window.location.reload(); // Revert on error
                }
            })
            .catch(err => {
                console.error(err);
                window.location.reload();
            });
        }
    }

    function removeItem(itemId) {
        const row = document.getElementById('cart-row-' + itemId);
        if (row) row.style.opacity = '0.5';

        fetch(`/cart/remove/${itemId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (row) row.remove();
                updateTotalsAndBadges(data);
            } else {
                alert('Error removing item');
                window.location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            window.location.reload();
        });
    }

    function clearCart() {
        fetch(`{{ route('cart.clear') }}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error clearing cart');
            }
        })
        .catch(err => {
            console.error(err);
            window.location.reload();
        });
    }
</script>

<style>
    .hover-danger:hover { color: var(--bs-danger) !important; }
    .shadow-inner { box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06); }
</style>
@endsection
