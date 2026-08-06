@extends('customer.account.layout')

@section('title', 'Change Password')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Change Password</h5>
        </div>
        <div class="card-body">
            @include('profile.partials.update-password-form')
        </div>
    </div>
@endsection
