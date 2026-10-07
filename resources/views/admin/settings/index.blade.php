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

            <!-- WhatsApp Cloud API Configuration -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-whatsapp text-success me-2"></i>Meta WhatsApp Business API
                    </h6>
                    <a href="{{ route('admin.integrations.whatsapp') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
                        Open Full WhatsApp Hub <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">WhatsApp Business Phone Number ID <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone-inbound"></i></span>
                                <input type="text" name="whatsapp_phone_number_id" class="form-control font-monospace" value="{{ $settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID', '') }}" placeholder="e.g. 104829104820194">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-bold text-dark small mb-0">Permanent Access Token <span class="text-danger">*</span></label>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    <i class="bi bi-shield-lock-fill me-1"></i>Stored Encrypted in DB
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="whatsapp_api_token" id="whatsapp_api_token_settings" class="form-control font-monospace" value="{{ $settings['whatsapp_api_token'] ?? env('WHATSAPP_API_TOKEN', '') }}" placeholder="EAAG.....">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('whatsapp_api_token_settings', this)" title="Toggle view token"><i class="bi bi-eye"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="copyCredential('whatsapp_api_token_settings', this)" title="Copy token"><i class="bi bi-clipboard"></i></button>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Click eye to reveal. Safely stored encrypted in database.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Brevo Transactional Email API -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-envelope-paper-fill me-2" style="color: #4f46e5;"></i>Brevo Transactional Email API
                    </h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            ⚡ Credits: {{ $brevoQuota['credits_label'] ?? '0' }}
                        </span>
                        <a href="{{ route('admin.integrations.brevo') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.72rem;">
                            Open Brevo Email Hub <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-bold text-dark small mb-0">Brevo v3 REST API Key <span class="text-danger">*</span></label>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    <i class="bi bi-shield-lock-fill me-1"></i>Stored Encrypted in DB
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="brevo_api_key" id="brevo_api_key_settings" class="form-control font-monospace" value="{{ $settings['brevo_api_key'] ?? env('BREVO_API_KEY', '') }}" placeholder="xkeysib-...">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('brevo_api_key_settings', this)" title="Toggle view key"><i class="bi bi-eye"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="copyCredential('brevo_api_key_settings', this)" title="Copy key"><i class="bi bi-clipboard"></i></button>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Click eye to reveal. Safely stored encrypted in database.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Default Sender Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope-check"></i></span>
                                <input type="email" name="brevo_sender_email" class="form-control" value="{{ $settings['brevo_sender_email'] ?? (config('mail.from.address') ?: 'support@shopcalm.in') }}" placeholder="support@shopcalm.in">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Razorpay Payment Gateway Configuration -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-credit-card-2-front-fill text-primary me-2"></i>Razorpay Payment Gateway
                    </h6>
                    <a href="{{ route('admin.integrations.razorpay') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 fw-bold text-dark" style="font-size: 0.72rem;">
                        Open Razorpay Hub <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Razorpay Key ID <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="razorpay_key_id" id="razorpay_key_id_settings" class="form-control font-monospace" value="{{ $settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', 'rzp_test_TiwC0gVacieKkn') }}" placeholder="rzp_test_...">
                                <button type="button" class="btn btn-outline-secondary" onclick="copyCredential('razorpay_key_id_settings', this)" title="Copy Key ID"><i class="bi bi-clipboard"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-bold text-dark small mb-0">Razorpay Key Secret <span class="text-danger">*</span></label>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                    <i class="bi bi-shield-lock-fill me-1"></i>Stored Encrypted in DB
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="razorpay_key_secret" id="razorpay_key_secret_settings" class="form-control font-monospace" value="{{ $settings['razorpay_key_secret'] ?? env('RAZORPAY_KEY_SECRET', 'nuKs1b9OeDT5p0Jxr4pUKcUR') }}" placeholder="Secret key...">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('razorpay_key_secret_settings', this)" title="Toggle view secret"><i class="bi bi-eye"></i></button>
                                <button type="button" class="btn btn-outline-secondary" onclick="copyCredential('razorpay_key_secret_settings', this)" title="Copy secret"><i class="bi bi-clipboard"></i></button>
                            </div>
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Click eye to reveal. Safely stored encrypted in database.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Storefront Branding -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-palette-fill text-primary me-2"></i>3. Visual Branding & Icons
                    </h6>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload & Save Branding
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Logo -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Main Storefront Logo</label>
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*,.svg,.webp,.ico" onchange="previewLogo(this)">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">PNG, JPG, WEBP, or SVG recommended (Max 10 MB).</div>
                            @error('logo')
                                <div class="invalid-feedback d-block fw-semibold mt-1">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div id="logoClientFeedback" class="small mt-1 d-none"></div>

                            <div class="mt-3 p-3 bg-light rounded-3 border text-center">
                                @if(!empty($settings['logo']))
                                    <img id="logoPreviewImg" src="{{ asset('storage/' . $settings['logo']) }}?v={{ @filemtime(storage_path('app/public/' . $settings['logo'])) ?: time() }}" class="img-fluid" style="max-height: 56px; object-fit: contain;">
                                @else
                                    <img id="logoPreviewImg" src="#" class="img-fluid d-none" style="max-height: 56px; object-fit: contain;">
                                    <div id="logoPlaceholder" class="text-muted small">No custom logo uploaded</div>
                                @endif
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Browser Tab Favicon</label>
                            <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept="image/*,.ico,.png,.jpg,.jpeg,.webp,.svg" onchange="previewFavicon(this)">
                            <div class="form-text text-muted" style="font-size: 0.72rem;">Supports PNG, JPG, ICO, WEBP, or SVG image (Max 5 MB).</div>
                            @error('favicon')
                                <div class="invalid-feedback d-block fw-semibold mt-1">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div id="faviconClientFeedback" class="small mt-1 d-none"></div>

                            <div class="mt-3 p-3 bg-light rounded-3 border text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 82px;">
                                @if(!empty($settings['favicon']))
                                    <img id="faviconPreviewImg" src="{{ asset('storage/' . $settings['favicon']) }}?v={{ @filemtime(storage_path('app/public/' . $settings['favicon'])) ?: time() }}" class="img-fluid rounded" style="max-height: 44px; object-fit: contain;">
                                    <span id="faviconCurrentLabel" class="text-success fw-semibold mt-1" style="font-size: 0.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Active Browser Tab Icon</span>
                                @else
                                    <img id="faviconPreviewImg" src="#" class="img-fluid d-none rounded" style="max-height: 44px; object-fit: contain;">
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
function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
}

