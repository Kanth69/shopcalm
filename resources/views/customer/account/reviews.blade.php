@extends('customer.account.layout')

@section('title', 'My Reviews')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Reviews</h5>
        </div>
        <div class="card-body p-0">
             @if($reviews->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                                <tr>
                                    <td>
                                        <a href="{{ route('product.show', $review->product->slug) }}">{{ $review->product->name }}</a>
                                    </td>
                                    <td class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </td>
                                    <td>{{ $review->title }}</td>
                                    <td>{{ $review->created_at->format('d M, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $review->status == 'Approved' ? 'success' : 'warning' }}">
                                            {{ $review->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
        @if($reviews->hasPages())
            <div class="card-footer">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@endsection
