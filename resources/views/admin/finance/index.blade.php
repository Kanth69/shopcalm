@extends('admin.layouts.app')

@section('header', 'Executive Financial & Profit Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Financial Dashboard</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.reports.index', array_merge(['type' => 'profit_loss'], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" class="btn btn-outline-primary rounded-pill px-3 shadow-xs fw-semibold" style="font-size: 0.85rem;">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Full P&L Ledger
        </a>
        <a href="{{ route('admin.reports.index', array_merge(['type' => 'profit_loss', 'export' => 1], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" class="btn btn-success rounded-pill px-3.5 shadow-xs fw-semibold" style="font-size: 0.85rem;">
            <i class="bi bi-download me-1"></i> Export P&L CSV
        </a>
    </div>
@endsection

@section('content')

{{-- 1. Executive Banner & Controls --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border: 1px solid rgba(255,255,255,0.08) !important;">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                     style="width: 54px; height: 54px; background: rgba(245, 158, 11, 0.15); border: 2px solid rgba(245, 158, 11, 0.35); color: #f59e0b;">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark px-2.5 py-1 fw-bolder rounded-pill" style="font-size: 0.68rem; letter-spacing: 0.06em;">
                            SUPER ADMIN EXCLUSIVE
                        </span>
                        <span class="text-white-50 small">&bull; Real-Time Unit Economics & P&L Analysis</span>
                    </div>
                    <h4 class="mb-0 fw-bolder text-white" style="letter-spacing: -0.02em;">Financial Health & Profitability Ledger</h4>
                </div>
            </div>

            {{-- Time Period Filter Pills --}}
            <div class="d-flex align-items-center gap-1.5 bg-dark bg-opacity-75 p-1.5 rounded-pill border border-secondary border-opacity-25 flex-wrap">
                <a href="{{ route('admin.finance.index', ['range' => 'today', 'status_scope' => $statusScope]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-bold {{ $range == 'today' ? 'btn-primary' : 'text-white-50' }}" style="font-size: 0.78rem;">
                    Today
                </a>
                <a href="{{ route('admin.finance.index', ['range' => 'last_7_days', 'status_scope' => $statusScope]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-bold {{ $range == 'last_7_days' ? 'btn-primary' : 'text-white-50' }}" style="font-size: 0.78rem;">
                    7 Days
                </a>
                <a href="{{ route('admin.finance.index', ['range' => 'this_month', 'status_scope' => $statusScope]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-bold {{ $range == 'this_month' ? 'btn-primary' : 'text-white-50' }}" style="font-size: 0.78rem;">
                    This Month
                </a>
                <a href="{{ route('admin.finance.index', ['range' => 'this_year', 'status_scope' => $statusScope]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-bold {{ $range == 'this_year' ? 'btn-primary' : 'text-white-50' }}" style="font-size: 0.78rem;">
                    This Year
                </a>
                <a href="{{ route('admin.finance.index', ['range' => 'all_time', 'status_scope' => $statusScope]) }}" 
                   class="btn btn-sm rounded-pill px-3 fw-bold {{ $range == 'all_time' ? 'btn-primary' : 'text-white-50' }}" style="font-size: 0.78rem;">
                    All Time
                </a>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 fw-bold {{ $range == 'custom' ? 'btn-warning text-dark shadow-sm' : 'text-white-50' }}" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#customDateFilterCollapse" 
                        aria-expanded="{{ $range == 'custom' ? 'true' : 'false' }}"
                        aria-controls="customDateFilterCollapse"
                        style="font-size: 0.78rem;">
                    <i class="bi bi-calendar3 me-1"></i> Custom Range
                </button>
            </div>
        </div>

        {{-- Collapsible Custom Date Form --}}
        <div class="collapse {{ $range == 'custom' ? 'show' : '' }} mb-3" id="customDateFilterCollapse">
            <form action="{{ route('admin.finance.index') }}" method="GET" class="p-3 rounded-4 border border-warning border-opacity-25" style="background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(10px);">
                <input type="hidden" name="range" value="custom">
                <input type="hidden" name="status_scope" value="{{ $statusScope }}">
                <div class="row g-2.5 align-items-center">
                    <div class="col-12 col-lg-auto text-white-50 small fw-bold text-uppercase d-flex align-items-center gap-1.5" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                        <i class="bi bi-calendar-range text-warning fs-6"></i>
                        <span>Custom Date Filter:</span>
                    </div>
                    <div class="col-6 col-sm-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary border-opacity-50 text-white-50 px-2.5" style="font-size: 0.75rem;">From</span>
                            <input type="date" name="start_date" 
                                   value="{{ request('start_date', $start->format('Y-m-d')) }}" 
                                   class="form-control form-control-sm bg-dark text-white border-secondary border-opacity-50" 
                                   max="{{ now()->format('Y-m-d') }}" 
                                   required 
                                   style="color-scheme: dark; font-size: 0.8rem;">
                        </div>
                    </div>
                    <div class="col-6 col-sm-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary border-opacity-50 text-white-50 px-2.5" style="font-size: 0.75rem;">To</span>
                            <input type="date" name="end_date" 
                                   value="{{ request('end_date', $end->format('Y-m-d')) }}" 
                                   class="form-control form-control-sm bg-dark text-white border-secondary border-opacity-50" 
                                   max="{{ now()->format('Y-m-d') }}" 
                                   required 
                                   style="color-scheme: dark; font-size: 0.8rem;">
                        </div>
                    </div>
                    <div class="col-12 col-sm-auto d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs" style="font-size: 0.78rem;">
                            <i class="bi bi-funnel me-1"></i> Apply Filter
                        </button>
                        @if($range == 'custom')
                            <a href="{{ route('admin.finance.index', ['range' => 'this_month', 'status_scope' => $statusScope]) }}" 
                               class="btn btn-sm btn-outline-light rounded-pill px-2.5 text-white-50 border-secondary border-opacity-50" 
                               style="font-size: 0.78rem;" title="Reset to This Month">
                                <i class="bi bi-x-circle me-1"></i> Reset
                            </a>
                        @endif
                    </div>
                    <div class="col-12 col-lg text-lg-end text-white-50 small" style="font-size: 0.75rem;">
                        Active Window: <strong class="text-white">{{ $start->format('d M Y') }}</strong> &mdash; <strong class="text-white">{{ $end->format('d M Y') }}</strong>
                        <span class="badge bg-warning bg-opacity-25 text-warning ms-1 px-2 py-0.5 rounded-pill">{{ $start->diffInDays($end) + 1 }} {{ Str::plural('day', $start->diffInDays($end) + 1) }}</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Order Status Scope Filter Bar with Exact Counts --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-secondary border-opacity-25" style="font-size: 0.8rem;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-white-50 fw-bold small text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Order Scope:</span>
                
                {{-- 1. All Orders (Matches Dashboard & Lifetime Orders Count) --}}
                <a href="{{ route('admin.finance.index', array_merge(['range' => $range, 'status_scope' => 'all'], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" 
                   class="badge text-decoration-none rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 {{ $statusScope == 'all' ? 'bg-primary text-white shadow-xs' : 'bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-25' }}">
                    <span>All Orders</span>
                    <span class="badge {{ $statusScope == 'all' ? 'bg-white text-primary' : 'bg-dark text-white-50' }} rounded-pill px-1.5 py-0.5 fw-bold" style="font-size: 0.68rem;">{{ $scopeCounts['all'] }}</span>
                </a>

                {{-- 2. Realized / Active Sales (Confirmed, In-Transit, Delivered) --}}
                <a href="{{ route('admin.finance.index', array_merge(['range' => $range, 'status_scope' => 'realized'], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" 
                   class="badge text-decoration-none rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 {{ $statusScope == 'realized' ? 'bg-primary text-white shadow-xs' : 'bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-25' }}">
                    <span>Realized & Active Sales</span>
                    <span class="badge {{ $statusScope == 'realized' ? 'bg-white text-primary' : 'bg-dark text-white-50' }} rounded-pill px-1.5 py-0.5 fw-bold" style="font-size: 0.68rem;">{{ $scopeCounts['realized'] }}</span>
                </a>

                {{-- 3. Delivered & Completed Only --}}
                <a href="{{ route('admin.finance.index', array_merge(['range' => $range, 'status_scope' => 'delivered'], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" 
                   class="badge text-decoration-none rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 {{ $statusScope == 'delivered' ? 'bg-primary text-white shadow-xs' : 'bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-25' }}">
                    <span>Delivered Only</span>
                    <span class="badge {{ $statusScope == 'delivered' ? 'bg-white text-primary' : 'bg-dark text-white-50' }} rounded-pill px-1.5 py-0.5 fw-bold" style="font-size: 0.68rem;">{{ $scopeCounts['delivered'] }}</span>
                </a>

                {{-- 4. Paid Status Verified --}}
                <a href="{{ route('admin.finance.index', array_merge(['range' => $range, 'status_scope' => 'paid'], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" 
                   class="badge text-decoration-none rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 {{ $statusScope == 'paid' ? 'bg-primary text-white shadow-xs' : 'bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-25' }}">
                    <span>Paid Verified</span>
                    <span class="badge {{ $statusScope == 'paid' ? 'bg-white text-primary' : 'bg-dark text-white-50' }} rounded-pill px-1.5 py-0.5 fw-bold" style="font-size: 0.68rem;">{{ $scopeCounts['paid'] }}</span>
                </a>

                {{-- 5. Cancelled / Failed Attempts --}}
                @if($scopeCounts['cancelled'] > 0)
                <a href="{{ route('admin.finance.index', array_merge(['range' => $range, 'status_scope' => 'cancelled'], $range === 'custom' ? ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()] : [])) }}" 
                   class="badge text-decoration-none rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 {{ $statusScope == 'cancelled' ? 'bg-danger text-white shadow-xs' : 'bg-secondary bg-opacity-25 text-white-50 border border-secondary border-opacity-25' }}">
                    <span>Cancelled / Failed</span>
                    <span class="badge {{ $statusScope == 'cancelled' ? 'bg-white text-danger' : 'bg-dark text-white-50' }} rounded-pill px-1.5 py-0.5 fw-bold" style="font-size: 0.68rem;">{{ $scopeCounts['cancelled'] }}</span>
                </a>
                @endif
            </div>

            <div class="text-white-50 small d-flex align-items-center gap-2">
                <span>Showing <strong>{{ $ordersCount }}</strong> of <strong>{{ $scopeCounts['all'] }} total orders</strong> ({{ $totalItemsCount }} items)</span>
                @if($scopeCounts['cancelled'] > 0)
                    <span class="badge bg-secondary bg-opacity-50 text-white-50 rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                        {{ $scopeCounts['realized'] }} Realized &bull; {{ $scopeCounts['cancelled'] }} Cancelled
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 2. Top 4 High-Impact KPI Metrics --}}
<div class="row g-3 mb-4">
    {{-- Gross Revenue --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Gross Revenue</div>
                    <div class="fs-4 fw-bolder text-dark font-monospace">₹{{ number_format($totalRevenue, 2) }}</div>
                    <span class="text-muted small" style="font-size: 0.74rem;">Avg Ticket: ₹{{ number_format($avgTicketSize, 2) }}</span>
                </div>
            </div>
            <div class="position-absolute bottom-0 start-0 end-0" style="height: 4px; background: #6366f1;"></div>
        </div>
    </div>

    {{-- COGS --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-box-arrow-right fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Product Cost (COGS)</div>
                    <div class="fs-4 fw-bolder text-danger font-monospace">₹{{ number_format($totalCost, 2) }}</div>
                    <span class="text-muted small" style="font-size: 0.74rem;">Wholesale Acquisition</span>
                </div>
            </div>
            <div class="position-absolute bottom-0 start-0 end-0" style="height: 4px; background: #ef4444;"></div>
        </div>
    </div>

    {{-- Gross Profit --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-piggy-bank fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Gross Margin Profit</div>
                    <div class="fs-4 fw-bolder font-monospace {{ $totalGrossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $totalGrossProfit >= 0 ? '+' : '' }}₹{{ number_format($totalGrossProfit, 2) }}
                    </div>
                    <span class="badge rounded-pill px-2.5 py-0.5 fw-bold {{ $overallMargin >= 20 ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.72rem;">
                        {{ $overallMargin }}% Margin
                    </span>
                </div>
            </div>
            <div class="position-absolute bottom-0 start-0 end-0" style="height: 4px; background: #10b981;"></div>
        </div>
    </div>

    {{-- Real In-Hand Profit --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Real In-Hand Net</div>
                    <div class="fs-4 fw-bolder font-monospace {{ $netInHandProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $netInHandProfit >= 0 ? '+' : '' }}₹{{ number_format($netInHandProfit, 2) }}
                    </div>
                    <span class="text-muted small" style="font-size: 0.74rem;">After Courier & 2% Fees</span>
                </div>
            </div>
            <div class="position-absolute bottom-0 start-0 end-0" style="height: 4px; background: #f59e0b;"></div>
        </div>
    </div>
</div>

{{-- 3. Revenue & Price Reconciliation Breakdown Banner (Original Price, Cart Subtotal, Deductions, Net In-Hand) --}}
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0 !important;">
    <div class="row g-3 align-items-center">
        <div class="col-md-2">
            <h6 class="fw-bold text-dark mb-1">
                <i class="bi bi-calculator text-primary me-1.5"></i>Price Breakdown
            </h6>
            <p class="text-muted small mb-0" style="font-size: 0.74rem;">Original MRP &rarr; Cart Subtotal &rarr; Deductions &rarr; Net In-Hand.</p>
        </div>
        <div class="col-md-10">
            <div class="row g-2 text-center text-md-start">
                {{-- 1. Original Price (MRP) --}}
                <div class="col-6 col-md-2 p-2.5 bg-white rounded-3 border shadow-2xs">
                    <span class="text-muted small d-block fw-semibold" style="font-size: 0.7rem;">Original Price (MRP)</span>
                    <strong class="text-dark small font-monospace" style="font-size: 0.92rem;">₹{{ number_format($totalOriginalPrice, 2) }}</strong>
                    <span class="text-muted d-block" style="font-size: 0.65rem;">Base Listed Value</span>
                </div>

                {{-- 2. Cart Items Subtotal --}}
                <div class="col-6 col-md-2 p-2.5 bg-white rounded-3 border shadow-2xs">
                    <span class="text-muted small d-block fw-semibold" style="font-size: 0.7rem;">Cart Subtotal</span>
                    <strong class="text-primary small font-monospace" style="font-size: 0.92rem;">₹{{ number_format($totalItemsSubtotal, 2) }}</strong>
                    <span class="text-muted d-block text-truncate" style="font-size: 0.65rem;" title="Offer Discount: -₹{{ number_format($totalOfferDiscount, 2) }}">
                        Offers: -₹{{ number_format($totalOfferDiscount, 2) }}
                    </span>
                </div>

                {{-- 3. Coupon Discounts --}}
                <div class="col-6 col-md-2 p-2.5 bg-white rounded-3 border shadow-2xs">
                    <span class="text-muted small d-block fw-semibold" style="font-size: 0.7rem;">Coupons Applied</span>
                    <strong class="text-warning small font-monospace" style="font-size: 0.92rem;">
                        {{ $totalCouponDiscount > 0 ? '-' : '' }}₹{{ number_format($totalCouponDiscount, 2) }}
                    </strong>
                    <span class="text-muted d-block" style="font-size: 0.65rem;">Promo Discounts</span>
                </div>

                {{-- 4. Wallet Used --}}
                <div class="col-6 col-md-2 p-2.5 bg-white rounded-3 border shadow-2xs">
                    <span class="text-muted small d-block fw-semibold" style="font-size: 0.7rem;">Wallet Used</span>
                    <strong class="text-info small font-monospace" style="font-size: 0.92rem;">
                        {{ $totalWalletUsed > 0 ? '-' : '' }}₹{{ number_format($totalWalletUsed, 2) }}
                    </strong>
                    <span class="text-muted d-block" style="font-size: 0.65rem;">Credits Redeemed</span>
                </div>

                {{-- 5. Delivery Charge (₹45 Fixed) --}}
                <div class="col-6 col-md-2 p-2.5 bg-white rounded-3 border shadow-2xs">
                    <span class="text-muted small d-block fw-semibold" style="font-size: 0.7rem;">Delivery (₹45 Fixed)</span>
                    <strong class="text-danger small font-monospace" style="font-size: 0.92rem;">
                        {{ $totalFixedDelivery > 0 ? '-' : '' }}₹{{ number_format($totalFixedDelivery, 2) }}
                    </strong>
                    <span class="text-muted d-block" style="font-size: 0.65rem;">₹45 &times; {{ $ordersCount }} {{ Str::plural('ord', $ordersCount) }}</span>
                </div>

                {{-- 6. Net In-Hand (Display) --}}
                @php
                    $totalDeductions = $totalOfferDiscount + $totalCouponDiscount + $totalWalletUsed + $totalFixedDelivery;
                @endphp
                <div class="col-6 col-md-2 p-2.5 bg-white rounded-3 border border-success border-opacity-50 shadow-2xs" style="background: rgba(16, 185, 129, 0.05) !important;">
                    <span class="text-success small d-block fw-bold" style="font-size: 0.7rem;">6. Net Realized In-Hand</span>
                    <strong class="text-success small font-monospace" style="font-size: 0.92rem;">
                        {{ $netDisplayAmount >= 0 ? '' : '-' }}₹{{ number_format(abs($netDisplayAmount), 2) }}
                    </strong>
                    <span class="text-success text-opacity-75 d-block text-truncate" style="font-size: 0.63rem;" title="MRP (₹{{ number_format($totalOriginalPrice, 0) }}) &minus; Total Deductions (₹{{ number_format($totalDeductions, 0) }})">
                        MRP &minus; Deductions (₹{{ number_format($totalDeductions, 0) }})
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Visual Charts Grid --}}
<div class="row g-4 mb-4">
    {{-- Financial Trend Chart (70% width) --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i>Financial Trajectory Trend
                    </h6>
                    <span class="text-muted small">Revenue Collected vs Product Acquisition Cost vs Gross Profit</span>
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 font-monospace small">
                    {{ \Carbon\Carbon::parse($start)->format('d M') }} &rarr; {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
                </span>
            </div>
            <div style="height: 320px;">
                <canvas id="financeTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Payment Channels Breakdown (30% width) --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <h6 class="fw-bold text-dark mb-1">
                <i class="bi bi-pie-chart text-info me-2"></i>Payment Channels Profit
            </h6>
            <span class="text-muted small mb-3 d-block">COD vs Razorpay / Online Gateways</span>
            <div style="height: 200px;" class="d-flex align-items-center justify-content-center mb-3">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <div class="d-flex flex-column gap-2 border-top pt-3">
                @forelse($paymentMethods as $pm)
                <div class="d-flex align-items-center justify-content-between small">
                    <span class="fw-semibold text-dark">{{ $pm['name'] }} ({{ $pm['count'] }} {{ Str::plural('order', $pm['count']) }})</span>
                    <span class="fw-bold text-success font-monospace">₹{{ number_format($pm['profit'], 2) }} ({{ $pm['margin'] }}%)</span>
                </div>
                @empty
                <span class="text-muted small text-center">No payment data in this range</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- 5. Top High-Profit Products & Recent Profitable Orders --}}
<div class="row g-4 mb-4">
    {{-- Top 5 High-Profit Products --}}
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-trophy text-warning me-2"></i>Top High-Profit Products
                </h6>
                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">By Realized Margin</span>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-2">Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Gross Profit</th>
                            <th class="pe-2 text-end">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $tp)
                        <tr>
                            <td class="ps-2">
                                <div class="d-flex align-items-center gap-2">
                                    @if($tp['image'])
                                        <img src="{{ asset('storage/' . $tp['image']) }}" class="rounded border flex-shrink-0" style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            <i class="bi bi-box"></i>
                                        </div>
                                    @endif
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 140px;" title="{{ $tp['name'] }}">
                                        {{ $tp['name'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border font-monospace px-2 py-0.5" style="font-size: 0.72rem;">{{ $tp['qty'] }}</span>
                            </td>
                            <td class="text-end fw-bold text-success font-monospace" style="font-size: 0.85rem;">
                                ₹{{ number_format($tp['profit'], 2) }}
                            </td>
                            <td class="pe-2 text-end">
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold {{ $tp['margin'] >= 20 ? 'bg-success text-white' : 'bg-warning text-dark' }}" style="font-size: 0.7rem;">
                                    {{ $tp['margin'] }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4 small">No product sales recorded</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Orders Ledger Table --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>Recent Orders Ledger
                </h6>
                <a href="{{ route('admin.reports.index', ['type' => 'profit_loss']) }}" class="small fw-bold text-primary text-decoration-none">
                    View Full P&L Ledger &rarr;
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-2">Order #</th>
                            <th>Customer</th>
                            <th>Status & Payment</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">COGS</th>
                            <th class="text-end">Gross Profit</th>
                            <th class="pe-2 text-center">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $ro)
                        <tr>
                            <td class="ps-2">
                                <a href="{{ route('admin.orders.show', $ro) }}" class="fw-bold text-primary text-decoration-none font-monospace" style="font-size: 0.85rem;">
                                    #{{ $ro->order_number }}
                                </a>
                            </td>
                            <td class="small text-dark fw-medium">{{ $ro->user?->name ?? ($ro->shipping_name ?? 'Guest') }}</td>
                            <td>
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem; background: {{ match($ro->status) { 'confirmed' => '#e0e7ff', 'delivered' => '#d1fae5', 'cancelled' => '#fee2e2', default => '#fef3c7' } }}; color: {{ match($ro->status) { 'confirmed' => '#3730a3', 'delivered' => '#065f46', 'cancelled' => '#991b1b', default => '#92400e' } }};">
                                    {{ ucfirst(str_replace('_', ' ', $ro->status)) }}
                                </span>
                                <div class="text-muted small" style="font-size: 0.65rem;">
                                    {{ strtoupper($ro->payment_method ?? 'COD') }} &bull; <span class="{{ $ro->payment_status === 'paid' ? 'text-success' : 'text-warning' }}">{{ $ro->payment_status }}</span>
                                </div>
                            </td>
                            <td class="text-end fw-semibold text-dark font-monospace" style="font-size: 0.85rem;">
                                ₹{{ number_format($ro->total_amount, 2) }}
                            </td>
                            <td class="text-end text-muted font-monospace small">
                                ₹{{ number_format($ro->total_cost, 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace {{ $ro->gross_profit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.85rem;">
                                {{ $ro->gross_profit >= 0 ? '+' : '' }}₹{{ number_format($ro->gross_profit, 2) }}
                            </td>
                            <td class="pe-2 text-center">
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold {{ $ro->profit_margin >= 20 ? 'bg-success text-white' : ($ro->profit_margin >= 0 ? 'bg-warning text-dark' : 'bg-danger text-white') }}" style="font-size: 0.72rem;">
                                    {{ $ro->profit_margin }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4 small">No orders recorded in this range</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Financial Trend Area Chart
    const trendCtx = document.getElementById('financeTrendChart');
    if (trendCtx) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($trendLabels) !!},
                datasets: [
                    {
                        label: 'Gross Revenue (₹)',
                        data: {!! json_encode($trendRevenue) !!},
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.12)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Product Cost (COGS) (₹)',
                        data: {!! json_encode($trendCost) !!},
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.04)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.35
                    },
                    {
                        label: 'Net Gross Profit (₹)',
                        data: {!! json_encode($trendProfit) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.18)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { font: { size: 11, weight: 'bold' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '₹' + value.toLocaleString('en-IN'); },
                            font: { size: 10 }
                        }
                    },
                    x: {
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // 2. Payment Methods Donut Chart
    const payCtx = document.getElementById('paymentMethodChart');
    if (payCtx) {
        const pmLabels = {!! json_encode(array_column($paymentMethods, 'name')) !!};
        const pmProfits = {!! json_encode(array_column($paymentMethods, 'profit')) !!};

        new Chart(payCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pmLabels.length > 0 ? pmLabels : ['No Data'],
                datasets: [{
                    data: pmProfits.length > 0 ? pmProfits : [1],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#06b6d4', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 } } }
                },
                cutout: '70%'
            }
        });
    }
});
</script>
@endpush

@endsection
