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
                    <h4 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill me-2"></i>LocationIQ & Maps Geocoding Control Center</h4>
                    <p class="mb-0 text-white-50 small">Configure your LocationIQ client access token for interactive checkout maps, doorstep delivery GPS pinning, and instant address autocomplete with free OpenStreetMap fallback.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.integrations.index') }}" class="btn btn-light rounded-pill px-3 py-2 fw-bold text-dark shadow-sm">
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
            <div class="text-muted" style="font-size: 0.72rem;">{{ $isConfigured ? 'Client Token Configured' : 'Using Free OpenStreetMap' }}</div>
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
            <div class="text-muted small fw-bold mb-1"><i class="bi bi-lightning-charge text-warning me-1"></i>Free Tier Limit</div>
            <div class="fs-4 fw-black text-success">5,000 <span class="fs-6 text-muted fw-normal">req/day</span></div>
            <div class="text-muted" style="font-size: 0.72rem;">Free Forever tier by LocationIQ</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: LocationIQ Token Form & Instructions -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-key-fill text-success me-2"></i>LocationIQ Access Token</h6>
                <span id="tokenStatusBadge" class="badge {{ $isConfigured ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
                    {{ $isConfigured ? 'Connected ✓' : 'Default Token' }}
                </span>
            </div>
            <div class="card-body p-4">
                @if(session('error'))
                    <div class="alert alert-danger py-2.5 px-3 rounded-3 small mb-3 border-0 d-flex align-items-start gap-2 shadow-xs" style="background: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444 !important;">
                        <i class="bi bi-x-circle-fill fs-6 flex-shrink-0 mt-0.5"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success py-2.5 px-3 rounded-3 small mb-3 border-0 d-flex align-items-start gap-2 shadow-xs" style="background: #f0fdf4; color: #166534; border-left: 4px solid #22c55e !important;">
                        <i class="bi bi-check-circle-fill fs-6 flex-shrink-0 mt-0.5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form action="{{ route('admin.integrations.locationiq.update') }}" method="POST" id="locationiqForm" onsubmit="return handleSaveToken(event)">
                    @csrf
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-bold text-dark small mb-0">LocationIQ API Access Token (Public Key)</label>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                <i class="bi bi-shield-lock-fill me-1"></i>Encrypted in DB
                            </span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-geo-alt"></i></span>
                            <input type="password" name="locationiq_api_key" id="locationiq_api_key" class="form-control font-monospace" value="{{ old('locationiq_api_key', $locationiqKey) }}" placeholder="pk.xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" autocomplete="off">
                            <button type="button" class="btn btn-outline-secondary" onclick="toggleTokenVisibility()" title="Toggle view token"><i class="bi bi-eye" id="toggleIcon"></i></button>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-1 text-muted" style="font-size: 0.72rem;">
                            <span>Starts with <code>pk.</code> (Public Access Token).</span>
                            <span class="text-primary fw-semibold"><i class="bi bi-shield-check me-0.5"></i> Auto-validated on save</span>
                        </div>
                    </div>

                    <!-- Live Test Connection Feedback Area -->
                    <div id="testResultAlert" class="alert d-none py-2 px-3 small rounded-3 mb-3"></div>

                    <div class="d-flex gap-2">
                        <button type="button" id="btnTestApi" class="btn btn-outline-secondary rounded-pill px-3 fw-bold small" onclick="testConnection()">
                            <i class="bi bi-broadcast me-1"></i> Test Connection
                        </button>
                        <button type="submit" class="btn btn-success rounded-pill flex-grow-1 fw-bold" id="btnSaveApi">
                            <i class="bi bi-check-circle me-1"></i> Save Access Token
                        </button>
                    </div>
                </form>
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
                    <p class="mb-2" style="font-size: 0.75rem;">If the LocationIQ client token is missing or daily quota is reached, ShopCalm automatically switches to free <strong>OpenStreetMap Nominatim</strong> with zero downtime.</p>
                    
                    <div id="osmResultAlert" class="alert d-none py-2 px-3 small rounded-3 mb-2"></div>

                    <button type="button" id="btnTestOsm" class="btn btn-sm btn-outline-success rounded-pill w-100 fw-bold" onclick="testOsmConnection()">
                        <i class="bi bi-shield-check me-1"></i> Test OpenStreetMap Fallback Ping
                    </button>
                </div>

                <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.8rem;"><i class="bi bi-mailbox2 text-primary me-1"></i> Postal Pincodes Auto-Fill</div>
                        <div class="text-muted" style="font-size: 0.72rem;">India Post & local serviceability directory</div>
                    </div>
                    <a href="{{ route('admin.integrations.pincodes') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" style="font-size: 0.72rem;">
                        Open Hub <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive Map Preview & Recent Geocoded Addresses -->
    <div class="col-lg-7">
        <!-- Interactive Map Preview Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-map-fill text-danger me-2"></i>Live Geocoding Preview Map</h6>
                    <span class="text-muted" style="font-size: 0.72rem;">Click anywhere on the map to test live reverse geocoding with your configured key</span>
                </div>
                <span id="mapProviderBadge" class="badge bg-light text-dark border rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                    Leaflet + OpenStreetMap
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
                    <span class="text-muted" style="font-size: 0.72rem;">Verified customer delivery locations saved with latitude & longitude</span>
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

