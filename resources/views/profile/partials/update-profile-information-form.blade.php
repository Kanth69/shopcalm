<section>
    <header>
        <h2 class="h5">
            {{ __('Profile Details') }}
        </h2>

        <p class="mt-1 text-muted">
            {{ __("Complete your profile to unlock personalized offers and rewards.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="row">
            <!-- Name -->
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label fw-bold">{{ __('Full Name') }}</label>
                <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Email -->
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label fw-bold">{{ __('Email Address') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" autocomplete="username">
                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Mobile -->
            <div class="col-md-6 mb-3">
                <label for="mobile_number" class="form-label fw-bold">{{ __('Mobile Number') }}</label>
                <input id="mobile_number" name="mobile_number" type="text" class="form-control" value="{{ old('mobile_number', $user->mobile_number) }}" required>
                @error('mobile_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Gender -->
            <div class="col-md-6 mb-3">
                <label for="gender" class="form-label fw-bold">{{ __('Gender') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <select id="gender" name="gender" class="form-select">
                    <option value="">Select Gender</option>
                    <option value="Male" {{ old('gender', $user->profile?->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender', $user->profile?->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender', $user->profile?->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                    <option value="Prefer not to say" {{ old('gender', $user->profile?->gender) === 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                </select>
                @error('gender') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- DOB -->
            <div class="col-md-6 mb-3">
                <label for="date_of_birth" class="form-label fw-bold">{{ __('Date of Birth') }} <span class="text-muted fw-normal">(Optional)</span></label>
                <input id="date_of_birth" name="date_of_birth" type="date" class="form-control" value="{{ old('date_of_birth', $user->profile?->date_of_birth?->format('Y-m-d')) }}">
                @error('date_of_birth') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <!-- Interests -->
            <div class="col-12 mb-4">
                <label class="form-label fw-bold">{{ __('Interests (Select Categories)') }}</label>
                <div class="row g-2">
                    @foreach($categories as $category)
                        <div class="col-md-4 col-6">
                            <div class="form-check border rounded p-2 ps-4">
                                <input class="form-check-input" type="checkbox" name="interests[]" value="{{ $category->id }}" id="cat_{{ $category->id }}"
                                    {{ in_array($category->id, old('interests', $user->interests->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="cat_{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">Select at least 3 categories to increase your profile completion.</div>
                @error('interests') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary px-4">{{ __('Save Profile') }}</button>
            @if (session('status') === 'profile-updated')
                <span class="text-success small"><i class="bi bi-check-circle me-1"></i>{{ __('Changes saved.') }}</span>
            @endif
        </div>
    </form>
</section>
