<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShopCalm — Customer Support & Helpdesk</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #0c0a1a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(139, 92, 246, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.2) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.6) 0px, transparent 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* Master Container */
        .support-auth-card {
            width: 100%;
            max-width: 1040px;
            background: #ffffff;
            border-radius: 1.75rem;
            box-shadow: 0 25px 70px -10px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        /* Left Showcase Column */
        .showcase-sidebar {
            background: linear-gradient(145deg, #110d28 0%, #1e1548 50%, #0d0a21 100%);
            color: #ffffff;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .showcase-sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 1px;
            background: rgba(255, 255, 255, 0.08);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(168, 85, 247, 0.15);
            border: 1px solid rgba(168, 85, 247, 0.35);
            color: #c084fc;
            font-size: 0.76rem;
            font-weight: 700;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.5px;
        }
        .status-pulse {
            width: 7px;
            height: 7px;
            background: #c084fc;
            border-radius: 50%;
            box-shadow: 0 0 8px #c084fc;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.5; }
        }

        .feature-card-dark {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 1.25rem;
            transition: transform 0.2s ease, background 0.2s ease;
        }
        .feature-card-dark:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateX(4px);
        }

        /* Right Form Area */
        .auth-form-area {
            padding: 3.5rem 3rem;
            background: #ffffff;
        }

        .form-control-support {
            width: 100%;
            height: 48px;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.85rem;
            font-size: 0.92rem;
            font-weight: 500;
            color: #0f172a;
            background: #ffffff;
            transition: all 0.2s ease;
        }
        .form-control-support:focus {
            border-color: #8b5cf6;
            outline: none;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.12);
            background: #ffffff;
        }

        .btn-support-primary {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            border: none;
            border-radius: 0.85rem;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            box-shadow: 0 10px 20px -5px rgba(139, 92, 246, 0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-support-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px -4px rgba(139, 92, 246, 0.5);
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        }

        .demo-chip {
            background: #faf5ff;
            border: 1px dashed #d8b4fe;
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .demo-chip:hover {
            background: #f3e8ff;
            border-color: #8b5cf6;
        }

        @media (max-width: 991.98px) {
            .showcase-sidebar {
                padding: 2.5rem 2rem;
            }
            .auth-form-area {
                padding: 2.5rem 2rem;
            }
        }
    </style>
</head>
<body>

<div class="support-auth-card">
    <div class="row g-0">
        <!-- Left Showcase Column -->
        <div class="col-lg-5 showcase-sidebar">
            <div>
                <!-- Brand & Status -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 38px; height: 38px; background: linear-gradient(135deg, #8b5cf6, #c084fc); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);">
                            <i class="bi bi-headset"></i>
                        </div>
                        <span class="fw-bold fs-5 tracking-tight text-white">ShopCalm</span>
                    </div>
                    <span class="status-pill">
                        <span class="status-pulse"></span> HELPDESK
                    </span>
                </div>

                <h2 class="fw-bold text-white mb-2" style="font-size: 1.75rem; letter-spacing: -0.5px;">
                    Customer Support & Assistance
                </h2>
                <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                    Dedicated portal for customer inquiries, order assistance, profile 360° lookup, and ticket resolution.
                </p>

                <!-- Feature Highlights -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="feature-card-dark d-flex align-items-center gap-3">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(139, 92, 246, 0.2); color: #c084fc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-envelope-open fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white small">Customer Inquiries Hub</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Respond to contact forms and resolve tickets.</div>
                        </div>
                    </div>

                    <div class="feature-card-dark d-flex align-items-center gap-3">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-person-badge fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white small">Customer 360° Lookup</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Instant order history, lifetime spend & address lookup.</div>
                        </div>
                    </div>

                    <div class="feature-card-dark d-flex align-items-center gap-3">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-white small">Order Status & Tracking</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Assist customers with live shipment tracking.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="pt-3 border-top border-white border-opacity-10 d-flex align-items-center justify-content-between text-white-50" style="font-size: 0.72rem;">
                <span>Role Protected &bull; RBAC Level 6</span>
                <span>v2.5 Enterprise</span>
            </div>
        </div>

        <!-- Right Form Area -->
        <div class="col-lg-7 auth-form-area d-flex flex-column justify-content-between">
            <div>
                <!-- Top Badge & Title -->
                <div class="mb-4">
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold mb-2" style="font-size: 0.72rem; color: #7c3aed; background: #f3e8ff; border: 1px solid rgba(139, 92, 246, 0.25);">
                        <i class="bi bi-shield-lock me-1"></i> Customer Support Portal
                    </span>
                    <h3 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.5px;">Agent Sign In</h3>
                    <p class="text-muted small">Enter your staff credentials to access the support desk.</p>
                </div>

                <!-- Session / Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 d-flex align-items-start gap-2 small">
                        <i class="bi bi-exclamation-octagon-fill fs-5 flex-shrink-0 mt-0.5"></i>
                        <div>
                            <div class="fw-bold">Authentication Failed</div>
                            <ul class="mb-0 ps-3 mt-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form action="{{ route('support.login.submit') }}" method="POST">
                    @csrf

                    <!-- Email Input -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1.5">Official Support Email</label>
                        <div class="position-relative">
                            <input type="email" id="loginEmail" name="email" class="form-control-support" 
                                   placeholder="e.g. support@shopcalm.com" 
                                   value="{{ old('email', 'support@shopcalm.com') }}" required autofocus>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label class="form-label fw-bold text-dark small mb-0">Password</label>
                            <span class="text-muted" style="font-size: 0.75rem;">Default: password123</span>
                        </div>
                        <div class="position-relative">
                            <input type="password" id="loginPassword" name="password" class="form-control-support" 
                                   placeholder="Enter password" value="password123" required>
                            <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted text-decoration-none pe-3" onclick="togglePasswordVisibility()">
                                <i id="passwordEyeIcon" class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                            <label class="form-check-label text-secondary small" for="rememberMe">
                                Keep me signed in
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-support-primary mb-3">
                        <span>Sign In to Helpdesk</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <!-- 1-Click Demo Fill -->
                <div class="demo-chip mt-2" onclick="fillSupportDemo()">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 0.8rem; background: #ede9fe; color: #7c3aed;">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.78rem;">1-Click Demo Credentials</div>
                                <div class="text-muted font-monospace" style="font-size: 0.7rem;">support@shopcalm.com &bull; password123</div>
                            </div>
                        </div>
                        <span class="badge bg-white text-dark border shadow-xs" style="font-size: 0.68rem;">Auto Fill</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Return Link -->
            <div class="mt-4 pt-3 border-top text-center">
                <a href="{{ route('shop') }}" class="text-secondary small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Back to Storefront
                </a>
                <span class="text-muted mx-2">&bull;</span>
                <a href="{{ route('admin.login') }}" class="text-secondary small text-decoration-none">
                    Admin Portal
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('loginPassword');
        const icon = document.getElementById('passwordEyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    function fillSupportDemo() {
        document.getElementById('loginEmail').value = 'support@shopcalm.com';
        document.getElementById('loginPassword').value = 'password123';
    }
</script>

</body>
</html>
