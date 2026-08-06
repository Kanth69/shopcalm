@extends('layouts.customer')

@section('title', 'Shopcalm - Your One-Stop Shop')

@section('content')
    @include('customer.components.home.hero-slider')

    @if(($settings['enable_trust_badges'] ?? '1') == '1')
        @include('customer.components.home.trust-badges', ['settings' => $settings])
    @endif

    @include('customer.components.home.shop-by-category', ['categories' => $categories])

    @if(($settings['enable_flash_sale'] ?? '1') == '1')
        @include('customer.components.home.flash-deals', ['flashProducts' => $flashProducts, 'settings' => $settings])
    @endif

    @include('customer.components.home.featured-products', ['featuredProducts' => $featuredProducts])
    @include('customer.components.home.trending-products', ['trendingProducts' => $trendingProducts])
    @include('customer.components.home.top-brands', ['brands' => $brands])
    @include('customer.components.home.new-arrivals', ['latestProducts' => $latestProducts])

    @include('customer.components.home.testimonials')

    @include('customer.components.home.newsletter')
@endsection
