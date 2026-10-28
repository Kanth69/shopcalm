@extends('customer.account.layout')

@section('title', 'My Addresses')

@section('account_content')

{{-- Hero Banner --}}
<div class="card border-0 shadow-sm rounded-4 mb-3 mb-md-4 overflow-hidden"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-3 p-md-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="bi bi-geo-alt-fill text-white fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-white mb-0.5" style="font-size: clamp(1.1rem, 3vw, 1.3rem);">Saved Addresses</h5>
                <p class="text-white-50 small mb-0" style="font-size: 0.8rem;">Manage your delivery destinations</p>
            </div>
        </div>
        <button type="button" onclick="openCreateAddressModal()" 
                class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 w-100 w-sm-auto shadow-xs" 
                style="font-size: 0.8rem;">
            <i class="bi bi-plus-lg"></i> <span>Add New Address</span>
        </button>
    </div>
</div>

{{-- Address Grid --}}
<div id="addresses-container" class="row g-3 g-md-4 mb-4" style="{{ $addresses->isEmpty() ? 'display:none;' : '' }}">
    @foreach($addresses as $address)
    <div class="col-md-6" id="address-card-{{ $address->id }}">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden"
             style="background: #ffffff; border: 1.5px solid #e2e8f0 !important; transition: all 0.25s ease;">
            <div class="card-body p-3 p-md-4 d-flex flex-column">

                {{-- Icon + Name --}}
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px; height:44px; background:linear-gradient(135deg,#ede9fe,#ddd6fe);">
                        <i class="bi bi-house-fill" style="color:#6d28d9; font-size:1.1rem;"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <h6 class="fw-bold text-dark mb-0.5 text-truncate" id="addr-name-{{ $address->id }}" style="font-size:0.95rem;">{{ $address->name }}</h6>
                        <span class="text-muted small" id="addr-phone-{{ $address->id }}"><i class="bi bi-telephone me-1 text-primary"></i>{{ $address->phone }}</span>
                    </div>
                </div>

                {{-- Address Details --}}
                <div class="rounded-3 p-3 mb-3 flex-grow-1" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <p class="text-secondary small mb-0 lh-lg" id="addr-text-{{ $address->id }}">
                        <i class="bi bi-geo-alt text-primary me-1"></i>
                        <span class="addr-line">{{ $address->address }}</span>,<br>
                        <span class="addr-city-state-zip">{{ $address->city }}, {{ $address->state }} – {{ $address->zip }}</span><br>
                        <span class="fw-medium addr-country">{{ $address->country ?? 'India' }}</span>
                    </p>
                </div>

                {{-- Actions (Popup Edit & Instant Delete) --}}
                <div class="d-flex gap-2 pt-3 border-top mt-auto">
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold flex-fill text-center btn-edit-address"
                            style="font-size:0.8rem;"
                            data-address="{{ json_encode($address) }}">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold flex-fill"
                            style="font-size:0.8rem;"
                            onclick="deleteAddress({{ $address->id }}, '{{ route('account.addresses.destroy', $address) }}', this)">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Empty State --}}
<div id="addresses-empty-state" style="{{ $addresses->isNotEmpty() ? 'display:none;' : '' }}">
    @include('customer.account.components.empty-state', [
        'icon'        => 'bi-geo-alt',
        'title'       => 'No Saved Addresses',
        'message'     => 'You have not saved any addresses yet. Add one to speed up checkout!',
        'button_text' => 'Add New Address',
        'button_url'  => 'javascript:openCreateAddressModal()'
    ])
</div>

{{-- ── Modern Popup Modal for Address Creation & Editing (Mobile Scroll & Sticky Save Button) ────────── --}}
<style>
    #addressModal .modal-dialog {
        max-width: 560px;
        margin: 0.75rem auto;
        height: calc(100% - 1.5rem);
        max-height: calc(100dvh - 1.5rem);
        display: flex;
        align-items: center;
    }
    #addressModal .modal-content {
        max-height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        width: 100%;
    }
    #addressModal .modal-header {
        flex-shrink: 0;
    }
    #addressModal .modal-body {
        flex: 1 1 auto;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        overscroll-behavior: contain;
    }
    #addressModal .modal-footer {
        flex-shrink: 0;
        position: sticky;
        bottom: 0;
        background: #ffffff !important;
        z-index: 1055;
        box-shadow: 0 -4px 14px rgba(0, 0, 0, 0.06);
    }
    @media (max-width: 576px) {
        #addressModal .modal-dialog {
            margin: 0.5rem auto;
            width: calc(100% - 1rem);
            max-width: 100%;
            height: calc(100% - 1rem);
            max-height: calc(100dvh - 1rem);
        }
        #addressModal .modal-body {
            padding: 1rem !important;
        }
        #addressModal .modal-footer {
            padding: 0.75rem 1rem !important;
        }
    }
</style>

