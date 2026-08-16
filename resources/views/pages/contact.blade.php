@extends('layouts.customer')

@section('title', 'Contact Us - ShopCalm')

@php
    $data = json_decode($page->content ?? '', true);
    if (!$data) {
        $data = [
            'hero_title' => 'Get in Touch',
            'hero_subtitle' => 'Have a question or need assistance? We\'re here to help.',
            'info_title' => 'Contact Information',
            'info_subtitle' => 'Fill up the form and our team will get back to you within 24 hours.',
            'form_title' => 'Send us a message'
        ];
    }
@endphp

@section('content')
<main class="contact-page">
    
    <!-- Hero Section -->
    <section class="vanilla-contact-hero">
        <div class="vanilla-container">
            <div class="vanilla-logo-wrapper">
                <x-logo height="56" />
            </div>
            <h1 class="vanilla-contact-title">{{ $data['hero_title'] ?? 'Get in Touch' }}</h1>
            <p class="vanilla-contact-subtitle">{{ $data['hero_subtitle'] ?? 'Have a question or need assistance? We\'re here to help.' }}</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="vanilla-contact-content">
        <div class="vanilla-container">
            <div class="vanilla-contact-wrapper">
                <div class="vanilla-contact-card">
                    <div class="vanilla-contact-grid">
                        
                        <!-- Left: Contact Information Panel -->
                        <div class="vanilla-info-panel">
                            <div class="vanilla-info-top">
                                <h3 class="vanilla-info-title">{{ $data['info_title'] ?? 'Contact Information' }}</h3>
                                <p class="vanilla-info-desc">{{ $data['info_subtitle'] ?? 'Fill up the form and our team will get back to you within 24 hours.' }}</p>
                                
                                <ul class="vanilla-info-list">
                                    <li>
                                        <i class="bi bi-geo-alt vanilla-info-icon"></i>
                                        <span>{{ \App\Models\Setting::get('address', '123 ShopCalm Street, Commerce City, 10001') }}</span>
                                    </li>
                                    <li>
                                        <i class="bi bi-telephone vanilla-info-icon"></i>
                                        <a href="tel:{{ \App\Models\Setting::get('contact_phone', '+1 (800) 123-4567') }}">{{ \App\Models\Setting::get('contact_phone', '+1 (800) 123-4567') }}</a>
                                    </li>
                                    <li>
                                        <i class="bi bi-envelope vanilla-info-icon"></i>
                                        <a href="mailto:{{ \App\Models\Setting::get('contact_email', 'support@shopcalm.com') }}">{{ \App\Models\Setting::get('contact_email', 'support@shopcalm.com') }}</a>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="vanilla-info-bottom">
                                <h6 class="vanilla-social-title">Follow Us</h6>
                                <div class="vanilla-social-links">
                                    <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
                                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                                </div>
                            </div>
                            
                            <!-- Decorative Circles -->
                            <div class="decor-circle-1"></div>
                            <div class="decor-circle-2"></div>
                        </div>
                        
                        <!-- Right: Contact Form -->
                        <div class="vanilla-form-panel">
                            <h3 class="vanilla-form-title">{{ $data['form_title'] ?? 'Send us a message' }}</h3>
                            
                            <form action="{{ route('page.contact.submit') }}" method="POST" class="contact-form" id="contactForm">
                                @csrf
                                <div class="vanilla-form-grid">
                                    <div class="vanilla-input-group">
                                        <label for="name" class="vanilla-label">Full Name <span class="vanilla-required">*</span></label>
                                        <input type="text" class="vanilla-input" id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                                        <div class="vanilla-error name-error"></div>
                                    </div>
                                    
                                    <div class="vanilla-input-group">
                                        <label for="email" class="vanilla-label">Email Address <span class="vanilla-required">*</span></label>
                                        <input type="email" class="vanilla-input" id="email" name="email" value="{{ old('email') }}" placeholder="john@example.com" required>
                                        <div class="vanilla-error email-error"></div>
                                    </div>
                                    
                                    <div class="vanilla-input-group">
                                        <label for="mobile" class="vanilla-label">Mobile Number</label>
                                        <input type="tel" class="vanilla-input" id="mobile" name="mobile" value="{{ old('mobile') }}" placeholder="1234567890" pattern="[0-9]{10}" maxlength="10" minlength="10" title="Please enter exactly 10 digits">
                                        <div class="vanilla-error mobile-error"></div>
                                    </div>
                                    
                                    <div class="vanilla-input-group">
                                        <label for="subject" class="vanilla-label">Subject <span class="vanilla-required">*</span></label>
                                        <input type="text" class="vanilla-input" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Order Inquiry" required>
                                        <div class="vanilla-error subject-error"></div>
                                    </div>
                                    
                                    <div class="vanilla-input-group full-width">
                                        <label for="message" class="vanilla-label">Message <span class="vanilla-required">*</span></label>
                                        <textarea class="vanilla-input vanilla-textarea" id="message" name="message" placeholder="Write your message here..." required>{{ old('message') }}</textarea>
                                        <div class="vanilla-error message-error"></div>
                                    </div>
                                    
                                    <div class="vanilla-submit-group">
                                        <button type="submit" id="submitBtn" class="vanilla-btn">
                                            <span>Send Message</span> <i class="bi bi-send-fill submit-icon"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    /* =========================================
       Vanilla CSS Replacements
       ========================================= */
    :root {
        --c-primary: #0d6efd;
        --c-primary-dark: #0a58ca;
        --c-primary-soft: #eff6ff;
        --c-surface: #ffffff;
        --c-bg: #f8fafc;
        --c-text: #334155;
        --c-heading: #0f172a;
        --c-muted: #94a3b8;
        --c-border: #e2e8f0;
        --c-danger: #ef4444;
    }

    .contact-page {
        font-family: 'Inter', sans-serif;
        color: var(--c-text);
        background-color: var(--c-bg);
    }
    
    .vanilla-container {
        width: 100%;
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Hero */
    .vanilla-contact-hero {
        text-align: center;
        padding: 4rem 0 2rem 0;
    }
    .vanilla-logo-wrapper { margin-bottom: 1.5rem; }
    .vanilla-contact-title {
        font-size: 3rem;
        font-weight: 800;
        color: var(--c-heading);
        margin: 0 0 1rem 0;
    }
    .vanilla-contact-subtitle {
        font-size: 1.2rem;
        color: var(--c-muted);
        max-width: 600px;
        margin: 0 auto;
    }

    /* Content & Layout */
    .vanilla-contact-content {
        padding-bottom: 4rem;
    }
    .vanilla-contact-wrapper {
        display: flex;
        justify-content: center;
    }
    .vanilla-contact-card {
        background: var(--c-surface);
        border-radius: 1.5rem;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.08);
        width: 100%;
        max-width: 1140px;
        overflow: hidden;
    }
    .vanilla-contact-grid {
        display: flex;
        flex-direction: column;
    }
    @media(min-width: 992px) {
        .vanilla-contact-grid { flex-direction: row; }
    }

    /* Left Panel */
    .vanilla-info-panel {
        background: var(--c-heading);
        color: #fff;
        padding: 3rem;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 0 0 auto;
        width: 100%;
    }
    @media(min-width: 992px) {
        .vanilla-info-panel { width: 33.333333%; }
    }
    .vanilla-info-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0 0 1rem 0;
        color: #fff;
    }
    .vanilla-info-desc {
        color: rgba(255,255,255,0.7);
        margin: 0 0 3rem 0;
        line-height: 1.6;
    }
    .vanilla-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .vanilla-info-list li {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    .vanilla-info-icon {
        font-size: 1.25rem;
        margin-top: 0.1rem;
        color: var(--c-primary);
    }
    .vanilla-info-list a {
        color: #fff;
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .vanilla-info-list a:hover { opacity: 0.8; }
    
    .vanilla-info-bottom { margin-top: 4rem; z-index: 1; position: relative; }
    .vanilla-social-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255,255,255,0.5);
        margin: 0 0 1rem 0;
    }
    .vanilla-social-links {
        display: flex;
        gap: 1rem;
    }

    /* Right Panel (Form) */
    .vanilla-form-panel {
        padding: 3rem;
        flex: 1;
    }
    @media(min-width: 992px) {
        .vanilla-form-panel { padding: 4rem; }
    }
    .vanilla-form-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--c-heading);
        margin: 0 0 2rem 0;
    }
    .vanilla-form-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media(min-width: 768px) {
        .vanilla-form-grid { grid-template-columns: 1fr 1fr; }
        .full-width { grid-column: span 2; }
    }
    
    .vanilla-input-group { display: flex; flex-direction: column; }
    .vanilla-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--c-text);
        margin-bottom: 0.5rem;
    }
    .vanilla-required { color: var(--c-danger); }
    .vanilla-input {
        background-color: var(--c-bg);
        border: 1px solid transparent;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        font-family: inherit;
        color: var(--c-text);
        transition: all 0.2s ease;
    }
    .vanilla-input:focus {
        outline: none;
        background-color: #fff;
        border-color: var(--c-primary);
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
    }
    .vanilla-textarea { height: 150px; resize: vertical; }
    .vanilla-error {
        color: var(--c-danger);
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }
    .vanilla-submit-group {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        margin-top: 1rem;
    }
    .vanilla-btn {
        background: var(--c-primary);
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 1rem 2.5rem;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        transition: background 0.2s;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .vanilla-btn:hover { background: var(--c-primary-dark); }
    .vanilla-btn:disabled { opacity: 0.7; cursor: not-allowed; }
    :root {
        --contact-primary: #0d6efd;
        --contact-dark: #0f172a;
    }

    body {
        background-color: #f8fafc;
    }

    /* Hero Section */
    .contact-hero {
        padding: 60px 0 80px;
        background-color: #f8fafc;
    }
    
    .contact-hero-title {
        font-size: 3rem;
        font-weight: 800;
        color: var(--contact-dark);
        letter-spacing: -0.02em;
        margin-bottom: 12px;
    }
    
    .contact-hero-subtitle {
        font-size: 1.15rem;
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Contact Card */
    .contact-card {
        margin-top: -40px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    /* Info Panel */
    .contact-info-panel {
        background: linear-gradient(135deg, var(--contact-dark) 0%, var(--contact-primary) 100%);
        position: relative;
        overflow: hidden;
    }

    .contact-info-list {
        position: relative;
        z-index: 2;
    }

    .contact-info-list li {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 30px;
    }

    .contact-info-list li span, .contact-info-list li a {
        color: #ffffff;
        text-decoration: none;
        line-height: 1.5;
        font-size: 1rem;
        opacity: 0.9;
        transition: opacity 0.2s ease;
    }

    .contact-info-list li a:hover {
        opacity: 1;
        text-decoration: underline;
    }

    /* Social Icons */
    .social-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.3s ease;
        z-index: 2;
    }
    .social-icon:hover {
        background: var(--contact-primary);
        color: #fff;
        transform: translateY(-3px);
    }

    /* Decorative Circles */
    .decor-circle-1 {
        position: absolute;
        bottom: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }
    
    .decor-circle-2 {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0) 70%);
        pointer-events: none;
    }

    /* Form Fields */
    .contact-form .form-control {
        border-radius: 12px;
        padding-top: 1.625rem;
        padding-bottom: 0.625rem;
        font-size: 1rem;
        font-weight: 500;
        color: #334155;
    }
    
    .contact-form .form-control:focus {
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1) !important;
        border: 1px solid var(--contact-primary) !important;
    }
    
    .contact-form .form-floating > label {
        padding: 1rem 1.25rem;
        font-weight: 500;
    }
    
    .contact-form .btn-primary {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .contact-form .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -6px rgba(13, 110, 253, 0.5) !important;
    }

    @media (max-width: 767px) {
        .contact-hero { padding: 40px 0 60px; }
        .contact-hero-title { font-size: 2.25rem; }
        .contact-card { margin-top: -20px; }
        .contact-info-panel { padding: 40px 30px !important; }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Fallback for non-AJAX successful submission
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Message Sent!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#000000',
            background: '#ffffff',
            customClass: { popup: 'rounded-4' }
        });
        @endif

        // AJAX Form Submission
        const form = document.getElementById('contactForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitBtn');
                const originalBtnContent = submitBtn.innerHTML;
                const originalWidth = submitBtn.offsetWidth;
                
                // Set loading state
                submitBtn.style.width = originalWidth + 'px';
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending...';
                
                // Clear previous errors
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.style.width = 'auto';
                    
                    if (data.success) {
                        form.reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Message Sent!',
                            text: data.message || 'Your message has been sent successfully.',
                            confirmButtonColor: '#0d6efd',
                            background: '#ffffff',
                            customClass: { popup: 'rounded-4' }
                        });
                    } else if (data.errors) {
                        // Handle validation errors from JSON response
                        for (const [key, messages] of Object.entries(data.errors)) {
                            const input = form.querySelector(`[name="${key}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const errorDiv = form.querySelector(`.${key}-error`);
                                if (errorDiv) {
                                    errorDiv.textContent = messages[0];
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.style.width = 'auto';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong. Please try again later.',
                        confirmButtonColor: '#ef4444'
                    });
                });
            });
        }
    });
</script>
@endpush
