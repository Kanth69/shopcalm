@extends('admin.layouts.app')

@section('header', 'Admin User & Roles Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Admin Users & Roles</li>
@endsection

@section('actions')
    @can('manage-admins', App\Models\User::class)
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary rounded-pill px-3.5 py-2 shadow-sm d-inline-flex align-items-center fw-semibold" style="gap: 0.5rem !important;">
            <i class="bi bi-person-plus-fill fs-6"></i> Add Admin User
        </a>
    @endcan
@endsection

@section('content')
<!-- KPI Summary Cards for ALL Roles -->
@php
    $superAdminCount = $admins->where('role_id', \App\Models\User::ROLE_SUPER_ADMIN)->count();
    $adminCount = $admins->where('role_id', \App\Models\User::ROLE_ADMIN)->count();
    $productManagerCount = $admins->where('role_id', \App\Models\User::ROLE_PRODUCT_MANAGER)->count();
    $orderManagerCount = $admins->where('role_id', \App\Models\User::ROLE_ORDER_MANAGER)->count();
    $supportCount = $admins->where('role_id', \App\Models\User::ROLE_SUPPORT)->count();
@endphp

<div class="row g-3 mb-4">
    {{-- 1. Total Staff --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByRole('all')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Staff</div>
            </div>
            <div class="fs-4 fw-bolder text-dark">{{ $admins->count() }} <small class="text-muted fs-6 fw-normal">Users</small></div>
        </div>
    </div>

    {{-- 2. Super Administrators --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByRole('1')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Super Admin</div>
            </div>
            <div class="fs-4 fw-bolder text-danger">{{ $superAdminCount }}</div>
        </div>
    </div>

    {{-- 3. System Administrators --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByRole('2')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-badge-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Admins</div>
            </div>
            <div class="fs-4 fw-bolder text-info">{{ $adminCount }}</div>
        </div>
    </div>

    {{-- 4. Product Managers --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByRole('4')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #f3e8ff; color: #7e22ce;">
                    <i class="bi bi-box-seam-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Product Mgrs</div>
            </div>
            <div class="fs-4 fw-bolder" style="color: #7e22ce;">{{ $productManagerCount }}</div>
        </div>
    </div>

    {{-- 5. Order Managers --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByRole('5')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #fef3c7; color: #b45309;">
                    <i class="bi bi-truck fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Order Mgrs</div>
            </div>
            <div class="fs-4 fw-bolder" style="color: #b45309;">{{ $orderManagerCount }}</div>
        </div>
    </div>

    {{-- 6. Customer Support --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByRole('6')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #ccfbf1; color: #0f766e;">
                    <i class="bi bi-headset fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Support Staff</div>
            </div>
            <div class="fs-4 fw-bolder" style="color: #0f766e;">{{ $supportCount }}</div>
        </div>
    </div>
</div>

<!-- Admin Users Registry Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center" style="gap: 0.75rem !important;">
            <div class="rounded-3 d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 32px; height: 32px; background: #ede9fe;">
                <i class="bi bi-shield-shaded fs-5"></i>
            </div>
            <h6 class="mb-0 fw-bold text-dark">Administrative Accounts Registry</h6>
        </div>
        
        {{-- Role Filter Pills Bar --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.4rem !important;">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 role-filter-btn active" data-role="all" onclick="filterByRole('all')">
                All ({{ $admins->count() }})
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 role-filter-btn" data-role="1" onclick="filterByRole('1')">
                Super Admin ({{ $superAdminCount }})
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 role-filter-btn" data-role="2" onclick="filterByRole('2')">
                Admin ({{ $adminCount }})
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 role-filter-btn" data-role="4" onclick="filterByRole('4')">
                Product Mgr ({{ $productManagerCount }})
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 role-filter-btn" data-role="5" onclick="filterByRole('5')">
                Order Mgr ({{ $orderManagerCount }})
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 role-filter-btn" data-role="6" onclick="filterByRole('6')">
                Support ({{ $supportCount }})
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="adminTable">
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
                    @php
                        $avatarBg = match((int)$admin->role_id) {
                            \App\Models\User::ROLE_SUPER_ADMIN => '#ef4444',
                            \App\Models\User::ROLE_ADMIN => '#3b82f6',
                            \App\Models\User::ROLE_PRODUCT_MANAGER => '#7e22ce',
                            \App\Models\User::ROLE_ORDER_MANAGER => '#b45309',
                            \App\Models\User::ROLE_SUPPORT => '#0f766e',
                            default => '#64748b'
                        };
                    @endphp
                    <tr class="admin-row" data-role="{{ $admin->role_id }}" id="admin-row-{{ $admin->id }}">
                        <!-- User & Avatar -->
                        <td class="ps-4">
                            <div class="d-flex align-items-center" style="gap: 1rem !important;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                     style="width: 42px; height: 42px; background: {{ $avatarBg }}; font-size: 0.88rem;">
                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">
                                        {{ $admin->name }}
                                        @if($admin->id === auth()->id())
                                            <span class="badge bg-light text-primary border ms-1.5 px-2 py-0.5" style="font-size: 0.68rem;">You</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.72rem; margin-top: 1px;">Account ID: #{{ $admin->id }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td>
                            <div class="text-dark small d-flex align-items-center" style="gap: 0.5rem !important;">
                                <i class="bi bi-envelope text-secondary"></i>
                                <span>{{ $admin->email }}</span>
                            </div>
                        </td>

                        <!-- Mobile -->
                        <td>
                            <div class="text-dark small d-flex align-items-center" style="gap: 0.5rem !important;">
                                <i class="bi bi-telephone text-secondary"></i>
                                <span>{{ $admin->mobile_number }}</span>
                            </div>
                        </td>

                        <!-- Role Badge -->
                        <td>
                            @if($admin->role_id == \App\Models\User::ROLE_SUPER_ADMIN)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="gap: 0.35rem !important;">
                                    <i class="bi bi-shield-fill-check"></i> Super Admin
                                </span>
                            @elseif($admin->role_id == \App\Models\User::ROLE_ADMIN)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="gap: 0.35rem !important;">
                                    <i class="bi bi-person-badge"></i> Admin
                                </span>
                            @elseif($admin->role_id == \App\Models\User::ROLE_PRODUCT_MANAGER)
                                <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="background-color: #f3e8ff; color: #7e22ce; border: 1px solid #d8b4fe; gap: 0.35rem !important;">
                                    <i class="bi bi-box-seam"></i> Product Manager
                                </span>
                            @elseif($admin->role_id == \App\Models\User::ROLE_ORDER_MANAGER)
                                <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; gap: 0.35rem !important;">
                                    <i class="bi bi-truck"></i> Order Manager
                                </span>
                            @elseif($admin->role_id == \App\Models\User::ROLE_SUPPORT)
                                <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; gap: 0.35rem !important;">
                                    <i class="bi bi-headset"></i> Customer Support
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1.5">
                                    Customer
                                </span>
                            @endif
                        </td>

                        <!-- Last Login -->
                        <td class="small text-muted">
                            @if($admin->last_login_at)
                                <div class="d-flex align-items-center" style="gap: 0.5rem !important;">
                                    <i class="bi bi-clock-history text-secondary"></i>
                                    <span>{{ $admin->last_login_at->diffForHumans() }}</span>
                                </div>
                            @else
                                <span class="text-secondary italic">Never</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            @can('manage-admins', App\Models\User::class)
                                <div class="d-flex align-items-center justify-content-end" style="gap: 0.35rem !important;">
                                    <a href="{{ route('admin.roles.edit', $admin) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit Admin">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($admin->id !== auth()->id())
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="confirmDeleteAdmin({{ $admin->id }}, '{{ route('admin.roles.destroy', $admin) }}', '{{ $admin->name }}')" title="Revoke Access">
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

<style>
.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08) !important;
}
.role-filter-btn.active {
    background-color: #6366f1 !important;
    border-color: #6366f1 !important;
    color: #ffffff !important;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function filterByRole(roleId) {
    // Update active filter button
    document.querySelectorAll('.role-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-role') === String(roleId)) {
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-light', 'border');
        } else {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-light', 'border');
        }
    });

    // Filter table rows
    const rows = document.querySelectorAll('#adminTable tbody tr.admin-row');
    rows.forEach(row => {
        if (roleId === 'all' || row.getAttribute('data-role') === String(roleId)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

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
