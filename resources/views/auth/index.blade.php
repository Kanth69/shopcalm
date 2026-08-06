<x-guest-layout>
    <x-slot name="title">Authentication</x-slot>

    <div id="auth-container" class="auth-card-container">
        <!-- Initial State -->
        <div id="state-initial" style="{{ $errors->any() ? 'display: none;' : '' }}">
            @include('auth.components.initial')
        </div>

        <!-- Login State -->
        <div id="state-login" style="display: none;">
            @include('auth.components.login')
        </div>

        <!-- Register State -->
        <div id="state-register" style="{{ $errors->any() ? 'display: block; opacity: 1; transform: translateY(0);' : 'display: none;' }}">
            @include('auth.components.register')
        </div>

        <!-- Forgot Password State -->
        <div id="state-forgot-password" style="display: none;">
            @include('auth.components.forgot-password')
        </div>

        <!-- Success State -->
        <div id="state-success" style="display: none;">
            @include('auth.components.success')
        </div>
    </div>

    <style>
        .auth-card-container {
            position: relative;
            padding: 10px 5px;
            min-height: 380px;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            margin-bottom: 16px;
        }

        .logo-w {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            border-radius: 12px;
            margin-right: 12px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .logo-text {
            font-size: 26px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: -0.5px;
        }

        .logo-text span {
            color: #3b82f6;
        }

        .auth-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #64748b;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .form-row {
            display: flex;
            gap: 12px;
        }

        .flex-1 {
            flex: 1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .forgot-link, .change-identifier-btn {
            font-size: 13px;
            font-weight: 700;
            color: #3b82f6;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        .back-nav-btn {
            position: absolute;
            top: 0;
            left: 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            z-index: 10;
        }

        .back-nav-btn:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .forgot-link:hover, .change-identifier-btn:hover {
            text-decoration: underline;
        }

        .auth-input {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 15px;
            color: #1e293b;
            transition: all 0.2s ease;
            outline: none;
            width: 100%;
        }

        .auth-input:focus {
            border-color: #3b82f6;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .readonly-input {
            background-color: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
            border-color: #cbd5e1;
        }

        .auth-alert {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
            animation: fadeIn 0.3s ease;
        }

        .auth-alert.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .auth-alert.info {
            background-color: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .auth-alert.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .field-hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .otp-input-group {
            display: flex;
            gap: 10px;
        }

        .otp-btn {
            background-color: #f1f5f9;
            border: 2px solid #e2e8f0;
            color: #334155;
            font-weight: 600;
            font-size: 13px;
            padding: 0 16px;
            border-radius: 12px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .otp-btn:hover:not(:disabled) {
            background-color: #e2e8f0;
            border-color: #cbd5e1;
        }

        .otp-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .auth-submit-btn {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
            margin-top: 6px;
        }

        .auth-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }

        .auth-submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .auth-divider {
            position: relative;
            text-align: center;
            margin: 12px 0;
        }

        .auth-divider:before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #e2e8f0;
            z-index: 1;
        }

        .auth-divider span {
            position: relative;
            background-color: #fff;
            padding: 0 15px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
            z-index: 2;
        }

        .google-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s;
            background: white;
        }

        .google-btn:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
        }

        .google-btn img {
            width: 20px;
        }

        /* Toast Popup */
        .auth-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 14px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }

        .auth-toast.show {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .auth-toast.error {
            background-color: #ef4444;
        }

        .auth-toast.success {
            background-color: #10b981;
        }

        /* Spin icon animation */
        .spin-icon {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    @push('scripts')
        <script>
            window.csrfToken = "{{ csrf_token() }}";
        </script>
        <script src="{{ asset('js/auth-flow.js') }}"></script>
    @endpush
</x-guest-layout>
