document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('auth-container');
    if (!container) return;

    const states = {
        initial: document.getElementById('state-initial'),
        login: document.getElementById('state-login'),
        register: document.getElementById('state-register'),
        forgot: document.getElementById('state-forgot-password'),
        success: document.getElementById('state-success'),
    };

    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag && metaTag.getAttribute('content')) {
            return metaTag.getAttribute('content');
        }
        const tokenInput = document.querySelector('input[name="_token"]');
        if (tokenInput && tokenInput.value) {
            return tokenInput.value;
        }
        return window.csrfToken || '';
    }

    let currentState = document.getElementById('state-register') && document.getElementById('state-register').style.display === 'block' ? 'register' : 'initial';
    let currentIdentifier = '';

    function showBannerAlert(message, type = 'success') {
        const alertEl = document.getElementById('register-alert');
        const alertMsg = document.getElementById('register-alert-msg');
        const alertIcon = document.getElementById('register-alert-icon');

        if (alertEl && alertMsg) {
            alertMsg.textContent = message;
            alertEl.className = `auth-alert ${type}`;
            if (alertIcon) {
                alertIcon.className = type === 'success' ? 'bi bi-check-circle-fill me-2' : (type === 'info' ? 'bi bi-hourglass-split me-2 spin-icon' : 'bi bi-exclamation-triangle-fill me-2');
            }
            alertEl.style.display = 'flex';
        }
    }

    function hideBannerAlert() {
        const alertEl = document.getElementById('register-alert');
        if (alertEl) alertEl.style.display = 'none';
    }

    function showToast(message, type = 'error') {
        let toastEl = document.getElementById('auth-toast');
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = 'auth-toast';
            toastEl.className = 'auth-toast';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = message;
        toastEl.className = `auth-toast show ${type}`;
        setTimeout(() => {
            toastEl.className = 'auth-toast';
        }, 3500);
    }

    function transitionTo(stateName, identifier = null) {
        if (!states[stateName] || currentState === stateName) return;

        const currentEl = states[currentState];
        const nextEl = states[stateName];

        currentEl.style.opacity = '0';
        currentEl.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            currentEl.style.display = 'none';
            nextEl.style.display = 'block';

            setTimeout(() => {
                nextEl.style.opacity = '1';
                nextEl.style.transform = 'translateY(0)';
            }, 50);

            currentState = stateName;

            if (identifier) {
                currentIdentifier = identifier;
            }

            const firstInput = nextEl.querySelector('input:not([readonly]):not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }
        }, 200);
    }

    // Initial check user flow
    const initialContinueBtn = document.getElementById('initial-continue-btn');
    const initialInput = document.getElementById('initial_identifier');

    function checkUserAndProceed() {
        const identifier = initialInput ? initialInput.value.trim() : '';
        if (!identifier) {
            showToast('Please enter an email address or mobile number.', 'error');
            return;
        }

        const btnText = initialContinueBtn.querySelector('.btn-text');
        const btnSpinner = initialContinueBtn.querySelector('.btn-spinner');
        const btnArrow = initialContinueBtn.querySelector('.btn-arrow');

        if (btnText && btnSpinner && btnArrow) {
            btnText.style.display = 'none';
            btnArrow.style.display = 'none';
            btnSpinner.style.display = 'inline-flex';
        }
        initialContinueBtn.disabled = true;

        fetch('/auth/check-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({ identifier: identifier })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                // Registered user -> Show Password / Login Screen
                const loginIdHidden = document.getElementById('login_identifier_for_login');
                const loginIdDisplay = document.getElementById('login_identifier_display');
                if (loginIdHidden) loginIdHidden.value = identifier;
                if (loginIdDisplay) loginIdDisplay.value = identifier;

                transitionTo('login', identifier);
            } else {
                // New user -> Show Registration Form & Auto-fill entered value
                const isEmail = identifier.includes('@');
                const regEmail = document.getElementById('register_email');
                const regMobile = document.getElementById('register_mobile');
                const changeBtn = document.getElementById('change-identifier-from-register');

                if (isEmail) {
                    if (regEmail) {
                        regEmail.value = identifier;
                        regEmail.readOnly = true;
                        regEmail.classList.add('readonly-input');
                    }
                    if (regMobile) {
                        regMobile.readOnly = false;
                        regMobile.classList.remove('readonly-input');
                    }
                } else {
                    if (regMobile) {
                        regMobile.value = identifier;
                        regMobile.readOnly = true;
                        regMobile.classList.add('readonly-input');
                    }
                    if (regEmail) {
                        regEmail.readOnly = false;
                        regEmail.classList.remove('readonly-input');
                    }
                }

                if (changeBtn) changeBtn.style.display = 'inline-block';
                transitionTo('register', identifier);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Something went wrong. Please check your network.', 'error');
        })
        .finally(() => {
            if (btnText && btnSpinner && btnArrow) {
                btnText.style.display = 'inline';
                btnArrow.style.display = 'inline';
                btnSpinner.style.display = 'none';
            }
            initialContinueBtn.disabled = false;
        });
    }

    if (initialContinueBtn) {
        initialContinueBtn.addEventListener('click', checkUserAndProceed);
    }
    if (initialInput) {
        initialInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkUserAndProceed();
            }
        });
    }

    const initialForm = document.getElementById('form-initial');
    if (initialForm) {
        initialForm.addEventListener('submit', function(e) {
            e.preventDefault();
            checkUserAndProceed();
        });
    }

    // AJAX Form Submission for Login (Zero Page Reload on Wrong Password)
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('login-submit-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';

            const formData = new FormData(loginForm);

            fetch(loginForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                if (res.status === 419) {
                    showToast('Session expired. Refreshing page for a fresh session...', 'error');
                    setTimeout(() => window.location.reload(), 1200);
                    return;
                }
                const data = await res.json();
                if (res.ok && data.success) {
                    window.location.href = data.redirect || '/';
                } else {
                    const errorMsg = data.errors && data.errors.login_identifier ? data.errors.login_identifier[0] : (data.message || 'Incorrect password. Please try again.');
                    
                    // Show Red Error Banner Above Form
                    const alertEl = document.getElementById('login-alert');
                    const alertMsg = document.getElementById('login-alert-msg');
                    if (alertEl && alertMsg) {
                        alertMsg.textContent = errorMsg;
                        alertEl.style.display = 'flex';
                    }

                    // Show Inline Red Error Under Password Input
                    const inlineErrSpan = document.getElementById('login-inline-error');
                    const inlineErrText = document.getElementById('login-error-text');
                    const passInput = document.getElementById('password_for_login');
                    if (inlineErrSpan && inlineErrText) {
                        inlineErrText.textContent = errorMsg;
                        inlineErrSpan.style.display = 'block';
                    }
                    if (passInput) {
                        passInput.classList.add('is-invalid');
                        passInput.value = '';
                        passInput.focus();
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Network error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // AJAX Form Submission for Forgot Password
    const forgotForm = document.getElementById('form-forgot-password');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('forgot-submit-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending link...';

            const formData = new FormData(forgotForm);

            fetch(forgotForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                const alertEl = document.getElementById('forgot-alert');
                const alertMsg = document.getElementById('forgot-alert-msg');
                const alertIcon = document.getElementById('forgot-alert-icon');

                if (res.status === 200 || data.success || data.status) {
                    if (alertEl && alertMsg) {
                        alertMsg.textContent = 'Reset link has been sent to your email!';
                        alertEl.className = 'auth-alert success';
                        if (alertIcon) alertIcon.className = 'bi bi-check-circle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                } else {
                    const errorMsg = data.errors && data.errors.email ? data.errors.email[0] : (data.message || 'We could not find a user with that email address.');
                    if (alertEl && alertMsg) {
                        alertMsg.textContent = errorMsg;
                        alertEl.className = 'auth-alert error';
                        if (alertIcon) alertIcon.className = 'bi bi-exclamation-triangle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Network error occurred. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    }

    // AJAX Form Submission for Registration (Zero Page Reload on Wrong OTP)
    const registerForm = document.getElementById('form-register');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const createBtn = document.getElementById('create-account-btn');
            const originalBtnText = createBtn.innerHTML;
            createBtn.disabled = true;
            createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';

            const formData = new FormData(registerForm);

            fetch(registerForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    // Success -> Redirect to target URL
                    window.location.href = data.redirect || '/';
                } else if (res.status === 422 || !data.success) {
                    // Validation Error (Wrong OTP, etc.) -> NO PAGE RELOAD
                    const otpError = data.errors && data.errors.otp ? data.errors.otp[0] : (data.message || 'Invalid or expired OTP. Please try again.');
                    
                    // Show Red Error Banner
                    showBannerAlert(otpError, 'error');

                    // Show Inline Red Error Under Input
                    const inlineErrSpan = document.getElementById('otp-inline-error');
                    const inlineErrText = document.getElementById('otp-error-text');
                    const otpInput = document.getElementById('otp');
                    if (inlineErrSpan && inlineErrText) {
                        inlineErrText.textContent = otpError;
                        inlineErrSpan.style.display = 'block';
                    }
                    if (otpInput) {
                        otpInput.classList.add('is-invalid');
                        otpInput.value = '';
                        otpInput.focus();
                    }
                } else {
                    showBannerAlert(data.message || 'An error occurred.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showBannerAlert('Network error occurred. Please try again.', 'error');
            })
            .finally(() => {
                createBtn.disabled = false;
                createBtn.innerHTML = originalBtnText;
            });
        });
    }

    // Change identifier / back buttons
    const changeLoginBtn = document.getElementById('change-identifier-from-login');
    if (changeLoginBtn) {
        changeLoginBtn.addEventListener('click', function() {
            transitionTo('initial');
        });
    }

    const changeRegBtn = document.getElementById('change-identifier-from-register');
    if (changeRegBtn) {
        changeRegBtn.addEventListener('click', function() {
            hideBannerAlert();
            transitionTo('initial');
        });
    }

    const regTopBackBtn = document.getElementById('register-top-back-btn');
    if (regTopBackBtn) {
        regTopBackBtn.addEventListener('click', function() {
            hideBannerAlert();
            transitionTo('initial');
        });
    }

    const showForgotBtn = document.getElementById('show-forgot-password');
    if (showForgotBtn) {
        showForgotBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const forgotInput = document.getElementById('email_for_forgot');
            if (forgotInput && currentIdentifier.includes('@')) {
                forgotInput.value = currentIdentifier;
            }
            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';
            transitionTo('forgot');
        });
    }

    const backToLoginBtn = document.getElementById('back-to-login');
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            transitionTo('login');
        });
    }

    // Send OTP logic
    const sendOtpBtn = document.getElementById('send-otp-btn');
    if (sendOtpBtn) {
        sendOtpBtn.addEventListener('click', function() {
            const emailInput = document.getElementById('register_email');
            const email = emailInput ? emailInput.value.trim() : '';

            if (!email || !email.includes('@')) {
                showBannerAlert('Please enter a valid email address to receive OTP.', 'error');
                return;
            }

            sendOtpBtn.disabled = true;
            sendOtpBtn.textContent = 'Sending...';
            showBannerAlert('Sending OTP...', 'info');

            fetch('/auth/send-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken()
                },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showBannerAlert('OTP sent successfully!', 'success');
                    let countdown = 60;
                    const timer = setInterval(() => {
                        sendOtpBtn.textContent = `Resend (${countdown}s)`;
                        countdown--;
                        if (countdown < 0) {
                            clearInterval(timer);
                            sendOtpBtn.disabled = false;
                            sendOtpBtn.textContent = 'Resend OTP';
                        }
                    }, 1000);
                } else {
                    showBannerAlert(data.message || 'Failed to send OTP.', 'error');
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.textContent = 'Send OTP';
                }
            })
            .catch(err => {
                console.error(err);
                showBannerAlert('Error sending OTP. Please try again.', 'error');
                sendOtpBtn.disabled = false;
                sendOtpBtn.textContent = 'Send OTP';
            });
        });
    }
});
