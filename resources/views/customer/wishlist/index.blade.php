@extends('customer.account.layout')

@section('title', 'My Wishlist')

@section('account_content')

{{-- Hero Banner --}}
<div class="rounded-4 mb-4 p-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width:52px; height:52px; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
            <i class="bi bi-heart-fill text-white fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold text-white mb-0">My Wishlist</h5>
            <p class="text-white-50 small mb-0">{{ $wishlistItems->count() }} item{{ $wishlistItems->count() !== 1 ? 's' : '' }} saved</p>
        </div>
    </div>
</div>

@if($wishlistItems->isNotEmpty())
    <div class="row g-3" id="wishlist-container">
        @foreach($wishlistItems as $item)
        <div class="col-12" id="wishlist-item-{{ $item->id }}">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="transition: box-shadow 0.2s ease;">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center gap-3 flex-wrap">

                        {{-- Product Image --}}
                        <a href="{{ route('product.show', $item->product->slug) }}" class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $item->product->main_image) }}"
                                 alt="{{ $item->product->name }}"
                                 class="rounded-3 object--fit-cover"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        </a>

                        {{-- Product Info --}}
                        <div class="flex-grow-1 min-w-0">
                            <a href="{{ route('product.show', $item->product->slug) }}"
                               class="fw-bold text-dark text-decoration-none d-block mb-1"
                               style="font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item->product->name }}
                            </a>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                @if($item->product->offer_price)
                                    <span class="fw-bold text-success fs-6">₹{{ number_format($item->product->offer_price, 2) }}</span>
                                    <del class="text-muted small">₹{{ number_format($item->product->selling_price, 2) }}</del>
                                    @php
                                        $saving = $item->product->selling_price - $item->product->offer_price;
                                        $pct = round(($saving / $item->product->selling_price) * 100);
                                    @endphp
                                    <span class="badge rounded-pill fw-bold px-2 py-1" style="background:#d1fae5; color:#065f46; font-size:0.7rem;">{{ $pct }}% OFF</span>
                                @else
                                    <span class="fw-bold fs-6" style="color:#1e293b;">₹{{ number_format($item->product->selling_price, 2) }}</span>
                                @endif
                            </div>
                            <div class="mt-1">
                                @if($item->product->stock > 0)
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background:#d1fae5; color:#065f46; font-size:0.7rem;">
                                        <i class="bi bi-check-circle me-1"></i>In Stock
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background:#fee2e2; color:#991b1b; font-size:0.7rem;">
                                        <i class="bi bi-x-circle me-1"></i>Out of Stock
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex gap-2 flex-shrink-0 flex-wrap">
                            <form action="{{ route('wishlist.move-to-cart', $item->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="btn btn-sm fw-semibold rounded-pill px-4"
                                        style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color: #fff; border: none;"
                                        @if($item->product->stock <= 0) disabled @endif>
                                    <i class="bi bi-cart-plus me-1"></i>Move to Cart
                                </button>
                            </form>
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold"
                                    onclick="removeWishlistItem({{ $item->id }}, '{{ route('wishlist.remove', $item->id) }}', this)"
                                    title="Remove from Wishlist">
                                <i class="bi bi-trash me-1"></i>Remove
                            </button>
                        </div>

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
            .then(() => {
                const card = document.getElementById('wishlist-item-' + id);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity   = '0';
                    card.style.transform = 'translateX(20px)';
                    setTimeout(() => card.remove(), 300);
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
