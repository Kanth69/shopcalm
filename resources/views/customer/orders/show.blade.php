@extends('customer.account.layout')

@section('title', 'Order Details - ' . $order->order_number)

@section('account_content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Order Details</h5>
            <a href="{{ route('account.orders.index') }}" class="btn btn-sm btn-outline-secondary">Back to Orders</a>
        </div>
        <div class="card-body">
            @include('customer.components.order-timeline', ['order' => $order])
            <hr>
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Shipping Address</h6>
                    <address>
                        <strong>{{ $order->shipping_name }}</strong><br>
                        {{ $order->shipping_address }}<br>
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                        <abbr title="Phone">P:</abbr> {{ $order->shipping_phone }}
                    </address>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6>Order Summary</h6>
                    <p class="mb-1"><strong>Order #:</strong> {{ $order->order_number }}</p>
                    <p class="mb-1"><strong>Date:</strong> {{ $order->created_at->format('d M, Y') }}</p>
                    <p class="mb-1"><strong>Total:</strong> ₹{{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>

            <h6>Order Items</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Original Price</th>
                                <th class="text-end">Offer</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <h6 class="mb-0 fw-bold">{{ $item->product_name }}</h6>
                                </td>
                                <td class="text-end text-muted text-decoration-line-through">₹{{ number_format($item->original_price, 2) }}</td>
                                <td class="text-end text-success">-₹{{ number_format($item->offer_discount, 2) }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
