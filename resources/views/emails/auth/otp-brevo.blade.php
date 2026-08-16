<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email address</title>
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
        .otp-box {
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: 8px;
            margin: 0;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="{{ config('app.url') }}" class="logo">ShopCalm<span>.in</span></a>
        </div>
        
        <div class="content">
            <h1>Verify Your Email Address</h1>
            <p>Welcome to ShopCalm! To complete your registration and secure your account, please use the verification code below.</p>
            
            <div class="otp-box">
                <p class="otp-code">{{ $otp }}</p>
                <p class="expiry-note">This code will expire in exactly 10 minutes.</p>
            </div>
            
            <p>If you did not request this code, you can safely ignore this email. Another user might have entered your email address by mistake.</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} ShopCalm. All rights reserved.</p>
            <p>Need help? Contact our support team at <a href="mailto:support@shopcalm.in">support@shopcalm.in</a></p>
        </div>
    </div>
</body>
</html>
