<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Recent Wishlist Items</h5>
    </div>
    <div class="card-body">
        @if($recentWishlistItems->isNotEmpty())
            <ul class="list-group list-group-flush">
                @foreach($recentWishlistItems as $item)
                    <li class="list-group-item d-flex align-items-center">
                        <img src="{{ asset('storage/' . $item->product->main_image) }}" alt="{{ $item->product->name }}" width="50" class="me-3">
                        <div class="flex-grow-1">
                            <a href="{{ route('product.show', $item->product->slug) }}" class="text-decoration-none text-dark">{{ $item->product->name }}</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
             @include('customer.account.components.empty-state', [
                'icon' => 'bi-heart',
                'title' => 'Your Wishlist is Empty',
                'message' => 'Add products you love to your wishlist.',
                'button_text' => 'Discover Products',
                'button_url' => route('shop')
            ])
        @endif
    </div>
</div>
