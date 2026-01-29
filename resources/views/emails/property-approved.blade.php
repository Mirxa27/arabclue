@extends('emails.layout')

@section('title', 'Property Approved - ' . $property->title)

@section('content')
<h1 class="email-title">🎉 Congratulations! Your property has been approved</h1>

<p class="email-text">
    Great news, {{ $host->name }}! Your property "<strong>{{ $property->title }}</strong>" has been approved and is now live on HabibiStay.
</p>

<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 18px; font-weight: 600;">
        ✅ Your property is now live!
    </h3>
    <p style="margin: 0; color: #065f46;">
        Guests can now discover and book your property. Start earning from your first booking today!
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
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Price per Night:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">SAR {{ number_format($property->price_per_night, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Approved Date:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $property->approved_at ? $property->approved_at->format('M d, Y') : 'Today' }}</td>
        </tr>
    </table>
</div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    What happens next?
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">1</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Your property is searchable</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Guests can now find and book your property through our website and mobile app.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">2</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Manage your calendar</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Keep your availability updated to maximize bookings and avoid conflicts.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">3</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Start earning</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Receive bookings and start earning from your property investment.</p>
        </div>
    </div>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $propertyUrl }}" class="email-button">
        View Your Property
    </a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $dashboardUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        Go to Host Dashboard →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Tips for success
</h2>

<ul style="color: #374151; line-height: 1.6; padding-left: 20px;">
    <li style="margin-bottom: 8px;"><strong>Keep your calendar updated</strong> - Accurate availability leads to more bookings</li>
    <li style="margin-bottom: 8px;"><strong>Respond quickly to inquiries</strong> - Fast responses improve your host rating</li>
    <li style="margin-bottom: 8px;"><strong>Provide excellent service</strong> - Great reviews attract more guests</li>
    <li style="margin-bottom: 8px;"><strong>Use professional photos</strong> - High-quality images increase booking rates</li>
</ul>

<p class="email-text">
    Need help? Our host support team is available 24/7 to assist you with any questions.
</p>

<p class="email-text">
    Congratulations again, and welcome to the HabibiStay host community!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
