@extends('emails.layout')

@section('title', 'Payout Processed - SAR ' . number_format($payoutAmount, 2))

@section('content')
<h1 class="email-title">💰 Payout Processed!</h1>

<p class="email-text">
    Great news, {{ $host->name }}! Your payout has been processed and is on its way to your account.
</p>

<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 18px; font-weight: 600;">
        ✅ Payout Confirmed
    </h3>
    <p style="margin: 0; color: #065f46;">
        SAR {{ number_format($payoutAmount, 2) }} has been processed and will arrive in your account within 1-3 business days.
    </p>
</div>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Payout Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Payout Amount:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600; font-size: 18px;">SAR {{ number_format($payoutAmount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Processing Date:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $payoutDate->format('l, M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Expected Arrival:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $payoutDate->addBusinessDays(3)->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Booking Reference:</td>
            <td style="padding: 8px 0; color: #1f2937;">#{{ $booking->id }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Booking Summary
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Property:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $property->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Guest:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $guest->name }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Stay Period:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Nights:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Total Booking Value:</td>
            <td style="padding: 8px 0; color: #1f2937;">SAR {{ number_format($booking->total_amount, 2) }}</td>
        </tr>
    </table>
</div>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Payout Breakdown
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280;">Booking subtotal</td>
            <td style="padding: 8px 0; color: #1f2937; text-align: right;">SAR {{ number_format($booking->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280;">Cleaning fee</td>
            <td style="padding: 8px 0; color: #1f2937; text-align: right;">SAR {{ number_format($booking->cleaning_fee, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280;">HabibiStay service fee ({{ $property->service_fee_percentage }}%)</td>
            <td style="padding: 8px 0; color: #ef4444; text-align: right;">-SAR {{ number_format($booking->service_fee, 2) }}</td>
        </tr>
        <tr style="border-top: 1px solid #e5e7eb;">
            <td style="padding: 12px 0 8px 0; color: #1f2937; font-weight: 600; font-size: 16px;">Your Payout</td>
            <td style="padding: 12px 0 8px 0; color: #059669; font-weight: 600; font-size: 16px; text-align: right;">SAR {{ number_format($payoutAmount, 2) }}</td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $earningsUrl }}" class="email-button">
        View Earnings Dashboard
    </a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $bookingUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        View Booking Details →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    Keep earning with HabibiStay
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">📈</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Optimize your pricing</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Use our smart pricing tools to maximize your earnings based on demand.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #8b5cf6; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">⭐</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Maintain high ratings</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Great reviews lead to more bookings and higher earnings.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">📅</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Keep calendar updated</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Accurate availability helps you capture more booking opportunities.</p>
        </div>
    </div>
</div>

<div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 16px; font-weight: 600;">
        📊 Tax Documentation
    </h3>
    <p style="margin: 0; color: #92400e; font-size: 14px;">
        Don't forget to download your tax documents for record keeping. Available in your host dashboard.
    </p>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $taxDocumentUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        Download Tax Documents →
    </a>
</div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Questions about your payout?
</h2>

<p class="email-text">
    If you have any questions about this payout or need assistance with your host account, our support team is here to help.
</p>

<p class="email-text">
    <strong>Host Support:</strong><br>
    📧 Email: host-support@habibistay.com<br>
    📱 WhatsApp: +966 50 123 4567<br>
    💬 Live Chat: Available 24/7
</p>

<p class="email-text">
    Thank you for being a valued HabibiStay host!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
