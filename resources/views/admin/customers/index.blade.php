@extends('admin.layouts.app')

@section('header', 'Customer Management')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small mb-1">Total</h6>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body text-center">
                <h6 class="text-muted small mb-1">Today</h6>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['today']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small mb-1">Yesterday</h6>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['yesterday']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small mb-1">This Week</h6>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['this_week']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small mb-1">This Month</h6>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['this_month']) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h6 class="text-muted small mb-1">Last Month</h6>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['last_month']) }}</h4>
            </div>
        </div>
    </div>
</div>

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
                    <tr>
                        <td class="ps-3">#{{ $customer->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $customer->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($customer->name).'&color=7F9CF5&background=EBF4FF' }}" alt="{{ $customer->name }}" class="rounded-circle me-3" style="width: 38px; height: 38px; object-fit: cover;">
                                <div class="fw-bold text-dark">{{ $customer->name }}</div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $customer->email ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $customer->mobile_number }}</div>
                        </td>
                        <td class="text-center">
                            @if($customer->email_verified_at)
                                <span class="badge bg-success-subtle text-success">Verified</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Unverified</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($customer->status === 'Active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Blocked</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $customer->created_at ? $customer->created_at->format('d M, Y') : 'N/A' }}<br>
                            {{ $customer->created_at ? $customer->created_at->format('h:i A') : '' }}
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('admin.customers.toggle-status', $customer) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $customer->status === 'Active' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $customer->status === 'Active' ? 'Block' : 'Unblock' }}">
                                        <i class="bi {{ $customer->status === 'Active' ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
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
            <div class="card-footer bg-white py-3">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
