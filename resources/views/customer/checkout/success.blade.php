@extends('layouts.customer')

@section('title', 'Order Successful - Shopcalm')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card">
                <div class="card-body py-5">
                    <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
                    <h1 class="card-title">Thank You For Your Order!</h1>
                    <p class="card-text text-muted">Your order has been placed successfully.</p>
                    <p>Your order number is: <strong>{{ $order->order_number }}</strong></p>
                    <p>We have sent an email to <strong>{{ $order->shipping_email }}</strong> with your order details.</p>
                    <div class="mt-4">
                        <a href="{{ route('shop') }}" class="btn btn-primary">Continue Shopping</a>
                        <a href="{{ route('account.orders.show', $order) }}" class="btn btn-outline-secondary">View Order Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
