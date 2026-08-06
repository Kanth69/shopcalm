@extends('customer.account.layout')

@section('title', 'My Orders')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">My Orders</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('account.orders.index') }}">
                <div class="row g-2 mb-4">
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

            @if($orders->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ $order->created_at->format('d M, Y') }}</td>
                                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
                                    <td>{{ strtoupper($order->payment_method) }}</td>
                                    <td>
                                        @include('customer.components.order-status-badge', ['status' => $order->status])
                                    </td>
                                    <td><a href="{{ route('account.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                @include('customer.account.components.empty-state', [
                    'icon' => 'bi-box-seam',
                    'title' => 'No Orders Found',
                    'message' => 'Your search or filter returned no results.',
                    'button_text' => 'Clear Filters',
                    'button_url' => route('account.orders.index')
                ])
            @endif
        </div>
        @if($orders->hasPages())
            <div class="card-footer">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