function previewLogo(input) {
    const feedback = document.getElementById('logoClientFeedback');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxBytes = 10 * 1024 * 1024; // 10 MB
        const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'bmp', 'jfif', 'avif'];
        const ext = (file.name.split('.').pop() || '').toLowerCase();

        if (!allowedExt.includes(ext)) {
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.className = 'small mt-1 text-danger fw-semibold';
                feedback.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-1"></i>Unsupported file format (.${ext}). Allowed: PNG, JPG, WEBP, SVG, GIF, ICO.`;
            }
            input.value = '';
            return;
        }
        if (file.size > maxBytes) {
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.className = 'small mt-1 text-danger fw-semibold';
                feedback.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-1"></i>File is too large (${formatBytes(file.size)}). Maximum allowed size for Logo is 10 MB.`;
            }
            input.value = '';
            return;
        }

        input.classList.remove('is-invalid');
        if (feedback) {
            feedback.className = 'small mt-1 text-success fw-semibold';
            feedback.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Ready to upload: ${file.name} (${formatBytes(file.size)}) — Click "Upload & Save Branding" to apply.`;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('logoPreviewImg');
            const placeholder = document.getElementById('logoPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
        };
        reader.readAsDataURL(file);
    }
}

function previewFavicon(input) {
    const feedback = document.getElementById('faviconClientFeedback');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const maxBytes = 5 * 1024 * 1024; // 5 MB
        const allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'bmp', 'jfif', 'avif'];
        const ext = (file.name.split('.').pop() || '').toLowerCase();

        if (!allowedExt.includes(ext)) {
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.className = 'small mt-1 text-danger fw-semibold';
                feedback.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-1"></i>Unsupported file format (.${ext}). Allowed: PNG, JPG, ICO, WEBP, SVG, GIF.`;
            }
            input.value = '';
            return;
        }
        if (file.size > maxBytes) {
            input.classList.add('is-invalid');
            if (feedback) {
                feedback.className = 'small mt-1 text-danger fw-semibold';
                feedback.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-1"></i>File is too large (${formatBytes(file.size)}). Maximum allowed size for Favicon is 5 MB.`;
            }
            input.value = '';
            return;
        }

        input.classList.remove('is-invalid');
        if (feedback) {
            feedback.className = 'small mt-1 text-success fw-semibold';
            feedback.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Ready to upload: ${file.name} (${formatBytes(file.size)}) — Click "Upload & Save Branding" to apply.`;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('faviconPreviewImg');
            const placeholder = document.getElementById('faviconPlaceholder');
            const currentLabel = document.getElementById('faviconCurrentLabel');
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            if (placeholder) placeholder.classList.add('d-none');
            if (currentLabel) currentLabel.innerHTML = '<i class="bi bi-eye-fill me-1"></i>New Favicon Preview (Click Save to Apply)';
        };
        reader.readAsDataURL(file);
    }
}


function refreshBrevoCredits(btn) {
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

    fetch("{{ route('admin.settings.brevo-credits') }}", {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        const creditsVal = document.getElementById('brevoCreditsVal');
        const planVal = document.getElementById('brevoPlanVal');
        const smtpVal = document.getElementById('brevoSmtpVal');
        const emailVal = document.getElementById('brevoAccountEmailVal');
        const badge = document.getElementById('brevoStatusBadge');
        const badgeText = document.getElementById('brevoStatusBadgeText');

        if (creditsVal) creditsVal.innerText = data.credits_label || '0';
        if (planVal) planVal.innerText = data.plan_type || 'Free';
        if (emailVal) {
            emailVal.innerText = data.account_email || 'N/A';
            emailVal.title = data.account_email || 'N/A';
        }
        if (smtpVal) {
            smtpVal.innerText = data.smtp_enabled ? 'Active ✓' : 'Disabled';
            smtpVal.className = 'fs-5 fw-bold ' + (data.smtp_enabled ? 'text-success' : 'text-muted');
        }
        if (badge && badgeText) {
            if (data.connected) {
                badge.className = 'badge bg-success text-white rounded-pill px-3 py-1 fw-bold';
                badgeText.innerText = 'Brevo REST API Active';
            } else {
                badge.className = 'badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold';
                badgeText.innerText = 'API Key Setup Needed';
            }
        }
        if (window.toast) {
            window.toast({ type: data.connected ? 'success' : 'warning', title: 'Brevo Credits Synced', message: data.connected ? `Live Credits: ${data.credits_label}` : data.message });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Failed to sync Brevo credits. Please check API Key configuration.');
    });
}

function sendBrevoTestEmail(btn) {
    const recipient = document.getElementById('test_email_recipient').value.trim();
    if (!recipient) {
        alert('Please enter a recipient email address for testing.');
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

    fetch("{{ route('admin.settings.test-email') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ email: recipient })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.success) {
            alert(`Success! ${data.message}`);
        } else {
            alert(`Test Email Failed: ${data.message}`);
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        alert('Error dispatching test email. Please check server logs.');
    });
}
</script>
@endpush
@endsection
