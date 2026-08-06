<footer class="section-footer border-top bg-dark text-white pt-5">
    <div class="container">
        <section class="footer-top pb-4">
            <div class="row g-4">
                <aside class="col-lg-4 col-md-12">
                    <article>
                        <div class="mb-3">
                            <x-logo variant="light" height="34" :showTagline="true" />
                        </div>
                        <p class="text-white-50 small pe-lg-5">Your one-stop shop for everything you need. Quality products, unbeatable prices, and a seamless shopping experience.</p>
                        <div class="mt-4 d-flex gap-2">
                            <a class="btn btn-sm btn-icon btn-outline-light rounded-circle border-secondary" title="Facebook" target="_blank" href="#"><i class="bi bi-facebook"></i></a>
                            <a class="btn btn-sm btn-icon btn-outline-light rounded-circle border-secondary" title="Instagram" target="_blank" href="#"><i class="bi bi-instagram"></i></a>
                            <a class="btn btn-sm btn-icon btn-outline-light rounded-circle border-secondary" title="Twitter" target="_blank" href="#"><i class="bi bi-twitter-x"></i></a>
                        </div>
                    </article>
                </aside>

                <aside class="col-lg-2 col-md-4 col-6">
                    <h6 class="title text-white fw-bold mb-3">Company</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"> <a href="{{ route('page.about') }}" class="text-white-50 text-decoration-none small hover-white">About us</a></li>
                        <li class="mb-2"> <a href="{{ route('page.contact') }}" class="text-white-50 text-decoration-none small hover-white">Contact us</a></li>
                        <li class="mb-2"> <a href="{{ route('page.terms') }}" class="text-white-50 text-decoration-none small hover-white">Terms & Condition</a></li>
                        <li> <a href="{{ route('page.privacy') }}" class="text-white-50 text-decoration-none small hover-white">Privacy Policy</a></li>
                    </ul>
                </aside>

                <aside class="col-lg-2 col-md-4 col-6">
                    <h6 class="title text-white fw-bold mb-3">Customer Service</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"> <a href="{{ route('page.faq') }}" class="text-white-50 text-decoration-none small hover-white">FAQ</a></li>
                        <li class="mb-2"> <a href="{{ route('page.return') }}" class="text-white-50 text-decoration-none small hover-white">Return & Refund</a></li>
                        <li class="mb-2"> <a href="{{ route('page.shipping') }}" class="text-white-50 text-decoration-none small hover-white">Shipping Policy</a></li>
                        <li> <a href="{{ route('page.cancellation') }}" class="text-white-50 text-decoration-none small hover-white">Cancellation</a></li>
                    </ul>
                </aside>

                <aside class="col-lg-4 col-md-4 col-12 text-lg-end">
                    <h6 class="title text-white fw-bold mb-3">Secure Payment</h6>
                    <div class="d-lg-inline-flex flex-column align-items-lg-end">
                        <div class="bg-white p-2 rounded-3 shadow-sm d-inline-block">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" alt="Razorpay" height="25" style="filter: brightness(0) saturate(100%) invert(32%) sepia(91%) rotate(193deg) brightness(97%) contrast(101%);">
                        </div>
                        <p class="text-white-50 small mt-2 mb-0">100% Safe and Secure checkout</p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="footer-bottom border-top border-secondary border-opacity-25 py-4 mt-2">
            <div class="container text-center">
                <p class="text-white-50 small mb-0"> &copy; {{ date('Y') }} ShopCalm. All rights reserved. </p>
            </div>
        </section>
    </div>
</footer>

<style>
    .hover-white:hover { color: #fff !important; }
    .letter-spacing-tight { letter-spacing: -0.5px; }
</style>