<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="addressForm" class="modal-content rounded-4 border-0 shadow" onsubmit="handleAddressFormSubmit(event)" novalidate>
            @csrf
            <input type="hidden" id="modal_address_id" value="">
            <input type="hidden" id="modal_http_method" value="POST">

            {{-- Modal Header --}}
            <div class="modal-header border-bottom py-3 px-3.5 px-sm-4 bg-light bg-opacity-75">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" id="addressModalIconBox"
                         style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff;">
                        <i class="bi bi-geo-alt-fill fs-5" id="addressModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="addressModalLabel" style="font-size: 1.05rem;">Add New Address</h5>
                        <p class="text-muted small mb-0" id="addressModalSubtitle" style="font-size: 0.76rem;">Enter your delivery destination details</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

                <div class="modal-body p-3.5 p-sm-4">
                    {{-- Alert for global errors --}}
                    <div class="alert alert-danger d-none py-2 px-3 rounded-3 small mb-3" id="modal-global-error"></div>

                    {{-- Live GPS Auto-fill Banner --}}
                    <div class="d-flex align-items-center justify-content-between p-2.5 mb-3 rounded-3 border" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-success flex-shrink-0" style="width: 28px; height: 28px; background: rgba(16, 185, 129, 0.15);">
                                <i class="bi bi-geo-alt-fill" style="font-size: 0.85rem;"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.82rem;">Fetch Live Location</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Auto-detect PIN, city, state & street via GPS</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btn-modal-gps" onclick="fetchModalGpsLocation()" style="font-size: 0.75rem;">
                            <i class="bi bi-crosshair" id="modal-gps-icon"></i>
                            <span id="modal-gps-text">Use GPS</span>
                        </button>
                    </div>

                    <div class="row g-3">
                        {{-- Full Name --}}
                        <div class="col-12 col-sm-6">
                            <label for="addr_input_name" class="form-label fw-semibold text-dark small mb-1">
                                Full Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 py-2 ps-1" id="addr_input_name" name="name" 
                                       placeholder="e.g. Rahul Sharma" required style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.88rem;">
                            </div>
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-name" style="display:none !important;"></div>
                        </div>

                        {{-- Phone Number --}}
                        <div class="col-12 col-sm-6">
                            <label for="addr_input_phone" class="form-label fw-semibold text-dark small mb-1">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" class="form-control border-start-0 py-2 ps-1" id="addr_input_phone" name="phone" 
                                       placeholder="10-digit mobile number" maxlength="10" inputmode="numeric" pattern="[0-9]{10}"
                                       oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)"
                                       required style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.88rem;">
                            </div>
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-phone" style="display:none !important;"></div>
                        </div>

                        {{-- Hidden Coordinates & Country --}}
                        <input type="hidden" id="addr_input_country" name="country" value="India">
                        <input type="hidden" id="addr_input_latitude" name="latitude" value="">
                        <input type="hidden" id="addr_input_longitude" name="longitude" value="">

                        {{-- Flat / House / Building Number --}}
                        <div class="col-12 col-sm-6">
                            <label for="addr_input_house_no" class="form-label fw-semibold text-dark small mb-1">
                                Flat / House / Building No. <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control py-2 px-3 rounded-3" id="addr_input_house_no" name="house_no" 
                                   placeholder="e.g. Flat 302, Sai Residency" required style="border-color: #cbd5e1; font-size: 0.88rem;">
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-house_no" style="display:none !important;"></div>
                        </div>

                        {{-- Landmark --}}
                        <div class="col-12 col-sm-6">
                            <label for="addr_input_landmark" class="form-label fw-semibold text-dark small mb-1">
                                Landmark <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="text" class="form-control py-2 px-3 rounded-3" id="addr_input_landmark" name="landmark" 
                                   placeholder="e.g. Near Apollo Pharmacy" style="border-color: #cbd5e1; font-size: 0.88rem;">
                        </div>

                        {{-- Road / Street / Colony --}}
                        <div class="col-12">
                            <label for="addr_input_street" class="form-label fw-semibold text-dark small mb-1">
                                Road / Street / Colony <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control py-2 px-3 rounded-3" id="addr_input_street" name="street" 
                                   placeholder="Detected road or street" required style="border-color: #cbd5e1; font-size: 0.88rem;">
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-street" style="display:none !important;"></div>
                        </div>

                        {{-- Pincode --}}
                        <div class="col-12 col-sm-4">
                            <label for="addr_input_zip" class="form-label fw-semibold text-dark small mb-1">
                                PIN Code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                                    <i class="bi bi-pin-map"></i>
                                </span>
                                <input type="text" class="form-control border-start-0 py-2 ps-1 font-monospace fw-bold" id="addr_input_zip" name="zip" 
                                       placeholder="6-digit PIN" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="postal-code"
                                       onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                       oninput="handleAddressModalPincodeInput(this)"
                                       required style="border-radius: 0 10px 10px 0; border-color: #cbd5e1; font-size: 0.88rem;">
                            </div>
                            <small id="addr_pincode_hint" class="d-block mt-1 transition-all" style="font-size: 0.72rem; min-height: 18px; line-height: 1.2;"></small>
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-zip" style="display:none !important;"></div>
                        </div>

                        {{-- City --}}
                        <div class="col-12 col-sm-4">
                            <label for="addr_input_city" class="form-label fw-semibold text-dark small mb-1">
                                City / District <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <input type="text" class="form-control py-2 ps-3 pe-4 rounded-3" id="addr_input_city" name="city" 
                                       placeholder="City / District" required style="border-color: #cbd5e1; font-size: 0.88rem;"
                                       oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                                <i class="bi bi-lock-fill position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted d-none" 
                                   id="addr_city_lock_icon" title="Auto-filled from PIN" style="pointer-events: none; font-size: 0.82rem;"></i>
                            </div>
                            <small id="addr_city_hint" class="d-block mt-1 text-muted transition-all" style="font-size: 0.72rem; min-height: 18px; line-height: 1.2;"></small>
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-city" style="display:none !important;"></div>
                        </div>

                        {{-- State --}}
                        <div class="col-12 col-sm-4">
                            <label for="addr_input_state" class="form-label fw-semibold text-dark small mb-1">
                                State <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <input type="text" class="form-control py-2 ps-3 pe-4 rounded-3" id="addr_input_state" name="state" 
                                       placeholder="State" required style="border-color: #cbd5e1; font-size: 0.88rem;"
                                       oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                                <i class="bi bi-lock-fill position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted d-none" 
                                   id="addr_state_lock_icon" title="Auto-filled from PIN" style="pointer-events: none; font-size: 0.82rem;"></i>
                            </div>
                            <small id="addr_state_hint" class="d-block mt-1 text-muted transition-all" style="font-size: 0.72rem; min-height: 18px; line-height: 1.2;"></small>
                            <div class="invalid-feedback d-block text-danger small mt-1" id="err-state" style="display:none !important;"></div>
                        </div>

                        {{-- Address Type Selector --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Address Type</label>
                            <div class="btn-group btn-group-sm w-100" role="group">
                                <input type="radio" class="btn-check" name="addr_type" id="addr_type_home" value="home" checked autocomplete="off">
                                <label class="btn btn-outline-secondary py-1.5" for="addr_type_home" style="font-size: 0.82rem;"><i class="bi bi-house me-1"></i>Home</label>

                                <input type="radio" class="btn-check" name="addr_type" id="addr_type_work" value="work" autocomplete="off">
                                <label class="btn btn-outline-secondary py-1.5" for="addr_type_work" style="font-size: 0.82rem;"><i class="bi bi-briefcase me-1"></i>Work</label>

                                <input type="radio" class="btn-check" name="addr_type" id="addr_type_other" value="other" autocomplete="off">
                                <label class="btn btn-outline-secondary py-1.5" for="addr_type_other" style="font-size: 0.82rem;"><i class="bi bi-geo me-1"></i>Other</label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Modal Footer (Sticky Bottom with Save Button Always in View) --}}
                <div class="modal-footer border-top py-2.5 px-3.5 px-sm-4 bg-white d-flex align-items-center justify-content-between gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-3.5 py-2 fw-semibold btn-sm" data-bs-dismiss="modal" style="font-size: 0.82rem;">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-address-modal" class="btn btn-primary rounded-pill px-4 py-2 fw-bold btn-sm shadow-xs d-inline-flex align-items-center gap-1.5" 
                            disabled
                            style="background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); border: none; font-size: 0.84rem; opacity: 0.6; cursor: not-allowed;">
                        <i class="bi bi-check2-circle" id="btn-save-icon"></i>
                        <span id="btn-save-text">Save Address</span>
                    </button>
                </div>
            </form>
    </div>
