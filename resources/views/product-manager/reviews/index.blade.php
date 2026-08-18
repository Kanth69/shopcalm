@extends('product-manager.layouts.app')

@section('title', 'Product Reviews')
@section('header', 'Product Reviews')
@section('subheader', 'Moderate customer reviews and rating feedback')

@section('content')
<!-- Rating KPIs -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Average Rating</div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($averageRating, 1) }} / 5.0</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Total Reviews</div>
            <div class="fs-3 fw-bold text-dark">{{ number_format($totalReviews) }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100 {{ $pendingReviews > 0 ? 'border-warning border-2' : '' }}">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Pending Moderation</div>
            <div class="fs-3 fw-bold text-warning">{{ number_format($pendingReviews) }}</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card p-3 bg-white border-0 shadow-sm rounded-4 h-100">
            <div class="text-muted small fw-semibold text-uppercase mb-1" style="font-size: 0.72rem;">Approved & Live</div>
            <div class="fs-3 fw-bold text-success">{{ number_format($approvedReviews) }}</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card bg-white border-0 shadow-sm rounded-4 p-3 mb-4">
    <form method="GET" action="{{ route('product-manager.reviews.index') }}">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search customer or review..." value="{{ request('search') }}" style="font-size: 0.85rem;">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Statuses</option>
                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="rating" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">All Star Ratings</option>
                    <option value="5" {{ request('rating') == 5 ? 'selected' : '' }}>5 Stars</option>
                    <option value="4" {{ request('rating') == 4 ? 'selected' : '' }}>4 Stars</option>
                    <option value="3" {{ request('rating') == 3 ? 'selected' : '' }}>3 Stars</option>
                    <option value="2" {{ request('rating') == 2 ? 'selected' : '' }}>2 Stars</option>
                    <option value="1" {{ request('rating') == 1 ? 'selected' : '' }}>1 Star</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="product_id" class="form-select" onchange="this.form.submit()" style="font-size: 0.85rem;">
                    <option value="">Filter by Product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status', 'rating', 'product_id']))
                    <a href="{{ route('product-manager.reviews.index') }}" class="btn btn-light border"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Reviews Table -->
<form action="{{ route('product-manager.reviews.bulk-action') }}" method="POST" id="bulkReviewForm">
    @csrf
    <div class="card bg-white border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="mb-0 fw-bold text-dark">Reviews ({{ $reviews->total() }})</h6>
            <div class="d-flex align-items-center gap-2">
                <select name="action" class="form-select form-select-sm" style="width: auto; font-size: 0.8rem;" required>
                    <option value="">Bulk Action</option>
                    <option value="approve">Approve</option>
                    <option value="reject">Reject</option>
                    <option value="delete">Delete</option>
                </select>
                <button type="submit" class="btn btn-sm btn-light border rounded-pill px-3" style="font-size: 0.8rem;">Apply</button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th style="font-size: 0.72rem;">Product</th>
                            <th style="font-size: 0.72rem;">Customer</th>
                            <th style="font-size: 0.72rem;">Review & Rating</th>
                            <th style="font-size: 0.72rem;">Status</th>
                            <th class="pe-3 text-end" style="font-size: 0.72rem;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $rev)
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" name="selected_reviews[]" value="{{ $rev->id }}" class="form-check-input review-checkbox">
                            </td>
                            <td>
                                <div class="fw-bold text-dark small text-truncate" style="max-width: 170px;">{{ $rev->product->name ?? '—' }}</div>
                            </td>
                            <td>
                                <div class="fw-medium text-dark small">{{ $rev->user->name ?? 'Guest User' }}</div>
                            </td>
                            <td style="max-width: 320px;">
                                <div class="text-warning small mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $rev->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <div class="text-dark small">{{ $rev->review }}</div>
                            </td>
                            <td>
                                @if($rev->status === 'Approved')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Approved</span>
                                @elseif($rev->status === 'Pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Pending</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">Rejected</span>
                                @endif
                            </td>
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
                            <td colspan="6" class="text-center py-5 text-muted">No product reviews found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reviews->hasPages())
                <div class="px-4 py-3 border-top bg-white">
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
        text: 'This review will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Delete'
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
@endsection
