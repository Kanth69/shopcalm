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
<!-- KPI Summary Cards for Roles & Account Status -->
@php
    $totalCount = $admins->count();
    $activeCount = $admins->where('status', 'Active')->count();
    $blockedCount = $admins->where('status', 'Blocked')->count();
    $superAdminCount = $admins->where('role_id', \App\Models\User::ROLE_SUPER_ADMIN)->count();
    $adminCount = $admins->where('role_id', \App\Models\User::ROLE_ADMIN)->count();
    $productManagerCount = $admins->where('role_id', \App\Models\User::ROLE_PRODUCT_MANAGER)->count();
    $orderManagerCount = $admins->where('role_id', \App\Models\User::ROLE_ORDER_MANAGER)->count();
    $supportCount = $admins->where('role_id', \App\Models\User::ROLE_SUPPORT)->count();
    $deliveryPartnerCount = $admins->where('role_id', \App\Models\User::ROLE_DELIVERY_PARTNER)->count();
    $deptStaffCount = $productManagerCount + $orderManagerCount + $supportCount + $deliveryPartnerCount;
@endphp

<div class="row g-3 mb-4">
    {{-- 1. Total Staff --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="resetFilters()" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Staff</div>
            </div>
            <div class="fs-4 fw-bolder text-dark">{{ $totalCount }} <small class="text-muted fs-6 fw-normal">Users</small></div>
        </div>
    </div>

    {{-- 2. Active Accounts --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByStatus('Active')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Status</div>
            </div>
            <div class="fs-4 fw-bolder text-success">{{ $activeCount }} <small class="text-muted fs-6 fw-normal">Active</small></div>
        </div>
    </div>

    {{-- 3. Blocked Accounts --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterByStatus('Blocked')" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                    <i class="bi bi-slash-circle-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Blocked</div>
            </div>
            <div class="fs-4 fw-bolder text-danger">{{ $blockedCount }} <small class="text-muted fs-6 fw-normal">Locked</small></div>
        </div>
    </div>

    {{-- 4. Super Administrators --}}
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

    {{-- 5. System Administrators --}}
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

    {{-- 6. Departmental Staff --}}
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 kpi-card cursor-pointer" onclick="filterDepartmental()" style="transition: all 0.2s ease;">
            <div class="d-flex align-items-center mb-2.5" style="gap: 0.75rem !important;">
                <div class="rounded-3 p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #f3e8ff; color: #7e22ce;">
                    <i class="bi bi-grid-fill fs-5"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase text-truncate" style="font-size: 0.72rem; letter-spacing: 0.5px;">Dept Staff</div>
            </div>
            <div class="fs-4 fw-bolder" style="color: #7e22ce;">{{ $deptStaffCount }}</div>
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
        
        {{-- Role & Status Filter Pills Bar --}}
        <div class="d-flex align-items-center flex-wrap" style="gap: 0.4rem !important;">
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 role-filter-btn active" data-role="all" onclick="filterByRole('all')">
                All ({{ $totalCount }})
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
            @if($deliveryPartnerCount > 0)
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 role-filter-btn" data-role="7" onclick="filterByRole('7')">
                Delivery ({{ $deliveryPartnerCount }})
            </button>
            @endif

            <span class="text-muted mx-1">|</span>

            <button type="button" class="btn btn-sm btn-dark rounded-pill px-2.5 status-filter-btn active" data-status="all" onclick="filterByStatus('all')">
                Status: All
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 text-success status-filter-btn" data-status="Active" onclick="filterByStatus('Active')">
                <i class="bi bi-check-circle-fill me-1"></i>Active ({{ $activeCount }})
            </button>
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 text-danger status-filter-btn" data-status="Blocked" onclick="filterByStatus('Blocked')">
                <i class="bi bi-slash-circle-fill me-1"></i>Blocked ({{ $blockedCount }})
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
                        <th>Account Status</th>
                        <th>Last Login</th>
                        <th class="pe-4 text-end" style="width: 150px;">Actions</th>
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
                            \App\Models\User::ROLE_DELIVERY_PARTNER => '#059669',
                            default => '#64748b'
                        };
                    @endphp
                    <tr class="admin-row" data-role="{{ $admin->role_id }}" data-status="{{ $admin->status }}" id="admin-row-{{ $admin->id }}">
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
                            @elseif($admin->role_id == \App\Models\User::ROLE_DELIVERY_PARTNER)
                                <span class="badge rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0; gap: 0.35rem !important;">
                                    <i class="bi bi-bicycle"></i> Delivery Partner
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1.5">
                                    Staff User
                                </span>
                            @endif
                        </td>

                        <!-- Account Status -->
                        <td>
                            @if($admin->status === 'Active')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="gap: 0.35rem !important;">
                                    <i class="bi bi-check-circle-fill"></i> Active
                                </span>
                            @elseif($admin->status === 'Blocked')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center" style="gap: 0.35rem !important;">
                                    <i class="bi bi-slash-circle-fill"></i> Blocked
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1.5">
                                    {{ $admin->status }}
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
                                <div class="d-flex align-items-center justify-content-end" style="gap: 0.4rem !important;">
                                    {{-- Edit Staff Details --}}
                                    <a href="{{ route('admin.roles.edit', $admin) }}" class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Edit Staff Member">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    @if($admin->id !== auth()->id())
                                        {{-- Block / Unblock Toggle Button --}}
                                        @if($admin->status === 'Active')
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="confirmToggleStatus({{ $admin->id }}, '{{ route('admin.roles.toggle-status', $admin) }}', '{{ addslashes($admin->name) }}', 'block')" title="Block Account">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="confirmToggleStatus({{ $admin->id }}, '{{ route('admin.roles.toggle-status', $admin) }}', '{{ addslashes($admin->name) }}', 'unblock')" title="Unblock Account">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        @endif

                                        {{-- Delete / Revoke Access Button --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="confirmDeleteAdmin({{ $admin->id }}, '{{ route('admin.roles.destroy', $admin) }}', '{{ addslashes($admin->name) }}')" title="Delete Account">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-light text-primary border px-2 py-1 small" style="font-size: 0.7rem;">Your Account</span>
                                    @endif
                                </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Admin Users Found</h6>
                        </td>
                    </tr>
                    @endforelse
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-funnel text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No accounts match the selected filter criteria.</h6>
                        </td>
                    </tr>
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
.status-filter-btn.active {
    background-color: #1e293b !important;
    border-color: #1e293b !important;
    color: #ffffff !important;
}
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentRoleFilter = 'all';
let currentStatusFilter = 'all';

function filterByRole(roleId) {
    currentRoleFilter = String(roleId);
    document.querySelectorAll('.role-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-role') === currentRoleFilter) {
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-light', 'border');
        } else {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-light', 'border');
        }
    });
    applyFilters();
}

function filterByStatus(status) {
    currentStatusFilter = status;
    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-status') === currentStatusFilter) {
            btn.classList.add('active', 'btn-dark');
            btn.classList.remove('btn-light', 'border');
        } else {
            btn.classList.remove('active', 'btn-dark');
            btn.classList.add('btn-light', 'border');
        }
    });
    applyFilters();
}

function filterDepartmental() {
    currentRoleFilter = 'dept';
    document.querySelectorAll('.role-filter-btn').forEach(btn => {
        btn.classList.remove('active', 'btn-primary');
        btn.classList.add('btn-light', 'border');
    });
    applyFilters();
}

function resetFilters() {
    filterByRole('all');
    filterByStatus('all');
}

function applyFilters() {
    const rows = document.querySelectorAll('#adminTable tbody tr.admin-row');
    let visibleCount = 0;
    rows.forEach(row => {
        const rowRole = row.getAttribute('data-role');
        const rowStatus = row.getAttribute('data-status');

        let roleMatch = false;
        if (currentRoleFilter === 'all') {
            roleMatch = true;
        } else if (currentRoleFilter === 'dept') {
            roleMatch = ['4', '5', '6', '7'].includes(rowRole);
        } else {
            roleMatch = (rowRole === currentRoleFilter);
        }

        const statusMatch = (currentStatusFilter === 'all' || rowStatus === currentStatusFilter);

        if (roleMatch && statusMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById('noResultsRow');
    if (noResultsRow) {
        noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
    }
}

function confirmToggleStatus(id, toggleUrl, name, action) {
    const isBlock = action === 'block';
    Swal.fire({
        title: isBlock ? 'Block Staff Account?' : 'Unblock Staff Account?',
        text: isBlock 
            ? `Are you sure you want to block "${name}"? This staff member will be immediately signed out and blocked from logging into all administrative and staff portals.`
            : `Are you sure you want to unblock "${name}"? Operational login access will be restored immediately across all portals.`,
        icon: isBlock ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: isBlock ? '#f59e0b' : '#10b981',
        cancelButtonColor: '#64748b',
        confirmButtonText: isBlock ? 'Yes, Block Account' : 'Yes, Unblock Account',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = toggleUrl;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmDeleteAdmin(id, deleteUrl, name) {
    Swal.fire({
        title: 'Delete Staff Account?',
        text: `Are you sure you want to permanently delete "${name}"? This will revoke all administrative permissions and remove this staff account.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete Account',
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
