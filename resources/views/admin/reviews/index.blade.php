@extends('admin.layouts.app')

@section('header', 'Product Reviews')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reviews</li>
@endsection

@section('content')

{{-- Quick Filter Pills Row --}}
<div class="row g-3 mb-4">
    {{-- All Reviews --}}
    @php $isActiveAll = !request('status'); @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reviews.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveAll ? '2px solid #6366f1' : '1px solid #e2e8f0' }}; border-left: 5px solid #6366f1 !important; background: {{ $isActiveAll ? '#f5f3ff' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #6366f1;">All Reviews</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#ede9fe; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-chat-left-text-fill" style="font-size:0.95rem; color:#6366f1;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;" id="kpi-count-all">{{ $stats['all'] }}</h3>
                    @if($isActiveAll)
                        <span class="badge bg-primary rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Pending Approval --}}
    @php $isActivePending = request('status') === 'Pending'; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reviews.index', ['status' => 'Pending']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActivePending ? '2px solid #d97706' : '1px solid #e2e8f0' }}; border-left: 5px solid #d97706 !important; background: {{ $isActivePending ? '#fffbeb' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #b45309;">Pending Approval</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fef3c7; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-clock-history" style="font-size:0.95rem; color:#d97706;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;" id="kpi-count-pending">{{ $stats['pending'] }}</h3>
                    @if($isActivePending)
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Approved --}}
    @php $isActiveApproved = request('status') === 'Approved'; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reviews.index', ['status' => 'Approved']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveApproved ? '2px solid #10b981' : '1px solid #e2e8f0' }}; border-left: 5px solid #10b981 !important; background: {{ $isActiveApproved ? '#f0fdf4' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #047857;">Approved</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#d1fae5; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-check-circle-fill" style="font-size:0.95rem; color:#10b981;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;" id="kpi-count-approved">{{ $stats['approved'] }}</h3>
                    @if($isActiveApproved)
                        <span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    {{-- Rejected --}}
    @php $isActiveRejected = request('status') === 'Rejected'; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('admin.reviews.index', ['status' => 'Rejected']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveRejected ? '2px solid #ef4444' : '1px solid #e2e8f0' }}; border-left: 5px solid #ef4444 !important; background: {{ $isActiveRejected ? '#fef2f2' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #b91c1c;">Rejected</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#fee2e2; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-x-circle-fill" style="font-size:0.95rem; color:#ef4444;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;" id="kpi-count-rejected">{{ $stats['rejected'] }}</h3>
                    @if($isActiveRejected)
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
        <form method="GET" action="{{ route('admin.reviews.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search by review title, user or product name..." 
                            value="{{ request('search') }}"
                            style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Review Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius: 10px; font-size: 0.85rem;">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-light" style="border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.85rem;" title="Reset Filters">
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
            <span class="fw-semibold">review(s) selected</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="applyBulkAction('approve')">
                <i class="bi bi-check-circle me-1"></i> Approve Selected
            </button>
            <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3" onclick="applyBulkAction('reject')">
                <i class="bi bi-x-circle me-1"></i> Reject Selected
            </button>
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" onclick="applyBulkAction('delete')">
                <i class="bi bi-trash me-1"></i> Delete Selected
            </button>
        </div>
    </div>
</div>

