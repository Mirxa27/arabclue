<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | HabibiStay</title>
    <meta name="description" content="Manage your HabibiStay profile, bookings, and preferences">
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
        
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border-radius: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .menu-item:hover {
            background: #f8faff;
            transform: translateX(4px);
        }
        
        .avatar-upload {
            position: relative;
            display: inline-block;
        }
        
        .avatar-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .avatar-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--brand-blue);
            color: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
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
                    <a href="/messages" class="text-gray-700 hover:text-blue-600 transition-colors">Messages</a>
                    <a href="/profile" class="text-blue-600 font-semibold">Profile</a>
                    @auth
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
            @auth
                <!-- Profile Header -->
                <div class="profile-card p-8 mb-8">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                        <!-- Avatar -->
                        <div class="avatar-upload">
                            <img src="{{ auth()->user()->avatar ?? '/images/default-avatar.jpg' }}" 
                                 alt="{{ auth()->user()->name }}" 
                                 class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg">
                            <input type="file" id="avatarUpload" accept="image/*" onchange="uploadAvatar(this)">
                            <div class="avatar-overlay">
                                <i class="fas fa-camera text-sm"></i>
                            </div>
                        </div>
                        
                        <!-- Profile Info -->
                        <div class="flex-1 text-center md:text-left">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ auth()->user()->name }}</h1>
                            <p class="text-gray-600 mb-4">{{ auth()->user()->email }}</p>
                            <div class="flex flex-wrap justify-center md:justify-start gap-4 mb-6">
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span>Joined {{ auth()->user()->created_at->format('M Y') }}</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                    <span>{{ auth()->user()->city ?? 'Location not set' }}</span>
                                </div>
                                @if(auth()->user()->phone)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-phone mr-2"></i>
                                        <span>{{ auth()->user()->phone }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap justify-center md:justify-start gap-3">
                                <button onclick="editProfile()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-edit mr-2"></i>Edit Profile
                                </button>
                                <button onclick="openSaraChat()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-robot mr-2"></i>Ask Sara
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card">
                        <div class="text-2xl font-bold text-blue-600 mb-2">{{ auth()->user()->bookings()->count() }}</div>
                        <div class="text-sm text-gray-600">Total Trips</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-2xl font-bold text-green-600 mb-2">{{ auth()->user()->reviews()->count() }}</div>
                        <div class="text-sm text-gray-600">Reviews</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-2xl font-bold text-purple-600 mb-2">{{ auth()->user()->wishlist()->count() }}</div>
                        <div class="text-sm text-gray-600">Wishlist</div>
                    </div>
                    <div class="stat-card">
                        <div class="text-2xl font-bold text-orange-600 mb-2">{{ auth()->user()->referrals()->count() }}</div>
                        <div class="text-sm text-gray-600">Referrals</div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account & Bookings -->
                    <div class="profile-card p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Account & Bookings</h2>
                        <div class="space-y-2">
                            <div class="menu-item" onclick="window.location.href='/bookings'">
                                <i class="fas fa-calendar-check text-blue-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">My Trips</div>
                                    <div class="text-sm text-gray-600">View and manage your bookings</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="window.location.href='/wishlist'">
                                <i class="fas fa-heart text-red-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Wishlist</div>
                                    <div class="text-sm text-gray-600">Your saved properties</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="window.location.href='/reviews'">
                                <i class="fas fa-star text-yellow-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Reviews</div>
                                    <div class="text-sm text-gray-600">Reviews you've written</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="window.location.href='/referrals'">
                                <i class="fas fa-users text-purple-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Referrals</div>
                                    <div class="text-sm text-gray-600">Invite friends and earn credits</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Settings & Support -->
                    <div class="profile-card p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Settings & Support</h2>
                        <div class="space-y-2">
                            <div class="menu-item" onclick="window.location.href='/settings'">
                                <i class="fas fa-cog text-gray-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Account Settings</div>
                                    <div class="text-sm text-gray-600">Privacy, notifications, and more</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="window.location.href='/payment-methods'">
                                <i class="fas fa-credit-card text-green-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Payment Methods</div>
                                    <div class="text-sm text-gray-600">Manage your payment options</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="openSaraChat()">
                                <i class="fas fa-robot text-blue-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Chat with Sara</div>
                                    <div class="text-sm text-gray-600">Get help from our AI assistant</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="window.location.href='/help'">
                                <i class="fas fa-question-circle text-orange-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Help Center</div>
                                    <div class="text-sm text-gray-600">FAQs and support articles</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Host Section (if applicable) -->
                @if(auth()->user()->isHost ?? false)
                    <div class="profile-card p-6 mt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-6">Host Dashboard</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="menu-item" onclick="window.location.href='/host/properties'">
                                <i class="fas fa-home text-blue-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">My Properties</div>
                                    <div class="text-sm text-gray-600">Manage your listings</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                            <div class="menu-item" onclick="window.location.href='/host/bookings'">
                                <i class="fas fa-calendar text-green-600 w-6 mr-4"></i>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Reservations</div>
                                    <div class="text-sm text-gray-600">View guest bookings</div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Become a Host CTA -->
                    <div class="brand-gradient rounded-2xl p-8 mt-6 text-white text-center">
                        <h2 class="text-2xl font-bold mb-4">Become a Host</h2>
                        <p class="text-blue-100 mb-6">Share your space and earn extra income with HabibiStay</p>
                        <button onclick="window.location.href='/host/onboarding'" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-semibold">
                            Get Started
                        </button>
                    </div>
                @endif
            @else
                <!-- Guest State -->
                <div class="text-center py-16">
                    <i class="fas fa-user-circle text-6xl text-gray-300 mb-6"></i>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome to HabibiStay</h1>
                    <p class="text-xl text-gray-600 mb-8">Sign in to access your profile and bookings</p>
                    <div class="space-y-4">
                        <a href="/login" class="block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            Sign In
                        </a>
                        <a href="/register" class="block bg-gray-600 text-white px-8 py-3 rounded-lg hover:bg-gray-700 transition-colors font-semibold">
                            Create Account
                        </a>
                    </div>
                </div>
            @endauth
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        function editProfile() {
            // This would open a modal or navigate to edit page
            alert('Edit profile feature coming soon!');
        }

        function uploadAvatar(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('avatar', input.files[0]);

                fetch('/api/v1/profile/avatar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Failed to upload avatar');
                    }
                })
                .catch(error => {
                    console.error('Error uploading avatar:', error);
                    alert('Failed to upload avatar');
                });
            }
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }
    </script>
</body>
</html>
