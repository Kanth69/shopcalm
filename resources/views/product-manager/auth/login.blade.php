<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Manager Portal - ShopCalm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 440px;
        }
        .portal-header {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            color: #ffffff;
            padding: 32px 28px;
            text-align: center;
            position: relative;
        }
        .portal-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 14px;
        }
        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.12);
        }
        .btn-submit {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            border: none;
            border-radius: 12px;
            padding: 13px 20px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.3px;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.4);
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="login-card">
    <!-- Header Banner -->
    <div class="portal-header">
        <div class="portal-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <h4 class="fw-bold mb-1">Product Manager Portal</h4>
        <p class="mb-0 text-white-50 small">Catalog, Inventory & Stock Operations</p>
    </div>

    <!-- Login Form -->
    <div class="p-4 p-sm-5">
        @if ($errors->any())
            <div class="alert alert-danger border-0 rounded-3 py-2.5 px-3 mb-4 small d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('product-manager.login') }}">
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <label class="form-label fw-bold small text-dark">Staff Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="pm@shopcalm.com" value="{{ old('email') }}" required autofocus style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label fw-bold small text-dark">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required style="border-radius: 0 12px 12px 0;">
                </div>
            </div>

            <!-- Remember Me -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small text-muted" for="remember">Keep me logged in</label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-submit w-100 d-flex align-items-center justify-content-center">
                <span>Sign In to Catalog</span>
                <i class="bi bi-arrow-right ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-4 pt-2 border-top">
            <span class="text-muted small" style="font-size: 0.75rem;">
                <i class="bi bi-shield-check text-success me-1"></i> Departmental Role-Based Access Enabled
            </span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