{{-- Reviews Card --}}
<div class="card border-0 shadow-sm" style="border-radius: 14px !important;">
    <div class="card-header d-flex align-items-center justify-content-between py-3 bg-white" style="border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
        <h6 class="mb-0 fw-bold text-dark" style="font-size:0.95rem;">
            <i class="bi bi-star text-primary me-2"></i>Customer Reviews List
        </h6>
        <span class="badge bg-light text-dark border fw-normal px-2.5 py-1.5" style="font-size:0.75rem; border-radius: 8px;">
            Total: {{ $reviews->total() }} reviews
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th class="ps-4" width="40">
                            <input type="checkbox" class="form-check-input" id="selectAllReviews" onchange="toggleSelectAll(this)">
                        </th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Product</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Customer</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Rating & Review</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Status</th>
                        <th class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Date</th>
                        <th class="pe-4 text-end text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr id="review-row-{{ $review->id }}">
                        <td class="ps-4">
                            <input type="checkbox" class="form-check-input review-checkbox" value="{{ $review->id }}" onchange="updateBulkBar()">
                        </td>
                        <td>
                            <div class="fw-semibold text-dark" style="font-size:0.85rem;">{{ Str::limit($review->product->name ?? '—', 28) }}</div>
                            @if($review->product)
                                <a href="{{ route('admin.products.show', $review->product) }}" target="_blank" class="small text-primary text-decoration-none">View Product <i class="bi bi-arrow-up-right-short"></i></a>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg, #6366f1, #8b5cf6); display:flex; align-items:center; justify-content:center; font-size:0.75rem; font-weight:700; color:#fff; flex-shrink:0;">
                                    {{ strtoupper(substr($review->user->name ?? 'C', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-medium text-dark" style="font-size:0.83rem;">{{ $review->user->name ?? 'Customer' }}</div>
                                    <div class="text-muted small" style="font-size:0.72rem;">{{ $review->user->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1 mb-0.5" style="color:#f59e0b; font-size:0.8rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= $review->rating ? '★' : '☆' }}
                                @endfor
                                <span class="ms-1 fw-bold text-dark" style="font-size:0.78rem;">({{ $review->rating }}/5)</span>
                            </div>
                            <div class="fw-semibold text-dark" style="font-size:0.82rem;">{{ Str::limit($review->title, 35) }}</div>
                            <div class="text-secondary small text-truncate" style="max-width: 250px; font-size: 0.78rem;">{{ $review->review }}</div>
                        </td>
                        <td id="status-cell-{{ $review->id }}">
                            @if($review->status == 'Approved')
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;">Approved</span>
                            @elseif($review->status == 'Rejected')
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;">Rejected</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">Pending</span>
                            @endif
                        </td>
                        <td style="font-size:0.8rem; color:#64748b;">
                            {{ $review->created_at->format('d M, Y') }}
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex gap-1 justify-content-end align-items-center" id="actions-cell-{{ $review->id }}">
                                {{-- View Full Review Modal Trigger --}}
                                <button type="button" class="btn btn-sm btn-light text-primary px-2.5 py-1 border" style="border-radius:6px; font-size:0.75rem;" 
                                    onclick="openReviewModal({{ $review->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>

                                {{-- Instant Status Change Buttons --}}
                                <button type="button" id="btn-approve-{{ $review->id }}" class="btn btn-sm btn-success text-white px-2.5 py-1 fw-semibold" 
                                    style="border-radius:6px; font-size:0.75rem; display: {{ $review->status === 'Approved' ? 'none' : 'inline-block' }};" 
                                    onclick="updateReviewStatus({{ $review->id }}, 'Approved', '{{ route('admin.reviews.update', $review) }}', this)" title="Approve Review">
                                    <i class="bi bi-check-lg me-1"></i>Approve
                                </button>

                                <button type="button" id="btn-reject-{{ $review->id }}" class="btn btn-sm btn-warning text-dark px-2.5 py-1 fw-semibold" 
                                    style="border-radius:6px; font-size:0.75rem; display: {{ $review->status === 'Rejected' ? 'none' : 'inline-block' }};" 
                                    onclick="updateReviewStatus({{ $review->id }}, 'Rejected', '{{ route('admin.reviews.update', $review) }}', this)" title="Reject Review">
                                    <i class="bi bi-x-circle me-1"></i>Reject
                                </button>

                                <button type="button" class="btn btn-sm btn-light text-danger px-2.5 py-1 border" style="border-radius:6px; font-size:0.75rem;" 
                                    onclick="confirmDeleteReview({{ $review->id }}, '{{ route('admin.reviews.destroy', $review) }}', this)" title="Delete Review">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <script>
                                window.reviewsMap = window.reviewsMap || {};
                                window.reviewsMap[{{ $review->id }}] = {
                                    id: {{ $review->id }},
                                    title: {!! json_encode($review->title) !!},
                                    review: {!! json_encode($review->review) !!},
                                    rating: {{ $review->rating }},
                                    status: {!! json_encode($review->status) !!},
                                    date: {!! json_encode($review->created_at->format('d M Y, h:i A')) !!},
                                    customer: {!! json_encode($review->user->name ?? 'Customer') !!},
                                    email: {!! json_encode($review->user->email ?? 'N/A') !!},
                                    product: {!! json_encode($review->product->name ?? 'N/A') !!}
                                };
                            </script>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-star text-muted opacity-50 display-6 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark mb-1">No Reviews Found</h6>
                                <p class="text-muted mb-0" style="font-size:0.82rem;">Try adjusting your status filter or search term.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="px-4 py-3 border-top" style="background:#fff; border-radius: 0 0 14px 14px;">
                {{ $reviews->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

{{-- View Review Modal --}}
<div class="modal fade" id="viewReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4 bg-white">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-star-fill text-warning me-2"></i>Review Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Product</small>
                        <div id="modal-product" class="fw-bold text-dark fs-6"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Customer</small>
                        <div id="modal-customer" class="fw-semibold text-dark"></div>
                        <div id="modal-email" class="text-muted small"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div id="modal-stars" style="color:#f59e0b; font-size: 1.2rem;"></div>
                    <div id="modal-status-badge"></div>
                </div>

                <h5 id="modal-title" class="fw-bold text-dark mb-2"></h5>
                <div id="modal-review" class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-line; line-height: 1.6;"></div>
                <div class="mt-2 text-end text-muted small"><i class="bi bi-calendar3 me-1"></i>Posted on: <span id="modal-date"></span></div>
            </div>
            <div class="modal-footer border-top py-2 px-4 bg-white">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Checkbox Selection
function toggleSelectAll(master) {
    document.querySelectorAll('.review-checkbox').forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.review-checkbox:checked');
    const bar = document.getElementById('bulk-action-bar');
    const countSpan = document.getElementById('selected-count');

    if (checked.length > 0) {
        bar.style.display = 'block';
        countSpan.textContent = checked.length;
    } else {
        bar.style.display = 'none';
    }
}

// Bulk Actions
function applyBulkAction(action) {
    const checked = Array.from(document.querySelectorAll('.review-checkbox:checked')).map(cb => cb.value);
    if (checked.length === 0) return;

    let actionLabel = action === 'approve' ? 'Approve' : (action === 'reject' ? 'Reject' : 'Delete');
    
    Swal.fire({
        title: `${actionLabel} ${checked.length} Review(s)?`,
        text: `Are you sure you want to ${action} the selected review(s)?`,
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonColor: action === 'delete' ? '#ef4444' : '#6366f1',
        cancelButtonColor: '#64748b',
        confirmButtonText: `Yes, ${actionLabel}`,
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch("{{ route('admin.reviews.bulk-action') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: checked, action: action })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Bulk operation failed.');
                return data;
            })
            .then(data => {
                if (action === 'delete') {
                    checked.forEach(id => {
                        const row = document.getElementById('review-row-' + id);
                        if (row) row.remove();
                    });
                } else {
                    location.reload(); // Refresh to update status pills & badges
                    return;
                }

                document.getElementById('selectAllReviews').checked = false;
                updateBulkBar();

                Swal.fire({
                    icon: 'success',
                    title: 'Completed!',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        }
    });
}

// Instant AJAX Status Update
function updateReviewStatus(id, newStatus, updateUrl, btnElement) {
    btnElement.disabled = true;
    
    fetch(updateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'PATCH',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Status update failed.');
        return data;
    })
    .then(data => {
        // 1. Update status badge with high contrast colors
        const statusCell = document.getElementById('status-cell-' + id);
        if (statusCell) {
            if (newStatus === 'Approved') {
                statusCell.innerHTML = '<span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;">Approved</span>';
            } else if (newStatus === 'Rejected') {
                statusCell.innerHTML = '<span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;">Rejected</span>';
            } else {
                statusCell.innerHTML = '<span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">Pending</span>';
            }
        }

        // 2. Toggle buttons dynamically: if Approved, hide Approve button and show Reject button
        const btnApprove = document.getElementById('btn-approve-' + id);
        const btnReject = document.getElementById('btn-reject-' + id);

        if (btnApprove) {
            btnApprove.disabled = false;
            btnApprove.style.display = (newStatus === 'Approved') ? 'none' : 'inline-block';
        }
        if (btnReject) {
            btnReject.disabled = false;
            btnReject.style.display = (newStatus === 'Rejected') ? 'none' : 'inline-block';
        }

        // 3. Update memory map for modal
        if (window.reviewsMap && window.reviewsMap[id]) {
            window.reviewsMap[id].status = newStatus;
        }

        // 4. Show SweetAlert Toast
        Swal.fire({
            icon: 'success',
            title: 'Status Updated',
            text: data.message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    })
    .catch(err => {
        btnElement.disabled = false;
        Swal.fire({ icon: 'error', title: 'Error', text: err.message, toast: true, position: 'top-end', timer: 3000 });
    });
}

// Delete Review Confirmation
function confirmDeleteReview(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Review?',
        text: "This review will be permanently deleted.",
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
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to delete review.');
                }
                return data;
            })
            .then(data => {
                const row = document.getElementById('review-row-' + id);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message || 'Review deleted successfully.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            })
            .catch(err => {
                btnElement.disabled = false;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Failed to delete review.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        }
    });
}

// Open Review Modal Handler
function openReviewModal(id) {
    const data = window.reviewsMap ? window.reviewsMap[id] : null;
    if (!data) return;

    document.getElementById('modal-product').textContent = data.product;
    document.getElementById('modal-customer').textContent = data.customer;
    document.getElementById('modal-email').textContent = data.email;
    document.getElementById('modal-title').textContent = data.title;
    document.getElementById('modal-review').textContent = data.review;
    document.getElementById('modal-date').textContent = data.date;

    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        starsHtml += (i <= data.rating ? '★' : '☆');
    }
    starsHtml += ` <span class="text-dark fw-bold ms-1" style="font-size:0.9rem;">(${data.rating}/5 Stars)</span>`;
    document.getElementById('modal-stars').innerHTML = starsHtml;

    let statusBadge = '';
    if (data.status === 'Approved') {
        statusBadge = '<span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0;">Approved</span>';
    } else if (data.status === 'Rejected') {
        statusBadge = '<span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fee2e2; color:#991b1b; border:1px solid #fca5a5;">Rejected</span>';
    } else {
        statusBadge = '<span class="badge rounded-pill px-3 py-1 fw-bold" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;">Pending</span>';
    }
    document.getElementById('modal-status-badge').innerHTML = statusBadge;

    const modal = new bootstrap.Modal(document.getElementById('viewReviewModal'));
    modal.show();
}
</script>
@endpush

@endsection


