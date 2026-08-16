<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShopCalm — Administrator Portal</title>

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
            background-color: #0b0f19;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.15) 0px, transparent 50%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        /* Master Container */
        .admin-auth-card {
            width: 100%;
            max-width: 1040px;
            background: #ffffff;
            border-radius: 1.75rem;
            box-shadow: 0 25px 70px -10px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        /* Left Showcase Column (Dark Enterprise Panel) */
        .showcase-sidebar {
            background: linear-gradient(145deg, #0f172a 0%, #1e1b4b 100%);
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
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
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
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
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
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s, background 0.2s;
        }
        .feature-card-dark:hover {
            background: rgba(255, 255, 255, 0.07);
            transform: translateX(4px);
        }
        .feature-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        /* Right Form Column (Clean, High-Contrast Crisp Form) */
        .form-content-area {
            background: #ffffff;
            padding: 3.5rem 3.25rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }
        .form-desc {
            color: #64748b;
            font-size: 0.95rem;
            margin-top: 0.4rem;
        }

        /* Inputs & Labels */
        .input-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #334155;
            margin-bottom: 0.45rem;
        }

        .input-box-wrapper {
            position: relative;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }
        .input-box-wrapper:focus-within {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        .field-icon {
            color: #64748b;
            font-size: 1.15rem;
            padding-left: 1rem;
            display: flex;
            align-items: center;
        }
        .input-box-wrapper:focus-within .field-icon {
            color: #6366f1;
        }

        .text-input-field {
            width: 100%;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            color: #0f172a !important;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.85rem 1rem 0.85rem 0.75rem;
        }
        .text-input-field::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Fix Chrome/Edge autofill styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset !important;
            -webkit-text-fill-color: #0f172a !important;
            border-radius: 0.85rem !important;
            font-weight: 600 !important;
        }

        .pw-toggle-btn {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 1.15rem;
            transition: color 0.2s;
        }
        .pw-toggle-btn:hover {
            color: #1e293b;
        }

        /* Checkbox */
        .remember-checkbox .form-check-input {
            width: 1.15rem;
            height: 1.15rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 0.35rem;
            cursor: pointer;
        }
        .remember-checkbox .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }
        .remember-checkbox .form-check-label {
            color: #475569;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            margin-left: 0.25rem;
        }

        /* Submit Button */
        .submit-btn-admin {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #8b5cf6 100%);
            border: none;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            padding: 0.95rem 1.5rem;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            cursor: pointer;
            box-shadow: 0 6px 20px -2px rgba(79, 70, 229, 0.45);
            transition: all 0.2s ease;
        }
        .submit-btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -2px rgba(79, 70, 229, 0.6);
            color: #ffffff;
        }
        .submit-btn-admin:active {
            transform: translateY(0);
        }
        .submit-btn-admin:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        /* Error Alert */
        .error-alert-badge {
            background: #fef2f2;
            border: 1.5px solid #fecaca;
            color: #991b1b;
            border-radius: 0.85rem;
            padding: 0.85rem 1.1rem;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin-bottom: 1.5rem;
        }

        /* Storefront Return Link */
        .back-link-store {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .back-link-store:hover {
            color: #0f172a;
            background: #f1f5f9;
        }

        @media (max-width: 991.98px) {
            .showcase-sidebar {
                display: none;
            }
            .form-content-area {
                padding: 2.75rem 2rem;
            }
            .admin-auth-card {
                max-width: 480px;
            }
        }
    </style>
</head>
<body>

    <div class="admin-auth-card">
        <div class="row g-0">
            <!-- Left Branding Showcase Panel -->
            <div class="col-lg-5 showcase-sidebar">
                <div>
                    <!-- Logo -->
                    <div class="d-flex align-items-center mb-4">
                        <x-logo variant="light" height="38" />
                    </div>

                    <div class="mb-4">
                        <span class="status-pill">
                            <span class="status-pulse"></span> SECURE GATEWAY • TLS 1.3
                        </span>
                    </div>

                    <h3 class="fw-bold text-white mb-2" style="font-size: 1.45rem; line-height: 1.3;">
                        Store Operations & Intelligence Console
                    </h3>
                    <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                        Authorized administrator access for catalog management, stock logistics, and customer orders.
                    </p>

                    <!-- Feature Highlights -->
                    <div class="d-flex flex-column gap-3">
                        <div class="feature-card-dark">
                            <div class="feature-card-icon" style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc;">
                                <i class="bi bi-box-seam-fill"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold small">Catalog & Inventory</div>
                                <div class="text-white-50" style="font-size: 0.75rem;">Real-time stock movements & price control</div>
                            </div>
                        </div>

                        <div class="feature-card-dark">
                            <div class="feature-card-icon" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7;">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold small">Order Fulfillment</div>
                                <div class="text-white-50" style="font-size: 0.75rem;">Instant dispatch tracking & status workflow</div>
                            </div>
                        </div>

                        <div class="feature-card-dark">
                            <div class="feature-card-icon" style="background: rgba(14, 165, 233, 0.2); color: #7dd3fc;">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <div>
                                <div class="text-white fw-bold small">Administrative Security</div>
                                <div class="text-white-50" style="font-size: 0.75rem;">Granular permissions & audit logs</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Copyright -->
                <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-white-50" style="font-size: 0.72rem;">
                    &copy; {{ date('Y') }} ShopCalm Commerce. All admin sessions are protected and audited.
                </div>
            </div>

            <!-- Right Crisp Form Area -->
            <div class="col-lg-7 form-content-area">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-lg-none">
                        <x-logo variant="dark" height="32" />
                    </div>
                    <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-shield-check me-1"></i>ADMIN PORTAL
                    </span>
                </div>

                <div class="mb-4">
                    <h2 class="form-title">Admin Sign In</h2>
                    <p class="form-desc">Enter your administrative credentials to manage your store.</p>
                </div>

                <!-- Errors Display -->
                @if ($errors->any())
                    <div class="error-alert-badge">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0 mt-0.5 fs-5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}" id="admin-login-form">
                    @csrf

                    <!-- Email Input -->
                    <div class="mb-3.5">
                        <label for="email" class="input-label">Admin Email Address</label>
                        <div class="input-box-wrapper">
                            <span class="field-icon">
                                <i class="bi bi-envelope-at-fill"></i>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="text-input-field"
                                placeholder="admin@shopcalm.com"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3.5 mt-3">
                        <label for="password" class="input-label">Password</label>
                        <div class="input-box-wrapper">
                            <span class="field-icon">
                                <i class="bi bi-key-fill"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="text-input-field"
                                placeholder="••••••••••••"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="pw-toggle-btn" id="togglePw" title="Toggle password visibility" tabindex="-1">
                                <i class="bi bi-eye-fill" id="togglePwIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="d-flex align-items-center justify-content-between mb-4 mt-3 remember-checkbox">
                        <div class="form-check d-flex align-items-center">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">Keep me signed in</label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="submit-btn-admin" id="loginBtn">
                        <span id="loginBtnText">
                            <i class="bi bi-shield-lock-fill me-1"></i> Sign In to Dashboard
                        </span>
                        <span id="loginBtnSpinner" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>Verifying Credentials...
                        </span>
                    </button>
                </form>

                <!-- Return to Storefront -->
                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="back-link-store">
                        <i class="bi bi-arrow-left"></i> Return to Storefront
                    </a>
                </div>

                <!-- Security Assurance -->
                <div class="text-center mt-3" style="font-size: 0.75rem; color: #94a3b8;">
                    <i class="bi bi-lock-fill text-muted me-1"></i> 256-Bit Encrypted Administrator Session
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password Visibility Toggle
        document.getElementById('togglePw')?.addEventListener('click', function () {
            const pwInput = document.getElementById('password');
            const icon = document.getElementById('togglePwIcon');
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                icon.className = 'bi bi-eye-slash-fill';
            } else {
                pwInput.type = 'password';
                icon.className = 'bi bi-eye-fill';
            }
        });

        // Submit Button Loading State
        document.getElementById('admin-login-form')?.addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            const text = document.getElementById('loginBtnText');
            const spinner = document.getElementById('loginBtnSpinner');
            btn.disabled = true;
            text.classList.add('d-none');
            spinner.classList.remove('d-none');
        });
    </script>
</body>
</html>
