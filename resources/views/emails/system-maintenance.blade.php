@extends('emails.layout')

@section('title', ($maintenanceType === 'emergency' ? 'Emergency' : 'Scheduled') . ' Maintenance Notice')

@section('content')
@if($maintenanceType === 'emergency')
<h1 class="email-title">🚨 Emergency Maintenance Notice</h1>

<p class="email-text">
    We're writing to inform you about an emergency maintenance that is currently in progress or will begin shortly.
</p>

<div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #991b1b; font-size: 18px; font-weight: 600;">
        ⚠️ Emergency Maintenance in Progress
    </h3>
    <p style="margin: 0; color: #991b1b;">
        We're working to resolve a critical issue to ensure the best experience for all users.
    </p>
</div>
@else
<h1 class="email-title">🔧 Scheduled Maintenance Notice</h1>

<p class="email-text">
    We're writing to inform you about scheduled maintenance that will temporarily affect HabibiStay services.
</p>

<div style="background-color: #eff6ff; border: 1px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 18px; font-weight: 600;">
        🔧 Scheduled Maintenance
    </h3>
    <p style="margin: 0; color: #1e40af;">
        We're performing routine maintenance to improve our services and add new features.
    </p>
</div>
@endif

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Maintenance Schedule
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Start Time:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $maintenanceStart->format('l, M d, Y \a\t g:i A T') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">End Time:</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{{ $maintenanceEnd->format('l, M d, Y \a\t g:i A T') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Duration:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $maintenanceStart->diffForHumans($maintenanceEnd, true) }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 500;">Type:</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ ucfirst($maintenanceType) }} Maintenance</td>
        </tr>
    </table>
</div>

@if(!empty($affectedServices))
<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
        Affected Services
    </h3>
    
    <ul style="color: #374151; line-height: 1.6; padding-left: 20px; margin: 0;">
        @foreach($affectedServices as $service)
        <li style="margin-bottom: 8px;">{{ $service }}</li>
        @endforeach
    </ul>
</div>
@endif

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    What to expect during maintenance
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    @if($maintenanceType === 'emergency')
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #ef4444; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">!</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Service interruptions</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Some services may be temporarily unavailable or experience reduced functionality.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #f59e0b; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">⏱️</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Working to resolve quickly</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Our team is working around the clock to restore full functionality as soon as possible.</p>
        </div>
    </div>
    @else
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #3b82f6; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">🌐</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Website and app access</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">You may experience temporary difficulty accessing our website and mobile app.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #8b5cf6; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">📧</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Email notifications</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Email notifications may be delayed during the maintenance window.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #10b981; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">💳</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Existing bookings</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">All confirmed bookings remain valid and unaffected by this maintenance.</p>
        </div>
    </div>
    @endif
</div>

@if($maintenanceType === 'scheduled')
<div style="background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #065f46; font-size: 16px; font-weight: 600;">
        ✨ What's coming
    </h3>
    <p style="margin: 0; color: #065f46; font-size: 14px;">
        This maintenance will bring you improved performance, new features, and enhanced security for a better HabibiStay experience.
    </p>
</div>
@endif

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $statusPageUrl }}" class="email-button">
        Check Status Page
    </a>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $supportUrl }}" style="color: #667eea; text-decoration: none; font-weight: 500;">
        Contact Support →
    </a>
</div>

<div class="divider"></div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Stay updated
</h2>

<p class="email-text">
    For real-time updates on this maintenance, please visit our status page or follow us on social media.
</p>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h4 style="margin: 0 0 10px 0; color: #1f2937; font-size: 16px; font-weight: 600;">Stay Connected:</h4>
    <ul style="color: #374151; line-height: 1.6; padding-left: 20px; margin: 0;">
        <li style="margin-bottom: 5px;">📊 Status Page: {{ $statusPageUrl }}</li>
        <li style="margin-bottom: 5px;">📱 Twitter: @HabibiStay</li>
        <li style="margin-bottom: 5px;">📘 Facebook: HabibiStay</li>
        <li style="margin-bottom: 5px;">📧 Email: support@habibistay.com</li>
    </ul>
</div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Need immediate assistance?
</h2>

<p class="email-text">
    If you have an urgent issue during the maintenance window, our support team is standing by to help.
</p>

<p class="email-text">
    <strong>Emergency Support:</strong><br>
    📱 WhatsApp: +966 50 123 4567<br>
    📞 Phone: +966 11 123 4567<br>
    💬 Live Chat: Available when services are restored
</p>

@if($maintenanceType === 'emergency')
<p class="email-text">
    We sincerely apologize for any inconvenience this emergency maintenance may cause and appreciate your patience as we work to resolve the issue.<br>
    <strong>The HabibiStay Team</strong>
</p>
@else
<p class="email-text">
    We apologize for any inconvenience and appreciate your patience as we work to improve your HabibiStay experience.<br>
    <strong>The HabibiStay Team</strong>
</p>
@endif
@endsection
