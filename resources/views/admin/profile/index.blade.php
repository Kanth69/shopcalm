@extends('admin.layouts.app')

@section('header', 'Admin Account Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
@endsection

@section('content')
<div class="row g-4 justify-content-center">
    <!-- Left Column: Identity & Avatar Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center p-4">
            <div class="card-body p-0">
                <!-- Avatar Upload Form -->
                <form action="{{ route('admin.profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3 position-relative d-inline-block">
                        @if($admin->avatar)
                            <img src="{{ asset('storage/' . $admin->avatar) }}" alt="{{ $admin->name }}" 
                                 class="rounded-circle shadow-sm border border-4 border-white" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle shadow-sm border border-4 border-white d-flex align-items-center justify-content-center text-white fw-bold mx-auto" 
                                 style="width: 120px; height: 120px; background: {{ $admin->isSuperAdmin() ? '#ef4444' : '#3b82f6' }}; font-size: 2.2rem;">
                                {{ strtoupper(substr($admin->name, 0, 2)) }}
                            </div>
                        @endif
                        <label for="avatar-input" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 shadow d-flex align-items-center justify-content-center" 
                               style="width: 34px; height: 34px; cursor: pointer;" title="Change Profile Picture">
                            <i class="bi bi-camera-fill" style="font-size: 0.85rem;"></i>
                        </label>
                        <input type="file" name="avatar" id="avatar-input" class="d-none" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                    </div>
                </form>

                <h5 class="fw-bold text-dark mb-1">{{ $admin->name }}</h5>
                <p class="text-muted small mb-3">{{ $admin->email }}</p>

                @if($admin->isSuperAdmin())
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill font-monospace small">
                        <i class="bi bi-shield-fill-check me-1"></i> Super Administrator
                    </span>
                @else
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill font-monospace small">
                        <i class="bi bi-person-badge-fill me-1"></i> System Administrator
                    </span>
                @endif

                <hr class="my-4">

                <!-- Account Information Summary -->
                <div class="text-start p-3 bg-light rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="text-muted small"><i class="bi bi-hash me-1 text-secondary"></i>Account ID:</span>
                        <span class="fw-bold text-dark small font-monospace">#{{ $admin->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="text-muted small"><i class="bi bi-calendar-check me-1 text-secondary"></i>Member Since:</span>
                        <span class="fw-semibold text-dark small">{{ $admin->created_at ? $admin->created_at->format('d M Y') : 'Unknown' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="text-muted small"><i class="bi bi-clock-history me-1 text-secondary"></i>Last Active:</span>
                        <span class="fw-semibold text-dark small">{{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Just now' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small"><i class="bi bi-check-circle me-1 text-success"></i>Status:</span>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5 small">Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Personal Information & Password -->
    <div class="col-lg-8">
        <!-- 1. Personal Details Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i>1. Personal Profile Information
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}" required>
                            </div>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                            </div>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" value="{{ old('mobile_number', $admin->mobile_number) }}" required>
                            </div>
                            @error('mobile_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Update Profile Details
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Security & Password Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-shield-lock-fill text-primary me-2"></i>2. Security & Password Modification
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Current Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required placeholder="••••••••">
                            </div>
                            @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Min 6 chars">
                            </div>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-shield-check"></i></span>
                                <input type="password" name="password_confirmation" class="form-control" required placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark">
                            <i class="bi bi-key-fill me-1"></i> Update Security Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
