<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->title ?? 'Property Details' }} | HabibiStay</title>
    <meta name="description" content="{{ $property->description ?? 'Discover this amazing property on HabibiStay' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #2957c3;
            --brand-blue-light: #4a6cf7;
            --brand-blue-dark: #1e3a8a;
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 8px;
            height: 400px;
            border-radius: 16px;
            overflow: hidden;
        }
        
        .main-image {
            grid-row: 1 / 3;
        }
        
        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .gallery-image:hover {
            transform: scale(1.05);
        }
        
        .booking-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            position: sticky;
            top: 100px;
        }
        
        .amenity-icon {
            width: 24px;
            height: 24px;
            color: var(--brand-blue);
        }
        
        .review-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .image-gallery {
                grid-template-columns: 1fr;
                grid-template-rows: 300px;
                height: 300px;
            }
            
            .main-image {
                grid-row: 1;
            }
            
            .secondary-images {
                display: none;
            }
            
            .booking-card {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                border-radius: 16px 16px 0 0;
                z-index: 40;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">HabibiStay</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                    <a href="/stays" class="text-gray-700 hover:text-blue-600 transition-colors">Stays</a>
                    <a href="/host" class="text-gray-700 hover:text-blue-600 transition-colors">Become a Host</a>
                    @auth
                        <a href="/profile" class="text-gray-700 hover:text-blue-600 transition-colors">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-600 transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-700 hover:text-blue-600 transition-colors">Login</a>
                        <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Sign Up</a>
                    @endauth
                </div>
                <div class="md:hidden">
                    <button onclick="window.history.back()" class="text-gray-700">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 pb-20 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <ol class="flex items-center space-x-2 text-sm text-gray-500">
                    <li><a href="/" class="hover:text-blue-600">Home</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li><a href="/stays" class="hover:text-blue-600">Stays</a></li>
                    <li><i class="fas fa-chevron-right text-xs"></i></li>
                    <li class="text-gray-900">{{ $property->city ?? 'Property' }}</li>
                </ol>
            </nav>

            <!-- Property Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ $property->title ?? 'Beautiful Property' }}</h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                        <span class="font-semibold">{{ $property->reviews_avg_rating ?? '4.8' }}</span>
                        <span class="ml-1">({{ $property->reviews_count ?? '24' }} reviews)</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span>{{ $property->city ?? 'Dubai' }}, {{ $property->country ?? 'UAE' }}</span>
                    </div>
                    @if($property->instant_booking ?? true)
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-bolt mr-1"></i>
                            <span class="font-semibold">Instant Book</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Image Gallery -->
            <div class="image-gallery mb-8">
                <div class="main-image">
                    <img src="{{ $property->primary_image->url ?? '/images/placeholder-property.jpg' }}" 
                         alt="{{ $property->title ?? 'Property' }}" 
                         class="gallery-image">
                </div>
                <div class="secondary-images">
                    @for($i = 1; $i <= 4; $i++)
                        <img src="{{ $property->images[$i]->url ?? '/images/placeholder-property.jpg' }}" 
                             alt="Property image {{ $i }}" 
                             class="gallery-image">
                    @endfor
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Property Details -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Basic Info -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">About this place</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center">
                                <i class="fas fa-users text-2xl text-blue-600 mb-2"></i>
                                <div class="text-sm text-gray-600">Guests</div>
                                <div class="font-semibold">{{ $property->accommodates ?? '4' }}</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-bed text-2xl text-blue-600 mb-2"></i>
                                <div class="text-sm text-gray-600">Bedrooms</div>
                                <div class="font-semibold">{{ $property->bedrooms ?? '2' }}</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-bath text-2xl text-blue-600 mb-2"></i>
                                <div class="text-sm text-gray-600">Bathrooms</div>
                                <div class="font-semibold">{{ $property->bathrooms ?? '2' }}</div>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-home text-2xl text-blue-600 mb-2"></i>
                                <div class="text-sm text-gray-600">Type</div>
                                <div class="font-semibold">{{ ucfirst($property->property_type ?? 'Apartment') }}</div>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            {{ $property->description ?? 'Experience luxury and comfort in this beautifully designed space. Perfect for travelers seeking a memorable stay with modern amenities and exceptional service.' }}
                        </p>
                    </div>

                    <!-- Amenities -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">What this place offers</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $defaultAmenities = [
                                    ['name' => 'WiFi', 'icon' => 'fas fa-wifi'],
                                    ['name' => 'Kitchen', 'icon' => 'fas fa-utensils'],
                                    ['name' => 'Free parking', 'icon' => 'fas fa-parking'],
                                    ['name' => 'Air conditioning', 'icon' => 'fas fa-snowflake'],
                                    ['name' => 'Pool', 'icon' => 'fas fa-swimming-pool'],
                                    ['name' => 'Gym', 'icon' => 'fas fa-dumbbell'],
                                ];
                                $amenities = $property->amenities ?? collect($defaultAmenities);
                            @endphp
                            @foreach($amenities as $amenity)
                                <div class="flex items-center space-x-3">
                                    <i class="{{ $amenity['icon'] ?? 'fas fa-check' }} amenity-icon"></i>
                                    <span class="text-gray-700">{{ $amenity['name'] ?? $amenity }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Host Info -->
                    <div class="border-t pt-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Meet your host</h2>
                        <div class="flex items-start space-x-4">
                            <img src="{{ $property->host->avatar ?? '/images/default-avatar.jpg' }}" 
                                 alt="{{ $property->host->name ?? 'Host' }}" 
                                 class="w-16 h-16 rounded-full object-cover">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $property->host->name ?? 'Ahmed Al-Rashid' }}</h3>
                                <p class="text-sm text-gray-600 mb-2">Host since {{ $property->host->created_at->format('Y') ?? '2020' }}</p>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span><i class="fas fa-star text-yellow-400 mr-1"></i>{{ $property->host->rating ?? '4.9' }} rating</span>
                                    <span><i class="fas fa-comment mr-1"></i>{{ $property->host->reviews_count ?? '156' }} reviews</span>
                                </div>
                                <button class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                                    Contact Host
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div class="border-t pt-8">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">
                            <i class="fas fa-star text-yellow-400 mr-2"></i>
                            {{ $property->reviews_avg_rating ?? '4.8' }} · {{ $property->reviews_count ?? '24' }} reviews
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @for($i = 0; $i < 4; $i++)
                                <div class="review-card">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <img src="/images/avatar-{{ $i + 1 }}.jpg" 
                                             alt="Reviewer" 
                                             class="w-10 h-10 rounded-full object-cover">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">Guest {{ $i + 1 }}</h4>
                                            <div class="flex items-center">
                                                @for($j = 0; $j < 5; $j++)
                                                    <i class="fas fa-star text-yellow-400 text-sm"></i>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-600">2 weeks ago</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-gray-700 text-sm">
                                        Amazing stay! The property was exactly as described and the host was very responsive. Highly recommended!
                                    </p>
                                </div>
                            @endfor
                        </div>
                        <button class="mt-6 border border-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                            Show all {{ $property->reviews_count ?? '24' }} reviews
                        </button>
                    </div>
                </div>

                <!-- Booking Card -->
                <div class="lg:col-span-1">
                    <div class="booking-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-2xl font-bold text-gray-900">${{ $property->price_per_night ?? '120' }}</span>
                                <span class="text-gray-600"> / night</span>
                            </div>
                            <div class="flex items-center text-sm">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="font-semibold">{{ $property->reviews_avg_rating ?? '4.8' }}</span>
                                <span class="text-gray-600 ml-1">({{ $property->reviews_count ?? '24' }})</span>
                            </div>
                        </div>

                        <form id="bookingForm" class="space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in</label>
                                    <input type="date" id="checkinDate" 
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out</label>
                                    <input type="date" id="checkoutDate" 
                                           class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Guests</label>
                                <select id="guestCount" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="1">1 guest</option>
                                    <option value="2">2 guests</option>
                                    <option value="3">3 guests</option>
                                    <option value="4">4 guests</option>
                                    <option value="5">5+ guests</option>
                                </select>
                            </div>

                            <!-- Price Breakdown -->
                            <div id="priceBreakdown" class="border-t pt-4 space-y-2 hidden">
                                <div class="flex justify-between text-sm">
                                    <span>${{ $property->price_per_night ?? '120' }} × <span id="nightCount">0</span> nights</span>
                                    <span id="subtotal">$0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Service fee</span>
                                    <span id="serviceFee">$0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Taxes</span>
                                    <span id="taxes">$0</span>
                                </div>
                                <div class="border-t pt-2 flex justify-between font-semibold">
                                    <span>Total</span>
                                    <span id="totalPrice">$0</span>
                                </div>
                            </div>

                            @if($property->instant_booking ?? true)
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                    Reserve
                                </button>
                                <p class="text-center text-sm text-gray-600">You won't be charged yet</p>
                            @else
                                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                    Request to Book
                                </button>
                            @endif
                        </form>

                        <div class="mt-4 text-center">
                            <button onclick="openSaraChat()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-robot mr-1"></i>Ask Sara for help
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeDates();
            setupEventListeners();
        });

        function initializeDates() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            document.getElementById('checkinDate').value = today.toISOString().split('T')[0];
            document.getElementById('checkoutDate').value = tomorrow.toISOString().split('T')[0];
            
            calculatePrice();
        }

        function setupEventListeners() {
            document.getElementById('checkinDate').addEventListener('change', calculatePrice);
            document.getElementById('checkoutDate').addEventListener('change', calculatePrice);
            document.getElementById('guestCount').addEventListener('change', calculatePrice);
            document.getElementById('bookingForm').addEventListener('submit', handleBooking);
        }

        function calculatePrice() {
            const checkin = new Date(document.getElementById('checkinDate').value);
            const checkout = new Date(document.getElementById('checkoutDate').value);
            
            if (checkin && checkout && checkout > checkin) {
                const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
                const pricePerNight = {{ $property->price_per_night ?? 120 }};
                const subtotal = nights * pricePerNight;
                const serviceFee = Math.round(subtotal * 0.1);
                const taxes = Math.round(subtotal * 0.05);
                const total = subtotal + serviceFee + taxes;
                
                document.getElementById('nightCount').textContent = nights;
                document.getElementById('subtotal').textContent = `$${subtotal}`;
                document.getElementById('serviceFee').textContent = `$${serviceFee}`;
                document.getElementById('taxes').textContent = `$${taxes}`;
                document.getElementById('totalPrice').textContent = `$${total}`;
                document.getElementById('priceBreakdown').classList.remove('hidden');
            } else {
                document.getElementById('priceBreakdown').classList.add('hidden');
            }
        }

        function handleBooking(event) {
            event.preventDefault();
            
            const checkin = document.getElementById('checkinDate').value;
            const checkout = document.getElementById('checkoutDate').value;
            const guests = document.getElementById('guestCount').value;
            
            if (!checkin || !checkout) {
                alert('Please select check-in and check-out dates');
                return;
            }
            
            const params = new URLSearchParams({
                check_in: checkin,
                check_out: checkout,
                guests: guests
            });
            
            window.location.href = `/properties/{{ $property->id ?? '1' }}/book?${params.toString()}`;
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }
    </script>
</body>
</html>
