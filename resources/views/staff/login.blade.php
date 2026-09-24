<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Operations Hub — ShopCalm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #091322 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            margin: 0;
            color: #f8fafc;
        }

        .staff-card {
            width: 100%;
            max-width: 440px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 1.5rem;
            padding: 2.25rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #0284c7 0%, #38bdf8 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.4);
            margin: 0 auto 1.25rem;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #38bdf8;
            color: #ffffff;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.85rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(2, 132, 199, 0.5);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .portal-chip {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
        }
    </style>
</head>
<body>

<div class="staff-card text-center">
    <div class="brand-logo">
        <i class="bi bi-shield-lock-fill"></i>
    </div>

    <h4 class="fw-bolder text-white mb-1" style="letter-spacing: -0.5px;">ShopCalm Staff Hub</h4>
    <p class="text-secondary small mb-4" style="font-size: 0.82rem;">
        Internal Portal for Operations, Logistics & Administration
    </p>

    @if($errors->any())
        <div class="alert alert-danger border-0 rounded-3 p-3 mb-4 text-start small d-flex align-items-center gap-2" style="background: rgba(225, 29, 72, 0.2); border: 1px solid rgba(225, 29, 72, 0.4) !important; color: #fecdd3;">
            <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
            <div>{{ $errors->first() }}</div>
        </div>
    @endif

    <form action="{{ url('/login') }}" method="POST" class="text-start">
        @csrf

        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Staff Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: rgba(255, 255, 255, 0.15);">
                    <i class="bi bi-envelope-fill"></i>
                </span>
                <input type="email" name="email" class="form-control border-start-0 ps-0" placeholder="e.g. admin@shopcalm.in" value="{{ old('email') }}" required autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-secondary small fw-bold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-secondary" style="border-color: rgba(255, 255, 255, 0.15);">
                    <i class="bi bi-key-fill"></i>
                </span>
                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-submit mb-4">
            <span>Sign In to Staff Hub</span> <i class="bi bi-arrow-right ms-1"></i>
        </button>
    </form>

    <div class="border-top pt-3" style="border-color: rgba(255, 255, 255, 0.08) !important;">
        <div class="text-secondary small fw-bold text-uppercase mb-2" style="font-size: 0.62rem; letter-spacing: 0.06em;">Supported Portals</div>
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-1.5">
            <span class="portal-chip"><i class="bi bi-shield-fill text-info me-1"></i>Admin</span>
            <span class="portal-chip"><i class="bi bi-box-seam-fill text-primary me-1"></i>Order Manager</span>
            <span class="portal-chip"><i class="bi bi-tags-fill text-warning me-1"></i>Catalog Manager</span>
            <span class="portal-chip"><i class="bi bi-headset text-success me-1"></i>Customer Support</span>
        </div>
    </div>
</div>

</body>
</html>
