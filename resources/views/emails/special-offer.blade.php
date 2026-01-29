@extends('emails.layout')

@section('title', $offerTitle . ' - Save ' . $discountPercentage . '%!')

@section('content')
<h1 class="email-title">🎉 {{ $offerTitle }}</h1>

<p class="email-text">
    Hi {{ $user->name }}, we have an exclusive offer just for you!
</p>

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 30px; margin: 30px 0; text-align: center; color: white;">
    <h2 style="margin: 0 0 15px 0; font-size: 36px; font-weight: 700; color: white;">
        {{ $discountPercentage }}% OFF
    </h2>
    <p style="margin: 0; font-size: 18px; color: #e5e7eb;">
        {{ $offerDescription }}
    </p>
    @if($promoCode)
    <div style="background: rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 15px; margin: 20px 0; border: 2px dashed rgba(255, 255, 255, 0.5);">
        <p style="margin: 0 0 5px 0; font-size: 14px; color: #e5e7eb;">Use promo code:</p>
        <p style="margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 2px; color: white;">{{ $promoCode }}</p>
    </div>
    @endif
    <p style="margin: 15px 0 0 0; font-size: 16px; color: #fbbf24;">
        ⏰ Valid until {{ $validUntil->format('M d, Y \a\t g:i A') }}
    </p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $browseUrl }}{{ $promoCode ? '?promo=' . $promoCode : '' }}" class="email-button" style="font-size: 18px; padding: 16px 32px;">
        Browse Properties & Save
    </a>
</div>

@if($featuredProperties->count() > 0)
<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 40px 0 20px 0;">
    ✨ Featured Properties for Your Trip
</h2>

<div style="margin: 20px 0;">
    @foreach($featuredProperties->take(3) as $property)
    <div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin-bottom: 15px; border: 1px solid #e5e7eb;">
        <div style="display: flex; align-items: flex-start;">
            @if($property->images && count($property->images) > 0)
            <div style="width: 120px; height: 80px; background-image: url('{{ $property->images[0]['url'] ?? '' }}'); background-size: cover; background-position: center; border-radius: 6px; margin-right: 15px; flex-shrink: 0;"></div>
            @endif
            <div style="flex: 1;">
                <h3 style="margin: 0 0 8px 0; font-size: 16px; font-weight: 600; color: #1f2937;">{{ $property->title }}</h3>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">📍 {{ $property->city }}, {{ $property->country }}</p>
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <span style="color: #6b7280; font-size: 14px; text-decoration: line-through;">SAR {{ number_format($property->price_per_night, 0) }}</span>
                        <span style="color: #059669; font-weight: 600; font-size: 16px; margin-left: 8px;">SAR {{ number_format($property->price_per_night * (1 - $discountPercentage / 100), 0) }}</span>
                        <span style="color: #6b7280; font-size: 14px;">/night</span>
                    </div>
                    @if($property->overall_rating)
                    <div style="display: flex; align-items: center;">
                        <span style="color: #fbbf24; margin-right: 4px;">⭐</span>
                        <span style="color: #1f2937; font-weight: 500; font-size: 14px;">{{ number_format($property->overall_rating, 1) }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<div class="divider"></div>

<h2 style="font-size: 20px; font-weight: 600; color: #1f2937; margin: 30px 0 15px 0;">
    How to redeem your discount
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 25px; margin: 20px 0;">
    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">1</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Browse properties</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Find your perfect stay from thousands of amazing properties across Saudi Arabia.</p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start; margin-bottom: 20px;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">2</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Apply promo code</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">
                @if($promoCode)
                Enter code <strong>{{ $promoCode }}</strong> at checkout to get {{ $discountPercentage }}% off your booking.
                @else
                Your discount will be automatically applied when you book through this email.
                @endif
            </p>
        </div>
    </div>

    <div style="display: flex; align-items: flex-start;">
        <div style="background-color: #667eea; color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-weight: 600;">3</div>
        <div>
            <h3 style="margin: 0 0 5px 0; font-size: 16px; font-weight: 600; color: #1f2937;">Enjoy your stay</h3>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Complete your booking and get ready for an amazing experience at a great price!</p>
        </div>
    </div>
</div>

<div style="background-color: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <h3 style="margin: 0 0 10px 0; color: #92400e; font-size: 16px; font-weight: 600;">
        ⏰ Limited Time Offer
    </h3>
    <p style="margin: 0; color: #92400e; font-size: 14px;">
        This exclusive {{ $discountPercentage }}% discount expires on {{ $validUntil->format('l, M d, Y \a\t g:i A') }}. Don't miss out!
    </p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $browseUrl }}{{ $promoCode ? '?promo=' . $promoCode : '' }}" style="display: inline-block; background-color: #f3f4f6; color: #374151; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 500; border: 1px solid #d1d5db;">
        Browse All Properties
    </a>
</div>

<h2 style="font-size: 18px; font-weight: 600; color: #1f2937; margin: 20px 0 15px 0;">
    Why choose HabibiStay?
</h2>

<div style="background-color: #f9fafb; border-radius: 8px; padding: 20px; margin: 20px 0;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div style="text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">🏠</div>
            <h4 style="margin: 0 0 5px 0; color: #1f2937; font-size: 14px; font-weight: 600;">Verified Properties</h4>
            <p style="margin: 0; color: #6b7280; font-size: 12px;">All properties are verified for quality and safety</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">💰</div>
            <h4 style="margin: 0 0 5px 0; color: #1f2937; font-size: 14px; font-weight: 600;">Best Prices</h4>
            <p style="margin: 0; color: #6b7280; font-size: 12px;">Competitive rates with no hidden fees</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">🛡️</div>
            <h4 style="margin: 0 0 5px 0; color: #1f2937; font-size: 14px; font-weight: 600;">Secure Booking</h4>
            <p style="margin: 0; color: #6b7280; font-size: 12px;">Safe and secure payment processing</p>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; margin-bottom: 8px;">📞</div>
            <h4 style="margin: 0 0 5px 0; color: #1f2937; font-size: 14px; font-weight: 600;">24/7 Support</h4>
            <p style="margin: 0; color: #6b7280; font-size: 12px;">Round-the-clock customer assistance</p>
        </div>
    </div>
</div>

<div style="text-align: center; margin: 30px 0; padding: 20px; background-color: #f9fafb; border-radius: 8px;">
    <p style="margin: 0; color: #6b7280; font-size: 14px;">
        Questions about this offer? Contact us at 
        <a href="mailto:support@habibistay.com" style="color: #667eea; text-decoration: none;">support@habibistay.com</a>
        or WhatsApp +966 50 123 4567
    </p>
</div>

<div style="text-align: center; margin: 20px 0;">
    <a href="{{ $unsubscribeUrl }}" style="color: #9ca3af; text-decoration: none; font-size: 12px;">
        Don't want to receive promotional emails? Unsubscribe here
    </a>
</div>

<p class="email-text">
    Happy travels and enjoy your savings!<br>
    <strong>The HabibiStay Team</strong>
</p>
@endsection
