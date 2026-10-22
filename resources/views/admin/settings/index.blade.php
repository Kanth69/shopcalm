@extends('admin.layouts.app')

@section('header', 'System & Store Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Store Settings</li>
@endsection

@section('actions')
    <button type="submit" form="settingsForm" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        <span>Save All Settings</span>
    </button>
@endsection

@push('styles')
<style>
    .form-control:focus, .form-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    .input-group-text {
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
    }
</style>
@endpush

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
    @csrf
    @method('PATCH')

    <div class="row g-4 justify-content-center">
        <div class="col-xl-9 col-lg-10 col-12">

            <!-- 1. Store & Business Identity -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-identity">
                <div class="card-header bg-white py-3.5 px-4 border-bottom">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                <i class="bi bi-shop text-primary fs-5 me-2"></i>1. Store & Business Identity
                            </h6>
                            <small class="text-muted">General store branding, official contact emails, phone, hours, and registered physical address.</small>
                        </div>
                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">Basic Profile</span>
                    </div>
                </div>
                <div class="card-body p-4 p-md-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Store / Brand Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-building"></i></span>
                                <input type="text" name="store_name" class="form-control" value="{{ $settings['store_name'] ?? 'Shopcalm' }}" required placeholder="e.g. Shopcalm">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Displayed in header, checkout, invoices, and automated emails.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Official Support Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? 'support@shopcalm.com' }}" required placeholder="support@shopcalm.com">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Customer inquiries and system notifications will be routed here.</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Customer Support Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+91 98765 43210">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Helpline displayed on the storefront for customer assistance.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Store Currency & Symbol</label>
                            <div class="input-group">
                                <span class="input-group-text">ISO</span>
                                <input type="text" name="currency" class="form-control font-monospace" placeholder="INR" value="{{ $settings['currency'] ?? 'INR' }}">
                                <span class="input-group-text">Symbol</span>
                                <input type="text" name="currency_symbol" class="form-control font-monospace" placeholder="₹" value="{{ $settings['currency_symbol'] ?? '₹' }}">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Default currency displayed across product pricing and cart checkouts.</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">WhatsApp Support Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text text-success"><i class="bi bi-whatsapp"></i></span>
                                <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="+91 98765 43210">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Enables 1-click WhatsApp customer support chat in the storefront footer.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Support Working Hours</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                <input type="text" name="support_hours" class="form-control" value="{{ $settings['support_hours'] ?? 'Mon - Sat: 9:00 AM - 8:00 PM' }}" placeholder="e.g. Mon - Sat: 9:00 AM - 8:00 PM">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Helpline availability hours displayed on the contact page.</div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Store Tagline / Footer Summary</label>
                            <input type="text" name="tagline" class="form-control" value="{{ $settings['tagline'] ?? 'Your one-stop shop for everything you need. Quality products, unbeatable prices.' }}" placeholder="Store tagline...">
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Short 1-sentence bio shown below the footer logo.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Footer Copyright Text</label>
                            <input type="text" name="copyright_text" class="form-control" value="{{ $settings['copyright_text'] ?? ('© ' . date('Y') . ' ShopCalm. All rights reserved.') }}" placeholder="© 2026 ShopCalm. All rights reserved.">
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Legal copyright line rendered at the bottom of every page.</div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark small mb-2">Registered Business Address</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Physical office or fulfillment hub address...">{{ $settings['address'] ?? '' }}</textarea>
                        <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Rendered in invoice headers and official contact pages.</div>
                    </div>
                </div>
            </div>

            <!-- 2. Official Social Media Channels -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-social">
                <div class="card-header bg-white py-3.5 px-4 border-bottom">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                <i class="bi bi-share-fill text-primary fs-5 me-2"></i>2. Official Social Media Channels
                            </h6>
                            <small class="text-muted">Link your official social media pages for customer engagement and footer links.</small>
                        </div>
                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">Community Links</span>
                    </div>
                </div>
                <div class="card-body p-4 p-md-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-facebook text-primary me-1"></i>Facebook Page URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ $settings['facebook_url'] ?? '' }}" placeholder="https://facebook.com/yourbrand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-instagram text-danger me-1"></i>Instagram Profile URL</label>
                            <input type="url" name="instagram_url" class="form-control" value="{{ $settings['instagram_url'] ?? '' }}" placeholder="https://instagram.com/yourbrand">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-twitter-x text-dark me-1"></i>Twitter / X Profile URL</label>
                            <input type="url" name="twitter_url" class="form-control" value="{{ $settings['twitter_url'] ?? '' }}" placeholder="https://x.com/yourbrand">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-linkedin text-info me-1"></i>LinkedIn Page URL</label>
                            <input type="url" name="linkedin_url" class="form-control" value="{{ $settings['linkedin_url'] ?? '' }}" placeholder="https://linkedin.com/company/yourbrand">
                        </div>
                    </div>

                    <div class="row g-4 mb-0">
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-youtube text-danger me-1"></i>YouTube Channel URL</label>
                            <input type="url" name="youtube_url" class="form-control" value="{{ $settings['youtube_url'] ?? '' }}" placeholder="https://youtube.com/@yourbrand">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Third-Party Service Integrations -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-integrations">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="bi bi-plugin text-primary fs-5 me-2"></i>3. Third-Party Service Integrations
                        </h6>
                        <small class="text-muted">Credentials, API tokens, and webhook configs are securely managed in their dedicated hubs.</small>
                    </div>
                    <a href="{{ route('admin.integrations.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1">
                        <i class="bi bi-grid-fill"></i>
                        <span>Open Integrations Hub</span>
                    </a>
                </div>
                <div class="card-body p-4 p-md-4">
                    <div class="row g-4">
                        <!-- Meta WhatsApp Cloud API -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border rounded-4 p-4 bg-white shadow-none d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2.5 bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                <i class="bi bi-whatsapp fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">WhatsApp Business</h6>
                                                <span class="text-muted" style="font-size: 0.72rem;">Meta Cloud API</span>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $hasWhatsApp = !empty($settings['whatsapp_api_token'] ?? env('WHATSAPP_API_TOKEN')) && !empty($settings['whatsapp_phone_number_id'] ?? env('WHATSAPP_PHONE_NUMBER_ID'));
                                    @endphp
                                    <div class="mb-3">
                                        <span class="badge {{ $hasWhatsApp ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25' }} rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                            <i class="bi {{ $hasWhatsApp ? 'bi-check-circle-fill' : 'bi-exclamation-circle' }} me-1"></i>{{ $hasWhatsApp ? 'Active & Configured' : 'Setup Needed' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-4" style="font-size: 0.8rem; line-height: 1.45;">
                                        Sends automated order confirmation, dispatch alerts, OTPs, and delivery notices directly to customers.
                                    </p>
                                </div>
                                <a href="{{ route('admin.integrations.whatsapp') }}" class="btn btn-sm btn-outline-success rounded-pill w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-1">
                                    <span>Manage WhatsApp</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Brevo Transactional Email -->
                        <div class="col-lg-4 col-md-6">
                            <div class="card h-100 border rounded-4 p-4 bg-white shadow-none d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2.5 bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                <i class="bi bi-envelope-paper-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Brevo Email (v3)</h6>
                                                <span class="text-muted" style="font-size: 0.72rem;">REST Mailer</span>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $hasBrevo = !empty($settings['brevo_api_key'] ?? env('BREVO_API_KEY'));
                                    @endphp
                                    <div class="mb-3">
                                        <span class="badge {{ $hasBrevo ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25' }} rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                            <i class="bi {{ $hasBrevo ? 'bi-check-circle-fill' : 'bi-exclamation-circle' }} me-1"></i>{{ $hasBrevo ? 'Active & Configured' : 'Setup Needed' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-4" style="font-size: 0.8rem; line-height: 1.45;">
                                        Dispatches GST invoice PDFs, transactional receipts, password resets, and admin notices.
                                    </p>
                                </div>
                                <a href="{{ route('admin.integrations.brevo') }}" class="btn btn-sm btn-outline-primary rounded-pill w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-1">
                                    <span>Manage Brevo Email</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Razorpay Payment Gateway -->
                        <div class="col-lg-4 col-md-12">
                            <div class="card h-100 border rounded-4 p-4 bg-white shadow-none d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="p-2.5 bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                <i class="bi bi-credit-card-2-front-fill fs-5"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Razorpay Gateway</h6>
                                                <span class="text-muted" style="font-size: 0.72rem;">Online & UPI</span>
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                        $rzpKey = $settings['razorpay_key_id'] ?? env('RAZORPAY_KEY_ID', '');
                                        $isRzpLive = str_starts_with($rzpKey, 'rzp_live');
                                        $hasRzp = !empty($rzpKey);
                                    @endphp
                                    <div class="mb-3">
                                        <span class="badge {{ $hasRzp ? ($isRzpLive ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-info bg-opacity-10 text-info border border-info border-opacity-25') : 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25' }} rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                            <i class="bi {{ $hasRzp ? 'bi-shield-check' : 'bi-exclamation-circle' }} me-1"></i>{{ $hasRzp ? ($isRzpLive ? 'Live Production Mode' : 'Test Sandbox Mode') : 'Setup Needed' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-4" style="font-size: 0.8rem; line-height: 1.45;">
                                        Accept UPI, Cards, Net Banking, and Doorstep Dynamic UPI QR code payments with webhooks.
                                    </p>
                                </div>
                                <a href="{{ route('admin.integrations.razorpay') }}" class="btn btn-sm btn-outline-info text-dark rounded-pill w-100 py-2 fw-bold d-flex align-items-center justify-content-center gap-1">
                                    <span>Manage Razorpay</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Visual Branding & Media Assets -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-branding">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="bi bi-palette-fill text-primary fs-5 me-2"></i>4. Visual Branding & Icons
                        </h6>
                        <small class="text-muted">Upload high-resolution storefront logo and browser tab favicon assets.</small>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span>Upload & Save Branding</span>
                    </button>
                </div>
                <div class="card-body p-4 p-md-4">
                    <div class="row g-4">
                        <!-- Logo -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Main Storefront Logo</label>
                            <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*,.svg,.webp,.ico" onchange="previewLogo(this)">
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">PNG, JPG, WEBP, or SVG recommended (Max 10 MB).</div>
                            @error('logo')
                                <div class="invalid-feedback d-block fw-semibold mt-1">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div id="logoClientFeedback" class="small mt-1 d-none"></div>

                            <div class="mt-3 p-4 bg-light bg-opacity-50 rounded-4 border text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 110px;">
                                @if(!empty($settings['logo']))
                                    <img id="logoPreviewImg" src="{{ asset('storage/' . $settings['logo']) }}?v={{ @filemtime(storage_path('app/public/' . $settings['logo'])) ?: time() }}" class="img-fluid" style="max-height: 58px; object-fit: contain;">
                                    <span class="text-muted small mt-2" style="font-size: 0.7rem;"><i class="bi bi-check-circle-fill text-success me-1"></i>Active Storefront Logo</span>
                                @else
                                    <img id="logoPreviewImg" src="#" class="img-fluid d-none" style="max-height: 58px; object-fit: contain;">
                                    <div id="logoPlaceholder" class="text-muted small"><i class="bi bi-image fs-4 d-block mb-1 text-muted opacity-50"></i>No custom logo uploaded yet</div>
                                @endif
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2">Browser Tab Favicon</label>
                            <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept="image/*,.ico,.png,.jpg,.jpeg,.webp,.svg" onchange="previewFavicon(this)">
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Supports PNG, JPG, ICO, WEBP, or SVG image (Max 5 MB).</div>
                            @error('favicon')
                                <div class="invalid-feedback d-block fw-semibold mt-1">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div id="faviconClientFeedback" class="small mt-1 d-none"></div>

                            <div class="mt-3 p-4 bg-light bg-opacity-50 rounded-4 border text-center d-flex flex-column align-items-center justify-content-center" style="min-height: 110px;">
                                @if(!empty($settings['favicon']))
                                    <img id="faviconPreviewImg" src="{{ asset('storage/' . $settings['favicon']) }}?v={{ @filemtime(storage_path('app/public/' . $settings['favicon'])) ?: time() }}" class="img-fluid rounded" style="max-height: 48px; object-fit: contain;">
                                    <span id="faviconCurrentLabel" class="text-success fw-semibold mt-2" style="font-size: 0.7rem;"><i class="bi bi-check-circle-fill me-1"></i>Active Browser Tab Icon</span>
                                @else
                                    <img id="faviconPreviewImg" src="#" class="img-fluid d-none rounded" style="max-height: 48px; object-fit: contain;">
                                    <div id="faviconPlaceholder" class="text-muted small"><i class="bi bi-bookmark-star fs-4 d-block mb-1 text-muted opacity-50"></i>No custom favicon uploaded yet</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Shipping, Delivery & Payment Rules -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-shipping">
                <div class="card-header bg-white py-3.5 px-4 border-bottom">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                <i class="bi bi-truck text-primary fs-5 me-2"></i>5. Shipping, Delivery & Payment Rules
                            </h6>
                            <small class="text-muted">Master controls for Cash on Delivery (COD), order delivery thresholds, and default handling fees.</small>
                        </div>
                        <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">Checkout Policies</span>
                    </div>
                </div>
                <div class="card-body p-4 p-md-4">
                    <!-- Master COD Switch -->
                    <div class="p-4 rounded-4 border mb-4" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div style="max-width: 650px;">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <div class="fw-bold text-dark fs-6">
                                        <i class="bi bi-cash-coin text-success me-1"></i>Cash on Delivery (COD) Master Switch
                                    </div>
                                    <span class="badge {{ ($settings['enable_cod'] ?? '1') == '1' ? 'bg-success' : 'bg-danger' }} rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                                        {{ ($settings['enable_cod'] ?? '1') == '1' ? 'COD ACTIVE (ON)' : 'COD DISABLED (OFF)' }}
                                    </span>
                                </div>
                                <div class="text-muted" style="font-size: 0.8rem; line-height: 1.5;">
                                    Controls Cash on Delivery globally across the entire platform (Web Storefront & Mobile Apps).<br>
                                    When <strong>OFF</strong>: The COD option is completely hidden at checkout. Customers are required to pay online prepaid.
                                </div>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input type="hidden" name="enable_cod" value="0">
                                <input class="form-check-input" type="checkbox" name="enable_cod" value="1" id="enable_cod" {{ ($settings['enable_cod'] ?? '1') == '1' ? 'checked' : '' }} style="width: 3.2em; height: 1.6em; cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Threshold & Fees -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-truck text-primary me-1"></i>Free Shipping Minimum Spend (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" name="free_shipping_min" class="form-control fw-bold" value="{{ $settings['free_shipping_min'] ?? '499' }}" placeholder="499">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Orders equal to or above this subtotal qualify for complimentary standard shipping.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-box-seam text-primary me-1"></i>Default Delivery Fee (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" name="default_shipping_fee" class="form-control fw-bold" value="{{ $settings['default_shipping_fee'] ?? '40.00' }}" placeholder="40.00">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Charged when order is below Free Shipping spend and no custom pincode fee is set. Enter 0 for free delivery.</div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-cash-stack text-success me-1"></i>Default COD Handling Fee (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold">₹</span>
                                <input type="number" step="0.01" name="cod_flat_fee" class="form-control fw-bold" value="{{ $settings['cod_flat_fee'] ?? '40.00' }}" placeholder="40.00">
                            </div>
                            <div class="form-text text-muted mt-1.5" style="font-size: 0.75rem;">Applied to COD orders when no custom pincode fee is configured. Enter 0 for free COD.</div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100 d-flex align-items-center">
                                <div class="small text-muted" style="font-size: 0.78rem; line-height: 1.45;">
                                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                                    <strong>Fee Hierarchy:</strong> Orders $\ge$ ₹{{ $settings['free_shipping_min'] ?? '499' }} get <strong>FREE delivery</strong>. For smaller orders, any specific pincode fee is charged first; if not configured, the store <strong>Default Delivery Fee</strong> applies.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 6. Homepage Trust Badges & Promises -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-trust">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="bi bi-patch-check-fill text-primary fs-5 me-2"></i>6. Homepage Trust Badges & Guarantees
                        </h6>
                        <small class="text-muted">Highlight trust factors, guarantees, and support assurances on the storefront homepage.</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="enable_trust_badges" value="0">
                        <input class="form-check-input" type="checkbox" name="enable_trust_badges" value="1" id="enable_trust_badges" {{ ($settings['enable_trust_badges'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.45em; cursor: pointer;">
                        <label class="form-check-label small fw-bold text-muted ms-1" for="enable_trust_badges">Display Badges</label>
                    </div>
                </div>
                <div class="card-body p-4 p-md-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-truck text-primary me-1"></i>Badge 1: Free Delivery Title</label>
                            <input type="text" name="trust_badge_1_title" class="form-control" value="{{ $settings['trust_badge_1_title'] ?? 'Free Delivery' }}" placeholder="Free Delivery">
                            <div class="mt-2">
                                <label class="form-label fw-semibold text-muted small mb-1">Badge 1 Subtext / Criteria</label>
                                <input type="text" name="trust_badge_1_desc" class="form-control form-control-sm text-muted" value="{{ $settings['trust_badge_1_desc'] ?? ('Orders ₹' . ($settings['free_shipping_min'] ?? '499') . '+') }}" placeholder="Orders ₹499+">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-shield-check text-success me-1"></i>Badge 2: Secure Payment Title</label>
                            <input type="text" name="trust_badge_2_title" class="form-control" value="{{ $settings['trust_badge_2_title'] ?? 'Secure Checkout' }}" placeholder="Secure Checkout">
                            <div class="mt-2">
                                <label class="form-label fw-semibold text-muted small mb-1">Badge 2 Subtext / Criteria</label>
                                <input type="text" name="trust_badge_2_desc" class="form-control form-control-sm text-muted" value="{{ $settings['trust_badge_2_desc'] ?? '100% Encrypted' }}" placeholder="100% Encrypted">
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-patch-check text-warning me-1"></i>Badge 3: Authenticity Title</label>
                            <input type="text" name="trust_badge_3_title" class="form-control" value="{{ $settings['trust_badge_3_title'] ?? '100% Genuine' }}" placeholder="100% Genuine">
                            <div class="mt-2">
                                <label class="form-label fw-semibold text-muted small mb-1">Badge 3 Subtext / Criteria</label>
                                <input type="text" name="trust_badge_3_desc" class="form-control form-control-sm text-muted" value="{{ $settings['trust_badge_3_desc'] ?? 'Direct From Brands' }}" placeholder="Direct From Brands">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small mb-2"><i class="bi bi-headset text-info me-1"></i>Badge 4: Support Title</label>
                            <input type="text" name="trust_badge_4_title" class="form-control" value="{{ $settings['trust_badge_4_title'] ?? 'Fast Support' }}" placeholder="Fast Support">
                            <div class="mt-2">
                                <label class="form-label fw-semibold text-muted small mb-1">Badge 4 Subtext / Criteria</label>
                                <input type="text" name="trust_badge_4_desc" class="form-control form-control-sm text-muted" value="{{ $settings['trust_badge_4_desc'] ?? 'Helpdesk Available' }}" placeholder="Helpdesk Available">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Customer Order Cancellation Controls -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 settings-section" id="section-cancellation">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                            <i class="bi bi-x-circle-fill text-danger fs-5 me-2"></i>7. Customer Self-Service Cancellation Control
                        </h6>
                        <small class="text-muted">Give customers permission to cancel unshipped orders directly from their account orders page.</small>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="allow_customer_cancellation" value="0">
                        <input class="form-check-input" type="checkbox" name="allow_customer_cancellation" value="1" id="allow_customer_cancellation" {{ ($settings['allow_customer_cancellation'] ?? '1') == '1' ? 'checked' : '' }} style="width: 2.8em; height: 1.45em; cursor: pointer;">
                        <label class="form-check-label small fw-bold text-muted ms-1" for="allow_customer_cancellation">Enabled</label>
                    </div>
                </div>
                <div class="card-body p-4 p-md-4">
                    <div class="p-4 rounded-4 border bg-light d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div style="max-width: 650px;">
                            <div class="fw-bold text-dark small mb-1">
                                <i class="bi bi-person-x text-danger me-1"></i>Allow Customers to Cancel Pending/Confirmed Orders
                            </div>
                            <div class="text-muted" style="font-size: 0.8rem; line-height: 1.45;">
                                When <strong>ON / YES</strong>: Customers see an active "Cancel Order" button in their Order Details page.<br>
                                When <strong>OFF / NO</strong>: Customer self-service cancellation is disabled. Only store support/admins can cancel orders.
                            </div>
                        </div>
                        <div class="badge {{ ($settings['allow_customer_cancellation'] ?? '1') == '1' ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill fs-7">
                            {{ ($settings['allow_customer_cancellation'] ?? '1') == '1' ? 'Allowed (YES)' : 'Disabled (NO)' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticky Bottom Save Bar -->
            <div class="card border-0 shadow-lg rounded-4 p-3 bg-white mb-5 sticky-bottom border" style="bottom: 1rem; z-index: 1020;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Changes made to Store Settings are applied immediately across web storefront and mobile apps.</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Save All Settings</span>
                        </button>
                    </div>
                </div>
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
</script>
@endpush
@endsection
