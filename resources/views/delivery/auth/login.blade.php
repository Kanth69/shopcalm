<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <title>Delivery Partner Login — WiseKart Fleet</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --primary-gradient-hover: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at top center, #1e1b4b 0%, #0f172a 55%, #020617 100%);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            padding-top: max(1rem, env(safe-area-inset-top));
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
            margin: 0;
            color: #0f172a;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            margin: auto;
        }

        .auth-card {
            width: 100%;
            background: #ffffff;
            border-radius: 1.5rem;
            padding: clamp(1.25rem, 5vw, 2.25rem);
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            overflow: hidden;
        }

        /* Top colored accent line */
        .auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .brand-icon-box {
            width: 58px;
            height: 58px;
            background: var(--primary-gradient);
            border-radius: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.75rem;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
            transition: transform 0.2s ease;
        }

        /* Prevent auto-zoom on mobile safari by keeping input >= 16px */
        .form-control, .input-group-text {
            font-size: 16px !important;
            height: 48px;
        }

        .form-control {
            background-color: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #1e293b !important;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background-color: #ffffff !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15) !important;
        }

        .input-group-text {
            background-color: #f8fafc !important;
            border: 1.5px solid #cbd5e1 !important;
            color: #64748b;
        }

        .btn-rider-primary {
            background: var(--primary-gradient);
            color: #ffffff;
            font-weight: 700;
            font-size: 0.96rem;
            border-radius: 0.85rem;
            height: 48px;
            border: none;
            box-shadow: 0 6px 18px rgba(79, 70, 229, 0.35);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-rider-primary:hover {
            background: var(--primary-gradient-hover);
            color: #ffffff;
            transform: translateY(-1.5px);
            box-shadow: 0 8px 22px rgba(79, 70, 229, 0.45);
        }

        .btn-rider-primary:active {
            transform: scale(0.98);
        }

        .demo-chip {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.85rem;
            padding: 0.65rem 0.85rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .demo-chip:hover {
            border-color: #6366f1;
            background: #f5f3ff;
            transform: translateY(-1px);
        }

        .demo-chip:active {
            transform: scale(0.98);
            background: #ede9fe;
        }

        /* Mobile Adjustments (<= 480px) */
        @media (max-width: 480px) {
            body {
                padding: 0.75rem 0.6rem;
            }
            .auth-card {
                padding: 1.35rem 1.15rem;
                border-radius: 1.25rem;
            }
            .brand-icon-box {
                width: 52px;
                height: 52px;
                font-size: 1.55rem;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <!-- Header -->
        <div class="text-center mb-3 mb-sm-3.5">
            <div class="brand-icon-box mb-2">
                <i class="bi bi-bicycle"></i>
            </div>
            <h4 class="fw-bolder text-dark mb-0.5" style="letter-spacing: -0.02em; font-size: 1.35rem;">WiseKart Fleet</h4>
            <div class="text-secondary fw-semibold" style="font-size: 0.82rem;">Bengaluru Local Delivery Partner Portal</div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-3 p-2.5 mb-3 small d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
                <div class="lh-sm">{{ $errors->first() }}</div>
            </div>
        @endif

        <form id="riderLoginForm" action="{{ route('delivery.login.submit') }}" method="POST">
            @csrf
            
            <!-- Mobile / Email Field -->
            <div class="mb-2.5">
                <label for="emailInput" class="form-label fw-bold small text-dark mb-1 d-flex align-items-center justify-content-between">
                    <span>Mobile Number or Email</span>
                    <span class="text-muted fw-normal" style="font-size: 0.72rem;">Rider ID</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-phone"></i>
                    </span>
                    <input type="text" 
                           id="emailInput" 
                           name="email" 
                           class="form-control border-start-0 ps-1" 
                           required 
                           autofocus 
                           value="{{ old('email') }}" 
                           placeholder="e.g. 9876543210 or email"
                           autocomplete="username"
                           inputmode="email">
                </div>
            </div>

            <!-- PIN / Password Field -->
            <div class="mb-2.5">
                <label for="passwordInput" class="form-label fw-bold small text-dark mb-1 d-flex align-items-center justify-content-between">
                    <span>PIN / Password</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text border-end-0">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" 
                           id="passwordInput" 
                           name="password" 
                           class="form-control border-start-0 border-end-0 ps-1" 
                           required 
                           placeholder="Enter your rider password"
                           autocomplete="current-password">
                    <button type="button" 
                            class="input-group-text border-start-0 text-secondary bg-transparent px-3" 
                            onclick="togglePasswordVisibility()" 
                            title="Show/Hide Password"
                            style="cursor: pointer;">
                        <i id="passwordToggleIcon" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-3 pt-0.5">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked style="cursor: pointer;">
                    <label class="form-check-label text-secondary small user-select-none" for="remember" style="font-size: 0.8rem; cursor: pointer;">
                        Stay signed in on this phone
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn" class="btn btn-rider-primary w-100 mb-3">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Sign In to Deliveries</span>
            </button>

            <!-- 1-Click Demo Credentials Autofill -->
            <div class="pt-2 border-top">
                <div class="text-secondary fw-bold text-uppercase mb-2 text-center" style="font-size: 0.68rem; letter-spacing: 0.06em;">
                    ⚡ 1-Click Demo Riders
                </div>

                <!-- Demo Rider #01 -->
                <div class="demo-chip mb-2" onclick="fillDemoRider('karthik.rider@shopcalm.com', 'password', this)">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" 
                             style="width: 32px; height: 32px; background: #4f46e5; font-size: 0.78rem;">
                            K
                        </div>
                        <div class="min-w-0 text-truncate">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;">Karthik (Delivery Executive)</div>
                            <div class="text-secondary font-monospace text-truncate" style="font-size: 0.68rem;">karthik.rider@shopcalm.com</div>
                        </div>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs flex-shrink-0" style="background: #4f46e5; font-size: 0.68rem;">
                        Autofill
                    </span>
                </div>

                <!-- Demo Rider #02 -->
                <div class="demo-chip" onclick="fillDemoRider('suresh.delivery@shopcalm.com', 'password', this)">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0" 
                             style="width: 32px; height: 32px; background: #10b981; font-size: 0.78rem;">
                            S
                        </div>
                        <div class="min-w-0 text-truncate">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.8rem;">Suresh (Delivery Executive)</div>
                            <div class="text-secondary font-monospace text-truncate" style="font-size: 0.68rem;">suresh.delivery@shopcalm.com</div>
                        </div>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs flex-shrink-0" style="background: #10b981; font-size: 0.68rem;">
                        Autofill
                    </span>
                </div>
            </div>

            <!-- Back to Storefront Link -->
            <div class="text-center pt-2.5">
                <a href="{{ route('shop') }}" class="text-secondary small text-decoration-none fw-medium" style="font-size: 0.8rem;">
                    <i class="bi bi-arrow-left me-1"></i> Back to Storefront
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('passwordInput');
    const toggleIcon = document.getElementById('passwordToggleIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}

function fillDemoRider(email, password, element) {
    document.getElementById('emailInput').value = email;
    document.getElementById('passwordInput').value = password;

    const badge = element.querySelector('.badge');
    const originalText = badge ? badge.textContent : 'Autofill';

    element.style.borderColor = '#4f46e5';
    element.style.background = '#eef2ff';
    if (badge) {
        badge.textContent = 'Filled ✓';
    }

    setTimeout(() => {
        element.style.borderColor = '#e2e8f0';
        element.style.background = '#f8fafc';
        if (badge) {
            badge.textContent = originalText;
        }
    }, 900);
}
</script>

</body>
</html>
