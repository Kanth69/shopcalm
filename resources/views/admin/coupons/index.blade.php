@extends('admin.layouts.app')

@section('header', 'Coupon Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Coupons</li>
@endsection

@section('actions')
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Create New Coupon
    </a>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.coupons.index') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search by Coupon Code or Campaign Name..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming / Scheduled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Coupons Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-ticket-perforated-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Active Coupon Promotions</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total: {{ $coupons->total() }} Coupons
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 170px;">Coupon Code</th>
                        <th>Campaign Name</th>
                        <th>Discount</th>
                        <th>Min Spend</th>
                        <th>Redemptions</th>
                        <th>Validity</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr id="coupon-row-{{ $coupon->id }}">
                        <!-- Coupon Code -->
                        <td class="ps-4">
                            <div class="d-inline-flex align-items-center gap-1.5 px-2.5 py-1 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-3 font-monospace fw-bold" style="font-size: 0.82rem;">
                                <i class="bi bi-tag-fill small"></i>{{ $coupon->code }}
                            </div>
                        </td>

                        <!-- Name & Applicability -->
                        <td>
                            <div class="fw-bold text-dark">{{ $coupon->name }}</div>
                            <div class="small text-muted" style="font-size: 0.72rem;">
                                Scope: 
                                @if($coupon->applicable_type->value === 'ALL')
                                    <span class="text-secondary fw-semibold">Storewide</span>
                                @elseif($coupon->applicable_type->value === 'CATEGORY')
                                    <span class="text-primary fw-semibold">Category ({{ $coupon->category?->name ?? 'Specified' }})</span>
                                @elseif($coupon->applicable_type->value === 'BRAND')
                                    <span class="text-primary fw-semibold">Brand ({{ $coupon->brand?->name ?? 'Specified' }})</span>
                                @elseif($coupon->applicable_type->value === 'PRODUCT')
                                    <span class="text-primary fw-semibold">Product ({{ Str::limit($coupon->product?->name ?? 'Specified', 25) }})</span>
                                @endif
                            </div>
                        </td>

                        <!-- Discount -->
                        <td>
                            @if($coupon->discount_type->value === 'PERCENTAGE')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                    {{ round($coupon->discount_value) }}% OFF
                                </span>
                                @if($coupon->maximum_discount_amount > 0)
                                    <div class="small text-muted" style="font-size: 0.7rem;">Cap: ₹{{ number_format($coupon->maximum_discount_amount, 2) }}</div>
                                @endif
                            @else
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                    ₹{{ number_format($coupon->discount_value, 2) }} FLAT
                                </span>
                            @endif
                        </td>

                        <!-- Min Spend -->
                        <td class="small fw-semibold text-dark">
                            @if($coupon->minimum_order_amount > 0)
                                ₹{{ number_format($coupon->minimum_order_amount, 2) }}
                            @else
                                <span class="text-muted">No Min</span>
                            @endif
                        </td>

                        <!-- Usage -->
                        <td>
                            <div class="small fw-bold text-dark">
                                {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                            </div>
                            <div class="progress mt-1" style="height: 4px; width: 60px;">
                                @php
                                    $percent = $coupon->usage_limit ? min(100, ($coupon->used_count / $coupon->usage_limit) * 100) : 0;
                                @endphp
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percent }}%"></div>
                            </div>
                        </td>

                        <!-- Validity Dates -->
                        <td class="small text-muted" style="font-size: 0.72rem;">
                            @if($coupon->valid_until)
                                <div>Expires: <strong class="text-dark">{{ $coupon->valid_until->format('d M Y') }}</strong></div>
                                <div>{{ $coupon->valid_until->format('h:i A') }}</div>
                            @else
                                <span class="text-success fw-semibold">No Expiry (Always Active)</span>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="text-center">
                            @if(!$coupon->is_active)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">Inactive</span>
                            @elseif($coupon->valid_until && $coupon->valid_until->isPast())
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1">Expired</span>
                            @elseif($coupon->valid_from && $coupon->valid_from->isFuture())
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1">Scheduled</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">Active</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-outline-primary" title="Edit Coupon">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteCoupon({{ $coupon->id }}, '{{ route('admin.coupons.destroy', $coupon) }}', this)" title="Delete Coupon">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-ticket-perforated text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Coupons Found</h6>
                            <p class="small text-muted mb-0">Create your first coupon or adjust search filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="p-4 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Showing <strong>{{ $coupons->firstItem() }}</strong> to <strong>{{ $coupons->lastItem() }}</strong> of <strong>{{ $coupons->total() }}</strong> coupons
                </div>
                <div>
                    {{ $coupons->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteCoupon(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Coupon?',
        text: 'Are you sure you want to delete this promotional coupon?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            btnElement.disabled = true;
            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'DELETE',
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to delete coupon.');
                }
                return data;
            })
            .then(data => {
                const row = document.getElementById('coupon-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: data.message || 'Coupon deleted successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500
                });
            })
            .catch(err => {
                btnElement.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Failed to delete coupon.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        }
    });
}
</script>
@endpush
@endsection
