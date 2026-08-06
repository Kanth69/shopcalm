@extends('admin.layouts.app')

@section('header', 'Banner Management')

@section('actions')
    <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add New Banner
    </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.banners.index') }}">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Banner Types</option>
                        <option value="GENERAL_PROMO" {{ request('type') == 'GENERAL_PROMO' ? 'selected' : '' }}>Category / Page Redirect Banner</option>
                        <option value="CAMPAIGN_OFFER" {{ request('type') == 'CAMPAIGN_OFFER' ? 'selected' : '' }}>Mega Sale Campaign Banner</option>
                        <option value="CATEGORY_HEADER" {{ request('type') == 'CATEGORY_HEADER' ? 'selected' : '' }}>Category Header Banner</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase fw-bold">
                    <tr>
                        <th class="ps-3" width="80">Order</th>
                        <th width="180">Preview</th>
                        <th>Banner Details</th>
                        <th>Banner Purpose</th>
                        <th>Linked Offer</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($banners as $banner)
                    <tr>
                        <td class="ps-3 text-center">
                            <span class="fw-bold fs-6">{{ $banner->display_order }}</span>
                        </td>
                        <td>
                            <img src="{{ asset('storage/' . $banner->desktop_image) }}" class="rounded shadow-sm border" style="width: 150px; height: 60px; object-fit: cover;">
                        </td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $banner->title }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 250px;">{{ $banner->subtitle }}</div>
                            <div class="small mt-1 text-primary"><i class="bi bi-link-45deg me-1"></i>{{ $banner->primary_button_link ?? '/shop' }}</div>
                        </td>
                        <td>
                            @if(($banner->banner_type ?? 'GENERAL_PROMO') === 'CAMPAIGN_OFFER')
                                <span class="badge rounded-pill px-3 py-1" style="background-color:#7c3aed; color:#fff;">
                                    <i class="bi bi-fire me-1"></i> Mega Sale
                                </span>
                            @elseif($banner->banner_type === 'CATEGORY_HEADER')
                                <span class="badge bg-info text-dark rounded-pill px-3 py-1">
                                    <i class="bi bi-grid me-1"></i> Category
                                </span>
                            @elseif($banner->banner_type === 'BRAND_PROMO')
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                    <i class="bi bi-award me-1"></i> Brand
                                </span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                    <i class="bi bi-image me-1"></i> Promo
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($banner->offer)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-tags-fill me-1"></i> {{ $banner->offer->title }}
                                </span>
                            @else
                                <span class="text-muted small">— None —</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $banner->is_active ? 'success' : 'secondary' }} rounded-pill px-3 py-1">
                                {{ $banner->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this banner?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-images fs-1 text-secondary d-block mb-2"></i>
                            No banners created yet. Click <strong>"Add New Banner"</strong> above to upload homepage sliders or campaign banners!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $banners->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
