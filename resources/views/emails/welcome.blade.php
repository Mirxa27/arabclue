@extends('emails.layout')

@section('title', 'Welcome to HabibiStay')

@section('content')
<h1 class="email-title">Welcome to HabibiStay, {{ $user->name }}! 🎉</h1>

<p class="email-text">
    We're thrilled to have you join our community of travelers and hosts. HabibiStay is your gateway to exceptional stays and exceptional returns in beautiful Saudi Arabia.
</p>

@if($verificationUrl)
<div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <p style="margin: 0; color: #92400e; font-weight: 600;">
        📧 Please verify your email address to get started
    </p>
    <p style="margin: 10px 0 0 0; color: #92400e;">
        Click the button below to verify your email and unlock all features.
    </p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $verificationUrl }}" class="email-button">
        Verify Email Address
    </a>
</div>
@endif

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    What's next?
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">1</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Complete Your Profile</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Add a photo and tell us about yourself to build trust with the community.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">2</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Explore Amazing Properties</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Discover unique stays across Saudi Arabia, from modern apartments to traditional riads.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">3</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Start Hosting or Investing</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">List your property or invest in our curated portfolio for steady returns.</p>
        </div>
    </div>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $exploreUrl }}" class="email-button">
        Explore Properties
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Need help getting started?
</h2>

<p class="email-text">
    Our team is here to help! You can also chat with Sara, our AI assistant, available 24/7 on our website and mobile app.
</p>

<p class="email-text">
    <strong>Contact us:</strong><br>
    📧 Email: support@habibistay.com<br>
    📱 WhatsApp: +966 50 123 4567<br>
    🕐 Available: 24/7
</p>

<p class="email-text">
    Welcome aboard, and happy travels!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
