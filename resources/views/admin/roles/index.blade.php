@extends('admin.layouts.app')

@section('header', 'Admin User & Roles Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Admin Users</li>
@endsection

@section('actions')
    @can('manage-admins', App\Models\User::class)
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary rounded-pill px-3">
            <i class="bi bi-person-plus-fill me-1"></i> Add Admin User
        </a>
    @endcan
@endsection

@section('content')
<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Administrative Staff</div>
                    <div class="fs-4 fw-bold text-dark">{{ $admins->count() }} Accounts</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-shield-lock-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Super Administrators</div>
                    <div class="fs-4 fw-bold text-dark">{{ $admins->where('role_id', \App\Models\User::ROLE_SUPER_ADMIN)->count() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-person-badge-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">System Administrators</div>
                    <div class="fs-4 fw-bold text-dark">{{ $admins->where('role_id', \App\Models\User::ROLE_ADMIN)->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin Users Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-shaded text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Administrative Accounts Registry</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total: {{ $admins->count() }} Accounts
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Admin User</th>
                        <th>Email Address</th>
                        <th>Mobile Number</th>
                        <th>Security Role</th>
                        <th>Last Login</th>
                        <th class="pe-4 text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr id="admin-row-{{ $admin->id }}">
                        <!-- User & Avatar -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                     style="width: 40px; height: 40px; background: {{ $admin->role_id == \App\Models\User::ROLE_SUPER_ADMIN ? '#ef4444' : '#3b82f6' }}; font-size: 0.85rem;">
                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $admin->name }}
                                        @if($admin->id === auth()->id())
                                            <span class="badge bg-light text-primary border ms-1" style="font-size: 0.65rem;">You</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.72rem;">Account ID: #{{ $admin->id }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td>
                            <div class="text-dark small"><i class="bi bi-envelope me-1.5 text-secondary"></i>{{ $admin->email }}</div>
                        </td>

                        <!-- Mobile -->
                        <td>
                            <div class="text-dark small"><i class="bi bi-telephone me-1.5 text-secondary"></i>{{ $admin->mobile_number }}</div>
                        </td>

                        <!-- Role Badge -->
                        <td>
                            @if($admin->role_id == \App\Models\User::ROLE_SUPER_ADMIN)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-shield-fill-check me-1"></i> Super Admin
                                </span>
                            @elseif($admin->role_id == \App\Models\User::ROLE_ADMIN)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1">
                                    <i class="bi bi-person-badge me-1"></i> Admin
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                    Customer
                                </span>
                            @endif
                        </td>

                        <!-- Last Login -->
                        <td class="small text-muted">
                            @if($admin->last_login_at)
                                <i class="bi bi-clock-history me-1 text-secondary"></i>{{ $admin->last_login_at->diffForHumans() }}
                            @else
                                <span class="text-secondary italic">Never</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            @can('manage-admins', App\Models\User::class)
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.roles.edit', $admin) }}" class="btn btn-outline-primary" title="Edit Admin">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($admin->id !== auth()->id())
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteAdmin({{ $admin->id }}, '{{ route('admin.roles.destroy', $admin) }}', '{{ $admin->name }}')" title="Revoke Access">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endif
                                </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Admin Users Found</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteAdmin(id, deleteUrl, name) {
    Swal.fire({
        title: 'Revoke Admin Access?',
        text: `Are you sure you want to revoke administrative access for "${name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Revoke Access',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteUrl;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
