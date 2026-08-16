@extends('customer.account.layout')

@section('title', 'My Addresses')

@section('account_content')

{{-- Hero Banner --}}
<div class="rounded-4 mb-4 p-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); min-height: 100px;">
    <div class="d-flex align-items-center gap-3">
        <div class="rounded-3 d-flex align-items-center justify-content-center"
             style="width:52px; height:52px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <i class="bi bi-geo-alt-fill text-white fs-4"></i>
        </div>
        <div>
            <h5 class="fw-bold text-white mb-0">Saved Addresses</h5>
            <p class="text-white-50 small mb-0">Manage your delivery addresses</p>
        </div>
    </div>
    <a href="{{ route('account.addresses.create') }}" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Add New Address
    </a>
</div>

{{-- Address Grid --}}
<div id="addresses-container" class="row g-3" style="{{ $addresses->isEmpty() ? 'display:none;' : '' }}">
    @foreach($addresses as $address)
    <div class="col-md-6" id="address-card-{{ $address->id }}">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden"
             style="transition: box-shadow 0.2s ease;">
            <div class="card-body p-4 d-flex flex-column">

                {{-- Icon + Name --}}
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px; height:44px; background:linear-gradient(135deg,#ede9fe,#ddd6fe);">
                        <i class="bi bi-house-fill" style="color:#6d28d9; font-size:1.1rem;"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0" style="font-size:0.95rem;">{{ $address->name }}</h6>
                        <span class="text-muted small"><i class="bi bi-telephone me-1 text-primary"></i>{{ $address->phone }}</span>
                    </div>
                </div>

                {{-- Address Details --}}
                <div class="rounded-3 p-3 mb-3 flex-grow-1" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <p class="text-secondary small mb-0 lh-lg">
                        <i class="bi bi-geo-alt text-primary me-1"></i>
                        {{ $address->address }},<br>
                        {{ $address->city }}, {{ $address->state }} – {{ $address->zip }}<br>
                        <span class="fw-medium">{{ $address->country }}</span>
                    </p>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 pt-2 border-top">
                    <a href="{{ route('account.addresses.edit', $address) }}"
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold flex-fill text-center"
                       style="font-size:0.8rem;">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold flex-fill"
                            style="font-size:0.8rem;"
                            onclick="deleteAddress({{ $address->id }}, '{{ route('account.addresses.destroy', $address) }}', this)">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Empty State --}}
<div id="addresses-empty-state" style="{{ $addresses->isNotEmpty() ? 'display:none;' : '' }}">
    @include('customer.account.components.empty-state', [
        'icon'        => 'bi-geo-alt',
        'title'       => 'No Saved Addresses',
        'message'     => 'You have not saved any addresses yet. Add one to speed up checkout!',
        'button_text' => 'Add New Address',
        'button_url'  => route('account.addresses.create')
    ])
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteAddress(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Address?',
        text: 'Are you sure you want to delete this address?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) performDeleteAddress(id, deleteUrl, btnElement);
    });
}

function performDeleteAddress(id, deleteUrl, btnElement) {
    btnElement.disabled = true;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'DELETE',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete address.');
        return data;
    })
    .then(data => {
        const cardCol = document.getElementById('address-card-' + id);
        if (cardCol) {
            cardCol.style.transition = 'all 0.3s ease';
            cardCol.style.opacity   = '0';
            cardCol.style.transform = 'scale(0.9)';
            setTimeout(() => {
                cardCol.remove();
                const container = document.getElementById('addresses-container');
                if (container && container.querySelectorAll('[id^="address-card-"]').length === 0) {
                    container.style.display = 'none';
                    document.getElementById('addresses-empty-state').style.display = 'block';
                }
            }, 300);
        }
        Swal.fire({ icon: 'success', title: 'Deleted!', text: data.message || 'Address deleted.', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
    })
    .catch(err => {
        btnElement.disabled = false;
        btnElement.innerHTML = '<i class="bi bi-trash me-1"></i>Delete';
        Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to delete address.' });
    });
}
</script>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Done!',
        text: '{{ session('success') }}',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
});
</script>
@endif

@endpush

@endsection
