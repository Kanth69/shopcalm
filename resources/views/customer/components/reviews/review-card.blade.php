<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <h6 class="fw-bold">{{ $review->user->name }}</h6>
                <p class="text-muted small">{{ $review->created_at->format('d M, Y') }}</p>
            </div>
            <div class="text-warning">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                @endfor
            </div>
        </div>
        <h5 class="card-title">{{ $review->title }}</h5>
        <p class="card-text">{{ $review->review }}</p>
        @if($review->is_verified_purchase)
            <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i> Verified Purchase</span>
        @endif
        @auth
            @if(Auth::id() == $review->user_id)
                <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline float-end">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            @endif
        @endauth
    </div>
</div>
