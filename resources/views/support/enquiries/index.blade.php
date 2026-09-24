@extends('support.layouts.app')

@section('title', 'Customer Inquiries')
@section('header', 'Customer Inquiries & Helpdesk Inbox')

@section('actions')
    <a href="{{ route('support.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>
@endsection

@section('content')

<!-- Filter Tabs -->
<div class="d-flex align-items-center gap-2 overflow-auto pb-2 mb-4">
    <a href="{{ route('support.enquiries.index') }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ !request()->filled('status') ? 'btn-primary' : 'btn-light border text-secondary' }}" 
       style="{{ !request()->filled('status') ? 'background: #8b5cf6; border-color: #8b5cf6;' : '' }}">
        All Inquiries ({{ $statusCounts['all'] }})
    </a>
    <a href="{{ route('support.enquiries.index', ['status' => 'unread']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request('status') === 'unread' ? 'btn-danger text-white' : 'btn-light border text-secondary' }}">
        Unread / Open ({{ $statusCounts['unread'] }})
    </a>
    <a href="{{ route('support.enquiries.index', ['status' => 'in_progress']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request('status') === 'in_progress' ? 'btn-warning text-dark' : 'btn-light border text-secondary' }}">
        In Progress ({{ $statusCounts['in_progress'] }})
    </a>
    <a href="{{ route('support.enquiries.index', ['status' => 'resolved']) }}" 
       class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold {{ request('status') === 'resolved' ? 'btn-success text-white' : 'btn-light border text-secondary' }}">
        Resolved ({{ $statusCounts['resolved'] }})
    </a>
</div>

