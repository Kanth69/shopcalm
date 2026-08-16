@extends('admin.layouts.app')

@section('header', 'Offers & Mega Sales Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Offers & Sales</li>
@endsection

@section('actions')
    <a href="{{ route('admin.offers.create') }}" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Create New Campaign
    </a>
@endsection

@section('content')

<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4">
        <form method="GET" action="{{ route('admin.offers.index') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search by campaign title or badge text..." 
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Campaign Formats</option>
                        <option value="MEGA_SALE" {{ request('type') == 'MEGA_SALE' ? 'selected' : '' }}>Mega Sale Event</option>
                        <option value="FLASH_DEAL" {{ request('type') == 'FLASH_DEAL' ? 'selected' : '' }}>Flash Lightning Deal</option>
                        <option value="BANK_OFFER" {{ request('type') == 'BANK_OFFER' ? 'selected' : '' }}>Bank Instant Offer</option>
                        <option value="CATEGORY_SALE" {{ request('type') == 'CATEGORY_SALE' ? 'selected' : '' }}>Category Sale</option>
                        <option value="DIRECT_DISCOUNT" {{ request('type') == 'DIRECT_DISCOUNT' ? 'selected' : '' }}>Direct Storefront Discount</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100 fw-bold">Filter</button>
                    @if(request()->hasAny(['search', 'type']))
                        <a href="{{ route('admin.offers.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Clear filters">Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Campaigns Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tags-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Promotional Campaigns Registry</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total: {{ $offers->total() }} Campaigns
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 340px;">Campaign Details</th>
                        <th>Format</th>
                        <th>Discount Rate</th>
                        <th>Target Scope</th>
                        <th>Schedule Timers</th>
                        <th class="text-center">Live Status</th>
                        <th class="pe-4 text-end" style="width: 110px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                    <tr id="offer-row-{{ $offer->id }}">
                        <!-- Campaign Title & Banner -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3 py-1">
                                @if($offer->banner_image)
                                    <img src="{{ asset('storage/' . $offer->banner_image) }}" class="rounded-3 border shadow-xs flex-shrink-0" style="width: 64px; height: 42px; object-fit: cover;">
                                @else
                                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                         style="width: 64px; height: 42px; background-color: {{ $offer->theme_color ?? '#6366f1' }}; font-size: 0.9rem;">
                                        {{ strtoupper(substr($offer->title, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="overflow-hidden ps-1">
                                    <div class="fw-bold text-dark text-truncate mb-1" style="max-width: 230px;" title="{{ $offer->title }}">
                                        {{ $offer->title }}
                                    </div>
                                    @if($offer->badge_text)
                                        <span class="badge rounded-pill px-2.5 py-0.5 text-white" style="background-color: {{ $offer->theme_color ?? '#6366f1' }}; font-size: 0.68rem;">
                                            {{ $offer->badge_text }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Format -->
                        <td>
                            <span class="badge bg-light text-dark border fw-semibold rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                {{ str_replace('_', ' ', $offer->type) }}
                            </span>
                        </td>

                        <!-- Discount -->
                        <td>
                            <div class="fw-bolder text-primary fs-6">
                                {{ $offer->discount_type === 'PERCENTAGE' ? round($offer->discount_value).'%' : '₹'.number_format($offer->discount_value, 2) }} OFF
                            </div>
                            @if($offer->min_purchase_amount > 0)
                                <div class="text-muted small" style="font-size: 0.7rem;">Min: ₹{{ number_format($offer->min_purchase_amount) }}</div>
                            @endif
                        </td>

                        <!-- Target Scope -->
                        <td>
                            @php $target = $offer->targets->first(); @endphp
                            @if(!$target)
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small">
                                    <i class="bi bi-globe me-1"></i>Storewide
                                </span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 small">
                                    {{ ucfirst(strtolower($target->target_type)) }} Target
                                </span>
                            @endif
                        </td>

                        <!-- Schedule Timers -->
                        <td class="small text-muted" style="font-size: 0.72rem;">
                            @if($offer->start_time)
                                <div>From: <strong class="text-dark">{{ $offer->start_time->format('d M Y') }}</strong></div>
                            @else
                                <div>From: <span class="text-success fw-semibold">Immediate</span></div>
                            @endif
                            @if($offer->end_time)
                                <div>To: <strong class="text-dark">{{ $offer->end_time->format('d M Y') }}</strong></div>
                            @else
                                <div>To: <span class="text-muted">No Expiry</span></div>
                            @endif
                        </td>

                        <!-- Live Status -->
                        <td class="text-center">
                            @if($offer->isLive())
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                    <i class="bi bi-broadcast me-1"></i> LIVE NOW
                                </span>
                            @elseif(!$offer->is_active)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">Inactive</span>
                            @elseif($offer->start_time && now()->lt($offer->start_time))
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1">Scheduled</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1">Ended</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-outline-primary" title="Edit Campaign">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteOffer({{ $offer->id }}, '{{ route('admin.offers.destroy', $offer) }}', this)" title="Delete Campaign">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-tags text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Sales Campaigns Found</h6>
                            <p class="small text-muted mb-0">Launch your first Mega Sale event or modify filter settings.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($offers->hasPages())
            <div class="p-4 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Showing <strong>{{ $offers->firstItem() }}</strong> to <strong>{{ $offers->lastItem() }}</strong> of <strong>{{ $offers->total() }}</strong> campaigns
                </div>
                <div>
                    {{ $offers->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteOffer(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Campaign?',
        text: 'Are you sure you want to delete this promotional offer campaign?',
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
                    throw new Error(data.message || 'Failed to delete campaign.');
                }
                return data;
            })
            .then(data => {
                const row = document.getElementById('offer-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message || 'Offer campaign deleted successfully.',
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
                    text: err.message || 'Failed to delete offer campaign.',
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
