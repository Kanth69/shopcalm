@extends('layouts.customer')

@php
    $storeName   = \App\Models\Setting::get('store_name', 'ShopCalm');
    $storePhone  = \App\Models\Setting::get('contact_phone', '+91 98765 43210');
    $storeEmail  = \App\Models\Setting::get('contact_email', 'support@shopcalm.com');
    $storeAddr   = \App\Models\Setting::get('address', 'ShopCalm HQ, Tech Park, Bangalore, India');
    $storeHours  = \App\Models\Setting::get('support_hours', 'Monday – Saturday: 9:00 AM – 8:00 PM');
    $fbUrl       = \App\Models\Setting::get('facebook_url');
    $instaUrl    = \App\Models\Setting::get('instagram_url');
    $twitterUrl  = \App\Models\Setting::get('twitter_url');
    $linkedinUrl = \App\Models\Setting::get('linkedin_url');
@endphp

@section('title', 'Contact Us & Customer Support - ' . $storeName)

@php
    $data = json_decode($page->content ?? '', true);
    if (!$data) {
        $data = [
            'hero_title'    => 'Get in Touch with ' . $storeName,
            'hero_subtitle' => 'Have questions about your order, tracking, or products? Our dedicated support team is here to assist you 24/7.',
            'info_title'    => 'Customer Care & Support',
            'info_subtitle' => 'Send us a message and our team will get back to you within 24 hours.',
            'form_title'    => 'Send us a message'
        ];
    }
@endphp

