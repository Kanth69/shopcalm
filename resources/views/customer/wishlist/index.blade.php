@extends('customer.account.layout')

@section('title', 'My Wishlist')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Wishlist</h5>
        </div>
        <div class="card-body p-0">
            @if($wishlistItems->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <tbody>
                            @foreach($wishlistItems as $item)
                                <tr>
                                    <td width="100">
                                        <a href="{{ route('product.show', $item->product->slug) }}">
                                            <img src="{{ asset('storage/' . $item->product->main_image) }}" class="img-fluid rounded" alt="{{ $item->product->name }}">
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('product.show', $item->product->slug) }}" class="text-dark text-decoration-none">{{ $item->product->name }}</a>
                                        <div class="small text-muted">
                                            @if($item->product->offer_price)
                                                <span class="fw-bold text-success">₹{{ number_format($item->product->offer_price, 2) }}</span>
                                                <del class="ms-2">₹{{ number_format($item->product->selling_price, 2) }}</del>
                                            @else
                                                <span class="fw-bold">₹{{ number_format($item->product->selling_price, 2) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($item->product->stock > 0)
                                            <span class="badge bg-success">In Stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <form action="{{ route('wishlist.move-to-cart', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" @if($item->product->stock <= 0) disabled @endif>Move to Cart</button>
                                            </form>
                                            <form action="{{ route('wishlist.remove', $item->id) }}" method="POST" class="ms-2">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">&times;</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
@endsection
