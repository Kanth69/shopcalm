@extends('layouts.customer')

@section('title', 'Complete Your Profile - Shopcalm')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-primary text-white py-4 text-center">
                    <h2 class="h4 mb-1 fw-bold">Welcome to Shopcalm!</h2>
                    <p class="mb-0 opacity-75">Let's personalize your shopping experience. All fields are optional.</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <!-- Read-only existing data -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Email Address</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->email ?? 'N/A' }}" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Mobile Number</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->mobile_number }}" readonly disabled>
                        </div>

                        <hr class="my-2 opacity-25">

                        <form action="{{ route('profile.setup.update') }}" method="POST" id="profile-setup-form">
                            @csrf
                            <div class="row g-4">
                                <!-- Gender -->
                                <div class="col-md-6">
                                    <label for="gender" class="form-label fw-bold">Gender</label>
                                    <select id="gender" name="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                        <option value="Prefer not to say">Prefer not to say</option>
                                    </select>
                                </div>

                                <!-- Date of Birth -->
                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" max="{{ date('Y-m-d') }}">
                                </div>

                                <!-- Interests -->
                                <div class="col-12">
                                    <label class="form-label fw-bold">What are you interested in? <small class="text-muted">(Select Categories)</small></label>
                                    <div class="row g-2">
                                        @foreach($categories as $category)
                                            <div class="col-md-4 col-6">
                                                <div class="form-check border rounded p-2 ps-4 hover-bg-light">
                                                    <input class="form-check-input" type="checkbox" name="interests[]" value="{{ $category->id }}" id="cat_{{ $category->id }}">
                                                    <label class="form-check-label small" for="cat_{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-5 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <button type="submit" form="profile-setup-form" class="btn btn-primary btn-lg px-5">Save & Continue</button>

                        <form action="{{ route('profile.setup.skip') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted text-decoration-none">Skip for Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg-light:hover { background-color: #f8f9fa; }
</style>
@endsection
