<section>
    <div class="border-bottom pb-3 mb-4">
        <h6 class="fw-bold text-dark mb-1 fs-5"><i class="bi bi-person-lines-fill text-primary me-2"></i>Personal Details</h6>
        <p class="text-muted small mb-0">Update your account info, contact details, and category preferences.</p>
    </div>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="row g-3">
            <!-- Name -->
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold text-dark small mb-1">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="bi bi-person"></i>
                    </span>
                    <input id="name" name="name" type="text" class="form-control border-start-0 ps-1" value="{{ old('name', $user->name) }}" required autocomplete="name" placeholder="John Doe" style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.875rem;">
                </div>
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold text-dark small mb-1">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input id="email" name="email" type="email" class="form-control border-start-0 ps-1" value="{{ old('email', $user->email) }}" required autocomplete="username" placeholder="name@example.com" style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.875rem;">
                </div>
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Mobile -->
            <div class="col-md-6">
                <label for="mobile_number" class="form-label fw-semibold text-dark small mb-1">{{ __('Mobile Number') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="bi bi-phone"></i>
                    </span>
                    <input id="mobile_number" name="mobile_number" type="text" class="form-control border-start-0 ps-1" value="{{ old('mobile_number', $user->mobile_number) }}" required placeholder="10-digit mobile number" style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.875rem;">
                </div>
                @error('mobile_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Gender -->
            <div class="col-md-6">
                <label for="gender" class="form-label fw-semibold text-dark small mb-1">{{ __('Gender') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="bi bi-gender-ambiguous"></i>
                    </span>
                    <select id="gender" name="gender" class="form-select border-start-0 ps-1" style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.875rem;">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $user->profile?->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $user->profile?->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $user->profile?->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        <option value="Prefer not to say" {{ old('gender', $user->profile?->gender) === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                    </select>
                </div>
                @error('gender') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- DOB -->
            <div class="col-md-6">
                <label for="date_of_birth" class="form-label fw-semibold text-dark small mb-1">{{ __('Date of Birth') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="bi bi-calendar3"></i>
                    </span>
                    <input id="date_of_birth" name="date_of_birth" type="date" class="form-control border-start-0 ps-1" value="{{ old('date_of_birth', $user->profile?->date_of_birth?->format('Y-m-d')) }}" style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.875rem;">
                </div>
                @error('date_of_birth') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Interests -->
            <div class="col-12 mt-4">
                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-1">
                    <label class="form-label fw-bold text-dark small mb-0 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-heart-fill text-danger"></i>
                        <span>{{ __('Shopping Interests & Categories') }}</span>
                    </label>
                    <span class="text-muted small" style="font-size: 0.75rem;">Select your favorite categories for personalized recommendations</span>
                </div>
                <div class="row g-2">
                    @php $userInterests = old('interests', $user->interests->pluck('id')->toArray()); @endphp
                    @foreach($categories as $category)
                        @php $isCatChecked = in_array($category->id, $userInterests); @endphp
                        <div class="col-md-4 col-6">
                            <input class="btn-check" type="checkbox" name="interests[]" value="{{ $category->id }}" id="cat_{{ $category->id }}" autocomplete="off" {{ $isCatChecked ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary w-100 text-start d-flex align-items-center justify-content-between py-2 px-3 rounded-3" for="cat_{{ $category->id }}" style="font-size:0.83rem; border-color: #cbd5e1;">
                                <span class="text-truncate">{{ $category->name }}</span>
                                <i class="bi bi-check-circle-fill ms-1 text-primary check-icon" style="display: {{ $isCatChecked ? 'inline-block' : 'none' }};"></i>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('interests') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-4 pt-2 border-top">
            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                <i class="bi bi-check-lg me-1"></i> {{ __('Save Profile Changes') }}
            </button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>{{ __('Profile updated successfully!') }}</span>
            @endif
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const interestChecks = document.querySelectorAll('.btn-check[name="interests[]"]');
    interestChecks.forEach(chk => {
        chk.addEventListener('change', function() {
            const label = document.querySelector(`label[for="${this.id}"]`);
            if (label) {
                const icon = label.querySelector('.check-icon');
                if (icon) icon.style.display = this.checked ? 'inline-block' : 'none';
            }
        });
    });
});
</script>
