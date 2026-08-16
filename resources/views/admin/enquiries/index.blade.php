@extends('admin.layouts.app')

@section('header', 'Customer Enquiries & Support')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Enquiries</li>
@endsection

@section('content')
<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-inbox-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Messages</div>
                    <div class="fs-4 fw-bold text-dark">{{ $enquiries->total() }} Inquiries</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-envelope-exclamation-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Unread Messages</div>
                    <div class="fs-4 fw-bold text-dark">{{ $enquiries->where('is_read', false)->count() }} New</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-envelope-check-fill fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Read & Processed</div>
                    <div class="fs-4 fw-bold text-dark">{{ $enquiries->where('is_read', true)->count() }} Messages</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enquiries Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-chat-left-text-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Customer Support Inquiries</h6>
        </div>

        <!-- Bulk Actions Bar -->
        <div id="bulk-toolbar" class="d-none align-items-center gap-2">
            <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small" id="selected-count">0 selected</span>
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold" id="btn-bulk-delete">
                <i class="bi bi-trash3 me-1"></i> Delete Selected
            </button>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="enquiries-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 40px;">
                            <input type="checkbox" class="form-check-input" id="check-all" title="Select All">
                        </th>
                        <th>Sender Details</th>
                        <th>Subject & Message Snippet</th>
                        <th>Received Date</th>
                        <th class="text-center">Status</th>
                        <th class="pe-4 text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enquiries as $enquiry)
                    <tr id="enquiry-row-{{ $enquiry->id }}" class="{{ !$enquiry->is_read ? 'bg-primary bg-opacity-10' : '' }}">
                        <!-- Checkbox -->
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input row-check" value="{{ $enquiry->id }}">
                        </td>

                        <!-- Sender Profile -->
                        <td>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                     style="width: 38px; height: 38px; background: {{ !$enquiry->is_read ? '#0d6efd' : '#64748b' }}; font-size: 0.8rem;">
                                    {{ strtoupper(substr($enquiry->name, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $enquiry->name }}</div>
                                    <div class="small text-muted" style="font-size: 0.72rem;">
                                        <a href="mailto:{{ $enquiry->email }}" class="text-decoration-none text-muted">{{ $enquiry->email }}</a>
                                        @if($enquiry->mobile)
                                            &middot; <span>{{ $enquiry->mobile }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Subject -->
                        <td>
                            <div class="fw-semibold text-dark text-truncate" style="max-width: 280px;" title="{{ $enquiry->subject }}">
                                {{ $enquiry->subject }}
                            </div>
                            <div class="small text-muted text-truncate" style="max-width: 280px; font-size: 0.72rem;">
                                {{ $enquiry->message }}
                            </div>
                        </td>

                        <!-- Date -->
                        <td class="small text-muted">
                            <i class="bi bi-clock me-1 text-secondary"></i>{{ $enquiry->created_at->format('d M Y, h:i A') }}
                        </td>

                        <!-- Status Badge -->
                        <td class="text-center">
                            @if(!$enquiry->is_read)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                    <i class="bi bi-envelope-fill me-1"></i> New
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1">
                                    Read
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="pe-4 text-end">
                            <div class="d-flex justify-content-end align-items-center gap-1.5">
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center" title="Open Message">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 btn-ajax-delete" data-id="{{ $enquiry->id }}" data-url="{{ route('admin.enquiries.destroy', $enquiry) }}" title="Delete Message">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="empty-row">
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox text-muted opacity-50 display-6 d-block mb-2"></i>
                            <h6 class="fw-bold text-dark">No Inquiries Found</h6>
                            <p class="small text-muted mb-0">Customer contact messages will appear here when submitted.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enquiries->hasPages())
            <div class="p-4 border-top bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="small text-muted">
                    Showing <strong>{{ $enquiries->firstItem() }}</strong> to <strong>{{ $enquiries->lastItem() }}</strong> of <strong>{{ $enquiries->total() }}</strong> enquiries
                </div>
                <div>
                    {{ $enquiries->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';
    const bulkUrl = '{{ route("admin.enquiries.bulk-destroy") }}';

    const checkAll = document.getElementById('check-all');
    const bulkToolbar = document.getElementById('bulk-toolbar');
    const selectedCount = document.getElementById('selected-count');

    function getChecked() {
        return [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
    }

    function updateToolbar() {
        const checked = getChecked();
        if (checked.length > 0) {
            bulkToolbar.classList.remove('d-none');
            bulkToolbar.classList.add('d-flex');
            selectedCount.textContent = `${checked.length} selected`;
        } else {
            bulkToolbar.classList.add('d-none');
            bulkToolbar.classList.remove('d-flex');
        }
        const all = document.querySelectorAll('.row-check');
        checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
        checkAll.checked = all.length > 0 && checked.length === all.length;
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(c => c.checked = this.checked);
            updateToolbar();
        });
    }

    document.querySelectorAll('.row-check').forEach(c => {
        c.addEventListener('change', updateToolbar);
    });

    function fadeRemoveRow(id) {
        const row = document.getElementById(`enquiry-row-${id}`);
        if (!row) return;
        row.style.transition = 'all 0.3s ease';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 300);
    }

    // Single Delete
    document.querySelectorAll('.btn-ajax-delete').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const url = this.dataset.url;
            const self = this;

            Swal.fire({
                title: 'Delete this message?',
                text: 'This inquiry will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;
                self.disabled = true;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        fadeRemoveRow(id);
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: data.message || 'Inquiry deleted successfully.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    } else {
                        self.disabled = false;
                    }
                })
                .catch(() => { self.disabled = false; });
            });
        });
    });

    // Bulk Delete
    const bulkBtn = document.getElementById('btn-bulk-delete');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function () {
            const ids = getChecked();
            if (ids.length === 0) return;

            Swal.fire({
                title: `Delete ${ids.length} inquiries?`,
                text: 'All selected messages will be permanently removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete All',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;
                this.disabled = true;

                fetch(bulkUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                })
                .then(r => r.json())
                .then(data => {
                    this.disabled = false;
                    if (data.success) {
                        ids.forEach(id => fadeRemoveRow(id));
                        checkAll.checked = false;
                        updateToolbar();
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: data.message || 'Selected inquiries deleted successfully.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    }
                })
                .catch(() => { this.disabled = false; });
            });
        });
    }
});
</script>
@endpush
@endsection
