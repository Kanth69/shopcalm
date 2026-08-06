@extends('customer.account.layout')

@section('title', 'My Addresses')

@section('account_content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Saved Addresses</h5>
            <a href="{{ route('account.addresses.create') }}" class="btn btn-sm btn-primary">Add New Address</a>
        </div>
        <div class="card-body">
            @if($addresses->isNotEmpty())
                <div class="row">
                    @foreach($addresses as $address)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $address->name }}</h6>
                                    <p class="card-text">
                                        {{ $address->address }}, {{ $address->city }}, {{ $address->state }} {{ $address->zip }}<br>
                                        {{ $address->country }}<br>
                                        <strong>Phone:</strong> {{ $address->phone }}
                                    </p>
                                    <a href="{{ route('account.addresses.edit', $address) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @include('customer.account.components.empty-state', [
                    'icon' => 'bi-geo-alt',
                    'title' => 'No Saved Addresses',
                    'message' => 'You have not saved any addresses yet.',
                    'button_text' => 'Add New Address',
                    'button_url' => route('account.addresses.create')
                ])
            @endif
        </div>
    </div>
@endsection
