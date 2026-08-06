@extends('admin.layouts.app')

@section('header', 'Customer Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}" class="text-decoration-none">Customers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-4 mb-4">
            <div class="card-body">
                <img src="{{ $customer->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($customer->name).'&color=7F9CF5&background=EBF4FF&size=128' }}"
                     alt="{{ $customer->name }}" class="rounded-circle shadow-sm mb-3" style="width: 128px; height: 128px; object-fit: cover;">
                <h5 class="fw-bold mb-1">{{ $customer->name }}</h5>
                <p class="text-muted small mb-3">Customer ID: #{{ $customer->id }}</p>

                @if($customer->status === 'Active')
                    <span class="badge bg-success px-3 py-2 rounded-pill mb-4">Active</span>
                @else
                    <span class="badge bg-danger px-3 py-2 rounded-pill mb-4">Blocked</span>
                @endif

                <div class="text-start">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-envelope me-2 text-primary"></i>
                        <span class="text-muted small">{{ $customer->email ?? 'No email provided' }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-phone me-2 text-primary"></i>
                        <span class="text-muted small">{{ $customer->mobile_number }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-calendar-check me-2 text-primary"></i>
                        <span class="text-muted small">Registered: {{ $customer->created_at ? $customer->created_at->format('d M, Y') : 'Unknown' }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-patch-check me-2 text-primary"></i>
                        <span class="text-muted small">Verified: {{ $customer->email_verified_at ? 'Yes' : 'No' }}</span>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <form action="{{ route('admin.customers.toggle-status', $customer) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-{{ $customer->status === 'Active' ? 'outline-warning' : 'success' }} w-100">
                            {{ $customer->status === 'Active' ? 'Block Account' : 'Unblock Account' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted text-uppercase mb-1">Account Created</label>
                        <p class="fw-bold">{{ $customer->created_at ? $customer->created_at->format('d F Y, h:i A') : 'Unknown' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted text-uppercase mb-1">Last Profile Update</label>
                        <p class="fw-bold">{{ $customer->updated_at ? $customer->updated_at->format('d F Y, h:i A') : 'Unknown' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Saved Addresses</h5>
            </div>
            <div class="card-body">
                @if($customer->addresses->isNotEmpty())
                    <div class="row g-3">
                        @foreach($customer->addresses as $address)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0">{{ $address->name }}</h6>
                                        @if($address->is_default)
                                            <span class="badge bg-primary rounded-pill">Default</span>
                                        @endif
                                    </div>
                                    <p class="small text-muted mb-0">
                                        {{ $address->address }}<br>
                                        {{ $address->city }}, {{ $address->state }} {{ $address->zip }}<br>
                                        {{ $address->country }}<br>
                                        <strong>Phone:</strong> {{ $address->phone }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-geo-alt fs-2 d-block mb-2"></i>
                        No saved addresses for this customer.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
