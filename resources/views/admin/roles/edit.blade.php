@extends('admin.layouts.app')

@section('header', 'Edit Admin User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit {{ $admin->name }}</li>
@endsection

@section('actions')
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-1.5 d-inline-flex align-items-center" style="gap: 0.5rem !important;">
        <i class="bi bi-arrow-left"></i> Back to Admins
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <div class="d-flex align-items-center" style="gap: 0.75rem !important;">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 32px; height: 32px; background: #ede9fe;">
                        <i class="bi bi-person-gear fs-5"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Edit Administrative Account</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.roles.update', $admin) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted px-3"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror ps-2" value="{{ old('name', $admin->name) }}" required>
                        </div>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted px-3"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror ps-2" value="{{ old('email', $admin->email) }}" required>
                            </div>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Mobile Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted px-3"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror ps-2" value="{{ old('mobile_number', $admin->mobile_number) }}" required>
                            </div>
                            @error('mobile_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Departmental Staff Role <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="7" {{ old('role_id', $admin->role_id) == '7' ? 'selected' : '' }}>🛵 Delivery Partner (Bengaluru Local Fleet Run-Sheets & Doorstep OTP - /delivery/login)</option>
                            <option value="5" {{ old('role_id', $admin->role_id) == '5' ? 'selected' : '' }}>🚚 Order Manager (Orders, Shipments & Fulfillment Analytics)</option>
                            <option value="4" {{ old('role_id', $admin->role_id) == '4' ? 'selected' : '' }}>📦 Product Manager (Catalog, Stock, Reviews & Product Reports - /product-manager/login)</option>
                            <option value="6" {{ old('role_id', $admin->role_id) == '6' ? 'selected' : '' }}>🎧 Customer Support (Contact Enquiries, Customer Registry & Subscribers)</option>
                            <option value="2" {{ old('role_id', $admin->role_id) == '2' ? 'selected' : '' }}>🛡️ Admin (General Operations, Product Approvals & Storefront Config)</option>
                            <option value="1" {{ old('role_id', $admin->role_id) == '1' ? 'selected' : '' }}>👑 Super Admin (Full Master Authority & Staff Provisioning)</option>
                        </select>
                        @error('role_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">New Password <span class="text-muted fw-normal">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted px-3"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror ps-2" placeholder="Leave blank to keep current">
                            </div>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted px-3"><i class="bi bi-shield-check"></i></span>
                                <input type="password" name="password_confirmation" class="form-control ps-2" placeholder="Repeat new password">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end" style="gap: 0.75rem !important;">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-light border rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold d-inline-flex align-items-center" style="gap: 0.5rem !important;">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
