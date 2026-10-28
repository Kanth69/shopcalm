@extends('customer.account.layout')

@section('title', 'Edit Address')

@section('account_content')

{{-- Hero Banner --}}
<div class="rounded-4 mb-4 p-3 p-sm-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 90px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:48px; height:48px; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
            <i class="bi bi-pencil-fill text-white fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold text-white mb-0">Edit Address</h5>
            <p class="text-white-50 small mb-0">Update your saved delivery details</p>
        </div>
    </div>
    <a href="{{ route('account.addresses.index') }}" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold btn-sm w-100 w-sm-auto text-center">
        <i class="bi bi-arrow-left me-1"></i> Back to Addresses
    </a>
</div>

{{-- Form Card --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="border: 1.5px solid #e2e8f0 !important;">
    <div class="card-body p-3 p-sm-4 p-md-5">
        <form action="{{ route('account.addresses.update', $address) }}" method="POST">
            @csrf
            @method('PUT')

            @include('customer.account.addresses.form', ['address' => $address])

            {{-- Actions --}}
            <div class="d-flex gap-2.5 gap-sm-3 flex-column-reverse flex-sm-row mt-4 pt-2 border-top">
                <a href="{{ route('account.addresses.index') }}"
                   class="btn btn-outline-secondary rounded-pill px-4 py-2.5 fw-semibold w-100 w-sm-auto text-center"
                   style="font-size:0.88rem;">
                    Cancel
                </a>
                <button type="submit" class="btn fw-bold rounded-pill px-4 py-2.5 shadow-xs w-100 w-sm-auto text-center"
                        style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); color:#fff; border:none; font-size:0.88rem;">
                    <i class="bi bi-check2-circle me-1.5"></i>Update Address
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
