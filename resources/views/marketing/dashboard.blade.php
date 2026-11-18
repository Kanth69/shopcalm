<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShopCalm — Marketing & Push Notifications Workspace</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }

        .navbar-marketing {
            background: linear-gradient(90deg, #180d28 0%, #2e1065 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

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

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<!-- Header Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-marketing py-3 shadow-sm sticky-top">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-pink p-2 d-flex align-items-center justify-content-center text-white" style="background-color: #ec4899; width: 36px; height: 36px;">
                <i class="bi bi-megaphone-fill small"></i>
            </div>
            <div>
                <a class="navbar-brand fw-bold mb-0 text-white" href="{{ route('marketing.dashboard') }}">ShopCalm</a>
                <span class="badge bg-white bg-opacity-20 text-white rounded-pill ms-1 small">Marketing Workspace</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="d-none d-md-block text-end">
                <div class="text-white small fw-bold">{{ auth('marketing')->user()->name ?? (auth()->user()->name ?? 'Marketing Staff') }}</div>
                <small class="text-white-50" style="font-size: 11px;">Marketing & Growth Manager</small>
            </div>

            <form action="{{ route('marketing.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">
    <!-- Flash Toast / Alerts -->
    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Audience Scorecards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">Active Devices</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill p-2"><i class="bi bi-phone-fill fs-6"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalActiveDevices) }}</h3>
                <small class="text-muted">Registered Customer Apps</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">Male Users</span>
                    <span class="badge bg-info-subtle text-info rounded-pill p-2"><i class="bi bi-gender-male fs-6"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($maleDevices) }}</h3>
                <small class="text-muted">Targetable Male Profiles</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">Female Users</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill p-2" style="background-color: #ffe4e6;"><i class="bi bi-gender-female fs-6"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($femaleDevices) }}</h3>
                <small class="text-muted">Targetable Female Profiles</small>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">Total Broadcasts</span>
                    <span class="badge bg-purple-subtle text-purple rounded-pill p-2"><i class="bi bi-broadcast fs-6"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalBroadcasts) }}</h3>
                <small class="text-muted">Push Alerts Sent</small>
            </div>
        </div>

        <div class="col-sm-12 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">Pushes Delivered</span>
                    <span class="badge bg-success-subtle text-success rounded-pill p-2"><i class="bi bi-check2-all fs-6"></i></span>
                </div>
                <h3 class="fw-bold mb-0 text-dark">{{ number_format($totalDelivered) }}</h3>
                <small class="text-muted">Delivered to Phones</small>
            </div>
        </div>
    </div>

    <!-- Workspace Tabs -->
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <button class="nav-link active rounded-pill px-4 fw-bold" id="pushes-tab" data-bs-toggle="pill" data-bs-target="#pushes-content" type="button">
                <i class="bi bi-bell-fill me-1"></i> App Push Notifications
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill px-4 fw-semibold text-muted" id="urls-tab" data-bs-toggle="pill" data-bs-target="#urls-content" type="button">
                <i class="bi bi-link-45deg me-1"></i> Digital Marketing URLs <span class="badge bg-secondary-subtle text-secondary ms-1 rounded-pill small">On Hold</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="marketingTabContent">
        <!-- TAB 1: App Push Notifications Composer & History -->
        <div class="tab-pane fade show active" id="pushes-content">
            <form action="{{ route('marketing.notifications.send') }}" method="POST" id="pushBroadcastForm">
                @csrf

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
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small">Step 1</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Push Alert Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="pushTitleInput" class="form-control" 
                                        placeholder="e.g., ❄️ Cozy Winter Sweaters - Flat 40% OFF!" 
                                        value="{{ old('title') }}" required>
                                    <div class="form-text text-muted">Displays prominently on the customer's lock screen.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Message Body <span class="text-danger">*</span></label>
                                    <textarea name="body" id="pushBodyInput" rows="3" class="form-control" 
                                        placeholder="Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!" required>{{ old('body') }}</textarea>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold text-dark">Rich Notification Image URL (Optional)</label>
                                    <input type="url" name="banner_image" id="pushImageInput" class="form-control" 
                                        placeholder="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" 
                                        value="{{ old('banner_image') }}">
                                    <div class="form-text text-muted">Direct image URL for expandable banner pushes on Android & iOS.</div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Audience Segmentation & User Filters Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-funnel-fill text-danger fs-5"></i>
                                    <h6 class="mb-0 fw-bold text-dark">2. Audience Segmentation & User Filters</h6>
                                </div>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 small">Step 2</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Gender Filter -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">Target Gender <span class="text-danger">*</span></label>
                                        <select name="target_gender" id="genderSelect" class="form-select" required>
                                            <option value="all" selected>👥 All Genders (Everyone)</option>
                                            <option value="Male">👨 Male Only</option>
                                            <option value="Female">👩 Female Only</option>
                                        </select>
                                        <div class="form-text text-muted">Filtered by gender in customer profiles.</div>
                                    </div>

                                    <!-- Date of Birth / Age Filter -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark">Date of Birth & Age Filter <span class="text-danger">*</span></label>
                                        <select name="target_age_group" id="ageSelect" class="form-select" required>
                                            <option value="all" selected>🎂 All Age Groups</option>
                                            <option value="birthday_month">🎉 Birthday This Month (Special Wishes)</option>
                                            <option value="under_25">🎒 Under 25 Yrs (Gen-Z / College)</option>
                                            <option value="25_40">💼 25 - 40 Yrs (Young Professionals)</option>
                                            <option value="over_40">🏡 40+ Yrs (Mature / Families)</option>
                                        </select>
                                        <div class="form-text text-muted">Filtered by DOB on customer profiles.</div>
                                    </div>

                                    <!-- Activity Filter -->
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-dark">User Activity Filter <span class="text-danger">*</span></label>
                                        <select name="target_activity" id="activitySelect" class="form-select" required>
                                            <option value="all" selected>All Active App Devices ({{ number_format($totalActiveDevices) }} Registered Phones)</option>
                                            <option value="dormant">💤 Dormant Users Only (Haven't ordered in 30 days - Win-Back Push)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Target In-App Destination Card -->
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-in-up-right text-success fs-5"></i>
                                    <h6 class="mb-0 fw-bold text-dark">3. Target In-App Destination (On Tap)</h6>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill px-2.5 py-1 small">Step 3</span>
                            </div>
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Where should tapping open in the App? <span class="text-danger">*</span></label>
                                    <select name="destination_type" id="destinationType" class="form-select" required>
                                        <option value="home" selected>🏠 Storefront Home Page</option>
                                        <option value="offers">🏷️ Deals & Offers Hub</option>
                                        <option value="category">📁 Specific Category Screen</option>
                                        <option value="cart">🛒 Cart Screen</option>
                                    </select>
                                </div>

                                <div class="mb-0 d-none" id="categoryGroup">
                                    <label class="form-label fw-bold text-dark">Select Target Category</label>
                                    <select name="category_id" id="categorySelect" class="form-select">
                                        <option value="">Choose category...</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
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
                        <div class="sticky-top" style="top: 90px; z-index: 10;">
                            <!-- Targeting Summary Card -->
                            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="bi bi-info-circle text-primary fs-5"></i>
                                        <span class="fw-bold text-dark small text-uppercase">Targeting Criteria</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-1.5" id="targetBadgeSummary">
                                        <span class="badge bg-primary px-2.5 py-1 rounded-pill small" id="badgeGender">All Genders</span>
                                        <span class="badge bg-secondary px-2.5 py-1 rounded-pill small" id="badgeAge">All Ages</span>
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
                                            <div class="display-3 fw-light" style="letter-spacing: -2px;" id="phoneClock">09:41</div>
                                            <div class="small opacity-75">{{ now()->format('l, F j') }}</div>
                                        </div>

                                        <!-- Live Notification Card -->
                                        <div class="phone-notification-card mx-3 p-3 rounded-4 shadow-sm">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;">
                                                        <i class="bi bi-bag-heart-fill small"></i>
                                                    </div>
                                                    <span class="fw-bold small text-dark">SHOPCALM</span>
                                                </div>
                                                <span class="text-muted" style="font-size: 11px;">now</span>
                                            </div>

                                            <div class="fw-bold text-dark mb-1" id="previewTitle" style="font-size: 13.5px; line-height: 1.3;">
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
                        <h6 class="mb-0 fw-bold text-dark">Broadcast Sent History</h6>
                    </div>
                    <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill font-monospace small">
                        Total Sent: {{ $notifications->total() }}
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4" style="width: 32%;">Notification Message</th>
                                <th>Audience Target</th>
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
                                                <div class="text-secondary small text-truncate" style="max-width: 320px;" title="{{ $item->body }}">
                                                    {{ $item->body }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if($item->target_gender === 'Male')
                                                <span class="badge bg-info-subtle text-info rounded-pill small">Male Only</span>
                                            @elseif($item->target_gender === 'Female')
                                                <span class="badge bg-danger-subtle text-danger rounded-pill small">Female Only</span>
                                            @else
                                                <span class="badge bg-light text-dark border rounded-pill small">All Genders</span>
                                            @endif

                                            @if($item->target_age_group === 'birthday_month')
                                                <span class="badge bg-warning-subtle text-warning rounded-pill small">Birthday Month</span>
                                            @elseif($item->target_age_group === 'under_25')
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small">Under 25</span>
                                            @elseif($item->target_age_group === '25_40')
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small">25 - 40 Yrs</span>
                                            @elseif($item->target_age_group === 'over_40')
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small">40+ Yrs</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        @if($item->destination_type === 'category' && $item->category)
                                            <span class="badge bg-primary-subtle text-primary rounded-pill">Category: {{ $item->category->name }}</span>
                                        @elseif($item->destination_type === 'offers')
                                            <span class="badge bg-warning-subtle text-warning rounded-pill">Offers Hub</span>
                                        @elseif($item->destination_type === 'cart')
                                            <span class="badge bg-success-subtle text-success rounded-pill">Cart Screen</span>
                                        @else
                                            <span class="badge bg-light text-dark border rounded-pill">App Home</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($item->isPending())
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small">
                                                <i class="bi bi-hourglass-split me-1"></i> Pending Review
                                            </span>
                                        @elseif($item->isApproved())
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                                <i class="bi bi-check2-circle me-1"></i> Approved & Broadcast
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
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No push alerts submitted yet. Compose your first campaign draft above!
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
        </div>

        <!-- TAB 2: Digital Marketing URLs (On Hold Placeholder) -->
        <div class="tab-pane fade" id="urls-content">
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                <div class="py-5">
                    <div class="rounded-circle bg-light p-4 d-inline-flex align-items-center justify-content-center text-muted mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-pause-circle fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark">Digital Marketing URLs (Paused)</h4>
                    <p class="text-muted mx-auto" style="max-width: 480px;">
                        Campaign tracking links and vanity URL generation for Instagram, Google Ads, and Influencers are currently on hold. We will activate this tab when you are ready to launch digital ads.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pushTitleInput = document.getElementById('pushTitleInput');
    const pushBodyInput = document.getElementById('pushBodyInput');
    const pushImageInput = document.getElementById('pushImageInput');
    const genderSelect = document.getElementById('genderSelect');
    const ageSelect = document.getElementById('ageSelect');
    const destinationType = document.getElementById('destinationType');
    const categoryGroup = document.getElementById('categoryGroup');
    const categorySelect = document.getElementById('categorySelect');

    const previewTitle = document.getElementById('previewTitle');
    const previewBody = document.getElementById('previewBody');
    const previewBannerContainer = document.getElementById('previewBannerContainer');
    const previewBannerImg = document.getElementById('previewBannerImg');

    const badgeGender = document.getElementById('badgeGender');
    const badgeAge = document.getElementById('badgeAge');
    const badgeDestination = document.getElementById('badgeDestination');

    function updatePhoneClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('phoneTime').textContent = `${hours}:${mins}`;
        document.getElementById('phoneClock').textContent = `${hours}:${mins}`;
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
        const val = this.value;
        categoryGroup.classList.toggle('d-none', val !== 'category');
        updateBadgeSummary();
    });

    function updateBadgeSummary() {
        const gVal = genderSelect.value;
        badgeGender.textContent = gVal === 'Male' ? '👨 Male Only' : (gVal === 'Female' ? '👩 Female Only' : '👥 All Genders');

        const aVal = ageSelect.value;
        badgeAge.textContent = ageSelect.options[ageSelect.selectedIndex].text;

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
</body>
</html>
