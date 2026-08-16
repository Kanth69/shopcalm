@extends('admin.layouts.app')

@section('header', 'Customer Management')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card border-0 rounded-4 shadow text-white" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); transition: transform 0.2s;">
            <div class="card-body text-center py-4">
                <i class="bi bi-people-fill fs-3 mb-2" style="color: rgba(255,255,255,0.8);"></i>
                <h6 class="small mb-1 text-uppercase fw-bold" style="color: rgba(255,255,255,0.75);">Total Customers</h6>
                <h3 class="mb-0 fw-bolder" style="color: #fff;">{{ number_format($stats['total']) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white" style="transition: transform 0.2s;">
            <div class="card-body text-center py-4">
                <i class="bi bi-person-plus text-primary fs-4 mb-2"></i>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Today</h6>
                <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['today']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white">
            <div class="card-body text-center py-4">
                <i class="bi bi-clock-history text-info fs-4 mb-2"></i>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Yesterday</h6>
                <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['yesterday']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white">
            <div class="card-body text-center py-4">
                <i class="bi bi-calendar-week text-success fs-4 mb-2"></i>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">This Week</h6>
                <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['this_week']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white">
            <div class="card-body text-center py-4">
                <i class="bi bi-calendar-month text-warning fs-4 mb-2"></i>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">This Month</h6>
                <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['this_month']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 rounded-4 shadow-sm bg-white">
            <div class="card-body text-center py-4">
                <i class="bi bi-calendar-check text-secondary fs-4 mb-2"></i>
                <h6 class="text-muted small mb-1 text-uppercase fw-bold">Last Month</h6>
                <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['last_month']) }}</h4>
            </div>
        </div>
    </div>
</div>

<style>
    .card.rounded-4:hover {
        transform: translateY(-3px);
    }
    .table-hover tbody tr {
        transition: all 0.2s ease-in-out;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.002);
    }
    .action-btn {
        transition: transform 0.2s;
    }
    .action-btn:hover {
        transform: scale(1.1);
    }
</style>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.customers.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by Name, Email, or Mobile..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="filter" class="form-select">
                        <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All Time</option>
                        <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('filter') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('filter') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('filter') == 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="az" {{ request('sort') == 'az' ? 'selected' : '' }}>A-Z</option>
                        <option value="za" {{ request('sort') == 'za' ? 'selected' : '' }}>Z-A</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-light w-100">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Customer ID</th>
                        <th>Customer</th>
                        <th>Contact Information</th>
                        <th class="text-center">Email Verified</th>
                        <th class="text-center">Status</th>
                        <th>Registered On</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr id="customer-row-{{ $customer->id }}">
                        <td class="ps-3 fw-medium text-muted">#{{ $customer->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $customer->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($customer->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $customer->name }}" class="rounded-circle shadow-sm me-3" style="width: 42px; height: 42px; object-fit: cover;">
                                <div class="fw-bold text-dark">{{ $customer->name }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $customer->email ?? 'N/A' }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone-fill me-1 opacity-50"></i>{{ $customer->mobile_number }}</div>
                        </td>
                        <td class="text-center">
                            @if($customer->email_verified_at)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Verified</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Unverified</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span id="status-badge-{{ $customer->id }}" class="badge rounded-pill px-3 py-1 {{ $customer->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $customer->status }}
                            </span>
                        </td>
                        <td class="small text-muted">
                            <i class="bi bi-calendar3 me-1 opacity-50"></i>{{ $customer->created_at ? $customer->created_at->format('d M, Y') : 'N/A' }}<br>
                            <i class="bi bi-clock me-1 opacity-50"></i>{{ $customer->created_at ? $customer->created_at->format('h:i A') : '' }}
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-light border action-btn rounded-circle" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" title="View">
                                    <i class="bi bi-eye text-info"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-light border action-btn rounded-circle btn-ajax-toggle" data-id="{{ $customer->id }}" data-url="{{ route('admin.customers.toggle-status', $customer) }}" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" title="{{ $customer->status === 'Active' ? 'Block' : 'Unblock' }}">
                                    <i id="toggle-icon-{{ $customer->id }}" class="bi {{ $customer->status === 'Active' ? 'bi-slash-circle text-warning' : 'bi-check-circle text-success' }}"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border action-btn rounded-circle btn-ajax-delete" data-id="{{ $customer->id }}" data-url="{{ route('admin.customers.destroy', $customer) }}" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;" title="Delete">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-3"></i>
                            No customer records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white border-top-0 py-3">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Helper for SweetAlert toasts
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    function showToast(message, type = 'success') {
        Toast.fire({
            icon: type,
            title: message
        });
    }

    // Toggle Status
    document.querySelectorAll('.btn-ajax-toggle').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const url = this.dataset.url;
            const icon = document.getElementById(`toggle-icon-${id}`);
            const badge = document.getElementById(`status-badge-${id}`);
            const originalTitle = this.getAttribute('title');

            this.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    // Update Badge
                    badge.textContent = data.status;
                    if (data.status === 'Active') {
                        badge.classList.remove('bg-danger');
                        badge.classList.add('bg-success');
                        icon.className = 'bi bi-slash-circle text-warning';
                        this.setAttribute('title', 'Block');
                    } else {
                        badge.classList.remove('bg-success');
                        badge.classList.add('bg-danger');
                        icon.className = 'bi bi-check-circle text-success';
                        this.setAttribute('title', 'Unblock');
                    }
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Error occurred', 'error');
                }
            })
            .catch(err => {
                this.disabled = false;
                showToast('Server error', 'error');
            });
        });
    });

    // Delete Customer
    document.querySelectorAll('.btn-ajax-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to delete this customer. This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const id = this.dataset.id;
                    const url = this.dataset.url;
                    const row = document.getElementById(`customer-row-${id}`);

                    this.disabled = true;

                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            row.style.transition = 'opacity 0.4s, transform 0.4s';
                            row.style.opacity = '0';
                            row.style.transform = 'scale(0.95)';
                            setTimeout(() => row.remove(), 400);
                            showToast(data.message, 'success');
                        } else {
                            this.disabled = false;
                            showToast(data.message || 'Error occurred', 'error');
                        }
                    })
                    .catch(err => {
                        this.disabled = false;
                        showToast('Server error', 'error');
                    });
                }
            });
        });
    });
});
</script>
@endsection
