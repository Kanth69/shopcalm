<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<style>
    /* Prevent Any Horizontal Viewport Overflow on Mobile */
    html, body {
        overflow-x: hidden !important;
        max-width: 100vw !important;
    }
    #checkout-page-container {
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    /* 8pt Spacing Grid & Modern Design Foundation */
    :root {
        --sk-primary: #4f46e5;
        --sk-primary-hover: #4338ca;
        --sk-success: #16a34a;
        --sk-border: #e2e8f0;
        --sk-card-bg: #ffffff;
    }

    input:focus::placeholder,
    textarea:focus::placeholder {
        color: transparent !important;
        opacity: 0 !important;
        transition: opacity 0.1s ease, color 0.1s ease;
    }

    /* Modern 3-Step Stepper Bar */
    .checkout-step-badge {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stepper-tab-item {
        transition: transform 0.2s ease;
    }
    .stepper-tab-item:hover {
        transform: translateY(-1px);
    }

    /* Relaxed Card Spacing & Typography (Anti-Clutter) */
    .checkout-card {
        background: #ffffff;
        border: 1px solid var(--sk-border) !important;
        border-radius: 1rem !important; /* 16px */
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        margin-bottom: 1.25rem !important; /* 20px */
    }
    .checkout-card-header {
        background: #ffffff;
        padding: 1rem 1.25rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .checkout-card-body {
        padding: 1.25rem 1.25rem !important; /* 20px */
    }
    @media (min-width: 768px) {
        .checkout-card-header {
            padding: 1.15rem 1.5rem !important;
        }
        .checkout-card-body {
            padding: 1.5rem 1.5rem !important; /* 24px */
        }
    }

    /* 1-Tap Delivery Preference Chips (No Emojis) */
    .delivery-chip {
        border: 1.5px solid #cbd5e1 !important;
        background: #ffffff !important;
        color: #475569 !important;
        border-radius: 50rem !important;
        padding: 0.45rem 0.65rem !important;
        font-size: 0.8rem !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
        transition: all 0.2s ease !important;
        cursor: pointer !important;
        user-select: none !important;
        white-space: nowrap !important;
    }
    .delivery-chip:hover {
        border-color: #94a3b8 !important;
        background: #f8fafc !important;
        color: #0f172a !important;
    }
    .delivery-chip.active {
        background: var(--sk-primary) !important;
        border-color: var(--sk-primary) !important;
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25) !important;
    }
    .delivery-chip.active i {
        color: #ffffff !important;
    }

    /* Payment Method Tiles (Compact, Clean & Responsive) */
    .payment-tile {
        border: 1.5px solid var(--sk-border);
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    @media (min-width: 768px) {
        .payment-tile {
            padding: 1rem 1.25rem;
        }
    }
    .payment-tile:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .payment-tile.active-tile {
        border-color: var(--sk-primary) !important;
        background: #faf5ff !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.15);
    }

    /* Sticky Right Sidebar on Desktop */
    @media (min-width: 992px) {
        .checkout-sticky-sidebar {
            position: sticky;
            top: 85px;
            z-index: 10;
        }
    }

    /* Mobile Sticky Bottom Action Bar */
    @media (max-width: 991.98px) {
        #mobile-checkout-sticky-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.08);
            padding: 0.85rem 1.25rem;
        }
        #checkout-page-container {
            padding-bottom: 100px !important;
        }
    }

    /* Map & Modals Helper Styles */
    #delivery-map-container {
        min-height: 290px !important;
        height: 290px !important;
        width: 100% !important;
        background-color: #e2e8f0;
    }
    .leaflet-container {
        font-family: inherit;
    }
    .checkout-addr-type-group {
        display: flex !important;
        width: 100% !important;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 50rem;
        border: 1.5px solid #cbd5e1;
        gap: 4px;
    }
    .checkout-addr-type-group .btn {
        flex: 1 1 0% !important;
        border-radius: 50rem !important;
        border: none !important;
        background: transparent;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        padding: 0.45rem 0.35rem !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .checkout-addr-type-group .btn-check:checked + .btn {
        background: #4f46e5 !important;
        color: #ffffff !important;
    }
</style>
