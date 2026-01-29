@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-4 md:space-y-0 md:space-x-6">
                <!-- Avatar -->
                <div class="relative">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" 
                             alt="{{ $user->name }}" 
                             class="w-24 h-24 rounded-full object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif
                    <button onclick="document.getElementById('avatar-upload').click()" 
                            class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </button>
                    <form id="avatar-form" action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" class="hidden">
                        @csrf
                        <input type="file" id="avatar-upload" name="avatar" accept="image/*" onchange="document.getElementById('avatar-form').submit()">
                    </form>
                </div>

                <!-- User Info -->
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-600">{{ $user->email }}</p>
                    @if($user->bio)
                        <p class="text-gray-700 mt-2">{{ $user->bio }}</p>
                    @endif
                    <div class="flex items-center mt-3 space-x-4">
                        <span class="text-sm text-gray-500">Member since {{ $stats['member_since'] }}</span>
                        @if($user->identity_verified)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Verified
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-2">
                    <a href="#edit-profile" onclick="showEditForm()" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Edit Profile
                    </a>
                    @if(!$user->identity_verified)
                        <a href="{{ route('profile.verify-identity') }}" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Verify Identity
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_bookings'] }}</div>
                <div class="text-sm text-gray-600">Total Bookings</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-green-600">{{ $stats['completed_trips'] }}</div>
                <div class="text-sm text-gray-600">Completed Trips</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['wishlist_count'] }}</div>
                <div class="text-sm text-gray-600">Saved Properties</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['reviews_given'] }}</div>
                <div class="text-sm text-gray-600">Reviews Given</div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-lg shadow-sm mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <a href="#" onclick="showTab('overview')" 
                       class="tab-link active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Overview
                    </a>
                    <a href="#" onclick="showTab('bookings')" 
                       class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        My Bookings
                    </a>
                    <a href="#" onclick="showTab('wishlist')" 
                       class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Wishlist
                    </a>
                    <a href="#" onclick="showTab('settings')" 
                       class="tab-link border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Settings
                    </a>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Overview Tab -->
                <div id="overview-tab" class="tab-content">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                    
                    <!-- Recent Bookings -->
                    @if($user->bookings->count() > 0)
                        <div class="space-y-4">
                            @foreach($user->bookings->take(3) as $booking)
                                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                                    @if($booking->property->images->count() > 0)
                                        <img src="{{ Storage::url($booking->property->images->first()->image_path) }}" 
                                             alt="{{ $booking->property->title }}"
                                             class="w-16 h-16 rounded-lg object-cover">
                                    @else
                                        <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $booking->property->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d, Y') }}</p>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-medium text-gray-900">{{ $booking->currency }} {{ number_format($booking->total_amount) }}</p>
                                        <a href="{{ route('bookings.show', $booking) }}" class="text-sm text-blue-600 hover:underline">View Details</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:underline">View all bookings →</a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No bookings yet. Start exploring amazing properties!</p>
                            <a href="{{ route('properties.index') }}" class="mt-2 inline-block text-blue-600 hover:underline">Browse Properties</a>
                        </div>
                    @endif
                </div>

                <!-- Bookings Tab -->
                <div id="bookings-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">My Bookings</h3>
                        <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:underline">View All</a>
                    </div>
                    <!-- Booking content would go here -->
                </div>

                <!-- Wishlist Tab -->
                <div id="wishlist-tab" class="tab-content hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">My Wishlist</h3>
                        <a href="{{ route('profile.wishlist') }}" class="text-blue-600 hover:underline">View All</a>
                    </div>
                    
                    @if($user->wishlists->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($user->wishlists->take(6) as $wishlist)
                                <div class="bg-gray-50 rounded-lg overflow-hidden">
                                    @if($wishlist->property->images->count() > 0)
                                        <img src="{{ Storage::url($wishlist->property->images->first()->image_path) }}" 
                                             alt="{{ $wishlist->property->title }}"
                                             class="w-full h-32 object-cover">
                                    @else
                                        <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-400">No Image</span>
                                        </div>
                                    @endif
                                    <div class="p-3">
                                        <h4 class="font-medium text-gray-900 text-sm">{{ $wishlist->property->title }}</h4>
                                        <p class="text-xs text-gray-600">{{ $wishlist->property->city }}</p>
                                        <p class="text-sm font-medium text-gray-900 mt-1">
                                            {{ $wishlist->property->currency }} {{ number_format($wishlist->property->price_per_night) }}/night
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No saved properties yet. Start building your wishlist!</p>
                        </div>
                    @endif
                </div>

                <!-- Settings Tab -->
                <div id="settings-tab" class="tab-content hidden">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Settings</h3>
                    <div class="space-y-4">
                        <a href="{{ route('profile.notification-settings') }}" 
                           class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h8V9H4v2z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">Notification Settings</span>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        
                        <a href="{{ route('profile.privacy-settings') }}"
                           class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">Privacy Settings</span>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>

                        @if($user->isHost())
                            <a href="{{ route('host.dashboard') }}"
                               class="flex items-center justify-between p-4 bg-blue-50 rounded-lg hover:bg-blue-100">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    <span class="font-medium text-blue-900">Host Dashboard</span>
                                </div>
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form (Hidden by default) -->
        <div id="edit-profile-form" class="bg-white rounded-lg shadow-sm p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Profile</h3>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="text" name="phone" value="{{ $user->phone }}" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ $user->date_of_birth?->format('Y-m-d') }}" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea name="bio" rows="3" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $user->bio }}</textarea>
                </div>
                
                <div class="mt-6 flex justify-end space-x-2">
                    <button type="button" onclick="hideEditForm()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all tab links
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('active', 'border-blue-500', 'text-blue-600');
        link.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to selected tab link
    event.target.classList.add('active', 'border-blue-500', 'text-blue-600');
    event.target.classList.remove('border-transparent', 'text-gray-500');
}

function showEditForm() {
    document.getElementById('edit-profile-form').classList.remove('hidden');
}

function hideEditForm() {
    document.getElementById('edit-profile-form').classList.add('hidden');
}
</script>
@endsection
