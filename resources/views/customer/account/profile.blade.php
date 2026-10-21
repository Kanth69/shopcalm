@extends('customer.account.layout')

@section('title', 'My Profile')

@section('account_content')

    {{-- 1. Top Profile Header Summary Card (Spacious & Clean) --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center flex-wrap flex-sm-nowrap" style="gap: 1.75rem !important;">
                <!-- Avatar with Glowing Gradient Ring -->
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bolder shadow flex-shrink-0 me-2" 
                    style="width: 70px; height: 70px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.7rem; color: #fff; border: 3px solid rgba(255,255,255,0.25); box-shadow: 0 8px 22px rgba(99,102,241,0.4);">
                    {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                </div>

                <div class="overflow-hidden flex-grow-1">
                    <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                        <h4 class="fw-bolder mb-0 text-white text-truncate" style="letter-spacing: -0.02em;">{{ $user->name }}</h4>
                        <span class="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs" 
                              style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4); font-size: 0.76rem; letter-spacing: 0.03em;">
                            <i class="bi bi-patch-check-fill" style="color: #34d399; font-size: 0.85rem;"></i> Verified Member
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-4 flex-wrap pt-0.5" style="font-size: 0.88rem; color: rgba(255, 255, 255, 0.85) !important;">
                        <span class="d-inline-flex align-items-center"><i class="bi bi-envelope-fill me-2" style="color: #38bdf8; font-size: 0.95rem;"></i>{{ $user->email ?? 'No email provided' }}</span>
                        <span class="d-inline-flex align-items-center"><i class="bi bi-telephone-fill me-2" style="color: #4ade80; font-size: 0.95rem;"></i>{{ $user->mobile_number ?? 'No mobile provided' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Personal Details Form Card --}}
    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4 p-md-5">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    {{-- 3. Newsletter Subscription Preferences Card --}}
    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-4 px-4 px-md-5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="mb-1 fw-bolder text-dark" style="letter-spacing: -0.02em;"><i class="bi bi-envelope-heart text-primary me-2"></i>Newsletter & Email Preferences</h5>
                <p class="text-muted small mb-0">Manage your promotional email and newsletter notifications.</p>
            </div>
            <span id="newsletter-status-badge" class="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs" 
                  style="{{ $isSubscribed ? 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;' : 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;' }}; font-size: 0.78rem;">
                <i class="bi {{ $isSubscribed ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }}" id="newsletter-badge-icon"></i>
                <span id="newsletter-badge-text">{{ $isSubscribed ? 'Subscribed' : 'Unsubscribed' }}</span>
            </span>
        </div>
        <div class="card-body p-4 p-md-5">
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
    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-4 px-4 px-md-5 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h5 class="mb-1 fw-bolder text-dark" style="letter-spacing: -0.02em;"><i class="bi bi-shield-check text-success me-2"></i>Data Privacy & Account Erasure (DPDP Act 2023)</h5>
                <p class="text-muted small mb-0">Under Indian Data Privacy Laws, you have full control over your personal data.</p>
            </div>
            <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background:#e0e7ff; color:#3730a3; border:1px solid #c7d2fe; font-size: 0.78rem;">
                DPDP Protected
            </span>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4 mb-4 pb-4 border-bottom">
                <div style="max-width: 600px;">
                    <h6 class="fw-bold text-dark mb-1.5 fs-6">Download Personal Data Archive (DPDP Act Sec 11)</h6>
                    <p class="text-secondary small mb-0" style="line-height: 1.65;">
                        Obtain a structured copy of your stored personal profile, saved addresses, wallet balance, and order history as a `.json` file.
                    </p>
                </div>
                <div>
                    <a href="{{ route('profile.export') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold text-decoration-none shadow-2xs d-inline-flex align-items-center" style="font-size: 0.85rem; gap: 0.4rem;">
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
                <div>
                    <button type="button" onclick="confirmAccountDeletion()" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold shadow-2xs d-inline-flex align-items-center" style="font-size: 0.85rem; gap: 0.4rem;">
                        <i class="bi bi-trash3"></i> <span>Delete Account & Data</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<style>
/* Modern, Premium SweetAlert2 Popup Styles */
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

.swal2-timer-red {
    background: linear-gradient(90deg, #ef4444, #dc2626) !important;
    height: 4px !important;
    border-radius: 0 0 24px 24px !important;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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
 * Main newsletter toggle function: Sends AJAX request & displays center alert popup
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

            if (isChecked) {
                // 1. SUBSCRIBED: Green Success Button at Center with Cross Mark & Auto-Vanish
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    iconColor: '#10b981',
                    title: '<div class="fw-extrabold text-dark mt-1" style="font-size: 1.35rem; letter-spacing: -0.02em;">Subscribed Successfully!</div>',
                    html: `
                        <div class="py-2">
                            <p class="text-secondary mb-3" style="font-size: 0.92rem; line-height: 1.55;">
                                ${data.message || 'You have successfully subscribed to our newsletter!'}
                            </p>
                            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-2xs" style="background: rgba(16, 185, 129, 0.12); color: #047857; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.28);">
                                <i class="bi bi-patch-check-fill text-success"></i> Promotional emails & discount alerts active
                            </div>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: true,
                    confirmButtonText: '<i class="bi bi-check-circle-fill me-1.5"></i> Subscribed',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal2-custom-card swal2-border-green',
                        confirmButton: 'btn btn-success rounded-pill px-4 py-2.5 fw-bold shadow-sm',
                        closeButton: 'swal2-custom-close',
                        timerProgressBar: 'swal2-timer-green'
                    },
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                // 2. UNSUBSCRIBED: Red Button at Center with Cross Mark & Auto-Vanish
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    iconColor: '#ef4444',
                    title: '<div class="fw-extrabold text-dark mt-1" style="font-size: 1.35rem; letter-spacing: -0.02em;">Unsubscribed</div>',
                    html: `
                        <div class="py-2">
                            <p class="text-secondary mb-3" style="font-size: 0.92rem; line-height: 1.55;">
                                ${data.message || 'You have unsubscribed from our newsletter.'}
                            </p>
                            <div class="d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-pill shadow-2xs" style="background: rgba(239, 68, 68, 0.1); color: #b91c1c; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(239, 68, 68, 0.28);">
                                <i class="bi bi-bell-slash-fill text-danger"></i> Promotional emails turned off
                            </div>
                        </div>
                    `,
                    showCloseButton: true,
                    showConfirmButton: true,
                    confirmButtonText: '<i class="bi bi-x-circle-fill me-1.5"></i> Unsubscribed',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal2-custom-card swal2-border-red',
                        confirmButton: 'btn btn-danger rounded-pill px-4 py-2.5 fw-bold shadow-sm',
                        closeButton: 'swal2-custom-close',
                        timerProgressBar: 'swal2-timer-red'
                    },
                    timer: 3000,
                    timerProgressBar: true
                });
            }
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
