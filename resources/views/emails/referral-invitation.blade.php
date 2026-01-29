<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e5e5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            background-color: #13246a;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 180px;
            margin-bottom: 15px;
        }
        .email-body {
            padding: 30px;
            background-color: #ffffff;
        }
        .user-message {
            border-left: 3px solid #13246a;
            padding: 10px 15px;
            margin: 20px 0;
            background-color: #f9f9f9;
            font-style: italic;
        }
        .cta-button {
            display: inline-block;
            background-color: #13246a;
            color: white !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 25px 0;
        }
        .reward-card {
            background-color: #f0f7ff;
            border: 1px solid #c3ddff;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .reward-amount {
            font-size: 24px;
            font-weight: bold;
            color: #13246a;
            margin: 10px 0;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ asset('images/logo-white.png') }}" alt="HabibiStay Logo">
            <h1>You've Been Invited!</h1>
        </div>
        
        <div class="email-body">
            <p>Hello,</p>
            
            <p><strong>{{ $referrer->name }}</strong> thinks you'd love HabibiStay and has invited you to join!</p>
            
            @if($customMessage)
                <div class="user-message">
                    "{{ $customMessage }}"
                </div>
            @endif
            
            <div class="reward-card">
                <p>Join using this referral link and you'll both receive</p>
                <div class="reward-amount">$10 in Credits</div>
                <p>Plus 5% off your first booking!</p>
            </div>
            
            <p>HabibiStay is a premier platform for short-term and long-term property rentals, connecting guests with unique stays around the world.</p>
            
            <div style="text-align: center;">
                <a href="{{ $referralLink }}" class="cta-button">Sign Up Now</a>
            </div>
            
            <p>This referral offer is valid for new users only. Credits will be applied to your account after registration.</p>
        </div>
        
        <div class="email-footer">
            <p>© {{ date('Y') }} HabibiStay. All rights reserved.</p>
            <p>If you don't want to receive these emails, you can <a href="{{ url('/unsubscribe') }}">unsubscribe here</a>.</p>
            <div class="social-links">
                <a href="https://facebook.com/habibistay"><img src="{{ asset('images/icons/facebook.png') }}" alt="Facebook"></a>
                <a href="https://instagram.com/habibistay"><img src="{{ asset('images/icons/instagram.png') }}" alt="Instagram"></a>
                <a href="https://twitter.com/habibistay"><img src="{{ asset('images/icons/twitter.png') }}" alt="Twitter"></a>
            </div>
        </div>
    </div>
</body>
</html>
