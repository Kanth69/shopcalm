@extends('marketing.layouts.app')

@section('title', 'Push Notifications')
@section('header', 'Push Notifications & Campaign Manager')
@section('subheader', 'Compose mobile push alerts, test live lock-screen previews, and broadcast to customer devices.')

@section('actions')
    <a href="{{ route('marketing.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
        <i class="bi bi-speedometer2"></i>
        <span>Analytics Dashboard</span>
    </a>
@endsection

@push('styles')
<style>
    /* 📱 Interactive iPhone Mockup */
    .phone-mockup-wrapper {
        width: 100%;
        max-width: 320px;
        margin: 0 auto;
    }
    .phone-frame {
        background: #0f172a;
        border: 10px solid #1e293b;
        border-radius: 44px;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.08);
        overflow: hidden;
        position: relative;
    }
    .phone-notch {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 18px;
        background: #1e293b;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        z-index: 20;
    }
    .phone-screen {
        background: linear-gradient(180deg, #1e293b 0%, #334155 50%, #0f172a 100%);
        min-height: 500px;
        position: relative;
        padding-top: 22px;
        padding-bottom: 22px;
    }
    .phone-notification-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.85);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
        border-radius: 16px;
        padding: 12px 14px;
    }
    .phone-home-indicator {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 2px;
    }

    /* ⚡ Preset Pill Buttons */
    .preset-pill-btn {
        transition: all 0.2s cubic-bezier(.4,0,.2,1);
        border: 1.5px solid var(--border);
        background: #ffffff;
        color: var(--slate-600);
        font-size: 0.83rem;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: var(--radius-pill);
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }
    .preset-pill-btn:hover {
        border-color: var(--primary);
        color: #be185d;
        background: var(--primary-subtle);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(236, 72, 153, 0.12);
    }
    .preset-pill-btn.active {
        background: var(--primary);
        border-color: var(--primary);
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35) !important;
    }

    /* Form & Spacing refinements */
    .form-control-custom {
        padding: 9px 14px;
        font-size: 0.9rem;
        border-radius: var(--radius-sm);
        border: 1px solid var(--border);
    }
    .form-control-custom:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.15);
    }
</style>
@endpush

@section('content')