</div>

@push('scripts')
<script>
// Escape HTML to prevent XSS injection
function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) return '';
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Clear all input validations and error feedback
function clearAddressModalErrors() {
    document.querySelectorAll('#addressForm .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('#addressForm .invalid-feedback').forEach(el => {
        el.innerText = '';
        el.style.setProperty('display', 'none', 'important');
    });
    const glob = document.getElementById('modal-global-error');
    if (glob) {
        glob.classList.add('d-none');
        glob.innerText = '';
    }
    const hint = document.getElementById('addr_pincode_hint');
    if (hint) hint.innerHTML = '';
    const cityHint = document.getElementById('addr_city_hint');
    if (cityHint) cityHint.innerHTML = '';
    const stateHint = document.getElementById('addr_state_hint');
    if (stateHint) stateHint.innerHTML = '';
}

// Display error message under specific field
function showAddressFieldError(field, message) {
    const input = document.getElementById('addr_input_' + field);
    const err = document.getElementById('err-' + field);
    if (input) input.classList.add('is-invalid');
    if (err) {
        err.innerText = message;
        err.style.setProperty('display', 'block', 'important');
    }
}

// Track if current PIN code is 6-digit validated
let isProfilePincodeValidated = false;
let profilePincodeErrorTimer = null;
let pincodeLookupTimer = null;

