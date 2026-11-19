@extends('marketing.layouts.app')

@section('title', 'Analytics Dashboard')
@section('header', 'Marketing Analytics & Performance')
@section('subheader', 'Real-time overview of customer app reach, device demographics, and push delivery performance.')

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('marketing.automations.index') }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
            <i class="bi bi-cpu-fill"></i>
            <span>Automations Studio</span>
        </a>
        <a href="{{ route('marketing.notifications.index') }}" class="btn btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
            <i class="bi bi-send-fill"></i>
            <span>Compose Push Alert &rarr;</span>
        </a>
    </div>
@endsection

@push('styles')
<style>
    .kpi-stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 20px 24px;
        box-shadow: var(--shadow-card);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-card-hover);
    }
</style>
@endpush

@section('content')

<!-- ======================================================== -->
<!-- 1. HIGH-LEVEL KPI METRICS SCORECARDS                     -->
<!-- ======================================================== -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="kpi-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active App Installs</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #e0e7ff; color: #4338ca;">
                    <i class="bi bi-phone-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">{{ number_format($totalActiveDevices) }}</div>
            <div class="text-muted small" style="font-size: 0.78rem;">FCM push-enabled devices</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Audience Profiles</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #fdf2f8; color: #db2777;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-dark mb-1 d-flex align-items-baseline gap-2" style="letter-spacing: -0.5px;">
                <span>{{ number_format($maleDevices) }} <small class="text-muted fs-6 fw-normal">M</small></span>
                <span class="text-muted fs-5 fw-light">/</span>
                <span class="text-danger">{{ number_format($femaleDevices) }} <small class="text-danger fs-6 fw-normal">F</small></span>
            </div>
            <div class="text-muted small" style="font-size: 0.78rem;">Verified customer profiles</div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Campaigns Broadcast</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #f3e8ff; color: #7e22ce;">
                    <i class="bi bi-broadcast fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">{{ number_format($totalBroadcasts) }}</div>
            <div class="text-muted small" style="font-size: 0.78rem;">
                @if($pendingApprovals > 0)
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill font-monospace px-2 py-0.5" style="font-size: 10px;">{{ $pendingApprovals }} Pending Review</span>
                @else
                    All broadcasts approved &amp; live
                @endif
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="kpi-stat-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Pushes Delivered</span>
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background: #d1fae5; color: #059669;">
                    <i class="bi bi-check2-all fs-5"></i>
                </div>
            </div>
            <div class="fs-3 fw-bold text-success mb-1" style="letter-spacing: -0.5px;">{{ number_format($totalDelivered) }}</div>
            <div class="text-muted small" style="font-size: 0.78rem;">Lock-screen impressions</div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 3. PERFORMANCE BREAKDOWN: CAMPAIGN TYPES & REGIONS      -->
