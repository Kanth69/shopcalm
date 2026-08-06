@extends('admin.layouts.app')

@section('header', 'Order Details: ' . $order->order_number)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td class="text-center">₹{{ number_format($item->product_price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₹{{ number_format($item->product_price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Actions</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <select name="status" class="form-select">
                            <option value="pending" @if($order->status == 'pending') selected @endif>Pending</option>
                            <option value="confirmed" @if($order->status == 'confirmed') selected @endif>Confirmed</option>
                            <option value="packed" @if($order->status == 'packed') selected @endif>Packed</option>
                            <option value="shipped" @if($order->status == 'shipped') selected @endif>Shipped</option>
                            <option value="out for delivery" @if($order->status == 'out for delivery') selected @endif>Out for Delivery</option>
                            <option value="delivered" @if($order->status == 'delivered') selected @endif>Delivered</option>
                            <option value="cancelled" @if($order->status == 'cancelled') selected @endif>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Customer Details</h5>
            </div>
            <div class="card-body">
                <h6>{{ $order->shipping_name }}</h6>
                <p class="text-muted mb-0">{{ $order->shipping_email }}</p>
                <p class="text-muted">{{ $order->shipping_phone }}</p>
                <hr>
                <h6>Shipping Address</h6>
                <address>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                </address>
            </div>
        </div>
    </div>
</div>
@endsection
