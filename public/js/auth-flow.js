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

    let currentState = 'initial';
    let currentIdentifier = '';
    let currentUserData = {};

    function maskMobile(phone) {
        if (!phone) return '••******••';
        if (phone.includes('*')) return phone;
        const clean = phone.replace(/\D/g, '');
        if (clean.length >= 10) {
            const ten = clean.slice(-10);
            return ten.slice(0, 2) + '******' + ten.slice(-2);
        }
        return phone;
    }

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

            if (stateName === 'login') {
                const pwInput = document.getElementById('password_for_login');
                if (pwInput) {
                    pwInput.value = '';
                }
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

        const rawVal = initialInput ? initialInput.value.trim() : '';
        if (!rawVal) {
            showInitialAlert('Please enter your 10-digit mobile number.');
            return;
        }

        const cleanDigits = rawVal.replace(/\D/g, '');
        const phone10 = cleanDigits.length > 10 ? cleanDigits.slice(-10) : cleanDigits;

        if (phone10.length !== 10 || !/^[6-9]\d{9}$/.test(phone10)) {
            showInitialAlert('Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
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
            body: JSON.stringify({ identifier: phone10 })
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
                // Registered user -> Show Password / OTP Login Screen
                currentUserData = (data && data.data) ? data.data : {};
                const loginIdHidden = document.getElementById('login_identifier_for_login');
                const loginIdDisplay = document.getElementById('login_identifier_display');
                if (loginIdHidden) loginIdHidden.value = phone10;
                if (loginIdDisplay) loginIdDisplay.value = phone10;

                const loginPw = document.getElementById('password_for_login');
                if (loginPw) loginPw.value = '';

                resetButtonState();
                transitionTo('login', phone10);
            } else {
                // New user -> Show Registration Form & Auto-fill mobile number
                const regMobile = document.getElementById('register_mobile');
                const changeMobileBtn = document.getElementById('change-identifier-from-register');

                if (regMobile) {
                    regMobile.value = phone10;
                    regMobile.readOnly = true;
                    regMobile.classList.add('readonly-input');
                }
                if (changeMobileBtn) changeMobileBtn.style.display = 'inline-block';

                // Reset to step 1 (details)
                regCurrentSubstep = 'details';
                if (regSubsteps.details) regSubsteps.details.style.display = 'block';
                if (regSubsteps.otp) regSubsteps.otp.style.display = 'none';
                hideRegAlert('details');
                hideRegAlert('otp');

                resetButtonState();
                transitionTo('register', phone10);
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

    // Web 1-Tap WhatsApp OTP Login
    const webLoginOtpBtn = document.getElementById('web-login-otp-btn');
    const webLoginOtpSection = document.getElementById('web-login-otp-section');
    const webPasswordSection = document.getElementById('web-password-section');
    const webLoginDivider = document.getElementById('web-login-divider');
    const webCancelOtpBtn = document.getElementById('web-cancel-otp-btn');
    const webLoginVerifyBtn = document.getElementById('web-login-verify-btn');
    const webLoginResendBtn = document.getElementById('web-login-resend-btn');
    const webLoginOtpInput = document.getElementById('web-login-otp-input');
    const webLoginCountdown = document.getElementById('web-login-countdown');
    let webOtpTimer = null;

    function startWebOtpCountdown() {
        let sec = 60;
        if (webLoginCountdown) webLoginCountdown.textContent = sec + 's';
        if (webLoginResendBtn) webLoginResendBtn.style.display = 'none';
        if (webOtpTimer) clearInterval(webOtpTimer);
        webOtpTimer = setInterval(() => {
            sec--;
            if (webLoginCountdown) webLoginCountdown.textContent = sec + 's';
            if (sec <= 0) {
                clearInterval(webOtpTimer);
                if (webLoginCountdown) webLoginCountdown.textContent = 'Expired';
                if (webLoginResendBtn) webLoginResendBtn.style.display = 'inline-block';
            }
        }, 1000);
    }

    if (webLoginOtpBtn) {
        webLoginOtpBtn.addEventListener('click', function() {
            const originalBtn = webLoginOtpBtn.innerHTML;
            webLoginOtpBtn.disabled = true;
            webLoginOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending WhatsApp OTP...';

            fetch('/api/v1/auth/send-whatsapp-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    identifier: currentIdentifier,
                    type: 'LOGIN'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('WhatsApp OTP sent successfully!', 'success');
                    if (webLoginOtpSection) webLoginOtpSection.style.display = 'block';
                    if (webPasswordSection) webPasswordSection.style.display = 'none';
                    if (webLoginDivider) webLoginDivider.style.display = 'none';
                    if (webLoginOtpBtn) webLoginOtpBtn.style.display = 'none';
                    startWebOtpCountdown();
                    if (webLoginOtpInput) webLoginOtpInput.focus();
                } else {
                    showToast(data.message || 'Failed to send WhatsApp OTP.', 'error');
                }
            })
            .catch(() => showToast('Network error while sending OTP.', 'error'))
            .finally(() => {
                webLoginOtpBtn.disabled = false;
                webLoginOtpBtn.innerHTML = originalBtn;
            });
        });
    }

    if (webCancelOtpBtn) {
        webCancelOtpBtn.addEventListener('click', function() {
            if (webLoginOtpSection) webLoginOtpSection.style.display = 'none';
            if (webPasswordSection) webPasswordSection.style.display = 'block';
            if (webLoginDivider) webLoginDivider.style.display = 'flex';
            if (webLoginOtpBtn) webLoginOtpBtn.style.display = 'block';
        });
    }

    if (webLoginResendBtn) {
        webLoginResendBtn.addEventListener('click', function() {
            if (webLoginOtpBtn) webLoginOtpBtn.click();
        });
    }

    if (webLoginVerifyBtn) {
        webLoginVerifyBtn.addEventListener('click', function() {
            const otpVal = webLoginOtpInput ? webLoginOtpInput.value.trim() : '';
            if (otpVal.length !== 6) {
                showToast('Please enter the 6-digit WhatsApp OTP.', 'error');
                return;
            }

            const originalBtn = webLoginVerifyBtn.innerHTML;
            webLoginVerifyBtn.disabled = true;
            webLoginVerifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verifying...';

            fetch('/auth/login-otp', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    mobile_number: currentIdentifier,
                    otp: otpVal
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Login successful! Redirecting...', 'success');
                    window.location.href = data.redirect || '/';
                } else {
                    showToast(data.message || 'Invalid or expired OTP. Please try again.', 'error');
                }
            })
            .catch(() => showToast('Network error while verifying OTP.', 'error'))
            .finally(() => {
                webLoginVerifyBtn.disabled = false;
                webLoginVerifyBtn.innerHTML = originalBtn;
            });
        });
    }

    // Step 1: Send WhatsApp OTP for Forgot Password (Masked Confirmation Flow)
    const forgotSendOtpBtn = document.getElementById('forgot-send-otp-btn');
    if (forgotSendOtpBtn) {
        forgotSendOtpBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const submitBtn = forgotSendOtpBtn;
            const originalBtnText = submitBtn.innerHTML;
            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';

            let identifier = currentIdentifier;
            if (!identifier && currentUserData && currentUserData.identifier) {
                identifier = currentUserData.identifier;
            }
            if (!identifier) {
                identifier = document.getElementById('mobile_number_for_forgot') ? document.getElementById('mobile_number_for_forgot').value.trim() : '';
            }

            if (!identifier) {
                if (alertEl) {
                    document.getElementById('forgot-alert-msg').textContent = 'No account identifier found. Please return to sign in.';
                    alertEl.className = 'auth-alert error';
                    document.getElementById('forgot-alert-icon').className = 'bi bi-exclamation-triangle-fill me-2';
                    alertEl.style.display = 'flex';
                }
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending WhatsApp OTP...';

            fetch('/forgot-password/send-otp', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ identifier: identifier, mobile_number: identifier })
            })
            .then(async res => {
                const data = await res.json();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (res.ok && data.success) {
                    // Pre-fill hidden mobile/identifier in step 2 form
                    const mobileHidden = document.getElementById('reset_modal_mobile_number');
                    if (mobileHidden) mobileHidden.value = identifier;

                    const confirmBox = document.getElementById('forgot-step-confirm');
                    if (confirmBox) confirmBox.style.display = 'none';

                    const reqForm = document.getElementById('form-forgot-request-otp');
                    if (reqForm) reqForm.style.display = 'none';

                    const resetForm = document.getElementById('form-forgot-reset-password');
                    if (resetForm) {
                        resetForm.style.display = 'flex';
                        resetForm.style.flexDirection = 'column';
                    }

                    const modalOtp = document.getElementById('modal_otp');
                    if (modalOtp) {
                        modalOtp.value = '';
                    }
                    const modalPw = document.getElementById('modal_new_password');
                    if (modalPw) {
                        modalPw.value = '';
                    }
                    const modalPwConf = document.getElementById('modal_password_confirmation');
                    if (modalPwConf) {
                        modalPwConf.value = '';
                    }

                    const displayMasked = data.masked_mobile || (currentUserData && currentUserData.masked_mobile) || maskMobile(identifier);
                    const subText = document.getElementById('forgot-subtitle-text');
                    if (subText) subText.textContent = 'Enter the 6-digit WhatsApp OTP code sent to +91 ' + displayMasked;

                    if (alertEl) {
                        document.getElementById('forgot-alert-msg').textContent = data.message + (data.dev_otp ? ' (Dev Code: ' + data.dev_otp + ')' : '');
                        alertEl.className = 'auth-alert success';
                        document.getElementById('forgot-alert-icon').className = 'bi bi-check-circle-fill me-2';
                        alertEl.style.display = 'flex';
                    }
                    if (modalOtp) modalOtp.focus();
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
                    document.getElementById('forgot-alert-msg').textContent = 'Network error sending OTP. Please check your connection.';
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
        otp: document.getElementById('reg-substep-otp')
    };

    let regCurrentSubstep = 'details';
    let regData = {
        name: '',
        mobile_number: '',
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
            const refInp = document.getElementById('referral_code');

            const name = nameInp ? nameInp.value.trim() : '';
            const mobile = mobileInp ? mobileInp.value.trim() : '';
            const ref = refInp ? refInp.value.trim() : '';

            // Reset field errors
            [nameInp, mobileInp].forEach(inp => inp && inp.classList.remove('is-invalid'));

            if (!name) {
                showRegAlert('details', 'Please enter your full name.');
                if (nameInp) { nameInp.classList.add('is-invalid'); nameInp.focus(); }
                return;
            }

            const cleanPhone = mobile.replace(/\D/g, '').slice(-10);
            if (!cleanPhone || cleanPhone.length !== 10 || !/^[6-9]\d{9}$/.test(cleanPhone)) {
                showRegAlert('details', 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
                if (mobileInp) { mobileInp.classList.add('is-invalid'); mobileInp.focus(); }
                return;
            }

            // Save details
            regData.name = name;
            regData.mobile_number = cleanPhone;
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

        let countdown = 90;
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

                // Direct 1-Step Passwordless Registration
                verifyOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Your Account...';
                
                fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: regData.name,
                        mobile_number: regData.mobile_number,
                        email: regData.email,
                        referral_code: regData.referral_code,
                        otp: otpVal
                    })
                })
                .then(r => r.json())
                .then(regResult => {
                    if (regResult.success || regResult.redirect) {
                        showToast('Account created successfully! Welcome to ShopCalm.', 'success');
                        window.location.href = regResult.redirect || '/';
                    } else {
                        showRegAlert('otp', regResult.message || 'Registration failed. Please try again.', 'error');
                        verifyOtpBtn.disabled = false;
                        verifyOtpBtn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    showRegAlert('otp', 'Network error creating account. Please try again.', 'error');
                    verifyOtpBtn.disabled = false;
                    verifyOtpBtn.innerHTML = originalText;
                });
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

    // Navigation & Back buttons
    function returnToInitialScreen() {
        const regMobile = document.getElementById('register_mobile');
        if (regMobile) {
            regMobile.readOnly = false;
            regMobile.classList.remove('readonly-input');
        }
        const changeMobileBtn = document.getElementById('change-identifier-from-register');
        if (changeMobileBtn) changeMobileBtn.style.display = 'none';

        hideRegAlert('details');
        hideRegAlert('otp');
        transitionTo('initial');

        if (initialInput) {
            setTimeout(() => {
                initialInput.focus();
                initialInput.select();
            }, 250);
        }
    }

    const changeLoginBtn = document.getElementById('change-identifier-from-login');
    if (changeLoginBtn) {
        changeLoginBtn.addEventListener('click', function() {
            transitionTo('initial');
            if (initialInput) {
                setTimeout(() => {
                    initialInput.focus();
                    initialInput.select();
                }, 250);
            }
        });
    }

    const changeRegBtn = document.getElementById('change-identifier-from-register');
    if (changeRegBtn) {
        changeRegBtn.addEventListener('click', returnToInitialScreen);
    }

    const regTopBackBtn = document.getElementById('register-top-back-btn');
    if (regTopBackBtn) {
        regTopBackBtn.addEventListener('click', returnToInitialScreen);
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
            const mobileInp = document.getElementById('register_mobile');
            if (mobileInp) mobileInp.focus();
        });
    }

    const showForgotBtn = document.getElementById('show-forgot-password');
    if (showForgotBtn) {
        showForgotBtn.addEventListener('click', function(e) {
            e.preventDefault();

            let maskedDisplay = '';
            if (currentUserData && currentUserData.masked_mobile) {
                maskedDisplay = currentUserData.masked_mobile;
            } else if (currentUserData && currentUserData.mobile_number) {
                maskedDisplay = maskMobile(currentUserData.mobile_number);
            } else if (currentIdentifier) {
                maskedDisplay = maskMobile(currentIdentifier);
            }

            const forgotInput = document.getElementById('mobile_number_for_forgot');
            if (forgotInput) {
                forgotInput.value = currentIdentifier || '';
            }

            const maskedEl = document.getElementById('forgot-masked-mobile-display');
            if (maskedEl) {
                maskedEl.textContent = '+91 ' + (maskMobile(maskedDisplay) || '••******••');
            }

            const nameRow = document.getElementById('forgot-user-name-row');
            const nameDisplay = document.getElementById('forgot-user-name-display');
            if (nameRow && nameDisplay) {
                if (currentUserData && currentUserData.name) {
                    nameDisplay.textContent = currentUserData.name;
                    nameRow.style.display = 'flex';
                } else {
                    nameRow.style.display = 'none';
                }
            }

            const confirmBox = document.getElementById('forgot-step-confirm');
            const resetForm = document.getElementById('form-forgot-reset-password');
            if (confirmBox) confirmBox.style.display = 'flex';
            if (resetForm) resetForm.style.display = 'none';

            const subText = document.getElementById('forgot-subtitle-text');
            if (subText) subText.textContent = 'Confirm your registered WhatsApp number to receive an OTP.';

            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';
            transitionTo('forgot');
        });
    }

    const backToLoginBtn = document.getElementById('back-to-login');
    if (backToLoginBtn) {
        backToLoginBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const confirmBox = document.getElementById('forgot-step-confirm');
            const reqForm = document.getElementById('form-forgot-request-otp');
            const resetForm = document.getElementById('form-forgot-reset-password');
            if (confirmBox) confirmBox.style.display = 'flex';
            if (reqForm) reqForm.style.display = 'flex';
            if (resetForm) resetForm.style.display = 'none';
            const alertEl = document.getElementById('forgot-alert');
            if (alertEl) alertEl.style.display = 'none';
            transitionTo(currentIdentifier ? 'login' : 'initial');
        });
    }

});
