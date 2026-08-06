@extends('customer.account.layout')

@section('title', 'My Dashboard')

@section('account_content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Hello, {{ $user->name }}!</h5>
                    <p class="card-text">Welcome back to your Shopcalm dashboard. Today is {{ date('F j, Y') }}.</p>
                </div>
            </div>
        </div>
    </div>

    @include('customer.account.components.summary-cards', ['stats' => $stats])

    <div class="row mt-4">
        <div class="col-md-8">
            @include('customer.account.components.recent-orders', ['recentOrders' => $recentOrders])
            @include('customer.account.components.recent-reviews', ['recentReviews' => $recentReviews])
        </div>
        <div class="col-md-4">
            @include('customer.account.components.profile-card', ['user' => $user])
            @include('customer.account.components.recent-wishlist', ['recentWishlistItems' => $recentWishlistItems])
        </div>
    </div>

    @include('customer.account.components.recommended-products', ['recommendedProducts' => $recommendedProducts])
@endsection
