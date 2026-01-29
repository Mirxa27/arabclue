<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'HabibiStay')</title>
    <style>
        /* Reset styles */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
        }

        /* Base styles */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            min-width: 100%;
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #374151;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .email-logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            text-decoration: none;
            margin-bottom: 10px;
            display: inline-block;
        }

        .email-tagline {
            color: #e5e7eb;
            font-size: 14px;
            margin: 0;
        }

        .email-content {
            padding: 40px 30px;
        }

        .email-title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 20px 0;
            line-height: 1.3;
        }

        .email-text {
            font-size: 16px;
            line-height: 1.6;
            color: #374151;
            margin: 0 0 20px 0;
        }

        .email-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        .email-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .email-footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .email-footer-text {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 10px 0;
        }

        .social-links {
            margin: 20px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #6b7280;
            text-decoration: none;
        }

        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 30px 0;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            .email-header, .email-content, .email-footer {
                padding: 20px !important;
            }
            .email-title {
                font-size: 20px !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <a href="{{ url('/') }}" class="email-logo">HabibiStay</a>
            <p class="email-tagline">Exceptional Stays. Exceptional Returns.</p>
        </div>

        <!-- Content -->
        <div class="email-content">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p class="email-footer-text">
                Thank you for choosing HabibiStay
            </p>
            
            <div class="social-links">
                <a href="#">Facebook</a>
                <a href="#">Twitter</a>
                <a href="#">Instagram</a>
                <a href="#">LinkedIn</a>
            </div>

            <p class="email-footer-text">
                © {{ date('Y') }} HabibiStay. All rights reserved.<br>
                Riyadh, Saudi Arabia
            </p>

            <p class="email-footer-text">
                <a href="{{ url('/unsubscribe') }}" style="color: #6b7280;">Unsubscribe</a> |
                <a href="{{ url('/privacy') }}" style="color: #6b7280;">Privacy Policy</a> |
                <a href="{{ url('/contact') }}" style="color: #6b7280;">Contact Us</a>
            </p>
        </div>
    </div>
</body>
</html>