<form action="{{ route('marketing.notifications.send') }}" method="POST" id="pushBroadcastForm">
    @csrf

    <input type="hidden" name="campaign_type" id="campaignTypeInput" value="seasonal">

    <!-- 1. Campaign Preset Pills -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <label class="form-label fw-bold text-dark small text-uppercase mb-3" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                Step 1: Choose Campaign Type
            </label>
            <div class="d-flex flex-wrap gap-2.5" id="presetButtons">
                <button type="button" class="preset-pill-btn active" data-preset="seasonal">
                    🌸 Festive &amp; Seasonal
                </button>
                <button type="button" class="preset-pill-btn" data-preset="flash_sale">
                    ⚡ Flash Sale (40% OFF)
                </button>
                <button type="button" class="preset-pill-btn" data-preset="birthday">
                    🎂 Birthday Special
                </button>
                <button type="button" class="preset-pill-btn" data-preset="dormant">
                    👋 Win-Back Dormant
                </button>
                <button type="button" class="preset-pill-btn" data-preset="wallet_expiry">
                    💰 Wallet Expiry Reminder
                </button>
                <button type="button" class="preset-pill-btn" data-preset="custom">
                    <i class="bi bi-pencil-square"></i> Custom Campaign
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Composer Left Column -->
        <div class="col-lg-7">
            <!-- 2. Content & Language -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3.5 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark">Step 2: Notification Copy &amp; Language</h6>
                </div>
                <div class="card-body p-4">
                    <!-- Language Selection -->
                    <div class="mb-3.5">
                        <label class="form-label fw-semibold text-dark small d-flex align-items-center justify-content-between mb-1.5">
                            <span>Language Targeting (Auto-translates Copy)</span>
                            <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Regional Pincode Matched</span>
                        </label>
                        <select name="target_language" id="languageSelect" class="form-select form-control-custom">
                            <option value="all" selected>🌐 All Languages (Pan-India Broadcast)</option>
                            <option value="te">🗣️ Telugu (తెలుగు) — AP &amp; Telangana (50-53xxx)</option>
                            <option value="hi">🗣️ Hindi (हिंदी) — North &amp; Central India</option>
                            <option value="en">🗣️ English Only (Global / Pan-India)</option>
                            <option value="ta">🗣️ Tamil (தமிழ்) — Tamil Nadu (60-64xxx)</option>
                            <option value="kn">🗣️ Kannada (ಕನ್ನಡ) — Karnataka (56-59xxx)</option>
                        </select>
                        <div class="form-text small text-muted mt-1.5" style="font-size: 0.78rem;">
                            <i class="bi bi-geo-alt text-primary me-1"></i> Automatically targets customers by registered language or matches delivery address pincodes.
                        </div>
                    </div>

                    <!-- Headline / Title -->
                    <div class="mb-3.5">
                        <label class="form-label fw-semibold text-dark small mb-1.5">Notification Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="pushTitleInput" class="form-control form-control-custom" placeholder="e.g. ✨ Festive Season Mega Deals Are Live!" value="✨ Festive Season Mega Deals Are Live!" required maxlength="255">
                        <div class="form-text small text-muted mt-1.5" style="font-size: 0.78rem;">Punchy lock-screen title. Under 50 characters recommended for mobile readability.</div>
                    </div>

                    <!-- Message Body -->
                    <div class="mb-3.5">
                        <label class="form-label fw-semibold text-dark small mb-1.5">Message Body <span class="text-danger">*</span></label>
                        <textarea name="body" id="pushBodyInput" class="form-control form-control-custom" rows="3" placeholder="Explore trending festive styles, ethnic wear, and exclusive bundle offers. Tap to shop now! 🛍️" required maxlength="1000">Explore trending festive styles, ethnic wear, and exclusive bundle offers. Tap to shop now! 🛍️</textarea>
                        <div class="form-text small text-muted mt-1.5" style="font-size: 0.78rem;">Full notification text displayed on user phones.</div>
                    </div>

                    <!-- Rich Banner Image URL -->
                    <div class="mb-1">
                        <label class="form-label fw-semibold text-dark small mb-1.5">Banner Image URL (Optional)</label>
                        <input type="url" name="banner_image" id="pushImageInput" class="form-control form-control-custom" placeholder="https://your-domain.com/storage/banners/festive-sale.jpg" value="{{ old('banner_image') }}">
                        <div class="form-text small text-muted mt-1.5" style="font-size: 0.78rem;">Optional image shown on Android &amp; iOS expanded push alerts.</div>
                    </div>
                </div>
            </div>

            <!-- 3. Automated Routing & Target Audience Summary -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark">Step 3: Audience &amp; In-App Destination</h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">
                        ⚡ Auto-Configured
                    </span>
                </div>
                <div class="card-body p-4">
                    <!-- Auto-Configured Summary Box -->
                    <div class="p-3 rounded-4 bg-light border d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
                        <div>
                            <div class="fw-bold text-dark small mb-1" id="autoPresetSummaryTitle">
                                🌸 Festive Campaign Automation
                            </div>
                            <div class="text-muted small" style="font-size: 0.8rem;" id="autoPresetSummaryDesc">
                                Targeting: <strong>All Active Devices</strong> &bull; Tapping opens: <strong>Offers Hub</strong>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-flex align-items-center gap-1.5" id="toggleAdvancedBtn" style="font-size: 0.8rem;">
                            <i class="bi bi-sliders"></i>
                            <span id="toggleBtnText">Customize Manually</span>
                        </button>
                    </div>

                    <!-- Hidden Manual Override Section (Collapsible) -->
                    <div id="advancedSection" class="d-none pt-3">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1.5">Gender</label>
                                <select name="target_gender" id="genderSelect" class="form-select rounded-3">
                                    <option value="all" selected>All Genders</option>
                                    <option value="Female">Female Only</option>
                                    <option value="Male">Male Only</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1.5">Age Filter</label>
                                <select name="target_age_group" id="ageSelect" class="form-select rounded-3">
                                    <option value="all" selected>All Ages</option>
                                    <option value="birthday_month">🎂 Birthday This Month</option>
                                    <option value="under_25">Under 25 Yrs</option>
                                    <option value="25_40">25 - 40 Yrs</option>
                                    <option value="over_40">40+ Yrs</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1.5">Activity</label>
                                <select name="target_activity" id="activitySelect" class="form-select rounded-3">
                                    <option value="all" selected>All Active</option>
                                    <option value="dormant">Dormant (&gt;30d inactive)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small mb-1.5">Tapping Opens</label>
                                <select name="destination_type" id="destinationType" class="form-select rounded-3" required>
                                    <option value="offers" selected>Offers &amp; Deals Hub</option>
                                    <option value="home">App Home Screen</option>
                                    <option value="category">Specific Category</option>
                                    <option value="cart">Cart Screen</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-2 d-none" id="categoryGroup">
                            <label class="form-label fw-semibold text-dark small mb-1.5">Category</label>
                            <select name="category_id" id="categorySelect" class="form-select rounded-3">
                                <option value="" disabled selected>-- Choose Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Submit Broadcast Box -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                            <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-0.5">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <span>Super Admin Broadcast</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.78rem;">Push alert will be dispatched to all matching active phones instantly.</small>
                        @else
                            <div class="fw-bold text-dark d-flex align-items-center gap-2 mb-0.5">
                                <i class="bi bi-shield-exclamation text-warning fs-5"></i>
                                <span>Submit for Admin Review</span>
                            </div>
                            <small class="text-muted" style="font-size: 0.78rem;">Push draft will be queued for Super Admin approval before broadcast.</small>
                        @endif
                    </div>

                    <div>
                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="return confirm('Broadcast this push notification alert to customer devices now?');">
                                <i class="bi bi-send-fill"></i>
                                <span>Broadcast Push Now</span>
                            </button>
                        @else
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm d-flex align-items-center gap-2" onclick="return confirm('Submit this push notification draft for Super Admin approval?');">
                                <i class="bi bi-send-check-fill"></i>
                                <span>Submit for Approval</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Clean Phone Mockup Preview -->
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 88px; z-index: 10;">
                <div class="text-center mb-3">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Live Lock-Screen Mobile Preview</span>
                </div>

                <!-- Phone Frame -->
                <div class="phone-mockup-wrapper mx-auto">
                    <div class="phone-frame shadow-lg">
                        <div class="phone-notch"></div>
                        <div class="phone-screen">
                            <!-- Status Bar -->
                            <div class="phone-status-bar d-flex justify-content-between px-4 pt-3 text-white small">
                                <span class="fw-bold" id="phoneTime">09:41</span>
                                <div class="d-flex gap-2 align-items-center">
                                    <i class="bi bi-reception-4" style="font-size: 11px;"></i>
                                    <i class="bi bi-wifi" style="font-size: 11px;"></i>
                                    <i class="bi bi-battery-full" style="font-size: 13px;"></i>
                                </div>
                            </div>

                            <!-- Lock Screen Clock -->
                            <div class="text-center text-white my-3">
                                <div class="display-5 fw-bold" id="phoneClock" style="letter-spacing: -1px;">09:41</div>
                                <div class="small opacity-75 mt-0.5" style="font-size: 11px;">{{ date('l, F j') }}</div>
                            </div>

                            <!-- Floating iOS Push Banner Preview -->
                            <div class="mx-3 phone-notification-card text-dark shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-0.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="shadow-xs d-flex align-items-center justify-content-center text-white flex-shrink-0" style="background: linear-gradient(135deg, #ec4899, #db2777); width: 24px; height: 24px; border-radius: 6px;">
                                            <i class="bi bi-bag-heart-fill" style="font-size: 12px; line-height: 1;"></i>
                                        </div>
                                        <span class="fw-bold text-dark" style="font-size: 11.5px; letter-spacing: 0.4px;">SHOPCALM</span>
                                    </div>
                                    <span class="text-muted" style="font-size: 10.5px;">now</span>
                                </div>

                                <div class="fw-bold text-dark mb-1.5" id="previewTitle" style="font-size: 13px; line-height: 1.35;">
                                    ✨ Festive Season Mega Deals Are Live!
                                </div>

                                <div class="text-secondary small mb-2" id="previewBody" style="font-size: 12px; line-height: 1.4;">
                                    Explore trending festive styles, ethnic wear, and exclusive bundle offers. Tap to shop now! 🛍️
                                </div>

                                <div class="notification-banner-preview rounded-3 overflow-hidden d-none mt-2" id="previewBannerContainer">
                                    <img src="" id="previewBannerImg" class="w-100" style="max-height: 125px; object-fit: cover;" alt="Notification Banner">
                                </div>
                            </div>

                            <div class="phone-home-indicator"></div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3 text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle me-1"></i> Renders accurately across Android 12+ and iOS 16+ lock-screens.
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Full Campaign History Table -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 bg-white">
    <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Campaign Submission &amp; Sent Log</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace small">
            Total Campaigns: {{ $notifications->total() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 py-2.5" style="width: 32%; font-size: 0.72rem; letter-spacing: 0.5px;">Notification Message</th>
                    <th class="py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Type</th>
                    <th class="py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Audience &amp; Lang</th>
                    <th class="py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Destination</th>
                    <th class="text-center py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</th>
                    <th class="text-center py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Delivered</th>
                    <th class="py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Submitted By</th>
                    <th class="text-end pe-4 py-2.5" style="font-size: 0.72rem; letter-spacing: 0.5px;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $item)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-start gap-2.5">
                                @if($item->banner_image)
                                    <img src="{{ $item->banner_image }}" class="rounded-3 border flex-shrink-0" style="width: 42px; height: 42px; object-fit: cover;" alt="Banner">
                                @else
                                    <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width: 42px; height: 42px; min-width: 42px;">
                                        <i class="bi bi-bell fs-5"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark mb-0.5" style="font-size: 0.85rem;">{{ $item->title }}</div>
                                    <div class="text-secondary small text-truncate" style="max-width: 300px; font-size: 0.78rem;" title="{{ $item->body }}">
                                        {{ $item->body }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="py-3">
                            @if($item->campaign_type === 'birthday')
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill small px-2.5 py-1">🎂 Birthday</span>
                            @elseif($item->campaign_type === 'seasonal')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small px-2.5 py-1" style="background:#ffe4e6;">🌸 Festive</span>
                            @elseif($item->campaign_type === 'flash_sale')
                                <span class="badge bg-danger text-white rounded-pill small px-2.5 py-1">⚡ Flash Sale</span>
                            @elseif($item->campaign_type === 'dormant')
                                <span class="badge bg-dark text-white rounded-pill small px-2.5 py-1">👋 Win-Back</span>
                            @elseif($item->campaign_type === 'wallet_expiry')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2.5 py-1">💰 Wallet</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill small px-2.5 py-1">🎯 Custom</span>
                            @endif
                        </td>

                        <td class="py-3">
                            <div class="d-flex flex-wrap gap-1">
                                <!-- Language Tag -->
                                @if($item->target_language === 'te')
                                    <span class="badge bg-primary text-white rounded-pill small px-2 py-0.5">తెలుగు</span>
                                @elseif($item->target_language === 'hi')
                                    <span class="badge bg-warning text-dark rounded-pill small px-2 py-0.5">हिंदी</span>
                                @elseif($item->target_language === 'ta')
                                    <span class="badge bg-info text-dark rounded-pill small px-2 py-0.5">தமிழ்</span>
                                @elseif($item->target_language === 'kn')
                                    <span class="badge bg-secondary text-white rounded-pill small px-2 py-0.5">ಕನ್ನಡ</span>
                                @else
                                    <span class="badge bg-light text-dark border rounded-pill small px-2 py-0.5">🌐 All</span>
                                @endif

                                @if($item->target_gender === 'Male')
                                    <span class="badge bg-info-subtle text-info rounded-pill small px-2 py-0.5">Male</span>
                                @elseif($item->target_gender === 'Female')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill small px-2 py-0.5">Female</span>
                                @endif

                                @if($item->target_age_group === 'birthday_month')
                                    <span class="badge bg-warning-subtle text-warning rounded-pill small px-2 py-0.5">Birthday</span>
                                @endif

                                @if($item->target_activity === 'dormant')
                                    <span class="badge bg-dark text-white rounded-pill small px-2 py-0.5">Dormant</span>
                                @endif
                            </div>
                        </td>

                        <td class="py-3">
                            @if($item->destination_type === 'category' && $item->category)
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">📁 {{ $item->category->name }}</span>
                            @elseif($item->destination_type === 'offers')
                                <span class="badge bg-warning-subtle text-warning rounded-pill px-2.5 py-1">🏷️ Offers Hub</span>
                            @elseif($item->destination_type === 'cart')
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1">🛒 Cart Screen</span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">🏠 App Home</span>
                            @endif
                        </td>

                        <td class="text-center py-3">
                            @if($item->isPending())
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small px-2.5 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
                                </span>
                            @elseif($item->isApproved())
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small px-2.5 py-1">
                                    <i class="bi bi-check2-circle me-1"></i> Broadcast
                                </span>
                            @elseif($item->isRejected())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small px-2.5 py-1" title="{{ $item->rejection_reason }}">
                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                </span>
                            @endif
                        </td>

                        <td class="text-center py-3">
                            @if($item->isApproved())
                                <span class="fw-bold text-dark small">{{ number_format($item->recipient_count) }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">phones</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td class="py-3">
                            <small class="fw-semibold text-dark" style="font-size: 0.8rem;">{{ optional($item->sender)->name ?? 'Marketing Staff' }}</small>
                            @if($item->isApproved() && $item->approver)
                                <small class="text-muted d-block" style="font-size: 10px;">Approved by {{ $item->approver->name }}</small>
                            @endif
                        </td>

                        <td class="text-end pe-4 py-3 text-nowrap">
                            <span class="text-dark small fw-semibold" style="font-size: 0.8rem;">{{ $item->created_at->format('d M Y') }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2 text-muted"></i>
                            No push campaigns recorded yet. Use the composer above to launch your first push alert!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pushTitleInput = document.getElementById('pushTitleInput');
    const pushBodyInput = document.getElementById('pushBodyInput');
    const pushImageInput = document.getElementById('pushImageInput');
    const campaignTypeInput = document.getElementById('campaignTypeInput');
    const languageSelect = document.getElementById('languageSelect');
    const genderSelect = document.getElementById('genderSelect');
    const ageSelect = document.getElementById('ageSelect');
    const activitySelect = document.getElementById('activitySelect');
    const destinationType = document.getElementById('destinationType');
    const categoryGroup = document.getElementById('categoryGroup');
    const categorySelect = document.getElementById('categorySelect');

    const autoPresetSummaryTitle = document.getElementById('autoPresetSummaryTitle');
    const autoPresetSummaryDesc = document.getElementById('autoPresetSummaryDesc');
    const toggleAdvancedBtn = document.getElementById('toggleAdvancedBtn');
    const advancedSection = document.getElementById('advancedSection');
    const toggleBtnText = document.getElementById('toggleBtnText');

    const previewTitle = document.getElementById('previewTitle');
    const previewBody = document.getElementById('previewBody');
    const previewBannerContainer = document.getElementById('previewBannerContainer');
    const previewBannerImg = document.getElementById('previewBannerImg');

    // ⚡ Campaign Presets Configuration (Copy + Auto Applied Targeting)
    const presets = {
        seasonal: {
            title_label: '🌸 Festive Campaign Automation',
            desc_label: 'Targeting: All Active Devices • Tapping opens: Offers Hub',
            en: { title: '✨ Festive Season Mega Deals Are Live!', body: 'Explore trending festive styles, ethnic wear, and exclusive bundle offers. Tap to shop now! 🛍️' },
            te: { title: '✨ పండుగ స్పెషల్ మెగా ఆఫర్లు లైవ్ అయ్యాయి! 🛍️', body: 'ట్రెండింగ్ ఎత్నిక్ వేర్, ఫెస్టివ్ కలెక్షన్స్ మరియు ప్రత్యేక కాంబో ఆఫర్లు. స్టాక్ ముగిసేలోపే ఇప్పుడే చూడండి!' },
            hi: { title: '✨ फेस्टिव सीजन महा सेल लाइव! 🛍️', body: 'शानदार एथनिक वियर, फेस्टिव स्टाइल्स और विशेष बंडल्स पर भारी छूट पाएँ। अभी टैप करें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'offers'
        },
        flash_sale: {
            title_label: '⚡ Flash Sale Automation',
            desc_label: 'Targeting: All Active Devices • Tapping opens: Offers Hub',
            en: { title: '⚡ 3-Hour Lightning Flash Sale - Flat 40% OFF!', body: 'Tick-tock! Limited inventory flash sale is live now across top categories. Tap to grab before stock runs out!' },
            te: { title: '⚡ 3 గంటల లైట్నింగ్ ఫ్లాష్ సేల్ - ఫ్లాట్ 40% తగ్గింపు!', body: 'సమయం వేగంగా ముగుస్తోంది! పరిమిత స్టాక్ మాత్రమే అందుబాటులో ఉంది. ఇప్పుడే ఆర్డర్ చేయండి!' },
            hi: { title: '⚡ 3 घंटे की लाइटनिंग फ्लैश सेल - फ्लैट 40% छूट!', body: 'समय सीमित है! बेहतरीन प्रोडक्ट्स पर भारी डिस्काउंट पाएँ। तुरंत टैप करें और बचत करें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'offers'
        },
        birthday: {
            title_label: '🎂 Birthday Treat Automation',
            desc_label: 'Targeting: Birthday Month (DOB) • Tapping opens: Offers Hub',
            en: { title: '🎂 Happy Birthday Month from ShopCalm!', body: 'Celebrate with an exclusive birthday gift discount just for you. Tap to claim your treat! 🎁' },
            te: { title: '🎂 జన్మదిన శుభాకాంక్షలు! మీ బర్త్‌డే గిఫ్ట్ ఇక్కడ ఉంది 🎁', body: 'మీ బర్త్‌డే స్పెషల్ గిఫ్ట్ డిస్కౌంట్‌తో షాపింగ్ చేసుకోండి. ఆఫర్ ఈ నెల మాత్రమే అందుబాటులో ఉంటుంది!' },
            hi: { title: '🎂 जन्मदिन की हार्दिक शुभकामनाएँ! आपका बर्थडे गिफ्ट यहाँ है 🎁', body: 'अपने जन्मदिन के विशेष डिस्काउंट का आनंद लें। आज ही अपने पसंदीदा सामान ऑर्डर करें!' },
            gender: 'all', age: 'birthday_month', activity: 'all', destination: 'offers'
        },
        dormant: {
            title_label: '👋 Win-Back Inactive Automation',
            desc_label: 'Targeting: Dormant Users (>30 Days Inactive) • Tapping opens: Offers Hub',
            en: { title: '👋 We miss you! Here is an exclusive comeback treat', body: 'It has been a while since your last visit. We have added fresh arrivals and a special comeback deal just for you!' },
            te: { title: '👋 మేము మిమ్మల్ని మిస్ అవుతున్నాము! మీ కోసం ప్రత్యేక ఆఫర్', body: 'మీరు విజిట్ చేసి చాలా రోజులయ్యింది. మీ కోసం సరికొత్త కలెక్షన్లు మరియు స్పెషల్ కమ్‌బ్యాక్ ఆఫర్ సిద్ధంగా ఉన్నాయి!' },
            hi: { title: '👋 हम आपको मिस कर रहे हैं! खास वेलकम बैक ट्रीट आपके लिए', body: 'बहुत दिन हो गए! आपके लिए नए स्टाइल्स और विशेष वेलकम-बैक डिस्काउंट तैयार हैं।' },
            gender: 'all', age: 'all', activity: 'dormant', destination: 'offers'
        },
        wallet_expiry: {
            title_label: '💰 Wallet Balance Expiry Automation',
            desc_label: 'Targeting: Active Wallet Credit Users • Tapping opens: Cart Screen',
            en: { title: '💰 Don\'t let your ShopCalm Cash expire!', body: 'You have unused wallet reward credits in your account. Apply your wallet balance on your order today!' },
            te: { title: '💰 మీ ShopCalm క్యాష్ వాలెట్ క్రెడిట్స్ గడువు ముగియకముందే వాడండి!', body: 'మీ వాలెట్ ఖాతాలో రివార్డ్ క్యాష్ సిద్ధంగా ఉంది. మీ తదుపరి ఆర్డర్‌పై ఈ రోజే వర్తింపజేయండి!' },
            hi: { title: '💰 अपने ShopCalm वॉलेट बैलेंस को एक्सपायर होने से पहले इस्तेमाल करें!', body: 'आपके खाते में अनयूज्ड वॉलेट रिवॉर्ड्स हैं। आज ही अपने ऑर्डर पर वॉलेट बैलेंस का इस्तेमाल करें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'cart'
        },
        custom: {
            title_label: '🎯 Custom Campaign Configuration',
            desc_label: 'Manual Filters Active • Configure parameters below',
            en: { title: '❄️ Cozy Winter Sweaters - Flat 40% OFF!', body: 'Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!' },
            te: { title: '❄️ వింటర్ స్వెటర్లపై ఫ్లాట్ 40% తగ్గింపు!', body: 'ఈ సీజన్‌లో వెచ్చగా ఉండే స్వెటర్లు, కార్డిగాన్స్ మరియు జాకెట్లు. స్టాక్ ముగిసేలోపే ఇప్పుడే కొనండి!' },
            hi: { title: '❄️ विंटर स्वेटर्स पर फ्लैट 40% छूट!', body: 'इस सीज़न में स्टाइलिश और गर्म स्वेटर, कार्डिगन और जैकेट पाएँ। स्टॉक खत्म होने से पहले अभी खरीदें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'home'
        }
    };

    let activePresetKey = 'seasonal';

    // Preset button click handling
    document.querySelectorAll('.preset-pill-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.preset-pill-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            activePresetKey = this.dataset.preset;
            campaignTypeInput.value = activePresetKey;

            // Automatically open manual section only if Custom is clicked
            if (activePresetKey === 'custom') {
                advancedSection.classList.remove('d-none');
                toggleBtnText.textContent = 'Hide Overrides';
            } else {
                advancedSection.classList.add('d-none');
                toggleBtnText.textContent = 'Customize Manually';
            }

            applyPresetData();
        });
    });

    // Toggle advanced manual controls
    toggleAdvancedBtn.addEventListener('click', function () {
        const isHidden = advancedSection.classList.toggle('d-none');
        toggleBtnText.textContent = isHidden ? 'Customize Manually' : 'Hide Overrides';
    });

    function applyPresetData() {
        const preset = presets[activePresetKey] || presets.seasonal;
        const currentLang = languageSelect.value;
        const langKey = (currentLang === 'te' || currentLang === 'hi') ? currentLang : 'en';

        // Auto-fill copy
        const textData = preset[langKey] || preset.en;
        pushTitleInput.value = textData.title;
        pushBodyInput.value = textData.body;

        // Auto-configure audience & routing
        genderSelect.value = preset.gender || 'all';
        ageSelect.value = preset.age || 'all';
        activitySelect.value = preset.activity || 'all';
        destinationType.value = preset.destination || 'offers';

        // Update Auto Notice Summary
        if (autoPresetSummaryTitle) autoPresetSummaryTitle.textContent = preset.title_label;
        if (autoPresetSummaryDesc) autoPresetSummaryDesc.innerHTML = preset.desc_label;

        categoryGroup.classList.toggle('d-none', destinationType.value !== 'category');

        // Update phone preview
        previewTitle.textContent = textData.title;
        previewBody.textContent = textData.body;
    }

    languageSelect.addEventListener('change', function () {
        applyPresetData();
    });

    function updatePhoneClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        const timeEl = document.getElementById('phoneTime');
        const clockEl = document.getElementById('phoneClock');
        if (timeEl) timeEl.textContent = `${hours}:${mins}`;
        if (clockEl) clockEl.textContent = `${hours}:${mins}`;
    }
    updatePhoneClock();
    setInterval(updatePhoneClock, 30000);

    pushTitleInput.addEventListener('input', function () {
        previewTitle.textContent = this.value.trim() || '✨ Festive Season Mega Deals Are Live!';
    });

    pushBodyInput.addEventListener('input', function () {
        previewBody.textContent = this.value.trim() || 'Explore trending festive styles, ethnic wear, and exclusive bundle offers. Tap to shop now! 🛍️';
    });

    pushImageInput.addEventListener('input', function () {
        const url = this.value.trim();
        if (url) {
            previewBannerImg.src = url;
            previewBannerContainer.classList.remove('d-none');
        } else {
            previewBannerContainer.classList.add('d-none');
            previewBannerImg.src = '';
        }
    });

    destinationType.addEventListener('change', function () {
        categoryGroup.classList.toggle('d-none', this.value !== 'category');
    });

    // Initial setup
    applyPresetData();
});
</script>
@endpush
