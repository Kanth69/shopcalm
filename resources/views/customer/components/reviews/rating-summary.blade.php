<div class="card">
    <div class="card-body">
        <h5 class="card-title">Customer Reviews</h5>
        <div class="row align-items-center">
            <div class="col-md-4 text-center">
                <h1 class="display-4 fw-bold">{{ number_format($product->averageRating(), 1) }}</h1>
                <div class="text-warning">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $i <= $product->averageRating() ? 'bi-star-fill' : ($i - 0.5 <= $product->averageRating() ? 'bi-star-half' : 'bi-star') }}"></i>
                    @endfor
                </div>
                <p class="text-muted">Based on {{ $product->approvedReviews->count() }} reviews</p>
            </div>
            <div class="col-md-8">
                @for($i = 5; $i >= 1; $i--)
                <div class="d-flex align-items-center mb-1">
                    <div class="text-nowrap me-3">{{ $i }} ★</div>
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $product->ratingPercentage($i) }}%;" aria-valuenow="{{ $product->ratingPercentage($i) }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>
