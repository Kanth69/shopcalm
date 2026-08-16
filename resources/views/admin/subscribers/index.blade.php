@extends('admin.layouts.app')

@section('title', 'Newsletter Subscribers')

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Newsletter Subscribers</h4>
        <p class="text-muted small mb-0">View, search, and manage all users subscribed to the store newsletter.</p>
    </div>
</div>

{{-- Quick Filter Cards Row --}}
<div class="row g-3 mb-4">
    @php $isActiveAll = !request('status'); @endphp
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.subscribers.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveAll ? '2px solid #6366f1' : '1px solid #e2e8f0' }}; border-left: 5px solid #6366f1 !important; background: {{ $isActiveAll ? '#f5f3ff' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #6366f1;">Total Subscribers</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#ede9fe; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-people-fill" style="font-size:0.95rem; color:#6366f1;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['all'] }}</h3>
                    @if($isActiveAll)
                        <span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    @php $isActiveSub = request('status') === 'Subscribed'; @endphp
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.subscribers.index', ['status' => 'Subscribed']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveSub ? '2px solid #10b981' : '1px solid #e2e8f0' }}; border-left: 5px solid #10b981 !important; background: {{ $isActiveSub ? '#f0fdf4' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #047857;">Active Subscribed</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#d1fae5; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-check-circle-fill" style="font-size:0.95rem; color:#10b981;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['subscribed'] }}</h3>
                    @if($isActiveSub)
                        <span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    @php $isActiveUnsub = request('status') === 'Unsubscribed'; @endphp
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.subscribers.index', ['status' => 'Unsubscribed']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveUnsub ? '2px solid #ef4444' : '1px solid #e2e8f0' }}; border-left: 5px solid #ef4444 !important; background: {{ $isActiveUnsub ? '#fef2f2' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #b91c1c;">Unsubscribed</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fee2e2; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-x-circle-fill" style="font-size:0.95rem; color:#ef4444;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['unsubscribed'] }}</h3>
                    @if($isActiveUnsub)
                        <span class="badge bg-danger rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.subscribers.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by subscriber email or IP address..." 
                            value="{{ request('search') }}" style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Subscribed" {{ request('status') == 'Subscribed' ? 'selected' : '' }}>Subscribed</option>
                        <option value="Unsubscribed" {{ request('status') == 'Unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.85rem;">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.subscribers.index') }}" class="btn btn-light" style="border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem;" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Action Bar (Hidden by default) --}}
<div id="bulk-action-bar" class="card border-0 shadow mb-3 rounded-4 bg-dark text-white p-3" style="display: none;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary rounded-pill px-3 py-1 fw-bold fs-6" id="selected-count">0</span>
            <span class="fw-semibold">subscriber(s) selected</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3" onclick="applyBulkAction('unsubscribe')">
                <i class="bi bi-slash-circle me-1"></i> Unsubscribe Selected
            </button>
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" onclick="applyBulkAction('delete')">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>
</div>

{{-- Subscribers Table Card --}}
<div class="card border-0 shadow-sm" style="border-radius: 14px !important;">
    <div class="card-header d-flex align-items-center justify-content-between py-3 bg-white" style="border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
        <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">
            <i class="bi bi-envelope-check text-primary me-2"></i>Newsletter Subscribers List
        </h6>
        <span class="badge bg-light text-dark border fw-normal px-2.5 py-1.5" style="font-size:0.75rem; border-radius: 8px;">
            Total: {{ $subscribers->total() }} records
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4" width="40">
                            <input type="checkbox" class="form-check-input" id="selectAllSubscribers" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Subscriber Email</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Status</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">IP Address</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Subscribed Date</th>
                        <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $sub)
                    <tr id="sub-row-{{ $sub->id }}">
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input sub-checkbox" value="{{ $sub->id }}" onchange="updateBulkBar()">
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:34px; height:34px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:#fff; flex-shrink:0;">
                                    {{ strtoupper(substr($sub->email, 0, 1)) }}
                                </div>
                                <span class="fw-semibold text-dark" style="font-size:0.875rem;">{{ $sub->email }}</span>
                            </div>
                        </td>
                        <td id="status-cell-{{ $sub->id }}">
                            @if($sub->status === 'Subscribed')
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;">Subscribed</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;">Unsubscribed</span>
                            @endif
                        </td>
                        <td class="small text-muted" style="font-size:0.8rem;">
                            {{ $sub->ip_address ?? 'N/A' }}
                        </td>
                        <td style="font-size:0.8rem; color:#64748b;">
                            {{ $sub->created_at ? $sub->created_at->format('d M, Y h:i A') : 'N/A' }}
                        </td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-sm btn-light text-danger border px-2.5 py-1" style="border-radius:6px; font-size:0.75rem;" 
                                onclick="confirmDeleteSubscriber({{ $sub->id }}, '{{ route('admin.subscribers.destroy', $sub) }}', this)" title="Delete Subscriber">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-envelope-open text-muted opacity-50 display-6 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Subscribers Found</h6>
                                <p class="text-muted mb-0" style="font-size:0.82rem;">Try adjusting your status filter or search query.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($subscribers->hasPages())
        <div class="card-footer bg-white border-top py-3" style="border-radius: 0 0 14px 14px;">
            {{ $subscribers->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Checkbox Multi-selection Logic
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.sub-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checkboxes = document.querySelectorAll('.sub-checkbox:checked');
    const bulkBar = document.getElementById('bulk-action-bar');
    const selectedCount = document.getElementById('selected-count');
    const masterCb = document.getElementById('selectAllSubscribers');

    const count = checkboxes.length;
    selectedCount.textContent = count;
    
    if (count > 0) {
        bulkBar.style.display = 'block';
    } else {
        bulkBar.style.display = 'none';
        if (masterCb) masterCb.checked = false;
    }
}

// Bulk Actions Logic
function applyBulkAction(action) {
    const checkboxes = document.querySelectorAll('.sub-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        Swal.fire({ icon: 'warning', title: 'No Selection', text: 'Please select at least one subscriber.' });
        return;
    }

    const actionText = action === 'delete' ? 'delete' : 'unsubscribe';

    Swal.fire({
        title: `Bulk ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}?`,
        text: `Are you sure you want to ${actionText} ${ids.length} selected subscriber(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#ef4444' : '#f59e0b',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Yes, ${actionText}`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("{{ route('admin.subscribers.bulk-action') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: action, ids: ids })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Bulk action failed.');
                return data;
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Action Completed',
                    text: data.message,
                    timer: 2000
                }).then(() => location.reload());
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        }
    });
}

// Single Delete Confirmation
function confirmDeleteSubscriber(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Subscriber?',
        text: 'This email will be removed from the newsletter subscriber list.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            btnElement.disabled = true;
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
                if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete subscriber.');
                return data;
            })
            .then(data => {
                const row = document.getElementById('sub-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message || 'Subscriber deleted successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500
                });
            })
            .catch(err => {
                btnElement.disabled = false;
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        }
    });
}
</script>
@endpush

@endsection
