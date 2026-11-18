@extends('marketing.layouts.app')

@section('title', 'Marketing & Push Notifications')
@section('header', 'Push Notifications & Campaign Manager')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('marketing.dashboard') }}">Marketing</a></li>
    <li class="breadcrumb-item active" aria-current="page">Push Notifications</li>
@endsection

@push('styles')
<style>
    .phone-mockup-wrapper {
        max-width: 320px;
    }

    .phone-frame {
        width: 310px;
        height: 620px;
        background: #000;
        border-radius: 46px;
        padding: 12px;
        border: 4px solid #333;
        position: relative;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
    }

    .phone-screen {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #4338ca 100%);
        border-radius: 36px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .phone-notch {
        position: absolute;
        top: 18px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 22px;
        background: #000;
        border-radius: 20px;
        z-index: 20;
    }

    .phone-notification-card {
        background: rgba(255, 255, 255, 0.94);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        animation: slideDown 0.3s ease-out;
    }

    .phone-home-indicator {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        background: rgba(255, 255, 255, 0.7);
        border-radius: 2px;
    }

    .preset-pill-btn {
        transition: all 0.2s cubic-bezier(.4,0,.2,1);
        border-width: 1.5px;
        font-size: 0.82rem;
    }
    .preset-pill-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
    }
    .preset-pill-btn.active {
        box-shadow: 0 4px 14px rgba(236, 72, 153, 0.35) !important;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<!-- Audience Demographics Scorecards -->
<div class="row g-3 mb-4" id="demographics-anchor">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Active Devices</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill p-2"><i class="bi bi-phone-fill fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalActiveDevices) }}</h3>
            <small class="text-muted">Registered Customer Apps</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Male Users</span>
                <span class="badge bg-info-subtle text-info rounded-pill p-2"><i class="bi bi-gender-male fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($maleDevices) }}</h3>
            <small class="text-muted">Targetable Male Profiles</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Female Users</span>
                <span class="badge bg-danger-subtle text-danger rounded-pill p-2" style="background-color: #ffe4e6;"><i class="bi bi-gender-female fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($femaleDevices) }}</h3>
            <small class="text-muted">Targetable Female Profiles</small>
        </div>
    </div>

    <div class="col-sm-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Total Broadcasts</span>
                <span class="badge bg-purple-subtle text-purple rounded-pill p-2"><i class="bi bi-broadcast fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBroadcasts) }}</h3>
            <small class="text-muted">Push Alerts Sent</small>
        </div>
    </div>

    <div class="col-sm-12 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold text-uppercase tracking-wider">Pushes Delivered</span>
                <span class="badge bg-success-subtle text-success rounded-pill p-2"><i class="bi bi-check2-all fs-6"></i></span>
            </div>
            <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDelivered) }}</h3>
            <small class="text-muted">Delivered to Phones</small>
        </div>
    </div>
</div>

