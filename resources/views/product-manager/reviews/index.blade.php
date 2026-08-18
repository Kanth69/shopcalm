@extends('product-manager.layouts.app')

@section('header', 'Product Reviews')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-manager.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reviews</li>
@endsection

@section('content')

{{-- Quick Filter Pills Row --}}
<div class="row g-3 mb-4">
    {{-- All Reviews --}}
    @php $isActiveAll = !request('status'); @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('product-manager.reviews.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveAll ? '2px solid #6366f1' : '1px solid #e2e8f0' }}; border-left: 5px solid #6366f1 !important; background: {{ $isActiveAll ? '#f5f3ff' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #6366f1;">All Reviews</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#ede9fe; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-chat-left-text-fill" style="font-size:0.95rem; color:#6366f1;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $totalReviews }}</h3>
                    @if($isActiveAll)
                        <span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Pending Approval --}}
    @php $isActivePending = request('status') === 'Pending'; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('product-manager.reviews.index', ['status' => 'Pending']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActivePending ? '2px solid #d97706' : '1px solid #e2e8f0' }}; border-left: 5px solid #d97706 !important; background: {{ $isActivePending ? '#fffbeb' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #b45309;">Pending Approval</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fef3c7; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-clock-history" style="font-size:0.95rem; color:#d97706;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $pendingReviews }}</h3>
                    @if($isActivePending)
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Approved --}}
    @php $isActiveApproved = request('status') === 'Approved'; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('product-manager.reviews.index', ['status' => 'Approved']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveApproved ? '2px solid #10b981' : '1px solid #e2e8f0' }}; border-left: 5px solid #10b981 !important; background: {{ $isActiveApproved ? '#f0fdf4' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #047857;">Approved</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#d1fae5; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-check-circle-fill" style="font-size:0.95rem; color:#10b981;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $approvedReviews }}</h3>
                    @if($isActiveApproved)
                        <span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Rejected --}}
    @php $isActiveRejected = request('status') === 'Rejected'; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('product-manager.reviews.index', ['status' => 'Rejected']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveRejected ? '2px solid #ef4444' : '1px solid #e2e8f0' }}; border-left: 5px solid #ef4444 !important; background: {{ $isActiveRejected ? '#fef2f2' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #b91c1c;">Avg Rating</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fee2e2; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-star-fill" style="font-size:0.95rem; color:#ef4444;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ number_format($averageRating, 1) }}</h3>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card mb-4" style="border-radius: 14px !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('product-manager.reviews.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search reviewer, product, or comment..." 
                            value="{{ request('search') }}"
                            style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="product_id" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 30) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="rating" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Ratings</option>
                        <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                        <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
                        <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
                        <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
                        <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.82rem;" title="Apply Filter">
                        <i class="bi bi-funnel"></i>
                    </button>
                    @if(request()->hasAny(['search', 'product_id', 'rating', 'status']))
                        <a href="{{ route('product-manager.reviews.index') }}" class="btn btn-light" style="border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.82rem;" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reviews Table Card --}}
<form action="{{ route('product-manager.reviews.bulk-action') }}" method="POST" id="bulkReviewForm">
    @csrf
    <div class="card" style="border-radius: 14px !important;">
        <div class="card-header d-flex align-items-center justify-content-between py-3" style="background:#fff; border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-chat-square-quote-fill text-primary"></i>
                <h6 class="mb-0 fw-bold text-dark">Reviews Feed</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select name="action" class="form-select form-select-sm" style="width: auto; border-radius: 8px; font-size: 0.8rem;" required>
                    <option value="">Bulk Actions</option>
                    <option value="approve">Approve Selected</option>
                    <option value="reject">Reject Selected</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.8rem;">Apply</button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th class="ps-3" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th style="font-size: 0.7rem;">Product</th>
                            <th style="font-size: 0.7rem;">Customer</th>
                            <th style="font-size: 0.7rem;">Rating & Comment</th>
                            <th style="font-size: 0.7rem;">Status</th>
                            <th style="font-size: 0.7rem;">Date</th>
                            <th class="pe-3 text-end" style="font-size: 0.7rem;">Moderate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $rev)
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" name="selected_reviews[]" value="{{ $rev->id }}" class="form-check-input review-checkbox">
                            </td>
                            <td>
                                <div class="fw-bold text-dark small text-truncate" style="max-width: 180px;">{{ $rev->product->name ?? '—' }}</div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark small">{{ $rev->user->name ?? 'Guest' }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $rev->user->email ?? '—' }}</div>
                            </td>
                            <td style="max-width: 320px;">
                                <div class="text-warning small mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $rev->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                    <span class="text-muted ms-1 font-monospace">{{ $rev->rating }}/5</span>
                                </div>
                                <div class="text-dark small" style="font-size: 0.8rem;">{{ $rev->review }}</div>
                            </td>
                            <td>
                                @if($rev->status === 'Approved')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1">Approved</span>
                                @elseif($rev->status === 'Pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-25 rounded-pill px-2.5 py-1">Pending</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1">Rejected</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $rev->created_at->format('M d, Y') }}</td>
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm">
                                    @if($rev->status !== 'Approved')
                                        <button type="button" class="btn btn-outline-success" onclick="updateReviewStatus({{ $rev->id }}, 'Approved')" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif
                                    @if($rev->status !== 'Rejected')
                                        <button type="button" class="btn btn-outline-warning" onclick="updateReviewStatus({{ $rev->id }}, 'Rejected')" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteReview({{ $rev->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No product reviews found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reviews->hasPages())
                <div class="px-4 py-3 border-top" style="background:#fff; border-radius: 0 0 14px 14px;">
                    {{ $reviews->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</form>

<form id="singleReviewActionForm" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="_method" id="singleReviewMethod" value="PATCH">
    <input type="hidden" name="status" id="singleReviewStatus">
</form>

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = this.checked);
});

function updateReviewStatus(reviewId, status) {
    const form = document.getElementById('singleReviewActionForm');
    form.action = `/product-manager/reviews/${reviewId}`;
    document.getElementById('singleReviewMethod').value = 'PATCH';
    document.getElementById('singleReviewStatus').value = status;
    form.submit();
}

function deleteReview(reviewId) {
    Swal.fire({
        title: 'Delete Review?',
        text: 'This review will be permanently removed.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it'
    }).then((res) => {
        if (res.isConfirmed) {
            const form = document.getElementById('singleReviewActionForm');
            form.action = `/product-manager/reviews/${reviewId}`;
            document.getElementById('singleReviewMethod').value = 'DELETE';
            form.submit();
        }
    });
}
</script>
@endpush

@endsection
