document.addEventListener('DOMContentLoaded', function () {
    // 1. Initialize Toasts
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function (toastEl) {
        return new bootstrap.Toast(toastEl, { autohide: true, delay: 5000 });
    });
    toastList.forEach(toast => toast.show());

    // 2. Button Loading State Management (Event Delegation for AJAX compatibility)
    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.tagName === 'FORM' && !form.classList.contains('no-loader')) {
            if (e.defaultPrevented) return;

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.classList.contains('no-loader')) {
                const originalHTML = submitButton.innerHTML;
                const originalWidth = submitButton.offsetWidth;

                submitButton.style.width = originalWidth + 'px';
                submitButton.disabled = true;

                const hasIcon = originalHTML.includes('<i');
                submitButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm ${hasIcon ? 'me-2' : ''}" role="status" aria-hidden="true"></span>
                    <span class="opacity-75">Wait...</span>
                `;

                window.addEventListener('pageshow', function(event) {
                    if (event.persisted) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalHTML;
                        submitButton.style.width = 'auto';
                    }
                }, { once: true });
            }
        }
    });

    // 3. Skeleton Loader Management
    // Hide skeletons and show real content once the page has fully loaded
    window.addEventListener('load', function() {
        const skeletonWrappers = document.querySelectorAll('.skeleton-wrapper');
        const contentContainers = document.querySelectorAll('.content-loaded');

        // Fade out skeletons
        skeletonWrappers.forEach(wrapper => {
            wrapper.style.transition = 'opacity 0.3s ease';
            wrapper.style.opacity = '0';
            setTimeout(() => {
                wrapper.style.display = 'none';
            }, 300);
        });

        // Fade in actual content
        contentContainers.forEach(container => {
            container.classList.remove('d-none');
            // Small delay to ensure CSS transition takes effect after removing d-none
            setTimeout(() => {
                container.style.transition = 'opacity 0.4s ease-in-out';
                container.style.opacity = '1';
            }, 50);
        });
    });
});
