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
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) return;

    const toastId = 'toast-' + Date.now();
    const icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';

    const toastHTML = `
        <div id="${toastId}" class="toast border-0 shadow-lg rounded-3 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header border-0 bg-white rounded-top-3 pt-3 pb-2 px-3">
                <i class="bi ${icon} me-2 fs-5"></i>
                <strong class="me-auto text-dark">${type === 'success' ? 'Success' : 'Error'}</strong>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-white rounded-bottom-3 px-3 pb-3 text-secondary">
                ${message}
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastEl = document.getElementById(toastId);
    const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 4000 });
    bsToast.show();

    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}
