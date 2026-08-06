@extends('customer.account.layout')

@section('title', 'Order Details - ' . $order->order_number)

@section('account_content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order Details</h5>
            <a href="{{ route('account.orders.index') }}" class="btn btn-sm btn-outline-secondary">Back to Orders</a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Order Information</h6>
                    <p><strong>Order #:</strong> {{ $order->order_number }}</p>
                    <p><strong>Date:</strong> {{ $order->created_at->format('d M, Y') }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($order->status) }}</span></p>
                    <p><strong>Total:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div class="col-md-6">
                    <h6>Shipping Address</h6>
                    <p>
                        {{ $order->shipping_name }}<br>
                        {{ $order->shipping_address }}<br>
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                        {{ $order->shipping_country }}<br>
                        <strong>Email:</strong> {{ $order->shipping_email }}<br>
                        <strong>Phone:</strong> {{ $order->shipping_phone }}
                    </p>
                </div>
            </div>

            <h6>Order Items</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>₹{{ number_format($item->product_price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format($item->product_price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
