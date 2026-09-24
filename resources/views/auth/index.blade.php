<x-guest-layout>
    <x-slot name="title">Authentication</x-slot>

    <div id="auth-container" class="auth-card-container">
        <!-- Initial State -->
        <div id="state-initial">
            @include('auth.components.initial')
        </div>

        <!-- Login State -->
        <div id="state-login" style="display: none;">
            @include('auth.components.login')
        </div>

        <!-- Register State -->
        <div id="state-register" style="display: none;">
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
            padding: 4px 2px;
            min-height: 340px;
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

        .password-input-wrapper {
            position: relative;
            width: 100%;
        }

        .password-input-wrapper .auth-input {
            padding-right: 46px;
        }

        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
            z-index: 5;
            font-size: 18px;
        }

        .password-toggle-btn:hover {
            color: #3b82f6;
            background-color: #f1f5f9;
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

        .auth-alert.error,
        .auth-alert.danger {
            background-color: #fef2f2 !important;
            color: #b91c1c !important;
            border: 1px solid #fca5a5 !important;
        }
        .auth-alert.error i,
        .auth-alert.danger i {
            color: #dc2626 !important;
        }

        /* Modern 3-Step Indicator Bar */
        .auth-stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin-bottom: 14px;
        }
        .step-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #f1f5f9;
            color: #94a3b8;
            transition: all 0.25s;
        }
        .step-pill.active {
            background: rgba(99, 102, 241, 0.12);
            color: #4f46e5;
            border: 1px solid rgba(99, 102, 241, 0.3);
        }
        .step-pill.completed {
            background: #ecfdf5;
            color: #059669;
        }
        .step-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            background: #cbd5e1;
            color: #fff;
        }
        .step-pill.active .step-dot {
            background: #4f46e5;
            color: #fff;
        }
        .step-pill.completed .step-dot {
            background: #059669;
            color: #fff;
        }
        .step-connector {
            width: 12px;
            height: 2px;
            background: #e2e8f0;
            border-radius: 1px;
        }
        .step-connector.active {
            background: #4f46e5;
        }

        /* 6-Digit Individual OTP Input Boxes */
        .otp-boxes-wrapper {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 10px 0;
        }
        .otp-digit-input {
            width: 48px;
            height: 52px;
            text-align: center;
            font-family: monospace;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            caret-color: #4f46e5;
        }
        .otp-digit-input:focus {
            border-color: #4f46e5;
            background: #ffffff;
            box-shadow: 0 0 0 3.5px rgba(99, 102, 241, 0.18);
            transform: translateY(-2px);
        }
        .otp-digit-input.filled {
            border-color: #a5b4fc;
            background: #ffffff;
        }
        .otp-digit-input.is-invalid {
            border-color: #ef4444 !important;
            background: #fff5f5 !important;
            color: #dc2626 !important;
            animation: shake 0.35s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        /* Clean Centered Email Pill */
        .email-banner-pill {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 30px;
            padding: 5px 14px;
            max-width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .email-banner-pill:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }
        .email-text {
            color: #0f172a;
            font-size: 13.5px;
            letter-spacing: -0.2px;
            max-width: 210px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
        }
        .edit-pill-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #4f46e5;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 12px;
            cursor: pointer;
            line-height: 1.4;
            transition: all 0.15s ease;
            margin-left: 2px;
            display: inline-flex;
            align-items: center;
        }
        .edit-pill-btn:hover {
            background: #4f46e5;
            color: #ffffff;
            border-color: #4f46e5;
            transform: scale(1.04);
        }

        .field-hint {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 2px;
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

        /* Mobile Single-Screen View Optimization */
        @media (max-width: 576px) {
            .auth-card-container {
                padding: 0;
                min-height: auto;
            }
            .auth-header {
                margin-bottom: 12px;
            }
            .auth-logo {
                margin-bottom: 6px;
            }
            .auth-logo img, .auth-logo svg {
                height: 28px !important;
            }
            .auth-title {
                font-size: 18px;
                margin-bottom: 2px;
                letter-spacing: -0.3px;
            }
            .auth-subtitle {
                font-size: 12px;
            }
            .auth-form {
                gap: 7px;
            }
            .form-row {
                gap: 7px;
            }
            .form-group {
                gap: 2px;
            }
            .form-group label {
                font-size: 10.5px;
                letter-spacing: 0.3px;
            }
            .auth-input {
                padding: 7px 11px;
                font-size: 13.5px;
                border-radius: 9px;
                min-height: 37px;
            }
            .password-toggle-btn {
                font-size: 15px;
                right: 8px;
            }
            .otp-input-group {
                gap: 6px;
            }
            .otp-btn {
                padding: 0 12px;
                font-size: 12px;
                border-radius: 9px;
            }
            .auth-submit-btn {
                padding: 8.5px 12px;
                font-size: 13px;
                border-radius: 9px;
                margin-top: 2px;
                min-height: 38px;
                gap: 6px;
            }
            .auth-divider {
                margin: 4px 0;
            }
            .auth-divider span {
                font-size: 10px;
                padding: 0 8px;
            }
            .google-btn {
                padding: 7px 12px;
                font-size: 12px;
                border-radius: 9px;
                min-height: 35px;
                gap: 8px;
            }
            .google-btn img {
                width: 16px;
            }
            .auth-alert {
                padding: 7px 11px;
                font-size: 11.5px;
                margin-bottom: 8px;
                border-radius: 8px;
            }
            .back-nav-btn {
                padding: 3px 8px;
                font-size: 11px;
                border-radius: 12px;
            }
            .field-hint {
                font-size: 11px;
            }
            .form-options {
                margin: 2px 0;
            }
            .otp-digit-input {
                width: 38px;
                height: 44px;
                font-size: 18px;
                border-radius: 9px;
                border-width: 1.5px;
            }
            .otp-boxes-wrapper {
                gap: 5px;
                margin: 8px 0;
            }
            .step-pill {
                padding: 2px 7px;
                font-size: 10px;
            }
            .step-connector {
                width: 8px;
            }
            .email-banner-pill {
                padding: 4px 10px;
            }
            .email-text {
                font-size: 12px;
                max-width: 165px;
            }
            .edit-pill-btn {
                padding: 1px 7px;
                font-size: 10.5px;
            }
        }
    </style>

    @push('scripts')
        <script>
            window.csrfToken = "{{ csrf_token() }}";
        </script>
        <script src="{{ asset('js/auth-flow.js') }}?v={{ filemtime(public_path('js/auth-flow.js')) }}"></script>
    @endpush
</x-guest-layout>
