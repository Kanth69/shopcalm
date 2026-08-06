<div class="card">
    <div class="card-body">
        @auth
            @php
                $userReview = $product->reviews()->where('user_id', Auth::id())->first();
            @endphp
            @if($userReview)
                <h5 class="card-title">You've already reviewed this product</h5>
                <p>You can edit or delete your existing review.</p>
                @include('customer.components.reviews.review-card', ['review' => $userReview])
            @else
                <h5 class="card-title">Write a Review</h5>
                <form action="{{ route('reviews.store', $product) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-rating">
                            <input type="radio" id="5-stars" name="rating" value="5" /><label for="5-stars" class="star">&#9733;</label>
                            <input type="radio" id="4-stars" name="rating" value="4" /><label for="4-stars" class="star">&#9733;</label>
                            <input type="radio" id="3-stars" name="rating" value="3" /><label for="3-stars" class="star">&#9733;</label>
                            <input type="radio" id="2-stars" name="rating" value="2" /><label for="2-stars" class="star">&#9733;</label>
                            <input type="radio" id="1-star" name="rating" value="1" /><label for="1-star" class="star">&#9733;</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Review Title</label>
                        <input type="text" name="title" id="title" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="review" class="form-label">Your Review</label>
                        <textarea name="review" id="review" rows="3" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            @endif
        @else
            <p><a href="{{ route('login') }}">Log in</a> to write a review.</p>
        @endauth
    </div>
</div>
