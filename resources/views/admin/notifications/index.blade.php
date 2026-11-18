@extends('admin.layouts.app')

@section('header', 'Mobile App Push Notifications Console')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Push Notifications</li>
@endsection

@section('actions')
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
        <i class="bi bi-send-fill me-1"></i> Compose & Send Push Alert
    </a>
@endsection

@section('content')
<!-- Audience Demographics & Delivery Scorecards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Active Devices</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill p-2"><i class="bi bi-phone-fill fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalActiveDevices) }}</h3>
            <small class="text-muted">Registered Customer Apps</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Male Users</span>
                <span class="badge bg-info-subtle text-info rounded-pill p-2"><i class="bi bi-gender-male fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($maleDevices) }}</h3>
            <small class="text-muted">Verified Male Profiles</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Female Users</span>
                <span class="badge bg-pink-subtle text-danger rounded-pill p-2" style="background-color: #ffe4e6;"><i class="bi bi-gender-female fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($femaleDevices) }}</h3>
            <small class="text-muted">Verified Female Profiles</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Campaigns Sent</span>
                <span class="badge bg-purple-subtle text-purple rounded-pill p-2"><i class="bi bi-broadcast fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBroadcasts) }}</h3>
            <small class="text-muted">Broadcast Campaigns</small>
        </div>
    </div>

    <div class="col-sm-12 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Delivered Pushes</span>
                <span class="badge bg-success-subtle text-success rounded-pill p-2"><i class="bi bi-check2-all fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDelivered) }}</h3>
            <small class="text-muted">Total Push Alerts Dispatched</small>
        </div>
    </div>
</div>

