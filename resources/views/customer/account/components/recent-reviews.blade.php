<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Reviews</h5>
    </div>
    <div class="card-body">
        @if($recentReviews->isNotEmpty())
            <ul class="list-group list-group-flush">
                @foreach($recentReviews as $review)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('product.show', $review->product->slug) }}">{{ $review->product->name }}</a>
                            <span class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </span>
                        </div>
                        <p class="mb-0 fst-italic">"{{ $review->title }}"</p>
                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                    </li>
                @endforeach
            </ul>
        @else
            @include('customer.account.components.empty-state', [
                'icon' => 'bi-star',
                'title' => 'No Reviews Yet',
                'message' => 'You haven\'t written any reviews yet.',
                'button_text' => 'Review a Product',
                'button_url' => route('shop')
            ])
        @endif
    </div>
</div>
