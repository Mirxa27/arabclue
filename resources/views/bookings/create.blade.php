@extends('layouts.app')

@section('title', 'Book Property - ' . $property->title)

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Booking Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Complete Your Booking</h1>
                    <p class="text-gray-600 mt-2">You're just a few steps away from your perfect stay</p>
                </div>

                <form id="bookingForm" action="{{ route('bookings.store') }}" method="POST" class="space-y-8">
                    @csrf
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <input type="hidden" name="check_in" value="{{ request('check_in') }}">
                    <input type="hidden" name="check_out" value="{{ request('check_out') }}">
                    <input type="hidden" name="guests" value="{{ request('guests', 1) }}">

                    <!-- Guest Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Guest Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="guest_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" id="guest_name" name="guest_name" 
                                       value="{{ old('guest_name', auth()->user()->name ?? '') }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('guest_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="guest_email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                                <input type="email" id="guest_email" name="guest_email" 
                                       value="{{ old('guest_email', auth()->user()->email ?? '') }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('guest_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="guest_phone" class="block text-sm font-medium text-gray-700">Phone Number *</label>
                                <input type="tel" id="guest_phone" name="guest_phone" 
                                       value="{{ old('guest_phone', auth()->user()->phone ?? '') }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="+966 50 123 4567">
                                @error('guest_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="guest_count" class="block text-sm font-medium text-gray-700">Number of Guests *</label>
                                <select id="guest_count" name="guest_count" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @for($i = 1; $i <= $property->accommodates; $i++)
                                        <option value="{{ $i }}" {{ request('guests', 1) == $i ? 'selected' : '' }}>
                                            {{ $i }} {{ $i == 1 ? 'Guest' : 'Guests' }}
                                        </option>
                                    @endfor
                                </select>
                                @error('guest_count')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="special_requests" class="block text-sm font-medium text-gray-700">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Any special requests or requirements...">{{ old('special_requests') }}</textarea>
                            @error('special_requests')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Details</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="check_in_display" class="block text-sm font-medium text-gray-700">Check-in Date</label>
                                <input type="text" id="check_in_display" value="{{ request('check_in') }}" readonly
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                            </div>

                            <div>
                                <label for="check_out_display" class="block text-sm font-medium text-gray-700">Check-out Date</label>
                                <input type="text" id="check_out_display" value="{{ request('check_out') }}" readonly
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                            </div>

                            <div>
                                <label for="arrival_time" class="block text-sm font-medium text-gray-700">Estimated Arrival Time</label>
                                <select id="arrival_time" name="arrival_time"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select arrival time</option>
                                    <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                                    <option value="afternoon">Afternoon (12:00 PM - 6:00 PM)</option>
                                    <option value="evening">Evening (6:00 PM - 10:00 PM)</option>
                                    <option value="late">Late (After 10:00 PM)</option>
                                </select>
                                @error('arrival_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose of Visit</label>
                                <select id="purpose" name="purpose"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select purpose</option>
                                    <option value="leisure">Leisure/Vacation</option>
                                    <option value="business">Business</option>
                                    <option value="family">Family Visit</option>
                                    <option value="event">Event/Wedding</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('purpose')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="radio" id="paypal" name="payment_method" value="paypal" checked
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="paypal" class="ml-3 flex items-center">
                                    <img src="/images/paypal-logo.png" alt="PayPal" class="h-6 mr-2">
                                    <span class="text-sm font-medium text-gray-700">PayPal</span>
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="radio" id="myfatoorah" name="payment_method" value="myfatoorah"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="myfatoorah" class="ml-3 flex items-center">
                                    <img src="/images/myfatoorah-logo.png" alt="MyFatoorah" class="h-6 mr-2">
                                    <span class="text-sm font-medium text-gray-700">MyFatoorah (Credit/Debit Cards)</span>
                                </label>
                            </div>

                            @if($property->instant_book)
                                <div class="flex items-center">
                                    <input type="radio" id="pay_at_property" name="payment_method" value="pay_at_property"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <label for="pay_at_property" class="ml-3">
                                        <span class="text-sm font-medium text-gray-700">Pay at Property</span>
                                        <p class="text-xs text-gray-500">Cash payment upon arrival</p>
                                    </label>
                                </div>
                            @endif
                        </div>

                        @error('payment_method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Terms and Conditions</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <input type="checkbox" id="agree_terms" name="agree_terms" value="1" required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                <label for="agree_terms" class="ml-3 text-sm text-gray-700">
                                    I agree to the <a href="{{ route('terms') }}" target="_blank" class="text-blue-600 hover:underline">Terms and Conditions</a> 
                                    and <a href="{{ route('privacy') }}" target="_blank" class="text-blue-600 hover:underline">Privacy Policy</a>
                                </label>
                            </div>

                            <div class="flex items-start">
                                <input type="checkbox" id="agree_cancellation" name="agree_cancellation" value="1" required
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                <label for="agree_cancellation" class="ml-3 text-sm text-gray-700">
                                    I understand the cancellation policy for this property
                                </label>
                            </div>

                            <div class="flex items-start">
                                <input type="checkbox" id="marketing_consent" name="marketing_consent" value="1"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                                <label for="marketing_consent" class="ml-3 text-sm text-gray-700">
                                    I would like to receive marketing emails about special offers and new properties
                                </label>
                            </div>
                        </div>

                        @error('agree_terms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('agree_cancellation')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <a href="{{ route('properties.show', $property) }}" 
                           class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                            Back to Property
                        </a>
                        
                        <button type="submit" id="submitBooking"
                                class="px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors font-semibold">
                            <span id="submitText">Complete Booking</span>
                            <i id="submitSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Booking Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Summary</h2>
                
                <!-- Property Info -->
                <div class="flex items-start space-x-4 mb-6">
                    <img src="{{ $property->main_image_url }}" alt="{{ $property->title }}" 
                         class="w-20 h-20 rounded-lg object-cover">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $property->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $property->city }}, {{ $property->country }}</p>
                        <div class="flex items-center mt-1">
                            <div class="flex text-yellow-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= ($property->average_rating ?? 5) ? '' : 'text-gray-300' }}"></i>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600 ml-1">
                                {{ number_format($property->average_rating ?? 5, 1) }} ({{ $property->reviews_count ?? 0 }} reviews)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Check-in:</span>
                        <span class="font-medium">{{ request('check_in') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Check-out:</span>
                        <span class="font-medium">{{ request('check_out') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Guests:</span>
                        <span class="font-medium">{{ request('guests', 1) }} {{ request('guests', 1) == 1 ? 'guest' : 'guests' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nights:</span>
                        <span class="font-medium" id="nightsCount">-</span>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="space-y-3 mb-6 border-t pt-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">SAR {{ number_format($property->price_per_night, 2) }} × <span id="nightsText">- nights</span></span>
                        <span class="font-medium" id="subtotal">SAR 0.00</span>
                    </div>
                    
                    @if($property->cleaning_fee > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cleaning fee</span>
                        <span class="font-medium">SAR {{ number_format($property->cleaning_fee, 2) }}</span>
                    </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Service fee</span>
                        <span class="font-medium" id="serviceFee">SAR 0.00</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxes</span>
                        <span class="font-medium" id="taxes">SAR 0.00</span>
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-lg font-bold text-gray-900" id="totalAmount">SAR 0.00</span>
                    </div>
                </div>

                <!-- Cancellation Policy -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-2">Cancellation Policy</h4>
                    <p class="text-sm text-gray-600">
                        {{ $property->cancellation_policy ?? 'Free cancellation up to 24 hours before check-in. After that, 50% refund up to 7 days before check-in.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    calculatePricing();
    
    // Form submission
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBooking');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');
        
        submitBtn.disabled = true;
        submitText.textContent = 'Processing...';
        submitSpinner.classList.remove('hidden');
    });
});

function calculatePricing() {
    const checkIn = new Date('{{ request("check_in") }}');
    const checkOut = new Date('{{ request("check_out") }}');
    const nights = Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24));
    
    const pricePerNight = {{ $property->price_per_night }};
    const cleaningFee = {{ $property->cleaning_fee ?? 0 }};
    
    const subtotal = nights * pricePerNight;
    const serviceFee = subtotal * 0.05; // 5% service fee
    const taxes = (subtotal + serviceFee + cleaningFee) * 0.15; // 15% VAT
    const total = subtotal + cleaningFee + serviceFee + taxes;
    
    document.getElementById('nightsCount').textContent = nights;
    document.getElementById('nightsText').textContent = nights + ' nights';
    document.getElementById('subtotal').textContent = 'SAR ' + subtotal.toFixed(2);
    document.getElementById('serviceFee').textContent = 'SAR ' + serviceFee.toFixed(2);
    document.getElementById('taxes').textContent = 'SAR ' + taxes.toFixed(2);
    document.getElementById('totalAmount').textContent = 'SAR ' + total.toFixed(2);
}
</script>
@endsection
