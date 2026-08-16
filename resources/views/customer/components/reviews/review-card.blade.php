<div class="card border-0 shadow-sm rounded-4 mb-3" id="user-review-card-{{ $review->id }}">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:0.9rem;">
                    {{ strtoupper(substr($review->user->name ?? 'C', 0, 1)) }}
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0" style="font-size:0.9rem;">{{ $review->user->name ?? 'Customer' }}</h6>
                    <span class="text-muted small" style="font-size:0.75rem;">{{ $review->created_at->format('d M, Y') }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="text-warning" style="font-size:0.85rem;">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                    @endfor
                </div>
                @auth
                    @if(Auth::id() == $review->user_id && $review->status === 'Approved')
                        <button type="button" class="btn btn-sm btn-light text-danger border rounded-pill px-3 py-1 ms-2" style="font-size:0.75rem;" 
                            onclick="confirmDeleteUserReview({{ $review->id }}, '{{ route('reviews.destroy', $review) }}', this)">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    @endif
                @endauth
            </div>
        </div>

        @if($review->title)
            <h6 class="fw-bold text-dark mb-1" style="font-size:0.9rem;">{{ $review->title }}</h6>
        @endif
        <p class="text-secondary mb-2" style="font-size:0.875rem; line-height:1.6;">{{ $review->review }}</p>

        @if($review->is_verified_purchase)
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-medium" style="font-size:0.7rem;">
                <i class="bi bi-patch-check-fill me-1"></i> Verified Purchase
            </span>
        @endif
    </div>
</div>
