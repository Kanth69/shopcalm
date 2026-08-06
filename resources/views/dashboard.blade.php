@extends('customer.account.layout')

@section('title', 'My Dashboard')

@section('account_content')
    {{-- The content is now in customer.account.dashboard, this file just extends the layout --}}
    @include('customer.account.dashboard')
@endsection
