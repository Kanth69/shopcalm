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

<!-- Broadcast History Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Broadcast Push Notifications History</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill font-monospace small">
            Total Sent: {{ $notifications->total() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" style="width: 35%;">Notification Message</th>
                    <th>Audience Segmentation</th>
                    <th>App Destination</th>
                    <th class="text-center">Devices Reached</th>
                    <th>Sent By</th>
                    <th class="text-end pe-4">Sent At</th>
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
                            <span class="fw-bold text-dark fs-6">{{ number_format($item->recipient_count) }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">phones</small>
                        </td>

                        <td>
                            <small class="fw-semibold text-dark">{{ optional($item->sender)->name ?? 'Admin' }}</small>
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <span class="text-dark small fw-semibold">{{ $item->created_at->format('d M Y') }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
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