// Enable or disable Save button
function setProfileAddressSaveEnabled(enabled) {
    const btn = document.getElementById('btn-save-address-modal');
    if (!btn) return;
    isProfilePincodeValidated = Boolean(enabled);
    btn.disabled = !enabled;
    if (enabled) {
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    } else {
        btn.style.opacity = '0.6';
        btn.style.cursor = 'not-allowed';
    }
}

// Lock / Unlock City & State inputs once PIN code is validated
function lockProfileCityState(locked) {
    const cityInput = document.getElementById('addr_input_city');
    const stateInput = document.getElementById('addr_input_state');
    const cityIcon = document.getElementById('addr_city_lock_icon');
    const stateIcon = document.getElementById('addr_state_lock_icon');
    const cityHint = document.getElementById('addr_city_hint');
    const stateHint = document.getElementById('addr_state_hint');

    if (cityInput) {
        cityInput.readOnly = locked;
        if (locked) {
            cityInput.style.backgroundColor = '#f8fafc';
            cityInput.style.cursor = 'default';
            cityInput.style.fontWeight = '600';
            cityInput.style.color = '#1e293b';
        } else {
            cityInput.style.backgroundColor = '';
            cityInput.style.cursor = '';
            cityInput.style.fontWeight = '';
            cityInput.style.color = '';
        }
    }
    if (stateInput) {
        stateInput.readOnly = locked;
        if (locked) {
            stateInput.style.backgroundColor = '#f8fafc';
            stateInput.style.cursor = 'default';
            stateInput.style.fontWeight = '600';
            stateInput.style.color = '#1e293b';
        } else {
            stateInput.style.backgroundColor = '';
            stateInput.style.cursor = '';
            stateInput.style.fontWeight = '';
            stateInput.style.color = '';
        }
    }
    if (cityIcon) cityIcon.classList.toggle('d-none', !locked);
    if (stateIcon) stateIcon.classList.toggle('d-none', !locked);
    if (cityHint) {
        cityHint.innerHTML = locked ? '<span class="text-success fw-medium"><i class="bi bi-shield-check me-0.5"></i>Auto-filled</span>' : '';
    }
    if (stateHint) {
        stateHint.innerHTML = locked ? '<span class="text-success fw-medium"><i class="bi bi-shield-check me-0.5"></i>Auto-filled</span>' : '';
    }
}

// Clear PIN error and cancel auto-dismiss timer
function clearProfilePincodeError() {
    if (profilePincodeErrorTimer) {
        clearTimeout(profilePincodeErrorTimer);
        profilePincodeErrorTimer = null;
    }
    const hintEl = document.getElementById('addr_pincode_hint');
    if (hintEl) {
        hintEl.style.opacity = '1';
        hintEl.innerHTML = '';
    }
    const errZip = document.getElementById('err-zip');
    if (errZip) {
        errZip.innerText = '';
        errZip.style.setProperty('display', 'none', 'important');
    }
}

// Show validation error for wrong PIN that auto-dismisses after 4 seconds
function showDismissableProfilePincodeError(msg) {
    clearProfilePincodeError();
    const hintEl = document.getElementById('addr_pincode_hint');
    if (!hintEl) return;
    hintEl.style.transition = 'opacity 0.35s ease';
    hintEl.style.opacity = '1';
    hintEl.innerHTML = `<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i>${msg}</span>`;

    profilePincodeErrorTimer = setTimeout(() => {
        hintEl.style.opacity = '0';
        setTimeout(() => {
            hintEl.innerHTML = '';
            hintEl.style.opacity = '1';
        }, 350);
    }, 4500);
}

// Real-time Pincode typing handler: numbers only, clears previous errors, manages locks
function handleAddressModalPincodeInput(input) {
    const pin = (input.value || '').replace(/\D/g, '').slice(0, 6);
    if (input.value !== pin) {
        input.value = pin;
    }

    // Immediately clear error when typing
    clearProfilePincodeError();

    if (pin.length < 6) {
        lockProfileCityState(false);
        setProfileAddressSaveEnabled(false);
        if (pin.length === 0) {
            document.getElementById('addr_input_city').value = '';
            document.getElementById('addr_input_state').value = '';
        }
        return;
    }

    handleAddressModalPincode(pin);
}

