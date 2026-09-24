document.addEventListener('DOMContentLoaded', function () {
    const productContainer = document.getElementById('products-container');
    const productGrid = document.getElementById('product-grid-wrapper');
    const paginationContainer = document.getElementById('pagination-container');
    const productCountText = document.getElementById('product-count');
    const sortSelect = document.querySelector('.filter-trigger[name="sort"]');
    const activeFiltersContainer = document.getElementById('active-filter-chips');

    function setButtonsLoading(isLoading) {
        document.querySelectorAll('.filter-form button[type="submit"]').forEach(button => {
            if (isLoading) {
                button.disabled = true;
                button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Wait...`;
            } else {
                button.disabled = false;
                button.innerHTML = `Apply Filters`;
            }
        });
    }

    function handleFilterChange(form) {
        // Auto-close offcanvas drawer if open
        const offcanvasEl = document.getElementById('offcanvasFilters');
        if (offcanvasEl && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
            const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (bsOffcanvas) {
                bsOffcanvas.hide();
            }
        }

        const formData = new FormData(form);
        if (sortSelect) {
            formData.set('sort', sortSelect.value);
        }

        // Clean up empty parameters
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null) {
                params.append(key, value);
            }
        }

        const url = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({ path: url }, '', url);
        fetchProducts(url);
    }

    // --- Core Sync Logic ---
    function syncFormsWithUrl(url) {
        const urlObj = new URL(url, window.location.origin);
        const urlParams = urlObj.searchParams;

        document.querySelectorAll('.filter-form').forEach(form => {
            // 1. Uncheck everything
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
            form.querySelectorAll('input[type="number"]').forEach(inp => inp.value = '');

            // 2. Check inputs based on URL
            urlParams.forEach((value, key) => {
                // Key might be "category[]" or "category"
                const input = form.querySelector(`[name="${key}"][value="${value}"]`) ||
                              form.querySelector(`[name="${key}"]`);

                if (input) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        if (input.value === value) input.checked = true;
                    } else {
                        input.value = value;
                    }
                }
            });

            // 3. Trigger brand cascading for the new state
            updateBrandFilter(form).then(() => {
                // After brands load, re-check any brand checkboxes from URL
                urlParams.getAll('brand[]').forEach(val => {
                    const brandInput = form.querySelector(`input[name="brand[]"][value="${val}"]`);
                    if (brandInput) brandInput.checked = true;
                });
            });
        });
    }

    // --- Event Listeners ---
    document.body.addEventListener('submit', function(e) {
        if (e.target.matches('.filter-form')) {
            e.preventDefault();
            handleFilterChange(e.target);
        }
    });

    document.body.addEventListener('click', function(e) {
        // Clear all filters from SIDEBAR (not chips)
        if (e.target.matches('.clear-filters-btn') || e.target.closest('.clear-filters-btn')) {
            const form = e.target.closest('.filter-form') || document.querySelector('.filter-form');
            if (form) {
                // Collapse all accordion sections
                document.querySelectorAll('.filter-form .collapse.show').forEach(coll => {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                        const bsColl = bootstrap.Collapse.getInstance(coll);
                        if (bsColl) bsColl.hide();
                        else coll.classList.remove('show');
                    } else {
                        coll.classList.remove('show');
                    }
                });
                document.querySelectorAll('.filter-accordion-header').forEach(h => h.setAttribute('aria-expanded', 'false'));

                // Close offcanvas drawer
                const offcanvasEl = document.getElementById('offcanvasFilters');
                if (offcanvasEl && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
                    const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
                    if (bsOffcanvas) bsOffcanvas.hide();
                }

                const defaultUrl = window.location.pathname + (sortSelect ? `?sort=${sortSelect.value}` : '');
                syncFormsWithUrl(defaultUrl);
                window.history.pushState({ path: defaultUrl }, '', defaultUrl);
                fetchProducts(defaultUrl);
            }
        }

        // Active filter chips removal (Individual & Clear All)
        const chipLink = e.target.closest('.chip-link');
        if (chipLink) {
            e.preventDefault();
            const url = chipLink.href;
            syncFormsWithUrl(url);
            window.history.pushState({ path: url }, '', url);
            fetchProducts(url);
        }

        // Pagination links
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;
            if (url) {
                window.history.pushState({ path: url }, '', url);
                fetchProducts(url);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Custom Sort Dropdown Option Selection
        const sortItem = e.target.closest('.sort-dropdown-item');
        if (sortItem) {
            e.preventDefault();
            const sortVal = sortItem.dataset.value;
            const targetSelect = document.getElementById('shopSortSelect') || document.querySelector('.filter-trigger[name="sort"]');
            
            // Update active states
            document.querySelectorAll('.sort-dropdown-item').forEach(el => {
                el.classList.remove('active', 'bg-primary', 'text-white');
                el.classList.add('text-dark');
                const chk = el.querySelector('.bi-check-lg');
                if (chk) chk.remove();
            });
            sortItem.classList.add('active', 'bg-primary', 'text-white');
            sortItem.classList.remove('text-dark');
            if (!sortItem.querySelector('.bi-check-lg')) {
                const chk = document.createElement('i');
                chk.className = 'bi bi-check-lg';
                sortItem.appendChild(chk);
            }

            // Update button label
            const labelMap = {
                'latest': '✨ Latest',
                'price_asc': '💰 Low to High',
                'price_desc': '💎 High to Low',
                'rating_high': '⭐ Top Rated'
            };
            const labelEl = document.getElementById('currentSortText');
            if (labelEl) {
                labelEl.textContent = labelMap[sortVal] || '✨ Latest';
            }

            if (targetSelect) {
                targetSelect.value = sortVal;
                targetSelect.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    });

    document.body.addEventListener('change', function(e) {
        if (e.target.matches('.filter-form input[name="category[]"]')) {
            const form = e.target.closest('.filter-form');
            updateBrandFilter(form);
        }
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const visibleForm = document.querySelector('#leftFilterPanel.show .filter-form') ||
                                document.querySelector('#offcanvasFilters.show .filter-form') ||
                                document.querySelector('.filter-form');
            if (visibleForm) {
                handleFilterChange(visibleForm);
            }
        });
    }

    async function updateBrandFilter(form) {
        const brandListContainer = form.querySelector('.brand-list-container');
        const brandFilterSection = form.querySelector('.brand-filter-section');
        const selectedCategories = Array.from(form.querySelectorAll('input[name="category[]"]:checked')).map(cb => cb.value);

        if (brandFilterSection && brandListContainer) {
            if (selectedCategories.length > 0) {
                brandFilterSection.style.display = 'block';
                const prefixMatch = form.querySelector('input[id^="cat_"]');
                const prefix = prefixMatch ? prefixMatch.id.split('_')[1] : 'ajax';

                const response = await fetch(`/shop/brands-by-category?prefix=${prefix}&category_ids[]=${selectedCategories.join('&category_ids[]=')}`);
                brandListContainer.innerHTML = await response.text();
            } else {
                brandFilterSection.style.display = 'none';
                brandListContainer.innerHTML = '';
            }
        }
    }

    function updateFilterCountBadge(url) {
        try {
            const urlObj = new URL(url, window.location.origin);
            const params = urlObj.searchParams;
            let count = 0;
            count += params.getAll('category[]').length;
            count += params.getAll('category').length;
            count += params.getAll('brand[]').length;
            count += params.getAll('brand').length;
            if (params.get('min_price') || params.get('max_price')) count++;
            if (params.get('only_discounted')) count++;
            if (params.get('featured')) count++;

            const badge = document.getElementById('filter-count-badge');
            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        } catch (e) {
            console.error('Error updating filter badge:', e);
        }
    }

    async function fetchProducts(url) {
        setButtonsLoading(true);
        productContainer.classList.add('is-loading');

        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();

            productGrid.innerHTML = data.product_grid_html;
            paginationContainer.innerHTML = data.pagination_html;
            if (productCountText) {
                productCountText.innerText = data.product_count;
            }
            if (activeFiltersContainer && data.active_filters_html !== undefined) {
                activeFiltersContainer.innerHTML = data.active_filters_html;
            }
            updateFilterCountBadge(url);

        } catch (error) {
            console.error('Error fetching products:', error);
        } finally {
            setButtonsLoading(false);
            productContainer.classList.remove('is-loading');
        }
    }
});
