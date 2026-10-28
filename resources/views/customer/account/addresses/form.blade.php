<div class="row g-3">
    {{-- Live Location GPS Button --}}
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 border" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0" style="width: 28px; height: 28px; background: rgba(16, 185, 129, 0.15);">
                    <i class="bi bi-geo-alt-fill" style="font-size: 0.85rem;"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark" style="font-size: 0.82rem;">Fetch Live Location</div>
                    <div class="text-muted" style="font-size: 0.72rem;">Auto-detect PIN, city, state & street via GPS</div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btn-page-gps" onclick="fetchPageGpsLocation()" style="font-size: 0.75rem;">
                <i class="bi bi-crosshair" id="page-gps-icon"></i>
                <span id="page-gps-text">Use GPS</span>
            </button>
        </div>
    </div>

    {{-- Full Name --}}
    <div class="col-md-6">
        <label for="name" class="form-label fw-semibold small text-dark mb-1">Full Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3 py-2" id="name" name="name" value="{{ old('name', $address->name ?? '') }}" placeholder="Enter full name" required>
    </div>

    {{-- Phone --}}
    <div class="col-md-6">
        <label for="phone" class="form-label fw-semibold small text-dark mb-1">Phone Number <span class="text-danger">*</span></label>
        <input type="tel" class="form-control rounded-3 py-2" id="phone" name="phone" value="{{ old('phone', $address->phone ?? '') }}" placeholder="10-digit mobile number" maxlength="10" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)" required>
    </div>

    {{-- Flat / House No. --}}
    <div class="col-md-6">
        <label for="house_no" class="form-label fw-semibold small text-dark mb-1">Flat / House / Building No. <span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3 py-2" id="house_no" name="house_no" value="{{ old('house_no', $address->house_no ?? '') }}" placeholder="e.g. Flat 302, Sai Residency" required>
    </div>

    {{-- Landmark --}}
    <div class="col-md-6">
        <label for="landmark" class="form-label fw-semibold small text-dark mb-1">Landmark <span class="text-muted">(Optional)</span></label>
        <input type="text" class="form-control rounded-3 py-2" id="landmark" name="landmark" value="{{ old('landmark', $address->landmark ?? '') }}" placeholder="e.g. Near Apollo Pharmacy">
    </div>

    {{-- Road / Street / Colony --}}
    <div class="col-12">
        <label for="street" class="form-label fw-semibold small text-dark mb-1">Road / Street / Colony <span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3 py-2" id="street" name="street" value="{{ old('street', $address->street ?? ($address->address ?? '')) }}" placeholder="Detected road or street" required>
    </div>

    {{-- Hidden single-line address fallback --}}
    <input type="hidden" id="address" name="address" value="{{ old('address', $address->address ?? '') }}">
    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $address->latitude ?? '') }}">
    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $address->longitude ?? '') }}">

    {{-- PIN Code --}}
    <div class="col-md-4">
        <label for="zip" class="form-label fw-semibold small text-dark mb-1">PIN Code <span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3 py-2 font-monospace fw-bold" id="zip" name="zip" value="{{ old('zip', $address->zip ?? '') }}" placeholder="6-digit PIN" maxlength="6" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)" required>
    </div>

    {{-- City --}}
    <div class="col-md-4">
        <label for="city" class="form-label fw-semibold small text-dark mb-1">City / District <span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3 py-2" id="city" name="city" value="{{ old('city', $address->city ?? '') }}" placeholder="City" required>
    </div>

    {{-- State --}}
    <div class="col-md-4">
        <label for="state" class="form-label fw-semibold small text-dark mb-1">State <span class="text-danger">*</span></label>
        <input type="text" class="form-control rounded-3 py-2" id="state" name="state" value="{{ old('state', $address->state ?? '') }}" placeholder="State" required>
    </div>

    {{-- Address Type Selector --}}
    <div class="col-12">
        <label class="form-label fw-semibold small text-dark mb-1">Save Address As</label>
        @php $currentType = strtolower(old('type', $address->type ?? 'home')); @endphp
        <div class="btn-group btn-group-sm w-100" role="group">
            <input type="radio" class="btn-check" name="type" id="page_type_home" value="home" {{ $currentType === 'home' ? 'checked' : '' }} autocomplete="off">
            <label class="btn btn-outline-secondary py-1.5" for="page_type_home"><i class="bi bi-house me-1"></i>Home</label>

            <input type="radio" class="btn-check" name="type" id="page_type_work" value="work" {{ $currentType === 'work' ? 'checked' : '' }} autocomplete="off">
            <label class="btn btn-outline-secondary py-1.5" for="page_type_work"><i class="bi bi-briefcase me-1"></i>Work</label>

            <input type="radio" class="btn-check" name="type" id="page_type_other" value="other" {{ $currentType === 'other' ? 'checked' : '' }} autocomplete="off">
            <label class="btn btn-outline-secondary py-1.5" for="page_type_other"><i class="bi bi-geo me-1"></i>Other</label>
        </div>
    </div>

    {{-- Country --}}
    <input type="hidden" id="country" name="country" value="{{ old('country', $address->country ?? 'India') }}">
</div>

<script>
function fetchPageGpsLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }

    const btn = document.getElementById('btn-page-gps');
    const icon = document.getElementById('page-gps-icon');
    const text = document.getElementById('page-gps-text');

    btn.disabled = true;
    icon.className = 'spinner-border spinner-border-sm';
    text.textContent = 'Locating...';

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');
            if (latEl) latEl.value = lat;
            if (lngEl) lngEl.value = lng;

            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                    headers: { 'Accept-Language': 'en' }
                });
                if (res.ok) {
                    const data = await res.json();
                    const addr = data.address || {};
                    const road = addr.road || addr.street || addr.suburb || addr.neighbourhood || addr.residential || '';
                    const city = addr.city || addr.town || addr.municipality || addr.village || '';
                    const state = addr.state || '';
                    const postcode = (addr.postcode || '').replace(/\D/g, '').slice(0, 6);

                    if (road) document.getElementById('street').value = road;
                    if (city) document.getElementById('city').value = city;
                    if (state) document.getElementById('state').value = state;
                    if (postcode && postcode.length === 6) document.getElementById('zip').value = postcode;
                }
            } catch (err) {
                console.warn('GPS geocode err:', err);
            } finally {
                btn.disabled = false;
                icon.className = 'bi bi-check2';
                text.textContent = 'Detected!';
                setTimeout(() => {
                    icon.className = 'bi bi-crosshair';
                    text.textContent = 'Use GPS';
                }, 3000);
            }
        },
        (err) => {
            btn.disabled = false;
            icon.className = 'bi bi-crosshair';
            text.textContent = 'Use GPS';
            alert('Could not acquire location permission.');
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}
</script>
