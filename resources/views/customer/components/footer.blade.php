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
<footer class="section-footer border-top bg-dark text-white pt-4 pt-md-5 footer-responsive-container">
    <div class="container px-3 px-md-4">
        <section class="footer-top pb-3 pb-md-4">
            <div class="row g-4 align-items-start">
                {{-- 1. Brand & Socials --}}
                <aside class="col-lg-4 col-md-12 text-center text-md-start">
                    <article>
                        <div class="mb-2.5 d-inline-block d-md-block">
                            <x-logo variant="light" height="30" :showTagline="false" />
                        </div>
                        <p class="text-white-50 small pe-lg-4 mb-3 mx-auto mx-md-0" style="font-size: 0.82rem; max-width: 380px; line-height: 1.55;">
                            {{ $tagline }}
                        </p>
                        
                        {{-- Social Links --}}
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 flex-wrap mb-2">
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
                <aside class="col-lg-2 col-md-4 col-6">
                    <h6 class="title text-white fw-bold mb-2.5" style="font-size: 0.9rem; letter-spacing: -0.2px;">Company</h6>
                    <ul class="list-unstyled mb-0" style="font-size: 0.82rem;">
                        <li class="mb-1.5"><a href="{{ route('page.about') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">About us</a></li>
                        <li class="mb-1.5"><a href="{{ route('page.contact') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Contact us</a></li>
                        <li class="mb-1.5"><a href="{{ route('page.terms') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Terms & Conditions</a></li>
                        <li><a href="{{ route('page.privacy') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Privacy Policy</a></li>
                    </ul>
                </aside>

                {{-- 3. Customer Help --}}
                <aside class="col-lg-2 col-md-4 col-6">
                    <h6 class="title text-white fw-bold mb-2.5" style="font-size: 0.9rem; letter-spacing: -0.2px;">Customer Help</h6>
                    <ul class="list-unstyled mb-0" style="font-size: 0.82rem;">
                        <li class="mb-1.5"><a href="{{ route('page.faq') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">FAQ</a></li>
                        <li class="mb-1.5"><a href="{{ route('page.return') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Return & Refund</a></li>
                        <li class="mb-1.5"><a href="{{ route('page.shipping') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Shipping Policy</a></li>
                        <li><a href="{{ route('page.cancellation') }}" class="text-white-50 text-decoration-none hover-white py-1 d-inline-block">Cancellation</a></li>
                    </ul>
                </aside>

                {{-- 4. Support & Security --}}
                <aside class="col-lg-4 col-md-4 col-12 text-center text-md-start text-lg-end">
                    <h6 class="title text-white fw-bold mb-2.5" style="font-size: 0.9rem; letter-spacing: -0.2px;">Support & Security</h6>
                    <div class="d-inline-flex flex-column align-items-center align-items-md-start align-items-lg-end">
                        <div class="bg-white px-3 py-1.5 rounded-3 shadow-sm d-inline-block mb-2">
                            <span class="fw-bold text-dark font-monospace small" style="font-size: 0.76rem;">
                                <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-BIT SSL ENCRYPTED
                            </span>
                        </div>
                        <p class="text-white-50 small mb-1" style="font-size: 0.76rem;">100% Safe & Genuine Checkout</p>
                        <small class="text-white-50 mb-1 d-block" style="font-size: 0.72rem;"><i class="bi bi-shield-check text-info me-1"></i>Grievance Officer: <a href="mailto:grievance@shopcalm.in" class="text-white-50 text-decoration-underline">grievance@shopcalm.in</a></small>
                        @if($supportHours)
                            <small class="text-white-50" style="font-size: 0.72rem;"><i class="bi bi-clock me-1"></i>{{ $supportHours }}</small>
                        @endif
                    </div>
                </aside>
            </div>
        </section>

        {{-- Footer Bottom Copyright --}}
        <section class="footer-bottom border-top border-secondary border-opacity-25 py-3 mt-1 text-center">
            <p class="text-white-50 small mb-0" style="font-size: 0.78rem;">{{ $copyright }}</p>
        </section>
    </div>
</footer>

<style>
    .footer-responsive-container {
        padding-bottom: 78px !important;
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
    .footer-social-btn:hover {
        transform: translateY(-2px);
    }
    .hover-white {
        transition: color 0.15s ease;
    }
    .hover-white:hover {
        color: #ffffff !important;
    }
</style>
