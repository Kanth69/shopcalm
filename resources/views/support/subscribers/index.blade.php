@extends('support.layouts.app')

@section('header', 'Newsletter Subscribers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('support.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Newsletter Subscribers</li>
@endsection

@section('content')

{{-- Quick Filter Cards Row --}}
<div class="row g-3 mb-4">
    @php $isActiveAll = !request('status'); @endphp
    <div class="col-6 col-md-4">
        <a href="{{ route('support.subscribers.index') }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveAll ? '2px solid #8b5cf6' : '1px solid #e2e8f0' }}; border-left: 5px solid #8b5cf6 !important; background: {{ $isActiveAll ? '#f5f3ff' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #8b5cf6;">Total Leads</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#ede9fe; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-people-fill" style="font-size:0.95rem; color:#8b5cf6;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['all'] }}</h3>
                    @if($isActiveAll)
                        <span class="badge bg-primary rounded-pill px-2.5 py-1" style="background:#8b5cf6 !important; font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>

    @php $isActiveSub = request('status') === 'Subscribed'; @endphp
    <div class="col-6 col-md-4">
        <a href="{{ route('support.subscribers.index', ['status' => 'Subscribed']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
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
        <a href="{{ route('support.subscribers.index', ['status' => 'Unsubscribed']) }}" class="card text-decoration-none h-100 shadow-sm transition-all" 
            style="border-radius: 14px !important; border: {{ $isActiveUnsub ? '2px solid #64748b' : '1px solid #e2e8f0' }}; border-left: 5px solid #64748b !important; background: {{ $isActiveUnsub ? '#f8fafc' : '#ffffff' }};">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-uppercase" style="font-size:0.72rem; letter-spacing:0.05em; color: #475569;">Unsubscribed</span>
                    <div style="width:34px; height:34px; border-radius:10px; background:#f1f5f9; display:flex; align-items:center; justify-content:center;">
                        <i class="bi bi-x-circle-fill" style="font-size:0.95rem; color:#64748b;"></i>
                    </div>
                </div>
                <div class="d-flex align-items-baseline justify-content-between">
                    <h3 class="mb-0 fw-bolder" style="font-size:1.6rem; color:#0f172a;">{{ $stats['unsubscribed'] }}</h3>
                    @if($isActiveUnsub)
                        <span class="badge bg-secondary rounded-pill px-2.5 py-1" style="font-size:0.65rem;">Active Filter</span>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Filters Card --}}
<div class="card mb-4" style="border-radius: 14px !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('support.subscribers.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" 
                            placeholder="Search subscriber email or IP address..." 
                            value="{{ request('search') }}"
                            style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" style="border-radius: 10px; border-color: #cbd5e1; font-size: 0.85rem;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Subscribed" {{ request('status') === 'Subscribed' ? 'selected' : '' }}>Subscribed</option>
                        <option value="Unsubscribed" {{ request('status') === 'Unsubscribed' ? 'selected' : '' }}>Unsubscribed</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold flex-grow-1" style="border-radius: 10px; font-size: 0.82rem; background: #8b5cf6; border-color: #8b5cf6;">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('support.subscribers.index') }}" class="btn btn-light" style="border-radius: 10px; border: 1px solid #e2e8f0; font-size: 0.82rem;" title="Reset Filters">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                    <a href="{{ route('support.subscribers.export') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-size: 0.82rem;" title="Export CSV">
                        <i class="bi bi-download me-1"></i> Export
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Subscribers Table Card --}}
<form action="{{ route('support.subscribers.bulk-action') }}" method="POST" id="bulkSubscriberForm">
    @csrf
    <div class="card" style="border-radius: 14px !important;">
        <div class="card-header d-flex align-items-center justify-content-between py-3" style="background:#fff; border-bottom: 1px solid #f1f5f9; border-radius: 14px 14px 0 0;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-envelope-paper-heart-fill text-purple" style="color: #8b5cf6;"></i>
                <h6 class="mb-0 fw-bold text-dark">Subscribers Directory</h6>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select name="action" class="form-select form-select-sm" style="width: auto; border-radius: 8px; font-size: 0.8rem;" required>
                    <option value="">Bulk Actions</option>
                    <option value="subscribe">Mark Subscribed</option>
                    <option value="unsubscribe">Mark Unsubscribed</option>
                    <option value="delete">Delete Selected</option>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.8rem;">Apply</button>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background:#f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <tr>
                            <th class="ps-3" style="width: 40px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th style="font-size: 0.72rem; letter-spacing: 0.04em;">Email Address</th>
                            <th style="font-size: 0.72rem; letter-spacing: 0.04em;">Subscription Status</th>
                            <th style="font-size: 0.72rem; letter-spacing: 0.04em;">IP Address</th>
                            <th style="font-size: 0.72rem; letter-spacing: 0.04em;">Date Subscribed</th>
                            <th class="pe-3 text-end" style="font-size: 0.72rem; letter-spacing: 0.04em;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $sub)
                        <tr>
                            <td class="ps-3">
                                <input type="checkbox" name="selected_subscribers[]" value="{{ $sub->id }}" class="form-check-input sub-checkbox">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem; background: #ede9fe; color: #8b5cf6;">
                                        {{ strtoupper(substr($sub->email, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $sub->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($sub->status === 'Subscribed')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">Subscribed</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">Unsubscribed</span>
                                @endif
                            </td>
                            <td class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                {{ $sub->ip_address ?? '—' }}
                            </td>
                            <td class="text-muted small" style="font-size: 0.75rem;">
                                {{ $sub->created_at ? $sub->created_at->format('M d, Y • h:i A') : '—' }}
                            </td>
                            <td class="pe-3 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                            style="width: 28px; height: 28px;"
                                            onclick="toggleSubscriberStatus({{ $sub->id }})" title="Toggle Subscription Status">
                                        <i class="bi bi-arrow-repeat" style="font-size: 0.75rem;"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                            style="width: 28px; height: 28px;"
                                            onclick="deleteSubscriber({{ $sub->id }})" title="Delete Subscriber">
                                        <i class="bi bi-trash" style="font-size: 0.75rem;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No newsletter subscribers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($subscribers->hasPages())
                <div class="px-4 py-3 border-top" style="background:#fff; border-radius: 0 0 14px 14px;">
                    {{ $subscribers->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</form>

<!-- Single Toggle Status Form -->
<form id="singleToggleForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<!-- Single Delete Form -->
<form id="singleDeleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.sub-checkbox').forEach(cb => cb.checked = this.checked);
});

function toggleSubscriberStatus(id) {
    const form = document.getElementById('singleToggleForm');
    form.action = `/support/subscribers/${id}/toggle`;
    form.submit();
}

function deleteSubscriber(id) {
    Swal.fire({
        title: 'Delete Subscriber?',
        text: 'This email will be removed from the newsletter directory.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete'
    }).then((res) => {
        if (res.isConfirmed) {
            const form = document.getElementById('singleDeleteForm');
            form.action = `/support/subscribers/${id}`;
            form.submit();
        }
    });
}
</script>
@endpush

@endsection
