@extends('customer.account.layout')

@section('title', 'My Profile')

@section('account_content')

    {{-- Top Profile Header Summary Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold shadow flex-shrink-0" 
                    style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); font-size: 1.3rem; color: #fff; border: 3px solid rgba(255,255,255,0.2);">
                    {{ strtoupper(substr($user->name ?? 'C', 0, 1)) }}
                </div>
                <div class="overflow-hidden">
                    <h5 class="fw-bold mb-1 text-white text-truncate">{{ $user->name }}</h5>
                    <div class="d-flex align-items-center gap-3 flex-wrap text-white-50" style="font-size:0.8rem;">
                        <span><i class="bi bi-envelope me-1 text-info"></i>{{ $user->email ?? 'No email provided' }}</span>
                        <span><i class="bi bi-phone me-1 text-success"></i>{{ $user->mobile_number ?? 'No mobile provided' }}</span>
                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-0.5" style="font-size:0.7rem;">Verified Member</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    {{-- Newsletter Subscription Preferences Card --}}
    <div class="card mb-4 border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-envelope-heart text-primary me-2"></i>Newsletter & Email Preferences</h5>
                <p class="text-muted small mb-0">Manage your promotional email and newsletter notifications.</p>
            </div>
            <span id="newsletter-status-badge" class="badge rounded-pill px-3 py-1 fw-bold" style="{{ $isSubscribed ? 'background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;' : 'background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;' }}">
                {{ $isSubscribed ? 'Subscribed' : 'Unsubscribed' }}
            </span>
        </div>
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1">Receive Newsletter Deals & Updates</h6>
                    <p class="text-secondary small mb-0">Get exclusive sales, discount coupons, and new product announcements sent to <strong>{{ $user->email }}</strong>.</p>
                </div>
                <div class="form-check form-switch fs-4 mb-0">
                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="newsletterToggle" {{ $isSubscribed ? 'checked' : '' }} onchange="toggleProfileNewsletter(this)">
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function toggleProfileNewsletter(checkbox) {
    const isChecked = checkbox.checked;
    checkbox.disabled = true;

    fetch("{{ route('newsletter.toggle') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ subscribe: isChecked })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to update newsletter preference.');
        return data;
    })
    .then(data => {
        checkbox.disabled = false;
        
        // Update status badge
        const badge = document.getElementById('newsletter-status-badge');
        if (badge) {
            if (data.subscribed) {
                badge.textContent = 'Subscribed';
                badge.style.background = '#d1fae5';
                badge.style.color = '#065f46';
                badge.style.border = '1px solid #a7f3d0';
            } else {
                badge.textContent = 'Unsubscribed';
                badge.style.background = '#fee2e2';
                badge.style.color = '#991b1b';
                badge.style.border = '1px solid #fca5a5';
            }
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Preference Updated',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500
            });
        }
    })
    .catch(err => {
        checkbox.disabled = false;
        checkbox.checked = !isChecked; // Revert switch state on error
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        } else {
            alert(err.message);
        }
    });
}
</script>
@endpush

@endsection
