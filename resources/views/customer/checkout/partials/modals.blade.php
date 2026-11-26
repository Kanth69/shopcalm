<!-- ============================================================== -->
<!-- INTERACTIVE MAP LOCATION PICKER MODAL (AMAZON / SWIGGY STYLE)  -->
<!-- ============================================================== -->
<div class="modal fade" id="locationPickerModal" tabindex="-1" aria-labelledby="locationPickerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-white py-2.5 px-3.5 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 34px; height: 34px; background: rgba(79, 70, 229, 0.1);">
                        <i class="bi bi-geo-alt-fill fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark" id="locationPickerModalLabel" style="font-size: 0.95rem;">Select Delivery Location on Map</h6>
                        <small class="text-muted" style="font-size: 0.72rem;">Drag the pin or search to set your exact doorstep</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-0">
                <!-- Map Container -->
                <div class="position-relative" style="background: #e2e8f0;">
                    <div id="delivery-map-container" style="min-height: 290px; height: 290px; width: 100%; z-index: 1; position: relative; background: #e2e8f0;"></div>

                    <!-- Location Permission / Fallback Alert -->
                    <div id="map-perm-hint" class="position-absolute top-0 start-0 end-0 m-2 alert alert-warning py-1.5 px-3 small d-none d-flex align-items-center justify-content-between shadow rounded-3 border-0" style="z-index: 1050; font-size: 0.74rem;">
                        <span><i class="bi bi-info-circle-fill me-1.5 text-warning"></i> GPS blocked. Centered via network area. Drag the pin or search above for your exact doorstep.</span>
                        <button type="button" class="btn-close ms-2" style="font-size: 0.65rem;" onclick="document.getElementById('map-perm-hint').classList.add('d-none')"></button>
                    </div>
                    
                    <!-- Search Location Floating Box -->
                    <div class="position-absolute top-0 start-0 end-0 p-2.5" style="z-index: 1000; pointer-events: none;">
                        <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border" style="pointer-events: auto;">
                            <span class="input-group-text bg-white border-0 text-muted ps-3 pe-1"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-0 py-2" id="map-search-input" placeholder="Search area, colony, or street in India..." style="font-size: 0.84rem;">
                            <button type="button" class="btn btn-white border-0 text-muted pe-3 d-none" id="btn-clear-map-search" onclick="clearMapSearch()"><i class="bi bi-x-circle-fill"></i></button>
                        </div>
                        <div id="map-search-results" class="list-group shadow-sm mt-1 d-none rounded-3 overflow-hidden" style="max-height: 180px; overflow-y: auto; pointer-events: auto; font-size: 0.8rem;"></div>
                    </div>

                    <!-- Floating Locate Me Button (Target GPS) -->
                    <button type="button" class="btn btn-light shadow rounded-circle d-flex align-items-center justify-content-center position-absolute bottom-0 end-0 m-3 border" id="btn-locate-me" onclick="recenterToGpsLocation()" title="Locate Me via GPS" style="width: 42px; height: 42px; z-index: 1000; color: #4f46e5;">
                        <i class="bi bi-crosshair fs-5"></i>
                    </button>
                    
                    <!-- Pin Tip Helper Banner -->
                    <div class="position-absolute bottom-0 start-0 m-2.5 px-2.5 py-1 rounded-pill bg-dark bg-opacity-75 text-white small" style="z-index: 1000; font-size: 0.72rem;">
                        <i class="bi bi-pin-map-fill me-1 text-warning"></i> Drag pin or click map to move
                    </div>
                </div>

                <!-- Detected Location Summary & Address Form -->
                <div class="p-3.5 p-md-4 bg-white">
                    
                    <!-- Detected Location Pill Bar -->
                    <div class="p-2.5 rounded-3 mb-3 d-flex align-items-start gap-2.5 border" id="map-detected-badge" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0 mt-0.5" style="width: 26px; height: 26px; background: rgba(79, 70, 229, 0.1);">
                            <i class="bi bi-geo-alt-fill" style="font-size: 0.85rem;"></i>
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <span class="fw-bold text-dark small" id="map-detected-area">Detecting location...</span>
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 font-monospace fw-bold" id="map-detected-zip" style="font-size: 0.72rem;">------</span>
                            </div>
                            <div class="text-secondary mt-0.5 text-truncate" id="map-detected-city-state" style="font-size: 0.76rem;">Please allow GPS or search your area.</div>
                        </div>
                    </div>

                    <!-- Quick Address Form (House No + Landmark + Contact) -->
                    <div class="row g-2.5">
                        <div class="col-12 col-sm-6">
                            <label for="map_house_no" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Flat / House / Building No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_house_no" placeholder="e.g. Flat 302, Sai Residency">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="map_landmark" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Landmark <span class="text-muted">(Optional)</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_landmark" placeholder="e.g. Near Apollo Pharmacy">
                        </div>
                        <div class="col-12">
                            <label for="map_road_area" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Road / Street / Colony <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_road_area" placeholder="Detected street or colony">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label for="map_city" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_city" placeholder="City" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label for="map_state" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_state" placeholder="State" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label for="map_zip_code" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold py-1.5" id="map_zip_code" placeholder="6-digit PIN" maxlength="6" inputmode="numeric" onkeypress="return event.charCode >= 48 && event.charCode <= 57" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 6)">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="map_name" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">Receiver Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="map_name" value="{{ auth()->user()->name ?? '' }}" placeholder="Name" autocomplete="name">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label for="map_phone" class="form-label fw-semibold small mb-1" style="font-size: 0.78rem;">10-Digit Mobile <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control form-control-sm rounded-3 py-1.5" id="map_phone" value="{{ auth()->user()->mobile_number ?? '' }}" placeholder="Mobile number" maxlength="10" inputmode="tel" autocomplete="tel" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)">
                        </div>
                        
                        <!-- Address Type (Home / Office / Other) -->
                        <div class="col-12 pt-1">
                            <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between" style="font-size: 0.8rem;">
                                <span><i class="bi bi-tag-fill text-primary me-1"></i> Save Address As</span>
                                <span class="text-muted fw-normal" style="font-size: 0.72rem;">Delivery Type</span>
                            </label>
                            <div class="btn-group w-100 checkout-addr-type-group" role="group" aria-label="Address Type">
                                <input type="radio" class="btn-check" name="map_address_type" id="map_type_home" value="home" checked autocomplete="off">
                                <label class="btn" for="map_type_home">
                                    <i class="bi bi-house-door-fill text-primary"></i> <span>Home</span>
                                </label>

                                <input type="radio" class="btn-check" name="map_address_type" id="map_type_work" value="work" autocomplete="off">
                                <label class="btn" for="map_type_work">
                                    <i class="bi bi-briefcase-fill text-warning"></i> <span>Office</span>
                                </label>

                                <input type="radio" class="btn-check" name="map_address_type" id="map_type_other" value="other" autocomplete="off">
                                <label class="btn" for="map_type_other">
                                    <i class="bi bi-geo-alt-fill text-success"></i> <span>Other</span>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-3.5 px-md-4 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-light border rounded-pill px-3.5 py-1.5 btn-sm fw-semibold" data-bs-dismiss="modal" style="font-size: 0.8rem;">Cancel</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 btn-sm fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="btn-save-map-address" onclick="saveMapAddressToCheckout()" style="background: #4f46e5; border: none; font-size: 0.84rem;">
                    <i class="bi bi-check2-circle fs-6"></i>
                    <span>Confirm Location &amp; Deliver Here</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Available Coupons Modal -->