<!-- Pending Approval Requests (Marketing Submissions) -->
@if(isset($pendingNotifications) && $pendingNotifications->isNotEmpty())
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 border-start border-4 border-warning">
    <div class="card-header bg-warning-subtle py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark p-2 rounded-circle">
                <i class="bi bi-hourglass-split fs-6"></i>
            </span>
            <div>
                <h6 class="mb-0 fw-bold text-dark">Marketing Team Push Notifications — Awaiting Approval</h6>
                <small class="text-muted">Review campaign drafts submitted by the marketing team. Approve to broadcast live to customer apps.</small>
            </div>
        </div>
        <span class="badge bg-warning text-dark border border-warning px-3 py-1.5 rounded-pill fw-bold">
            {{ $pendingNotifications->count() }} Pending Review
        </span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" style="width: 32%;">Notification Content</th>
                    <th>Target Audience</th>
                    <th>App Destination</th>
                    <th>Submitted By</th>
                    <th class="text-end pe-4">Super Admin Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingNotifications as $pending)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-start gap-2.5">
                                @if($pending->banner_image)
                                    <img src="{{ $pending->banner_image }}" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;" alt="Banner">
                                @else
                                    <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-warning" style="width: 48px; height: 48px; min-width: 48px;">
                                        <i class="bi bi-bell-fill fs-5"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $pending->title }}</div>
                                    <div class="text-secondary small text-truncate" style="max-width: 320px;" title="{{ $pending->body }}">
                                        {{ $pending->body }}
                                    </div>
                                    @if($pending->banner_image)
                                        <a href="{{ $pending->banner_image }}" target="_blank" class="small text-decoration-none text-primary" style="font-size: 11px;">
                                            <i class="bi bi-image me-1"></i>View Banner Image
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if($pending->target_gender === 'Male')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small">Male Only</span>
                                @elseif($pending->target_gender === 'Female')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">Female Only</span>
                                @else
                                    <span class="badge bg-light text-dark border rounded-pill small">All Genders</span>
                                @endif

                                @if($pending->target_age_group === 'birthday_month')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small">🎂 Birthday Month</span>
                                @elseif($pending->target_age_group === 'under_25')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">Under 25</span>
                                @elseif($pending->target_age_group === '25_40')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">25 - 40 Yrs</span>
                                @elseif($pending->target_age_group === 'over_40')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">40+ Yrs</span>
                                @endif

                                @if($pending->target_activity === 'dormant')
                                    <span class="badge bg-dark text-white rounded-pill small">Dormant Users</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($pending->destination_type === 'category' && $pending->category)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                    <i class="bi bi-grid-fill me-1"></i> {{ $pending->category->name }}
                                </span>
                            @elseif($pending->destination_type === 'offers')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                    <i class="bi bi-percent me-1"></i> Offers Hub
                                </span>
                            @elseif($pending->destination_type === 'cart')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <i class="bi bi-bag-fill me-1"></i> Cart Screen
                                </span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill">
                                    <i class="bi bi-house-door-fill me-1"></i> App Home
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold text-dark small">{{ optional($pending->sender)->name ?? 'Marketing Staff' }}</div>
                            <small class="text-muted" style="font-size: 11px;">{{ $pending->created_at->diffForHumans() }}</small>
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <div class="d-inline-flex gap-2">
                                <form action="{{ route('admin.notifications.approve', $pending->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Approve and immediately broadcast this push notification to all matching customer devices?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm">
                                        <i class="bi bi-check-circle-fill me-1"></i> Approve & Broadcast
                                    </button>
                                </form>

                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $pending->id }}">
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            </div>

                            <!-- Reject Reason Modal -->
                            <div class="modal fade text-start" id="rejectModal{{ $pending->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <form action="{{ route('admin.notifications.reject', $pending->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-bottom py-3">
                                                <h6 class="modal-title fw-bold text-dark">
                                                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Reject Push Notification Draft
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                    <div class="fw-bold text-dark small mb-1">{{ $pending->title }}</div>
                                                    <div class="text-muted small">{{ $pending->body }}</div>
                                                </div>

                                                <label class="form-label fw-semibold text-dark small">Reason / Feedback for Marketing Team <span class="text-danger">*</span></label>
                                                <textarea name="rejection_reason" class="form-control rounded-3" rows="3" required minlength="5" placeholder="e.g. Please revise notification copy or banner URL before re-submitting."></textarea>
                                                <div class="form-text small text-muted">This feedback will be visible to the marketing team on their dashboard.</div>
                                            </div>
                                            <div class="modal-footer bg-light border-top py-2">
                                                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Confirm Rejection
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Broadcast History Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Push Notifications Campaign Log</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total Records: {{ $notifications->total() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" style="width: 30%;">Notification Message</th>
                    <th>Audience Segmentation</th>
                    <th>App Destination</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Delivered</th>
                    <th>Sent By</th>
                    <th class="text-end pe-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-start gap-2.5">
                                @if($item->banner_image)
                                    <img src="{{ $item->banner_image }}" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;" alt="Banner">
                                @else
                                    <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-primary" style="width: 48px; height: 48px; min-width: 48px;">
                                        <i class="bi bi-bell fs-5"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $item->title }}</div>
                                    <div class="text-secondary small text-truncate" style="max-width: 320px;" title="{{ $item->body }}">
                                        {{ $item->body }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <!-- Gender Tag -->
                                @if($item->target_gender === 'Male')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small">
                                        <i class="bi bi-gender-male me-1"></i> Male Only
                                    </span>
                                @elseif($item->target_gender === 'Female')
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">
                                        <i class="bi bi-gender-female me-1"></i> Female Only
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border rounded-pill small">
                                        All Genders
                                    </span>
                                @endif

                                <!-- Age / DOB Tag -->
                                @if($item->target_age_group === 'birthday_month')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small">
                                        🎂 Birthday Month
                                    </span>
                                @elseif($item->target_age_group === 'under_25')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">Under 25</span>
                                @elseif($item->target_age_group === '25_40')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">25 - 40 Yrs</span>
                                @elseif($item->target_age_group === 'over_40')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">40+ Yrs</span>
                                @endif

                                <!-- Activity Tag -->
                                @if($item->target_activity === 'dormant')
                                    <span class="badge bg-dark text-white rounded-pill small">Dormant Users</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($item->destination_type === 'category' && $item->category)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                    <i class="bi bi-grid-fill me-1"></i> Category: {{ $item->category->name }}
                                </span>
                            @elseif($item->destination_type === 'offers')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">
                                    <i class="bi bi-percent me-1"></i> Offers Hub
                                </span>
                            @elseif($item->destination_type === 'cart')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                    <i class="bi bi-bag-fill me-1"></i> Cart Screen
                                </span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill">
                                    <i class="bi bi-house-door-fill me-1"></i> App Home
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($item->isPending())
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
                                </span>
                            @elseif($item->isApproved())
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                    <i class="bi bi-check2-circle me-1"></i> Broadcast
                                </span>
                            @elseif($item->isRejected())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" title="{{ $item->rejection_reason }}">
                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($item->isApproved())
                                <span class="fw-bold text-dark fs-6">{{ number_format($item->recipient_count) }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">phones</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold text-dark small">{{ optional($item->sender)->name ?? 'Admin' }}</div>
                            @if($item->isApproved() && $item->approver)
                                <small class="text-muted d-block" style="font-size: 10px;">Appr: {{ $item->approver->name }}</small>
                            @elseif($item->isRejected() && $item->rejection_reason)
                                <small class="text-danger d-block text-truncate" style="font-size: 10px; max-width: 120px;" title="{{ $item->rejection_reason }}">
                                    {{ $item->rejection_reason }}
                                </small>
                            @endif
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <span class="text-dark small fw-semibold">{{ $item->created_at->format('d M Y') }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-bell-slash display-4 text-muted opacity-50"></i>
                                <h5 class="fw-bold mt-3 text-dark">No Push Alerts Sent Yet</h5>
                                <p class="text-muted small mb-4">Broadcast targeted alerts directly to customer lock-screens filtered by gender, birthday, and age.</p>
                                <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-send-fill me-1"></i> Compose First Push Notification
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
