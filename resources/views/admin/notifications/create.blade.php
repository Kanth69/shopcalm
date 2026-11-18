@extends('admin.layouts.app')

@section('header', 'Compose App Push Notification')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Push Notifications</a></li>
    <li class="breadcrumb-item active" aria-current="page">Compose</li>
@endsection

@section('actions')
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Back to History
    </a>
@endsection

@section('content')
<form action="{{ route('admin.notifications.send') }}" method="POST" id="pushForm">
    @csrf

    <div class="row g-4">
        <!-- LEFT COLUMN: Composer & Audience Filtration -->
        <div class="col-lg-7">
            <!-- 1. Notification Content Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-chat-square-text-fill text-primary fs-5"></i>
                        <h6 class="mb-0 fw-bold text-dark">1. Notification Content</h6>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 small">Step 1</span>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Push Alert Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="pushTitleInput" class="form-control @error('title') is-invalid @enderror" 
                            placeholder="e.g., ❄️ Cozy Winter Sweaters - Flat 40% OFF!" 
                            value="{{ old('title') }}" required>
                        <div class="form-text text-muted">Displays bold on customer's phone lock screen.</div>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Message Body <span class="text-danger">*</span></label>
                        <textarea name="body" id="pushBodyInput" rows="3" class="form-control @error('body') is-invalid @enderror" 
                            placeholder="Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!" required>{{ old('body') }}</textarea>
                        <div class="form-text text-muted">Keep it crisp (under 120 characters) for maximum glance readability.</div>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold text-dark">Rich Notification Image URL (Optional)</label>
                        <input type="url" name="banner_image" id="pushImageInput" class="form-control @error('banner_image') is-invalid @enderror" 
                            placeholder="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600" 
                            value="{{ old('banner_image') }}">
                        <div class="form-text text-muted">Direct image URL for expandable rich notification banner on Android & iOS.</div>
                        @error('banner_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- 2. Audience Filtration Card (Gender, DOB / Age, Activity) -->
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
                            <select name="target_gender" id="genderSelect" class="form-select @error('target_gender') is-invalid @enderror" required>
                                <option value="all" {{ old('target_gender') == 'all' ? 'selected' : '' }}>👥 All Genders (Everyone)</option>
                                <option value="Male" {{ old('target_gender') == 'Male' ? 'selected' : '' }}>👨 Male Only</option>
                                <option value="Female" {{ old('target_gender') == 'Female' ? 'selected' : '' }}>👩 Female Only</option>
                            </select>
                            <div class="form-text text-muted">Target gender-specific product lines (e.g. Sarees vs Men's Shoes).</div>
                            @error('target_gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Date of Birth / Age Filter -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Date of Birth & Age Filter <span class="text-danger">*</span></label>
                            <select name="target_age_group" id="ageSelect" class="form-select @error('target_age_group') is-invalid @enderror" required>
                                <option value="all" {{ old('target_age_group') == 'all' ? 'selected' : '' }}>🎂 All Age Groups</option>
                                <option value="birthday_month" {{ old('target_age_group') == 'birthday_month' ? 'selected' : '' }}>🎉 Birthday This Month (Special Wishes)</option>
                                <option value="under_25" {{ old('target_age_group') == 'under_25' ? 'selected' : '' }}>🎒 Under 25 Yrs (Gen-Z / College)</option>
                                <option value="25_40" {{ old('target_age_group') == '25_40' ? 'selected' : '' }}>💼 25 - 40 Yrs (Young Professionals)</option>
                                <option value="over_40" {{ old('target_age_group') == 'over_40' ? 'selected' : '' }}>🏡 40+ Yrs (Mature / Families)</option>
                            </select>
                            <div class="form-text text-muted">Filtered by DOB on customer profiles.</div>
                            @error('target_age_group')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- User Activity Status -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark">User Activity Filter <span class="text-danger">*</span></label>
                            <select name="target_activity" id="activitySelect" class="form-select @error('target_activity') is-invalid @enderror" required>
                                <option value="all" {{ old('target_activity') == 'all' ? 'selected' : '' }}>All Active App Devices ({{ number_format($totalActiveDevices) }} Registered Phones)</option>
                                <option value="dormant" {{ old('target_activity') == 'dormant' ? 'selected' : '' }}>💤 Dormant Users Only (Haven't ordered in 30 days - Win-Back Push)</option>
                            </select>
                            @error('target_activity')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                        <select name="destination_type" id="destinationType" class="form-select @error('destination_type') is-invalid @enderror" required>
                            <option value="home" {{ old('destination_type') == 'home' ? 'selected' : '' }}>🏠 Storefront Home Page</option>
                            <option value="offers" {{ old('destination_type') == 'offers' ? 'selected' : '' }}>🏷️ Deals & Offers Hub</option>
                            <option value="category" {{ old('destination_type') == 'category' ? 'selected' : '' }}>📁 Specific Category Screen</option>
                            <option value="cart" {{ old('destination_type') == 'cart' ? 'selected' : '' }}>🛒 Cart Screen</option>
                        </select>
                        @error('destination_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Category Dropdown (Conditional) -->
                    <div class="mb-0 d-none" id="categoryGroup">
                        <label class="form-label fw-bold text-dark">Select Target Category</label>
                        <select name="category_id" id="categorySelect" class="form-select">
                            <option value="">Choose category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex align-items-center justify-content-between mb-5">
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow" onclick="return confirm('Broadcast this push notification alert to all matching active app devices now?');">
                    <i class="bi bi-send-fill me-2"></i> Broadcast Push Alert Now
                </button>
            </div>
        </div>

        <!-- RIGHT COLUMN: Interactive Smartphone Lock Screen Mockup -->
        <div class="col-lg-5">
            <div class="sticky-top" style="top: 24px; z-index: 10;">
                <!-- Audience Summary Card -->
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

                <!-- Phone Lock Screen Mockup -->
                <div class="phone-mockup-wrapper mx-auto">
                    <div class="phone-frame shadow-lg">
                        <!-- Dynamic Island / Speaker Notch -->
                        <div class="phone-notch"></div>

                        <!-- Phone Screen -->
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

                            <!-- Notification Card (Live Updating) -->
                            <div class="phone-notification-card mx-3 p-3 rounded-4 shadow-sm">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="app-icon-badge rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
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

                                <!-- Banner Preview -->
                                <div class="notification-banner-preview rounded-3 overflow-hidden d-none" id="previewBannerContainer">
                                    <img src="" id="previewBannerImg" class="w-100" style="max-height: 140px; object-fit: cover;" alt="Notification Banner">
                                </div>
                            </div>

                            <!-- Bottom Phone Swipe Bar -->
                            <div class="phone-home-indicator"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.phone-mockup-wrapper {
    max-width: 330px;
}
.phone-frame {
    width: 320px;
    height: 640px;
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
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 110px;
    height: 24px;
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
.app-icon-badge {
    width: 22px;
    height: 22px;
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

    // Phone Clock
    function updatePhoneClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const mins = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('phoneTime').textContent = `${hours}:${mins}`;
        document.getElementById('phoneClock').textContent = `${hours}:${mins}`;
    }
    updatePhoneClock();
    setInterval(updatePhoneClock, 30000);

    // Live Title
    pushTitleInput.addEventListener('input', function () {
        previewTitle.textContent = this.value.trim() || '❄️ Cozy Winter Sweaters - Flat 40% OFF!';
    });

    // Live Body
    pushBodyInput.addEventListener('input', function () {
        previewBody.textContent = this.value.trim() || 'Stay warm this season with cozy knits, cardigans, and jackets. Tap now to shop before stock sells out!';
    });

    // Live Banner Image
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

    // Destination toggle
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
@endsection
