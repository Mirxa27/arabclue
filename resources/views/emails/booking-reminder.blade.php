@extends('emails.layout')

@section('title', 'Booking Reminder - ' . $property->title)

@section('content')
@if($reminderType === 'check_in')
<h1 class="email-title">🎉 Your check-in is tomorrow!</h1>

<p class="email-text">
    Hi {{ $guest->name }}, we're excited that your stay at {{ $property->title }} is just around the corner!
</p>

<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 18px; font-weight: 600;">
        ✅ Check-in Tomorrow
    </h3>
    <p style="margin: 0; color: #065f46;">
        Your reservation is confirmed and ready. Here's everything you need to know for a smooth check-in.
    </p>
</div>

@elseif($reminderType === 'check_out')
<h1 class="email-title">🏠 Check-out reminder</h1>

<p class="email-text">
    Hi {{ $guest->name }}, we hope you've had an amazing stay at {{ $property->title }}!
</p>

<div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 18px; font-weight: 600;">
        📅 Check-out Today
    </h3>
    <p style="margin: 0; color: #92400e;">
        Please remember to check out by {{ $property->check_out_time }} today.
    </p>
</div>

@else
<h1 class="email-title">📅 Your upcoming stay</h1>

<p class="email-text">
    Hi {{ $guest->name }}, your stay at {{ $property->title }} is coming up soon!
</p>

<div style="background-color: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 18px; font-weight: 600;">
        🗓️ Upcoming Reservation
    </h3>
    <p style="margin: 0; color: #1e40af;">
        Get ready for your stay! Here are the important details you'll need.
    </p>
</div>
@endif

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Booking Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Property:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $property->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Address:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $property->address }}, {{ $property->city }}</td>
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
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Host:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $host->name }}</td>
        </tr>
    </table>
</div>

@if($reminderType === 'check_in')
<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    Check-in Instructions
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">1</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Contact your host</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Message {{ $host->name }} to confirm your arrival time and get check-in instructions.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">2</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Prepare your documents</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Have your ID and booking confirmation ready for check-in.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">3</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Review house rules</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Familiarize yourself with the property rules for a smooth stay.</p>
        </div>
    </div>
</div>

@elseif($reminderType === 'check_out')
<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    Check-out Instructions
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <ul style="color: #374151; line-height: 1.6; padding-left: 20px; margin: 0;">
        <li style="margin-bottom: 8px;">Check out by {{ $property->check_out_time }}</li>
        <li style="margin-bottom: 8px;">Leave keys as instructed by your host</li>
        <li style="margin-bottom: 8px;">Ensure all windows and doors are locked</li>
        <li style="margin-bottom: 8px;">Turn off all lights and appliances</li>
        <li style="margin-bottom: 8px;">Take all personal belongings</li>
    </ul>
</div>

<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 16px; font-weight: 600;">
        💡 Don't forget to leave a review!
    </h3>
    <p style="margin: 0; color: #065f46; font-size: 14px;">
        Share your experience to help other travelers and support your host.
    </p>
</div>
@endif

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $bookingUrl }}" class="email-button">
        View Booking Details
    </a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $hostContactUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        Message {{ $host->name }} →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Need assistance?
</h2>

<p class="email-text">
    Our support team is available 24/7 to help with any questions about your stay.
</p>

<p class="email-text">
    <strong>Contact Support:</strong><br>
    📧 Email: support@habibistay.com<br>
    📱 WhatsApp: +966 50 123 4567<br>
    💬 Live Chat: Available on our website and app
</p>

@if($reminderType === 'check_in')
<p class="email-text">
    We hope you have an amazing stay at {{ $property->title }}!<br>
    <strong>The HabibiStay Team</strong>
</p>
@elseif($reminderType === 'check_out')
<p class="email-text">
    Thank you for choosing HabibiStay. We hope you had a wonderful stay!<br>
    <strong>The HabibiStay Team</strong>
</p>
@else
<p class="email-text">
    We're excited for your upcoming stay!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endif
@endsection
