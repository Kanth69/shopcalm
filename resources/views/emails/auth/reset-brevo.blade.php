<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        .header {
            background-color: #2563eb;
            padding: 30px 20px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo span {
            color: #93c5fd;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        h1 {
            color: #111827;
            font-size: 24px;
            margin-bottom: 16px;
            font-weight: 700;
        }
        p {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            font-weight: 600;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-size: 16px;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .expiry-note {
            font-size: 14px;
            color: #64748b;
            margin-top: 12px;
            font-style: italic;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            color: #94a3b8;
            font-size: 13px;
            margin: 0 0 8px 0;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .link-text {
            word-break: break-all;
            font-size: 12px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">ShopCalm<span>.in</span></a>
        </div>
        
        <div class="content">
            <h1>Reset Your Password</h1>
            <p>We received a request to reset your password for your ShopCalm account. Click the button below to choose a new password.</p>
            
            <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
            
            <p class="expiry-note">This password reset link will expire in 60 minutes.</p>
            
            <p>If you did not request a password reset, no further action is required.</p>

            <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
            <p style="font-size: 12px; text-align: left;">If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
            <p class="link-text" style="text-align: left;">{{ $resetUrl }}</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} ShopCalm. All rights reserved.</p>
            <p>Need help? Contact our support team at <a href="mailto:support@shopcalm.in">support@shopcalm.in</a></p>
        </div>
    </div>
</body>
</html>