// Open modal for CREATION (No page reload)
function openCreateAddressModal() {
    clearAddressModalErrors();
    clearProfilePincodeError();

    document.getElementById('modal_address_id').value = '';
    document.getElementById('modal_http_method').value = 'POST';

    // Header styling
    document.getElementById('addressModalLabel').textContent = 'Add New Address';
    document.getElementById('addressModalSubtitle').textContent = 'Enter your delivery destination details';
    document.getElementById('addressModalIconBox').style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
    document.getElementById('addressModalIcon').className = 'bi bi-geo-alt-fill fs-5';

    // Button label & initial disabled state
    document.getElementById('btn-save-icon').className = 'bi bi-plus-circle';
    document.getElementById('btn-save-text').textContent = 'Save Address';
    setProfileAddressSaveEnabled(false);
    lockProfileCityState(false);

    // Default values
    document.getElementById('addr_input_name').value = '{{ auth()->user()->name ?? "" }}';
    document.getElementById('addr_input_phone').value = '{{ preg_replace("/\D/", "", substr(auth()->user()->mobile_number ?? "", -10)) }}';
    document.getElementById('addr_input_house_no').value = '';
    document.getElementById('addr_input_landmark').value = '';
    document.getElementById('addr_input_street').value = '';
    document.getElementById('addr_input_zip').value = '';
    document.getElementById('addr_input_city').value = '';
    document.getElementById('addr_input_state').value = '';
    document.getElementById('addr_input_country').value = 'India';
    document.getElementById('addr_input_latitude').value = '';
    document.getElementById('addr_input_longitude').value = '';

    const homeRadio = document.getElementById('addr_type_home');
    if (homeRadio) homeRadio.checked = true;

    delete document.getElementById('addr_input_city').dataset.autofilled;
    delete document.getElementById('addr_input_state').dataset.autofilled;

    const modalEl = document.getElementById('addressModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

// Open modal for EDITING (No page reload)
function openEditAddressModal(address) {
    if (!address) return;
    clearAddressModalErrors();
    clearProfilePincodeError();

    document.getElementById('modal_address_id').value = address.id;
    document.getElementById('modal_http_method').value = 'PUT';

    // Header styling
    document.getElementById('addressModalLabel').textContent = 'Edit Delivery Address';
    document.getElementById('addressModalSubtitle').textContent = 'Update your delivery destination details';
    document.getElementById('addressModalIconBox').style.background = 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)';
    document.getElementById('addressModalIcon').className = 'bi bi-pencil-square fs-5';

    // Button label
    document.getElementById('btn-save-icon').className = 'bi bi-check2-circle';
    document.getElementById('btn-save-text').textContent = 'Update Address';

    // Populate data
    const zipVal = (address.zip || '').replace(/\D/g, '').slice(0, 6);
    document.getElementById('addr_input_name').value = address.name || '';
    document.getElementById('addr_input_phone').value = (address.phone || '').replace(/\D/g, '').slice(-10);
    document.getElementById('addr_input_house_no').value = address.house_no || '';
    document.getElementById('addr_input_landmark').value = address.landmark || '';
    document.getElementById('addr_input_street').value = address.street || address.address || '';
    document.getElementById('addr_input_zip').value = zipVal;
    document.getElementById('addr_input_city').value = address.city || '';
    document.getElementById('addr_input_state').value = address.state || '';
    document.getElementById('addr_input_country').value = address.country || 'India';
    document.getElementById('addr_input_latitude').value = address.latitude || '';
    document.getElementById('addr_input_longitude').value = address.longitude || '';

    const typeVal = (address.type || 'home').toLowerCase();
    const typeRadio = document.getElementById('addr_type_' + typeVal) || document.getElementById('addr_type_home');
    if (typeRadio) typeRadio.checked = true;

    delete document.getElementById('addr_input_city').dataset.autofilled;
    delete document.getElementById('addr_input_state').dataset.autofilled;

    // In edit mode, if existing address has valid 6-digit zip, city and state, lock and enable save
    if (zipVal.length === 6 && address.city && address.state) {
        lockProfileCityState(true);
        setProfileAddressSaveEnabled(true);
    } else {
        lockProfileCityState(false);
        setProfileAddressSaveEnabled(false);
    }

    const modalEl = document.getElementById('addressModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

// Live GPS fetcher for Account Address Modal
function fetchModalGpsLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }

    const btn = document.getElementById('btn-modal-gps');
    const icon = document.getElementById('modal-gps-icon');
    const text = document.getElementById('modal-gps-text');

    btn.disabled = true;
    icon.className = 'spinner-border spinner-border-sm';
    text.textContent = 'Locating...';

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            document.getElementById('addr_input_latitude').value = lat;
            document.getElementById('addr_input_longitude').value = lng;

            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
                    headers: { 'Accept-Language': 'en' }
                });
                if (res.ok) {
                    const data = await res.json();
                    const addr = data.address || {};
                    const road = addr.road || addr.street || addr.suburb || addr.neighbourhood || addr.residential || '';
                    const city = addr.city || addr.town || addr.municipality || addr.village || addr.city_district || '';
                    const state = addr.state || '';
                    const postcode = (addr.postcode || '').replace(/\D/g, '').slice(0, 6);

                    if (road) document.getElementById('addr_input_street').value = road;
                    if (city) document.getElementById('addr_input_city').value = city;
                    if (state) document.getElementById('addr_input_state').value = state;
                    if (postcode && postcode.length === 6) {
                        const zipEl = document.getElementById('addr_input_zip');
                        zipEl.value = postcode;
                        handleAddressModalPincodeInput(zipEl);
                    }
                }
            } catch (err) {
                console.warn('Reverse geocoding error:', err);
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
        (error) => {
            btn.disabled = false;
            icon.className = 'bi bi-crosshair';
            text.textContent = 'Use GPS';
            alert('Could not detect location. Please allow browser location access or type manually.');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
}

// Delegate click on Edit buttons
document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('.btn-edit-address');
    if (editBtn && editBtn.dataset.address) {
        try {
            const addrData = JSON.parse(editBtn.dataset.address);
            openEditAddressModal(addrData);
        } catch (err) {
            console.error('Failed to parse address data for edit:', err);
        }
    }
});

