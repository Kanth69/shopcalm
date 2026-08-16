<section class="py-5 bg-white">
    <div class="container">
        <div class="card border-0 shadow-sm overflow-hidden position-relative" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%); border: 1px solid #e2e8f0 !important; border-radius: 20px !important;">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle" style="width: 54px; height: 54px; background: #ede9fe;">
                    <i class="bi bi-envelope-open-heart-fill fs-4" style="color: #6366f1;"></i>
                </div>
                <h3 class="fw-bold mb-1 text-dark" style="font-size: 1.5rem;">Subscribe to Our Newsletter</h3>
                <p class="text-secondary mb-4 mx-auto" style="max-width: 480px; font-size: 0.9rem;">
                    Get exclusive deals, new arrivals, and special promotions delivered straight to your inbox.
                </p>

                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-8">
                        <form id="newsletter-subscription-form" action="{{ route('newsletter.subscribe') }}" method="POST" onsubmit="handleNewsletterSubmit(event, this)">
                            @csrf
                            <div class="input-group p-1 bg-white rounded-pill shadow-sm" style="border: 1px solid #cbd5e1;">
                                <span class="input-group-text bg-transparent border-0 ps-3 text-muted">
                                    <i class="bi bi-envelope fs-6 text-primary"></i>
                                </span>
                                <input type="email" name="email" id="newsletter-email-input" class="form-control border-0 bg-transparent px-2 shadow-none text-dark" placeholder="Enter your email address..." required style="font-size: 0.875rem;">
                                <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold ms-1" type="submit" id="btn-subscribe-newsletter" style="font-size: 0.875rem; background: #6366f1; border-color: #6366f1;">
                                    <i class="bi bi-send-fill me-1"></i> Subscribe
                                </button>
                            </div>
                            <div id="newsletter-error-msg" class="text-danger small mt-2 fw-semibold" style="display: none;"></div>
                        </form>
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
