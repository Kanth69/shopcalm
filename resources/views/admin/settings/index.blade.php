@extends('admin.layouts.app')

@section('header', 'System & Store Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Store Settings</li>
@endsection

@section('actions')
    <button type="submit" form="settingsForm" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
        <i class="bi bi-check-circle me-1"></i> Save Changes
    </button>
@endsection

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
    @csrf
    @method('PATCH')

    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <!-- 1. General Store Identity -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-shop text-primary me-2"></i>1. Store & Business Identity
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Store / Brand Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-building"></i></span>
                                <input type="text" name="store_name" class="form-control" value="{{ $settings['store_name'] ?? 'Shopcalm' }}" required placeholder="e.g. Shopcalm">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Official Support Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? 'support@shopcalm.com' }}" required placeholder="support@shopcalm.com">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Customer Support Phone</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+91 98765 43210">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Store Currency & Symbol</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">ISO</span>
                                <input type="text" name="currency" class="form-control font-monospace" placeholder="INR" value="{{ $settings['currency'] ?? 'INR' }}">
                                <span class="input-group-text bg-light text-muted">Symbol</span>
                                <input type="text" name="currency_symbol" class="form-control font-monospace" placeholder="₹" value="{{ $settings['currency_symbol'] ?? '₹' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">WhatsApp Support Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="+91 98765 43210">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Enables 1-click WhatsApp customer support chat.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Support Working Hours</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-clock"></i></span>
                                <input type="text" name="support_hours" class="form-control" value="{{ $settings['support_hours'] ?? 'Mon - Sat: 9:00 AM - 8:00 PM' }}" placeholder="e.g. Mon - Sat: 9:00 AM - 8:00 PM">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Store Tagline / Footer Summary</label>
                            <input type="text" name="tagline" class="form-control" value="{{ $settings['tagline'] ?? 'Your one-stop shop for everything you need. Quality products, unbeatable prices.' }}" placeholder="Store tagline...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Footer Copyright Text</label>
                            <input type="text" name="copyright_text" class="form-control" value="{{ $settings['copyright_text'] ?? ('© ' . date('Y') . ' ShopCalm. All rights reserved.') }}" placeholder="© 2026 ShopCalm. All rights reserved.">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Registered Business Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Physical office or fulfillment hub address...">{{ $settings['address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 2. Social Media Profiles & Links -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-share-fill text-primary me-2"></i>2. Official Social Media Channels
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-facebook text-primary me-1"></i>Facebook Page URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/yourbrand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-instagram text-danger me-1"></i>Instagram Profile URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/yourbrand">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-twitter-x text-dark me-1"></i>Twitter / X Profile URL</label>
                            <input type="url" name="twitter_url" class="form-control" value="{{ $settings['twitter_url'] ?? '' }}" placeholder="https://x.com/yourbrand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-linkedin text-info me-1"></i>LinkedIn Page URL</label>
                            <input type="url" name="linkedin_url" class="form-control" value="{{ $settings['linkedin_url'] ?? '' }}" placeholder="https://linkedin.com/company/yourbrand">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-youtube text-danger me-1"></i>YouTube Channel URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="{{ $settings['youtube_url'] ?? '' }}" placeholder="https://youtube.com/@yourbrand">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Storefront Branding -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-palette-fill text-primary me-2"></i>3. Visual Branding & Icons
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Logo -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Main Storefront Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewLogo(this)">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Transparent PNG or SVG recommended.</div>

                            <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                                @if(isset($settings['logo']))
                                    <img id="logoPreviewImg" src="{{ asset('storage/' . $settings['logo']) }}" class="img-fluid" style="max-height: 50px; object-fit: contain;">
                                @else
                                    <img id="logoPreviewImg" src="#" class="img-fluid d-none" style="max-height: 50px; object-fit: contain;">
                                    <div id="logoPlaceholder" class="text-muted small">No custom logo uploaded</div>
                                @endif
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Browser Tab Favicon</label>
                            <input type="file" name="favicon" class="form-control" accept="image/*" onchange="previewFavicon(this)">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Square 32×32 or 64×64 PNG/ICO image.</div>

                            <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                                @if(isset($settings['favicon']))
                                    <img id="faviconPreviewImg" src="{{ asset('storage/' . $settings['favicon']) }}" class="img-fluid" style="max-height: 32px; object-fit: contain;">
                                @else
                                    <img id="faviconPreviewImg" src="#" class="img-fluid d-none" style="max-height: 32px; object-fit: contain;">
                                    <div id="faviconPlaceholder" class="text-muted small">No custom favicon uploaded</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Trust Badges & Guarantees -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-patch-check-fill text-primary me-2"></i>4. Homepage Trust Badges & Promises
                    </h6>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="enable_trust_badges" value="0">
                        <input class="form-check-input" type="checkbox" name="enable_trust_badges" value="1" id="enable_trust_badges" {{ ($settings['enable_trust_badges'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label small fw-bold text-muted" for="enable_trust_badges">Show Badges</label>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-truck text-primary me-1"></i>Badge 1: Free Delivery Title</label>
                            <input type="text" name="trust_badge_1_title" class="form-control" value="{{ $settings['trust_badge_1_title'] ?? 'Free Delivery' }}" placeholder="Free Delivery">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Badge 1: Description / Subtext</label>
                            <input type="text" name="trust_badge_1_desc" class="form-control" value="{{ $settings['trust_badge_1_desc'] ?? ('Orders ₹' . ($settings['free_shipping_min'] ?? '499') . '+') }}" placeholder="Orders ₹499+">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-shield-check text-success me-1"></i>Badge 2: Secure Payment Title</label>
                            <input type="text" name="trust_badge_2_title" class="form-control" value="{{ $settings['trust_badge_2_title'] ?? 'Secure Checkout' }}" placeholder="Secure Checkout">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Badge 2: Description / Subtext</label>
                            <input type="text" name="trust_badge_2_desc" class="form-control" value="{{ $settings['trust_badge_2_desc'] ?? '100% Encrypted' }}" placeholder="100% Encrypted">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-patch-check text-warning me-1"></i>Badge 3: Authenticity Title</label>
                            <input type="text" name="trust_badge_3_title" class="form-control" value="{{ $settings['trust_badge_3_title'] ?? '100% Genuine' }}" placeholder="100% Genuine">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Badge 3: Description / Subtext</label>
                            <input type="text" name="trust_badge_3_desc" class="form-control" value="{{ $settings['trust_badge_3_desc'] ?? 'Direct From Brands' }}" placeholder="Direct From Brands">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-headset text-info me-1"></i>Badge 4: Support Title</label>
                            <input type="text" name="trust_badge_4_title" class="form-control" value="{{ $settings['trust_badge_4_title'] ?? 'Fast Support' }}" placeholder="Fast Support">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Badge 4: Description / Subtext</label>
                            <input type="text" name="trust_badge_4_desc" class="form-control" value="{{ $settings['trust_badge_4_desc'] ?? 'Helpdesk Available' }}" placeholder="Helpdesk Available">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-truck text-primary me-1"></i>Free Shipping Minimum Spend (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="free_shipping_min" class="form-control fw-bold" value="{{ $settings['free_shipping_min'] ?? '499' }}" placeholder="499">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Orders above this spend qualify for free delivery across the store.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small"><i class="bi bi-cash-stack text-success me-1"></i>Default COD Handling Fee (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" step="0.01" name="cod_flat_fee" class="form-control fw-bold" value="{{ $settings['cod_flat_fee'] ?? '40.00' }}" placeholder="40.00">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Applied to COD orders when no custom pincode fee is set.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark small"><i class="bi bi-toggle-on text-primary me-1"></i>Enable Cash on Delivery (COD) Handling Fees</div>
                                    <div class="text-muted" style="font-size: 0.72rem;">When enabled, customers selecting COD pay the handling fee unless prepaid online.</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input type="hidden" name="cod_fee_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" name="cod_fee_enabled" value="1" id="cod_fee_enabled" {{ ($settings['cod_fee_enabled'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Customer Order Cancellation Controls -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-x-circle-fill text-danger me-2"></i>5. Customer Self-Service Cancellation Control
                    </h6>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="allow_customer_cancellation" value="0">
                        <input class="form-check-input" type="checkbox" name="allow_customer_cancellation" value="1" id="allow_customer_cancellation" {{ ($settings['allow_customer_cancellation'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.5em;">
                        <label class="form-check-label small fw-bold text-muted" for="allow_customer_cancellation">Enabled</label>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 rounded-3 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <div class="fw-bold text-dark small">
                                <i class="bi bi-person-x text-danger me-1"></i>Allow Customers to Cancel Unshipped Orders
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">
                                When <strong>ON / YES</strong>: Customers can see and use the "Cancel Order" button for pending/confirmed orders.<br>
                                When <strong>OFF / NO</strong>: Customer self-cancellation is disabled. Only store staff/admin can cancel orders.
                            </div>
                        </div>
                        <div class="badge {{ ($settings['allow_customer_cancellation'] ?? '1') == '1' ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill fs-7">
                            {{ ($settings['allow_customer_cancellation'] ?? '1') == '1' ? 'Allowed (YES)' : 'Disabled (NO)' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="d-grid gap-2 mb-4">
                <button type="submit" class="btn btn-primary rounded-pill btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save All Settings
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logoPreviewImg');
            const placeholder = document.getElementById('logoPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('faviconPreviewImg');
            const placeholder = document.getElementById('faviconPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
