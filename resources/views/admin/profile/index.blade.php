@extends('admin.layouts.app')

@section('header', 'Admin Profile')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Profile</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4">
            <div class="card-body">
                <form action="{{ route('admin.profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3 position-relative d-inline-block">
                        <img src="{{ $admin->avatar ? asset('storage/' . $admin->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($admin->name).'&background=3b82f6&color=fff&size=128' }}"
                             alt="Admin Profile" class="rounded-circle shadow-sm border border-4 border-white" style="width: 128px; height: 128px; object-fit: cover;" id="avatar-preview">
                        <label for="avatar-input" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 shadow cursor-pointer" title="Change Avatar">
                            <i class="bi bi-camera"></i>
                        </label>
                        <input type="file" name="avatar" id="avatar-input" class="d-none" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                    </div>
                </form>
                <h5 class="fw-bold mb-1">{{ $admin->name }}</h5>
                <p class="text-muted small mb-3">{{ $admin->email }}</p>
                <span class="badge bg-primary px-3 py-2 rounded-pill">{{ $admin->role->name ?? 'Admin' }}</span>
                <hr class="my-4">
                <div class="text-start">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        <span class="text-muted small">Joined: {{ $admin->created_at ? $admin->created_at->format('d M, Y') : 'Unknown' }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        <span class="text-muted small">Last Login: {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Update Personal Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $admin->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" value="{{ old('mobile_number', $admin->mobile_number) }}" required>
                            @error('mobile_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Update Security</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-warning px-4">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
</style>
@endsection
