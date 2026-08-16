@extends('admin.layouts.app')

@section('header', 'Banner Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Banners</li>
@endsection

@section('actions')
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Add New Banner
    </a>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.banners.index') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search by title, subtitle..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Banner Types</option>
                        <option value="GENERAL_PROMO" {{ request('type') == 'GENERAL_PROMO' ? 'selected' : '' }}>Promo Slider</option>
                        <option value="CAMPAIGN_OFFER" {{ request('type') == 'CAMPAIGN_OFFER' ? 'selected' : '' }}>Mega Sale Campaign</option>
                        <option value="CATEGORY_HEADER" {{ request('type') == 'CATEGORY_HEADER' ? 'selected' : '' }}>Category Banner</option>
                        <option value="BRAND_PROMO" {{ request('type') == 'BRAND_PROMO' ? 'selected' : '' }}>Brand Banner</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'type', 'status']))
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear filters">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Banners Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-images text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Active Storefront Banners</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total: {{ $banners->total() }} Banners
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 70px;">Order</th>
                        <th style="width: 170px;">Preview</th>
                        <th>Banner Details</th>
                        <th>Type / Purpose</th>
                        <th>Destination / Offer</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                    <tr id="banner-row-{{ $banner->id }}">
                        <!-- Order # -->
                        <td class="ps-4 text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace fw-bold">
                                #{{ $banner->display_order }}
                            </span>
                        </td>

                        <!-- Banner Preview Thumbnail -->
                        <td>
                            @if($banner->desktop_image)
                                <img src="{{ asset('storage/' . $banner->desktop_image) }}" class="rounded-3 border shadow-xs" style="width: 150px; height: 56px; object-fit: cover;">
                            @else
                                <div class="rounded-3 d-flex align-items-center justify-content-center text-white small fw-bold px-2 shadow-xs text-center text-truncate" 
                                     style="width: 150px; height: 56px; background: {{ $banner->bg_gradient ?? 'linear-gradient(135deg, #6366f1 0%, #0f172a 100%)' }}; font-size: 0.72rem;">
                                    Gradient Preview
                                </div>
                            @endif
                        </td>

                        <!-- Title & Subtitle -->
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $banner->title }}</div>
                            @if($banner->subtitle)
                                <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $banner->subtitle }}</div>
                            @endif
                            <div class="small mt-1 text-secondary">
                                <span class="badge bg-light text-dark border me-1"><i class="bi bi-cursor-fill me-1 text-primary"></i>{{ $banner->primary_button_text ?? 'Shop Now' }}</span>
                            </div>
                        </td>

                        <!-- Banner Type Badge -->
                        <td>
                            @if(($banner->banner_type ?? 'GENERAL_PROMO') === 'CAMPAIGN_OFFER')
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-fire me-1"></i> Mega Sale
                                </span>
                            @elseif($banner->banner_type === 'CATEGORY_HEADER')
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-grid me-1"></i> Category
                                </span>
                            @elseif($banner->banner_type === 'BRAND_PROMO')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-award me-1"></i> Brand
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-image me-1"></i> Promo Slider
                                </span>
                            @endif
                        </td>

                        <!-- Destination URL / Linked Offer -->
                        <td>
                            @if($banner->offer)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-semibold">
                                    <i class="bi bi-tags-fill me-1"></i> {{ $banner->offer->title }}
                                </span>
                            @else
                                <code class="text-secondary small bg-light px-2 py-1 rounded">{{ $banner->primary_button_link ?? '/shop' }}</code>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="text-center">
                            @if($banner->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">Inactive</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-outline-primary" title="Edit Banner">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteBanner({{ $banner->id }}, '{{ route('admin.banners.destroy', $banner) }}', this)" title="Delete Banner">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-images text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Banners Found</h6>
                            <p class="small text-muted mb-0">Upload your first homepage hero slider or promo banner.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($banners->hasPages())
            <div class="p-4 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Showing <strong>{{ $banners->firstItem() }}</strong> to <strong>{{ $banners->lastItem() }}</strong> of <strong>{{ $banners->total() }}</strong> banners
                </div>
                <div>
                    {{ $banners->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteBanner(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Banner?',
        text: 'Are you sure you want to delete this storefront banner?',
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
                    throw new Error(data.message || 'Failed to delete banner.');
                }
                return data;
            })
            .then(data => {
                const row = document.getElementById('banner-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: data.message || 'Banner deleted successfully.',
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
                    text: err.message || 'Failed to delete banner.',
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
