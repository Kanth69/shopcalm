<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ShopCalm — Growth & Marketing Portal</title>

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
                radial-gradient(at 0% 0%, rgba(236, 72, 153, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(99, 102, 241, 0.22) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.8) 0px, transparent 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 980px;
            background: #ffffff;
            border-radius: 1.75rem;
            box-shadow: 0 25px 70px -10px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .showcase-sidebar {
            background: linear-gradient(145deg, #180d28 0%, #2e1065 50%, #0f172a 100%);
            color: #ffffff;
            padding: 3.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .form-pane {
            padding: 3.5rem 3rem;
        }

        .form-control:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);
        }

        .btn-marketing {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #ec4899 100%);
            border: none;
            color: white;
            font-weight: 700;
            padding: 0.85rem;
            border-radius: 50rem;
            transition: all 0.2s ease;
        }

        .btn-marketing:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.4);
            color: white;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="row g-0">
        <!-- Showcase Sidebar (Left) -->
        <div class="col-lg-5 showcase-sidebar d-none d-lg-flex">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white bg-opacity-10 border border-white border-opacity-15 mb-4">
                    <span class="badge bg-pink rounded-circle p-1" style="background-color: #ec4899;"></span>
                    <span class="small fw-semibold text-white tracking-wide">Growth & Engagement</span>
                </div>
                <h3 class="fw-bolder mb-3 text-white">Marketing Team Portal</h3>
                <p class="text-white-50 small mb-4" style="line-height: 1.6;">
                    Broadcast high-converting push alerts to customers filtered by gender, birthday month, and seasonal categories.
                </p>

                <div class="d-flex flex-column gap-3 mt-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-10 text-white">
                            <i class="bi bi-bell-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-white">Mobile Push Broadcasts</div>
                            <small class="text-white-50">Reach customer lock screens directly</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle p-2 bg-white bg-opacity-10 text-white">
                            <i class="bi bi-funnel-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-white">Audience Filtration</div>
                            <small class="text-white-50">Filter by Male, Female, DOB & Age</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-top border-white border-opacity-10 text-white-50 small">
                Authorized Marketing Staff &bull; ShopCalm Platform
            </div>
        </div>

        <!-- Form Pane (Right) -->
        <div class="col-lg-7 form-pane d-flex flex-column justify-content-center">
            <div class="mb-4">
                <span class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill small mb-2">Staff Sign-In</span>
                <h4 class="fw-bold text-dark mb-1">Welcome to Marketing Studio</h4>
                <p class="text-muted small">Enter your staff credentials to access your marketing workspace.</p>
            </div>

            @if(session('status'))
                <div class="alert alert-success border-0 rounded-4 small mb-3">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 rounded-4 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('marketing.login.submit') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark">Staff Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="staff@shopcalm.in" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-dark">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••••••" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small text-muted" for="remember">
                            Remember session
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-marketing w-100 shadow-sm mb-3">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Marketing Workspace
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">Need marketing staff access? Contact your platform <span class="fw-bold text-dark">Super Administrator</span>.</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