// Real-time 6-digit Pincode auto-lookup
function handleAddressModalPincode(pincode) {
    pincode = (pincode || '').replace(/\D/g, '').slice(0, 6);
    const hintEl = document.getElementById('addr_pincode_hint');
    clearTimeout(pincodeLookupTimer);

    if (pincode.length !== 6) {
        if (hintEl) hintEl.innerHTML = '';
        lockProfileCityState(false);
        setProfileAddressSaveEnabled(false);
        return;
    }

    if (hintEl) {
        hintEl.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm me-1" style="width:0.72rem; height:0.72rem;"></span>Detecting City & State...</span>';
    }

    pincodeLookupTimer = setTimeout(async () => {
        let city = '';
        let state = '';
        let isServiceable = false;
        let deliveryMessage = '';

        // 1. Check ShopCalm store delivery serviceability first
        try {
            const res = await fetch("{{ route('delivery.check') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pincode: pincode })
            });
            const data = await res.json();
            if (data.city && !data.city.startsWith('PIN ')) city = data.city;
            if (data.state && data.state !== 'India') state = data.state;
            if (data.is_serviceable) {
                isServiceable = true;
                deliveryMessage = data.message || `Delivery available to ${data.city || 'your area'}`;
            }
        } catch (err) {
            console.warn('Delivery service check failed:', err);
        }

        // 2. Fallback to Postal Pincode Directory if city/state not in local DB
        if (!city || !state) {
            try {
                const postalRes = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
                const postalData = await postalRes.json();
                if (Array.isArray(postalData) && postalData[0]?.Status === 'Success' && postalData[0].PostOffice?.length > 0) {
                    const po = postalData[0].PostOffice[0];
                    if (!city) city = po.District || po.Division || po.Block || '';
                    if (!state) state = po.State || '';
                }
            } catch (postalErr) {
                console.warn('Postal API lookup fallback error:', postalErr);
            }
        }

        const cityInput = document.getElementById('addr_input_city');
        const stateInput = document.getElementById('addr_input_state');

        // 3. Validation result handling: only allow saving if city & state are verified
        if (city && state) {
            if (cityInput) {
                cityInput.value = city;
                cityInput.style.transition = 'background-color 0.4s ease';
                cityInput.style.backgroundColor = '#ecfdf5';
                setTimeout(() => { if (cityInput) cityInput.style.backgroundColor = ''; }, 1000);
            }
            if (stateInput) {
                stateInput.value = state;
                stateInput.style.transition = 'background-color 0.4s ease';
                stateInput.style.backgroundColor = '#ecfdf5';
                setTimeout(() => { if (stateInput) stateInput.style.backgroundColor = ''; }, 1000);
            }

            // Lock City & State (Readonly) and Enable Save Button
            lockProfileCityState(true);
            setProfileAddressSaveEnabled(true);

            if (hintEl) {
                if (isServiceable) {
                    hintEl.innerHTML = `<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>${city}, ${state} (${deliveryMessage})</span>`;
                } else {
                    hintEl.innerHTML = `<span class="text-primary fw-semibold"><i class="bi bi-geo-alt-fill me-1"></i>${city}, ${state}</span>`;
                }
            }
        } else {
            // Wrong / Non-existent PIN code: Keep unlocked, Disable Save, Show Auto-Dismiss Error
            lockProfileCityState(false);
            setProfileAddressSaveEnabled(false);
            showDismissableProfilePincodeError(`Invalid PIN code (${pincode}). Please check and enter a valid PIN.`);
        }
    }, 280);
}