<form action="{{ route('marketing.notifications.send') }}" method="POST" id="pushBroadcastForm">
    @csrf

    <input type="hidden" name="campaign_type" id="campaignTypeInput" value="custom">

    <!-- ⚡ Quick Campaign Presets Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3.5">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                        <i class="bi bi-lightning-charge-fill fs-6"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Quick Campaign Presets</h6>
                        <small class="text-muted">1-click autofills recommended headline, body, and audience filters.</small>
                    </div>
                </div>
                <span class="badge bg-light text-muted border rounded-pill small">Autofill & Customise</span>
            </div>

            <div class="d-flex flex-wrap gap-2" id="presetButtons">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold preset-pill-btn active" data-preset="custom">
                    <i class="bi bi-pencil-square me-1"></i> Custom Campaign
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold preset-pill-btn" data-preset="birthday">
                    🎂 Birthday Treat
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-semibold preset-pill-btn" data-preset="seasonal">
                    🌸 Festive &amp; Seasonal
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5 fw-semibold preset-pill-btn" data-preset="flash_sale">
                    ⚡ Flash Sale (40% OFF)
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-semibold preset-pill-btn" data-preset="dormant">
                    👋 Win-Back Dormant
                </button>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-semibold preset-pill-btn" data-preset="wallet_expiry">
                    💰 Wallet Expiry Reminder
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Composer Left Column -->
        <div class="col-lg-7">
            <!-- 1. Content Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-square-dots-fill text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">1. Notification Content</h6>
                    </div>
                    <span class="badge bg-light text-muted border rounded-pill small">Required</span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Notification Headline / Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="pushTitleInput" class="form-control rounded-3" placeholder="e.g. ❄️ Cozy Winter Sweaters - Flat 40% OFF!" value="{{ old('title', '❄️ Cozy Winter Sweaters - Flat 40% OFF!') }}" required maxlength="255">
                        <div class="form-text small text-muted">Keep headline concise (under 50 characters recommended for mobile lock-screen readability).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Message Body <span class="text-danger">*</span></label>
                        <textarea name="body" id="pushBodyInput" class="form-control rounded-3" rows="3" placeholder="e.g. Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!" required maxlength="1000">{{ old('body', 'Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!') }}</textarea>
                        <div class="form-text small text-muted">Displays directly on user phone notification center and banner alert.</div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold text-dark small">Rich Banner Image URL (Optional)</label>
                        <input type="url" name="banner_image" id="pushImageInput" class="form-control rounded-3" placeholder="https://your-domain.com/storage/banners/winter-sale.jpg" value="{{ old('banner_image') }}">
                        <div class="form-text small text-muted">Direct image URL (JPG/PNG). Displayed prominently in expanded push notifications on Android &amp; iOS.</div>
                    </div>
                </div>
            </div>

            <!-- 2. Audience Segmentation & Regional Language Matching Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-people-fill text-info fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">2. Audience Segmentation &amp; Language Matching</h6>
                    </div>
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small">Smart Targeting</span>
                </div>
                <div class="card-body p-4">
                    <!-- Language & Regional Pincode Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Language &amp; Regional Pincode Targeting</label>
                        <select name="target_language" id="languageSelect" class="form-select rounded-3">
                            <option value="all" selected>🌐 All Languages (Pan-India Broadcast)</option>
                            <option value="te">🗣️ Telugu (తెలుగు) — AP &amp; Telangana (Pincodes 50-53xxx)</option>
                            <option value="hi">🗣️ Hindi (हिंदी) — North &amp; Central India</option>
                            <option value="en">🗣️ English Only (Global / Pan-India)</option>
                            <option value="ta">🗣️ Tamil (தமிழ்) — Tamil Nadu (Pincodes 60-64xxx)</option>
                            <option value="kn">🗣️ Kannada (ಕನ್ನಡ) — Karnataka (Pincodes 56-59xxx)</option>
                        </select>
                        <div class="form-text small text-muted">
                            <i class="bi bi-geo-alt me-1 text-primary"></i> Automatically filters users by their chosen profile language or mapped delivery pincode states.
                        </div>
                    </div>

                    <!-- Gender Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Gender Segmentation</label>
                        <select name="target_gender" id="genderSelect" class="form-select rounded-3">
                            <option value="all" selected>All Genders (Broadcast to everyone)</option>
                            <option value="Female">Female Profiles Only (Dresses, Beauty, Women's Fashion)</option>
                            <option value="Male">Male Profiles Only (Men's Grooming, Watches, Menswear)</option>
                        </select>
                        <div class="form-text small text-muted">Targeted based on user's registered customer profile gender.</div>
                    </div>

                    <!-- Age / Birthday Filter -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Age / Birthday Criteria</label>
                        <select name="target_age_group" id="ageSelect" class="form-select rounded-3">
                            <option value="all" selected>All Ages (No DOB restrictions)</option>
                            <option value="birthday_month">🎂 Users with Birthday This Month (Special Birthday Treat)</option>
                            <option value="under_25">Under 25 Years (Gen-Z &amp; Trendy College Collections)</option>
                            <option value="25_40">25 - 40 Years (Young Professionals &amp; Families)</option>
                            <option value="over_40">Over 40 Years (Classic &amp; Mature Segment)</option>
                        </select>
                        <div class="form-text small text-muted">Dynamically filtered using the registered date of birth in verified customer profiles.</div>
                    </div>

                    <!-- Activity Status -->
                    <div class="mb-1">
                        <label class="form-label fw-semibold text-dark small">User Engagement &amp; Activity</label>
                        <select name="target_activity" id="activitySelect" class="form-select rounded-3">
                            <option value="all" selected>All Active Devices (Full Reach)</option>
                            <option value="dormant">Dormant Users Only (No orders in the last 30 days - Re-engagement winback)</option>
                        </select>
                        <div class="form-text small text-muted">Identify inactive customers and re-engage them with compelling seasonal offers.</div>
                    </div>
                </div>
            </div>

            <!-- 3. Destination Deep Link Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-in-up-right text-success fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">3. Mobile App Destination (Deep Link)</h6>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">In-App Routing</span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Tapping Notification Opens <span class="text-danger">*</span></label>
                        <select name="destination_type" id="destinationType" class="form-select rounded-3" required>
                            <option value="home" selected>App Home Screen</option>
                            <option value="offers">Offers &amp; Deals Hub</option>
                            <option value="category">Specific Category</option>
                            <option value="cart">Cart Screen (Checkout Nudge)</option>
                        </select>
                        <div class="form-text small text-muted">When customer taps this push on their phone lock-screen, the app will instantly launch this page.</div>
                    </div>

                    <!-- Specific Category Picker (Conditional) -->
                    <div class="mb-1 d-none" id="categoryGroup">
                        <label class="form-label fw-semibold text-dark small">Select Destination Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="categorySelect" class="form-select rounded-3">
                            <option value="" disabled selected>-- Choose Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mb-5">
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow" onclick="return confirm('Broadcast this push notification alert to all matching active app devices now?');">
                        <i class="bi bi-send-fill me-2"></i> Broadcast Push Alert Now
                    </button>
                    <div class="form-text text-muted small mt-2">
                        <i class="bi bi-shield-check text-primary me-1"></i> Logged in as Super Admin. Your push broadcast will be dispatched immediately.
                    </div>
                @else
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow" onclick="return confirm('Submit this push notification draft for Super Admin approval?');">
                        <i class="bi bi-send-check-fill me-2"></i> Submit for Super Admin Approval
                    </button>
                    <div class="form-text text-muted small mt-2">
                        <i class="bi bi-info-circle text-primary me-1"></i> Push notification will be queued for Super Admin review before broadcasting to customer apps.
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Live Phone Mockup Preview -->
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 85px; z-index: 10;">
                <!-- Targeting Summary Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                    <div class="card-body p-3.5">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle text-primary fs-5"></i>
                            <span class="fw-bold text-dark small text-uppercase">Targeting Criteria</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1.5" id="targetBadgeSummary">
                            <span class="badge bg-primary px-2.5 py-1 rounded-pill small" id="badgeGender">All Genders</span>
                            <span class="badge bg-secondary px-2.5 py-1 rounded-pill small" id="badgeAge">All Ages</span>
                            <span class="badge bg-info text-dark px-2.5 py-1 rounded-pill small" id="badgeLanguage">All Languages</span>
                            <span class="badge bg-success px-2.5 py-1 rounded-pill small" id="badgeDestination">Opens Home</span>
                        </div>
                    </div>
                </div>

                <!-- Phone Frame -->
                <div class="phone-mockup-wrapper mx-auto">
                    <div class="phone-frame shadow-lg">
                        <div class="phone-notch"></div>
                        <div class="phone-screen">
                            <!-- Status Bar -->
                            <div class="phone-status-bar d-flex justify-content-between px-4 pt-3 text-white small">
                                <span class="fw-bold" id="phoneTime">09:41</span>
                                <div class="d-flex gap-1 align-items-center">
                                    <i class="bi bi-reception-4"></i>
                                    <i class="bi bi-wifi"></i>
                                    <i class="bi bi-battery-full"></i>
                                </div>
                            </div>

                            <!-- Lock Screen Clock -->
                            <div class="text-center text-white my-4">
                                <div class="display-5 fw-bold" id="phoneClock" style="letter-spacing: -1px;">09:41</div>
                                <div class="small opacity-75">{{ date('l, F j') }}</div>
                            </div>

                            <!-- Floating iOS Push Banner Preview -->
                            <div class="mx-3 p-3 rounded-4 phone-notification-card text-dark shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <div class="rounded-2 p-1 text-white d-flex align-items-center justify-content-center" style="background-color: #ec4899; width: 20px; height: 20px;">
                                            <i class="bi bi-bag-fill" style="font-size: 10px;"></i>
                                        </div>
                                        <span class="fw-bold" style="font-size: 11px; letter-spacing: 0.3px;">SHOPCALM</span>
                                    </div>
                                    <span class="text-muted" style="font-size: 10px;">now</span>
                                </div>

                                <div class="fw-bold text-dark small mb-1" id="previewTitle" style="line-height: 1.3;">
                                    ❄️ Cozy Winter Sweaters - Flat 40% OFF!
                                </div>

                                <div class="text-secondary small mb-2" id="previewBody" style="font-size: 12px; line-height: 1.35;">
                                    Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!
                                </div>

                                <div class="notification-banner-preview rounded-3 overflow-hidden d-none" id="previewBannerContainer">
                                    <img src="" id="previewBannerImg" class="w-100" style="max-height: 140px; object-fit: cover;" alt="Notification Banner">
                                </div>
                            </div>

                            <div class="phone-home-indicator"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Broadcast History Table Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Campaign Submission &amp; Sent Log</h6>
        </div>
        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace small">
            Total Records: {{ $notifications->total() }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4" style="width: 30%;">Notification Message</th>
                    <th>Type</th>
                    <th>Audience &amp; Language</th>
                    <th>Destination</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Delivered</th>
                    <th>Submitted By</th>
                    <th class="text-end pe-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $item)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-start gap-2.5">
                                @if($item->banner_image)
                                    <img src="{{ $item->banner_image }}" class="rounded-3 border" style="width: 44px; height: 44px; object-fit: cover;" alt="Banner">
                                @else
                                    <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center text-primary" style="width: 44px; height: 44px; min-width: 44px;">
                                        <i class="bi bi-bell fs-5"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $item->title }}</div>
                                    <div class="text-secondary small text-truncate" style="max-width: 300px;" title="{{ $item->body }}">
                                        {{ $item->body }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($item->campaign_type === 'birthday')
                                <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill small">🎂 Birthday</span>
                            @elseif($item->campaign_type === 'seasonal')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" style="background:#ffe4e6;">🌸 Festive</span>
                            @elseif($item->campaign_type === 'flash_sale')
                                <span class="badge bg-danger text-white rounded-pill small">⚡ Flash Sale</span>
                            @elseif($item->campaign_type === 'dormant')
                                <span class="badge bg-dark text-white rounded-pill small">👋 Win-Back</span>
                            @elseif($item->campaign_type === 'wallet_expiry')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">💰 Wallet</span>
                            @else
                                <span class="badge bg-light text-secondary border rounded-pill small">🎯 Custom</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <!-- Language Tag -->
                                @if($item->target_language === 'te')
                                    <span class="badge bg-primary text-white rounded-pill small">తెలుగు</span>
                                @elseif($item->target_language === 'hi')
                                    <span class="badge bg-warning text-dark rounded-pill small">हिंदी</span>
                                @elseif($item->target_language === 'ta')
                                    <span class="badge bg-info text-dark rounded-pill small">தமிழ்</span>
                                @elseif($item->target_language === 'kn')
                                    <span class="badge bg-secondary text-white rounded-pill small">ಕನ್ನಡ</span>
                                @endif

                                @if($item->target_gender === 'Male')
                                    <span class="badge bg-info-subtle text-info rounded-pill small">Male</span>
                                @elseif($item->target_gender === 'Female')
                                    <span class="badge bg-danger-subtle text-danger rounded-pill small">Female</span>
                                @endif

                                @if($item->target_age_group === 'birthday_month')
                                    <span class="badge bg-warning-subtle text-warning rounded-pill small">Birthday Month</span>
                                @elseif($item->target_age_group === 'under_25')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">&lt; 25 Yrs</span>
                                @elseif($item->target_age_group === '25_40')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">25-40 Yrs</span>
                                @elseif($item->target_age_group === 'over_40')
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small">40+ Yrs</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($item->destination_type === 'category' && $item->category)
                                <span class="badge bg-primary-subtle text-primary rounded-pill">📁 {{ $item->category->name }}</span>
                            @elseif($item->destination_type === 'offers')
                                <span class="badge bg-warning-subtle text-warning rounded-pill">🏷️ Offers Hub</span>
                            @elseif($item->destination_type === 'cart')
                                <span class="badge bg-success-subtle text-success rounded-pill">🛒 Cart Screen</span>
                            @else
                                <span class="badge bg-light text-dark border rounded-pill">🏠 App Home</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($item->isPending())
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small">
                                    <i class="bi bi-hourglass-split me-1"></i> Pending Review
                                </span>
                            @elseif($item->isApproved())
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                    <i class="bi bi-check2-circle me-1"></i> Broadcast
                                </span>
                            @elseif($item->isRejected())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small" title="{{ $item->rejection_reason }}">
                                    <i class="bi bi-x-circle me-1"></i> Rejected
                                </span>
                                @if($item->rejection_reason)
                                    <small class="text-danger d-block mt-1 text-truncate" style="font-size: 10px; max-width: 140px;" title="{{ $item->rejection_reason }}">
                                        Note: {{ $item->rejection_reason }}
                                    </small>
                                @endif
                            @endif
                        </td>

                        <td class="text-center">
                            @if($item->isApproved())
                                <span class="fw-bold text-dark">{{ number_format($item->recipient_count) }}</span>
                                <small class="text-muted d-block" style="font-size: 11px;">phones</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>

                        <td>
                            <small class="fw-semibold text-dark">{{ optional($item->sender)->name ?? 'Marketing Staff' }}</small>
                            @if($item->isApproved() && $item->approver)
                                <small class="text-muted d-block" style="font-size: 10px;">Approved by {{ $item->approver->name }}</small>
                            @endif
                        </td>

                        <td class="text-end pe-4 text-nowrap">
                            <span class="text-dark small fw-semibold">{{ $item->created_at->format('d M Y') }}</span>
                            <small class="text-muted d-block" style="font-size: 11px;">{{ $item->created_at->format('h:i A') }}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            No push alerts submitted yet. Pick a preset above to compose your first campaign!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($notifications->hasPages())
        <div class="card-footer bg-white border-top py-3">
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

    const previewTitle = document.getElementById('previewTitle');
    const previewBody = document.getElementById('previewBody');
    const previewBannerContainer = document.getElementById('previewBannerContainer');
    const previewBannerImg = document.getElementById('previewBannerImg');

    const badgeGender = document.getElementById('badgeGender');
    const badgeAge = document.getElementById('badgeAge');
    const badgeLanguage = document.getElementById('badgeLanguage');
    const badgeDestination = document.getElementById('badgeDestination');

    // ⚡ Campaign Presets Data (Multi-Lingual)
    const presets = {
        custom: {
            en: { title: '❄️ Cozy Winter Sweaters - Flat 40% OFF!', body: 'Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!' },
            te: { title: '❄️ వింటర్ స్వెటర్లపై ఫ్లాట్ 40% తగ్గింపు!', body: 'ఈ సీజన్‌లో వెచ్చగా ఉండే స్వెటర్లు, కార్డిగాన్స్ మరియు జాకెట్లు. స్టాక్ ముగిసేలోపే ఇప్పుడే కొనండి!' },
            hi: { title: '❄️ विंटर स्वेटर्स पर फ्लैट 40% छूट!', body: 'इस सीज़न में स्टाइलिश और गर्म स्वेटर, कार्डिगन और जैकेट पाएँ। स्टॉक खत्म होने से पहले अभी खरीदें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'home'
        },
        birthday: {
            en: { title: '🎂 Happy Birthday Month from ShopCalm!', body: 'Celebrate with an exclusive birthday gift discount just for you. Tap to claim your treat! 🎁' },
            te: { title: '🎂 జన్మదిన శుభాకాంక్షలు! మీ బర్త్‌డే గిఫ్ట్ ఇక్కడ ఉంది 🎁', body: 'మీ బర్త్‌డే స్పెషల్ గిఫ్ట్ డిస్కౌంట్‌తో షాపింగ్ చేసుకోండి. ఆఫర్ ఈ నెల మాత్రమే అందుబాటులో ఉంటుంది!' },
            hi: { title: '🎂 जन्मदिन की हार्दिक शुभकामनाएँ! आपका बर्थडे गिफ्ट यहाँ है 🎁', body: 'अपने जन्मदिन के विशेष डिस्काउंट का आनंद लें। आज ही अपने पसंदीदा सामान ऑर्डर करें!' },
            gender: 'all', age: 'birthday_month', activity: 'all', destination: 'offers'
        },
        seasonal: {
            en: { title: '✨ Festive Season Mega Deals Are Live!', body: 'Explore trending festive styles, ethnic wear, and exclusive bundle offers. Tap to shop now! 🛍️' },
            te: { title: '✨ పండుగ స్పెషల్ మెగా ఆఫర్లు లైవ్ అయ్యాయి! 🛍️', body: 'ట్రెండింగ్ ఎత్నిక్ వేర్, ఫెస్టివ్ కలెక్షన్స్ మరియు ప్రత్యేక కాంబో ఆఫర్లు. స్టాక్ ముగిసేలోపే ఇప్పుడే చూడండి!' },
            hi: { title: '✨ फेस्टिव सीजन महा सेल लाइव! 🛍️', body: 'शानदार एथनिक वियर, फेस्टिव स्टाइल्स और विशेष बंडल्स पर भारी छूट पाएँ। अभी टैप करें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'offers'
        },
        flash_sale: {
            en: { title: '⚡ 3-Hour Lightning Flash Sale - Flat 40% OFF!', body: 'Tick-tock! Limited inventory flash sale is live now across top categories. Tap to grab before stock runs out!' },
            te: { title: '⚡ 3 గంటల లైట్నింగ్ ఫ్లాష్ సేల్ - ఫ్లాట్ 40% తగ్గింపు!', body: 'సమయం వేగంగా ముగుస్తోంది! పరిమిత స్టాక్ మాత్రమే అందుబాటులో ఉంది. ఇప్పుడే ఆర్డర్ చేయండి!' },
            hi: { title: '⚡ 3 घंटे की लाइटनिंग फ्लैश सेल - फ्लैट 40% छूट!', body: 'समय सीमित है! बेहतरीन प्रोडक्ट्स पर भारी डिस्काउंट पाएँ। तुरंत टैप करें और बचत करें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'offers'
        },
        dormant: {
            en: { title: '👋 We miss you! Here is an exclusive comeback treat', body: 'It has been a while since your last visit. We have added fresh arrivals and a special comeback deal just for you!' },
            te: { title: '👋 మేము మిమ్మల్ని మిస్ అవుతున్నాము! మీ కోసం ప్రత్యేక ఆఫర్', body: 'మీరు విజిట్ చేసి చాలా రోజులయ్యింది. మీ కోసం సరికొత్త కలెక్షన్లు మరియు స్పెషల్ కమ్‌బ్యాక్ ఆఫర్ సిద్ధంగా ఉన్నాయి!' },
            hi: { title: '👋 हम आपको मिस कर रहे हैं! खास वेलकम बैक ट्रीट आपके लिए', body: 'बहुत दिन हो गए! आपके लिए नए स्टाइल्स और विशेष वेलकम-बैक डिस्काउंट तैयार हैं।' },
            gender: 'all', age: 'all', activity: 'dormant', destination: 'offers'
        },
        wallet_expiry: {
            en: { title: '💰 Don\'t let your ShopCalm Cash expire!', body: 'You have unused wallet reward credits in your account. Apply your wallet balance on your order today!' },
            te: { title: '💰 మీ ShopCalm క్యాష్ వాలెట్ క్రెడిట్స్ గడువు ముగియకముందే వాడండి!', body: 'మీ వాలెట్ ఖాతాలో రివార్డ్ క్యాష్ సిద్ధంగా ఉంది. మీ తదుపరి ఆర్డర్‌పై ఈ రోజే వర్తింపజేయండి!' },
            hi: { title: '💰 अपने ShopCalm वॉलेट बैलेंस को एक्सपायर होने से पहले इस्तेमाल करें!', body: 'आपके खाते में अनयूज्ड वॉलेट रिवॉर्ड्स हैं। आज ही अपने ऑर्डर पर वॉलेट बैलेंस का इस्तेमाल करें!' },
            gender: 'all', age: 'all', activity: 'all', destination: 'offers'
        }
    };

    let activePresetKey = 'custom';

    // Preset button click handling
    document.querySelectorAll('.preset-pill-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.preset-pill-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            activePresetKey = this.dataset.preset;
            campaignTypeInput.value = activePresetKey;
            applyPresetData();
        });
    });

    function applyPresetData() {
        const preset = presets[activePresetKey] || presets.custom;
        const currentLang = languageSelect.value;
        const langKey = (currentLang === 'te' || currentLang === 'hi') ? currentLang : 'en';

        const textData = preset[langKey] || preset.en;
        pushTitleInput.value = textData.title;
        pushBodyInput.value = textData.body;

        genderSelect.value = preset.gender || 'all';
        ageSelect.value = preset.age || 'all';
        activitySelect.value = preset.activity || 'all';
        destinationType.value = preset.destination || 'home';

        categoryGroup.classList.toggle('d-none', destinationType.value !== 'category');

        previewTitle.textContent = textData.title;
        previewBody.textContent = textData.body;
        updateBadgeSummary();
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
        previewTitle.textContent = this.value.trim() || '❄️ Cozy Winter Sweaters - Flat 40% OFF!';
    });

    pushBodyInput.addEventListener('input', function () {
        previewBody.textContent = this.value.trim() || 'Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!';
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
        updateBadgeSummary();
    });

    function updateBadgeSummary() {
        if (!badgeGender || !badgeAge || !badgeDestination || !badgeLanguage) return;
        const gVal = genderSelect.value;
        badgeGender.textContent = gVal === 'Male' ? '👨 Male Only' : (gVal === 'Female' ? '👩 Female Only' : '👥 All Genders');

        const aVal = ageSelect.value;
        badgeAge.textContent = ageSelect.options[ageSelect.selectedIndex].text;

        const lVal = languageSelect.value;
        badgeLanguage.textContent = languageSelect.options[languageSelect.selectedIndex].text.split('—')[0].trim();

        const dVal = destinationType.value;
        if (dVal === 'category') {
            const catText = categorySelect.options[categorySelect.selectedIndex]?.text || 'Category';
            badgeDestination.textContent = `📁 Opens: ${catText}`;
        } else if (dVal === 'offers') {
            badgeDestination.textContent = '🏷️ Opens Offers';
        } else if (dVal === 'cart') {
            badgeDestination.textContent = '🛒 Opens Cart';
        } else {
            badgeDestination.textContent = '🏠 Opens Home';
        }
    }

    genderSelect.addEventListener('change', updateBadgeSummary);
    ageSelect.addEventListener('change', updateBadgeSummary);
    categorySelect.addEventListener('change', updateBadgeSummary);
    updateBadgeSummary();
});
</script>
@endpush
