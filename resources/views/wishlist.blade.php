<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | HabibiStay</title>
    <meta name="description" content="Your saved properties and favorite stays on HabibiStay">
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
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .wishlist-heart {
            color: #ef4444;
            transition: all 0.3s ease;
        }
        
        .wishlist-heart:hover {
            transform: scale(1.1);
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
                    <a href="/wishlist" class="text-blue-600 font-semibold">Wishlist</a>
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
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">My Wishlist</h1>
                <p class="text-xl text-gray-600">Your saved properties and favorite stays</p>
            </div>

            <!-- Wishlist Content -->
            <div id="wishlistContent">
                <!-- Loading state -->
                <div id="loadingState" class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mb-4"></i>
                    <p class="text-gray-600">Loading your wishlist...</p>
                </div>

                <!-- Empty state -->
                <div id="emptyState" class="empty-state hidden">
                    <div class="max-w-md mx-auto">
                        <i class="fas fa-heart text-6xl text-gray-300 mb-6"></i>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Your wishlist is empty</h2>
                        <p class="text-gray-600 mb-8">Start exploring and save your favorite properties to see them here.</p>
                        <div class="space-y-4">
                            <a href="/stays" class="block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                Explore Properties
                            </a>
                            <button onclick="openSaraChat()" class="block w-full bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                <i class="fas fa-robot mr-2"></i>Ask Sara for Recommendations
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Wishlist Grid -->
                <div id="wishlistGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 hidden">
                    <!-- Wishlist items will be loaded here -->
                </div>
            </div>

            <!-- Wishlist Actions -->
            <div id="wishlistActions" class="mt-8 hidden">
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button onclick="shareWishlist()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-share mr-2"></i>Share Wishlist
                    </button>
                    <button onclick="clearWishlist()" class="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 transition-colors font-semibold">
                        <i class="fas fa-trash mr-2"></i>Clear All
                    </button>
                    <button onclick="exportWishlist()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors font-semibold">
                        <i class="fas fa-download mr-2"></i>Export List
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        let wishlistItems = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadWishlist();
        });

        function loadWishlist() {
            // Check if user is authenticated
            @auth
                fetch('/api/v1/wishlist')
                    .then(response => response.json())
                    .then(data => {
                        wishlistItems = data.data || [];
                        displayWishlist();
                    })
                    .catch(error => {
                        console.error('Error loading wishlist:', error);
                        showEmptyState();
                    });
            @else
                // Load from localStorage for guest users
                const savedWishlist = localStorage.getItem('guestWishlist');
                if (savedWishlist) {
                    const propertyIds = JSON.parse(savedWishlist);
                    if (propertyIds.length > 0) {
                        loadPropertiesFromIds(propertyIds);
                    } else {
                        showEmptyState();
                    }
                } else {
                    showEmptyState();
                }
            @endauth
        }

        function loadPropertiesFromIds(propertyIds) {
            const promises = propertyIds.map(id => 
                fetch(`/api/v1/properties/${id}`)
                    .then(response => response.json())
                    .catch(() => null)
            );

            Promise.all(promises)
                .then(results => {
                    wishlistItems = results.filter(item => item !== null);
                    displayWishlist();
                })
                .catch(error => {
                    console.error('Error loading properties:', error);
                    showEmptyState();
                });
        }

        function displayWishlist() {
            document.getElementById('loadingState').classList.add('hidden');
            
            if (wishlistItems.length === 0) {
                showEmptyState();
                return;
            }

            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('wishlistGrid').classList.remove('hidden');
            document.getElementById('wishlistActions').classList.remove('hidden');

            const grid = document.getElementById('wishlistGrid');
            grid.innerHTML = '';

            wishlistItems.forEach(property => {
                const propertyCard = createWishlistCard(property);
                grid.appendChild(propertyCard);
            });
        }

        function showEmptyState() {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('wishlistGrid').classList.add('hidden');
            document.getElementById('wishlistActions').classList.add('hidden');
            document.getElementById('emptyState').classList.remove('hidden');
        }

        function createWishlistCard(property) {
            const card = document.createElement('div');
            card.className = 'property-card cursor-pointer';
            
            card.innerHTML = `
                <div class="relative">
                    <img src="${property.primary_image?.url || '/images/placeholder-property.jpg'}" 
                         alt="${property.title}" 
                         class="w-full h-48 object-cover"
                         onclick="viewProperty(${property.id})">
                    <div class="absolute top-4 right-4 bg-white rounded-full px-3 py-1 text-sm font-semibold text-gray-800">
                        $${property.price_per_night}/night
                    </div>
                    <button onclick="removeFromWishlist(${property.id})" 
                            class="absolute top-4 left-4 w-8 h-8 bg-white rounded-full flex items-center justify-center hover:bg-gray-100 transition-colors">
                        <i class="fas fa-heart wishlist-heart"></i>
                    </button>
                    ${property.instant_booking ? '<div class="absolute bottom-4 left-4 bg-green-500 text-white rounded-full px-3 py-1 text-xs font-semibold">Instant Book</div>' : ''}
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-lg mb-2 line-clamp-2" onclick="viewProperty(${property.id})">${property.title}</h3>
                    <p class="text-gray-600 mb-3">${property.city}, ${property.country}</p>
                    <div class="flex items-center justify-between mb-4">
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
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold text-gray-900">
                            $${property.price_per_night} <span class="text-sm font-normal text-gray-500">/ night</span>
                        </div>
                        <button onclick="bookProperty(${property.id})" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                            ${property.instant_booking ? 'Book Now' : 'Request'}
                        </button>
                    </div>
                </div>
            `;
            
            return card;
        }

        function removeFromWishlist(propertyId) {
            @auth
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
                        wishlistItems = wishlistItems.filter(item => item.id !== propertyId);
                        displayWishlist();
                    }
                })
                .catch(error => console.error('Error removing from wishlist:', error));
            @else
                // Remove from localStorage for guest users
                const savedWishlist = localStorage.getItem('guestWishlist');
                if (savedWishlist) {
                    let propertyIds = JSON.parse(savedWishlist);
                    propertyIds = propertyIds.filter(id => id !== propertyId);
                    localStorage.setItem('guestWishlist', JSON.stringify(propertyIds));
                    wishlistItems = wishlistItems.filter(item => item.id !== propertyId);
                    displayWishlist();
                }
            @endauth
        }

        function viewProperty(propertyId) {
            window.location.href = `/properties/${propertyId}`;
        }

        function bookProperty(propertyId) {
            window.location.href = `/properties/${propertyId}/book`;
        }

        function shareWishlist() {
            if (navigator.share) {
                navigator.share({
                    title: 'My HabibiStay Wishlist',
                    text: 'Check out my favorite properties on HabibiStay!',
                    url: window.location.href
                });
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Wishlist link copied to clipboard!');
                });
            }
        }

        function clearWishlist() {
            if (confirm('Are you sure you want to clear your entire wishlist? This action cannot be undone.')) {
                @auth
                    fetch('/api/v1/wishlist/clear', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            wishlistItems = [];
                            displayWishlist();
                        }
                    })
                    .catch(error => console.error('Error clearing wishlist:', error));
                @else
                    localStorage.removeItem('guestWishlist');
                    wishlistItems = [];
                    displayWishlist();
                @endauth
            }
        }

        function exportWishlist() {
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Title,City,Country,Price per Night,Rating,Reviews\n"
                + wishlistItems.map(item => 
                    `"${item.title}","${item.city}","${item.country}",${item.price_per_night},${item.reviews_avg_rating || 'N/A'},${item.reviews_count || 0}`
                ).join("\n");

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "my_wishlist.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }
    </script>
</body>
</html>
