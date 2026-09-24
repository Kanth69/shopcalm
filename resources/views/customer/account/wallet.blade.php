@extends('customer.account.layout')

@section('account_title', 'My Wallet & Referral Rewards')

@section('account_content')
<div class="d-flex flex-column gap-4">

    @if($wallet->status === 'frozen')
    {{-- Frozen Wallet Alert Banner --}}
    <div class="alert alert-danger border-0 rounded-4 shadow-sm p-3.5 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-3" 
         style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border: 1.5px solid #f87171 !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger text-white flex-shrink-0 shadow-xs" style="width: 44px; height: 44px;">
                <i class="bi bi-shield-lock-fill fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold text-danger mb-0.5">Wallet Account Temporarily Frozen</h6>
                <p class="text-secondary small mb-0" style="font-size: 0.82rem;">
                    Your wallet balance has been temporarily locked by administration for security verification. You cannot redeem credits at checkout until resolved.
                </p>
            </div>
        </div>
        <a href="{{ route('page.contact') }}" class="btn btn-danger btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-xs">
            <i class="bi bi-headset me-1"></i> Contact Support
        </a>
    </div>
    @endif

    {{-- Top Hero Row: Golden Wallet Card & Referral Promo --}}
    <div class="row g-4">
        {{-- Golden Wallet Balance Card --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 text-white position-relative overflow-hidden h-100 p-4"
                 style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1.5px solid #334155 !important;">
                
                {{-- Decorative Ambient Glow --}}
                <div class="position-absolute" style="top: -40px; right: -40px; width: 140px; height: 140px; background: radial-gradient(circle, rgba(245, 158, 11, 0.3) 0%, rgba(245, 158, 11, 0) 70%); border-radius: 50%; pointer-events: none;"></div>

                <div class="d-flex justify-content-between align-items-start mb-4 position-relative">
                    <div>
                        <span class="badge rounded-pill px-3 py-1 fw-bold text-uppercase" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-wallet2 me-1"></i> {{ \App\Models\Setting::get('store_name', 'ShopCalm') }} Wallet
                        </span>
                        <div class="text-secondary small mt-2" style="font-size: 0.78rem;">Available for 1-Click Checkout</div>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15);">
                        <i class="bi bi-coin text-warning fs-4"></i>
                    </div>
                </div>

                <div class="mb-4 position-relative">
                    <div class="text-white-50 small mb-1">Total Available Balance</div>
                    <div class="display-6 fw-extrabold text-white font-monospace" style="letter-spacing: -0.5px;">
                        ₹{{ number_format($wallet->balance, 2) }}
                    </div>
                </div>

                <div class="mt-auto pt-3 border-top position-relative" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white-50 small" style="font-size: 0.78rem;">Lifetime Cashback Earned:</span>
                        <span class="fw-bold font-monospace text-warning small">₹{{ number_format($wallet->total_earned, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Share & Earn Viral Referral Card (Voucher & Reward Flagship Layout) --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-xs rounded-4 h-100 p-3.5 p-md-4 position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #ffffff 100%); border: 1.5px solid #a7f3d0 !important;">
                
                {{-- Decorative background glow --}}
                <div class="position-absolute" style="top: -30px; right: -30px; width: 120px; height: 120px; background: radial-gradient(circle, rgba(16, 185, 129, 0.15) 0%, transparent 70%); pointer-events: none;"></div>

                {{-- Header Strip --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div class="d-flex align-items-center gap-2.5 min-w-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0 shadow-xs" 
                             style="width: 44px; height: 44px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bi bi-gift-fill fs-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                                <h6 class="fw-bolder text-dark mb-0" style="font-size: 1rem; letter-spacing: -0.01em;">Refer Friends & Earn ₹175!</h6>
                                <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="background: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.68rem;">
                                    <i class="bi bi-stars"></i> UNLIMITED
                                </span>
                            </div>
                            <p class="text-secondary small mb-0" style="font-size: 0.78rem; line-height: 1.4;">
                                They get <strong class="text-success">₹50 welcome bonus</strong>, you earn <strong class="text-primary">₹175 across 3 orders</strong>!
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Interactive Referral Code Voucher Ticket --}}
                <div class="p-3 rounded-3 bg-white border mb-3 shadow-xs position-relative" style="border: 1.5px dashed #10b981 !important;">
                    <div class="row g-2.5 align-items-center">
                        <div class="col-sm-6">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1" style="font-size: 0.68rem; letter-spacing: 0.5px;">Your Unique Referral Code</label>
                            <div class="d-flex align-items-center gap-2">
                                <span class="h4 fw-extrabold text-dark mb-0 font-monospace px-2.5 py-1 rounded-2" 
                                      style="background: #f1f5f9; color: #0f172a; letter-spacing: 1.5px; font-size: 1.25rem;" id="ref-code-display">{{ $wallet->referral_code }}</span>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-bold" 
                                        onclick="navigator.clipboard.writeText('{{ $wallet->referral_code }}'); this.innerHTML = '<i class=\'bi bi-check-lg\'></i> Copied!'; setTimeout(() => this.innerHTML = '<i class=\'bi bi-copy\'></i> Copy', 1800);" style="font-size: 0.74rem;">
                                    <i class="bi bi-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <a href="https://api.whatsapp.com/send?text={{ $whatsappShareText }}" target="_blank" 
                               class="btn btn-success btn-sm w-100 w-sm-auto rounded-pill px-3.5 py-2 fw-bold shadow-xs d-inline-flex align-items-center justify-content-center gap-2" 
                               style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); border: none; font-size: 0.82rem;">
                                <i class="bi bi-whatsapp fs-6"></i> <span>Share on WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Direct Shareable Link Field --}}
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: #cbd5e1; border-radius: 999px 0 0 999px; font-size: 0.85rem;">
                        <i class="bi bi-link-45deg"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm bg-white border-start-0 border-end-0 font-monospace text-dark fw-semibold" 
                           value="{{ $referralUrl }}" readonly id="ref-link-input" style="border-color: #cbd5e1; font-size: 0.78rem;">
                    <button class="btn btn-primary btn-sm px-3.5 fw-bold shadow-xs flex-shrink-0" type="button" 
                            style="background: #4f46e5; border: none; border-radius: 0 999px 999px 0; font-size: 0.78rem;"
                            onclick="navigator.clipboard.writeText('{{ $referralUrl }}'); this.innerHTML = '<i class=\'bi bi-check-lg me-1\'></i>Copied!'; setTimeout(() => this.innerText = 'Copy Link', 1800);">
                        Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 3-Tier Repeat Rewards Milestone Explainer Banner --}}
    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-stars text-warning me-2"></i>How the 3-Tier Referral Reward Program Works:</h6>
        <div class="row g-3 text-center text-md-start">
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border h-100 position-relative">
                    <div class="badge bg-primary text-white rounded-pill px-2.5 py-1 mb-2 fw-bold" style="font-size: 0.68rem;">Step 1: Welcome</div>
                    <div class="fw-bold text-dark small">Friend Registers</div>
                    <p class="text-success fw-bold small mb-0 mt-1">Friend gets ₹50 Bonus</p>
                    <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">Instant credit upon signup</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border h-100 position-relative">
                    <div class="badge bg-success text-white rounded-pill px-2.5 py-1 mb-2 fw-bold" style="font-size: 0.68rem;">Step 2: 1st Order</div>
                    <div class="fw-bold text-dark small">1st Order Delivered</div>
                    <p class="text-primary fw-bold small mb-0 mt-1">You get ₹100.00</p>
                    <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">Auto-credited on delivery</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border h-100 position-relative">
                    <div class="badge bg-warning text-dark rounded-pill px-2.5 py-1 mb-2 fw-bold" style="font-size: 0.68rem;">Step 3: 2nd Order</div>
                    <div class="fw-bold text-dark small">2nd Order Delivered</div>
                    <p class="text-primary fw-bold small mb-0 mt-1">You get ₹50.00</p>
                    <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">Repeat purchase bonus</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 rounded-3 bg-light border h-100 position-relative">
                    <div class="badge bg-info text-dark rounded-pill px-2.5 py-1 mb-2 fw-bold" style="font-size: 0.68rem;">Step 4: 3rd Order</div>
                    <div class="fw-bold text-dark small">3rd Order Delivered</div>
                    <p class="text-primary fw-bold small mb-0 mt-1">You get ₹25.00</p>
                    <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">Final loyalty milestone</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Friends Joined Tracker --}}
    @if($friendsStats->isNotEmpty())
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Referred Friends ({{ $totalReferralsCount }})</h6>
            <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-3 py-1 fw-bold font-monospace">
                Total Earned: ₹{{ number_format($totalReferralEarnings, 2) }}
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Friend Name</th>
                        <th>Joined Date</th>
                        <th class="text-center">1st Order (₹100)</th>
                        <th class="text-center">2nd Order (₹50)</th>
                        <th class="text-center">3rd Order (₹25)</th>
                        <th class="text-end pe-4">Total Earned</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($friendsStats as $friend)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($friend->friend_name, 0, 1)) }}
                                </div>
                                <span class="fw-bold text-dark">{{ $friend->friend_name }}</span>
                            </div>
                        </td>
                        <td class="text-secondary small">{{ $friend->joined_date ? $friend->joined_date->format('d M, Y') : 'N/A' }}</td>
                        <td class="text-center">
                            @if($friend->first_order_done)
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Earned ₹100</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($friend->second_order_done)
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Earned ₹50</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($friend->third_order_done)
                                <span class="badge bg-success text-white rounded-pill px-2.5 py-1"><i class="bi bi-check-circle-fill me-1"></i> Earned ₹25</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">Pending</span>
                            @endif
                        </td>
                        <td class="text-end pe-4 font-monospace fw-bold {{ $friend->total_earned > 0 ? 'text-success' : 'text-muted' }}">
                            ₹{{ number_format($friend->total_earned, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Passbook / Transaction History --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>Wallet Passbook History</h6>
            <span class="text-muted small">Updated in real-time</span>
        </div>
        <div class="card-body p-0">
            @if($transactions->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 bg-light text-muted" style="width: 56px; height: 56px;">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No Transactions Yet</h6>
                    <p class="text-muted small mb-0">Share your referral link with friends or place an order to get started!</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach($transactions as $txn)
                    @php $badge = $txn->source_badge; @endphp
                    <div class="list-group-item p-3.5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center {{ $txn->type === 'CREDIT' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}" style="width: 44px; height: 44px; font-size: 1.2rem;">
                                <i class="bi {{ $txn->type === 'CREDIT' ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-0.5">
                                    <span class="badge rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem; background: {{ $txn->type === 'CREDIT' ? '#ecfdf5; color: #065f46;' : '#fef2f2; color: #991b1b;' }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.72rem;">{{ $txn->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                                <div class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ $txn->description }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h6 mb-0 font-monospace fw-bolder {{ $txn->type === 'CREDIT' ? 'text-success' : 'text-danger' }}">
                                {{ $txn->type === 'CREDIT' ? '+' : '-' }}₹{{ number_format($txn->amount, 2) }}
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Balance: ₹{{ number_format($txn->balance_after, 2) }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($transactions->hasPages())
                <div class="p-3 border-top bg-light">
                    {{ $transactions->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection
