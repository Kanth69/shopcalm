@extends('customer.account.layout')

@section('title', 'Edit Address')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Address</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('account.addresses.update', $address) }}" method="POST">
                @csrf
                @method('PUT')
                @include('customer.account.addresses.form')
                <button type="submit" class="btn btn-primary">Update Address</button>
                <a href="{{ route('account.addresses.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
