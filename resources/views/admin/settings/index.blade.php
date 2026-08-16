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

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small">Registered Business Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Physical office or fulfillment hub address...">{{ $settings['address'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 2. Storefront Branding -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-palette-fill text-primary me-2"></i>2. Visual Branding & Icons
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

            <!-- 3. Storefront Features & Commerce Rules -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-sliders text-primary me-2"></i>3. Storefront Features & Marketing Control
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Trust Badges Strip (Footer/Header)</label>
                            <select name="enable_trust_badges" class="form-select">
                                <option value="1" {{ ($settings['enable_trust_badges'] ?? '1') == '1' ? 'selected' : '' }}>Enabled (Show Quality & Fast Dispatch Badges)</option>
                                <option value="0" {{ ($settings['enable_trust_badges'] ?? '1') == '0' ? 'selected' : '' }}>Disabled (Hidden)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Free Shipping Cart Minimum</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">₹</span>
                                <input type="number" name="free_shipping_min" class="form-control fw-bold" value="{{ $settings['free_shipping_min'] ?? '499' }}" placeholder="499">
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Orders above this spend qualify for free delivery.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Flash Lightning Deals Ribbon</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Section Visibility</label>
                            <select name="enable_flash_sale" class="form-select">
                                <option value="1" {{ ($settings['enable_flash_sale'] ?? '1') == '1' ? 'selected' : '' }}>Active on Storefront</option>
                                <option value="0" {{ ($settings['enable_flash_sale'] ?? '1') == '0' ? 'selected' : '' }}>Disabled / Hidden</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Ribbon Display Title</label>
                            <input type="text" name="flash_sale_title" class="form-control" value="{{ $settings['flash_sale_title'] ?? '⚡ Limited Time Deals' }}" placeholder="⚡ Limited Time Deals">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Countdown Timer Expiry</label>
                            <input type="datetime-local" name="flash_sale_end_time" class="form-control" value="{{ $settings['flash_sale_end_time'] ?? date('Y-m-d\TH:i', strtotime('+3 days')) }}">
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
