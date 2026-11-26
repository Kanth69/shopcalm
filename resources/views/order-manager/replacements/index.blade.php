@extends('order-manager.layouts.app')

@section('title', 'Item Replacements & Reverse Logistics Desk - ShopCalm')

@section('content')
<div class="container-fluid py-4">
    <!-- Top Action Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-info-subtle text-info fw-bold border border-info-subtle px-2 py-1">
                    <i class="bi bi-arrow-repeat me-1"></i> Reverse Logistics Hub
                </span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 d-inline-flex align-items-center gap-1" id="live-poller-pill">
                    <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></span>
                    <span id="poller-status-text">Live Polling Active (15s)</span>
                </span>
            </div>
            <h2 class="h3 fw-bold text-white mb-0">Item Replacements Registry</h2>
            <p class="text-secondary small mb-0">Review customer replacement requests, schedule doorstep pickup dates, and dispatch delivery fleet.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="triggerManualPoll()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh Now
            </button>
        </div>
    </div>

    <!-- Metrics Tab Strip -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('order-manager.replacements.index', ['status' => 'requested']) }}" class="card bg-dark border text-decoration-none h-100 p-3 {{ request()->get('status') === 'requested' || (!request()->filled('status') && $metrics['requested'] > 0) ? 'border-warning shadow-sm' : 'border-secondary-subtle' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-semibold">Pending Review</span>
                    <i class="bi bi-hourglass-split text-warning fs-5"></i>
                </div>
                <div class="h3 fw-bold text-warning mb-0" id="metric-requested-count">{{ $metrics['requested'] }}</div>
                <span class="text-secondary" style="font-size: 0.75rem;">Awaiting Fleet Assignment</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('order-manager.replacements.index', ['status' => 'pickup_assigned']) }}" class="card bg-dark border text-decoration-none h-100 p-3 {{ request()->get('status') === 'pickup_assigned' ? 'border-primary shadow-sm' : 'border-secondary-subtle' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-semibold">Pickup Scheduled</span>
                    <i class="bi bi-calendar-event text-primary fs-5"></i>
                </div>
                <div class="h3 fw-bold text-primary mb-0">{{ $metrics['pickup_assigned'] }}</div>
                <span class="text-secondary" style="font-size: 0.75rem;">Assigned to Delivery Partner</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('order-manager.replacements.index', ['status' => 'picked_up']) }}" class="card bg-dark border text-decoration-none h-100 p-3 {{ request()->get('status') === 'picked_up' ? 'border-info shadow-sm' : 'border-secondary-subtle' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-semibold">In Transit (Picked Up)</span>
                    <i class="bi bi-truck text-info fs-5"></i>
                </div>
                <div class="h3 fw-bold text-info mb-0">{{ $metrics['picked_up'] }}</div>
                <span class="text-secondary" style="font-size: 0.75rem;">Returning to Warehouse Hub</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('order-manager.replacements.index', ['status' => 'completed']) }}" class="card bg-dark border text-decoration-none h-100 p-3 {{ request()->get('status') === 'completed' ? 'border-success shadow-sm' : 'border-secondary-subtle' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-semibold">Exchange Completed</span>
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                </div>
                <div class="h3 fw-bold text-success mb-0">{{ $metrics['completed'] }}</div>
                <span class="text-secondary" style="font-size: 0.75rem;">Replacement Handed Over</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('order-manager.replacements.index', ['status' => 'rejected']) }}" class="card bg-dark border text-decoration-none h-100 p-3 {{ request()->get('status') === 'rejected' ? 'border-danger shadow-sm' : 'border-secondary-subtle' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-semibold">Rejected Claims</span>
                    <i class="bi bi-x-circle text-danger fs-5"></i>
                </div>
                <div class="h3 fw-bold text-danger mb-0">{{ $metrics['rejected'] }}</div>
                <span class="text-secondary" style="font-size: 0.75rem;">Invalid or Policy Void</span>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <a href="{{ route('order-manager.replacements.index', ['status' => 'all']) }}" class="card bg-dark border text-decoration-none h-100 p-3 {{ request()->get('status') === 'all' || !request()->has('status') ? 'border-light shadow-sm' : 'border-secondary-subtle' }}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="text-secondary small fw-semibold">Total Lifecycle</span>
                    <i class="bi bi-collection text-white fs-5"></i>
                </div>
                <div class="h3 fw-bold text-white mb-0">{{ $metrics['all'] }}</div>
                <span class="text-secondary" style="font-size: 0.75rem;">All Historical Requests</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card bg-dark border-secondary-subtle mb-4 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('order-manager.replacements.index') }}" class="row g-2 align-items-center">
                <input type="hidden" name="status" value="{{ request()->get('status', 'all') }}">
                <div class="col-12 col-md-8">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request()->get('search') }}" class="form-control bg-dark border-secondary text-white" placeholder="Search by Replacement Number, Order #, Customer Mobile, or Rider...">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                </div>
                <div class="col-6 col-md-2">
                    <a href="{{ route('order-manager.replacements.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Toast Alert Container -->
    <div id="live-alert-placeholder"></div>

    <!-- Replacements Table -->
    <div class="card bg-dark border-secondary-subtle shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" id="replacements-table">
                <thead class="table-dark text-secondary small border-secondary">
                    <tr>
                        <th class="ps-3 py-3">REPLACEMENT ID</th>
                        <th class="py-3">ORDER &amp; CUSTOMER</th>
                        <th class="py-3">ITEM DETAILS</th>
                        <th class="py-3">REASON &amp; PROOF</th>
                        <th class="py-3">STATUS</th>
                        <th class="py-3">ASSIGNED RIDER &amp; PICKUP</th>
                        <th class="pe-3 py-3 text-end">OPERATIONS</th>
                    </tr>
                </thead>
                <tbody id="replacements-tbody">
                    @forelse($replacements as $rep)
                    <tr id="rep-row-{{ $rep->id }}" class="border-secondary-subtle {{ $rep->status === 'requested' ? 'bg-warning-subtle bg-opacity-10' : '' }}">
                        <td class="ps-3">
                            <div class="fw-bold text-info font-monospace">{{ $rep->replacement_number }}</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">
                                <i class="bi bi-clock me-1"></i>{{ $rep->created_at->format('d M, h:i A') }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-white">#{{ $rep->order?->order_number }}</div>
                            <div class="small text-secondary">{{ $rep->user?->name ?? 'Customer' }}</div>
                            <div class="small text-secondary font-monospace">{{ $rep->order?->shipping_phone ?? $rep->user?->mobile_number }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($rep->product?->main_image)
                                    <img src="{{ asset('storage/' . $rep->product->main_image) }}" class="rounded border border-secondary flex-shrink-0" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.onerror=null; this.src='/assets/images/placeholder.webp';">
                                @endif
                                <div>
                                    <div class="fw-semibold text-white small" style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $rep->orderItem?->product_name ?? ($rep->product?->name ?? 'Item') }}
                                    </div>
                                    @if($rep->orderItem?->selected_option)
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.7rem;">{{ $rep->orderItem->selected_option }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $rep->reason === 'physical_damage' ? 'bg-danger' : 'bg-primary' }} mb-1">
                                {{ $rep->reason_label }}
                            </span>
                            @if(!empty($rep->proof_images) && count($rep->proof_images) > 0)
                                <div class="mt-1">
                                    <button class="btn btn-outline-info btn-xs py-0 px-2" onclick="viewProofImages({{ json_encode($rep->proof_images) }})">
                                        <i class="bi bi-camera me-1"></i>View {{ count($rep->proof_images) }} Photos
                                    </button>
                                </div>
                            @else
                                <div class="text-secondary" style="font-size: 0.72rem;">No Photo Required</div>
                            @endif
                        </td>
                        <td>
                            @switch($rep->status)
                                @case('requested')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>Pending Review</span>
                                    @break
                                @case('approved')
                                @case('pickup_assigned')
                                    <span class="badge bg-primary"><i class="bi bi-calendar-check me-1"></i>Pickup Scheduled</span>
                                    @break
                                @case('picked_up')
                                    <span class="badge bg-info text-dark"><i class="bi bi-truck me-1"></i>Picked Up</span>
                                    @break
                                @case('completed')
                                    <span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Exchanged</span>
                                    @break
                                @case('rejected')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ ucfirst($rep->status) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @if($rep->rider_name)
                                <div class="fw-semibold text-white small"><i class="bi bi-person-badge text-info me-1"></i>{{ $rep->rider_name }}</div>
                                <div class="small text-secondary font-monospace"><i class="bi bi-telephone me-1"></i>{{ $rep->rider_phone }}</div>
                                @if($rep->pickup_date)
                                    <div class="small text-warning mt-1"><i class="bi bi-calendar-event me-1"></i>Pickup: {{ $rep->pickup_date->format('d M Y') }}</div>
                                @endif
                            @else
                                <span class="text-secondary small italic">Not Assigned</span>
                            @endif
                        </td>
                        <td class="pe-3 text-end">
                            @if($rep->status === 'requested')
                                <button class="btn btn-success btn-sm me-1" onclick="openAssignModal({{ $rep->id }}, '{{ $rep->replacement_number }}', '{{ $rep->order?->order_number }}')">
                                    <i class="bi bi-check2-circle me-1"></i>Approve &amp; Assign
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="openRejectModal({{ $rep->id }}, '{{ $rep->replacement_number }}')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            @elseif($rep->status === 'pickup_assigned')
                                <button class="btn btn-outline-info btn-sm" onclick="openAssignModal({{ $rep->id }}, '{{ $rep->replacement_number }}', '{{ $rep->order?->order_number }}', '{{ $rep->rider_id }}', '{{ $rep->pickup_date?->format('Y-m-d') }}')">
                                    <i class="bi bi-pencil me-1"></i>Reschedule
                                </button>
                            @elseif($rep->status === 'rejected')
                                <span class="text-secondary small" title="{{ $rep->rejection_reason }}">{{ Str::limit($rep->rejection_reason, 25) }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">In Flow</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No replacement requests found matching this criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($replacements->hasPages())
        <div class="card-footer bg-dark border-secondary-subtle py-3">
            {{ $replacements->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Approve & Assign Rider + Pickup Date -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary text-white shadow-lg">
            <form id="assignForm" method="POST" action="">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-truck text-info me-2"></i>Schedule Pickup &amp; Assign Rider
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-secondary-subtle bg-opacity-10 rounded border border-secondary mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary small">Replacement ID:</span>
                            <span class="fw-bold text-info font-monospace" id="modal-rep-number"></span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-secondary small">Order Reference:</span>
                            <span class="fw-bold text-white font-monospace" id="modal-order-number"></span>
                        </div>
                    </div>

                    <!-- Pickup Date Input -->
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">Doorstep Pickup Date <span class="text-danger">*</span></label>
                        <input type="date" name="pickup_date" id="modal-pickup-date" class="form-control bg-dark border-secondary text-white" value="{{ date('Y-m-d', strtotime('+1 day')) }}" min="{{ date('Y-m-d') }}" required>
                        <div class="form-text text-secondary" style="font-size: 0.75rem;">Customer will receive notification with this scheduled pickup date.</div>
                    </div>

                    <!-- Assign Delivery Partner -->
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">Assign Delivery Partner <span class="text-danger">*</span></label>
                        <select name="rider_id" id="modal-rider-select" class="form-select bg-dark border-secondary text-white" required>
                            <option value="">-- Choose Active Rider --</option>
                            @foreach($riders as $rider)
                                <option value="{{ $rider->id }}">
                                    {{ $rider->name }} ({{ $rider->mobile_number }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-secondary" style="font-size: 0.75rem;">This task will appear in the rider's reverse logistics pickup queue.</div>
                    </div>

                    <!-- Internal Notes -->
                    <div class="mb-2">
                        <label class="form-label text-secondary small fw-semibold">Order Manager Instructions (Optional)</label>
                        <textarea name="order_manager_notes" class="form-control bg-dark border-secondary text-white" rows="2" placeholder="e.g. Verify sealed box before accepting exchange"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Confirm &amp; Dispatch Fleet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reject Replacement -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-danger text-white shadow-lg">
            <form id="rejectForm" method="POST" action="">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="bi bi-x-circle me-2"></i>Reject Replacement Request
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-3">Please provide a clear reason for rejecting replacement claim <span class="fw-bold text-white font-monospace" id="reject-modal-rep-number"></span>. Customer will be notified.</p>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-semibold">Rejection Explanation <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control bg-dark border-secondary text-white" rows="3" placeholder="e.g. Claim filed after policy expiry / Proof photo shows user-inflicted physical damage" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Proof Photos Gallery -->
<div class="modal fade" id="proofPhotosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="bi bi-images text-info me-2"></i>Customer Proof Photos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4" id="proof-photos-container">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>
</div>

<!-- Audio Chime for Live Notification (Web Audio API synthesis for zero external dependency) -->
<script>
let lastKnownId = {{ \App\Models\OrderReplacement::max('id') ?: 0 }};

function playNotificationChime() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
        osc.frequency.setValueAtTime(880, audioCtx.currentTime + 0.1); // A5
        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + 0.4);
    } catch(e) {}
}

function openAssignModal(id, repNumber, orderNumber, riderId = '', pickupDate = '') {
    document.getElementById('assignForm').action = `/order-manager/replacements/${id}/approve-and-assign`;
    document.getElementById('modal-rep-number').innerText = repNumber;
    document.getElementById('modal-order-number').innerText = '#' + orderNumber;
    if (riderId) document.getElementById('modal-rider-select').value = riderId;
    if (pickupDate) document.getElementById('modal-pickup-date').value = pickupDate;
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

function openRejectModal(id, repNumber) {
    document.getElementById('rejectForm').action = `/order-manager/replacements/${id}/reject`;
    document.getElementById('reject-modal-rep-number').innerText = repNumber;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function viewProofImages(images) {
    const container = document.getElementById('proof-photos-container');
    container.innerHTML = '';
    if (!images || images.length === 0) {
        container.innerHTML = '<p class="text-secondary">No photos attached.</p>';
    } else {
        let html = '<div class="row g-3 justify-content-center">';
        images.forEach(img => {
            html += `
                <div class="col-6 col-md-4">
                    <a href="/storage/${img}" target="_blank">
                        <img src="/storage/${img}" class="img-fluid rounded border border-secondary shadow-sm" style="max-height: 250px; object-fit: cover; width: 100%;">
                    </a>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }
    new bootstrap.Modal(document.getElementById('proofPhotosModal')).show();
}

// ── 15-SECOND BACKGROUND LIVE POLLER HEARTBEAT ──
function triggerManualPoll() {
    pollReplacementRequests(true);
}

function pollReplacementRequests(isManual = false) {
    fetch(`{{ route('order-manager.replacements.live-poll') }}?last_id=${lastKnownId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data || !data.success) return;

        // Update badge counters
        const pendingBadge = document.getElementById('metric-requested-count');
        if (pendingBadge) pendingBadge.innerText = data.pending_count;

        const navBadge = document.getElementById('nav-rep-badge');
        if (navBadge) navBadge.innerText = data.pending_count;

        // If new incoming replacement requests detected
        if (data.new_count > 0 && data.new_requests.length > 0) {
            lastKnownId = data.latest_id;
            playNotificationChime();

            // Display Toast notification
            const alertBox = document.getElementById('live-alert-placeholder');
            data.new_requests.forEach(req => {
                const toastHtml = `
                    <div class="alert alert-warning alert-dismissible fade show border-warning shadow-lg mb-3 d-flex align-items-center justify-content-between" role="alert">
                        <div>
                            <strong><i class="bi bi-bell-fill text-warning me-2"></i>New Replacement Request:</strong>
                            <span class="font-monospace fw-bold text-info">${req.replacement_number}</span> for Order <strong>#${req.order_number}</strong> (${req.item_name}) - <em>${req.reason_label}</em>.
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                alertBox.insertAdjacentHTML('afterbegin', toastHtml);
            });
        }
    })
    .catch(err => {});
}

// Start recurring 15-second heartbeat
setInterval(() => {
    pollReplacementRequests(false);
}, 15000);
</script>
@endsection
