@extends('admin.layouts.app')

@section('title', 'Customer Wallets & Referral Management')

@section('header', 'Wallets & Referral Program')

@push('styles')
<style>
    /* Admin Wallet Header Button */
    .btn-rules-header {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%) !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35) !important;
        transition: all 0.25s ease;
    }
    .btn-rules-header:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.45) !important;
        background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%) !important;
        color: #ffffff !important;
    }

    /* Action Buttons in Wallets Table */
    .btn-wallet-action {
        font-size: 0.76rem !important;
        font-weight: 600 !important;
        padding: 0.35rem 0.75rem !important;
        border-radius: 50rem !important;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s cubic-bezier(.4,0,.2,1);
        border: 1.5px solid transparent;
        white-space: nowrap;
    }
    .btn-wallet-action:hover {
        transform: translateY(-1.5px);
    }
    .btn-wallet-action:active {
        transform: translateY(0);
    }

    /* History Button */
    .btn-wallet-history {
        background: #f1f5f9 !important;
        color: #475569 !important;
        border-color: #cbd5e1 !important;
    }
    .btn-wallet-history:hover {
        background: #e2e8f0 !important;
        color: #0f172a !important;
        border-color: #94a3b8 !important;
    }

    /* Adjust Balance Button */
    .btn-wallet-adjust {
        background: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }
    .btn-wallet-adjust:hover {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    /* Freeze Button */
    .btn-wallet-freeze {
        background: #fff1f2 !important;
        color: #e11d48 !important;
        border-color: #fecdd3 !important;
    }
    .btn-wallet-freeze:hover {
        background: #e11d48 !important;
        color: #ffffff !important;
        border-color: #e11d48 !important;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
    }

    /* Unfreeze Button */
    .btn-wallet-unfreeze {
        background: #ecfdf5 !important;
        color: #059669 !important;
        border-color: #a7f3d0 !important;
    }
    .btn-wallet-unfreeze:hover {
        background: #059669 !important;
        color: #ffffff !important;
        border-color: #059669 !important;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
    }

    /* Metric Cards Accent line */
    .kpi-accent {
        height: 4px;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }
</style>
@endpush

@section('actions')
    @if(auth()->user()->isSuperAdmin())
    <button type="button" class="btn btn-rules-header rounded-pill px-4 py-2.5 fw-bold d-flex align-items-center gap-2" 
            data-bs-toggle="modal" data-bs-target="#editRulesModal">
        <i class="bi bi-gear-wide-connected fs-5"></i>
        <span>Configure Referral & Reward Rules</span>
    </button>
    @endif
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- KPI Metric Cards --}}
    <div class="row g-3 mb-4">
        {{-- Active Wallet Liability --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-wallet-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Active Wallet Liability</div>
                        <div class="fs-4 fw-extrabold text-dark font-monospace">₹{{ number_format($totalLiability, 2) }}</div>
                        <span class="text-muted small" style="font-size: 0.72rem;">Held across {{ $totalWalletsCount }} customer accounts</span>
                    </div>
                </div>
                <div class="kpi-accent" style="background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
            </div>
        </div>

        {{-- Total Distributed --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2.5 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-gift-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Rewards Distributed</div>
                        <div class="fs-4 fw-extrabold text-success font-monospace">₹{{ number_format($totalDistributed, 2) }}</div>
                        <span class="text-muted small" style="font-size: 0.72rem;">Signups & 3-Tier Repeat Rewards</span>
                    </div>
                </div>
                <div class="kpi-accent" style="background: linear-gradient(90deg, #10b981, #34d399);"></div>
            </div>
        </div>

        {{-- Total Redeemed in Orders --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2.5 bg-warning bg-opacity-15 text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-cart-check-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Redeemed in Purchases</div>
                        <div class="fs-4 fw-extrabold text-dark font-monospace">₹{{ number_format($totalRedeemed, 2) }}</div>
                        <span class="text-muted small" style="font-size: 0.72rem;">Deducted at customer checkout</span>
                    </div>
                </div>
                <div class="kpi-accent" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
            </div>
        </div>

        {{-- Active Referral Rules Policy Card --}}
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 position-relative overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="d-flex align-items-center gap-1.5">
                        <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.5px;">Referral Policy</div>
                        @if($rules['referral_enabled'])
                            <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">
                                ● Active
                            </span>
                        @else
                            <span class="badge bg-secondary bg-opacity-20 text-secondary rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem;">
                                ○ Disabled
                            </span>
                        @endif
                    </div>
                    @if(auth()->user()->isSuperAdmin())
                    <button type="button" class="btn btn-link p-0 text-primary fw-bold text-decoration-none" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#editRulesModal">
                        <i class="bi bi-sliders me-0.5"></i> Edit
                    </button>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-1.5 small mb-1">
                    <span class="text-secondary" style="font-size: 0.75rem;">Welcome Bonus:</span>
                    <strong class="text-dark font-monospace">₹{{ number_format($rules['signup_bonus'], 0) }}</strong>
                    @if(!$rules['signup_bonus_enabled'])
                        <span class="text-muted" style="font-size: 0.65rem;">(Disabled)</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-1.5 small mb-1">
                    <span class="text-secondary" style="font-size: 0.75rem;">3-Tier Repeat:</span>
                    <strong class="text-success font-monospace">₹{{ number_format($rules['first_order_reward'], 0) }} + ₹{{ number_format($rules['second_order_reward'], 0) }} + ₹{{ number_format($rules['third_order_reward'], 0) }}</strong>
                </div>
                <div class="text-muted" style="font-size: 0.7rem;">
                    Max Payout: <strong class="text-dark">₹{{ number_format($rules['first_order_reward'] + $rules['second_order_reward'] + $rules['third_order_reward'], 0) }}</strong>/friend
                </div>
                <div class="kpi-accent" style="background: linear-gradient(90deg, {{ $rules['referral_enabled'] ? '#8b5cf6, #a78bfa' : '#9ca3af, #cbd5e1' }});"></div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="{{ route('admin.wallets.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search by customer name, email, phone, or referral code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm bg-light">
                    <option value="">All Wallet Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Wallets</option>
                    <option value="frozen" {{ request('status') === 'frozen' ? 'selected' : '' }}>Frozen / Locked Wallets</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-semibold flex-grow-1 shadow-xs">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.wallets.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Customer Wallets Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="ps-4" style="width: 25%;">Customer Details</th>
                        <th style="width: 14%;">Referral Code</th>
                        <th style="width: 16%;">Referred By</th>
                        <th style="width: 11%;">Status</th>
                        <th class="text-end" style="width: 13%;">Available Balance</th>
                        <th class="text-end" style="width: 13%;">Lifetime Earned</th>
                        <th class="text-center pe-4" style="width: 18%;">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $w)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-xs flex-shrink-0" 
                                     style="width: 40px; height: 40px; background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); font-size: 0.92rem;">
                                    {{ strtoupper(substr($w->user->name ?? 'C', 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark text-truncate">{{ $w->user->name ?? 'Unknown User' }}</div>
                                    <div class="text-secondary small font-monospace" style="font-size: 0.74rem;">{{ $w->user->email ?? $w->user->mobile_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border font-monospace px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                {{ $w->referral_code }}
                            </span>
                        </td>
                        <td>
                            @if($w->referrer)
                                <div class="fw-semibold text-dark small">{{ $w->referrer->name }}</div>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-0.5" style="font-size: 0.68rem;">
                                    Code: {{ $w->referrer->wallet?->referral_code ?? 'REF' }}
                                </span>
                            @else
                                <span class="text-muted small"><i class="bi bi-person-check me-1"></i>Direct Signup</span>
                            @endif
                        </td>
                        <td>
                            @if($w->status === 'active')
                                <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Active
                                </span>
                            @else
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                                    <i class="bi bi-lock-fill me-1"></i>Frozen
                                </span>
                            @endif
                        </td>
                        <td class="text-end font-monospace fw-bold fs-6 {{ $w->balance > 0 ? 'text-success' : 'text-muted' }}">
                            ₹{{ number_format($w->balance, 2) }}
                        </td>
                        <td class="text-end font-monospace fw-semibold text-dark small">
                            ₹{{ number_format($w->total_earned, 2) }}
                        </td>
                        <td class="text-center pe-4">
                            <div class="d-inline-flex align-items-center gap-1.5 flex-wrap justify-content-center">
                                {{-- History / Passbook Modal Trigger --}}
                                <button type="button" class="btn btn-wallet-action btn-wallet-history shadow-xs" 
                                        data-bs-toggle="modal" data-bs-target="#historyModal{{ $w->id }}" title="View Transaction Passbook">
                                    <i class="bi bi-clock-history text-secondary"></i>
                                    <span>Passbook</span>
                                    <span class="badge bg-secondary bg-opacity-20 text-dark rounded-pill ms-0.5 px-1.5" style="font-size: 0.65rem;">{{ $w->transactions->count() }}</span>
                                </button>

                                {{-- Adjust Modal Trigger --}}
                                <button type="button" class="btn btn-wallet-action btn-wallet-adjust shadow-xs" 
                                        data-bs-toggle="modal" data-bs-target="#adjustModal{{ $w->id }}" title="Manual Balance Adjustment">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Adjust</span>
                                </button>

                                {{-- Toggle Freeze Status --}}
                                <form action="{{ route('admin.wallets.toggle-status', $w) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to {{ $w->status === 'active' ? 'FREEZE (Lock)' : 'UNFREEZE (Restore)' }} this customer wallet?');">
                                    @csrf
                                    <button type="submit" class="btn btn-wallet-action {{ $w->status === 'active' ? 'btn-wallet-freeze' : 'btn-wallet-unfreeze' }} shadow-xs" 
                                            title="{{ $w->status === 'active' ? 'Freeze / Lock Wallet' : 'Unfreeze / Restore Wallet' }}">
                                        <i class="bi {{ $w->status === 'active' ? 'bi-lock-fill' : 'bi-unlock-fill' }}"></i>
                                        <span>{{ $w->status === 'active' ? 'Freeze' : 'Unfreeze' }}</span>
                                    </button>
                                </form>
                            </div>

                            {{-- Transaction Passbook Modal --}}
                            <div class="modal fade" id="historyModal{{ $w->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content rounded-4 border-0 shadow-lg text-start">
                                        <div class="modal-header border-bottom py-3 px-4 bg-light">
                                            <div>
                                                <h6 class="modal-title fw-bold text-dark mb-0">
                                                    <i class="bi bi-clock-history text-primary me-2"></i>Wallet Passbook: {{ $w->user->name }}
                                                </h6>
                                                <small class="text-muted font-monospace" style="font-size: 0.75rem;">Referral Code: {{ $w->referral_code }} | Balance: ₹{{ number_format($w->balance, 2) }}</small>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div class="table-responsive" style="max-height: 400px;">
                                                <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
                                                    <thead class="bg-white border-bottom sticky-top">
                                                        <tr class="text-secondary small">
                                                            <th class="ps-4 py-2.5">Date</th>
                                                            <th class="py-2.5">Type & Source</th>
                                                            <th class="py-2.5">Description / Order Details</th>
                                                            <th class="text-end py-2.5">Amount</th>
                                                            <th class="text-end pe-4 py-2.5">Balance After</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($w->transactions->sortByDesc('created_at') as $t)
                                                        <tr>
                                                            <td class="ps-4 text-nowrap text-secondary">
                                                                <div>{{ $t->created_at->format('d M Y') }}</div>
                                                                <small class="text-muted" style="font-size: 0.7rem;">{{ $t->created_at->format('h:i A') }}</small>
                                                            </td>
                                                            <td>
                                                                @php $badge = $t->source_badge; @endphp
                                                                <span class="badge {{ $badge['class'] }} rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.7rem;">
                                                                    {{ $badge['label'] }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="text-dark">{{ $t->description }}</div>
                                                                @if($t->order)
                                                                    <a href="{{ route('admin.orders.show', $t->order_id) }}" class="small fw-semibold text-primary text-decoration-none" target="_blank">
                                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Order #{{ $t->order->order_number }}
                                                                    </a>
                                                                @endif
                                                            </td>
                                                            <td class="text-end font-monospace fw-bold {{ $t->type === 'CREDIT' ? 'text-success' : 'text-danger' }}">
                                                                {{ $t->type === 'CREDIT' ? '+' : '-' }}₹{{ number_format($t->amount, 2) }}
                                                            </td>
                                                            <td class="text-end pe-4 font-monospace text-dark">
                                                                ₹{{ number_format($t->balance_after, 2) }}
                                                            </td>
                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center py-4 text-muted">No transactions recorded for this wallet yet.</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-2.5 px-4 bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Adjust Modal for this wallet --}}
                            <div class="modal fade" id="adjustModal{{ $w->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow-lg text-start">
                                        <form action="{{ route('admin.wallets.adjust', $w) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-bottom py-3 px-4 bg-light">
                                                <h6 class="modal-title fw-bold text-dark mb-0">
                                                    <i class="bi bi-pencil-square text-primary me-2"></i>Manual Wallet Adjustment
                                                </h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="p-3 bg-light rounded-3 mb-3 border">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-secondary small">Customer:</span>
                                                        <strong class="text-dark">{{ $w->user->name }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="text-secondary small">Current Available Balance:</span>
                                                        <span class="font-monospace fw-bold text-primary fs-6">₹{{ number_format($w->balance, 2) }}</span>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase">Action Type</label>
                                                    <select name="type" class="form-select" required>
                                                        <option value="CREDIT">Credit (+) Add Money to Wallet</option>
                                                        <option value="DEBIT">Debit (-) Deduct Money from Wallet</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase">Amount (₹)</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                                        <input type="number" step="0.01" min="1" name="amount" class="form-control font-monospace" placeholder="e.g. 100.00" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-uppercase">Mandatory Reason / Note</label>
                                                    <input type="text" name="reason" class="form-control" placeholder="e.g. Promotional goodwill bonus, dispute resolution" required maxlength="255">
                                                    <small class="text-muted" style="font-size: 0.72rem;">This explanation will be logged in the customer passbook and audit trail.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top py-2.5 px-4 bg-light">
                                                <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">Confirm Adjustment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-wallet2 fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            No customer wallets found matching your filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wallets->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            {{ $wallets->links() }}
        </div>
        @endif
    </div>

</div>

{{-- Super Admin Modal: Configure Dynamic Multi-Tier Referral Rules --}}
@if(auth()->user()->isSuperAdmin())
<div class="modal fade" id="editRulesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <form action="{{ route('admin.wallets.update-rules') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3.5 px-4 bg-light">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="rounded-circle p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-sliders fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Referral & Wallet Program Rules</h5>
                            <small class="text-muted" style="font-size: 0.75rem;">Adjust reward amounts and milestone policies across ShopCalm</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    {{-- Master Program Switches --}}
                    <div class="p-3 bg-light rounded-4 border mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5">Enable Referral Program</h6>
                                <small class="text-secondary d-block" style="font-size: 0.78rem;">Allow customers to share their referral link and earn repeat delivery milestones.</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="wallet_referral_enabled" value="1" id="rule_ref_enabled" {{ $rules['referral_enabled'] ? 'checked' : '' }} style="width: 2.8em; height: 1.5em; cursor: pointer;">
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0.5">Enable Signup Welcome Bonus</h6>
                                <small class="text-secondary d-block" style="font-size: 0.78rem;">Instantly credit new customers with a welcome bonus when they register via a referral link.</small>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" name="wallet_signup_bonus_enabled" value="1" id="rule_bonus_enabled" {{ $rules['signup_bonus_enabled'] ? 'checked' : '' }} style="width: 2.8em; height: 1.5em; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    {{-- Reward Amounts Configuration --}}
                    <div class="row g-3 mb-3">
                        {{-- Signup Bonus --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">
                                <i class="bi bi-gift-fill text-primary me-1"></i>New Friend Signup Bonus
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold font-monospace">₹</span>
                                <input type="number" step="1" min="0" name="wallet_signup_bonus" class="form-control font-monospace fw-bold" value="{{ $rules['signup_bonus'] }}" required id="input_signup_bonus">
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Credited immediately upon friend's successful registration.</small>
                        </div>

                        {{-- Min Order Spend Threshold --}}
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-uppercase">
                                <i class="bi bi-cart-check text-secondary me-1"></i>Min Order Spend to Qualify
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold font-monospace">₹</span>
                                <input type="number" step="1" min="0" name="wallet_min_order_reward_spend" class="form-control font-monospace fw-bold" value="{{ $rules['min_order_spend'] }}" placeholder="0 for no minimum">
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Set to 0 to reward orders of any value.</small>
                        </div>
                    </div>

                    {{-- 3-Tier Repeat Rewards --}}
                    <div class="card border rounded-4 p-3.5 bg-light mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="bi bi-layers-fill text-success me-1.5"></i>3-Tier Repeat Order Rewards for Referrer
                            </h6>
                            <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.72rem;">RETENTION LOOP</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">🥇 1st Order Delivered</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white fw-bold font-monospace">₹</span>
                                    <input type="number" step="1" min="0" name="wallet_first_order_reward" class="form-control font-monospace fw-bold tier-input" value="{{ $rules['first_order_reward'] }}" required id="input_tier1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">🥈 2nd Order Delivered</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white fw-bold font-monospace">₹</span>
                                    <input type="number" step="1" min="0" name="wallet_second_order_reward" class="form-control font-monospace fw-bold tier-input" value="{{ $rules['second_order_reward'] }}" required id="input_tier2">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">🥉 3rd Order Delivered</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white fw-bold font-monospace">₹</span>
                                    <input type="number" step="1" min="0" name="wallet_third_order_reward" class="form-control font-monospace fw-bold tier-input" value="{{ $rules['third_order_reward'] }}" required id="input_tier3">
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-2.5 border-top d-flex justify-content-between align-items-center text-secondary small" style="font-size: 0.78rem;">
                            <span>Total Payout Potential per Referred Friend:</span>
                            <strong class="text-success font-monospace fs-6" id="total_payout_preview">
                                ₹{{ number_format($rules['first_order_reward'] + $rules['second_order_reward'] + $rules['third_order_reward'], 2) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4 bg-light">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="bi bi-check2-circle me-1"></i> Save Program Rules
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function recalculateTotalPayout() {
        const t1 = parseFloat(document.getElementById('input_tier1')?.value || 0);
        const t2 = parseFloat(document.getElementById('input_tier2')?.value || 0);
        const t3 = parseFloat(document.getElementById('input_tier3')?.value || 0);
        const total = t1 + t2 + t3;
        const preview = document.getElementById('total_payout_preview');
        if (preview) {
            preview.textContent = '₹' + total.toFixed(2);
        }
    }

    document.querySelectorAll('.tier-input').forEach(input => {
        input.addEventListener('input', recalculateTotalPayout);
    });
});
</script>
@endpush
@endif

@endsection
