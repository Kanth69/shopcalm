@extends('customer.account.layout')

@section('title', 'My Orders')

@section('account_content')
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('account.orders.index') }}">
                <div class="row g-2">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search by Order #" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-5">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>Packed</option>
                            <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">My Orders</h5>
        </div>
        <div class="card-body p-0">
            @include('components.skeleton-loader', ['count' => 1, 'type' => 'table'])

            <div class="content-loaded d-none">
                @if($orders->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Order #</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $order->order_number }}</td>
                                        <td>{{ $order->created_at->format('d M, Y') }}</td>
                                        <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @include('customer.components.order-status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="text-end pe-3"><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View Details</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    @include('customer.account.components.empty-state', [
                        'icon' => 'bi-box-seam',
                        'title' => 'No Orders Found',
                        'message' => 'You haven\'t placed any orders matching your criteria.',
                        'button_text' => 'Start Shopping',
                        'button_url' => route('shop')
                    ])
                @endif
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white py-3 content-loaded d-none">
                {{ $orders->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
@endsection
