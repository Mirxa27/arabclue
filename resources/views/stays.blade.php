<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Perfect Stay | HabibiStay</title>
    <meta name="description" content="Search and discover amazing accommodations worldwide with advanced filters and AI assistance.">
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
        
        .search-header {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
        }
        
        .filter-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .property-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .property-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
        }
        
        .filter-btn {
            background: white;
            border: 2px solid var(--brand-blue);
            color: var(--brand-blue);
            border-radius: 25px;
            padding: 8px 16px;
            transition: all 0.3s ease;
            font-size: 14px;
            font-weight: 500;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: var(--brand-blue);
            color: white;
        }
        
        .price-range-slider {
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            border-radius: 3px;
            background: #e2e8f0;
            outline: none;
        }
        
        .price-range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--brand-blue);
            cursor: pointer;
        }
        
        .price-range-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--brand-blue);
            cursor: pointer;
            border: none;
        }
        
        @media (max-width: 768px) {
            .filters-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100vh;
                background: white;
                z-index: 50;
                transition: left 0.3s ease;
            }
            
            .filters-sidebar.open {
                left: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">HabibiStay</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                    <a href="/stays" class="text-blue-600 font-semibold">Stays</a>
                    <a href="/host" class="text-gray-700 hover:text-blue-600 transition-colors">Owners</a>
                    <a href="/invest" class="text-gray-700 hover:text-blue-600 transition-colors">Invest</a>
                    <a href="/about" class="text-gray-700 hover:text-blue-600 transition-colors">About</a>
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

    <!-- Search Header -->
    <section class="search-header pt-20 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-white text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold mb-4">Your Perfect Riyadh Stay.</h1>
                <p class="text-xl opacity-90">Curated spaces, 24/7 support, effortless booking. Discover your home away from home with HabibiStay.</p>
            </div>
            
            <!-- Search Bar -->
            <div class="bg-white rounded-2xl p-6 shadow-xl">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Where</label>
                        <input type="text" id="searchLocation" placeholder="Search destinations" 
                               value="{{ request('location') }}"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-in</label>
                        <input type="date" id="searchCheckin" 
                               value="{{ request('check_in') }}"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Check-out</label>
                        <input type="date" id="searchCheckout" 
                               value="{{ request('check_out') }}"
                               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Guests</label>
                        <select id="searchGuests" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="1" {{ request('guests') == '1' ? 'selected' : '' }}>1 guest</option>
                            <option value="2" {{ request('guests') == '2' ? 'selected' : '' }}>2 guests</option>
                            <option value="3" {{ request('guests') == '3' ? 'selected' : '' }}>3 guests</option>
                            <option value="4" {{ request('guests') == '4' ? 'selected' : '' }}>4 guests</option>
                            <option value="5" {{ request('guests') == '5' ? 'selected' : '' }}>5+ guests</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 mt-6">
                    <button onclick="searchProperties()" class="flex-1 bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    <button onclick="openSaraChat()" class="flex-1 bg-green-600 text-white py-3 px-6 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                        <i class="fas fa-robot mr-2"></i>Ask Sara AI
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Points Section -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Experience the HabibiStay Difference</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Premium Comfort & Hotel-Grade Amenities</h3>
                    <p class="text-gray-600">Step into beautifully designed spaces equipped with premium bedding, high-speed Wi-Fi, fully-equipped kitchens, and hotel-grade toiletries. Every detail is curated for your utmost comfort.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bolt text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Instant Booking & Flexible Cancellation</h3>
                    <p class="text-gray-600">Find and book your ideal stay in minutes with our secure, user-friendly platform. Enjoy peace of mind with flexible cancellation policies on many of our properties.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-concierge-bell text-white text-xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Local Concierge & Multilingual Team</h3>
                    <p class="text-gray-600">Our dedicated team is available 24/7 to assist you. From local recommendations to addressing any needs during your stay, we speak your language and are here to help.</p>
                </div>
            </div>
            
            <div class="text-center mt-8">
                <a href="/sara" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    Find a Stay →
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <div id="filtersSidebar" class="filters-sidebar lg:w-80 lg:flex-shrink-0">
                <div class="filter-card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                        <button id="closeFilters" class="lg:hidden text-gray-500">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Price Range</h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="range" id="minPrice" min="0" max="1000" value="0" 
                                       class="price-range-slider flex-1">
                                <span id="minPriceValue" class="text-sm text-gray-600 w-16">$0</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="range" id="maxPrice" min="0" max="1000" value="1000" 
                                       class="price-range-slider flex-1">
                                <span id="maxPriceValue" class="text-sm text-gray-600 w-16">$1000+</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property Type -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Property Type</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" value="apartment" class="property-type-filter mr-2">
                                <span class="text-sm text-gray-700">Apartment</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="house" class="property-type-filter mr-2">
                                <span class="text-sm text-gray-700">House</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="villa" class="property-type-filter mr-2">
                                <span class="text-sm text-gray-700">Villa</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="condo" class="property-type-filter mr-2">
                                <span class="text-sm text-gray-700">Condo</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Amenities -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 mb-3">Amenities</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" value="wifi" class="amenity-filter mr-2">
                                <span class="text-sm text-gray-700">WiFi</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="parking" class="amenity-filter mr-2">
                                <span class="text-sm text-gray-700">Free Parking</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="pool" class="amenity-filter mr-2">
                                <span class="text-sm text-gray-700">Pool</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="kitchen" class="amenity-filter mr-2">
                                <span class="text-sm text-gray-700">Kitchen</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" value="ac" class="amenity-filter mr-2">
                                <span class="text-sm text-gray-700">Air Conditioning</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Instant Booking -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="instantBooking" class="mr-2">
                            <span class="text-sm text-gray-700">Instant Booking</span>
                        </label>
                    </div>
                    
                    <!-- Apply Filters Button -->
                    <button onclick="applyFilters()" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Properties Grid -->
            <div class="flex-1">
                <!-- Results Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-xl font-semibold text-gray-900">
                            <span id="resultsCount">Loading...</span> stays found
                        </h2>
                        <button id="showFilters" class="lg:hidden bg-blue-600 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-filter mr-2"></i>Filters
                        </button>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label class="text-sm text-gray-700">Sort by:</label>
                        <select id="sortBy" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="relevance">Relevance</option>
                            <option value="price_low">Price: Low to High</option>
                            <option value="price_high">Price: High to Low</option>
                            <option value="rating">Highest Rated</option>
                            <option value="newest">Newest</option>
                        </select>
                    </div>
                </div>
                
                <!-- Properties Grid -->
                <div id="propertiesGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <!-- Properties will be loaded here -->
                </div>
                
                <!-- Load More Button -->
                <div class="text-center mt-8">
                    <button id="loadMoreBtn" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        Load More Properties
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        let currentPage = 1;
        let isLoading = false;
        let hasMorePages = true;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
            setupEventListeners();
            loadProperties();
        });

        function initializePage() {
            // Set default dates if not provided
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            if (!document.getElementById('searchCheckin').value) {
                document.getElementById('searchCheckin').value = today.toISOString().split('T')[0];
            }
            if (!document.getElementById('searchCheckout').value) {
                document.getElementById('searchCheckout').value = tomorrow.toISOString().split('T')[0];
            }
        }

        function setupEventListeners() {
            // Price range sliders
            document.getElementById('minPrice').addEventListener('input', updatePriceDisplay);
            document.getElementById('maxPrice').addEventListener('input', updatePriceDisplay);

            // Sort dropdown
            document.getElementById('sortBy').addEventListener('change', loadProperties);

            // Mobile filters
            document.getElementById('showFilters').addEventListener('click', openFilters);
            document.getElementById('closeFilters').addEventListener('click', closeFilters);

            // Load more button
            document.getElementById('loadMoreBtn').addEventListener('click', loadMoreProperties);
        }

        function updatePriceDisplay() {
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;

            document.getElementById('minPriceValue').textContent = `$${minPrice}`;
            document.getElementById('maxPriceValue').textContent = maxPrice >= 1000 ? '$1000+' : `$${maxPrice}`;
        }

        function openFilters() {
            document.getElementById('filtersSidebar').classList.add('open');
        }

        function closeFilters() {
            document.getElementById('filtersSidebar').classList.remove('open');
        }

        function searchProperties() {
            currentPage = 1;
            hasMorePages = true;
            loadProperties();
        }

        function applyFilters() {
            currentPage = 1;
            hasMorePages = true;
            loadProperties();
            closeFilters();
        }

        function loadProperties() {
            if (isLoading) return;

            isLoading = true;
            const loadingHtml = currentPage === 1 ?
                '<div class="col-span-full text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2 text-gray-600">Loading properties...</p></div>' :
                '';

            if (currentPage === 1) {
                document.getElementById('propertiesGrid').innerHTML = loadingHtml;
            }

            const searchParams = getSearchParams();

            fetch(`/api/v1/properties/search?${searchParams}`)
                .then(response => response.json())
                .then(data => {
                    if (currentPage === 1) {
                        document.getElementById('propertiesGrid').innerHTML = '';
                        document.getElementById('resultsCount').textContent = data.total || 0;
                    }

                    if (data.data && data.data.length > 0) {
                        data.data.forEach(property => {
                            const propertyCard = createPropertyCard(property);
                            document.getElementById('propertiesGrid').appendChild(propertyCard);
                        });

                        hasMorePages = data.current_page < data.last_page;
                        document.getElementById('loadMoreBtn').style.display = hasMorePages ? 'block' : 'none';
                    } else if (currentPage === 1) {
                        document.getElementById('propertiesGrid').innerHTML =
                            '<div class="col-span-full text-center py-12"><i class="fas fa-search text-4xl text-gray-400 mb-4"></i><h3 class="text-xl font-semibold text-gray-600 mb-2">No properties found</h3><p class="text-gray-500">Try adjusting your search criteria or filters</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading properties:', error);
                    if (currentPage === 1) {
                        document.getElementById('propertiesGrid').innerHTML =
                            '<div class="col-span-full text-center py-12"><i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i><h3 class="text-xl font-semibold text-gray-600 mb-2">Error loading properties</h3><p class="text-gray-500">Please try again later</p></div>';
                    }
                })
                .finally(() => {
                    isLoading = false;
                });
        }

        function loadMoreProperties() {
            if (hasMorePages && !isLoading) {
                currentPage++;
                loadProperties();
            }
        }

        function getSearchParams() {
            const params = new URLSearchParams();

            // Basic search parameters
            const location = document.getElementById('searchLocation').value;
            const checkin = document.getElementById('searchCheckin').value;
            const checkout = document.getElementById('searchCheckout').value;
            const guests = document.getElementById('searchGuests').value;

            if (location) params.append('location', location);
            if (checkin) params.append('check_in', checkin);
            if (checkout) params.append('check_out', checkout);
            if (guests) params.append('guests', guests);

            // Price range
            const minPrice = document.getElementById('minPrice').value;
            const maxPrice = document.getElementById('maxPrice').value;
            if (minPrice > 0) params.append('min_price', minPrice);
            if (maxPrice < 1000) params.append('max_price', maxPrice);

            // Property types
            const propertyTypes = Array.from(document.querySelectorAll('.property-type-filter:checked'))
                .map(cb => cb.value);
            if (propertyTypes.length > 0) {
                propertyTypes.forEach(type => params.append('property_type[]', type));
            }

            // Amenities
            const amenities = Array.from(document.querySelectorAll('.amenity-filter:checked'))
                .map(cb => cb.value);
            if (amenities.length > 0) {
                amenities.forEach(amenity => params.append('amenities[]', amenity));
            }

            // Instant booking
            if (document.getElementById('instantBooking').checked) {
                params.append('instant_booking', '1');
            }

            // Sorting
            const sortBy = document.getElementById('sortBy').value;
            if (sortBy !== 'relevance') {
                params.append('sort_by', sortBy);
            }

            // Pagination
            params.append('page', currentPage);
            params.append('per_page', '12');

            return params.toString();
        }

        function createPropertyCard(property) {
            const card = document.createElement('div');
            card.className = 'property-card cursor-pointer';
            card.onclick = () => window.location.href = `/properties/${property.id}`;

            const amenitiesHtml = property.amenities ?
                property.amenities.slice(0, 3).map(amenity =>
                    `<span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full mr-1 mb-1">${amenity.name}</span>`
                ).join('') : '';

            card.innerHTML = `
                <div class="relative">
                    <img src="${property.primary_image?.url || '/images/placeholder-property.jpg'}"
                         alt="${property.title}"
                         class="w-full h-48 object-cover">
                    <div class="absolute top-4 right-4 bg-white rounded-full px-3 py-1 text-sm font-semibold text-gray-800">
                        $${property.price_per_night}/night
                    </div>
                    ${property.instant_booking ? '<div class="absolute top-4 left-4 bg-green-500 text-white rounded-full px-3 py-1 text-xs font-semibold">Instant Book</div>' : ''}
                    <button onclick="event.stopPropagation(); toggleWishlist(${property.id})"
                            class="absolute top-4 right-16 w-8 h-8 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors">
                        <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                    </button>
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-2">${property.title}</h3>
                    <p class="text-gray-600 mb-2">${property.city}, ${property.country}</p>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-1">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="text-sm text-gray-600">${property.reviews_avg_rating || 'New'}</span>
                            <span class="text-sm text-gray-500">(${property.reviews_count || 0})</span>
                        </div>
                        <div class="flex items-center space-x-1 text-gray-500">
                            <i class="fas fa-users text-sm"></i>
                            <span class="text-sm">${property.accommodates} guests</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        ${amenitiesHtml}
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold text-gray-900">
                            $${property.price_per_night} <span class="text-sm font-normal text-gray-500">/ night</span>
                        </div>
                        <button onclick="event.stopPropagation(); bookProperty(${property.id})"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                            ${property.instant_booking ? 'Book Now' : 'Request'}
                        </button>
                    </div>
                </div>
            `;

            return card;
        }

        function toggleWishlist(propertyId) {
            // Implement wishlist functionality
            fetch(`/api/v1/properties/${propertyId}/wishlist`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update heart icon
                    const heartIcon = event.target.closest('button').querySelector('i');
                    if (data.data.added) {
                        heartIcon.className = 'fas fa-heart text-red-500';
                    } else {
                        heartIcon.className = 'far fa-heart text-gray-600';
                    }
                }
            })
            .catch(error => console.error('Error toggling wishlist:', error));
        }

        function bookProperty(propertyId) {
            const checkin = document.getElementById('searchCheckin').value;
            const checkout = document.getElementById('searchCheckout').value;
            const guests = document.getElementById('searchGuests').value;

            const params = new URLSearchParams({
                check_in: checkin,
                check_out: checkout,
                guests: guests
            });

            window.location.href = `/properties/${propertyId}/book?${params.toString()}`;
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }
    </script>
</body>
</html>
