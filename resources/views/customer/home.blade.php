@extends('layouts.customer')

@section('title', \App\Models\Setting::get('store_name', 'ShopCalm') . ' - ' . \App\Models\Setting::get('tagline', 'Your One-Stop Shop'))

@section('content')
    @include('customer.components.home.hero-slider')

    @if(($settings['enable_trust_badges'] ?? '1') == '1')
        @include('customer.components.home.trust-badges', ['settings' => $settings])
    @endif

    @include('customer.components.home.shop-by-category', ['categories' => $categories])

    @if(isset($activeFlashDeal) && !empty($flashProducts) && count($flashProducts) > 0)
        @include('customer.components.home.flash-deals', ['activeFlashDeal' => $activeFlashDeal, 'flashProducts' => $flashProducts])
    @endif

    @include('customer.components.home.featured-products', ['featuredProducts' => $featuredProducts])
    @include('customer.components.home.trending-products', ['trendingProducts' => $trendingProducts])
    @include('customer.components.home.top-brands', ['brands' => $brands])
    @include('customer.components.home.new-arrivals', ['latestProducts' => $latestProducts])

    @include('customer.components.home.testimonials', ['testimonials' => $testimonials ?? collect()])

    @include('customer.components.home.newsletter')
@endsection