@push('styles')
<style>
    /* Ultra-clean Minimalist Hero */
    .contact-hero-clean {
        padding: clamp(1.75rem, 4vw, 3rem) 0.5rem clamp(1.25rem, 3vw, 2rem);
        text-align: center;
        position: relative;
    }

    /* Clean Information Panel (Light, high-contrast, comfortable to read) */
    .contact-info-panel-light {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: clamp(1.5rem, 4vw, 3rem) clamp(1.25rem, 3.5vw, 2.25rem);
        height: 100%;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .contact-info-item {
        display: flex;
        align-items: flex-start;
        gap: 1.15rem;
        margin-bottom: 1.5rem;
    }
    .contact-info-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #ede9fe;
        color: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* Clean Form Panel */
    .contact-form-panel-light {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: clamp(1.5rem, 4vw, 3rem) clamp(1.25rem, 3.5vw, 2.5rem);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .contact-input {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 0.92rem;
        color: #0f172a;
        min-height: 46px;
        transition: all 0.2s ease;
    }
    .contact-input:focus {
        background: #ffffff;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        color: #0f172a;
        outline: none;
    }

    .social-touch-pill {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .social-touch-pill:hover {
        background: #ede9fe;
        color: #6366f1;
        border-color: #ddd6fe;
        transform: translateY(-2px);
    }

    @media (max-width: 991.98px) {
        .contact-info-panel-light, .contact-form-panel-light {
            padding: 2rem 1.5rem;
            border-radius: 18px;
        }
    }

    @media (max-width: 575.98px) {
        .contact-info-panel-light, .contact-form-panel-light {
            padding: 1.5rem 1.15rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-3 py-md-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb mb-0" style="font-size: 0.82rem;">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="bi bi-house-door me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><span class="text-muted">Company</span></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Contact Us</li>
        </ol>
    </nav>

    <!-- 1. Hero Header (Clean Minimalist Design) -->
    <div class="contact-hero-clean mb-4 mb-md-5 pb-lg-2">
        <div class="mx-auto" style="max-width: 760px;">
            <div class="mb-3">
                <span class="badge rounded-pill px-3.5 py-1.5 fw-bold text-uppercase d-inline-flex align-items-center gap-1.5 shadow-xs" style="background: linear-gradient(135deg, #ede9fe, #e0e7ff); color: #4f46e5; font-size: 0.76rem; border: 1px solid #c7d2fe; letter-spacing: 0.05em;">
                    <span class="d-inline-block rounded-circle bg-success" style="width: 8px; height: 8px;"></span>
                    <span>Support Desk Online &bull; Fast Response</span>
                </span>
            </div>

            @php
                $heroTitle = $data['hero_title'] ?? ('Get in Touch with ' . $storeName);
                $titleParts = explode(' ', $heroTitle);
                $lastWord = count($titleParts) > 1 ? array_pop($titleParts) : '';
                $firstPart = implode(' ', $titleParts);
            @endphp
            <h1 class="fw-bolder mb-3 text-dark" style="letter-spacing: -0.03em; font-size: clamp(1.6rem, 4vw, 2.4rem); color: #0f172a; line-height: 1.25;">
                @if($lastWord)
                    {{ $firstPart }} <span style="background: linear-gradient(135deg, #6366f1, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">{{ $lastWord }}</span>
                @else
                    {{ $heroTitle }}
                @endif
            </h1>

            <p class="text-muted fs-5 mb-0 mx-auto" style="line-height: 1.65; max-width: 640px; font-size: clamp(0.92rem, 2.5vw, 1.15rem) !important;">
                {{ $data['hero_subtitle'] ?? 'Have questions about your order, tracking, or products? Our dedicated support team is here to assist you 24/7.' }}
            </p>
        </div>
    </div>

    <!-- 2. Contact Main Row -->
    <div class="row g-4 g-lg-5 mb-4 mb-md-5 pb-lg-2">
        <!-- Left: Information Panel (Light & Readable) -->
        <div class="col-lg-5">
            <div class="contact-info-panel-light">
                <div>
                    <div class="mb-4">
                        <x-logo height="36" :showTagline="true" />
                    </div>

                    <h3 class="fw-bolder mb-2" style="color: #0f172a; letter-spacing: -0.02em; font-size: 1.45rem;">
                        {{ $data['info_title'] ?? 'Customer Care & Support' }}
                    </h3>
                    <p class="text-muted mb-4 pb-2" style="line-height: 1.65; font-size: 0.92rem;">
                        {{ $data['info_subtitle'] ?? 'Reach out through any channel below. Our customer support agents respond promptly.' }}
                    </p>

                    <!-- Contact Detail 1: Address -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon-box">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Store Headquarters</div>
                            <div class="fw-semibold text-dark mt-0.5" style="font-size: 0.92rem; color: #1e293b !important;">
                                {{ $storeAddr }}
                            </div>
                        </div>
                    </div>

                    <!-- Contact Detail 2: Helpline -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon-box">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Customer Helpline</div>
                            <a href="tel:{{ $storePhone }}" class="fw-bold text-primary mt-0.5 d-inline-block text-decoration-none" style="font-size: 0.92rem; color: #4f46e5 !important;">
                                {{ $storePhone }}
                            </a>
                        </div>
                    </div>

                    <!-- Contact Detail 3: Email -->
                    <div class="contact-info-item">
                        <div class="contact-info-icon-box">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Official Support Email</div>
                            <a href="mailto:{{ $storeEmail }}" class="fw-bold text-primary mt-0.5 d-inline-block text-decoration-none" style="font-size: 0.92rem; color: #4f46e5 !important;">
                                {{ $storeEmail }}
                            </a>
                        </div>
                    </div>

                    <!-- Contact Detail 4: Hours -->
                    <div class="contact-info-item mb-0">
                        <div class="contact-info-icon-box">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div>
                            <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Operating Hours</div>
                            <div class="fw-semibold text-dark mt-0.5" style="font-size: 0.92rem; color: #1e293b !important;">
                                {{ $storeHours }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Connect -->
                <div class="pt-4 border-top mt-4">
                    <div class="text-muted small mb-2.5 fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Connect on Social Media</div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @if($twitterUrl)<a href="{{ $twitterUrl }}" target="_blank" rel="noopener noreferrer" class="social-touch-pill" title="Twitter / X"><i class="bi bi-twitter-x"></i></a>@endif
                        @if($instaUrl)<a href="{{ $instaUrl }}" target="_blank" rel="noopener noreferrer" class="social-touch-pill" title="Instagram"><i class="bi bi-instagram"></i></a>@endif
                        @if($fbUrl)<a href="{{ $fbUrl }}" target="_blank" rel="noopener noreferrer" class="social-touch-pill" title="Facebook"><i class="bi bi-facebook"></i></a>@endif
                        @if($linkedinUrl)<a href="{{ $linkedinUrl }}" target="_blank" rel="noopener noreferrer" class="social-touch-pill" title="LinkedIn"><i class="bi bi-linkedin"></i></a>@endif
                        @if(!$twitterUrl && !$instaUrl && !$fbUrl && !$linkedinUrl)
                            <a href="javascript:void(0)" class="social-touch-pill" title="Twitter / X"><i class="bi bi-twitter-x"></i></a>
                            <a href="javascript:void(0)" class="social-touch-pill" title="Instagram"><i class="bi bi-instagram"></i></a>
                            <a href="javascript:void(0)" class="social-touch-pill" title="Facebook"><i class="bi bi-facebook"></i></a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Message Form -->
        <div class="col-lg-7">
            <div class="contact-form-panel-light">
                <div class="mb-4 pb-2 border-bottom">
                    <h3 class="fw-bolder mb-1" style="letter-spacing: -0.02em; color: #0f172a; font-size: 1.5rem;">
                        {{ $data['form_title'] ?? 'Send us a message' }}
                    </h3>
                    <p class="text-muted small mb-0" style="font-size: 0.92rem;">
                        Fill out this quick form and our support desk will respond promptly.
                    </p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3" role="alert">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form action="{{ route('page.contact.submit') }}" method="POST" id="customerContactForm">
                    @csrf
                    <div class="row g-3 g-md-4">
                        <div class="col-md-6">
                            <label for="contact_name" class="form-label fw-bold small" style="color: #0f172a;">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control contact-input @error('name') is-invalid @enderror" id="contact_name" name="name" value="{{ old('name', Auth::guard('customer')->user()->name ?? '') }}" placeholder="e.g. John Doe" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="contact_email" class="form-label fw-bold small" style="color: #0f172a;">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control contact-input @error('email') is-invalid @enderror" id="contact_email" name="email" value="{{ old('email', Auth::guard('customer')->user()->email ?? '') }}" placeholder="john@example.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="contact_mobile" class="form-label fw-bold small" style="color: #0f172a;">10-Digit Mobile Number</label>
                            <input type="tel" class="form-control contact-input @error('mobile') is-invalid @enderror" id="contact_mobile" name="mobile" value="{{ old('mobile', Auth::guard('customer')->user()->phone ?? '') }}" placeholder="9876543210" pattern="[0-9]{10}" maxlength="10" minlength="10">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="contact_subject" class="form-label fw-bold small" style="color: #0f172a;">Inquiry Subject <span class="text-danger">*</span></label>
                            <select class="form-select contact-input @error('subject') is-invalid @enderror" id="contact_subject" name="subject" required>
                                <option value="" selected disabled>Select inquiry type</option>
                                <option value="Order & Delivery Tracking" {{ old('subject') === 'Order & Delivery Tracking' ? 'selected' : '' }}>Order & Delivery Tracking</option>
                                <option value="Returns & Refunds Request" {{ old('subject') === 'Returns & Refunds Request' ? 'selected' : '' }}>Returns & Refunds Request</option>
                                <option value="Product Details & Stock" {{ old('subject') === 'Product Details & Stock' ? 'selected' : '' }}>Product Details & Stock</option>
                                <option value="Payment & Discount Coupons" {{ old('subject') === 'Payment & Discount Coupons' ? 'selected' : '' }}>Payment & Discount Coupons</option>
                                <option value="Other Assistance" {{ old('subject') === 'Other Assistance' ? 'selected' : '' }}>Other Assistance</option>
                            </select>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="contact_message" class="form-label fw-bold small" style="color: #0f172a;">Detailed Message <span class="text-danger">*</span></label>
                            <textarea class="form-control contact-input @error('message') is-invalid @enderror" id="contact_message" name="message" rows="5" placeholder="Please describe how our team can help you..." required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" id="contactSubmitBtn" class="btn btn-primary rounded-pill px-4 py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 w-100 w-md-auto" style="font-size: 0.94rem; min-height: 48px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border: none;">
                                <i class="bi bi-send-fill" id="submitIcon"></i> 
                                <span id="submitText">Submit Message</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Attractive Contact Success Modal -->
<div class="modal fade" id="contactSuccessModal" tabindex="-1" aria-labelledby="contactSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden text-center p-4 p-md-5" style="background: linear-gradient(135deg, #ffffff 0%, #faf5ff 100%);">
            <div class="modal-body p-0">
                <!-- Glowing Success Icon Circle -->
                <div class="mb-3.5 d-inline-flex align-items-center justify-content-center p-2 rounded-circle" style="background: rgba(16, 185, 129, 0.12); border: 2px dashed rgba(16, 185, 129, 0.35);">
                    <div class="d-flex align-items-center justify-content-center rounded-circle text-white" style="width: 68px; height: 68px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);">
                        <i class="bi bi-check-lg fs-1 fw-bold"></i>
                    </div>
                </div>

                <h3 class="fw-bolder mb-2 text-dark" id="contactSuccessModalLabel" style="letter-spacing: -0.02em; color: #0f172a;">
                    Message Sent Successfully!
                </h3>

                <p class="text-muted mb-4 mx-auto" style="font-size: 0.95rem; line-height: 1.65; max-width: 360px;">
                    Thank you for reaching out. Our support team has received your enquiry and will get back to you shortly (typically within 24 hours).
                </p>

                <div class="d-inline-flex align-items-center gap-2 bg-light px-3.5 py-1.5 rounded-pill border small fw-semibold text-dark mb-4" style="font-size: 0.8rem;">
                    <i class="bi bi-headset text-primary"></i> 
                    <span>Helpdesk Ticket Logged</span>
                </div>

                <div>
                    <button type="button" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm" data-bs-dismiss="modal" style="font-size: 0.9rem;">
                        Got it, Thanks!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('customerContactForm');
    const submitBtn = document.getElementById('contactSubmitBtn');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    const successModal = new bootstrap.Modal(document.getElementById('contactSuccessModal'));

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear existing invalid states
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback-ajax').forEach(el => el.remove());

        // Set Loading State
        submitBtn.disabled = true;
        submitIcon.className = 'spinner-border spinner-border-sm';
        submitText.textContent = 'Sending message...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                // Reset form
                form.reset();
                // Show attractive modal popup
                successModal.show();
            } else if (response.status === 422 && data.errors) {
                // Display validation errors under fields
                Object.keys(data.errors).forEach(fieldName => {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback invalid-feedback-ajax d-block';
                        errorDiv.textContent = data.errors[fieldName][0];
                        input.parentNode.appendChild(errorDiv);
                    }
                });
            } else {
                alert(data.message || 'Something went wrong. Please try again.');
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            alert('An unexpected error occurred. Please check your connection and try again.');
        })
        .finally(() => {
            // Restore button state
            submitBtn.disabled = false;
            submitIcon.className = 'bi bi-send-fill';
            submitText.textContent = 'Submit Message';
        });
    });
});
</script>
@endpush
@endsection
