@extends('layouts.customer')

@section('title', \App\Models\Setting::get('store_name', 'ShopCalm') . ' - ' . \App\Models\Setting::get('tagline', 'Your One-Stop Shop'))

@section('content')
    @include('customer.components.home.hero-slider')


    @include('customer.components.home.shop-by-category', ['categories' => $categories])

    @if(isset($activeOffers) && $activeOffers->isNotEmpty())
        @include('customer.components.home.offers-slider', ['offers' => $activeOffers])
    @endif

    @include('customer.components.home.featured-products', ['featuredProducts' => $featuredProducts])
    @include('customer.components.home.trending-products', ['trendingProducts' => $trendingProducts])
    @include('customer.components.home.top-brands', ['brands' => $brands, 'totalBrandsCount' => $totalBrandsCount ?? null])
    @include('customer.components.home.new-arrivals', ['latestProducts' => $latestProducts])

    @include('customer.components.home.explore-more', ['exploreProducts' => $exploreProducts, 'totalExploreCount' => $totalExploreCount])

    @include('customer.components.home.newsletter')
@endsection
