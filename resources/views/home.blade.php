<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HabibiStay - Your Perfect Home Away From Home</title>
    <meta name="description" content="Discover unique accommodations worldwide with HabibiStay. Book your perfect stay with our AI assistant Sara.">
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
        
        .hero-section {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
            min-height: 70vh;
        }
        
        .search-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .property-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .sara-chat-btn {
            position: fixed;
            bottom: 100px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: var(--brand-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 4px 20px rgba(41, 87, 195, 0.4);
            transition: all 0.3s ease;
            z-index: 40;
        }
        
        .sara-chat-btn:hover {
            transform: scale(1.1);
            background: var(--brand-blue-dark);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--brand-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 16px;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-4px);
        }
        
        @media (max-width: 768px) {
            .hero-section {
                min-height: 60vh;
            }
            
            .sara-chat-btn {
                bottom: 80px;
                right: 16px;
                width: 56px;
                height: 56px;
                font-size: 20px;
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
                    <a href="/stays" class="text-gray-700 hover:text-blue-600 transition-colors">Stays</a>
                    <a href="/host" class="text-gray-700 hover:text-blue-600 transition-colors">Owners</a>
                    <a href="/invest" class="text-gray-700 hover:text-blue-600 transition-colors">Invest</a>
                    <a href="/about" class="text-gray-700 hover:text-blue-600 transition-colors">About</a>
                    <a href="/stories" class="text-gray-700 hover:text-blue-600 transition-colors">Stories</a>
                    <a href="/blog" class="text-gray-700 hover:text-blue-600 transition-colors">Blog</a>
                    <a href="/contact" class="text-gray-700 hover:text-blue-600 transition-colors">Contact</a>
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
                    <button id="mobileMenuBtn" class="text-gray-700">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section flex items-center justify-center pt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-white mb-12">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Exceptional Stays.
                    <span class="block">Exceptional Returns.</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">
                    Book memorable getaways, unlock steady income, and grow your capital—all with HabibiStay.
                </p>
            </div>
            
            <!-- Search Card -->
            <div class="search-card p-8 max-w-4xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Where</label>
                        <input type="text" id="location" placeholder="Search destinations" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-in</label>
                        <input type="date" id="checkin" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-out</label>
                        <input type="date" id="checkout" 
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Guests</label>
                        <select id="guests" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="1">1 guest</option>
                            <option value="2">2 guests</option>
                            <option value="3">3 guests</option>
                            <option value="4">4 guests</option>
                            <option value="5">5+ guests</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4">
                    <button onclick="searchProperties()" class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-search mr-2"></i>Book a Stay
                    </button>
                    <button onclick="window.location.href='/host'" class="flex-1 bg-gray-600 text-white py-3 px-6 rounded-lg hover:bg-gray-700 transition-colors font-semibold border border-gray-400">
                        <i class="fas fa-key mr-2"></i>List Property
                    </button>
                    <button onclick="window.location.href='/invest'" class="flex-1 bg-gray-600 text-white py-3 px-6 rounded-lg hover:bg-gray-700 transition-colors font-semibold border border-gray-400">
                        <i class="fas fa-chart-line mr-2"></i>Invest Now
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Properties -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Discover Our Featured Properties</h2>
                <p class="text-xl text-gray-600">Handpicked accommodations for your perfect stay</p>
            </div>
            
            <div id="featuredProperties" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Featured properties will be loaded here -->
            </div>
            
            <div class="text-center mt-12">
                <a href="/stays" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    See All Stays →
                </a>
            </div>
        </div>
    </section>

    <!-- Why HabibiStay Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why HabibiStay</h2>
                <p class="text-xl text-gray-600">Discover the HabibiStay difference, tailored for everyone:</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-suitcase"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Five‑star comfort, local flair.</h3>
                    <p class="text-gray-600">Experience Riyadh like never before. Our stays blend five-star comfort with authentic local flair, ensuring every trip is memorable.</p>
                </div>
                
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Hands‑off income, expert care.</h3>
                    <p class="text-gray-600">Maximize your rental income effortlessly. We provide comprehensive property management, from guest booking to maintenance, so you enjoy peace of mind.</p>
                </div>
                
                <div class="text-center">
                    <div class="feature-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Reliable, inflation‑hedged returns.</h3>
                    <p class="text-gray-600">Tap into Riyadh's thriving real estate market. We offer secure investment opportunities designed for robust, inflation-hedged returns.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonial Teaser Section -->
    <section class="py-16 bg-blue-600 relative">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-blue-700 opacity-90"></div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <blockquote class="text-white">
                <p class="text-2xl md:text-3xl font-light italic mb-6">
                    "HabibiStay doubled my rental income in six months. Their professionalism and market knowledge are unmatched."
                </p>
                <footer class="text-xl font-semibold">
                    — Ahmed, Riyadh
                </footer>
            </blockquote>
            <div class="mt-8">
                <a href="/stories" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                    More Stories →
                </a>
            </div>
        </div>
    </section>

    <!-- Sara Chat Button -->
    <a href="/sara" class="sara-chat-btn" title="Chat with Sara AI">
        <i class="fas fa-robot"></i>
    </a>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        // Load featured properties on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFeaturedProperties();
            setDefaultDates();
        });

        function setDefaultDates() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            document.getElementById('checkin').value = today.toISOString().split('T')[0];
            document.getElementById('checkout').value = tomorrow.toISOString().split('T')[0];
        }

        function loadFeaturedProperties() {
            fetch('/api/v1/properties/featured')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('featuredProperties');
                    container.innerHTML = '';
                    
                    data.data.forEach(property => {
                        const propertyCard = createPropertyCard(property);
                        container.appendChild(propertyCard);
                    });
                })
                .catch(error => {
                    console.error('Error loading featured properties:', error);
                    document.getElementById('featuredProperties').innerHTML = 
                        '<p class="text-center text-gray-500 col-span-full">Unable to load properties at the moment.</p>';
                });
        }

        function createPropertyCard(property) {
            const card = document.createElement('div');
            card.className = 'property-card cursor-pointer';
            card.onclick = () => window.location.href = `/properties/${property.id}`;
            
            card.innerHTML = `
                <div class="relative">
                    <img src="${property.primary_image?.url || '/images/placeholder-property.jpg'}" 
                         alt="${property.title}" 
                         class="w-full h-48 object-cover">
                    <div class="absolute top-4 right-4 bg-white rounded-full px-3 py-1 text-sm font-semibold text-gray-800">
                        $${property.price_per_night}/night
                    </div>
                    ${property.instant_booking ? '<div class="absolute top-4 left-4 bg-green-500 text-white rounded-full px-3 py-1 text-xs font-semibold">Instant Book</div>' : ''}
                </div>
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 text-lg mb-2">${property.title}</h3>
                    <p class="text-gray-600 mb-3">${property.city}, ${property.country}</p>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="text-sm text-gray-600">${property.reviews_avg_rating || 'New'}</span>
                            <span class="text-sm text-gray-500">(${property.reviews_count || 0} reviews)</span>
                        </div>
                        <div class="flex items-center space-x-1 text-gray-500">
                            <i class="fas fa-users text-sm"></i>
                            <span class="text-sm">${property.accommodates} guests</span>
                        </div>
                    </div>
                </div>
            `;
            
            return card;
        }

        function searchProperties() {
            const location = document.getElementById('location').value;
            const checkin = document.getElementById('checkin').value;
            const checkout = document.getElementById('checkout').value;
            const guests = document.getElementById('guests').value;
            
            const params = new URLSearchParams({
                location: location,
                check_in: checkin,
                check_out: checkout,
                guests: guests
            });
            
            window.location.href = `/search?${params.toString()}`;
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }

        // Mobile menu toggle
        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            // Add mobile menu functionality here
            alert('Mobile menu - to be implemented');
        });
    </script>
</body>
</html>
