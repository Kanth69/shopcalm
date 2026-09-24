<div class="card border-0 shadow-xs rounded-4 overflow-hidden mb-3.5" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-danger" style="width: 30px; height: 30px; background: #fce7f3;">
                <i class="bi bi-heart-fill"></i>
            </div>
            <h6 class="mb-0 fw-bolder text-dark" style="font-size: 0.92rem;">Recent Wishlist</h6>
        </div>
        <a href="{{ route('wishlist.index') }}" class="small fw-semibold text-danger text-decoration-none" style="font-size: 0.78rem;">
            View All <i class="bi bi-arrow-right ms-0.5"></i>
        </a>
    </div>
    
    <div class="card-body p-3">
        @if($recentWishlistItems->isNotEmpty())
            <div class="d-flex flex-column gap-2">
                @foreach($recentWishlistItems as $item)
                    @php $prod = $item->product; @endphp
                    @if($prod)
                    <div class="d-flex align-items-center gap-2.5 p-2 rounded-3 wishlist-hover-item" 
                         style="background: #f8fafc; border: 1px solid #f1f5f9; transition: all 0.2s;">
                        <a href="{{ route('product.show', $prod->slug) }}" class="flex-shrink-0">
                            @if($prod->main_image)
                                <img src="{{ asset('storage/' . $prod->main_image) }}" alt="{{ $prod->name }}" 
                                     class="rounded-3" style="width: 46px; height: 46px; object-fit: contain; background: #ffffff; border: 1px solid #e2e8f0;">
                            @else
                                <div class="rounded-3 d-flex align-items-center justify-content-center bg-white" 
                                     style="width: 46px; height: 46px; border: 1px solid #e2e8f0;">
                                    <i class="bi bi-image text-muted opacity-25"></i>
                                </div>
                            @endif
                        </a>
                        <div class="flex-grow-1 overflow-hidden">
                            <a href="{{ route('product.show', $prod->slug) }}" class="text-decoration-none text-dark fw-semibold d-block text-truncate" 
                               style="font-size: 0.82rem;" title="{{ $prod->name }}">
                                {{ $prod->name }}
                            </a>
                            <div class="fw-bolder text-primary font-monospace" style="font-size: 0.82rem;">
                                ₹{{ number_format($prod->final_price ?? $prod->price, 2) }}
                            </div>
                        </div>
                        <a href="{{ route('product.show', $prod->slug) }}" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center text-primary flex-shrink-0" 
                           style="width: 28px; height: 28px; font-size: 0.76rem; background: #ffffff;" title="View Product">
                            <i class="bi bi-arrow-right-short fs-6"></i>
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="p-3 text-center">
                @include('customer.account.components.empty-state', [
                    'icon'        => 'bi-heart',
                    'title'       => 'Wishlist Empty',
                    'message'     => 'Save items you want to buy later.',
                    'button_text' => 'Discover Products',
                    'button_url'  => route('shop')
                ])
            </div>
        @endif
    </div>
</div>

<style>
.wishlist-hover-item:hover {
    background: #ffffff !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
    border-color: #e2e8f0 !important;
}
</style>
