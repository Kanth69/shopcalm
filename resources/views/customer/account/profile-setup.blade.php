@extends('layouts.customer')

@php
    $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
@endphp

@section('title', 'Complete Your Profile - ' . $storeName)

@section('content')
<div class="container my-3 my-md-4 px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
                <div class="card-header text-white py-4 px-3 px-md-4 text-center" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%);">
                    <h2 class="h5 mb-1 fw-bold text-white">Welcome to {{ $storeName }}! 👋</h2>
                    <p class="mb-0 text-white-50 small">Let's personalize your shopping experience. All fields are optional.</p>
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

                    <div class="mt-4 pt-3 border-top d-flex flex-column-reverse flex-md-row justify-content-between align-items-center gap-3">
                        <form action="{{ route('profile.setup.skip') }}" method="POST" class="m-0 w-100 w-md-auto text-center text-md-start">
                            @csrf
                            <button type="submit" class="btn btn-link text-secondary text-decoration-none small fw-semibold">Skip for Now</button>
                        </form>

                        <button type="submit" form="profile-setup-form" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold shadow-xs w-100 w-md-auto" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">Save & Continue</button>
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
