@extends('admin.layouts.app')

@section('header', 'LocationIQ & Maps Geocoding Hub')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.integrations.index') }}">Integrations Hub</a></li>
    <li class="breadcrumb-item active" aria-current="page">LocationIQ Maps API</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Header Summary Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #0d9488 0%, #059669 100%);">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill me-2"></i>LocationIQ Multi-Key Pool &amp; Maps Geocoding Hub</h4>
                    <p class="mb-0 text-white-50 small">Manage multiple LocationIQ free-tier access tokens with automatic rotation and failover. Combined daily quota, doorstep GPS pinning, address autocomplete, and free OpenStreetMap fallback.</p>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-success shadow-sm" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Add LocationIQ Key
                    </button>
                    <a href="{{ route('admin.integrations.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 fw-bold shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Integrations Hub
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
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-shield-check text-success me-1"></i>Provider Status</div>
            <div class="fs-5 fw-black {{ $isConfigured ? 'text-success' : 'text-warning' }}">
                {{ $isConfigured ? 'LocationIQ Active' : 'OSM Fallback' }}
            </div>
            <div class="text-muted" style="font-size: 0.72rem;">{{ count($activeKeys) }} Active {{ Str::plural('Key', count($activeKeys)) }} Configured</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-pin-map text-primary me-1"></i>Geocoded Addresses</div>
            <div class="fs-4 fw-black text-dark">{{ $stats['geocoded_addresses'] ?? 0 }} <span class="fs-6 text-muted fw-normal">/ {{ $stats['total_addresses'] ?? 0 }}</span></div>
            <div class="text-muted" style="font-size: 0.72rem;">Addresses with GPS pins</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-bag-check text-info me-1"></i>Geocoded Orders</div>
            <div class="fs-4 fw-black text-info">{{ $stats['geocoded_orders'] ?? 0 }} <span class="fs-6 text-muted fw-normal">/ {{ $stats['total_orders'] ?? 0 }}</span></div>
            <div class="text-muted" style="font-size: 0.72rem;">Orders with Doorstep GPS</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100">
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-lightning-charge text-warning me-1"></i>Combined Daily Quota</div>
            <div class="fs-4 fw-black text-success">{{ number_format($totalDailyQuota) }} <span class="fs-6 text-muted fw-normal">req/day</span></div>
            <div class="text-muted" style="font-size: 0.72rem;">{{ count($activeKeys) }} Accounts &times; 5,000 Free Req/Day</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Multi-Key Pool Manager & Fallback Info -->
    <div class="col-lg-6">
        <!-- Multi-Key Pool Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-collection-fill text-success me-2"></i>Multi-Key Pool Manager</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Automatic rotation across multiple free-tier accounts</span>
                </div>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Key
                </button>
            </div>
            <div class="card-body p-4">
                <!-- Live Feedback Area -->
                <div id="poolAlertBox" class="alert d-none py-2.5 px-3 small rounded-3 mb-3"></div>

                @if(session('error'))
                    <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 d-flex align-items-start gap-2 shadow-xs alert-auto-dismiss" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                        <i class="bi bi-x-circle-fill fs-6 flex-shrink-0 mt-0.5"></i>
                        <div class="flex-grow-1">{{ session('error') }}</div>
                        <button type="button" class="btn-close flex-shrink-0 ms-1 mt-0.5" aria-label="Close" style="font-size: 0.65rem;" onclick="dismissAlertElement(this.closest('.alert'))"></button>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 d-flex align-items-start gap-2 shadow-xs alert-auto-dismiss" style="background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
                        <i class="bi bi-check-circle-fill fs-6 flex-shrink-0 mt-0.5"></i>
                        <div class="flex-grow-1">{{ session('success') }}</div>
                        <button type="button" class="btn-close flex-shrink-0 ms-1 mt-0.5" aria-label="Close" style="font-size: 0.65rem;" onclick="dismissAlertElement(this.closest('.alert'))"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" id="keysTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-2">Account / Key</th>
                                <th>Daily Limit</th>
                                <th>Status</th>
                                <th class="text-end pe-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="keysTableBody">
                            @forelse($keyPool as $index => $item)
                            <tr id="row_{{ $item['id'] }}">
                                <td class="ps-2">
                                    <div class="fw-bold text-dark">{{ $item['label'] ?? ('Key #' . ($index + 1)) }}</div>
                                    <div class="font-monospace text-muted small" style="font-size: 0.72rem;">{{ $item['masked_key'] ?? 'pk.••••' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.68rem;">5,000 req/day</span>
                                </td>
                                <td>
                                    @if(!empty($item['is_exhausted']))
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="Temporarily rate limited, will auto-reset">
                                            <i class="bi bi-hourglass-split me-0.5"></i> Limit Hit
                                        </span>
                                    @elseif(!empty($item['is_active']))
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                            <i class="bi bi-check-circle-fill me-0.5"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                            <i class="bi bi-dash-circle me-0.5"></i> Disabled
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-2">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" title="Test Key" onclick="testSingleKey('{{ $item['id'] }}', this)">
                                            <i class="bi bi-broadcast"></i> Test
                                        </button>
                                        <button type="button" class="btn btn-outline-{{ !empty($item['is_active']) ? 'warning' : 'success' }} btn-sm py-1 px-2" title="{{ !empty($item['is_active']) ? 'Disable' : 'Enable' }}" onclick="toggleKey('{{ $item['id'] }}', this)">
                                            <i class="bi bi-power"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete Key" onclick="deleteKey('{{ $item['id'] }}', '{{ addslashes($item['label'] ?? 'Key') }}', this)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyKeysRow">
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-key text-secondary fs-3 d-block mb-1"></i>
                                    No LocationIQ keys added to the pool yet.<br>
                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 mt-2 fw-bold" data-bs-toggle="modal" data-bs-target="#addKeyModal">
                                        <i class="bi bi-plus-circle me-1"></i> Add Your First Free Key
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-light-subtle rounded-3 p-3 border mt-3 small text-muted" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle-fill text-success me-1"></i> <strong>How Multi-Key Rotation Works:</strong> 
                    When customers use checkout maps or search addresses, requests rotate evenly across your active keys. If any account reaches its 5,000 daily limit (HTTP 429), it automatically fails over to the next key with zero customer interruption!
                </div>
            </div>
        </div>

        <!-- Security Best Practice Card: HTTP Referrer Restriction -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 border-start border-4 border-primary">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Token Security &amp; Referrer Restriction</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;">Best Practice</span>
            </div>
            <div class="card-body p-4 small">
                <p class="mb-2 text-dark fw-medium">Restrict each LocationIQ key to your authorized domains so unauthorized sites cannot use your quota:</p>
                <ol class="ps-3 mb-3 text-muted" style="line-height: 1.6; font-size: 0.76rem;">
                    <li>Log in to your <a href="https://my.locationiq.com/dashboard#accesstokens" target="_blank" class="fw-bold text-primary text-decoration-underline">LocationIQ Access Tokens Dashboard</a>.</li>
                    <li>Click <strong>Edit / Settings</strong> on your public token (<code>pk....</code>).</li>
                    <li>Under <strong>Allowed HTTP Referrers</strong>, add your domains:
                        <div class="bg-light p-2 rounded-2 font-monospace text-dark mt-1 border">
                            https://shopcalm.in/*<br>
                            https://hub.shopcalm.in/*
                        </div>
                    </li>
                    <li>Click <strong>Save</strong>. Once restricted, any third-party scraper will be blocked by LocationIQ.</li>
                </ol>
            </div>
        </div>

        <!-- Dual-Provider & OpenStreetMap Fallback Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-layers-fill text-success me-2"></i>Dual-Provider Fallback Architecture</h6>
            </div>
            <div class="card-body p-4 small text-muted">
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-1"></i> OpenStreetMap Nominatim</div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill" style="font-size: 0.68rem;">Fallback Ready</span>
                    </div>
                    <p class="mb-2" style="font-size: 0.75rem;">If all LocationIQ pool keys are exhausted, ShopCalm seamlessly switches to free <strong>OpenStreetMap Nominatim</strong> with zero downtime.</p>
                    
                    <div id="osmResultAlert" class="alert d-none py-2 px-3 small rounded-3 mb-2"></div>

                    <button type="button" id="btnTestOsm" class="btn btn-sm btn-outline-success rounded-pill w-100 fw-bold" onclick="testOsmConnection()">
                        <i class="bi bi-shield-check me-1"></i> Test OpenStreetMap Fallback Ping
                    </button>
                </div>

                <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.8rem;"><i class="bi bi-mailbox2 text-primary me-1"></i> Postal Pincodes Auto-Fill</div>
                        <div class="text-muted" style="font-size: 0.72rem;">India Post &amp; local serviceability directory</div>
                    </div>
                    <a href="{{ route('admin.integrations.pincodes') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" style="font-size: 0.72rem;">
                        Open Hub <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Map Preview & Recent Geocoded Addresses -->
    <div class="col-lg-6">
        <!-- Interactive Map Preview Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-map-fill text-danger me-2"></i>Live Geocoding Preview Map</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Click anywhere on the map to test live reverse geocoding with your active pool</span>
                </div>
                <span id="mapProviderBadge" class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                    Leaflet + Multi-Key Pool
                </span>
            </div>
            <div class="card-body p-0 position-relative">
                <div id="adminTestMap" style="height: 280px; width: 100%; z-index: 1;"></div>
                <div id="mapAddressOutput" class="p-3 bg-white border-top small text-muted d-flex align-items-center justify-content-between">
                    <div class="text-truncate me-2">
                        <i class="bi bi-geo-alt text-danger me-1"></i>
                        <span id="geocodedText" class="fw-medium text-dark">Click the map or drag the pin to test geocoding...</span>
                    </div>
                    <span id="coordsBadge" class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;">12.9716, 77.5946</span>
                </div>
            </div>
        </div>

        <!-- Recent Customer Geocoded Addresses Table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>Recent Customer Addresses with GPS Pins</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Verified customer delivery locations saved with latitude &amp; longitude</span>
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                    {{ count($recentGeocodedAddresses) }} Recent Pins
                </span>
            </div>
            <div class="card-body p-0">
                @if(count($recentGeocodedAddresses) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Customer</th>
                                <th>Delivery Address</th>
                                <th>Coordinates</th>
                                <th class="text-end pe-3">Navigation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentGeocodedAddresses as $addr)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">
                                    {{ $addr->name ?: ($addr->user?->name ?: 'Customer') }}
                                    <div class="text-muted fw-normal" style="font-size: 0.7rem;">{{ $addr->phone ?: $addr->user?->mobile_number }}</div>
                                </td>
                                <td>
                                    <div class="text-truncate text-dark fw-medium" style="max-width: 220px;" title="{{ $addr->address_line1 }}, {{ $addr->city }}">
                                        {{ $addr->address_line1 }}
                                    </div>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $addr->city }}, {{ $addr->pincode }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.7rem;">
                                        {{ number_format($addr->latitude, 4) }}, {{ number_format($addr->longitude, 4) }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $addr->latitude }},{{ $addr->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-geo-alt-fill me-1"></i> Maps
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-4 text-center text-muted small">
                    <i class="bi bi-pin-map text-secondary fs-3 d-block mb-2"></i>
                    No customer addresses with GPS coordinates recorded yet.<br>
                    Coordinates will appear here automatically as customers place orders using the checkout location picker.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Key Modal -->
<div class="modal fade" id="addKeyModal" tabindex="-1" aria-labelledby="addKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title fw-bold" id="addKeyModalLabel">
                    <i class="bi bi-plus-circle me-1"></i> Add LocationIQ API Key to Pool
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addKeyForm" onsubmit="return handleAddKey(event)">
                <div class="modal-body p-4">
                    <div id="modalAlertBox" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

                    <div class="mb-3">
                        <label for="new_key_label" class="form-label fw-bold text-dark small mb-1">Key Label / Account Name</label>
                        <input type="text" class="form-control" id="new_key_label" placeholder="e.g. Account 2 (Secondary) or Personal Email" maxlength="100">
                        <div class="text-muted" style="font-size: 0.72rem;">Give this key a helpful name to identify which LocationIQ account it belongs to.</div>
                    </div>

                    <div class="mb-3">
                        <label for="new_key_token" class="form-label fw-bold text-dark small mb-1">LocationIQ Public Client Access Token <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="text" class="form-control font-monospace" id="new_key_token" placeholder="pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" required autocomplete="off">
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.72rem;">
                            Must start with <code>pk.</code> from <a href="https://my.locationiq.com/dashboard#accesstokens" target="_blank" class="text-primary fw-semibold">LocationIQ Dashboard</a>. Automatically tested &amp; verified live before saving.
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold" id="btnSubmitAddKey">
                        <i class="bi bi-shield-check me-1"></i> Verify &amp; Add Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leaflet CSS & JS for Admin Preview Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
// Auto-dismiss helper
function showAutoDismissAlert(alertEl, type, htmlContent, duration = 5000) {
    if (!alertEl) return;

    if (alertEl._timer) {
        clearTimeout(alertEl._timer);
        alertEl._timer = null;
    }

    alertEl.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
    alertEl.style.opacity = '1';
    alertEl.style.transform = 'translateY(0)';
    alertEl.classList.remove('d-none');
    alertEl.className = `alert alert-${type} py-2.5 px-3 small rounded-3 mb-3 d-flex align-items-start gap-2 shadow-xs position-relative`;

    alertEl.innerHTML = `
        <div class="flex-grow-1">${htmlContent}</div>
        <button type="button" class="btn-close flex-shrink-0 ms-1 mt-0.5" aria-label="Close" style="font-size: 0.65rem;" onclick="dismissAlertElement(this.closest('.alert'))"></button>
    `;

    if (duration > 0) {
        alertEl._timer = setTimeout(() => {
            dismissAlertElement(alertEl);
        }, duration);
    }
}

function dismissAlertElement(alertEl) {
    if (!alertEl) return;
    if (alertEl._timer) {
        clearTimeout(alertEl._timer);
        alertEl._timer = null;
    }
    alertEl.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
    alertEl.style.opacity = '0';
    alertEl.style.transform = 'translateY(-4px)';
    setTimeout(() => {
        alertEl.classList.add('d-none');
        alertEl.innerHTML = '';
        alertEl.style.opacity = '1';
        alertEl.style.transform = 'translateY(0)';
    }, 350);
}

// Add Key Form Handler
function handleAddKey(e) {
    e.preventDefault();

    const tokenInput = document.getElementById('new_key_token');
    const labelInput = document.getElementById('new_key_label');
    const token = tokenInput.value.trim();
    const label = labelInput.value.trim();
    const btn = document.getElementById('btnSubmitAddKey');
    const alertBox = document.getElementById('modalAlertBox');
    const poolAlert = document.getElementById('poolAlertBox');

    if (!token.startsWith('pk.')) {
        showAutoDismissAlert(alertBox, 'danger', '<strong><i class="bi bi-x-circle-fill me-1"></i> Invalid Format:</strong> Token must start with <code>pk.</code> (Public Client Token).', 6000);
        tokenInput.focus();
        return false;
    }

    if (token.length < 20) {
        showAutoDismissAlert(alertBox, 'danger', '<strong><i class="bi bi-x-circle-fill me-1"></i> Token Too Short:</strong> Please enter the complete LocationIQ key.', 6000);
        tokenInput.focus();
        return false;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying with LocationIQ...';

    fetch("{{ route('admin.integrations.locationiq.keys.add') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ key: token, label: label })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (status === 200 && body.success) {
            // Close modal
            const modalEl = document.getElementById('addKeyModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            tokenInput.value = '';
            labelInput.value = '';

            // Reload to reflect updated pool & metrics
            window.location.reload();
        } else {
            showAutoDismissAlert(alertBox, 'danger', `<strong><i class="bi bi-x-circle-fill me-1"></i> Validation Failed:</strong> ${body.message || 'Key rejected by LocationIQ.'}`, 8000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        showAutoDismissAlert(alertBox, 'danger', `<strong><i class="bi bi-x-circle-fill me-1"></i> Connection Error:</strong> ${err.message}`, 8000);
    });

    return false;
}

// Test a single key from the pool
function testSingleKey(keyId, btn) {
    const alertBox = document.getElementById('poolAlertBox');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch("{{ route('admin.integrations.locationiq.test') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ key_id: keyId })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.success) {
            const locDetails = data.display_name 
                ? `<div class="text-dark small mt-1 text-truncate" style="max-width: 400px;"><i class="bi bi-geo-alt me-1 text-danger"></i>${data.display_name}</div>` 
                : '';
            showAutoDismissAlert(alertBox, 'success', `<strong><i class="bi bi-check-circle-fill me-1"></i> Key Verified (${data.masked_key || 'Active'}):</strong> ${data.message}${locDetails}`, 6000);

            const geoText = document.getElementById('geocodedText');
            if (geoText && data.display_name) geoText.textContent = data.display_name;
        } else {
            showAutoDismissAlert(alertBox, 'danger', `<strong><i class="bi bi-exclamation-triangle-fill me-1"></i> Test Failed:</strong> ${data.message}`, 8000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        showAutoDismissAlert(alertBox, 'danger', `<strong>Error:</strong> ${err.message}`, 8000);
    });
}

// Toggle key active/disabled status
function toggleKey(keyId, btn) {
    const alertBox = document.getElementById('poolAlertBox');
    btn.disabled = true;

    fetch("{{ route('admin.integrations.locationiq.keys.toggle') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id: keyId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            btn.disabled = false;
            showAutoDismissAlert(alertBox, 'danger', data.message || 'Action failed.', 6000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        showAutoDismissAlert(alertBox, 'danger', err.message, 6000);
    });
}

// Delete a key from the pool
function deleteKey(keyId, label, btn) {
    if (!confirm(`Are you sure you want to remove "${label}" from your LocationIQ pool?`)) {
        return;
    }

    const alertBox = document.getElementById('poolAlertBox');
    btn.disabled = true;

    fetch("{{ route('admin.integrations.locationiq.keys.delete') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ id: keyId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById(`row_${keyId}`);
            if (row) row.remove();
            showAutoDismissAlert(alertBox, 'info', '<strong>Key Removed:</strong> The key was removed from your active pool.', 4000);
            setTimeout(() => window.location.reload(), 1200);
        } else {
            btn.disabled = false;
            showAutoDismissAlert(alertBox, 'danger', data.message || 'Failed to remove key.', 6000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        showAutoDismissAlert(alertBox, 'danger', err.message, 6000);
    });
}

// Test OSM Connection
function testOsmConnection() {
    const btn = document.getElementById('btnTestOsm');
    const alertBox = document.getElementById('osmResultAlert');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Pinging OSM...';
    if (alertBox._timer) clearTimeout(alertBox._timer);
    alertBox.classList.add('d-none');

    fetch("{{ route('admin.integrations.locationiq.test-osm') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Test OpenStreetMap Fallback Ping';
        if (data.success) {
            const locDetails = data.display_name 
                ? `<div class="text-muted mt-1 text-truncate" style="font-size: 0.72rem; max-width: 360px;" title="${data.display_name}"><i class="bi bi-geo-alt text-danger me-1"></i>${data.display_name}</div>` 
                : '';
            showAutoDismissAlert(alertBox, 'success', `<div class="fw-bold"><i class="bi bi-check-circle-fill text-success me-1"></i>${data.message}</div>${locDetails}`, 5000);
        } else {
            showAutoDismissAlert(alertBox, 'danger', `<div><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>OSM Fallback Test Failed:</strong> ${data.message || 'OSM test failed'}</div>`, 8000);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Test OpenStreetMap Fallback Ping';
        showAutoDismissAlert(alertBox, 'danger', `<div><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Error:</strong> ${err.message}</div>`, 8000);
    });
}

// Initialize Leaflet Map in Admin Preview with Active Pool Keys
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert-auto-dismiss').forEach(function(el) {
        el._timer = setTimeout(function() {
            dismissAlertElement(el);
        }, 6000);
    });

    try {
        const defaultLat = 12.9716;
        const defaultLng = 77.5946;
        const poolKeys = @json($activeKeys ?? []);

        const map = L.map('adminTestMap').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        async function reverseGeocode(lat, lng) {
            document.getElementById('coordsBadge').textContent = lat.toFixed(4) + ', ' + lng.toFixed(4);
            document.getElementById('geocodedText').textContent = 'Resolving address via pool...';

            let resolved = false;

            if (Array.isArray(poolKeys) && poolKeys.length > 0) {
                for (const key of poolKeys) {
                    try {
                        const url = 'https://us1.locationiq.com/v1/reverse?key=' + encodeURIComponent(key) + '&lat=' + lat + '&lon=' + lng + '&format=json&accept-language=en';
                        const res = await fetch(url);
                        if (res.status === 429) continue; // Try next key
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.display_name) {
                                document.getElementById('geocodedText').textContent = data.display_name;
                                resolved = true;
                                break;
                            }
                        }
                    } catch (e) {
                        // Continue to next key
                    }
                }
            }

            if (!resolved) {
                fallbackOsm(lat, lng);
            }
        }

        function fallbackOsm(lat, lng) {
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('geocodedText').textContent = (data && data.display_name) ? data.display_name : 'Lat: ' + lat + ', Lng: ' + lng;
                })
                .catch(() => {
                    document.getElementById('geocodedText').textContent = 'Coordinates: ' + lat.toFixed(4) + ', ' + lng.toFixed(4);
                });
        }

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        marker.on('dragend', function(e) {
            const pos = marker.getLatLng();
            reverseGeocode(pos.lat, pos.lng);
        });
    } catch(e) {
        console.warn('Admin map init error:', e);
    }
});
</script>
@endsection
