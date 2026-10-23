@php
    $storeName     = \App\Models\Setting::get('store_name', 'ShopCalm');
    $tagline       = \App\Models\Setting::get('tagline', 'Your one-stop shop for everything you need. Quality products, unbeatable prices, and a seamless shopping experience.');
    $copyright     = \App\Models\Setting::get('copyright_text', '© ' . date('Y') . ' ' . $storeName . '. All rights reserved.');
    $supportHours  = \App\Models\Setting::get('support_hours', 'Mon - Sat: 9:00 AM - 8:00 PM');
    $fbUrl         = \App\Models\Setting::get('facebook_url');
    $instaUrl      = \App\Models\Setting::get('instagram_url');
    $twitterUrl    = \App\Models\Setting::get('twitter_url');
    $linkedinUrl   = \App\Models\Setting::get('linkedin_url');
    $youtubeUrl    = \App\Models\Setting::get('youtube_url');
    $whatsappNum   = \App\Models\Setting::get('whatsapp_number');
@endphp
<footer class="section-footer border-top bg-dark text-white footer-responsive-container">
    <div class="container px-3 px-md-4">

        {{-- ============================================================ --}}
        {{-- 📱 MOBILE FOOTER (< 768px): Brand-Rich, Clean & Well-Spaced  --}}
        {{-- ============================================================ --}}
        <div class="d-md-none py-4 text-start">
            {{-- 1. Logo & Tagline --}}
            <div class="mb-3 text-center d-flex flex-column align-items-center">
                <x-logo variant="light" height="28" :showTagline="false" />
                <p class="text-white-50 mb-0 mt-1.5 fw-medium" style="font-size: 0.82rem; letter-spacing: 0.01em;">Shop More, Worry Less</p>
            </div>

            {{-- 2. Social Links (Instagram, Facebook, YouTube, etc.) --}}
            <div class="d-flex align-items-center justify-content-center gap-2 mb-3 pb-3 border-bottom border-secondary border-opacity-25 flex-wrap">
                @if($instaUrl)
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="{{ $instaUrl }}" target="_blank" rel="noopener noreferrer" title="Instagram"><i class="bi bi-instagram"></i></a>
                @endif
                @if($fbUrl)
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="{{ $fbUrl }}" target="_blank" rel="noopener noreferrer" title="Facebook"><i class="bi bi-facebook"></i></a>
                @endif
                @if($youtubeUrl)
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="{{ $youtubeUrl }}" target="_blank" rel="noopener noreferrer" title="YouTube"><i class="bi bi-youtube"></i></a>
                @endif
                @if($twitterUrl)
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="{{ $twitterUrl }}" target="_blank" rel="noopener noreferrer" title="Twitter"><i class="bi bi-twitter-x"></i></a>
                @endif
                @if($whatsappNum)
                    <a class="btn btn-icon btn-outline-success rounded-circle text-success footer-social-btn-sm" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNum) }}" target="_blank" rel="noopener noreferrer" title="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                @endif
                @if(!$instaUrl && !$fbUrl && !$youtubeUrl)
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="javascript:void(0);"><i class="bi bi-instagram"></i></a>
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="javascript:void(0);"><i class="bi bi-facebook"></i></a>
                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn-sm" href="javascript:void(0);"><i class="bi bi-youtube"></i></a>
                @endif
            </div>

            {{-- 3. Links with comfortable gaps between lines --}}
            <div class="row g-3 mb-3 pb-3 border-bottom border-secondary border-opacity-25">
                <div class="col-6">
                    <span class="text-white fw-bold d-block mb-2" style="font-size: 0.88rem; letter-spacing: -0.2px;">Company</span>
                    <ul class="list-unstyled mb-0" style="font-size: 0.8rem; line-height: 1.9;">
                        <li><a href="{{ route('page.about') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">About us</a></li>
                        <li><a href="{{ route('page.contact') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">Contact us</a></li>
                        <li><a href="{{ route('page.terms') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">Terms & Conditions</a></li>
                        <li><a href="{{ route('page.privacy') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="col-6">
                    <span class="text-white fw-bold d-block mb-2" style="font-size: 0.88rem; letter-spacing: -0.2px;">Customer Help</span>
                    <ul class="list-unstyled mb-0" style="font-size: 0.8rem; line-height: 1.9;">
                        <li><a href="{{ route('page.faq') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">FAQ</a></li>
                        <li><a href="{{ route('page.return') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">Return & Refund</a></li>
                        <li><a href="{{ route('page.shipping') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">Shipping Policy</a></li>
                        <li><a href="{{ route('page.cancellation') }}" class="text-white-50 text-decoration-none hover-white d-inline-block py-0.5">Cancellation</a></li>
                    </ul>
                </div>
            </div>

            {{-- 4. Grievance Email & Support Info --}}
            <div class="p-3 rounded-3 mb-3 border border-secondary border-opacity-25" style="background: rgba(255, 255, 255, 0.04);">
                <div class="d-flex align-items-center mb-1.5">
                    <i class="bi bi-shield-check text-info me-2 fs-6"></i>
                    <span class="text-white fw-semibold" style="font-size: 0.82rem;">Grievance & Support</span>
                </div>
                <div class="text-white-50 mb-1" style="font-size: 0.78rem;">
                    <span>Email: </span>
                    <a href="mailto:grievance@shopcalm.in" class="text-info text-decoration-underline ms-1 fw-medium">grievance@shopcalm.in</a>
                </div>
                @if($supportHours)
                    <div class="text-white-50" style="font-size: 0.74rem;">
                        <i class="bi bi-clock me-1"></i> {{ $supportHours }}
                    </div>
                @endif
            </div>

            {{-- 5. Copyright at the very end --}}
            <div class="text-center pt-2">
                <p class="text-white-50 small mb-0" style="font-size: 0.76rem;">{{ $copyright }}</p>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- 💻 DESKTOP FOOTER (>= 768px): Original Layout Preserved 100% --}}
        {{-- ============================================================ --}}
        <div class="d-none d-md-block pt-5 pb-3">
            <section class="footer-top pb-4">
                <div class="row g-4 align-items-start">
                    {{-- 1. Brand & Socials --}}
                    <aside class="col-lg-4 col-md-12 text-md-start">
                        <article>
                            <div class="mb-2.5 d-block">
                                <x-logo variant="light" height="30" :showTagline="false" />
                            </div>
                            <p class="text-white-50 small pe-lg-4 mb-3" style="font-size: 0.82rem; max-width: 380px; line-height: 1.55;">
                                {{ $tagline }}
                            </p>
                            
                            {{-- Social Links --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                                @if($fbUrl)
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="Facebook" target="_blank" rel="noopener noreferrer" href="{{ $fbUrl }}"><i class="bi bi-facebook"></i></a>
                                @endif
                                @if($instaUrl)
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="Instagram" target="_blank" rel="noopener noreferrer" href="{{ $instaUrl }}"><i class="bi bi-instagram"></i></a>
                                @endif
                                @if($twitterUrl)
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="Twitter / X" target="_blank" rel="noopener noreferrer" href="{{ $twitterUrl }}"><i class="bi bi-twitter-x"></i></a>
                                @endif
                                @if($linkedinUrl)
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="LinkedIn" target="_blank" rel="noopener noreferrer" href="{{ $linkedinUrl }}"><i class="bi bi-linkedin"></i></a>
                                @endif
                                @if($youtubeUrl)
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="YouTube" target="_blank" rel="noopener noreferrer" href="{{ $youtubeUrl }}"><i class="bi bi-youtube"></i></a>
                                @endif
                                @if($whatsappNum)
                                    <a class="btn btn-icon btn-outline-success rounded-circle text-success footer-social-btn" title="Chat on WhatsApp" target="_blank" rel="noopener noreferrer" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNum) }}"><i class="bi bi-whatsapp"></i></a>
                                @endif
                                @if(!$fbUrl && !$instaUrl && !$twitterUrl && !$linkedinUrl && !$youtubeUrl)
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="Facebook" href="javascript:void(0);"><i class="bi bi-facebook"></i></a>
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="Instagram" href="javascript:void(0);"><i class="bi bi-instagram"></i></a>
                                    <a class="btn btn-icon btn-outline-light rounded-circle border-secondary footer-social-btn" title="Twitter" href="javascript:void(0);"><i class="bi bi-twitter-x"></i></a>
                                @endif
                            </div>
                        </article>
                    </aside>

                    {{-- 2. Company Links --}}
                    <aside class="col-lg-2 col-md-4">
                        <h6 class="title text-white fw-bold mb-2.5" style="font-size: 0.9rem; letter-spacing: -0.2px;">Company</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.82rem;">
                            <li class="mb-1.5"><a href="{{ route('page.about') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">About us</a></li>
                            <li class="mb-1.5"><a href="{{ route('page.contact') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Contact us</a></li>
                            <li class="mb-1.5"><a href="{{ route('page.terms') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Terms & Conditions</a></li>
                            <li><a href="{{ route('page.privacy') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Privacy Policy</a></li>
                        </ul>
                    </aside>

                    {{-- 3. Customer Help --}}
                    <aside class="col-lg-2 col-md-4">
                        <h6 class="title text-white fw-bold mb-2.5" style="font-size: 0.9rem; letter-spacing: -0.2px;">Customer Help</h6>
                        <ul class="list-unstyled mb-0" style="font-size: 0.82rem;">
                            <li class="mb-1.5"><a href="{{ route('page.faq') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">FAQ</a></li>
                            <li class="mb-1.5"><a href="{{ route('page.return') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Return & Refund</a></li>
                            <li class="mb-1.5"><a href="{{ route('page.shipping') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Shipping Policy</a></li>
                            <li><a href="{{ route('page.cancellation') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Cancellation</a></li>
                        </ul>
                    </aside>

                    {{-- 4. Support & Security --}}
                    <aside class="col-lg-4 col-md-4 text-md-start text-lg-end">
                        <h6 class="title text-white fw-bold mb-2.5" style="font-size: 0.9rem; letter-spacing: -0.2px;">Support & Security</h6>
                        <div class="d-inline-flex flex-column align-items-md-start align-items-lg-end">
                            <div class="bg-white px-3 py-1.5 rounded-3 shadow-sm d-inline-block mb-2">
                                <span class="fw-bold text-dark font-monospace small" style="font-size: 0.76rem;">
                                    <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-BIT SSL ENCRYPTED
                                </span>
                            </div>
                            <p class="text-white-50 small mb-1" style="font-size: 0.76rem;">100% Safe & Genuine Checkout</p>
                            <small class="text-white-50 mb-1 d-block" style="font-size: 0.72rem;"><i class="bi bi-shield-check text-info me-1"></i>Grievance: <a href="mailto:grievance@shopcalm.in" class="text-white-50 text-decoration-underline">grievance@shopcalm.in</a></small>
                            @if($supportHours)
                                <small class="text-white-50" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>{{ $supportHours }}</small>
                            @endif
                        </div>
                    </aside>
                </div>
            </section>

            {{-- Footer Bottom Copyright (Desktop) --}}
            <section class="footer-bottom border-top border-secondary border-opacity-25 py-3 mt-1 text-center">
                <p class="text-white-50 small mb-0" style="font-size: 0.78rem;">{{ $copyright }}</p>
            </section>
        </div>

    </div>
</footer>

<style>
    /* Mobile Navigation Safe Padding */
    @media (max-width: 767.98px) {
        .footer-responsive-container {
            padding-bottom: calc(72px + env(safe-area-inset-bottom, 0px)) !important;
        }
        .footer-social-btn-sm {
            width: 30px !important;
            height: 30px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0.78rem !important;
        }
    }
    @media (min-width: 768px) {
        .footer-responsive-container {
            padding-bottom: 0 !important;
        }
    }
    .footer-social-btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.88rem;
        transition: all 0.2s ease;
    }
    .footer-social-btn:hover,
    .footer-social-btn-sm:hover {
        transform: translateY(-2px);
    }
    .hover-white {
        transition: color 0.15s ease;
    }
    .hover-white:hover {
        color: #ffffff !important;
    }
</style>
