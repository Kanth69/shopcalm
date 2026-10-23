@extends('admin.layouts.app')

@section('header', 'Postal Pincodes & Address Auto-Fill Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">Postal Pincodes & Auto-Fill</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #4338ca 0%, #3b82f6 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-mailbox2 me-2"></i>Postal Pincodes & Address Auto-Fill Control Center</h4>
                    <p class="mb-0 text-white-50 small">Manage India Post national directory integration, automatic city & state address population for customers, and verify store delivery boundaries.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pincodes.index') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-dark shadow-sm">
                        <i class="bi bi-geo-fill me-1"></i> Manage Serviceable Pincodes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metrics Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-building-check text-primary me-1"></i>Postal Directory API</div>
            <div class="fs-5 fw-black text-success">Active & Online ✓</div>
            <div class="text-muted" style="font-size: 0.72rem;">India Post (api.postalpincode.in)</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-pin-map-fill text-indigo me-1"></i>Total Store Pincodes</div>
            <div class="fs-4 fw-black text-dark">{{ $stats['total_pincodes'] ?? 0 }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Configured in Database</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>Serviceable Pincodes</div>
            <div class="fs-4 fw-black text-success">{{ $stats['serviceable_pincodes'] ?? 0 }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Active Delivery Zones</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-cash-coin text-warning me-1"></i>COD Enabled</div>
            <div class="fs-4 fw-black text-primary">{{ $stats['cod_available'] ?? 0 }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">Cash on Delivery Allowed</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Live Pincode Tester & APIs Architecture -->
    <div class="col-lg-5">
        <!-- Live Pincode Verification Widget -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-search text-primary me-2"></i>Live Pincode Verification Tool</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-0.5" style="font-size: 0.7rem;">
                    Dual Check (Postal + Store)
                </span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    Enter any 6-digit Indian PIN code to test the live India Post national lookup and your store's delivery rules.
                </p>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Indian PIN Code</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt"></i></span>
                        <input type="text" id="testPincodeInput" class="form-control font-monospace" placeholder="e.g. 560001, 500001" maxlength="6" value="560001">
                        <button type="button" id="btnTestPincode" class="btn btn-primary fw-bold px-3" onclick="testPincodeLookup()">
                            <i class="bi bi-arrow-repeat me-1"></i> Verify
                        </button>
                    </div>
                </div>

                <!-- Quick sample pincode chips -->
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <span class="text-muted" style="font-size: 0.72rem;">Quick test:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 font-monospace" style="font-size: 0.7rem;" onclick="setPinAndTest('560001')">560001 (Bengaluru)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 font-monospace" style="font-size: 0.7rem;" onclick="setPinAndTest('500001')">500001 (Hyderabad)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 font-monospace" style="font-size: 0.7rem;" onclick="setPinAndTest('110001')">110001 (Delhi)</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2 font-monospace" style="font-size: 0.7rem;" onclick="setPinAndTest('400001')">400001 (Mumbai)</button>
                </div>

                <!-- Result Box -->
                <div id="pincodeResultBox" class="p-3 bg-light rounded-3 border d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="fw-bold text-dark small" id="resPinLabel">PIN: 560001</span>
                        <span class="badge rounded-pill px-2.5 py-1" id="resServiceBadge">Serviceable</span>
                    </div>

                    <!-- Postal API Data -->
                    <div class="mb-2">
                        <div class="text-muted fw-bold" style="font-size: 0.7rem;">INDIA POST DIRECTORY</div>
                        <div class="text-dark fw-medium small" id="resPostalLoc">Bengaluru, Karnataka</div>
                        <div class="text-muted" style="font-size: 0.72rem;" id="resPostOffices">Bangalore G.P.O.</div>
                    </div>

                    <!-- Store Logistics Data -->
                    <div>
                        <div class="text-muted fw-bold" style="font-size: 0.7rem;">STORE LOGISTICS & DISPATCH</div>
                        <div class="d-flex align-items-center justify-content-between small text-dark mt-1">
                            <span>Delivery Zone:</span>
                            <span class="fw-bold font-monospace" id="resZone">local_fleet</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small text-dark mt-1">
                            <span>Delivery Charge:</span>
                            <span class="fw-bold text-success" id="resCharge">₹0.00</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small text-dark mt-1">
                            <span>Cash on Delivery:</span>
                            <span class="fw-bold" id="resCod">Available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Architecture & Services Info Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-diagram-3-fill text-indigo me-2"></i>Pincode Pipeline Architecture</h6>
            </div>
            <div class="card-body p-4 small text-muted">
                <ul class="list-unstyled mb-0 space-y-3">
                    <li class="d-flex gap-2 mb-3">
                        <div class="rounded-circle p-1.5 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                            <span class="fw-bold" style="font-size: 0.75rem;">1</span>
                        </div>
                        <div>
                            <strong class="text-dark">India Post API (api.postalpincode.in)</strong>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">When a customer enters their 6-digit pincode, city, district, and state are fetched automatically without manual typing.</p>
                        </div>
                    </li>
                    <li class="d-flex gap-2 mb-3">
                        <div class="rounded-circle p-1.5 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                            <span class="fw-bold" style="font-size: 0.75rem;">2</span>
                        </div>
                        <div>
                            <strong class="text-dark">Zippopotam Global Backup (api.zippopotam.us)</strong>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">If the national postal server experiences delays, ShopCalm falls back to Zippopotam instantly.</p>
                        </div>
                    </li>
                    <li class="d-flex gap-2">
                        <div class="rounded-circle p-1.5 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px;">
                            <span class="fw-bold" style="font-size: 0.75rem;">3</span>
                        </div>
                        <div>
                            <strong class="text-dark">Local Store Serviceability Database</strong>
                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">Your local <code>pincodes</code> table governs whether orders are accepted, customized shipping charges, COD enablement, and fleet assignment.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Pincodes Management & Recent Pincodes -->
    <div class="col-lg-7">
        <!-- Action Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-database-fill-gear text-primary me-2"></i>Manage Store Delivery Boundaries</h6>
                    <p class="text-muted small mb-0">Add new serviceable delivery zones, toggle COD availability, or bulk-import pincodes via CSV.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.pincodes.create') }}" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-bold small">
                        <i class="bi bi-plus-circle me-1"></i> Add Pincode
                    </a>
                    <a href="{{ route('admin.pincodes.index') }}" class="btn btn-primary rounded-pill px-3 py-1.5 fw-bold small">
                        <i class="bi bi-table me-1"></i> All Pincodes
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Pincodes Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>Active Pincodes in Store</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Recently configured delivery boundaries and logistics rules</span>
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    {{ count($recentPincodes) }} Recent Records
                </span>
            </div>
            <div class="card-body p-0">
                @if(count($recentPincodes) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Pincode</th>
                                <th>City / Area</th>
                                <th>State</th>
                                <th>Shipping Fee</th>
                                <th>COD</th>
                                <th class="text-end pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPincodes as $pin)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-dark">
                                    {{ $pin->pincode }}
                                </td>
                                <td>
                                    <div class="text-truncate text-dark fw-medium" style="max-width: 180px;" title="{{ $pin->city }}">
                                        {{ $pin->city }}
                                    </div>
                                </td>
                                <td class="text-muted">{{ $pin->state }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $pin->delivery_charge !== null ? '₹' . number_format($pin->delivery_charge, 2) : 'Default' }}
                                    </span>
                                </td>
                                <td>
                                    @if($pin->is_cod_available)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">COD Yes</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-muted rounded-pill">Prepaid Only</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($pin->is_serviceable)
                                        <span class="badge bg-success rounded-pill px-2 py-1">Serviceable ✓</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-2 py-1">Disabled</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-mailbox text-secondary fs-3 d-block mb-2"></i>
                    No pincodes configured yet.<br>
                    <a href="{{ route('admin.pincodes.create') }}" class="btn btn-sm btn-primary rounded-pill mt-2">Add Your First Pincode</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function setPinAndTest(pin) {
    document.getElementById('testPincodeInput').value = pin;
    testPincodeLookup();
}

function testPincodeLookup() {
    const pin = document.getElementById('testPincodeInput').value.trim();
    const btn = document.getElementById('btnTestPincode');
    const resultBox = document.getElementById('pincodeResultBox');

    if (!pin || pin.length !== 6) {
        alert('Please enter a valid 6-digit Indian PIN code.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking...';

    fetch("{{ route('admin.integrations.pincodes.test-lookup') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ pincode: pin })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Verify';

        if (!data.success) {
            alert(data.message || 'Pincode lookup failed');
            return;
        }

        resultBox.classList.remove('d-none');
        document.getElementById('resPinLabel').textContent = 'PIN: ' + data.pincode;

        // Serviceable badge
        const badge = document.getElementById('resServiceBadge');
        if (data.serviceability && data.serviceability.is_serviceable) {
            badge.className = 'badge bg-success rounded-pill px-2.5 py-1';
            badge.textContent = 'Serviceable ✓';
        } else {
            badge.className = 'badge bg-danger rounded-pill px-2.5 py-1';
            badge.textContent = 'Delivery Unavailable';
        }

        // Postal directory
        if (data.postal_info) {
            document.getElementById('resPostalLoc').textContent = (data.postal_info.district || '') + ', ' + (data.postal_info.state || '');
            document.getElementById('resPostOffices').textContent = 'Post Office: ' + (data.postal_info.post_office || 'N/A') + ' (' + (data.postal_info.source || 'India Post') + ')';
        } else {
            document.getElementById('resPostalLoc').textContent = 'No postal record found';
            document.getElementById('resPostOffices').textContent = 'Checked via India Post & Zippopotam';
        }

        // Logistics
        if (data.serviceability) {
            document.getElementById('resZone').textContent = data.serviceability.zone === 'local_fleet' ? 'Local Fleet (Express)' : 'National Courier';
            document.getElementById('resCharge').textContent = '₹' + parseFloat(data.serviceability.delivery_charge || 0).toFixed(2);
            document.getElementById('resCod').textContent = data.serviceability.is_cod_available ? 'Enabled (COD fee: ₹' + (data.serviceability.cod_fee || 0) + ')' : 'Prepaid Orders Only';
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Verify';
        alert('Network error testing pincode: ' + err.message);
    });
}
</script>
@endsection
