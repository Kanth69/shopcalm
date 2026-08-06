@extends('admin.layouts.app')

@section('header', 'Offers & Mega Sales Management')

@section('actions')
    <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create New Campaign
    </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.offers.index') }}">
            <div class="row g-3">
                <div class="col-md-7">
                    <input type="text" name="search" class="form-control" placeholder="Search by campaign title..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Campaign Types</option>
                        <option value="MEGA_SALE" {{ request('type') == 'MEGA_SALE' ? 'selected' : '' }}>Mega Sale Campaign</option>
                        <option value="FLASH_DEAL" {{ request('type') == 'FLASH_DEAL' ? 'selected' : '' }}>Flash Deal</option>
                        <option value="BANK_OFFER" {{ request('type') == 'BANK_OFFER' ? 'selected' : '' }}>Bank Offer</option>
                        <option value="CATEGORY_SALE" {{ request('type') == 'CATEGORY_SALE' ? 'selected' : '' }}>Category Sale</option>
                        <option value="DIRECT_DISCOUNT" {{ request('type') == 'DIRECT_DISCOUNT' ? 'selected' : '' }}>Direct Discount</option>
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
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Campaign / Offer</th>
                        <th>Type</th>
                        <th>Badge</th>
                        <th>Discount</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offers as $offer)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-3">
                                @if($offer->banner_image)
                                    <img src="{{ asset('storage/' . $offer->banner_image) }}" class="rounded-2" style="width: 54px; height: 38px; object-fit: cover;">
                                @else
                                    <div class="rounded-2 d-flex align-items-center justify-content-center text-white fw-bold" style="width: 54px; height: 38px; background-color: {{ $offer->theme_color }};">
                                        {{ substr($offer->title, 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $offer->title }}</h6>
                                    @if($offer->description)
                                        <small class="text-muted text-truncate d-inline-block" style="max-width: 240px;">{{ $offer->description }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-semibold">{{ str_replace('_', ' ', $offer->type) }}</span>
                        </td>
                        <td>
                            @if($offer->badge_text)
                                <span class="badge text-white px-2 py-1" style="background-color: {{ $offer->theme_color }};">{{ $offer->badge_text }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-primary fs-6">
                                {{ $offer->discount_type === 'PERCENTAGE' ? $offer->discount_value.'%' : '₹'.number_format($offer->discount_value, 2) }}
                            </strong>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                @if($offer->min_purchase_amount > 0) Min: ₹{{ number_format($offer->min_purchase_amount) }} @endif
                            </small>
                        </td>
                        <td>
                            <small class="text-muted d-block">From: {{ $offer->start_time ? $offer->start_time->format('d M Y, H:i') : 'Immediate' }}</small>
                            <small class="text-muted d-block">To: {{ $offer->end_time ? $offer->end_time->format('d M Y, H:i') : 'No Expiry' }}</small>
                        </td>
                        <td>
                            @if($offer->isLive())
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-broadcast me-1"></i> LIVE NOW
                                </span>
                            @elseif(!$offer->is_active)
                                <span class="badge bg-secondary rounded-pill px-3">Inactive</span>
                            @elseif($offer->start_time && now()->lt($offer->start_time))
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">Scheduled</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Ended</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group">
                                <a href="{{ route('admin.offers.edit', $offer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this offer campaign?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-tags fs-1 d-block mb-2 text-secondary"></i>
                            No sales offer campaigns created yet. Click <strong>"Create New Campaign"</strong> above to launch your first Mega Sale!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($offers->hasPages())
            <div class="p-3">
                {{ $offers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
