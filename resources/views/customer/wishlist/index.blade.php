@extends('customer.account.layout')

@section('title', 'My Wishlist')

@section('account_content')

{{-- Hero Banner --}}
<div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4 overflow-hidden"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
                <i class="bi bi-heart-fill text-white fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-white mb-0.5" style="font-size: clamp(1.1rem, 3vw, 1.3rem);">My Wishlist</h5>
                <p class="text-white-50 small mb-0" style="font-size: 0.8rem;">{{ $wishlistItems->count() }} {{ Str::plural('item', $wishlistItems->count()) }} saved for later</p>
            </div>
        </div>
        <a href="{{ route('shop') }}" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 w-100 w-sm-auto" style="font-size: 0.8rem;">
            <i class="bi bi-bag-plus"></i> <span>Explore More</span>
        </a>
    </div>
</div>

@if($wishlistItems->isNotEmpty())
    <div class="d-flex flex-column gap-3 gap-md-4 mb-4" id="wishlist-container">
        @foreach($wishlistItems as $item)
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden transition-all" id="wishlist-item-{{ $item->id }}" style="background: #ffffff; border: 1.5px solid #e2e8f0 !important;">
            <div class="card-body p-3 p-md-4">
                
                {{-- 💻 Desktop Layout (>= 768px) --}}
                <div class="d-none d-md-flex align-items-center justify-content-between gap-3">
                    {{-- Image + Info --}}
                    <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1">
                        <a href="{{ route('product.show', $item->product->slug) }}" class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $item->product->main_image) }}"
                                 alt="{{ $item->product->name }}"
                                 class="rounded-3 border p-1 bg-white shadow-2xs"
                                 style="width: 76px; height: 76px; object-fit: contain;">
                        </a>

                        <div class="min-w-0 flex-grow-1">
                            @if($item->product->brand)
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.65rem;">
                                    {{ $item->product->brand->name }}
                                </span>
                            @endif
                            <a href="{{ route('product.show', $item->product->slug) }}"
                               class="fw-bold text-dark text-decoration-none d-block mb-1 text-truncate"
                               style="font-size: 0.95rem; line-height: 1.35;" title="{{ $item->product->name }}">
                                {{ $item->product->name }}
                            </a>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($item->product->offer_price)
                                    <span class="fw-bold text-dark font-monospace fs-6">₹{{ number_format($item->product->offer_price, 2) }}</span>
                                    <del class="text-muted small" style="font-size: 0.78rem;">₹{{ number_format($item->product->selling_price, 2) }}</del>
                                    @php
                                        $saving = $item->product->selling_price - $item->product->offer_price;
                                        $pct = round(($saving / $item->product->selling_price) * 100);
                                    @endphp
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-bold px-2 py-0.5" style="font-size: 0.68rem;">{{ $pct }}% OFF</span>
                                @else
                                    <span class="fw-bold font-monospace fs-6 text-dark">₹{{ number_format($item->product->selling_price, 2) }}</span>
                                @endif

                                @if($item->product->stock > 0)
                                    <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background:#d1fae5; color:#065f46; font-size:0.68rem;">
                                        <i class="bi bi-check-circle me-1"></i>In Stock
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background:#fee2e2; color:#991b1b; font-size:0.68rem;">
                                        <i class="bi bi-x-circle me-1"></i>Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <form action="{{ route('wishlist.move-to-cart', $item->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm fw-bold rounded-pill px-3.5 py-2 shadow-xs d-inline-flex align-items-center gap-1.5"
                                    style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #fff; border: none; font-size: 0.82rem;"
                                    @if($item->product->stock <= 0) disabled @endif>
                                <i class="bi bi-bag-plus"></i> <span>Move to Cart</span>
                            </button>
                        </form>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs"
                                style="font-size: 0.82rem;"
                                onclick="removeWishlistItem({{ $item->id }}, '{{ route('wishlist.remove', $item->id) }}', this)"
                                title="Remove from Wishlist">
                            <i class="bi bi-trash3"></i> <span>Remove</span>
                        </button>
                    </div>
                </div>

                {{-- 📱 Mobile Layout (< 768px) --}}
                <div class="d-block d-md-none">
                    {{-- Top Section: Image + Info --}}
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <a href="{{ route('product.show', $item->product->slug) }}" class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $item->product->main_image) }}"
                                 alt="{{ $item->product->name }}"
                                 class="rounded-3 border p-1 bg-white shadow-2xs"
                                 style="width: 70px; height: 70px; object-fit: contain;">
                        </a>

                        <div class="min-w-0 flex-grow-1">
                            @if($item->product->brand)
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 mb-1" style="font-size: 0.64rem;">
                                    {{ $item->product->brand->name }}
                                </span>
                            @endif
                            <a href="{{ route('product.show', $item->product->slug) }}"
                               class="fw-bold text-dark text-decoration-none d-block mb-1 text-truncate"
                               style="font-size: 0.92rem; line-height: 1.35;" title="{{ $item->product->name }}">
                                {{ $item->product->name }}
                            </a>
                            <div class="d-flex align-items-baseline gap-2 flex-wrap mb-1">
                                @if($item->product->offer_price)
                                    <span class="fw-bold text-dark font-monospace" style="font-size: 1rem;">₹{{ number_format($item->product->offer_price, 2) }}</span>
                                    <del class="text-muted small" style="font-size: 0.75rem;">₹{{ number_format($item->product->selling_price, 2) }}</del>
                                    @php
                                        $saving = $item->product->selling_price - $item->product->offer_price;
                                        $pct = round(($saving / $item->product->selling_price) * 100);
                                    @endphp
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-bold px-2 py-0.5" style="font-size: 0.65rem;">{{ $pct }}% OFF</span>
                                @else
                                    <span class="fw-bold font-monospace text-dark" style="font-size: 1rem;">₹{{ number_format($item->product->selling_price, 2) }}</span>
                                @endif
                            </div>
                            <div>
                                @if($item->product->stock > 0)
                                    <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background:#d1fae5; color:#065f46; font-size:0.66rem;">
                                        <i class="bi bi-check-circle me-1"></i>In Stock
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background:#fee2e2; color:#991b1b; font-size:0.66rem;">
                                        <i class="bi bi-x-circle me-1"></i>Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Action Row --}}
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <form action="{{ route('wishlist.move-to-cart', $item->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit"
                                    class="btn btn-sm fw-bold rounded-pill px-3 py-2 w-100 shadow-xs d-inline-flex align-items-center justify-content-center gap-1.5"
                                    style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #fff; border: none; font-size: 0.8rem;"
                                    @if($item->product->stock <= 0) disabled @endif>
                                <i class="bi bi-bag-plus"></i> <span>Move to Cart</span>
                            </button>
                        </form>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-semibold shadow-xs d-inline-flex align-items-center justify-content-center gap-1"
                                style="font-size: 0.8rem; min-width: 90px;"
                                onclick="removeWishlistItem({{ $item->id }}, '{{ route('wishlist.remove', $item->id) }}', this)"
                                title="Remove from Wishlist">
                            <i class="bi bi-trash3"></i> <span>Remove</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        @endforeach
    </div>

