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
            <span id="newsletter-status-badge" class="badge rounded-pill px-3 py-1.5 fw-bold" style="{{ $isSubscribed ? 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;' : 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;' }}; font-size: 0.78rem;">
                {{ $isSubscribed ? 'Subscribed' : 'Unsubscribed' }}
            </span>
        </div>
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
                <div style="max-width: 580px;">
                    <h6 class="fw-bold text-dark mb-1.5 fs-6">Receive Newsletter Deals & Updates</h6>
                    <p class="text-secondary small mb-0" style="line-height: 1.65;">Get exclusive sales, discount coupons, and new product announcements sent directly to <strong>{{ $user->email ?? 'your registered email address' }}</strong>.</p>
                </div>
                <div class="form-check form-switch fs-3 mb-0">
                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="newsletterToggle" {{ $isSubscribed ? 'checked' : '' }} onchange="toggleProfileNewsletter(this)" style="cursor: pointer;">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleProfileNewsletter(checkbox) {
    const isChecked = checkbox.checked;
    const statusBadge = document.getElementById('newsletter-status-badge');
    
    // Disable switch while processing
    checkbox.disabled = true;

    fetch("{{ route('newsletter.toggle') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({ is_subscribed: isChecked })
    })
    .then(response => response.json())
    .then(data => {
        checkbox.disabled = false;
        if (data.success) {
            if (isChecked) {
                statusBadge.textContent = 'Subscribed';
                statusBadge.style.background = '#d1fae5';
                statusBadge.style.color = '#065f46';
                statusBadge.style.borderColor = '#a7f3d0';
            } else {
                statusBadge.textContent = 'Unsubscribed';
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#991b1b';
                statusBadge.style.borderColor = '#fca5a5';
            }

            Swal.fire({
                icon: 'success',
                title: isChecked ? 'Subscribed!' : 'Unsubscribed!',
                text: data.message,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            checkbox.checked = !isChecked; // Revert
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to update preferences.'
            });
        }
    })
    .catch(error => {
        checkbox.disabled = false;
        checkbox.checked = !isChecked; // Revert
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while updating newsletter preferences.'
        });
    });
}

function confirmAccountDeletion() {
    Swal.fire({
        title: 'Delete Account & Erase Personal Data?',
        html: `<p style="font-size: 0.9rem; color: #475569;" class="mb-3">This action will immediately erase your profile, saved addresses, and active login sessions. Past order invoices will be archived securely for tax compliance.</p>
               <p style="font-size: 0.85rem; font-weight: 600;" class="text-danger mb-2">Type <strong>DELETE</strong> to confirm:</p>`,
        input: 'text',
        inputPlaceholder: 'DELETE',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Erase My Data',
        cancelButtonText: 'Cancel',
        inputValidator: (value) => {
            if (value !== 'DELETE') {
                return 'You must type DELETE in capital letters to confirm!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Erasing Data...',
                text: 'Processing DPDP account deletion request...',
                allowOutsideClick: false,
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
                        icon: 'success',
                        title: 'Account Deleted',
                        text: data.message,
                        confirmButtonColor: '#6366f1'
                    }).then(() => {
                        window.location.href = data.redirect || "{{ route('home') }}";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Deletion Failed',
                        text: data.message || 'Could not complete account deletion.',
                        confirmButtonColor: '#6366f1'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred during account deletion.',
                    confirmButtonColor: '#6366f1'
                });
            });
        }
    });
}
</script>
@endpush

@endsection
