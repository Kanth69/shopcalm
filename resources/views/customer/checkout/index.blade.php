@extends('layouts.customer')

@section('title', 'Secure Checkout — ' . \App\Models\Setting::get('store_name', 'ShopCalm'))

@push('styles')
    @include('customer.checkout.partials.styles')
@endpush

@section('content')
@php
    $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
@endphp
<div class="container py-3 py-md-4 px-3 px-sm-3 px-md-4" id="checkout-page-container">
    
    <!-- Top Breadcrumb -->
    <div class="d-flex align-items-center justify-content-between mb-2 mb-md-3">
        <a href="{{ route('cart.index') }}" class="text-decoration-none small text-muted fw-bold d-inline-flex align-items-center">
            <i class="bi bi-arrow-left me-1.5"></i> Back to Shopping Bag
        </a>
    </div>

    <!-- Modern 3-Step Stepper Progress Bar -->
    @include('customer.checkout.partials.stepper')

    <!-- Main Checkout Form -->
    <form action="{{ route('checkout.place-order') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="use_wallet" id="hidden_use_wallet" value="{{ $useWallet ? '1' : '0' }}">
        <input type="hidden" name="shipping_name" id="hidden_shipping_name">
        <input type="hidden" name="shipping_email" id="hidden_shipping_email" value="{{ auth()->user()->email ?? '' }}">
        <input type="hidden" name="shipping_phone" id="hidden_shipping_phone">
        <input type="hidden" name="shipping_house_no" id="hidden_shipping_house_no">
        <input type="hidden" name="shipping_street" id="hidden_shipping_street">
        <input type="hidden" name="shipping_address" id="hidden_shipping_address">
        <input type="hidden" name="shipping_city" id="hidden_shipping_city">
        <input type="hidden" name="shipping_state" id="hidden_shipping_state">
        <input type="hidden" name="shipping_zip" id="hidden_shipping_zip">
        <input type="hidden" name="shipping_country" id="hidden_shipping_country" value="India">
        <input type="hidden" name="shipping_latitude" id="hidden_shipping_latitude">
        <input type="hidden" name="shipping_longitude" id="hidden_shipping_longitude">
        <input type="hidden" name="shipping_landmark" id="hidden_shipping_landmark">
        <input type="hidden" name="notes" id="notes">

        <!-- Unified 2-Column Responsive Layout -->
        <div class="row g-3 g-lg-4" id="checkout-main-grid">
            
            <!-- Left Column: Step Views (Step 1, Step 2, OR Step 3) -->
            <div class="col-12 col-lg-7 col-xl-8">
                <!-- Step 1: Delivery Address & Location-Based Estimated Date -->
                @include('customer.checkout.partials.step1_delivery')

                <!-- Step 2: Offers, Coupons & Wallet Savings -->
                @include('customer.checkout.partials.step2_offers')

                <!-- Step 3: Payment Method Selection -->
                @include('customer.checkout.partials.step3_payment')
            </div>

            <!-- Right Column: Sticky Sidebar Summary & Single Action Button -->
            <div class="col-12 col-lg-5 col-xl-4">
                @include('customer.checkout.partials.sidebar_summary')
            </div>
        </div>
    </form>
</div>

<!-- All Checkout Modals -->
@include('customer.checkout.partials.modals')
@endsection

@include('customer.checkout.partials.scripts')
