<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delivery Partner Login — ShopCalm Fleet</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #091322 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            color: #0f172a;
        }

        .auth-card {
            width: 100%;
            max-width: 440px;
            background: #ffffff;
            border-radius: 1.5rem;
            padding: 2.25rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-rider-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff;
            font-weight: 800;
            border-radius: 9999px;
            padding: 0.85rem;
            border: none;
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);
            transition: all 0.2s ease;
        }
        .btn-rider-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .demo-chip {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 1rem;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .demo-chip:hover {
            border-color: #4f46e5;
            background: #eef2ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.12);
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mb-2 shadow-sm" 
             style="width: 60px; height: 60px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); font-size: 1.8rem;">
            <i class="bi bi-bicycle"></i>
        </div>
        <h4 class="fw-bolder text-dark mb-1" style="letter-spacing: -0.5px;">ShopCalm Fleet</h4>
        <div class="text-secondary small fw-semibold">Bengaluru Local Delivery Partner Portal</div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 small">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
        </div>
    @endif

    <form id="riderLoginForm" action="{{ route('delivery.login.submit') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Mobile Number or Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-phone"></i></span>
                <input type="text" id="emailInput" name="email" class="form-control bg-light border-start-0" required autofocus value="{{ old('email') }}" placeholder="e.g. +91 9876543210 or email">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold small text-dark">PIN / Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-secondary"><i class="bi bi-lock"></i></span>
                <input type="password" id="passwordInput" name="password" class="form-control bg-light border-start-0 border-end-0" required placeholder="Enter your rider password">
                <button type="button" class="input-group-text bg-light border-start-0 text-secondary" onclick="togglePasswordVisibility()" title="Show/Hide Password">
                    <i id="passwordToggleIcon" class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                <label class="form-check-label small text-secondary" for="remember">
                    Stay logged in on this phone
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-rider-primary w-100 mb-3.5">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Deliveries
        </button>

        <!-- 1-Click Demo Credentials Autofill Section -->
        <div class="mb-3">
            <div class="text-secondary small fw-bold text-uppercase mb-2 text-center" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                ⚡ 1-Click Demo Credentials
            </div>

            <!-- Demo Rider #01 -->
            <div class="demo-chip mb-2" onclick="fillDemoRider('karthik.rider@shopcalm.com', 'password', this)">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 30px; height: 30px; background: #4f46e5; font-size: 0.75rem;">
                        K
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.8rem;">Karthik (Fleet Rider #01)</div>
                        <div class="text-secondary font-monospace" style="font-size: 0.7rem;">karthik.rider@shopcalm.com</div>
                    </div>
                </div>
                <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" style="background: #4f46e5; font-size: 0.7rem;">
                    Autofill
                </span>
            </div>

            <!-- Demo Rider #02 -->
            <div class="demo-chip" onclick="fillDemoRider('suresh.delivery@shopcalm.com', 'password', this)">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width: 30px; height: 30px; background: #10b981; font-size: 0.75rem;">
                        S
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size: 0.8rem;">Suresh (Fleet Rider #02)</div>
                        <div class="text-secondary font-monospace" style="font-size: 0.7rem;">suresh.delivery@shopcalm.com</div>
                    </div>
                </div>
                <span class="badge rounded-pill px-2.5 py-1 fw-bold text-white shadow-xs" style="background: #10b981; font-size: 0.7rem;">
                    Autofill
                </span>
            </div>
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('shop') }}" class="text-secondary small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to Storefront
            </a>
        </div>
    </form>
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

    element.style.borderColor = '#4f46e5';
    element.style.background = '#eef2ff';

    setTimeout(() => {
        element.style.borderColor = '#e2e8f0';
        element.style.background = '#f8fafc';
    }, 800);
}
</script>

</body>
</html>
