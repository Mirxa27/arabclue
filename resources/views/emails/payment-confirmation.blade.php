@extends('emails.layout')

@section('title', 'Payment Confirmed - Booking #' . $booking->id)

@section('content')
<h1 class="email-title">🎉 Payment Confirmed!</h1>

<p class="email-text">
    Great news, {{ $guest->name }}! Your payment has been successfully processed and your booking is confirmed.
</p>

<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 18px; font-weight: 600;">
        ✅ Booking Confirmed
    </h3>
    <p style="margin: 0; color: #065f46;">
        Your reservation at {{ $property->title }} is all set! Get ready for an amazing stay.
    </p>
</div>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Booking Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Booking ID:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">#{{ $booking->id }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Property:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $property->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Location:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $property->city }}, {{ $property->country }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Check-in:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $booking->check_in->format('l, M d, Y') }} at {{ $property->check_in_time }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Check-out:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $booking->check_out->format('l, M d, Y') }} at {{ $property->check_out_time }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Guests:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Nights:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Payment Summary
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280;">{{ $booking->nights }} nights × SAR {{ number_format($booking->subtotal / $booking->nights, 2) }}</td>
            <td style="padding: 8px 0; color: #1f2937; text-align: right;">SAR {{ number_format($booking->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280;">Cleaning fee</td>
            <td style="padding: 8px 0; color: #1f2937; text-align: right;">SAR {{ number_format($booking->cleaning_fee, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280;">Service fee</td>
            <td style="padding: 8px 0; color: #1f2937; text-align: right;">SAR {{ number_format($booking->service_fee, 2) }}</td>
        </tr>
        <tr style="border-top: 1px solid #e5e7eb;">
            <td style="padding: 12px 0 8px 0; color: #1f2937; font-weight: 600; font-size: 16px;">Total Paid</td>
            <td style="padding: 12px 0 8px 0; color: #1f2937; font-weight: 600; font-size: 16px; text-align: right;">SAR {{ number_format($booking->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6b7280; font-size: 14px;">Payment Method</td>
            <td style="padding: 4px 0; color: #6b7280; font-size: 14px; text-align: right;">{{ ucfirst(str_replace('_', ' ', $booking->payment_method)) }}</td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $bookingUrl }}" class="email-button">
        View Booking Details
    </a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $receiptUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        Download Receipt →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    What's next?
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">1</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Contact your host</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Introduce yourself and coordinate check-in details with {{ $host->name }}.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">2</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Plan your trip</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Explore local attractions and plan your itinerary for {{ $property->city }}.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">3</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Prepare for check-in</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Review house rules and prepare any required documents for a smooth check-in.</p>
        </div>
    </div>
</div>

<div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 16px; font-weight: 600;">
        📱 Download our mobile app
    </h3>
    <p style="margin: 0; color: #92400e; font-size: 14px;">
        Get instant access to your booking details, chat with your host, and receive important updates on the go.
    </p>
</div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Need help?
</h2>

<p class="email-text">
    Our support team is available 24/7 to assist you with any questions about your booking.
</p>

<p class="email-text">
    <strong>Contact Support:</strong><br>
    📧 Email: support@habibistay.com<br>
    📱 WhatsApp: +966 50 123 4567<br>
    💬 Live Chat: Available on our website and app
</p>

<p class="email-text">
    We can't wait for you to experience your stay at {{ $property->title }}!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
