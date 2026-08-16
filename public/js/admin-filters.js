/**
 * Universal AJAX Filter Handler for Admin Pages
 * Transforms all GET form submissions and pagination links into seamless AJAX transitions.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Determine if we are inside the admin panel
    if (!document.querySelector('.main-content')) return;

    function fetchAndReplace(url) {
        const mainContent = document.querySelector('main.main-content');
        if (!mainContent) return;

        // Apply loading state
        mainContent.style.opacity = '0.5';
        mainContent.style.pointerEvents = 'none';
        mainContent.style.transition = 'opacity 0.2s';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMain = doc.querySelector('main.main-content');
            
            if (newMain) {
                mainContent.innerHTML = newMain.innerHTML;
                
                // Remove loading state
                mainContent.style.opacity = '1';
                mainContent.style.pointerEvents = 'auto';
                
                // Immediately reveal any content hidden by skeleton loaders
                const skeletons = mainContent.querySelectorAll('.skeleton-wrapper');
                const contents = mainContent.querySelectorAll('.content-loaded');
                
                skeletons.forEach(s => {
                    s.style.display = 'none';
                    s.classList.add('d-none');
                });
                
                contents.forEach(c => {
                    c.classList.remove('d-none');
                    c.style.opacity = '1';
                });
                
                window.history.pushState({}, '', url);

                // Re-initialize tooltips, sweetalert confirms, etc. if needed
                document.dispatchEvent(new Event('adminFiltersUpdated'));
            } else {
                // Fallback to normal navigation if parsing failed
                window.location.href = url;
            }
        })
        .catch(err => {
            console.error('AJAX Filter Error:', err);
            window.location.href = url;
        });
    }

    // 1. Intercept GET Forms (Filters/Search)
    document.body.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.tagName === 'FORM' && form.method.toUpperCase() === 'GET' && !form.classList.contains('no-ajax')) {
            e.preventDefault();
            
            const url = new URL(form.action || window.location.href);
            const formData = new FormData(form);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            url.search = params.toString();
            fetchAndReplace(url.toString());
        }
    });

    // 1b. Direct Change Interceptor for Selects/Inputs (Bulletproof)
    document.body.addEventListener('change', function(e) {
        if (e.target.tagName === 'SELECT') {
            const form = e.target.closest('form');
            if (form && form.method.toUpperCase() === 'GET' && !form.classList.contains('no-ajax')) {
                // Instantly dispatch a submit event to our AJAX handler
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        }
    });

    // 2. Intercept Pagination Links
    document.body.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination a');
        if (link && link.href) {
            e.preventDefault();
            fetchAndReplace(link.href);
        }
    });

    // 3. Handle Back/Forward Buttons
    window.addEventListener('popstate', function() {
        fetchAndReplace(window.location.href);
    });
});
