@extends('layouts.customer')

@section('content')
<div class="container px-2 px-sm-3 my-2 my-md-4">
    {{-- 📱 Mobile Horizontal Navigation Chips (Hidden in Desktop Mode) --}}
    <div class="d-block d-md-none mb-3">
        <div class="d-flex align-items-center gap-1.5 overflow-x-auto py-1 px-0.5 text-nowrap no-scrollbar" 
             style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
            @php 
                $activeTabClass = 'text-white shadow-xs';
                $inactiveTabClass = 'bg-white border text-dark shadow-2xs';
            @endphp
            
            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('dashboard') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('dashboard') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-speedometer2 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-primary' }}"></i>
                <span>Dashboard</span>
            </a>

            {{-- My Orders --}}
            <a href="{{ route('account.orders.index') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('account.orders.*') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('account.orders.*') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-box-seam {{ request()->routeIs('account.orders.*') ? 'text-white' : 'text-primary' }}"></i>
                <span>My Orders</span>
            </a>

            {{-- Wishlist --}}
            <a href="{{ route('wishlist.index') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('wishlist.*') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('wishlist.*') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-heart-fill {{ request()->routeIs('wishlist.*') ? 'text-white' : 'text-danger' }}"></i>
                <span>Wishlist</span>
            </a>

            {{-- Wallet --}}
            <a href="{{ route('account.wallet') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('account.wallet*') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('account.wallet*') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-wallet2 {{ request()->routeIs('account.wallet*') ? 'text-white' : 'text-success' }}"></i>
                <span>Wallet</span>
            </a>

            {{-- Addresses --}}
            <a href="{{ route('account.addresses.index') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('account.addresses.*') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('account.addresses.*') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-geo-alt-fill {{ request()->routeIs('account.addresses.*') ? 'text-white' : 'text-info' }}"></i>
                <span>Addresses</span>
            </a>

            {{-- Reviews --}}
            <a href="{{ route('account.reviews') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('account.reviews') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('account.reviews') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-star-fill {{ request()->routeIs('account.reviews') ? 'text-white' : 'text-warning' }}"></i>
                <span>Reviews</span>
            </a>

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}" 
               class="btn rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none {{ request()->routeIs('profile.edit') ? $activeTabClass : $inactiveTabClass }}" 
               style="font-size: 0.78rem; {{ request()->routeIs('profile.edit') ? 'background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;' : 'border-color: #e2e8f0 !important;' }}">
                <i class="bi bi-person-gear {{ request()->routeIs('profile.edit') ? 'text-white' : 'text-secondary' }}"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        {{-- Desktop / Laptop Sidebar (Visible in Desktop Mode) --}}
        <div class="col-md-4 col-lg-3 d-none d-md-block">
            @include('customer.account.components.sidebar')
        </div>
        
        {{-- Main Content --}}
        <div class="col-md-8 col-lg-9">
            @include('customer.components.engagement-hub')
            @yield('account_content')
        </div>
    </div>

    @yield('full_width_account_content')
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/account.css') }}">
<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
@endpush