<!-- Search Toolbar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
    <form method="GET" action="{{ route('support.enquiries.index') }}" class="row g-2 align-items-center">
        @if(request()->filled('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif

        <div class="col-md-8 col-lg-9">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control bg-light border-start-0 small" 
                       placeholder="Search customer name, email, mobile, subject keywords..." 
                       value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-4 col-lg-3 text-end d-flex gap-2 justify-content-end">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" style="background: #8b5cf6; border-color: #8b5cf6;">Search</button>
            @if(request()->filled('search'))
                <a href="{{ route('support.enquiries.index', ['status' => request('status')]) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5">
                    Clear
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Inquiries Table Card with Bulk Action Support -->
<form action="{{ route('support.enquiries.bulk-action') }}" method="POST" id="bulkEnquiryForm">
    @csrf

    <!-- Floating / Sticky Bulk Action Bar -->
    <div id="bulkActionBar" class="card border-0 shadow-lg rounded-4 mb-3 p-3 text-white d-none" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border-left: 5px solid #8b5cf6 !important;">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-bold" id="selectedCountBadge" style="background: #8b5cf6 !important; font-size: 0.8rem;">
                    0 Selected
                </span>
                <span class="small text-white-50">Choose a bulk update action below:</span>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <select name="action" id="bulkActionSelect" class="form-select form-select-sm bg-white text-dark fw-semibold" style="border-radius: 10px; min-width: 190px; font-size: 0.82rem;" required>
                    <option value="" disabled selected>Choose Bulk Action...</option>
                    <optgroup label="Status Updates">
                        <option value="resolved">Mark as Resolved</option>
                        <option value="in_progress">Mark as In Progress</option>
                        <option value="unread">Mark as Unread / Open</option>
                        <option value="mark_read">Mark as Read</option>
                    </optgroup>
                    <optgroup label="Danger Zone">
                        <option value="delete">Delete Selected Inquiries</option>
                    </optgroup>
                </select>

                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" id="applyBulkBtn" style="background: #8b5cf6; border-color: #8b5cf6; font-size: 0.82rem;">
                    <i class="bi bi-check-circle me-1"></i> Apply
                </button>

                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" onclick="clearAllSelections()" style="font-size: 0.8rem;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small" style="font-size: 0.75rem; letter-spacing: 0.04em;">
                    <tr>
                        <th class="ps-4 py-3" style="width: 40px;">
                            <input type="checkbox" class="form-check-input shadow-none" id="selectAllEnquiries" title="Select All Inquiries on this page">
                        </th>
                        <th class="py-3">Customer</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3">Subject & Message Summary</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Received At</th>
                        <th class="pe-4 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($enquiries as $enquiry)
                        @php
                            $statusBadge = match($enquiry->status) {
                                'unread'      => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Unread / Open'],
                                'in_progress' => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'In Progress'],
                                'resolved'    => ['bg' => '#d1fae5', 'color' => '#059669', 'label' => 'Resolved'],
                                default       => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => ucfirst($enquiry->status)],
                            };
                        @endphp
                        <tr class="{{ !$enquiry->is_read ? 'bg-light bg-opacity-50' : '' }}" id="row-{{ $enquiry->id }}">
                            <td class="ps-4">
                                <input type="checkbox" name="ids[]" value="{{ $enquiry->id }}" class="form-check-input enquiry-checkbox shadow-none">
                            </td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center border flex-shrink-0" 
                                         style="width: 34px; height: 34px; font-size: 0.8rem; background: {{ !$enquiry->is_read ? '#ede9fe' : '#f8fafc' }}; color: {{ !$enquiry->is_read ? '#7c3aed' : '#64748b' }};">
                                        {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $enquiry->name }}</div>
                                        @if(!$enquiry->is_read)
                                            <span class="badge bg-danger rounded-pill" style="font-size: 0.62rem;">NEW</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="small text-dark">{{ $enquiry->email }}</div>
                                @if($enquiry->mobile)
                                    <div class="text-muted small" style="font-size: 0.72rem;">{{ $enquiry->mobile }}</div>
                                @endif
                            </td>

                            <td style="max-width: 320px;">
                                <div class="fw-semibold text-dark small text-truncate">{{ $enquiry->subject }}</div>
                                <div class="text-muted small text-truncate" style="font-size: 0.72rem;">{{ $enquiry->message }}</div>
                            </td>

                            <td>
                                <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.72rem;">
                                    {{ $statusBadge['label'] }}
                                </span>
                                @if($enquiry->resolved_by && $enquiry->resolver)
                                    <div class="text-muted" style="font-size: 0.65rem;">By: {{ $enquiry->resolver->name }}</div>
                                @endif
                            </td>

                            <td class="text-muted small" style="font-size: 0.72rem;">
                                {{ $enquiry->created_at->format('d M Y, h:i A') }}
                                <div style="font-size: 0.68rem;">({{ $enquiry->created_at->diffForHumans() }})</div>
                            </td>

                            <td class="pe-4 text-end">
                                <a href="{{ route('support.enquiries.show', $enquiry) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 shadow-xs fw-bold" style="font-size: 0.75rem; background: #8b5cf6; border-color: #8b5cf6;">
                                    <span>Respond</span> <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-inbox text-muted opacity-50 display-6 mb-3 d-block"></i>
                                    <h6 class="fw-bold text-dark mb-1">No Inquiries Found</h6>
                                    <p class="text-muted mb-0 small">No customer messages match your current filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($enquiries->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex align-items-center justify-content-between">
                <div class="small text-muted">
                    Showing {{ $enquiries->firstItem() }} to {{ $enquiries->lastItem() }} of {{ $enquiries->total() }} inquiries
                </div>
                {{ $enquiries->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllEnquiries');
    const checkboxes = document.querySelectorAll('.enquiry-checkbox');
    const bulkBar = document.getElementById('bulkActionBar');
    const countBadge = document.getElementById('selectedCountBadge');
    const form = document.getElementById('bulkEnquiryForm');
    const actionSelect = document.getElementById('bulkActionSelect');

    function updateBulkBar() {
        const checkedCount = document.querySelectorAll('.enquiry-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkBar.classList.remove('d-none');
            countBadge.textContent = `${checkedCount} Inquiry${checkedCount > 1 ? 's' : ''} Selected`;
        } else {
            bulkBar.classList.add('d-none');
        }

        if (selectAll) {
            selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
            selectAll.indeterminate = (checkedCount > 0 && checkedCount < checkboxes.length);
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkBar();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    window.clearAllSelections = function() {
        checkboxes.forEach(cb => { cb.checked = false; });
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        updateBulkBar();
    };

    if (form) {
        form.addEventListener('submit', function(e) {
            const checkedCount = document.querySelectorAll('.enquiry-checkbox:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Please select at least one inquiry first.');
                return;
            }

            const action = actionSelect.value;
            if (!action) {
                e.preventDefault();
                alert('Please select a bulk action from the dropdown.');
                return;
            }

            if (action === 'delete') {
                if (!confirm(`Are you sure you want to permanently delete ${checkedCount} selected inquiries?`)) {
                    e.preventDefault();
                }
            }
        });
    }
});
</script>
@endpush

@endsection
