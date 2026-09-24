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

    function showInitialAlert(message, type = 'error') {
        const alertEl = document.getElementById('initial-alert');
        const alertMsg = document.getElementById('initial-alert-msg');
        const alertIcon = document.getElementById('initial-alert-icon');

        if (alertEl && alertMsg) {
            alertMsg.textContent = message;
            alertEl.className = `auth-alert ${type}`;
            if (alertIcon) {
                alertIcon.className = type === 'success' ? 'bi bi-check-circle-fill me-2' : (type === 'info' ? 'bi bi-hourglass-split me-2 spin-icon' : 'bi bi-exclamation-triangle-fill me-2');
            }
            alertEl.style.display = 'flex';
        }
    }

    function hideInitialAlert() {
        const alertEl = document.getElementById('initial-alert');
        if (alertEl) alertEl.style.display = 'none';
    }

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
        hideInitialAlert();

        const identifier = initialInput ? initialInput.value.trim() : '';
        if (!identifier) {
            showInitialAlert('Please enter an email address or mobile number.');
            return;
        }

        // Strict format validation
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const hasLetters = /[a-zA-Z]/.test(identifier);
        const cleanPhone = identifier.replace(/[\s\-\(\)]/g, '');
        const phoneRegex = /^(\+?\d{1,3})?[6-9]\d{9}$/;
        const internationalPhoneRegex = /^\+?[1-9]\d{9,14}$/;

        const isEmail = emailRegex.test(identifier);
        const isPhone = !hasLetters && (phoneRegex.test(cleanPhone) || internationalPhoneRegex.test(cleanPhone));

        if (!isEmail && !isPhone) {
            showInitialAlert('Please enter a valid Email (e.g. name@example.com) or 10-digit Mobile number.');
            if (initialInput) initialInput.focus();
            return;
        }

        const btnText = initialContinueBtn.querySelector('.btn-text');
        const btnSpinner = initialContinueBtn.querySelector('.btn-spinner');
        const btnArrow = initialContinueBtn.querySelector('.btn-arrow');

        function resetButtonState() {
            if (btnText && btnSpinner && btnArrow) {
                btnText.style.display = 'inline';
                btnArrow.style.display = 'inline';
                btnSpinner.style.display = 'none';
            }
            initialContinueBtn.disabled = false;
        }

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
        .then(res => {
            if (!res.ok) {
                return res.json().then(err => { throw err; });
            }
            return res.json();
        })
        .then(data => {
            if (data.blocked) {
                resetButtonState();
                showInitialAlert(data.message || 'Your account has been blocked. Please contact support.');
                return;
            }

            const isUserExisting = (data.exists === true) || (data.data && data.data.exists === true);

            if (isUserExisting) {
                // Registered user -> Show Password / Login Screen
                const loginIdHidden = document.getElementById('login_identifier_for_login');
                const loginIdDisplay = document.getElementById('login_identifier_display');
                if (loginIdHidden) loginIdHidden.value = identifier;
                if (loginIdDisplay) loginIdDisplay.value = identifier;

                resetButtonState();
                transitionTo('login', identifier);
            } else {
                // New user -> Show Registration Form & Auto-fill entered value
                const isEmailType = identifier.includes('@');
                const regEmail = document.getElementById('register_email');
                const regMobile = document.getElementById('register_mobile');
                const changeBtn = document.getElementById('change-identifier-from-register');

                if (isEmailType) {
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

                // Reset to step 1 (details)
                regCurrentSubstep = 'details';
                if (regSubsteps.details) regSubsteps.details.style.display = 'block';
                if (regSubsteps.otp) regSubsteps.otp.style.display = 'none';
                if (regSubsteps.password) regSubsteps.password.style.display = 'none';
                hideRegAlert('details');
                hideRegAlert('otp');
                hideRegAlert('password');

                resetButtonState();
                transitionTo('register', identifier);
            }
        })
        .catch(err => {
            console.error(err);
            resetButtonState();
            const msg = (err && err.message) ? err.message : 'Something went wrong. Please check your network.';
            showInitialAlert(msg);
        })
        .finally(() => {
            resetButtonState();
        });
    }

    if (initialContinueBtn) {
        initialContinueBtn.addEventListener('click', checkUserAndProceed);
    }
    if (initialInput) {
        initialInput.addEventListener('input', hideInitialAlert);
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

                    const passInput = document.getElementById('password_for_login');
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

    // Step 1: Send WhatsApp OTP for Forgot Password
    const forgotRequestOtpForm = document.getElementById('form-forgot-request-otp');
    if (forgotRequestOtpForm) {
        forgotRequestOtpForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('forgot-send-otp-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending OTP...';

            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';

            const mobile = document.getElementById('mobile_number_for_forgot').value.trim();

            fetch('/forgot-password/send-otp', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mobile_number: mobile })
            })
            .then(async res => {
                const data = await res.json();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (res.ok && data.success) {
                    // Pre-fill hidden mobile in step 2 form
                    document.getElementById('reset_modal_mobile_number').value = mobile;
                    forgotRequestOtpForm.style.display = 'none';
                    const resetForm = document.getElementById('form-forgot-reset-password');
                    if (resetForm) resetForm.style.display = 'block';

                    const subText = document.getElementById('forgot-subtitle-text');
                    if (subText) subText.textContent = 'Enter the 6-digit WhatsApp OTP code and your new password.';

                    if (alertEl) {
                        document.getElementById('forgot-alert-msg').textContent = data.message + (data.dev_otp ? ' (Dev Code: ' + data.dev_otp + ')' : '');
                        alertEl.className = 'auth-alert success';
                        document.getElementById('forgot-alert-icon').className = 'bi bi-check-circle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                } else {
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Mobile number not found.');
                    if (alertEl) {
                        document.getElementById('forgot-alert-msg').textContent = msg;
                        alertEl.className = 'auth-alert error';
                        document.getElementById('forgot-alert-icon').className = 'bi bi-exclamation-triangle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (alertEl) {
                    document.getElementById('forgot-alert-msg').textContent = 'An unexpected error occurred.';
                    alertEl.className = 'auth-alert error';
                    document.getElementById('forgot-alert-icon').className = 'bi bi-exclamation-triangle-fill me-2';
                    alertEl.style.display = 'flex';
                }
            });
        });
    }

    // Step 2: Reset Password & Auto-Login inside Modal
    const forgotResetForm = document.getElementById('form-forgot-reset-password');
    if (forgotResetForm) {
        forgotResetForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('forgot-reset-btn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';

            const formData = new FormData(forgotResetForm);

            fetch('/reset-password', {
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
                    if (alertEl) {
                        document.getElementById('forgot-alert-msg').textContent = 'Password reset successfully! Redirecting...';
                        alertEl.className = 'auth-alert success';
                        document.getElementById('forgot-alert-icon').className = 'bi bi-check-circle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect || '/';
                    }, 1000);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    const msg = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not reset password.');
                    if (alertEl) {
                        document.getElementById('forgot-alert-msg').textContent = msg;
                        alertEl.className = 'auth-alert error';
                        document.getElementById('forgot-alert-icon').className = 'bi bi-exclamation-triangle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                }
            })
            .catch(err => {
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (alertEl) {
                    document.getElementById('forgot-alert-msg').textContent = 'An unexpected error occurred.';
                    alertEl.className = 'auth-alert error';
                    document.getElementById('forgot-alert-icon').className = 'bi bi-exclamation-triangle-fill me-2';
                    alertEl.style.display = 'flex';
                }
            });
        });
    }

    // Registration Multi-Step State Management
    const regSubsteps = {
        details: document.getElementById('reg-substep-details'),
        otp: document.getElementById('reg-substep-otp'),
        password: document.getElementById('reg-substep-password'),
    };

    let regCurrentSubstep = 'details';
    let regData = {
        name: '',
        mobile_number: '',
        email: '',
        referral_code: '',
        otp: ''
    };

    function showRegAlert(substep, message, type = 'error') {
        const alertEl = document.getElementById(`register-${substep}-alert`);
        const alertMsg = document.getElementById(`register-${substep}-alert-msg`);
        const alertIcon = document.getElementById(`register-${substep}-alert-icon`);

        if (alertEl && alertMsg) {
            alertMsg.textContent = message;
            alertEl.className = `auth-alert ${type}`;
            if (alertIcon) {
                alertIcon.className = type === 'success' ? 'bi bi-check-circle-fill me-2' : (type === 'info' ? 'bi bi-hourglass-split me-2 spin-icon' : 'bi bi-exclamation-triangle-fill me-2');
            }
            alertEl.style.display = 'flex';
        }
    }

    function hideRegAlert(substep) {
        const alertEl = document.getElementById(`register-${substep}-alert`);
        if (alertEl) alertEl.style.display = 'none';
    }

    function transitionRegSubstep(toSubstep) {
        if (!regSubsteps[toSubstep] || regCurrentSubstep === toSubstep) return;

        const currentEl = regSubsteps[regCurrentSubstep];
        const nextEl = regSubsteps[toSubstep];

        currentEl.style.opacity = '0';
        currentEl.style.transform = 'translateY(-6px)';

        setTimeout(() => {
            currentEl.style.display = 'none';
            nextEl.style.display = 'block';

            setTimeout(() => {
                nextEl.style.opacity = '1';
                nextEl.style.transform = 'translateY(0)';
            }, 30);

            regCurrentSubstep = toSubstep;

            if (toSubstep === 'otp') {
                const firstBox = document.querySelector('.otp-digit-input[data-index="0"]');
                if (firstBox) firstBox.focus();
            } else {
                const firstInput = nextEl.querySelector('input:not([readonly]):not([type="hidden"])');
                if (firstInput) {
                    firstInput.focus();
                }
            }
        }, 150);
    }

    // Step 1 Details -> Step 2 OTP
    const continueToOtpBtn = document.getElementById('reg-continue-to-otp-btn');
    if (continueToOtpBtn) {
        continueToOtpBtn.addEventListener('click', function() {
            hideRegAlert('details');

            const nameInp = document.getElementById('name');
            const mobileInp = document.getElementById('register_mobile');
            const emailInp = document.getElementById('register_email');
            const refInp = document.getElementById('referral_code');

            const name = nameInp ? nameInp.value.trim() : '';
            const mobile = mobileInp ? mobileInp.value.trim() : '';
            const email = emailInp ? emailInp.value.trim() : '';
            const ref = refInp ? refInp.value.trim() : '';

            // Reset field errors
            [nameInp, mobileInp, emailInp].forEach(inp => inp && inp.classList.remove('is-invalid'));

            if (!name) {
                showRegAlert('details', 'Please enter your full name.');
                if (nameInp) { nameInp.classList.add('is-invalid'); nameInp.focus(); }
                return;
            }

            const cleanPhone = mobile.replace(/[\s\-]/g, '');
            if (!cleanPhone || !/^\d{10}$/.test(cleanPhone)) {
                showRegAlert('details', 'Please enter a valid 10-digit mobile number.');
                if (mobileInp) { mobileInp.classList.add('is-invalid'); mobileInp.focus(); }
                return;
            }

            if (email && (!email.includes('@') || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))) {
                showRegAlert('details', 'Please enter a valid email address.');
                if (emailInp) { emailInp.classList.add('is-invalid'); emailInp.focus(); }
                return;
            }

            // Save details
            regData.name = name;
            regData.mobile_number = cleanPhone;
            regData.email = email;
            regData.referral_code = ref;

            // Trigger OTP send
            const originalText = continueToOtpBtn.innerHTML;
            continueToOtpBtn.disabled = true;
            continueToOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending WhatsApp OTP...';

            fetch('/auth/send-whatsapp-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mobile_number: regData.mobile_number
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    // Update mobile display in step 2
                    const emailDisp = document.getElementById('reg-display-email');
                    if (emailDisp) emailDisp.textContent = regData.mobile_number + (body.dev_otp ? ' (Dev Code: ' + body.dev_otp + ')' : '');

                    startOtpCountdown();
                    transitionRegSubstep('otp');
                } else {
                    let errMsg = body.message || 'Failed to send verification code.';
                    if (body.errors) {
                        if (body.errors.mobile_number) {
                            errMsg = body.errors.mobile_number[0];
                            if (mobileInp) { mobileInp.classList.add('is-invalid'); mobileInp.focus(); }
                        } else if (body.errors.email) {
                            errMsg = body.errors.email[0];
                            if (emailInp) { emailInp.classList.add('is-invalid'); emailInp.focus(); }
                        }
                    }
                    showRegAlert('details', errMsg, 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showRegAlert('details', 'Network error. Please check your connection.', 'error');
            })
            .finally(() => {
                continueToOtpBtn.disabled = false;
                continueToOtpBtn.innerHTML = originalText;
            });
        });
    }

    // OTP Countdown Timer Logic
    let otpTimerInterval = null;
    function startOtpCountdown() {
        const resendBtn = document.getElementById('reg-resend-otp-btn');
        const countdownPill = document.getElementById('otp-countdown-pill');
        const countdownText = document.getElementById('otp-countdown-text');
        if (!resendBtn) return;

        if (otpTimerInterval) clearInterval(otpTimerInterval);

        let countdown = 60;
        resendBtn.style.display = 'none';
        if (countdownPill) countdownPill.style.display = 'inline-flex';
        if (countdownText) countdownText.textContent = `${countdown}s`;

        otpTimerInterval = setInterval(() => {
            countdown--;
            if (countdownText) countdownText.textContent = `${countdown}s`;
            if (countdown <= 0) {
                clearInterval(otpTimerInterval);
                if (countdownPill) countdownPill.style.display = 'none';
                resendBtn.style.display = 'inline-flex';
                resendBtn.disabled = false;
            }
        }, 1000);
    }

    // 6-Digit Individual OTP Input Boxes Management
    const otpBoxes = Array.from(document.querySelectorAll('.otp-digit-input'));
    const regOtpInp = document.getElementById('reg_otp');

    function syncOtpFromBoxes() {
        const otpStr = otpBoxes.map(b => b.value).join('');
        if (regOtpInp) regOtpInp.value = otpStr;
        return otpStr;
    }

    function clearOtpBoxes() {
        otpBoxes.forEach(b => {
            b.value = '';
            b.classList.remove('filled', 'is-invalid');
        });
        if (regOtpInp) regOtpInp.value = '';
        if (otpBoxes[0]) otpBoxes[0].focus();
    }

    otpBoxes.forEach((box, index) => {
        box.addEventListener('input', function(e) {
            box.classList.remove('is-invalid');
            const val = box.value.replace(/\D/g, '');
            box.value = val ? val.slice(-1) : '';

            if (box.value) {
                box.classList.add('filled');
                if (index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            } else {
                box.classList.remove('filled');
            }

            const currentOtp = syncOtpFromBoxes();
            if (currentOtp.length === 6) {
                executeOtpVerification();
            }
        });

        box.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace') {
                if (!box.value && index > 0) {
                    otpBoxes[index - 1].focus();
                    otpBoxes[index - 1].value = '';
                    otpBoxes[index - 1].classList.remove('filled');
                    syncOtpFromBoxes();
                } else {
                    box.value = '';
                    box.classList.remove('filled');
                    syncOtpFromBoxes();
                }
            } else if (e.key === 'ArrowLeft' && index > 0) {
                otpBoxes[index - 1].focus();
            } else if (e.key === 'ArrowRight' && index < otpBoxes.length - 1) {
                otpBoxes[index + 1].focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                executeOtpVerification();
            }
        });

        box.addEventListener('paste', function(e) {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
            const cleanDigits = pasteData.replace(/\D/g, '').slice(0, 6);

            if (cleanDigits) {
                cleanDigits.split('').forEach((char, i) => {
                    if (otpBoxes[i]) {
                        otpBoxes[i].value = char;
                        otpBoxes[i].classList.add('filled');
                        otpBoxes[i].classList.remove('is-invalid');
                    }
                });

                const lastIdx = Math.min(cleanDigits.length - 1, otpBoxes.length - 1);
                if (otpBoxes[lastIdx]) otpBoxes[lastIdx].focus();

                const fullOtp = syncOtpFromBoxes();
                if (fullOtp.length === 6) {
                    executeOtpVerification();
                }
            }
        });
    });

    // Resend OTP in Step 2
    const regResendBtn = document.getElementById('reg-resend-otp-btn');
    if (regResendBtn) {
        regResendBtn.addEventListener('click', function() {
            if (regResendBtn.disabled || !regData.mobile_number) return;

            regResendBtn.disabled = true;
            regResendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Sending...';
            showRegAlert('otp', 'Resending WhatsApp OTP code...', 'info');

            fetch('/auth/send-whatsapp-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mobile_number: regData.mobile_number })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showRegAlert('otp', 'New WhatsApp code sent!' + (data.dev_otp ? ' (Dev Code: ' + data.dev_otp + ')' : ''), 'success');
                    clearOtpBoxes();
                    startOtpCountdown();
                } else {
                    showRegAlert('otp', data.message || 'Failed to resend OTP.', 'error');
                    regResendBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                showRegAlert('otp', 'Error resending code. Please try again.', 'error');
                regResendBtn.disabled = false;
            })
            .finally(() => {
                regResendBtn.innerHTML = '<i class="bi bi-arrow-repeat me-0.5"></i> Resend OTP';
            });
        });
    }

    // Step 2 OTP Verification -> Step 3 Password
    const verifyOtpBtn = document.getElementById('reg-verify-otp-btn');

    function executeOtpVerification() {
        hideRegAlert('otp');
        const otpVal = syncOtpFromBoxes();

        if (!otpVal || otpVal.length !== 6) {
            showRegAlert('otp', 'Please enter the full 6-digit code.', 'error');
            otpBoxes.forEach(b => { if (!b.value) b.classList.add('is-invalid'); });
            const firstEmpty = otpBoxes.find(b => !b.value);
            if (firstEmpty) firstEmpty.focus();
            return;
        }

        const originalText = verifyOtpBtn.innerHTML;
        verifyOtpBtn.disabled = true;
        verifyOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying Code...';

        fetch('/auth/verify-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                mobile_number: regData.mobile_number,
                otp: otpVal
            })
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                regData.otp = otpVal;

                // Populate hidden inputs in final step form
                const fName = document.getElementById('final_reg_name');
                const fMob = document.getElementById('final_reg_mobile');
                const fEmail = document.getElementById('final_reg_email');
                const fRef = document.getElementById('final_reg_referral');
                const fOtp = document.getElementById('final_reg_otp');

                if (fName) fName.value = regData.name;
                if (fMob) fMob.value = regData.mobile_number;
                if (fEmail) fEmail.value = regData.email;
                if (fRef) fRef.value = regData.referral_code;
                if (fOtp) fOtp.value = regData.otp;

                transitionRegSubstep('password');
            } else {
                showRegAlert('otp', body.message || 'Invalid or expired code. Please try again.', 'error');
                otpBoxes.forEach(b => {
                    b.classList.add('is-invalid');
                    b.value = '';
                    b.classList.remove('filled');
                });
                if (otpBoxes[0]) otpBoxes[0].focus();
                syncOtpFromBoxes();
            }
        })
        .catch(err => {
            console.error(err);
            showRegAlert('otp', 'Network error. Please try again.', 'error');
        })
        .finally(() => {
            verifyOtpBtn.disabled = false;
            verifyOtpBtn.innerHTML = originalText;
        });
    }

    if (verifyOtpBtn) {
        verifyOtpBtn.addEventListener('click', executeOtpVerification);
    }

    // Step 3 Password Submission (Final Account Creation)
    const finalRegisterForm = document.getElementById('form-register-final');
    if (finalRegisterForm) {
        finalRegisterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            hideRegAlert('password');

            const pwInp = document.getElementById('register_password');
            const pwConfInp = document.getElementById('password_confirmation');
            const pw = pwInp ? pwInp.value : '';
            const pwConf = pwConfInp ? pwConfInp.value : '';

            [pwInp, pwConfInp].forEach(inp => inp && inp.classList.remove('is-invalid'));

            if (!pw || pw.length < 8) {
                showRegAlert('password', 'Password must be at least 8 characters long.', 'error');
                if (pwInp) { pwInp.classList.add('is-invalid'); pwInp.focus(); }
                return;
            }

            if (pw !== pwConf) {
                showRegAlert('password', 'Passwords do not match. Please re-check.', 'error');
                if (pwConfInp) { pwConfInp.classList.add('is-invalid'); pwConfInp.focus(); }
                return;
            }

            const createBtn = document.getElementById('create-account-btn');
            const originalBtnText = createBtn.innerHTML;
            createBtn.disabled = true;
            createBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';

            const formData = new FormData(finalRegisterForm);

            fetch(finalRegisterForm.action, {
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
                    window.location.href = data.redirect || '/';
                } else {
                    let errMsg = data.message || 'Registration failed. Please try again.';
                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];
                        errMsg = data.errors[firstKey][0];
                    }
                    showRegAlert('password', errMsg, 'error');
                    createBtn.disabled = false;
                    createBtn.innerHTML = originalBtnText;
                }
            })
            .catch(err => {
                console.error(err);
                showRegAlert('password', 'Network error occurred. Please try again.', 'error');
                createBtn.disabled = false;
                createBtn.innerHTML = originalBtnText;
            });
        });
    }

    // Navigation & Back buttons
    const changeLoginBtn = document.getElementById('change-identifier-from-login');
    if (changeLoginBtn) {
        changeLoginBtn.addEventListener('click', function() {
            transitionTo('initial');
        });
    }

    const regTopBackBtn = document.getElementById('register-top-back-btn');
    if (regTopBackBtn) {
        regTopBackBtn.addEventListener('click', function() {
            hideRegAlert('details');
            transitionTo('initial');
        });
    }

    const regOtpBackBtn = document.getElementById('reg-otp-back-btn');
    if (regOtpBackBtn) {
        regOtpBackBtn.addEventListener('click', function() {
            hideRegAlert('otp');
            transitionRegSubstep('details');
        });
    }

    const editEmailBtn = document.getElementById('edit-reg-email-btn');
    if (editEmailBtn) {
        editEmailBtn.addEventListener('click', function() {
            hideRegAlert('otp');
            transitionRegSubstep('details');
            const emailInp = document.getElementById('register_email');
            if (emailInp) emailInp.focus();
        });
    }

    const showForgotBtn = document.getElementById('show-forgot-password');
    if (showForgotBtn) {
        showForgotBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const forgotInput = document.getElementById('mobile_number_for_forgot');
            if (forgotInput && /^\d+$/.test(currentIdentifier)) {
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
            const reqForm = document.getElementById('form-forgot-request-otp');
            const resetForm = document.getElementById('form-forgot-reset-password');
            if (reqForm) reqForm.style.display = 'block';
            if (resetForm) resetForm.style.display = 'none';
            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';
            transitionTo(currentIdentifier ? 'login' : 'initial');
        });
    }

});