@else
    @include('customer.account.components.empty-state', [
        'icon'        => 'bi-heart',
        'title'       => 'Your Wishlist is Empty',
        'message'     => 'Save products you love to your wishlist and shop them later.',
        'button_text' => 'Discover Products',
        'button_url'  => route('shop')
    ])
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function removeWishlistItem(id, url, btn) {
    Swal.fire({
        title: 'Remove from Wishlist?',
        text: 'This item will be removed from your wishlist.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel',
        borderRadius: '16px'
    }).then(result => {
        if (!result.isConfirmed) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Removing...';

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');

        fetch(url, { method: 'POST', body: formData })
            .then(async res => {
                if (!res.ok) throw new Error('Failed to remove item.');
                return res.json().catch(() => ({}));
            })
            .then((data) => {
                const card = document.getElementById('wishlist-item-' + id);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity   = '0';
                    card.style.transform = 'translateX(20px)';
                    setTimeout(() => card.remove(), 300);
                }
                if (typeof updateWishlistBadge === 'function' && data && typeof data.wishlist_count !== 'undefined') {
                    updateWishlistBadge(data.wishlist_count);
                }
                Swal.fire({ icon: 'success', title: 'Removed!', text: 'Item removed from your wishlist.', toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-1"></i>Remove';
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
    });
}
</script>
@endpush

@endsection
