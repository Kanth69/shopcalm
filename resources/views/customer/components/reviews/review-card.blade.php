<div class="review-card-item py-3 border-bottom" id="user-review-card-{{ $review->id }}" style="border-color: #f1f5f9 !important;">
    {{-- Rating Pill & Headline --}}
    <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
        <span class="badge d-inline-flex align-items-center gap-1 text-white fw-bold px-2 py-1 rounded" 
              style="font-size: 0.76rem; background-color: {{ $review->rating >= 3 ? '#388e3c' : ($review->rating == 2 ? '#ff9f00' : '#ff6161') }};">
            <span>{{ $review->rating }}</span>
            <i class="bi bi-star-fill" style="font-size: 0.65rem;"></i>
        </span>
        @if($review->title)
            <span class="fw-bold text-dark text-truncate" style="font-size: 0.92rem; max-width: 200px;">{{ $review->title }}</span>
        @endif

        {{-- Author Delete Option --}}
        @auth('customer')
            @if(Auth::id() == $review->user_id && $review->status === 'Approved')
                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 ms-auto small" style="font-size: 0.72rem;" 
                        onclick="confirmDeleteUserReview({{ $review->id }}, '{{ route('reviews.destroy', $review) }}', this)">
                    <i class="bi bi-trash3"></i> Delete
                </button>
            @endif
        @endauth
    </div>

    {{-- Review Comment --}}
    <div class="text-dark my-2 review-comment-text" style="font-size: 0.88rem; line-height: 1.6; color: #212121;">
        {{ $review->review }}
    </div>

    {{-- Flipkart Signature Bottom Metadata Strip --}}
    <div class="d-flex align-items-center justify-content-between text-muted flex-wrap gap-2 pt-1" style="font-size: 0.76rem;">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-semibold text-secondary">{{ $review->user->name ?? \App\Models\Setting::get('store_name', 'ShopCalm') . ' Customer' }}</span>
            @if($review->is_verified_purchase)
                <span class="text-muted d-inline-flex align-items-center gap-1">
                    <i class="bi bi-patch-check-fill text-muted"></i> Certified Buyer
                </span>
            @endif
            <span class="text-muted">{{ $review->created_at->format('d M, Y') }}</span>
        </div>

        <div class="d-flex align-items-center gap-3 text-muted">
            <span class="d-inline-flex align-items-center gap-1 cursor-pointer hover-primary">
                <i class="bi bi-hand-thumbs-up"></i> <span class="small">Helpful</span>
            </span>
        </div>
    </div>
</div>



