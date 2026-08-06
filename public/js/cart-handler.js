/**
 * AJAX Cart Handler for ShopCalm
 * Supports 100% reload-free Add to Cart and Interactive Quantity Controller (+ / -)
 */

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // 1. Handle "Add to Cart" Form Submissions (Zero Reloads)
    document.body.addEventListener('submit', function (e) {
        if (e.target.classList.contains('ajax-cart-form')) {
            e.preventDefault();
            handleAddToCartSubmit(e.target);
        }
    });

    // 2. Handle Product Card Quantity Controller Buttons (+ / -)
    document.body.addEventListener('click', function (e) {
        const qtyBtn = e.target.closest('.card-qty-btn');
        if (qtyBtn) {
            e.preventDefault();
            handleCardQtyChange(qtyBtn);
        }
    });

    function updateCartBadge(count) {
        const cartBadges = document.querySelectorAll('.header-main .badge.bg-primary, .cart-badge-count');
        cartBadges.forEach(badge => {
            badge.innerText = count;
            badge.classList.remove('d-none');
            badge.style.display = (count > 0) ? 'inline-block' : 'none';
            if (count > 0) {
                badge.style.transform = 'scale(1.4)';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
            }
        });
    }

    function renderQtyControllerHtml(productId, itemId, quantity) {
        return `
            <div class="d-inline-flex align-items-center justify-content-between bg-white border border-2 border-primary rounded-pill p-1 shadow-sm mx-auto" style="width: 110px; height: 36px;">
                <button type="button" class="btn btn-sm btn-light border-0 rounded-circle d-flex align-items-center justify-content-center p-0 text-primary fw-bold card-qty-btn" style="width: 26px; height: 26px; background-color: #e0e7ff;" data-item-id="${itemId}" data-action="decrease" data-product-id="${productId}" data-current-qty="${quantity}">
                    <i class="bi bi-dash-lg" style="font-size: 12px;"></i>
                </button>
                <span class="fw-bolder text-primary fs-6 card-qty-val px-1" id="card-qty-val-${productId}">${quantity}</span>
                <button type="button" class="btn btn-sm btn-primary rounded-circle d-flex align-items-center justify-content-center p-0 text-white fw-bold card-qty-btn" style="width: 26px; height: 26px;" data-item-id="${itemId}" data-action="increase" data-product-id="${productId}" data-current-qty="${quantity}">
                    <i class="bi bi-plus-lg" style="font-size: 12px;"></i>
                </button>
            </div>
            <small class="text-success fw-bold d-block mt-1.5 text-center" style="font-size: 0.68rem; letter-spacing: 0.5px;"><i class="bi bi-check-circle-fill me-1"></i>Added to Bag</small>
        `;
    }

    function renderAddToCartButtonHtml(productId) {
        return `
            <form action="/cart/add" method="POST" class="m-0 ajax-cart-form no-loader">
                <input type="hidden" name="_token" value="${csrfToken}">
                <input type="hidden" name="product_id" value="${productId}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 d-flex align-items-center justify-content-center fw-semibold transition-all hover-elevate no-loader">
                    <i class="bi bi-cart-plus me-2"></i> Add to Cart
                </button>
            </form>
        `;
    }

    function handleAddToCartSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnContent = submitBtn.innerHTML;
        const productId = form.querySelector('input[name="product_id"]')?.value;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Adding...`;

        const formData = new FormData(form);

        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartBadge(data.cart_count);

                if (typeof showToast === 'function') {
                    showToast(data.message || 'Added to bag!', 'success');
                }

                // Swap Add to Cart button with Quantity Controller Pill
                if (productId && data.item_id) {
                    const actionWraps = document.querySelectorAll(`#card-action-wrap-${productId}`);
                    actionWraps.forEach(wrap => {
                        wrap.innerHTML = renderQtyControllerHtml(productId, data.item_id, data.quantity);
                    });
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Could not add product.', 'error');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
            }
        })
        .catch(err => {
            console.error('Cart Add Error:', err);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        });
    }

    function handleCardQtyChange(btn) {
        const itemId = btn.dataset.itemId;
        const action = btn.dataset.action;
        const productId = btn.dataset.productId;
        const currentQty = parseInt(btn.dataset.currentQty || '1');

        if (!itemId || !productId) return;

        let newQty = (action === 'increase') ? currentQty + 1 : currentQty - 1;

        btn.disabled = true;

        if (newQty <= 0) {
            // Delete Item from Cart
            fetch(`/cart/remove/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateCartBadge(data.cart_count);

                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Removed from bag.', 'success');
                    }

                    // Swap back to Add to Cart button
                    const actionWraps = document.querySelectorAll(`#card-action-wrap-${productId}`);
                    actionWraps.forEach(wrap => {
                        wrap.innerHTML = renderAddToCartButtonHtml(productId);
                    });
                }
            })
            .catch(err => console.error('Remove Error:', err));
        } else {
            // Update Quantity in Cart
            fetch(`/cart/update/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ quantity: newQty })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateCartBadge(data.cart_count);

                    // Re-render Quantity Controllers for this product
                    const actionWraps = document.querySelectorAll(`#card-action-wrap-${productId}`);
                    actionWraps.forEach(wrap => {
                        wrap.innerHTML = renderQtyControllerHtml(productId, itemId, data.quantity);
                    });
                } else {
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Could not update quantity.', 'error');
                    }
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error('Update Qty Error:', err);
                btn.disabled = false;
            });
        }
    }
});