<!-- ======================================================== -->
<div class="row g-4 mb-4">
    <!-- Campaign Types Performance -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pie-chart-fill text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Campaign Performance by Type</h6>
                </div>
                <span class="text-muted small" style="font-size: 11px;">Breakdown across presets</span>
            </div>
            <div class="card-body p-4">
                @php
                    $presetNames = [
                        'seasonal' => ['🌸 Festive & Seasonal', 'bg-danger'],
                        'flash_sale' => ['⚡ Flash Sale', 'bg-warning text-dark'],
                        'birthday' => ['🎂 Birthday Special', 'bg-info text-dark'],
                        'dormant' => ['👋 Win-Back Dormant', 'bg-dark'],
                        'wallet_expiry' => ['💰 Wallet Expiry', 'bg-success'],
                        'custom' => ['🎯 Custom Campaign', 'bg-secondary'],
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th style="font-size: 0.72rem;" class="ps-3 py-2.5">Campaign Preset</th>
                                <th class="text-center py-2.5" style="font-size: 0.72rem;">Broadcasts</th>
                                <th class="text-center py-2.5" style="font-size: 0.72rem;">Phones Reached</th>
                                <th class="text-end pe-3 py-2.5" style="font-size: 0.72rem;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($typeStats as $stat)
                                @php
                                    $info = $presetNames[$stat->campaign_type] ?? ['📢 ' . ucfirst(str_replace('_', ' ', $stat->campaign_type)), 'bg-light text-dark border'];
                                @endphp
                                <tr>
                                    <td class="py-3 ps-3">
                                        <span class="fw-semibold text-dark small">{{ $info[0] }}</span>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace">{{ $stat->count }}</span>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="fw-bold text-dark small">{{ number_format($stat->total_delivered ?? 0) }}</span>
                                    </td>
                                    <td class="text-end pe-3 py-3">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2.5 py-1">Active</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">
                                        No push campaigns launched yet. Start by sending your first campaign!
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Language & Regional Audience Reach -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-translate text-success fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Language &amp; Regional Reach</h6>
                </div>
                <span class="text-muted small" style="font-size: 11px;">Pincode Mapped</span>
            </div>
            <div class="card-body p-4">
                @php
                    $langs = [
                        'te' => ['🗣️ Telugu (తెలుగు)', 'AP & Telangana (50-53xxx)', '#7c3aed'],
                        'hi' => ['🗣️ Hindi (हिंदी)', 'North & Central India', '#ea580c'],
                        'en' => ['🌐 English', 'Global / Pan-India', '#0284c7'],
                        'ta' => ['🗣️ Tamil (தமிழ்)', 'Tamil Nadu (60-64xxx)', '#059669'],
                        'kn' => ['🗣️ Kannada (ಕನ್ನಡ)', 'Karnataka (56-59xxx)', '#d97706'],
                        'all' => ['🌐 Pan-India (All)', 'All Languages & Circles', '#ec4899'],
                    ];
                @endphp

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th style="font-size: 0.72rem;" class="ps-3 py-2.5">Language &amp; Region</th>
                                <th class="text-end pe-3 py-2.5" style="font-size: 0.72rem;">Broadcasts</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($langs as $code => $data)
                                @php
                                    $found = $languageStats->firstWhere('target_language', $code);
                                    $cnt = $found ? $found->count : 0;
                                @endphp
                                <tr>
                                    <td class="py-3 ps-3">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 8px; height: 8px; background-color: {{ $data[2] }};"></span>
                                            <div>
                                                <div class="fw-semibold text-dark small" style="font-size: 0.82rem;">{{ $data[0] }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $data[1] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3 py-3">
                                        @if($cnt > 0)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace small px-2.5 py-1">
                                                {{ $cnt }} sent
                                            </span>
                                        @else
                                            <span class="text-muted small font-monospace" style="font-size: 11px;">0 sent</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 4. RECENT BROADCAST ACTIVITY LOG (PREVIEW)               -->
<!-- ======================================================== -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Recent Broadcast Activity Log</h6>
        </div>
        <a href="{{ route('marketing.notifications.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold small d-flex align-items-center gap-1.5">
            <span>View All in Push Notifications</span>
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 py-2.5" style="font-size: 0.72rem;">Notification Message</th>
                    <th class="py-2.5" style="font-size: 0.72rem;">Type</th>
                    <th class="py-2.5" style="font-size: 0.72rem;">Language</th>
                    <th class="text-center py-2.5" style="font-size: 0.72rem;">Status</th>
                    <th class="text-center py-2.5" style="font-size: 0.72rem;">Delivered</th>
                    <th class="text-end pe-4 py-2.5" style="font-size: 0.72rem;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentNotifications as $item)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark small mb-0.5">{{ $item->title }}</div>
                            <div class="text-muted small text-truncate" style="max-width: 320px; font-size: 0.78rem;">{{ $item->body }}</div>
                        </td>
                        <td class="py-3">
                            @if($item->campaign_type === 'birthday')
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill small px-2.5 py-1">🎂 Birthday</span>
                            @elseif($item->campaign_type === 'seasonal')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small px-2.5 py-1" style="background:#ffe4e6;">🌸 Festive</span>
                            @elseif($item->campaign_type === 'flash_sale')
                                <span class="badge bg-danger text-white rounded-pill small px-2.5 py-1">⚡ Flash Sale</span>
                            @elseif($item->campaign_type === 'dormant')
                                <span class="badge bg-dark text-white rounded-pill small px-2.5 py-1">👋 Win-Back</span>
                            @elseif($item->campaign_type === 'wallet_expiry')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2.5 py-1">💰 Wallet</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill small px-2.5 py-1">🎯 Custom</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border rounded-pill small font-monospace px-2 py-0.5">
                                {{ strtoupper($item->target_language ?? 'ALL') }}
                            </span>
                        </td>
                        <td class="text-center py-3">
                            @if($item->isPending())
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small px-2.5 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
                                </span>
                            @elseif($item->isApproved())
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2.5 py-1">
                                    <i class="bi bi-check2-circle me-1"></i> Broadcast
                                </span>
                            @elseif($item->isRejected())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small px-2.5 py-1">
                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                </span>
                            @endif
                        </td>
                        <td class="text-center py-3">
                            <span class="fw-bold text-dark small">{{ number_format($item->recipient_count) }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">phones</small>
                        </td>
                        <td class="text-end pe-4 py-3 text-nowrap">
                            <span class="text-dark small fw-semibold">{{ $item->created_at->format('d M Y') }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted small">
                            <i class="bi bi-bell-slash fs-4 d-block mb-1.5 text-muted"></i>
                            No campaigns recorded yet. Click "Compose &amp; Send Push Alert" to launch your first broadcast!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
