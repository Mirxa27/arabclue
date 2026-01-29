@extends('emails.layout')

@section('title', 'How was your stay at ' . $property->title . '?')

@section('content')
<h1 class="email-title">How was your stay? ⭐</h1>

<p class="email-text">
    Hi {{ $guest->name }}, we hope you had an amazing time at {{ $property->title }}!
</p>

<p class="email-text">
    Your experience matters to us and helps other travelers make informed decisions. Would you mind sharing your thoughts about your recent stay?
</p>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Your Stay Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Property:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $property->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Location:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $property->city }}, {{ $property->country }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Host:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $host->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Check-in:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->check_in->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Check-out:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->check_out->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Duration:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}</td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $reviewUrl }}" class="email-button">
        Write Your Review
    </a>
</div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    What to include in your review
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #fbbf24; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">⭐</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Overall Experience</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Rate your overall satisfaction with the property and stay experience.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">🏠</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Property Condition</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">How clean, comfortable, and well-maintained was the property?</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #8b5cf6; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">👤</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Host Communication</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">How responsive and helpful was {{ $host->name }}?</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #ef4444; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">📍</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Location & Amenities</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Was the location convenient and did the amenities meet your expectations?</p>
        </div>
    </div>
</div>

<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 16px; font-weight: 600;">
        💡 Your review helps others
    </h3>
    <p style="margin: 0; color: #065f46; font-size: 14px;">
        Honest reviews help fellow travelers choose the right place for their stay and help hosts improve their service.
    </p>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $propertyUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        View Property Details →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Planning your next trip?
</h2>

<p class="email-text">
    Discover more amazing properties on HabibiStay and enjoy exclusive member benefits:
</p>

<ul style="color: #374151; line-height: 1.6; padding-left: 20px;">
    <li style="margin-bottom: 8px;">Early access to new listings</li>
    <li style="margin-bottom: 8px;">Special discounts for repeat guests</li>
    <li style="margin-bottom: 8px;">Priority customer support</li>
    <li style="margin-bottom: 8px;">Personalized recommendations</li>
</ul>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/properties') }}" style="display: inline-block; background-color: #f3f4f6; color: #374151; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 500; border: 1px solid #d1d5db;">
        Explore More Properties
    </a>
</div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Questions about your review?
</h2>

<p class="email-text">
    If you have any questions about the review process or need assistance, our support team is here to help.
</p>

<p class="email-text">
    <strong>Contact Support:</strong><br>
    📧 Email: support@habibistay.com<br>
    📱 WhatsApp: +966 50 123 4567<br>
    💬 Live Chat: Available 24/7
</p>

<p class="email-text">
    Thank you for choosing HabibiStay and for taking the time to share your experience!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