<div class="modal fade" id="couponsModal" tabindex="-1" aria-labelledby="couponsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white py-3 px-3.5 px-md-4 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <h5 class="modal-title fw-bolder text-dark mb-0" id="couponsModalLabel" style="font-size: 1rem;">Available Coupons & Offers</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-3.5 bg-light">
                @if(isset($availableCoupons) && $availableCoupons->count() > 0)
                    @php
                        $eligibleCoupons = $availableCoupons->where('is_eligible', true);
                        $ineligibleCoupons = $availableCoupons->where('is_eligible', false);
                    @endphp

                    <!-- Eligible Coupons Section -->
                    @if($eligibleCoupons->count() > 0)
                        <div class="mb-3">
                            <span class="fw-bold text-dark small mb-2 d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Applicable to Your Order ({{ $eligibleCoupons->count() }})</span>
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($eligibleCoupons as $c)
                                    @php $discType = is_object($c->discount_type) ? $c->discount_type->value : (string)$c->discount_type; @endphp
                                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white" style="border: 1.5px dashed #3b82f6 !important;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <span class="font-monospace fw-bold px-2.5 py-1 rounded" style="background: #eff6ff; color: #1d4ed8; font-size: 0.85rem;">{{ $c->code }}</span>
                                                <span class="badge bg-success bg-opacity-10 text-success ms-2 fw-bold" style="font-size: 0.68rem;">Eligible</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" onclick="applyCouponFromModal('{{ $c->code }}')" style="font-size: 0.76rem; background: #4f46e5; border: none;">APPLY</button>
                                        </div>
                                        <div class="fw-bold text-success mt-1" style="font-size: 0.9rem;">
                                            @if($discType === 'PERCENTAGE') Save {{ round($c->discount_value) }}% @else Save ₹{{ number_format($c->discount_value) }} FLAT @endif
                                        </div>
                                        @if($c->description)<p class="text-secondary small mb-0 mt-0.5" style="font-size: 0.78rem;">{{ $c->description }}</p>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Ineligible Coupons Section -->
                    @if($ineligibleCoupons->count() > 0)
                        <div>
                            <span class="fw-bold text-muted small mb-2 d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Other Store Coupons ({{ $ineligibleCoupons->count() }})</span>
                            <div class="d-flex flex-column gap-2.5">
                                @foreach($ineligibleCoupons as $c)
                                    @php $discType = is_object($c->discount_type) ? $c->discount_type->value : (string)$c->discount_type; @endphp
                                    <div class="card border-0 shadow-xs rounded-3 p-3 bg-white" style="border: 1px dashed #cbd5e1 !important; opacity: 0.85;">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="font-monospace fw-bold px-2.5 py-1 rounded text-secondary" style="background: #f1f5f9; font-size: 0.85rem;">{{ $c->code }}</span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold" style="font-size: 0.68rem;">Not Applicable</span>
                                        </div>
                                        <div class="fw-semibold text-secondary mt-1" style="font-size: 0.85rem;">
                                            @if($discType === 'PERCENTAGE') {{ round($c->discount_value) }}% OFF @else ₹{{ number_format($c->discount_value) }} FLAT OFF @endif
                                        </div>
                                        @if($c->ineligibility_reason)
                                            <div class="text-danger small mt-1" style="font-size: 0.76rem;">
                                                <i class="bi bi-info-circle me-1"></i> {{ $c->ineligibility_reason }}
                                            </div>
                                        @endif
                                        @if($c->description)<p class="text-muted small mb-0 mt-0.5" style="font-size: 0.76rem;">{{ $c->description }}</p>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-ticket-perforated fs-1 d-block mb-2 text-secondary"></i>
                        <p class="mb-0 small fw-semibold">No coupons available at the moment.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-white py-2 px-3 border-top">
                <button type="button" class="btn btn-light rounded-pill px-3.5 btn-sm fw-semibold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for selecting/changing saved address -->
