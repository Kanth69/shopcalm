@extends('support.layouts.app')

@section('title', 'Helpdesk Dashboard')
@section('header', 'Support Operations Dashboard')

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('support.enquiries.index', ['status' => 'unread']) }}" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm">
            <i class="bi bi-envelope-exclamation me-1"></i> Open Inquiries ({{ $stats['open_enquiries'] }})
        </a>
        <a href="{{ route('support.customers.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-people me-1"></i> Customer 360°
        </a>
    </div>
@endsection

@section('content')

<!-- Hero Support Header Banner -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 position-relative" 
     style="background: linear-gradient(135deg, #100b2b 0%, #20134f 50%, #6d28d9 100%); color: #ffffff;">
    <div class="card-body p-4 p-md-4.5 position-relative" style="z-index: 2;">
        <div class="row align-items-center justify-content-between g-3">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: rgba(192, 132, 252, 0.2); color: #c084fc; border: 1px solid rgba(192, 132, 252, 0.35); font-size: 0.72rem; letter-spacing: 0.5px;">
                        <span class="spinner-grow spinner-grow-sm text-info me-1" style="width: 8px; height: 8px; color: #c084fc !important;" role="status"></span> HELPDESK OPERATIONS ONLINE
                    </span>
                    <span class="text-white-50 small">&bull; Support Queue Active</span>
                </div>
                <h3 class="fw-bold text-white mb-1" style="letter-spacing: -0.5px;">
                    Customer Care & Helpdesk Center
                </h3>
                <p class="text-white-50 small mb-0" style="max-width: 580px; line-height: 1.6;">
                    Respond to customer inquiries, view 360° customer purchase profiles, and resolve customer messages quickly.
                </p>
            </div>

            <div class="col-lg-5 text-lg-end">
                <div class="d-inline-flex flex-column flex-sm-row gap-2 bg-white bg-opacity-10 p-2 rounded-4 border border-white border-opacity-15 backdrop-blur">
                    <div class="px-3 py-1.5 text-center">
                        <div class="text-white-50" style="font-size: 0.68rem; text-transform: uppercase;">Open Inquiries</div>
                        <div class="h4 fw-bolder text-white mb-0 mt-0.5">{{ $stats['open_enquiries'] }}</div>
                    </div>
                    <div class="vr bg-white opacity-25 d-none d-sm-block"></div>
                    <div class="px-3 py-1.5 text-center">
                        <div class="text-white-50" style="font-size: 0.68rem; text-transform: uppercase;">Resolved Today</div>
                        <div class="h4 fw-bolder text-white mb-0 mt-0.5">{{ $stats['resolved_today'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4 KPI Metric Cards -->
<div class="row g-3 g-md-4 mb-4">
    <!-- Card 1: Open Inquiries -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('support.enquiries.index', ['status' => 'unread']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative overflow-hidden kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #ef4444 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.12); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-envelope-exclamation"></i>
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                        Needs Reply
                    </span>
                </div>
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Open Inquiries</div>
                <div class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['open_enquiries'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
                    <span>Awaiting agent response</span>
                    <i class="bi bi-arrow-right text-danger"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 2: In Progress -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('support.enquiries.index', ['status' => 'in_progress']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative overflow-hidden kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #f59e0b !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.7rem; color: #b45309 !important;">
                        In Progress
                    </span>
                </div>
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">In Progress</div>
                <div class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['in_progress'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
                    <span>Agent working on ticket</span>
                    <i class="bi bi-arrow-right text-warning"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 3: Resolved Today -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('support.enquiries.index', ['status' => 'resolved']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative overflow-hidden kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #10b981 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                        Today's SLA
                    </span>
                </div>
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Resolved Today</div>
                <div class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['resolved_today'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
                    <span>Inquiries closed</span>
                    <i class="bi bi-arrow-right text-success"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Card 4: Total Customers -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('support.customers.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative overflow-hidden kpi-card" 
                 style="background: #ffffff; border-top: 4px solid #8b5cf6 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(139, 92, 246, 0.12); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="badge bg-purple bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1" style="font-size: 0.7rem; color: #8b5cf6 !important; background: #f3e8ff !important;">
                        Directory
                    </span>
                </div>
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Registered Customers</div>
                <div class="h2 fw-bolder text-dark mb-1 mt-1">{{ $stats['total_customers'] }}</div>
                <div class="text-secondary small d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
                    <span>View customer 360°</span>
                    <i class="bi bi-arrow-right text-primary"></i>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Priority Inquiries Table (8 Cols) -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 1rem;">
                        <i class="bi bi-exclamation-diamond-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Priority Customer Inquiries</h6>
                        <span class="text-muted small" style="font-size: 0.72rem;">Customer contact requests awaiting agent response</span>
                    </div>
                </div>
                <a href="{{ route('support.enquiries.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 0.75rem;">
                    View All Inquiries <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                        <tr>
                            <th class="ps-4 py-3">Customer</th>
                            <th class="py-3">Subject & Message</th>
                            <th class="py-3">Received</th>
                            <th class="py-3">Status</th>
                            <th class="pe-4 py-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($urgentEnquiries as $enquiry)
                            @php
                                $statusBadge = match($enquiry->status) {
                                    'unread'      => ['bg' => '#fee2e2', 'color' => '#dc2626', 'label' => 'Unread / Open'],
                                    'in_progress' => ['bg' => '#fef3c7', 'color' => '#d97706', 'label' => 'In Progress'],
                                    'resolved'    => ['bg' => '#d1fae5', 'color' => '#059669', 'label' => 'Resolved'],
                                    default       => ['bg' => '#f1f5f9', 'color' => '#475569', 'label' => ucfirst($enquiry->status)],
                                };
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light text-secondary fw-bold d-flex align-items-center justify-content-center border" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($enquiry->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $enquiry->name }}</div>
                                            <div class="text-muted" style="font-size: 0.7rem;">{{ $enquiry->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td style="max-width: 280px;">
                                    <div class="fw-semibold text-dark small text-truncate">{{ $enquiry->subject }}</div>
                                    <div class="text-muted small text-truncate" style="font-size: 0.72rem;">{{ $enquiry->message }}</div>
                                </td>

                                <td class="text-muted small" style="font-size: 0.72rem;">
                                    {{ $enquiry->created_at->diffForHumans() }}
                                </td>

                                <td>
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background: {{ $statusBadge['bg'] }}; color: {{ $statusBadge['color'] }}; font-size: 0.7rem;">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                </td>

                                <td class="pe-4 text-end">
                                    <a href="{{ route('support.enquiries.show', $enquiry) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 shadow-xs fw-bold" style="font-size: 0.72rem; background: #8b5cf6; border-color: #8b5cf6;">
                                        <span>Respond</span> <i class="bi bi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="py-3">
                                        <i class="bi bi-check2-all text-success display-6 d-block mb-2 opacity-50"></i>
                                        <h6 class="fw-bold text-dark mb-1">Inquiry Inbox is Clear!</h6>
                                        <span class="small">All customer inquiries have been addressed.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Recently Resolved & Recent Customers (4 Cols) -->
    <div class="col-lg-4">
        <!-- Recently Resolved Inquiries -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; background: #d1fae5; color: #059669;">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Recently Resolved</h6>
                </div>
                <a href="{{ route('support.enquiries.index', ['status' => 'resolved']) }}" class="text-primary small text-decoration-none" style="font-size: 0.72rem;">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentlyResolved->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentlyResolved as $res)
                            <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="overflow-hidden pe-2">
                                    <a href="{{ route('support.enquiries.show', $res) }}" class="fw-bold text-dark text-decoration-none small text-truncate d-block">
                                        {{ $res->subject }}
                                    </a>
                                    <div class="text-muted small text-truncate" style="font-size: 0.7rem;">
                                        By {{ $res->name }} &bull; {{ $res->resolved_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-0.5 rounded-pill small flex-shrink-0" style="font-size: 0.65rem;">
                                    Closed
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">No recently resolved tickets.</div>
                @endif
            </div>
        </div>

        <!-- Recent Customers Directory -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.9rem; background: #f3e8ff; color: #7c3aed;">
                        <i class="bi bi-people"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Customer Profiles</h6>
                </div>
                <a href="{{ route('support.customers.index') }}" class="text-primary small text-decoration-none" style="font-size: 0.72rem;">Directory</a>
            </div>
            <div class="card-body p-0">
                @if($recentCustomers->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentCustomers as $cust)
                            <div class="list-group-item p-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 overflow-hidden pe-2">
                                    <div class="rounded-circle bg-light text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($cust->name, 0, 1)) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        <a href="{{ route('support.customers.show', $cust) }}" class="fw-bold text-dark text-decoration-none small text-truncate d-block">
                                            {{ $cust->name }}
                                        </a>
                                        <div class="text-muted text-truncate" style="font-size: 0.68rem;">{{ $cust->email }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill small flex-shrink-0" style="font-size: 0.68rem;">
                                    {{ $cust->orders_count }} {{ Str::plural('order', $cust->orders_count) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted small">No customers registered yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .kpi-card {
        transition: all 0.22s cubic-bezier(.4,0,.2,1);
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 25px -5px rgba(0, 0, 0, 0.08), 0 0 1px 1px rgba(0, 0, 0, 0.04) !important;
    }
</style>
@endpush

@endsection
