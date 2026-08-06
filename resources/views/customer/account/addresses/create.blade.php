@extends('customer.account.layout')

@section('title', 'Add New Address')

@section('account_content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Address</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('account.addresses.store') }}" method="POST">
                @csrf
                @include('customer.account.addresses.form')
                <button type="submit" class="btn btn-primary">Save Address</button>
                <a href="{{ route('account.addresses.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