<!-- Leaflet CSS & JS for Admin Preview Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
function toggleTokenVisibility() {
    const input = document.getElementById('locationiq_api_key');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

function handleSaveToken(e) {
    e.preventDefault();

    const input = document.getElementById('locationiq_api_key');
    const token = input.value.trim();
    const btn = document.getElementById('btnSaveApi');
    const alertBox = document.getElementById('testResultAlert');
    const statusBadge = document.getElementById('tokenStatusBadge');

    // Quick client-side check if non-empty
    if (token !== '') {
        if (!token.startsWith('pk.')) {
            alertBox.className = 'alert alert-danger py-2.5 px-3 small rounded-3 mb-3 shadow-xs';
            alertBox.classList.remove('d-none');
            alertBox.innerHTML = '<strong><i class="bi bi-x-circle-fill me-1"></i> Invalid Token Format:</strong> LocationIQ public access tokens must start with <code>pk.</code> (e.g. <code>pk.8fcbbfbdafcf2d840b41ffd3c60e443f</code>). The token was NOT saved.';
            input.focus();
            return false;
        }

        if (token.length < 20) {
            alertBox.className = 'alert alert-danger py-2.5 px-3 small rounded-3 mb-3 shadow-xs';
            alertBox.classList.remove('d-none');
            alertBox.innerHTML = '<strong><i class="bi bi-x-circle-fill me-1"></i> Token Too Short:</strong> Please enter a complete LocationIQ access token.';
            input.focus();
            return false;
        }
    }

    // Disable button & show spinner
    const origBtnHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Validating with LocationIQ...';
    alertBox.className = 'alert d-none py-2 px-3 small rounded-3 mb-3';

    fetch("{{ route('admin.integrations.locationiq.update') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ locationiq_api_key: token })
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
    .then(({ status, body }) => {
        btn.disabled = false;
        btn.innerHTML = origBtnHtml;
        alertBox.classList.remove('d-none');

        if (status === 200 && body.success) {
            alertBox.className = 'alert alert-success py-2.5 px-3 small rounded-3 mb-3 shadow-xs';
            alertBox.innerHTML = '<strong><i class="bi bi-check-circle-fill me-1"></i> ' + (body.message || 'Token verified and saved!') + '</strong>' + 
                (body.display_name ? '<br><span class="text-dark small"><i class="bi bi-geo-alt me-1 text-danger"></i> Verified location: ' + body.display_name + '</span>' : '');

            if (statusBadge) {
                if (token !== '') {
                    statusBadge.className = 'badge bg-success rounded-pill px-3 py-1 fw-bold';
                    statusBadge.textContent = 'Connected ✓';
                } else {
                    statusBadge.className = 'badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold';
                    statusBadge.textContent = 'OSM Fallback Active';
                }
            }

            if (body.display_name) {
                const geoText = document.getElementById('geocodedText');
                if (geoText) geoText.textContent = body.display_name;
            }
        } else {
            alertBox.className = 'alert alert-danger py-2.5 px-3 small rounded-3 mb-3 shadow-xs';
            alertBox.innerHTML = '<strong><i class="bi bi-x-circle-fill me-1"></i> Verification Failed:</strong> ' + 
                (body.message || 'LocationIQ rejected this access token. The token was NOT saved.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origBtnHtml;
        alertBox.classList.remove('d-none');
        alertBox.className = 'alert alert-danger py-2.5 px-3 small rounded-3 mb-3 shadow-xs';
        alertBox.innerHTML = '<strong><i class="bi bi-x-circle-fill me-1"></i> Connection Error:</strong> ' + err.message;
    });

    return false;
}

function testConnection() {
    const btn = document.getElementById('btnTestApi');
    const alertBox = document.getElementById('testResultAlert');
    const token = document.getElementById('locationiq_api_key').value;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
    alertBox.className = 'alert d-none py-2 px-3 small rounded-3 mb-3';

    fetch("{{ route('admin.integrations.locationiq.test') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ locationiq_api_key: token })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-broadcast me-1"></i> Test Connection';
        alertBox.classList.remove('d-none');
        if (data.success) {
            alertBox.className = 'alert alert-success py-2 px-3 small rounded-3 mb-3';
            alertBox.innerHTML = '<strong><i class="bi bi-check-circle-fill me-1"></i>' + data.message + '</strong><br><span class="text-dark">' + (data.display_name || '') + '</span>';
            // Also test map geocoding output
            document.getElementById('geocodedText').textContent = data.display_name || 'Bengaluru, Karnataka';
        } else {
            alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3';
            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'Connection test failed');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-broadcast me-1"></i> Test Connection';
        alertBox.classList.remove('d-none');
        alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-3';
        alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error: ' + err.message;
    });
}

function testOsmConnection() {
    const btn = document.getElementById('btnTestOsm');
    const alertBox = document.getElementById('osmResultAlert');

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Pinging OSM...';
    alertBox.className = 'alert d-none py-2 px-3 small rounded-3 mb-2';

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
        alertBox.classList.remove('d-none');
        if (data.success) {
            alertBox.className = 'alert alert-success py-2 px-3 small rounded-3 mb-2';
            alertBox.innerHTML = '<strong><i class="bi bi-check-circle-fill me-1"></i>' + data.message + '</strong><br><span class="text-dark">' + (data.display_name || '') + '</span>';
        } else {
            alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-2';
            alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + (data.message || 'OSM test failed');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Test OpenStreetMap Fallback Ping';
        alertBox.classList.remove('d-none');
        alertBox.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-2';
        alertBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Error: ' + err.message;
    });
}

// Initialize Leaflet Map in Admin Preview
document.addEventListener('DOMContentLoaded', function() {
    try {
        const defaultLat = 12.9716;
        const defaultLng = 77.5946;

        const map = L.map('adminTestMap').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        function reverseGeocode(lat, lng) {
            document.getElementById('coordsBadge').textContent = lat.toFixed(4) + ', ' + lng.toFixed(4);
            document.getElementById('geocodedText').textContent = 'Resolving address...';

            const token = document.getElementById('locationiq_api_key').value;
            if (token && token.trim() !== '') {
                const url = 'https://us1.locationiq.com/v1/reverse?key=' + encodeURIComponent(token.trim()) + '&lat=' + lat + '&lon=' + lng + '&format=json&accept-language=en';
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.display_name) {
                            document.getElementById('geocodedText').textContent = data.display_name;
                        } else {
                            fallbackOsm(lat, lng);
                        }
                    })
                    .catch(() => fallbackOsm(lat, lng));
            } else {
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
