// Custom JavaScript for Shopcalm Customer Portal

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Tooltips & Popovers
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))

    // 2. Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]:not([data-bs-toggle])').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                e.preventDefault();
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // 3. Product Quantity Selector Enhancement (for product details page)
    const qtyInput = document.getElementById('qty-input');
    if (qtyInput) {
        // Prevent manual entry of negative numbers or exceeding stock
        qtyInput.addEventListener('change', function() {
            let val = parseInt(this.value);
            const max = parseInt(this.max);
            const min = parseInt(this.min) || 1;

            if (isNaN(val) || val < min) val = min;
            if (max && val > max) val = max;

            this.value = val;
        });
    }

    // 4. Fade in content on load (works with the skeleton loader logic in ui-interactions.js)
    const fadeElements = document.querySelectorAll('.fade-in-on-load');
    fadeElements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        }, index * 100); // Staggered fade in
    });
});

// Helper function to show quick toast without reloading
function showToast(message, type = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '109999';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const isSuccess = type === 'success';
    const isInfo = type === 'info';
    const icon = isSuccess ? 'bi-check-circle-fill text-success' : (isInfo ? 'bi-info-circle-fill text-primary' : 'bi-exclamation-triangle-fill text-danger');
    const titleText = isSuccess ? 'Success' : (isInfo ? 'Notice' : 'Error');

    const toastHTML = `
        <div id="${toastId}" class="toast border-0 shadow-lg rounded-3 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header border-0 bg-white rounded-top-3 pt-3 pb-2 px-3">
                <i class="bi ${icon} me-2 fs-5"></i>
                <strong class="me-auto text-dark">${titleText}</strong>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-white rounded-bottom-3 px-3 pb-3 text-secondary" style="font-size: 0.88rem;">
                ${message}
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastEl = document.getElementById(toastId);
    if (toastEl && window.bootstrap) {
        const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3500 });
        bsToast.show();

        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
}

// Global Wishlist Badge Updater
function updateWishlistBadge(count) {
    const badges = document.querySelectorAll('.wishlist-badge-count');
    badges.forEach(badge => {
        badge.innerText = count;
        if (badge.id === 'wishlist-badge') {
            badge.style.display = (count > 0) ? 'inline-block' : 'none';
        }
        badge.classList.remove('badge-pop-animate');
        void badge.offsetWidth; // force reflow for animation restart
        badge.classList.add('badge-pop-animate');
    });
}

// 5. Global Reload-Free Wishlist Toggle Handler (Home, Shop, PDP, Collections, etc.)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.wishlist-toggle-btn');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    if (btn.disabled) return;

    const productId = btn.getAttribute('data-product-id');
    if (!productId) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrfToken) {
        console.error('CSRF token not found.');
        return;
    }

    // Disable all buttons matching this product while processing
    const matchingBtns = document.querySelectorAll(`.wishlist-toggle-btn[data-product-id="${productId}"]`);
    matchingBtns.forEach(b => b.disabled = true);

    // Subtle press feedback
    btn.style.transform = 'scale(0.88)';
    setTimeout(() => { btn.style.transform = ''; }, 150);

    fetch('/wishlist/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(async res => {
        if (res.status === 401) {
            const data = await res.json().catch(() => ({}));
            showToast(data.message || 'Please sign in to save items to your wishlist.', 'info');
            setTimeout(() => {
                window.location.href = data.redirect || '/login';
            }, 1000);
            return null;
        }
        if (!res.ok) {
            throw new Error('Failed to update wishlist.');
        }
        return res.json();
    })
    .then(data => {
        if (!data) return;

        if (data.success) {
            const isAdded = data.is_in_wishlist;

            // Sync all instances of this product's wishlist button on the page
            matchingBtns.forEach(b => {
                const icon = b.querySelector('i');
                if (icon) {
                    if (isAdded) {
                        icon.className = 'bi bi-heart-fill text-danger';
                        icon.style.animation = 'heartPulse 0.35s ease';
                    } else {
                        icon.className = b.classList.contains('pdp-wishlist-btn') ? 'bi bi-heart' : 'bi bi-heart text-secondary';
                        icon.style.animation = '';
                    }
                }
                b.setAttribute('title', isAdded ? 'Remove from Wishlist' : 'Add to Wishlist');
            });

            // Sync header badge
            if (typeof data.wishlist_count !== 'undefined') {
                updateWishlistBadge(data.wishlist_count);
            }

            // Show friendly notification toast
            showToast(data.message, isAdded ? 'success' : 'info');
        } else {
            showToast(data.message || 'Could not update wishlist.', 'error');
        }
    })
    .catch(err => {
        console.error('Wishlist Toggle Error:', err);
        showToast('Something went wrong. Please try again.', 'error');
    })
    .finally(() => {
        matchingBtns.forEach(b => b.disabled = false);
    });
});

