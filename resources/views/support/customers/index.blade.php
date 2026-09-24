@extends('support.layouts.app')

@section('title', 'Customer 360° Directory & Account Status')
@section('header', 'Customer 360° Profiles & Access Control')

@section('actions')
    <a href="{{ route('support.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>
@endsection

@section('content')

<!-- Top KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <a href="{{ route('support.customers.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 {{ !request()->filled('status') ? 'border-primary' : '' }}" style="{{ !request()->filled('status') ? 'border-left: 4px solid #8b5cf6 !important;' : '' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Total Customers</div>
                        <div class="h3 fw-bolder text-dark mb-0 mt-1 font-monospace" id="kpiTotal">{{ $totalCustomers }}</div>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px; background: #f3e8ff; font-size: 1.3rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('support.customers.index', ['status' => 'active']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 {{ request('status') === 'active' ? 'border-success' : '' }}" style="{{ request('status') === 'active' ? 'border-left: 4px solid #10b981 !important;' : '' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Active Accounts</div>
                        <div class="h3 fw-bolder text-success mb-0 mt-1 font-monospace" id="kpiActive">{{ $activeCustomers }}</div>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-success" style="width: 44px; height: 44px; background: #ecfdf5; font-size: 1.3rem;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="{{ route('support.customers.index', ['status' => 'blocked']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 {{ request('status') === 'blocked' ? 'border-danger' : '' }}" style="border-left: 4px solid #ef4444 !important;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem;">Blocked Accounts (Support Action)</div>
                        <div class="h3 fw-bolder text-danger mb-0 mt-1 font-monospace" id="kpiBlocked">{{ $blockedCustomers }}</div>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center text-danger" style="width: 44px; height: 44px; background: #fee2e2; font-size: 1.3rem;">
                        <i class="bi bi-slash-circle-fill"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Search & Status Filter Toolbar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
    <form method="GET" action="{{ route('support.customers.index') }}" class="row g-2 align-items-center">
        <div class="col-md-6 col-lg-7">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0 small" 
                       placeholder="Search by customer name, email address, phone number..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3 col-lg-3">
            <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                <option value="">All Account Statuses</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>✅ Active Only</option>
                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>🚫 Blocked Only</option>
            </select>
        </div>

        <div class="col-md-3 col-lg-2 text-end d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" style="background: #8b5cf6; border-color: #8b5cf6;">Filter</button>
            @if(request()->filled('search') || request()->filled('status'))
                <a href="{{ route('support.customers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Customer Directory Table with Instant Unblock/Block Controls -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small" style="font-size: 0.75rem; letter-spacing: 0.04em;">
                <tr>
                    <th class="ps-4 py-3">Customer Profile</th>
                    <th class="py-3">Contact Details</th>
                    <th class="py-3">Instant Access Control</th>
                    <th class="py-3">Orders</th>
                    <th class="py-3">Member Since</th>
                    <th class="pe-4 py-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($customers as $customer)
                    <tr id="customer-row-{{ $customer->id }}">
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2.5">
                                <div id="avatar-{{ $customer->id }}" class="rounded-circle fw-bold d-flex align-items-center justify-content-center border flex-shrink-0" 
                                     style="width: 38px; height: 38px; font-size: 0.85rem; background: {{ $customer->status === 'Blocked' ? '#fee2e2' : '#f3e8ff' }}; color: {{ $customer->status === 'Blocked' ? '#dc2626' : '#7c3aed' }};">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('support.customers.show', $customer) }}" class="fw-bold text-dark text-decoration-none small d-block">
                                        {{ $customer->name }}
                                    </a>
                                    <div class="text-muted" style="font-size: 0.68rem;">ID: #{{ str_pad($customer->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="small text-dark fw-semibold">{{ $customer->email }}</div>
                            <div class="text-muted small" style="font-size: 0.72rem;">
                                <i class="bi bi-telephone text-success me-0.5"></i> {{ $customer->mobile_number ?? 'No phone' }}
                            </div>
                        </td>

                        <!-- Instant 1-Click Status Toggle Cell -->
                        <td>
                            <div id="status-container-{{ $customer->id }}" class="d-flex align-items-center gap-2">
                                @if($customer->status === 'Blocked')
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5 status-toggle-btn" 
                                            data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" data-current="Blocked"
                                            style="font-size: 0.75rem;">
                                        <i class="bi bi-unlock-fill"></i> Unblock
                                    </button>
                                    <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: #fee2e2; color: #991b1b; font-size: 0.68rem;">
                                        Blocked
                                    </span>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-semibold d-inline-flex align-items-center gap-1 status-toggle-btn" 
                                            data-id="{{ $customer->id }}" data-name="{{ $customer->name }}" data-current="Active"
                                            style="font-size: 0.72rem;">
                                        <i class="bi bi-slash-circle"></i> Block
                                    </button>
                                    <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: #ecfdf5; color: #065f46; font-size: 0.68rem;">
                                        Active
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-primary border px-2.5 py-1 rounded-pill fw-bold small" style="color: #7c3aed !important;">
                                {{ $customer->orders_count }} {{ Str::plural('order', $customer->orders_count) }}
                            </span>
                        </td>

                        <td class="text-muted small" style="font-size: 0.72rem;">
                            {{ $customer->created_at->format('d M Y') }}
                            <div style="font-size: 0.68rem;">({{ $customer->created_at->diffForHumans() }})</div>
                        </td>

                        <td class="pe-4 text-end">
                            <a href="{{ route('support.customers.show', $customer) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1 fw-bold text-dark" style="font-size: 0.75rem;">
                                <span>360° View</span> <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-person-x text-muted opacity-50 display-6 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Customers Found</h6>
                                <p class="text-muted mb-0 small">Try searching with a different name, email, or filter.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="card-footer bg-white border-top p-3">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<!-- Instant AJAX 1-Click Block/Unblock Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.status-toggle-btn');
        if (!btn) return;
        e.preventDefault();

        const customerId = btn.getAttribute('data-id');
        const customerName = btn.getAttribute('data-name');
        const currentStatus = btn.getAttribute('data-current');
        const targetStatus = currentStatus === 'Blocked' ? 'Active' : 'Blocked';

        // Disable button during flight
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch(`/support/customers/${customerId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                reason: `Support agent direct 1-click ${targetStatus === 'Active' ? 'unblock' : 'block'}.`
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById(`status-container-${customerId}`);
                const avatar = document.getElementById(`avatar-${customerId}`);
                const kpiActive = document.getElementById('kpiActive');
                const kpiBlocked = document.getElementById('kpiBlocked');

                if (targetStatus === 'Active') {
                    // Updated to Active
                    container.innerHTML = `
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-0.5 fw-semibold d-inline-flex align-items-center gap-1 status-toggle-btn" 
                                data-id="${customerId}" data-name="${customerName}" data-current="Active"
                                style="font-size: 0.72rem;">
                            <i class="bi bi-slash-circle"></i> Block
                        </button>
                        <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: #ecfdf5; color: #065f46; font-size: 0.68rem;">
                            Active
                        </span>
                    `;
                    if (avatar) {
                        avatar.style.background = '#f3e8ff';
                        avatar.style.color = '#7c3aed';
                    }
                    if (kpiActive && kpiBlocked) {
                        kpiActive.textContent = parseInt(kpiActive.textContent || 0) + 1;
                        kpiBlocked.textContent = Math.max(0, parseInt(kpiBlocked.textContent || 0) - 1);
                    }

                    // Toast
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: `🎉 ${customerName} UNBLOCKED!`,
                            text: 'Customer can now log in and place orders.',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                } else {
                    // Updated to Blocked
                    container.innerHTML = `
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5 status-toggle-btn" 
                                data-id="${customerId}" data-name="${customerName}" data-current="Blocked"
                                style="font-size: 0.75rem;">
                            <i class="bi bi-unlock-fill"></i> Unblock
                        </button>
                        <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: #fee2e2; color: #991b1b; font-size: 0.68rem;">
                            Blocked
                        </span>
                    `;
                    if (avatar) {
                        avatar.style.background = '#fee2e2';
                        avatar.style.color = '#dc2626';
                    }
                    if (kpiActive && kpiBlocked) {
                        kpiActive.textContent = Math.max(0, parseInt(kpiActive.textContent || 0) - 1);
                        kpiBlocked.textContent = parseInt(kpiBlocked.textContent || 0) + 1;
                    }

                    // Toast
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'warning',
                            title: `🚫 ${customerName} BLOCKED`,
                            text: 'Account has been restricted.',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                }
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.textContent = currentStatus === 'Blocked' ? 'Unblock' : 'Block';
            if (typeof Swal !== 'undefined') {
                Swal.fire('Error', 'Could not update status. Please try again.', 'error');
            }
        });
    });
});
</script>

@endsection