// Generate Address Card HTML for smooth dynamic rendering
function renderAddressCardHtml(address) {
    const rawJson = JSON.stringify(address);
    const country = address.country || 'India';
    const houseNo = address.house_no ? escapeHtml(address.house_no) : '';
    const street = address.street ? escapeHtml(address.street) : escapeHtml(address.address || '');
    const landmark = address.landmark ? ` (Near ${escapeHtml(address.landmark)})` : '';
    const displayAddress = houseNo ? `${houseNo}, ${street}${landmark}` : `${street}${landmark}`;
    const type = (address.type || 'home').toUpperCase();
    const typeBadgeColor = type === 'WORK' ? 'background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;' : 'background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;';

    return `
    <div class="col-md-6" id="address-card-${address.id}">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden"
             style="background: #ffffff; border: 1.5px solid #e2e8f0 !important; transition: all 0.25s ease;">
            <div class="card-body p-3 p-md-4 d-flex flex-column">

                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:44px; height:44px; background:linear-gradient(135deg,#ede9fe,#ddd6fe);">
                        <i class="bi ${type === 'WORK' ? 'bi-briefcase-fill' : 'bi-house-fill'}" style="color:#6d28d9; font-size:1.1rem;"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="d-flex align-items-center gap-1.5 mb-0.5">
                            <h6 class="fw-bold text-dark mb-0 text-truncate" id="addr-name-${address.id}" style="font-size:0.95rem;">${escapeHtml(address.name)}</h6>
                            <span class="badge rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.65rem; ${typeBadgeColor}">${type}</span>
                        </div>
                        <span class="text-muted small" id="addr-phone-${address.id}"><i class="bi bi-telephone me-1 text-primary"></i>${escapeHtml(address.phone)}</span>
                    </div>
                </div>

                <div class="rounded-3 p-3 mb-3 flex-grow-1" style="background:#f8fafc; border:1px solid #e2e8f0;">
                    <p class="text-secondary small mb-0 lh-lg" id="addr-text-${address.id}">
                        <i class="bi bi-geo-alt text-primary me-1"></i>
                        <span class="addr-line">${displayAddress}</span>,<br>
                        <span class="addr-city-state-zip">${escapeHtml(address.city)}, ${escapeHtml(address.state)} – ${escapeHtml(address.zip)}</span><br>
                        <span class="fw-medium addr-country">${escapeHtml(country)}</span>
                    </p>
                </div>

                <div class="d-flex gap-2 pt-3 border-top mt-auto">
                    <button type="button"
                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold flex-fill text-center btn-edit-address"
                            style="font-size:0.8rem;"
                            data-address='${escapeHtml(rawJson)}'>
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                    <button type="button"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold flex-fill"
                            style="font-size:0.8rem;"
                            onclick="deleteAddress(${address.id}, '/account/addresses/${address.id}', this)">
                        <i class="bi bi-trash me-1"></i>Delete
                    </button>
                </div>

            </div>
        </div>
    </div>`;
}

