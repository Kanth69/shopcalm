<!-- ====================================================== -->
<!-- STEP 1 VIEW: DELIVERY ADDRESS & ESTIMATED DELIVERY     -->
<!-- ====================================================== -->
<div id="checkout-step-1-view">
    
    <!-- 1. Delivery Address Card -->
    <div class="checkout-card mb-3.5">
        <div class="checkout-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; background: #4f46e5; font-size: 0.9rem;">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.98rem;">Delivery Address</h6>
            </div>
            <button type="button" id="btn-saved-addresses-header" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" onclick="showAddressSelectorModal()" style="font-size: 0.78rem; display: {{ $addresses->count() > 0 ? 'inline-block' : 'none' }};">
                <i class="bi bi-geo-alt me-1"></i> <span id="saved-addresses-count-text">Saved ({{ $addresses->count() }})</span>
            </button>
        </div>

        <div class="checkout-card-body">
            <div id="address-error-banner" class="alert alert-danger small mb-3 rounded-3 border-0 bg-danger-subtle text-danger" style="display: none;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><span>Please select or add a delivery address to proceed.</span>
            </div>

            <div id="address-success-banner" class="alert alert-success small mb-3 rounded-3 border-0 bg-success-subtle text-success" style="display: none;">
                <i class="bi bi-check-circle-fill me-2"></i><span>Address saved successfully!</span>
            </div>

            <!-- Active Address Box -->
            <div id="active-address-display" class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                <div id="no-address-msg" style="display: {{ $addresses->count() > 0 ? 'none' : 'block' }};" class="text-center py-3 text-muted">
                    <i class="bi bi-building-add fs-2 text-primary d-block mb-1.5"></i>
                    <h6 class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">No saved address</h6>
                    <p class="small text-secondary mb-2.5" style="font-size: 0.8rem;">Add your delivery address to see estimated arrival dates.</p>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 fw-semibold" onclick="openCheckoutAddressModal()">
                        <i class="bi bi-plus-lg me-1"></i> Add Address
                    </button>
                </div>

                @php $activeAddr = $addresses->count() > 0 ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null; @endphp
                <div id="active-address-details" style="display: {{ $addresses->count() > 0 ? 'block' : 'none' }};">
                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2 pb-2 border-bottom border-slate-200">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <h6 class="fw-bold mb-0 text-dark text-truncate" style="font-size: 0.95rem;" id="display-name">{{ $activeAddr ? $activeAddr->name : '' }}</h6>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.68rem;">Default</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" id="btn-switch-address" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-semibold" onclick="showAddressSelectorModal()" style="font-size: 0.75rem; display: {{ $addresses->count() > 1 ? 'inline-flex' : 'none' }};">
                                <i class="bi bi-arrow-repeat me-1"></i> Switch
                            </button>
                            <button type="button" id="btn-edit-active-address" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 fw-semibold" onclick="editActiveCheckoutAddress()" style="font-size: 0.75rem;">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-secondary mb-1.5 small text-break-word" id="display-street-city" style="line-height: 1.5; font-size: 0.86rem;">
                        {{ $activeAddr ? "{$activeAddr->address}, {$activeAddr->city}, {$activeAddr->state}" : '' }} @if($activeAddr)<span class="text-nowrap font-monospace fw-bold text-dark">- {{ $activeAddr->zip }}</span>@endif
                    </p>
                    
                    <div class="small text-muted" id="display-phone" style="font-size: 0.82rem;">
                        <i class="bi bi-telephone text-muted me-1"></i> <strong class="text-dark font-monospace">{{ $activeAddr ? $activeAddr->phone : '' }}</strong>
                    </div>
                    <div id="active-address-serviceability-badge" class="mt-2 text-break-word"></div>
                </div>
            </div>

            <!-- Address Quick Action Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="openLocationPickerModal(true)" style="font-size: 0.8rem; background: #4f46e5; border-color: #4f46e5;">
                    <i class="bi bi-crosshair"></i>
                    <span>Use Current Location</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1" onclick="openCheckoutAddressModal()" style="font-size: 0.8rem;">
                    <i class="bi bi-plus-lg"></i>
                    <span>+ New Address</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Compact Estimated Arrival Badge -->
    <div class="checkout-card mb-3.5">
        <div class="checkout-card-body p-3">
            <div class="d-flex align-items-center gap-2.5">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 38px; height: 38px; background: rgba(79, 70, 229, 0.1);">
                    <i class="bi bi-truck fs-5"></i>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-0.5">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5 fw-bold" style="font-size: 0.68rem;">ESTIMATED ARRIVAL</span>
                        <span class="text-secondary small" id="delivery-location-text" style="font-size: 0.78rem;">
                            {{ $activeAddr ? "{$activeAddr->city} ({$activeAddr->zip})" : 'Calculating...' }}
                        </span>
                    </div>
                    <div class="fw-bold text-dark" id="delivery-estimated-date" style="font-size: 0.92rem;">
                        Standard Delivery by ShopCalm Logistics
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Delivery Preferences (Clean 2-in-a-row on Mobile, 4-inline on Desktop) -->
    <div class="checkout-card mb-3.5">
        <div class="checkout-card-body p-3 p-sm-3.5">
            <!-- Preferred Delivery Time (Single-Select Time Slot) -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label for="delivery_time_select" class="fw-bold text-dark d-flex align-items-center gap-1.5 mb-0" style="font-size: 0.88rem;">
                    <i class="bi bi-clock-history text-primary"></i>
                    <span>Preferred Delivery Time</span>
                </label>
                <span id="delivery-addr-badge" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;">
                    <i class="bi bi-house-door-fill me-1"></i> Home
                </span>
            </div>
            <div class="mb-3">
                <select id="delivery_time_select" class="form-select form-select-sm rounded-3 py-2 fw-semibold text-dark" style="font-size: 0.82rem; border-color: #cbd5e1;">
                    <option value="Anytime (7 AM - 9 PM)">Anytime (7 AM - 9 PM)</option>
                    <option value="Morning (7 AM - 12 PM)">Morning (7 AM - 12 PM)</option>
                    <option value="Evening (5 PM - 9 PM)">Evening (5 PM - 9 PM)</option>
                </select>
                <input type="hidden" name="preferred_time_slot" id="selected_time_slot" value="Anytime (7 AM - 9 PM)">
            </div>

            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <label class="fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 0.9rem;">
                    <i class="bi bi-card-checklist text-primary"></i>
                    <span>Delivery Instructions</span>
                    <span class="text-muted fw-normal small" style="font-size: 0.76rem;">(Optional)</span>
                </label>
            </div>

            <!-- 2-col on Mobile, 4-col on Desktop -->
            <div class="row g-2 mb-2" id="delivery-chips-group">
                <div class="col-6 col-md-3">
                    <button type="button" class="delivery-chip w-100 justify-content-center" data-pref="Leave at Door" onclick="toggleDeliveryChip(this)">
                        <i class="bi bi-door-open"></i> <span>Leave at Door</span>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="delivery-chip w-100 justify-content-center" data-pref="Don't Ring Bell" onclick="toggleDeliveryChip(this)">
                        <i class="bi bi-bell-slash"></i> <span>Don't Ring</span>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="delivery-chip w-100 justify-content-center" data-pref="Leave with Security" onclick="toggleDeliveryChip(this)">
                        <i class="bi bi-shield"></i> <span>With Guard</span>
                    </button>
                </div>
                <div class="col-6 col-md-3">
                    <button type="button" class="delivery-chip w-100 justify-content-center" data-pref="Call Before Delivery" onclick="toggleDeliveryChip(this)">
                        <i class="bi bi-telephone"></i> <span>Call Before</span>
                    </button>
                </div>
            </div>
            
            <div class="pt-1.5">
                <a href="javascript:void(0)" onclick="toggleCustomNotesInput()" class="text-decoration-none small text-primary fw-semibold d-inline-flex align-items-center" id="btn-toggle-custom-note" style="font-size: 0.8rem;">
                    <i class="bi bi-plus-circle me-1"></i> Add landmark instruction
                </a>
                <div id="custom-notes-wrapper" class="d-none mt-2">
                    <input type="text" class="form-control rounded-3" id="custom_notes_input" maxlength="150" placeholder="e.g. Near Watermark Tower, green gate..." style="font-size: 0.84rem; padding: 0.5rem 0.75rem;" oninput="updateNotesField()">
                </div>
            </div>
        </div>
    </div>
</div>
