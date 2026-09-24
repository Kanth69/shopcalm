<section class="py-4 py-md-5 bg-white border-bottom">
    <div class="container px-3 px-md-4">
        <div class="card border-0 shadow-sm overflow-hidden position-relative rounded-4" 
             style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%); color: #ffffff;">
            
            {{-- Decorative pattern overlay --}}
            <div class="position-absolute w-100 h-100"
                 style="background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 24px 24px; top: 0; left: 0; pointer-events: none;"></div>

            <div class="card-body p-3.5 p-md-5 text-center position-relative z-1">
                <div class="d-inline-flex align-items-center justify-content-center mb-2.5 rounded-circle shadow-xs" 
                     style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.25); border: 2px solid rgba(99,102,241,0.5);">
                    <i class="bi bi-envelope-open-heart-fill fs-4 text-white"></i>
                </div>
                
                <h4 class="fw-bolder mb-1 text-white" style="letter-spacing: -0.02em; font-size: clamp(1.2rem, 3vw, 1.65rem);">Subscribe to Our Newsletter</h4>
                <p class="text-white-50 mb-3 mx-auto small" style="max-width: 520px; font-size: 0.84rem; line-height: 1.45;">
                    Get exclusive members-only deals, early access to new arrivals, and special promotions delivered straight to your inbox.
                </p>

                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <form id="newsletter-subscription-form" action="{{ route('newsletter.subscribe') }}" method="POST" onsubmit="handleNewsletterSubmit(event, this)">
                            @csrf
                            <div class="input-group p-1 bg-white rounded-pill shadow-lg">
                                <span class="input-group-text bg-transparent border-0 ps-2.5 text-muted d-none d-sm-flex">
                                    <i class="bi bi-envelope fs-5 text-primary"></i>
                                </span>
                                <input type="email" name="email" id="newsletter-email-input" class="form-control border-0 bg-transparent px-2.5 shadow-none text-dark" placeholder="Enter your email address..." required style="font-size: 0.85rem;">
                                <button class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold ms-1 shadow-sm flex-shrink-0" type="submit" id="btn-subscribe-newsletter" 
                                        style="font-size: 0.84rem; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); border: none;">
                                    <i class="bi bi-send-fill me-1"></i> Subscribe
                                </button>
                            </div>
                            <div id="newsletter-error-msg" class="text-danger small mt-2 fw-semibold" style="display: none;"></div>
                        </form>
                        <div class="text-white-50 small mt-2.5" style="font-size: 0.72rem;">
                            <i class="bi bi-shield-check me-1 text-success"></i> No spam. You can unsubscribe at any time.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function handleNewsletterSubmit(e, form) {
    e.preventDefault();

    const emailInput = document.getElementById('newsletter-email-input');
    const submitBtn = document.getElementById('btn-subscribe-newsletter');
    const errorMsg = document.getElementById('newsletter-error-msg');
    
    if (errorMsg) errorMsg.style.display = 'none';

    const emailVal = emailInput ? emailInput.value.trim() : '';

    // Strict Regex check for email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailVal || !emailRegex.test(emailVal)) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.',
                confirmButtonColor: '#4f46e5'
            });
        } else if (errorMsg) {
            errorMsg.textContent = 'Please enter a valid email address.';
            errorMsg.style.display = 'block';
        }
        return;
    }

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Subscribing...';
    }

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: emailVal })
    })
    .then(async res => {
        const data = await res.json();
        if (!res.ok) {
            throw new Error(data.message || 'Subscription failed. Please check your email.');
        }
        return data;
    })
    .then(data => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Subscribe';
        }
        if (emailInput) emailInput.value = '';

        if (typeof Swal !== 'undefined') {
            if (data.already_subscribed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Already Subscribed!',
                    text: data.message,
                    confirmButtonColor: '#4f46e5'
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Congratulations! 🎉',
                    text: data.message,
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'Great!'
                });
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Subscribe';
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Subscription Failed',
                text: err.message || 'Something went wrong. Please try again.',
                confirmButtonColor: '#ef4444'
            });
        } else if (errorMsg) {
            errorMsg.textContent = err.message || 'Something went wrong.';
            errorMsg.style.display = 'block';
        }
    });
}
</script>
