@extends('emails.layout')

@section('title', 'Property Needs Attention - ' . $property->title)

@section('content')
<h1 class="email-title">Your property needs some attention</h1>

<p class="email-text">
    Hi {{ $host->name }}, thank you for submitting your property "<strong>{{ $property->title }}</strong>" to HabibiStay. 
</p>

<p class="email-text">
    After reviewing your listing, we found some areas that need improvement before we can approve it for booking. Don't worry - this is common and we're here to help you get it approved quickly!
</p>

<div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 18px; font-weight: 600;">
        ⚠️ Action Required
    </h3>
    <p style="margin: 0; color: #92400e;">
        Please review the feedback below and update your property listing accordingly.
    </p>
</div>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Property Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Property Name:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $property->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Location:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $property->city }}, {{ $property->country }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Property Type:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ ucfirst($property->property_type) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Submitted Date:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $property->created_at->format('M d, Y') }}</td>
        </tr>
    </table>
</div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    Common areas to improve
</h2>

<div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <ul style="color: #991b1b; line-height: 1.6; margin: 0; padding-left: 20px;">
        <li style="margin-bottom: 8px;"><strong>Photos:</strong> Add more high-quality photos (minimum 5 required)</li>
        <li style="margin-bottom: 8px;"><strong>Description:</strong> Provide a more detailed property description</li>
        <li style="margin-bottom: 8px;"><strong>Amenities:</strong> List all available amenities and features</li>
        <li style="margin-bottom: 8px;"><strong>House Rules:</strong> Clearly state your house rules and policies</li>
        <li style="margin-bottom: 8px;"><strong>Pricing:</strong> Ensure competitive and accurate pricing</li>
        <li><strong>Location:</strong> Verify the exact address and location details</li>
    </ul>
</div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    How to get approved
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">1</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Review the feedback</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Check the specific areas mentioned above that need improvement.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">2</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Update your listing</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Make the necessary changes to your property listing.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">3</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Resubmit for review</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Once updated, your property will be automatically reviewed again within 24 hours.</p>
        </div>
    </div>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $propertyUrl }}" class="email-button">
        Update Your Property
    </a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $dashboardUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        Go to Host Dashboard →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Need help?
</h2>

<p class="email-text">
    Our host support team is here to help you get your property approved. We can provide personalized feedback and guidance.
</p>

<p class="email-text">
    <strong>Contact our Host Support:</strong><br>
    📧 Email: host-support@habibistay.com<br>
    📱 WhatsApp: +966 50 123 4567<br>
    🕐 Available: 24/7
</p>

<p class="email-text">
    We're excited to help you become a successful host on HabibiStay!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
