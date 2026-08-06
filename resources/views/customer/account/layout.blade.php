@extends('layouts.customer')

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-lg-3">
            @include('customer.account.components.sidebar')
        </div>
        <div class="col-lg-9">
            @include('customer.components.engagement-hub')
            @yield('account_content')
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/account.css') }}">
@endpush
