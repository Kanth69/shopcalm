<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShopCalm — Product Manager Portal</title>

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
            background-color: #090d16;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.22) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.5) 0px, transparent 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* Master Container */
        .pm-auth-card {
            width: 100%;
            max-width: 1040px;
            background: #ffffff;
            border-radius: 1.75rem;
            box-shadow: 0 25px 70px -10px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        /* Left Showcase Column (Dark Enterprise Panel) */
        .showcase-sidebar {
            background: linear-gradient(145deg, #0c101d 0%, #171336 50%, #0f172a 100%);
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
            background: rgba(129, 140, 248, 0.12);
            border: 1px solid rgba(129, 140, 248, 0.3);
            color: #a5b4fc;
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
            background: #818cf8;
            border-radius: 50%;
            box-shadow: 0 0 8px #818cf8;
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
            padding: 1.1rem 1.25rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .feature-card-dark:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(129, 140, 248, 0.4);
            transform: translateX(4px);
        }

        .feature-icon-wrapper {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        /* Right Form Column */
        .form-side {
            padding: 3.5rem 3.5rem;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-control-custom {
            height: 52px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 0.75rem 1rem 0.75rem 2.85rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: #0f172a;
            transition: all 0.2s ease;
        }
        .form-control-custom:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
            outline: none;
        }

        .input-group-custom {
            position: relative;
        }
        .input-icon-lead {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
            z-index: 10;
            pointer-events: none;
            transition: color 0.2s;
        }
        .form-control-custom:focus ~ .input-icon-lead,
        .input-group-custom:focus-within .input-icon-lead {
            color: #6366f1;
        }

        .password-toggle-btn {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.4rem;
            font-size: 1.1rem;
            z-index: 10;
            transition: color 0.2s;
        }
        .password-toggle-btn:hover {
            color: #475569;
        }

        .btn-primary-action {
            height: 52px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.2px;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.45);
            transition: all 0.25s cubic-bezier(.4,0,.2,1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }
        .btn-primary-action:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
            box-shadow: 0 14px 30px -5px rgba(99, 102, 241, 0.55);
            transform: translateY(-2px);
            color: #ffffff;
        }

        .demo-chip {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .demo-chip:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        @media (max-width: 991.98px) {
            .showcase-sidebar {
                padding: 2.5rem 2rem;
            }
            .form-side {
                padding: 2.5rem 2rem;
            }
        }
    </style>
</head>
<body>

<div class="pm-auth-card">
    <div class="row g-0">
        <!-- Left Showcase Side -->
        <div class="col-lg-5 d-none d-lg-flex showcase-sidebar">
            <!-- Brand & Status -->
            <div>
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #6366f1, #a855f7); display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-box-seam text-white fs-5"></i>
                        </div>
                        <span class="fw-bold fs-5 tracking-tight text-white">ShopCalm</span>
                    </div>
                    <div class="status-pill">
                        <span class="status-pulse"></span>
                        PRODUCT OPS
                    </div>
                </div>

                <h2 class="fw-bold text-white mb-2" style="font-size: 1.65rem; letter-spacing: -0.5px;">Catalog & Stock Command</h2>
                <p class="text-white-50 small mb-4">Centralized workspace for inventory lifecycle, media specifications, and instant approvals.</p>

                <!-- Feature Highlights -->
                <div class="d-flex flex-column gap-3">
                    <div class="feature-card-dark">
                        <div class="d-flex align-items-start gap-3">
                            <div class="feature-icon-wrapper" style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc;">
                                <i class="bi bi-grid-fill"></i>
                            </div>
                            <div>
                                <h6 class="text-white fw-bold mb-0.5" style="font-size: 0.88rem;">Catalog Architecture</h6>
                                <p class="text-white-50 small mb-0" style="font-size: 0.76rem;">Create, enrich, and organize categories, brands, and multi-angle galleries.</p>
                            </div>
                        </div>
                    </div>

                    <div class="feature-card-dark">
                        <div class="d-flex align-items-start gap-3">
                            <div class="feature-icon-wrapper" style="background: rgba(16, 185, 129, 0.15); color: #6ee7b7;">
                                <i class="bi bi-boxes"></i>
                            </div>
                            <div>
                                <h6 class="text-white fw-bold mb-0.5" style="font-size: 0.88rem;">Real-time Inventory Ledger</h6>
                                <p class="text-white-50 small mb-0" style="font-size: 0.76rem;">Inbound restock purchasing, damaged write-offs, and physical audits.</p>
                            </div>
                        </div>
                    </div>

                    <div class="feature-card-dark">
                        <div class="d-flex align-items-start gap-3">
                            <div class="feature-icon-wrapper" style="background: rgba(245, 158, 11, 0.15); color: #fcd34d;">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <div>
                                <h6 class="text-white fw-bold mb-0.5" style="font-size: 0.88rem;">Approval Synchronization</h6>
                                <p class="text-white-50 small mb-0" style="font-size: 0.76rem;">Instant Admin review queue with detailed feedback and rejection history.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="pt-4 border-top border-white border-opacity-10 d-flex align-items-center justify-content-between text-white-50" style="font-size: 0.75rem;">
                <span><i class="bi bi-shield-lock-fill me-1 text-primary"></i> RBAC Enforced</span>
                <span>v2.4 Production</span>
            </div>
        </div>

        <!-- Right Form Side -->
        <div class="col-lg-7 form-side">
            <div class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                        <i class="bi bi-person-badge-fill me-1"></i> Department Login
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-1" style="font-size: 1.65rem; letter-spacing: -0.5px;">Product Manager Sign In</h3>
                <p class="text-muted small">Enter your staff credentials to access your catalog dashboard.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 small d-flex align-items-start gap-2" style="background: #fef2f2; border-left: 4px solid #ef4444 !important;">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-5 mt-0.5"></i>
                    <div>
                        <div class="fw-bold text-danger">Authentication Failed</div>
                        <div class="text-dark">{{ $errors->first() }}</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('product-manager.login') }}" id="loginForm">
                @csrf

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-bold text-dark small">Work Email Address</label>
                    <div class="input-group-custom">
                        <i class="bi bi-envelope input-icon-lead"></i>
                        <input type="email" name="email" id="email" class="form-control form-control-custom @error('email') is-invalid @enderror" 
                            placeholder="name@shopcalm.com" value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="password" class="form-label fw-bold text-dark small mb-0">Password</label>
                    </div>
                    <div class="input-group-custom">
                        <i class="bi bi-lock input-icon-lead"></i>
                        <input type="password" name="password" id="password" class="form-control form-control-custom @error('password') is-invalid @enderror" 
                            placeholder="••••••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" title="Toggle password visibility">
                            <i class="bi bi-eye" id="passwordToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Security -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" style="cursor: pointer;">
                        <label class="form-check-label text-secondary small user-select-none" for="remember" style="cursor: pointer;">
                            Keep me signed in
                        </label>
                    </div>
                    <span class="text-muted small" style="font-size: 0.78rem;">
                        <i class="bi bi-shield-check text-success me-1"></i>Secure Portal
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary-action mb-4" id="submitBtn">
                    <span>Access Dashboard</span>
                    <i class="bi bi-arrow-right fs-5"></i>
                </button>

                <!-- Demo Autofill Chip -->
                <div class="demo-chip mb-3" onclick="fillDemoCredentials()">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-lightning-charge-fill text-warning fs-5"></i>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.8rem;">Click to Autofill PM Demo Account</div>
                            <div class="text-muted font-monospace" style="font-size: 0.72rem;">pm@shopcalm.com &bull; password123</div>
                        </div>
                    </div>
                    <span class="badge bg-light text-primary border rounded-pill px-2.5 py-1 fw-bold">Fill</span>
                </div>

                <!-- Back to Shop -->
                <div class="text-center pt-2">
                    <a href="{{ route('home') }}" class="text-muted small text-decoration-none hover-primary">
                        <i class="bi bi-arrow-left me-1"></i> Return to Storefront
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
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

function fillDemoCredentials() {
    document.getElementById('email').value = 'pm@shopcalm.com';
    document.getElementById('password').value = 'password123';
    
    const chip = document.querySelector('.demo-chip');
    chip.style.borderColor = '#6366f1';
    chip.style.background = '#eef2ff';
    setTimeout(() => {
        chip.style.borderColor = '#e2e8f0';
        chip.style.background = '#f8fafc';
    }, 1000);
}
</script>

</body>
</html>