<div class="modal fade" id="addressSelectorModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-3.5 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 38px; height: 38px; background: rgba(79, 70, 229, 0.1);">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="modal-title fw-bold mb-0 text-dark" id="addressModalLabel" style="font-size: 1.05rem;">Select Saved Address</h5>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-semibold d-inline-flex align-items-center gap-1 ms-1 shadow-xs" onclick="const m = bootstrap.Modal.getInstance(document.getElementById('addressSelectorModal')); if (m) m.hide(); openLocationPickerModal(true);" style="font-size: 0.74rem; background: #4f46e5; border-color: #4f46e5;">
                                    <i class="bi bi-crosshair"></i> Use Current Location
                                </button>
                            </div>
                        <small class="text-secondary" style="font-size: 0.78rem;">Choose an address for your order delivery</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5 p-md-4">
                <div class="row g-3" id="modal-address-list">
                    @foreach($addresses as $addr)
                    @php
                        $isCurrent = (isset($activeAddr) && $activeAddr->id === $addr->id);
                    @endphp
                    <div class="col-12">
                        <div class="card border rounded-4 p-3 p-sm-3.5 address-modal-option cursor-pointer position-relative shadow-xs {{ $isCurrent ? 'is-selected' : '' }}" onclick="selectModalAddress({{ json_encode($addr) }})">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2.5">
                                <div class="min-w-0 flex-grow-1 w-100">
                                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                        <strong class="text-dark fs-6">{{ $addr->name }}</strong>
                                        @if(!empty($addr->type))
                                            <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5 text-uppercase fw-semibold" style="font-size: 0.65rem;">{{ $addr->type }}</span>
                                        @endif
                                        @if($isCurrent)
                                            <span class="badge bg-success text-white rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 0.68rem;"><i class="bi bi-check-lg me-1"></i>Selected</span>
                                        @elseif($addr->is_default)
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">Default</span>
                                        @endif
                                    </div>
                                    <div class="text-secondary mb-1.5" style="font-size: 0.86rem; line-height: 1.45;">
                                        @if(!empty($addr->house_no))<strong>{{ $addr->house_no }}</strong>, @endif{{ $addr->street ?? $addr->address }}, {{ $addr->city }}, {{ $addr->state }} <span class="text-nowrap font-monospace text-dark fw-bold">- {{ $addr->zip }}</span>
                                        @if(!empty($addr->landmark))
                                            <span class="text-muted small d-block mt-0.5"><i class="bi bi-geo-alt"></i> Landmark: {{ $addr->landmark }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted text-nowrap" style="font-size: 0.82rem; white-space: nowrap !important;">
                                        <i class="bi bi-telephone-fill text-muted me-1 small"></i> Mobile: <strong class="text-dark font-monospace">{{ $addr->phone }}</strong>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 align-self-end align-self-sm-center d-flex align-items-center gap-1.5 mt-2 mt-sm-0">
                                    @if($isCurrent)
                                        <span class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-xs text-nowrap" style="font-size: 0.78rem; pointer-events: none;">
                                            <i class="bi bi-check2"></i> Selected
                                        </span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap" style="font-size: 0.78rem;">
                                            Select
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="editCheckoutAddress(event, {{ $addr->id }})" title="Edit Address">
                                        <i class="bi bi-pencil small"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" onclick="deleteCheckoutAddress(event, {{ $addr->id }}, this)" title="Delete Address">
                                        <i class="bi bi-trash small"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-3.5 px-md-4 d-flex justify-content-between align-items-center" style="position: sticky; bottom: 0; z-index: 10; background: #ffffff !important;">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="openNewAddressFromModal()" style="background: #4f46e5; border: none; font-size: 0.82rem;">
                    <i class="bi bi-plus-lg"></i> Add New Address
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.82rem;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modern Popup Modal for Adding & Editing Address at Checkout (Mobile Scroll & Sticky Save Button) -->
<div class="modal fade" id="checkoutAddressModal" tabindex="-1" aria-labelledby="checkoutAddressModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content border-0 shadow" id="checkoutAddressModalForm" onsubmit="handleSaveAddress(event)" novalidate>
            @csrf
            <input type="hidden" id="edit_address_id" value="">

            {{-- Modal Header --}}
            <div class="modal-header border-bottom py-3 px-3.5 px-sm-4 bg-light bg-opacity-75 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" id="checkoutAddressModalIconBox"
                         style="width: 38px; height: 38px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); color: #ffffff;">
                        <i class="bi bi-house-add-fill fs-5" id="checkoutAddressModalIcon"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="checkoutAddressModalLabel" style="font-size: 1.02rem;">Add New Address</h5>
                        <p class="text-muted small mb-0" id="checkoutAddressModalSubtitle" style="font-size: 0.74rem;">Enter your delivery destination details</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link text-primary p-0 fw-semibold text-decoration-none d-none d-sm-inline" onclick="openLocationPickerFromAddressModal()" style="font-size: 0.76rem;">
                        <i class="bi bi-map-fill me-1"></i>Select on Map
                    </button>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-3.5 p-sm-4">
                <!-- GPS & Map Quick Actions Banner -->
                <div class="d-flex align-items-center justify-content-between p-2.5 mb-3 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-success fs-5"></i>
                        <div>
                            <div class="fw-semibold text-dark small" style="font-size: 0.78rem;">Save time with GPS</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Auto-fill area, city & PIN code</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-semibold text-nowrap" id="btn-checkout-modal-gps" onclick="fetchCheckoutModalGpsLocation()" style="font-size: 0.74rem;">
                            <i class="bi bi-crosshair me-1"></i>Auto-Fill
                        </button>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-primary fw-semibold text-nowrap" onclick="openLocationPickerFromAddressModal()" style="font-size: 0.74rem;">
                            <i class="bi bi-map-fill me-1"></i>Map
                        </button>
                    </div>
                </div>

                <!-- Address Type Selector (Home / Office / Other) -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center justify-content-between" style="font-size: 0.8rem;">
                        <span><i class="bi bi-tag-fill text-primary me-1"></i> Save Address As</span>
                        <span class="text-muted fw-normal" style="font-size: 0.72rem;">Delivery Type</span>
                    </label>
                    <div class="btn-group w-100 checkout-addr-type-group" role="group" aria-label="Address Type">
                        <input type="radio" class="btn-check" name="new_type" id="checkout_addr_type_home" value="home" checked autocomplete="off">
                        <label class="btn" for="checkout_addr_type_home">
                            <i class="bi bi-house-door-fill text-primary"></i> <span>Home</span>
                        </label>

                        <input type="radio" class="btn-check" name="new_type" id="checkout_addr_type_work" value="work" autocomplete="off">
                        <label class="btn" for="checkout_addr_type_work">
                            <i class="bi bi-briefcase-fill text-warning"></i> <span>Office</span>
                        </label>

                        <input type="radio" class="btn-check" name="new_type" id="checkout_addr_type_other" value="other" autocomplete="off">
                        <label class="btn" for="checkout_addr_type_other">
                            <i class="bi bi-geo-alt-fill text-success"></i> <span>Other</span>
                        </label>
                    </div>
                </div>

                <div class="row g-2.5">
                    <div class="col-12 col-sm-6">
                        <label for="new_name" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_name" value="{{ auth()->user()->name ?? '' }}" placeholder="Enter receiver's name" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="new_phone" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control form-control-sm rounded-3 py-1.5" id="new_phone" value="{{ auth()->user()->mobile_number ?? '' }}" placeholder="10-digit mobile" maxlength="10" inputmode="tel" autocomplete="tel" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)" required>
                    </div>

                    {{-- Structured Address: House/Flat (User Input) --}}
                    <div class="col-12">
                        <label for="new_house_no" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Flat / House No. / Building / Floor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_house_no" placeholder="e.g. Flat 302, Sai Nilayam, 3rd Floor" required>
                    </div>

                    {{-- Structured Address: Street / Road / Colony (Auto-filled by GPS/Map or User) --}}
                    <div class="col-12">
                        <label for="new_street" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Street / Road / Colony / Area <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_street" placeholder="e.g. 4th Cross, 100ft Ring Road, Indiranagar" required>
                    </div>

                    {{-- Structured Address: Landmark (Optional) --}}
                    <div class="col-12">
                        <label for="new_landmark" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Landmark <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 py-1.5" id="new_landmark" placeholder="e.g. Near Apollo Pharmacy, Behind City Hospital">
                    </div>

                    {{-- Pincode --}}
                    <div class="col-12 col-sm-4">
                        <label for="new_zip" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">Pincode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold py-1.5" id="new_zip" 
                               placeholder="6-digit PIN" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="postal-code"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57" 
                               oninput="handleCheckoutPincodeInput(this)" required>
                        <small id="new_zip_lookup_hint" class="d-block mt-0.5 transition-all" style="font-size: 0.72rem; min-height: 16px;"></small>
                    </div>

                    {{-- City --}}
                    <div class="col-12 col-sm-4">
                        <label for="new_city" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">City / District <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5 ps-3 pe-4" id="new_city" 
                                   placeholder="City"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" required>
                            <i class="bi bi-lock-fill position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted d-none" 
                               id="new_city_lock_icon" title="Auto-filled from PIN" style="pointer-events: none; font-size: 0.82rem;"></i>
                        </div>
                    </div>

                    {{-- State --}}
                    <div class="col-12 col-sm-4">
                        <label for="new_state" class="form-label fw-semibold small mb-1" style="font-size: 0.8rem;">State <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control form-control-sm rounded-3 py-1.5 ps-3 pe-4" id="new_state" 
                                   placeholder="State"
                                   oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" required>
                            <i class="bi bi-lock-fill position-absolute top-50 end-0 translate-middle-y me-2.5 text-muted d-none" 
                               id="new_state_lock_icon" title="Auto-filled from PIN" style="pointer-events: none; font-size: 0.82rem;"></i>
                        </div>
                    </div>

                    <input type="hidden" id="new_latitude" value="">
                    <input type="hidden" id="new_longitude" value="">
                    <input type="hidden" id="new_address" value="">
                </div>
            </div>

            {{-- Modal Sticky Footer --}}
            <div class="modal-footer py-2.5 px-3.5 px-sm-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3.5 py-1.5 fw-semibold" data-bs-dismiss="modal" style="font-size: 0.82rem;">Cancel</button>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 py-2 shadow-xs fw-semibold" id="btn-save-address" disabled style="background: #4f46e5; border: none; opacity: 0.6; cursor: not-allowed; font-size: 0.82rem;">
                    <i class="bi bi-bookmark-check me-1"></i> Save Address
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>