// Handle Form Submission via AJAX (Zero Full-Page Reloads)
async function handleAddressFormSubmit(event) {
    event.preventDefault();
    clearAddressModalErrors();

    const addressId = document.getElementById('modal_address_id').value;
    const isEdit = Boolean(addressId);
    const url = isEdit ? `/account/addresses/${addressId}` : "{{ route('account.addresses.store') }}";
    const httpMethod = isEdit ? 'PUT' : 'POST';

    const houseNo = document.getElementById('addr_input_house_no').value.trim();
    const street = document.getElementById('addr_input_street').value.trim();
    const landmark = document.getElementById('addr_input_landmark').value.trim();
    const type = document.querySelector('input[name="addr_type"]:checked')?.value || 'home';
    const lat = document.getElementById('addr_input_latitude').value.trim();
    const lng = document.getElementById('addr_input_longitude').value.trim();

    const fullAddress = landmark ? `${houseNo}, ${street} (Near ${landmark})` : `${houseNo}, ${street}`;

    const payload = {
        name: document.getElementById('addr_input_name').value.trim(),
        phone: document.getElementById('addr_input_phone').value.trim(),
        house_no: houseNo,
        street: street,
        landmark: landmark,
        type: type,
        address: fullAddress,
        zip: document.getElementById('addr_input_zip').value.trim(),
        city: document.getElementById('addr_input_city').value.trim(),
        state: document.getElementById('addr_input_state').value.trim(),
        country: document.getElementById('addr_input_country').value.trim() || 'India',
        latitude: lat ? parseFloat(lat) : null,
        longitude: lng ? parseFloat(lng) : null
    };

    // Client-side quick check
    let hasClientErr = false;
    if (!payload.name) { showAddressFieldError('name', 'Please enter your full name.'); hasClientErr = true; }
    if (!payload.phone || !/^\d{10}$/.test(payload.phone)) { 
        showAddressFieldError('phone', 'Please enter a valid 10-digit mobile number.'); 
        hasClientErr = true; 
    }
    if (!payload.house_no) { showAddressFieldError('house_no', 'Please enter Flat / House / Building Number.'); hasClientErr = true; }
    if (!payload.street) { showAddressFieldError('street', 'Please enter Road / Street details.'); hasClientErr = true; }
    if (!payload.zip || payload.zip.length !== 6) { 
        showAddressFieldError('zip', 'Please enter a valid 6-digit PIN code.'); 
        hasClientErr = true; 
    }
    if (!payload.city) { showAddressFieldError('city', 'Please enter your city.'); hasClientErr = true; }
    if (!payload.state) { showAddressFieldError('state', 'Please enter your state.'); hasClientErr = true; }
    if (!isProfilePincodeValidated && !isEdit) {
        showAddressFieldError('zip', 'Please wait for your 6-digit PIN code to detect City & State.');
        showDismissableProfilePincodeError('PIN code must be validated before saving.');
        hasClientErr = true;
    }
    if (hasClientErr) return;

    // Button loading state
    const submitBtn = document.getElementById('btn-save-address-modal');
    const saveIcon = document.getElementById('btn-save-icon');
    const saveText = document.getElementById('btn-save-text');

    submitBtn.disabled = true;
    saveIcon.className = 'spinner-border spinner-border-sm me-1';
    saveText.textContent = isEdit ? 'Updating...' : 'Saving...';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-HTTP-Method-Override': httpMethod,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const msg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                    showAddressFieldError(field, msg);
                });
            } else {
                const glob = document.getElementById('modal-global-error');
                if (glob) {
                    glob.textContent = data.message || 'An unexpected error occurred. Please try again.';
                    glob.classList.remove('d-none');
                }
            }
            return;
        }

        if (data.success && data.address) {
            const savedAddress = data.address;
            const container = document.getElementById('addresses-container');
            const emptyState = document.getElementById('addresses-empty-state');

            if (isEdit) {
                // Update existing card in place
                const existingCard = document.getElementById('address-card-' + savedAddress.id);
                if (existingCard) {
                    existingCard.outerHTML = renderAddressCardHtml(savedAddress);
                    const updatedCard = document.getElementById('address-card-' + savedAddress.id);
                    if (updatedCard) {
                        updatedCard.style.boxShadow = '0 0 0 3px rgba(99, 102, 241, 0.3)';
                        setTimeout(() => { updatedCard.style.boxShadow = ''; }, 1200);
                    }
                }
            } else {
                // Prepend new card
                if (emptyState) emptyState.style.display = 'none';
                if (container) {
                    container.style.display = 'flex';
                    container.insertAdjacentHTML('afterbegin', renderAddressCardHtml(savedAddress));
                    const newCard = document.getElementById('address-card-' + savedAddress.id);
                    if (newCard) {
                        newCard.style.opacity = '0';
                        newCard.style.transform = 'translateY(-10px)';
                        requestAnimationFrame(() => {
                            newCard.style.opacity = '1';
                            newCard.style.transform = 'translateY(0)';
                        });
                    }
                }
            }

            // Close modal cleanly
            const modalEl = document.getElementById('addressModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) modalInstance.hide();

            // Toast notification
            Swal.fire({
                icon: 'success',
                title: isEdit ? 'Address Updated!' : 'Address Saved!',
                text: data.message || (isEdit ? 'Address updated successfully.' : 'Address saved successfully.'),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (err) {
        console.error('Save address error:', err);
        const glob = document.getElementById('modal-global-error');
        if (glob) {
            glob.textContent = 'Network or server error. Please check your connection and try again.';
            glob.classList.remove('d-none');
        }
    } finally {
        submitBtn.disabled = false;
        saveIcon.className = isEdit ? 'bi bi-check2-circle' : 'bi bi-plus-circle';
        saveText.textContent = isEdit ? 'Update Address' : 'Save Address';
    }
}

// Handle Address Deletion with SweetAlert2 & Animated Removal
function deleteAddress(id, deleteUrl, btnElement) {
    Swal.fire({
        title: 'Delete Address?',
        text: 'Are you sure you want to delete this address?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) performDeleteAddress(id, deleteUrl, btnElement);
    });
}

function performDeleteAddress(id, deleteUrl, btnElement) {
    btnElement.disabled = true;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

    fetch(deleteUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-HTTP-Method-Override': 'DELETE',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.message || 'Failed to delete address.');
        return data;
    })
    .then(data => {
        const cardCol = document.getElementById('address-card-' + id);
        if (cardCol) {
            cardCol.style.transition = 'all 0.3s ease';
            cardCol.style.opacity   = '0';
            cardCol.style.transform = 'scale(0.9)';
            setTimeout(() => {
                cardCol.remove();
                const container = document.getElementById('addresses-container');
                if (container && container.querySelectorAll('[id^="address-card-"]').length === 0) {
                    container.style.display = 'none';
                    document.getElementById('addresses-empty-state').style.display = 'block';
                }
            }, 300);
        }
        Swal.fire({ 
            icon: 'success', 
            title: 'Deleted!', 
            text: data.message || 'Address deleted.', 
            toast: true, 
            position: 'top-end', 
            showConfirmButton: false, 
            timer: 3000, 
            timerProgressBar: true 
        });
    })
    .catch(err => {
        btnElement.disabled = false;
        btnElement.innerHTML = '<i class="bi bi-trash me-1"></i>Delete';
        Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to delete address.' });
    });
}
</script>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Done!',
        text: '{{ session('success') }}',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
});
</script>
@endif

@endpush

@endsection
