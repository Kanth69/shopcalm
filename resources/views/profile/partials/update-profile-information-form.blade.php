<section>
    <div class="border-bottom pb-3 mb-4">
        <h5 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.02em;">
            <i class="bi bi-person-lines-fill text-primary me-2"></i>Personal Details
        </h5>
        <p class="text-muted small mb-0">Update your account information, contact details, and category shopping preferences.</p>
    </div>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="row g-4">
            <!-- Name -->
            <div class="col-md-6">
                <label for="name" class="form-label fw-bold text-dark small mb-2">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">
                        <i class="bi bi-person fs-6"></i>
                    </span>
                    <input id="name" name="name" type="text" class="form-control border-start-0 py-2.5 ps-1" value="{{ old('name', $user->name) }}" required autocomplete="name" placeholder="John Doe" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; font-size: 0.92rem; color: #0f172a;">
                </div>
                @error('name') <div class="text-danger small mt-1.5">{{ $message }}</div> @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6">
                <label for="email" class="form-label fw-bold text-dark small mb-2">{{ __('Email Address') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">
                        <i class="bi bi-envelope fs-6"></i>
                    </span>
                    <input id="email" name="email" type="email" class="form-control border-start-0 py-2.5 ps-1" value="{{ old('email', $user->email) }}" autocomplete="username" placeholder="name@example.com (Optional)" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; font-size: 0.92rem; color: #0f172a;">
                </div>
                @error('email') <div class="text-danger small mt-1.5">{{ $message }}</div> @enderror
            </div>

            <!-- Mobile -->
            <div class="col-md-6">
                <label for="mobile_number" class="form-label fw-bold text-dark small mb-2">{{ __('Mobile Number') }} <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">
                        <i class="bi bi-telephone fs-6"></i>
                    </span>
                    <input id="mobile_number" name="mobile_number" type="text" class="form-control border-start-0 py-2.5 ps-1" value="{{ old('mobile_number', $user->mobile_number) }}" required placeholder="10-digit mobile number" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; font-size: 0.92rem; color: #0f172a;">
                </div>
                @error('mobile_number') <div class="text-danger small mt-1.5">{{ $message }}</div> @enderror
            </div>

            <!-- Gender -->
            <div class="col-md-6">
                <label for="gender" class="form-label fw-bold text-dark small mb-2">{{ __('Gender') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">
                        <i class="bi bi-gender-ambiguous fs-6"></i>
                    </span>
                    <select id="gender" name="gender" class="form-select border-start-0 py-2.5 ps-1" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; font-size: 0.92rem; color: #0f172a;">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $user->profile?->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $user->profile?->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $user->profile?->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                        <option value="Prefer not to say" {{ old('gender', $user->profile?->gender) === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                    </select>
                </div>
                @error('gender') <div class="text-danger small mt-1.5">{{ $message }}</div> @enderror
            </div>

            <!-- DOB -->
            <div class="col-md-6">
                <label for="date_of_birth" class="form-label fw-bold text-dark small mb-2">{{ __('Date of Birth') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted px-3" style="border-radius: 12px 0 0 12px; border-color: #cbd5e1;">
                        <i class="bi bi-calendar3 fs-6"></i>
                    </span>
                    <input id="date_of_birth" name="date_of_birth" type="date" class="form-control border-start-0 py-2.5 ps-1" value="{{ old('date_of_birth', $user->profile?->date_of_birth?->format('Y-m-d')) }}" style="border-radius: 0 12px 12px 0; border-color: #cbd5e1; font-size: 0.92rem; color: #0f172a;">
                </div>
                @error('date_of_birth') <div class="text-danger small mt-1.5">{{ $message }}</div> @enderror
            </div>

            <!-- Interests -->
            <div class="col-12 mt-4 pt-2">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <label class="form-label fw-bolder text-dark small mb-0 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-heart-fill text-danger"></i>
                        <span>{{ __('Shopping Interests & Categories') }}</span>
                    </label>
                    <span class="text-muted small" style="font-size: 0.78rem;">Select your favorite categories for personalized recommendations</span>
                </div>
                <div class="row g-2.5">
                    @php $userInterests = old('interests', $user->interests->pluck('id')->toArray()); @endphp
                    @foreach($categories as $category)
                        @php $isCatChecked = in_array($category->id, $userInterests); @endphp
                        <div class="col-md-4 col-6">
                            <input class="btn-check" type="checkbox" name="interests[]" value="{{ $category->id }}" id="cat_{{ $category->id }}" autocomplete="off" {{ $isCatChecked ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary w-100 text-start d-flex align-items-center justify-content-between py-2.5 px-3 rounded-3" for="cat_{{ $category->id }}" style="font-size:0.86rem; border-color: #cbd5e1;">
                                <span class="text-truncate">{{ $category->name }}</span>
                                <i class="bi bi-check-circle-fill ms-1 text-primary check-icon" style="display: {{ $isCatChecked ? 'inline-block' : 'none' }};"></i>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('interests') <div class="text-danger small mt-1.5">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="font-size: 0.92rem;">
                <i class="bi bi-check-lg"></i> {{ __('Save Profile Changes') }}
            </button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-bold d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-check-circle-fill"></i> {{ __('Profile updated successfully!') }}
                </span>
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
