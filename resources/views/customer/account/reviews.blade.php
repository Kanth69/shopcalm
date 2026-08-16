@extends('customer.account.layout')

@section('title', 'My Reviews')

@section('account_content')

{{-- Hero Banner --}}
<div class="rounded-4 mb-4 p-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width:52px; height:52px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <i class="bi bi-star-fill text-white fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold text-white mb-0">My Reviews</h5>
            <p class="text-white-50 small mb-0">Your product feedback & ratings</p>
        </div>
    </div>
    <a href="{{ route('shop') }}" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold btn-sm">
        <i class="bi bi-bag me-1"></i> Shop & Review More
    </a>
</div>

@if($reviews->isNotEmpty())
    <div class="d-flex flex-column gap-3" id="reviews-list">
        @foreach($reviews as $review)
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" id="account-review-row-{{ $review->id }}"
             style="transition: all 0.3s ease;">
            <div class="card-body p-4">
                <div class="d-flex align-items-start gap-3 flex-wrap">

                    {{-- Rating Circle --}}
                    <div class="rounded-3 d-flex flex-column align-items-center justify-content-center flex-shrink-0"
                         style="width:60px; height:60px; background: {{ $review->rating >= 4 ? 'linear-gradient(135deg,#d1fae5,#a7f3d0)' : ($review->rating == 3 ? 'linear-gradient(135deg,#fef3c7,#fde68a)' : 'linear-gradient(135deg,#fee2e2,#fca5a5)') }};">
                        <span class="fw-bold fs-5" style="color:{{ $review->rating >= 4 ? '#065f46' : ($review->rating == 3 ? '#92400e' : '#991b1b') }};">{{ $review->rating }}</span>
                        <i class="bi bi-star-fill small" style="color:{{ $review->rating >= 4 ? '#065f46' : ($review->rating == 3 ? '#92400e' : '#991b1b') }};"></i>
                    </div>

                    {{-- Review Info --}}
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <a href="{{ route('product.show', $review->product->slug) }}"
                               class="fw-bold text-dark text-decoration-none"
                               style="font-size: 0.95rem;">
                                {{ $review->product->name }}
                            </a>
                            @if($review->status == 'Approved')
                                <span class="badge rounded-pill px-2 py-1 fw-bold" style="background:#d1fae5; color:#065f46; font-size:0.7rem;">
                                    <i class="bi bi-check-circle me-1"></i>Approved
                                </span>
                            @else
                                <span class="badge rounded-pill px-2 py-1 fw-bold" style="background:#fef3c7; color:#92400e; font-size:0.7rem;">
                                    <i class="bi bi-clock me-1"></i>Pending
                                </span>
                            @endif
                        </div>

                        {{-- Star display --}}
                        <div class="text-warning mb-1" style="font-size: 0.85rem; letter-spacing:1px;">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>

                        @if($review->title)
                        <p class="fw-semibold text-secondary mb-1" style="font-size:0.875rem;">
                            "{{ $review->title }}"
                        </p>
                        @endif

                        @if($review->body ?? $review->comment ?? null)
                        <p class="text-muted small mb-1" style="font-size:0.82rem; line-height:1.5;">
                            {{ Str::limit($review->body ?? $review->comment, 120) }}
                        </p>
                        @endif

                        <span class="text-muted" style="font-size:0.75rem;">
                            <i class="bi bi-calendar3 me-1"></i>{{ $review->created_at->format('d M, Y') }}
                        </span>
                    </div>

                    {{-- Delete Action --}}
                    <div class="flex-shrink-0 ms-auto">
                        @if($review->status === 'Approved')
                            <button type="button"
                                    class="btn btn-sm btn-light text-danger border rounded-pill px-3 fw-semibold"
                                    style="font-size:0.78rem;"
                                    onclick="confirmDeleteAccountReview({{ $review->id }}, '{{ route('reviews.destroy', $review) }}', this)">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        @else
                            <span class="text-muted small d-flex align-items-center gap-1" style="font-size:0.75rem;">
                                <i class="bi bi-hourglass-split"></i> Awaiting approval
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($reviews->hasPages())
    <div class="mt-4">
        {{ $reviews->links() }}
    </div>
    @endif

@else
    @include('customer.account.components.empty-state', [
        'icon'        => 'bi-star',
        'title'       => 'No Reviews Yet',
        'message'     => "You haven't written any reviews yet. Share your experience with products you've bought!",
        'button_text' => 'Review a Product',
        'button_url'  => route('shop')
    ])
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteAccountReview(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Review?',
        text: 'This review will be permanently deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) executeAccountReviewDelete(id, deleteUrl, btnElement);
    });
}

function executeAccountReviewDelete(id, deleteUrl, btnElement) {
    btnElement.disabled = true;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

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
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete review.');
        return data;
    })
    .then(data => {
        const row = document.getElementById('account-review-row-' + id);
        if (row) {
            row.style.opacity   = '0';
            row.style.transform = 'translateX(20px)';
            setTimeout(() => row.remove(), 300);
        }
        Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message || 'Review deleted.', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
    })
    .catch(err => {
        btnElement.disabled = false;
        btnElement.innerHTML = '<i class="bi bi-trash me-1"></i>Delete';
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
    });
}
</script>
@endpush

@endsection
