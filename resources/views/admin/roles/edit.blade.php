@extends('admin.layouts.app')

@section('header', 'Edit Admin User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $admin->name }}</li>
@endsection

@section('actions')
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Admins
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-person-gear text-primary me-2"></i>Edit Administrative Account
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.roles.update', $admin) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $admin->name) }}" required>
                        </div>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
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

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Security Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="2" {{ old('role_id', $admin->role_id) == 2 ? 'selected' : '' }}>Admin (Standard Catalog, Orders, Stocks & Offer Management)</option>
                            <option value="1" {{ old('role_id', $admin->role_id) == 1 ? 'selected' : '' }}>Super Admin (Full Unrestricted System & Admin Access)</option>
                        </select>
                        @error('role_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="fw-bold text-dark small mb-2"><i class="bi bi-shield-lock me-1"></i>Change Password (Optional)</div>
                        <div class="text-muted small mb-3" style="font-size: 0.72rem;">Leave these fields blank to retain the current password.</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">New Password</label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 6 characters">
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small">Confirm New Password</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-check-circle me-1"></i> Update Admin User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
