@extends('customer.account.layout')

@section('title', 'My Profile')

@section('account_content')

    {{-- 1. Top Profile Header Summary Card (Spacious & Clean) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3 mb-md-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
        <div class="card-body p-3 p-sm-4 p-md-5">
            <div class="d-flex align-items-center flex-wrap flex-sm-nowrap" style="gap: 1.5rem !important;">
                <!-- Avatar with Glowing Gradient Ring -->
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bolder shadow flex-shrink-0" 
                    style="width: 64px; height: 64px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.6rem; color: #fff; border: 3px solid rgba(255,255,255,0.25); box-shadow: 0 8px 22px rgba(99,102,241,0.4);">
                    {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                </div>

                <div class="overflow-hidden flex-grow-1">
                    <div class="d-flex align-items-center gap-2 gap-sm-3 flex-wrap mb-2">
                        <h4 class="fw-bolder mb-0 text-white text-truncate" style="letter-spacing: -0.02em; font-size: clamp(1.1rem, 3vw, 1.4rem);">{{ $user->name }}</h4>
                        <span class="badge rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" 
                              style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); font-size: 0.74rem; letter-spacing: 0.03em;">
                            <i class="bi bi-patch-check-fill" style="color: #34d399; font-size: 0.85rem;"></i> Verified Member
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-3 gap-sm-4 flex-wrap pt-0.5" style="font-size: 0.84rem; color: rgba(255, 255, 255, 0.85) !important;">
                        <span class="d-inline-flex align-items-center text-truncate"><i class="bi bi-envelope-fill me-2" style="color: #38bdf8; font-size: 0.9rem;"></i>{{ $user->email ?? 'No email provided' }}</span>
                        <span class="d-inline-flex align-items-center text-truncate"><i class="bi bi-telephone-fill me-2" style="color: #4ade80; font-size: 0.9rem;"></i>{{ $user->mobile_number ?? 'No mobile provided' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Personal Details Form Card --}}
    <div class="card mb-3 mb-md-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3 p-sm-4 p-md-5">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    {{-- 3. Newsletter Subscription Preferences Card --}}
    <div class="card mb-3 mb-md-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 py-md-4 px-3 px-sm-4 px-md-5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="mb-1 fw-bolder text-dark" style="letter-spacing: -0.02em; font-size: clamp(1rem, 2.5vw, 1.25rem);"><i class="bi bi-envelope-heart text-primary me-2"></i>Newsletter & Email Preferences</h5>
                <p class="text-muted small mb-0">Manage your promotional email and newsletter notifications.</p>
            </div>
            <span id="newsletter-status-badge" class="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs" 
                  style="{{ $isSubscribed ? 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;' : 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;' }}; font-size: 0.78rem;">
                <i class="bi {{ $isSubscribed ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}" id="newsletter-badge-icon"></i>
                <span id="newsletter-badge-text">{{ $isSubscribed ? 'Subscribed' : 'Unsubscribed' }}</span>
            </span>
        </div>
        <div class="card-body p-3 p-sm-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div style="max-width: 580px;">
                    <h6 class="fw-bold text-dark mb-1.5 fs-6">Receive Newsletter Deals & Updates</h6>
                    <p class="text-secondary small mb-0" style="line-height: 1.65;">Get exclusive sales, discount coupons, and new product announcements sent directly to <strong>{{ $user->email ?? 'your registered email address' }}</strong>.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    {{-- Status Action Button (Green if Subscribed, Red if Unsubscribed) --}}
                    <button type="button" id="newsletter-action-btn" onclick="toggleNewsletterFromBtn()" 
                            class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs transition-all"
                            style="{{ $isSubscribed ? 'background: #10b981; color: #ffffff; border: none;' : 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;' }}; font-size: 0.82rem;">
                        <i class="bi {{ $isSubscribed ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}" id="newsletter-btn-icon"></i>
                        <span id="newsletter-btn-text">{{ $isSubscribed ? 'Subscribed' : 'Unsubscribed' }}</span>
                    </button>

                    <div class="form-check form-switch fs-3 mb-0 d-flex align-items-center">
                        <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="newsletterToggle" {{ $isSubscribed ? 'checked' : '' }} onchange="toggleProfileNewsletter(this.checked)" style="cursor: pointer; width: 2.7em; height: 1.35em;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. DPDP Act 2023 Personal Data & Account Erasure Card --}}
    <div class="card mb-3 mb-md-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 py-md-4 px-3 px-sm-4 px-md-5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="mb-1 fw-bolder text-dark" style="letter-spacing: -0.02em; font-size: clamp(1rem, 2.5vw, 1.25rem);"><i class="bi bi-shield-check text-success me-2"></i>Data Privacy & Account Erasure (DPDP Act 2023)</h5>
                <p class="text-muted small mb-0">Under Indian Data Privacy Laws, you have full control over your personal data.</p>
            </div>
            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; font-size: 0.78rem;">
                DPDP Protected
            </span>
        </div>
        <div class="card-body p-3 p-sm-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4 mb-4 pb-4 border-bottom">
                <div style="max-width: 600px;">
                    <h6 class="fw-bold text-dark mb-1.5 fs-6">Download Personal Data Archive (DPDP Act Sec 11)</h6>
                    <p class="text-secondary small mb-0" style="line-height: 1.65;">
                        Obtain a structured copy of your stored personal profile, saved addresses, wallet balance, and order history as a `.json` file.
                    </p>
                </div>
                <div class="w-100 w-sm-auto">
                    <a href="{{ route('profile.export') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold text-decoration-none shadow-2xs d-inline-flex align-items-center justify-content-center w-100 w-sm-auto" style="font-size: 0.85rem; gap: 0.4rem;">
                        <i class="bi bi-download"></i> <span>Download My Data</span>
                    </a>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div style="max-width: 600px;">
                    <h6 class="fw-bold text-dark mb-1.5 fs-6">Request Account Deletion & Data Removal</h6>
                    <p class="text-secondary small mb-0" style="line-height: 1.65;">
                        You have the right to request deletion of your profile data. In accordance with DPDP Act 2023 rules and GST Tax Regulations, personal profile data will be erased while order invoices are archived securely for tax compliance.
                    </p>
                </div>
                <div class="w-100 w-sm-auto">
                    <button type="button" onclick="confirmAccountDeletion()" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold shadow-2xs d-inline-flex align-items-center justify-content-center w-100 w-sm-auto" style="font-size: 0.85rem; gap: 0.4rem;">
                        <i class="bi bi-trash3"></i> <span>Delete Account & Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

{{-- ========================================================================= --}}
{{-- 5. BESPOKE MODERN NEWSLETTER CENTER ALERT MODAL (HIGH-END UI)              --}}
{{-- ========================================================================= --}}
<div id="newsletter-center-modal" class="nl-modal-overlay" style="display: none;" onclick="handleNlOverlayClick(event)">
    <div class="nl-modal-box" id="nl-modal-box">
        {{-- Close Cross Mark --}}
        <button type="button" class="nl-modal-close-btn" onclick="closeNewsletterAlert()" aria-label="Close" title="Close">
            <i class="bi bi-x-lg"></i>
        </button>

        {{-- Glowing Icon Container --}}
        <div class="nl-modal-icon-wrap" id="nl-modal-icon-wrap">
            <div class="nl-modal-icon-inner" id="nl-modal-icon-inner">
                <i class="bi bi-check2 text-white" id="nl-modal-icon"></i>
            </div>
        </div>

        {{-- Small Pill Status Tag --}}
        <div class="mb-2">
            <span class="nl-modal-badge" id="nl-modal-badge">
                <i class="bi bi-sparkles me-1" id="nl-modal-badge-icon"></i>
                <span id="nl-modal-badge-text">Promotions Active</span>
            </span>
        </div>

        {{-- Title --}}
        <h4 class="nl-modal-title" id="nl-modal-title">Subscribed Successfully!</h4>

        {{-- Description --}}
        <p class="nl-modal-desc" id="nl-modal-desc">
            You're now subscribed to exclusive sales, discount coupons, and new product announcements.
        </p>

        {{-- Center Button --}}
        <div class="d-flex justify-content-center mt-3 pt-1">
            <button type="button" class="nl-modal-action-btn" id="nl-modal-action-btn" onclick="closeNewsletterAlert()">
                <i class="bi bi-check-circle-fill me-1.5" id="nl-modal-action-btn-icon"></i>
                <span id="nl-modal-action-btn-text">Subscribed</span>
            </button>
        </div>

        {{-- Smooth Auto-Vanish Progress Line --}}
        <div class="nl-modal-progress-track">
            <div class="nl-modal-progress-bar" id="nl-modal-progress-bar"></div>
        </div>
    </div>
</div>

<style>
/* ========================================================================= */
/* ULTRA-LUXURY CUSTOM CENTER MODAL STYLING                                  */
/* ========================================================================= */
.nl-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    opacity: 0;
    transition: opacity 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.nl-modal-overlay.active {
    opacity: 1;
}

.nl-modal-box {
    background: #ffffff;
    width: 100%;
    max-width: 430px;
    border-radius: 28px;
    padding: 2.25rem 2rem 2rem 2rem;
    text-align: center;
    position: relative;
    box-shadow: 0 25px 65px -12px rgba(15, 23, 42, 0.35), 0 0 0 1px rgba(226, 232, 240, 0.9);
    transform: scale(0.9) translateY(15px);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}

.nl-modal-overlay.active .nl-modal-box {
    transform: scale(1) translateY(0);
}

/* Close Button (Cross Mark) */
.nl-modal-close-btn {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    z-index: 10;
}

.nl-modal-close-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    transform: rotate(90deg);
}

/* Ambient Glowing Icon */
.nl-modal-icon-wrap {
    margin: 0 auto 1.25rem auto;
    width: 76px;
    height: 76px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.nl-modal-icon-inner {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.85rem;
    transition: all 0.3s ease;
}

/* 1. GREEN THEME (SUBSCRIBED) */
.nl-theme-green .nl-modal-icon-wrap {
    background: rgba(16, 185, 129, 0.16);
    box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.08);
}
.nl-theme-green .nl-modal-icon-inner {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 8px 22px rgba(16, 185, 129, 0.42);
}
.nl-theme-green .nl-modal-badge {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.nl-theme-green .nl-modal-action-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.38);
}
.nl-theme-green .nl-modal-action-btn:hover {
    box-shadow: 0 8px 26px rgba(16, 185, 129, 0.52);
    transform: translateY(-2px);
}
.nl-theme-green .nl-modal-progress-bar {
    background: linear-gradient(90deg, #10b981, #059669);
}

/* 2. RED THEME (UNSUBSCRIBED) */
.nl-theme-red .nl-modal-icon-wrap {
    background: rgba(239, 68, 68, 0.16);
    box-shadow: 0 0 0 8px rgba(239, 68, 68, 0.08);
}
.nl-theme-red .nl-modal-icon-inner {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 8px 22px rgba(239, 68, 68, 0.38);
}
.nl-theme-red .nl-modal-badge {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}
.nl-theme-red .nl-modal-action-btn {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.38);
}
.nl-theme-red .nl-modal-action-btn:hover {
    box-shadow: 0 8px 26px rgba(239, 68, 68, 0.52);
    transform: translateY(-2px);
}
.nl-theme-red .nl-modal-progress-bar {
    background: linear-gradient(90deg, #ef4444, #dc2626);
}

/* Pill, Title & Subtitle */
.nl-modal-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.95rem;
    border-radius: 50px;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.nl-modal-title {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
    margin-bottom: 0.45rem;
}

.nl-modal-desc {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.55;
    margin: 0 auto;
    max-width: 350px;
}

/* Center Action Button */
.nl-modal-action-btn {
    border: none;
    border-radius: 50px;
    padding: 0.75rem 2.25rem;
    font-size: 0.92rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

/* Bottom Progress Track & Bar */
.nl-modal-progress-track {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #f1f5f9;
}

.nl-modal-progress-bar {
    height: 100%;
    width: 100%;
    transition: width 3.2s linear;
}

/* SweetAlert2 Styling for DPDP & Profile Save */
.swal2-custom-card {
    border-radius: 24px !important;
    padding: 2rem 1.75rem 1.75rem 1.75rem !important;
    box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.28) !important;
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
}

.swal2-border-green {
    border-top: 5px solid #10b981 !important;
}

.swal2-border-red {
    border-top: 5px solid #ef4444 !important;
}

.swal2-custom-close {
    color: #94a3b8 !important;
    font-size: 1.5rem !important;
    width: 36px !important;
    height: 36px !important;
    line-height: 36px !important;
    transition: all 0.2s ease !important;
    border-radius: 50% !important;
    margin: 10px 10px 0 0 !important;
}

.swal2-custom-close:hover {
    color: #0f172a !important;
    background: #f1f5f9 !important;
    transform: rotate(90deg);
}

.swal2-timer-green {
    background: linear-gradient(90deg, #10b981, #059669) !important;
    height: 4px !important;
    border-radius: 0 0 24px 24px !important;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let nlAutoVanishTimer = null;

/**
 * Display the Bespoke Centered Newsletter Modal
 */
function showNewsletterCenterModal(isSubscribed, message) {
    const overlay = document.getElementById('newsletter-center-modal');
    const box = document.getElementById('nl-modal-box');
    const badgeText = document.getElementById('nl-modal-badge-text');
    const badgeIcon = document.getElementById('nl-modal-badge-icon');
    const title = document.getElementById('nl-modal-title');
    const desc = document.getElementById('nl-modal-desc');
    const btnText = document.getElementById('nl-modal-action-btn-text');
    const btnIcon = document.getElementById('nl-modal-action-btn-icon');
    const icon = document.getElementById('nl-modal-icon');
    const progressBar = document.getElementById('nl-modal-progress-bar');

    if (!overlay) return;

    // Reset previous timer
    clearTimeout(nlAutoVanishTimer);

    // Reset progress bar width
    if (progressBar) {
        progressBar.style.transition = 'none';
        progressBar.style.width = '100%';
    }

    if (isSubscribed) {
        // GREEN SUCCESS THEME (Subscribed)
        box.className = 'nl-modal-box nl-theme-green';
        if (icon) icon.className = 'bi bi-check2 text-white';
        if (badgeIcon) badgeIcon.className = 'bi bi-sparkles me-1 text-success';
        if (badgeText) badgeText.textContent = 'Promotions Active';
        if (title) title.textContent = 'Subscribed Successfully!';
        if (desc) desc.textContent = message || 'You have successfully subscribed to receive exclusive offers, promo codes, and product updates.';
        if (btnIcon) btnIcon.className = 'bi bi-check-circle-fill me-1.5';
        if (btnText) btnText.textContent = 'Subscribed';
    } else {
        // RED DANGER THEME (Unsubscribed)
        box.className = 'nl-modal-box nl-theme-red';
        if (icon) icon.className = 'bi bi-bell-slash text-white';
        if (badgeIcon) badgeIcon.className = 'bi bi-x-circle me-1 text-danger';
        if (badgeText) badgeText.textContent = 'Promotions Paused';
        if (title) title.textContent = 'Unsubscribed';
        if (desc) desc.textContent = message || 'You have unsubscribed from our newsletter. Promotional emails have been turned off.';
        if (btnIcon) btnIcon.className = 'bi bi-x-circle-fill me-1.5';
        if (btnText) btnText.textContent = 'Unsubscribed';
    }

    // Display overlay and trigger CSS scale-in
    overlay.style.display = 'flex';
    requestAnimationFrame(() => {
        overlay.classList.add('active');
        if (progressBar) {
            progressBar.style.transition = 'width 3.2s linear';
            progressBar.style.width = '0%';
        }
    });

    // Auto-vanish after 3.2 seconds
    nlAutoVanishTimer = setTimeout(() => {
        closeNewsletterAlert();
    }, 3200);
}

/**
 * Dismiss the Newsletter Modal with Fade-out
 */
function closeNewsletterAlert() {
    const overlay = document.getElementById('newsletter-center-modal');
    if (!overlay) return;

    clearTimeout(nlAutoVanishTimer);
    overlay.classList.remove('active');

    setTimeout(() => {
        overlay.style.display = 'none';
        const progressBar = document.getElementById('nl-modal-progress-bar');
        if (progressBar) {
            progressBar.style.transition = 'none';
            progressBar.style.width = '100%';
        }
    }, 260);
}

/**
 * Handle backdrop click to close
 */
function handleNlOverlayClick(e) {
    if (e.target && e.target.id === 'newsletter-center-modal') {
        closeNewsletterAlert();
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewsletterAlert();
    }
});

/**
 * Update UI state of newsletter badge and button on the card
 */
function updateNewsletterCardUI(isSubscribed) {
    const statusBadge = document.getElementById('newsletter-status-badge');
    const badgeText = document.getElementById('newsletter-badge-text');
    const badgeIcon = document.getElementById('newsletter-badge-icon');
    const actionBtn = document.getElementById('newsletter-action-btn');
    const btnText = document.getElementById('newsletter-btn-text');
    const btnIcon = document.getElementById('newsletter-btn-icon');
    const checkbox = document.getElementById('newsletterToggle');

    if (checkbox) checkbox.checked = isSubscribed;

    if (isSubscribed) {
        if (statusBadge) {
            statusBadge.style.background = '#d1fae5';
            statusBadge.style.color = '#065f46';
            statusBadge.style.borderColor = '#a7f3d0';
        }
        if (badgeText) badgeText.textContent = 'Subscribed';
        if (badgeIcon) badgeIcon.className = 'bi bi-check-circle-fill text-success';

        if (actionBtn) {
            actionBtn.style.background = '#10b981';
            actionBtn.style.color = '#ffffff';
            actionBtn.style.borderColor = 'transparent';
        }
        if (btnText) btnText.textContent = 'Subscribed';
        if (btnIcon) btnIcon.className = 'bi bi-check-circle-fill';
    } else {
        if (statusBadge) {
            statusBadge.style.background = '#fee2e2';
            statusBadge.style.color = '#991b1b';
            statusBadge.style.borderColor = '#fca5a5';
        }
        if (badgeText) badgeText.textContent = 'Unsubscribed';
        if (badgeIcon) badgeIcon.className = 'bi bi-x-circle-fill text-danger';

        if (actionBtn) {
            actionBtn.style.background = '#fee2e2';
            actionBtn.style.color = '#dc2626';
            actionBtn.style.borderColor = '#fca5a5';
        }
        if (btnText) btnText.textContent = 'Unsubscribed';
        if (btnIcon) btnIcon.className = 'bi bi-x-circle-fill';
    }
}

/**
 * Click handler for the newsletter button
 */
function toggleNewsletterFromBtn() {
    const checkbox = document.getElementById('newsletterToggle');
    if (!checkbox || checkbox.disabled) return;
    const newState = !checkbox.checked;
    checkbox.checked = newState;
    toggleProfileNewsletter(newState);
}

/**
 * Main newsletter toggle function: Sends AJAX request & displays custom center modal
 */
function toggleProfileNewsletter(isChecked) {
    const checkbox = document.getElementById('newsletterToggle');
    const actionBtn = document.getElementById('newsletter-action-btn');
    
    // Disable controls while processing
    if (checkbox) checkbox.disabled = true;
    if (actionBtn) actionBtn.disabled = true;

    fetch("{{ route('newsletter.toggle') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({ subscribe: isChecked, is_subscribed: isChecked })
    })
    .then(response => response.json())
    .then(data => {
        if (checkbox) checkbox.disabled = false;
        if (actionBtn) actionBtn.disabled = false;

        if (data.success) {
            updateNewsletterCardUI(isChecked);
            // Show custom center modal with green or red button, cross mark, and auto-vanish
            showNewsletterCenterModal(isChecked, data.message);
        } else {
            // Revert state
            updateNewsletterCardUI(!isChecked);
            Swal.fire({
                position: 'center',
                icon: 'warning',
                iconColor: '#f59e0b',
                title: '<div class="fw-extrabold text-dark mt-1" style="font-size: 1.3rem;">Notice</div>',
                html: `<p class="text-secondary mb-0" style="font-size: 0.92rem;">${data.message || 'Failed to update preferences.'}</p>`,
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Got It',
                buttonsStyling: false,
                customClass: {
                    popup: 'swal2-custom-card',
                    confirmButton: 'btn btn-secondary rounded-pill px-4 py-2 fw-bold',
                    closeButton: 'swal2-custom-close'
                },
                timer: 3500,
                timerProgressBar: true
            });
        }
    })
    .catch(error => {
        if (checkbox) checkbox.disabled = false;
        if (actionBtn) actionBtn.disabled = false;
        updateNewsletterCardUI(!isChecked);
        Swal.fire({
            position: 'center',
            icon: 'error',
            iconColor: '#ef4444',
            title: '<div class="fw-extrabold text-dark mt-1" style="font-size: 1.3rem;">Error</div>',
            html: '<p class="text-secondary mb-0" style="font-size: 0.92rem;">An error occurred while updating newsletter preferences. Please try again.</p>',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            buttonsStyling: false,
            customClass: {
                popup: 'swal2-custom-card swal2-border-red',
                confirmButton: 'btn btn-danger rounded-pill px-4 py-2 fw-bold',
                closeButton: 'swal2-custom-close'
            },
            timer: 3500,
            timerProgressBar: true
        });
    });
}

/**
 * DPDP Account Deletion Confirmation Modal
 */
function confirmAccountDeletion() {
    Swal.fire({
        position: 'center',
        title: '<div class="fw-extrabold text-dark mt-1" style="font-size: 1.35rem;">Delete Account & Erase Personal Data?</div>',
        html: `
            <div class="text-start py-2">
                <p style="font-size: 0.9rem; color: #475569; line-height: 1.55;" class="mb-3">
                    This action will immediately erase your profile, saved addresses, and active login sessions. Past order invoices will be archived securely for GST tax compliance.
                </p>
                <p style="font-size: 0.85rem; font-weight: 700;" class="text-danger mb-2">
                    Type <strong>DELETE</strong> below to confirm:
                </p>
            </div>
        `,
        input: 'text',
        inputPlaceholder: 'DELETE',
        icon: 'warning',
        iconColor: '#f59e0b',
        showCloseButton: true,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Yes, Erase My Data',
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: {
            popup: 'swal2-custom-card',
            confirmButton: 'btn btn-danger rounded-pill px-4 py-2.5 fw-bold shadow-sm me-2',
            cancelButton: 'btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-bold shadow-2xs',
            closeButton: 'swal2-custom-close'
        },
        inputValidator: (value) => {
            if (value !== 'DELETE') {
                return 'You must type DELETE in capital letters to confirm!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                position: 'center',
                title: '<div class="fw-extrabold text-dark" style="font-size: 1.3rem;">Erasing Data...</div>',
                html: '<p class="text-secondary small mb-0">Processing DPDP account deletion request securely...</p>',
                allowOutsideClick: false,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal2-custom-card'
                },
                didOpen: () => Swal.showLoading()
            });

            fetch("{{ route('profile.destroy') }}", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        iconColor: '#10b981',
                        title: '<div class="fw-extrabold text-dark" style="font-size: 1.35rem;">Account Deleted</div>',
                        text: data.message,
                        showCloseButton: true,
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal2-custom-card swal2-border-green',
                            confirmButton: 'btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm',
                            closeButton: 'swal2-custom-close'
                        }
                    }).then(() => {
                        window.location.href = data.redirect || "{{ route('home') }}";
                    });
                } else {
                    Swal.fire({
                        position: 'center',
                        icon: 'error',
                        iconColor: '#ef4444',
                        title: '<div class="fw-extrabold text-dark" style="font-size: 1.35rem;">Deletion Failed</div>',
                        text: data.message || 'Could not complete account deletion.',
                        showCloseButton: true,
                        confirmButtonText: 'Close',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'swal2-custom-card swal2-border-red',
                            confirmButton: 'btn btn-secondary rounded-pill px-4 py-2 fw-bold',
                            closeButton: 'swal2-custom-close'
                        }
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    iconColor: '#ef4444',
                    title: '<div class="fw-extrabold text-dark" style="font-size: 1.35rem;">Error</div>',
                    text: 'An error occurred during account deletion.',
                    showCloseButton: true,
                    confirmButtonText: 'Close',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal2-custom-card swal2-border-red',
                        confirmButton: 'btn btn-secondary rounded-pill px-4 py-2 fw-bold',
                        closeButton: 'swal2-custom-close'
                    }
                });
            });
        }
    });
}

// Show sleek center modal if profile was updated via form submit
@if (session('status') === 'profile-updated')
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        position: 'center',
        icon: 'success',
        iconColor: '#10b981',
        title: '<div class="fw-extrabold text-dark mt-1" style="font-size: 1.35rem; letter-spacing: -0.02em;">Profile Updated!</div>',
        html: '<p class="text-secondary mb-0" style="font-size: 0.92rem;">Your account details and preferences have been updated successfully.</p>',
        showCloseButton: true,
        showConfirmButton: true,
        confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Great!',
        buttonsStyling: false,
        customClass: {
            popup: 'swal2-custom-card swal2-border-green',
            confirmButton: 'btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm',
            closeButton: 'swal2-custom-close',
            timerProgressBar: 'swal2-timer-green'
        },
        timer: 3000,
        timerProgressBar: true
    });
});
@endif
</script>
@endpush

@endsection
