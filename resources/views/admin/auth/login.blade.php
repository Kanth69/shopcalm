<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — Shopcalm</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Subtle background grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(59,130,246,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59,130,246,0.06) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* Glow blobs */
        .glow-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.18;
            pointer-events: none;
        }
        .glow-blob-1 {
            width: 500px; height: 500px;
            background: #3b82f6;
            top: -100px; left: -100px;
        }
        .glow-blob-2 {
            width: 400px; height: 400px;
            background: #8b5cf6;
            bottom: -100px; right: -80px;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 1.5rem;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            position: relative;
            z-index: 10;
        }

        .brand-icon {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            box-shadow: 0 8px 24px rgba(59,130,246,0.4);
        }

        .form-control-dark {
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(255,255,255,0.12);
            color: #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control-dark:focus {
            background: rgba(15,23,42,0.85);
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
            color: #e2e8f0;
            outline: none;
        }
        .form-control-dark::placeholder { color: #64748b; }

        .input-group-icon {
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(255,255,255,0.12);
            border-right: none;
            color: #64748b;
            border-radius: 0.75rem 0 0 0.75rem;
        }
        .input-group .form-control-dark:not(:first-child) {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }

        .btn-admin-login {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.8rem;
            font-size: 1rem;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.1s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(59,130,246,0.35);
        }
        .btn-admin-login:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(59,130,246,0.45);
            color: #fff;
        }
        .btn-admin-login:active { transform: translateY(0); }
        .btn-admin-login:disabled { opacity: 0.6; cursor: not-allowed; }

        label { color: #94a3b8; font-size: 0.8rem; font-weight: 500; letter-spacing: 0.4px; margin-bottom: 0.4rem; }

        .form-check-input { background-color: rgba(15,23,42,0.7); border-color: rgba(255,255,255,0.2); }
        .form-check-input:checked { background-color: #3b82f6; border-color: #3b82f6; }
        .form-check-label { color: #94a3b8; font-size: 0.82rem; }

        .alert-danger-dark {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            border-radius: 0.75rem;
            font-size: 0.85rem;
        }

        .toggle-pw { cursor: pointer; background: transparent; border: none; color: #64748b; padding: 0 0.75rem; }
        .toggle-pw:hover { color: #94a3b8; }

        .storefront-link {
            color: #475569;
            font-size: 0.78rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .storefront-link:hover { color: #94a3b8; }
    </style>
</head>
<body>

    <div class="glow-blob glow-blob-1"></div>
    <div class="glow-blob glow-blob-2"></div>

    <div class="login-card">
        <!-- Brand -->
        <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom border-secondary border-opacity-25">
            <x-logo variant="light" height="36" />
            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-50 px-2 py-1 small">Admin Panel</span>
        </div>

        <h4 class="fw-bold text-white mb-1">Sign in</h4>
        <p class="text-secondary mb-4" style="font-size: 0.85rem;">Enter your admin credentials to continue.</p>

        <!-- Session errors -->
        @if ($errors->any())
            <div class="alert alert-danger-dark mb-4 d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" id="admin-login-form">
            @csrf

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="text-uppercase">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text input-group-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control form-control-dark"
                        placeholder="admin@shopcalm.com"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="text-uppercase">Password</label>
                <div class="input-group">
                    <span class="input-group-text input-group-icon">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control form-control-dark"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="toggle-pw input-group-text input-group-icon border-left-0" id="togglePw" style="border-left: none; border-radius: 0 0.75rem 0.75rem 0;" title="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePwIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>
            </div>

            <button type="submit" class="btn btn-admin-login w-100" id="loginBtn">
                <span id="loginBtnText"><i class="bi bi-shield-check me-2"></i>Sign In to Admin Panel</span>
                <span id="loginBtnSpinner" class="d-none">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>Authenticating...
                </span>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="{{ route('home') }}" class="storefront-link">
                <i class="bi bi-arrow-left me-1"></i>Back to Storefront
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePw')?.addEventListener('click', function () {
            const pw = document.getElementById('password');
            const icon = document.getElementById('togglePwIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pw.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });

        // Show spinner on submit
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
