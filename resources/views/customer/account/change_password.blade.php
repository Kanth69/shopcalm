@extends('customer.account.layout')

@section('title', 'Change Password')

@section('account_content')

{{-- Hero Banner --}}
<div class="rounded-4 mb-4 p-4 d-flex align-items-center gap-3"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100px;">
    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
         style="width:52px; height:52px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
        <i class="bi bi-shield-lock-fill text-white fs-4"></i>
    </div>
    <div>
        <h5 class="fw-bold text-white mb-0">Change Password</h5>
        <p class="text-white-50 small mb-0">Keep your account safe with a strong password</p>
    </div>
</div>

{{-- Password Form Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4 p-md-5" style="max-width: 540px;">

        {{-- Security tip --}}
        <div class="rounded-3 p-3 mb-4 d-flex align-items-start gap-3" style="background:#f0f9ff; border:1px solid #bae6fd;">
            <i class="bi bi-lightbulb-fill mt-1" style="color:#0284c7; font-size:1rem;"></i>
            <p class="small mb-0" style="color:#0369a1; line-height:1.5;">
                Use at least <strong>8 characters</strong> mixing uppercase, lowercase, numbers, and symbols for the strongest protection.
            </p>
        </div>

        <form method="post" action="{{ route('password.update') }}" id="password-update-form">
            @csrf
            @method('put')

            {{-- Current Password --}}
            <div class="mb-4">
                <label for="current_password" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-lock me-1 text-primary"></i>Current Password
                </label>
                <div class="position-relative">
                    <input id="current_password"
                           name="current_password"
                           type="password"
                           class="form-control rounded-3 pe-5 @error('current_password', 'updatePassword') is-invalid @enderror"
                           placeholder="Enter your current password"
                           autocomplete="current-password"
                           style="height:46px;">
                    <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1 text-muted toggle-pw"
                            data-target="current_password" style="background:none; border:none; z-index:5;">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('current_password', 'updatePassword')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            {{-- New Password --}}
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-key me-1 text-primary"></i>New Password
                </label>
                <div class="position-relative">
                    <input id="password"
                           name="password"
                           type="password"
                           class="form-control rounded-3 pe-5 @error('password', 'updatePassword') is-invalid @enderror"
                           placeholder="Enter a strong new password"
                           autocomplete="new-password"
                           style="height:46px;"
                           oninput="updateStrength(this.value)">
                    <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1 text-muted toggle-pw"
                            data-target="password" style="background:none; border:none; z-index:5;">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                {{-- Strength meter --}}
                <div class="mt-2" id="strength-meter" style="display:none;">
                    <div class="d-flex gap-1 mb-1">
                        <div class="rounded-pill flex-fill" id="s1" style="height:4px; background:#e2e8f0; transition:background 0.3s;"></div>
                        <div class="rounded-pill flex-fill" id="s2" style="height:4px; background:#e2e8f0; transition:background 0.3s;"></div>
                        <div class="rounded-pill flex-fill" id="s3" style="height:4px; background:#e2e8f0; transition:background 0.3s;"></div>
                        <div class="rounded-pill flex-fill" id="s4" style="height:4px; background:#e2e8f0; transition:background 0.3s;"></div>
                    </div>
                    <span id="strength-label" class="small fw-semibold" style="font-size:0.75rem;"></span>
                </div>
                @error('password', 'updatePassword')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-5">
                <label for="password_confirmation" class="form-label fw-semibold text-dark mb-1" style="font-size:0.875rem;">
                    <i class="bi bi-check2-circle me-1 text-primary"></i>Confirm New Password
                </label>
                <div class="position-relative">
                    <input id="password_confirmation"
                           name="password_confirmation"
                           type="password"
                           class="form-control rounded-3 pe-5 @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                           placeholder="Re-enter the new password"
                           autocomplete="new-password"
                           style="height:46px;">
                    <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-1 text-muted toggle-pw"
                            data-target="password_confirmation" style="background:none; border:none; z-index:5;">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                @error('password_confirmation', 'updatePassword')
                    <div class="text-danger mt-1 small">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn fw-bold rounded-pill px-5 py-2"
                    style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff; border: none; font-size:0.9rem; min-width:180px;">
                <i class="bi bi-shield-check me-2"></i>Update Password
            </button>

            @if(session('status') === 'password-updated')
                <div class="mt-3 d-flex align-items-center gap-2 text-success small fw-semibold">
                    <i class="bi bi-check-circle-fill"></i> Password updated successfully.
                </div>
            @endif
        </form>

    </div>
</div>

@push('scripts')
<script>
// Toggle password visibility
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon  = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });
});

// Password strength meter
function updateStrength(val) {
    const meter = document.getElementById('strength-meter');
    if (!val) { meter.style.display = 'none'; return; }
    meter.style.display = 'block';

    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors  = ['#ef4444','#f97316','#eab308','#22c55e'];
    const labels  = ['Weak','Fair','Good','Strong'];
    const labelColors = ['#b91c1c','#c2410c','#a16207','#15803d'];

    for (let i = 1; i <= 4; i++) {
        const seg = document.getElementById('s' + i);
        seg.style.background = i <= score ? colors[score - 1] : '#e2e8f0';
    }
    const lbl = document.getElementById('strength-label');
    lbl.textContent = labels[score - 1] || '';
    lbl.style.color = labelColors[score - 1] || '#94a3b8';
}
</script>
@endpush

@endsection
