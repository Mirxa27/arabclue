@extends('layouts.host')

@section('title', 'Host Dashboard')
@section('page-title', 'Host Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Host Dashboard</h1>
            <p class="text-gray-600">Manage your properties and bookings</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Properties</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_properties'] }}</p>
                        <p class="text-xs text-green-600">{{ $stats['active_properties'] }} active</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Bookings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_bookings'] }}</p>
                        <p class="text-xs text-yellow-600">{{ $stats['pending_bookings'] }} pending</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">SAR {{ number_format($stats['total_earnings']) }}</p>
                        <p class="text-xs text-green-600">SAR {{ number_format($stats['earnings_this_month']) }} this month</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Average Rating</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}</p>
                        <p class="text-xs text-gray-600">{{ $stats['total_reviews'] }} reviews</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('host.properties.create') }}" 
                   class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                    <div class="p-2 bg-blue-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Add New Property</p>
                        <p class="text-sm text-gray-600">List a new property</p>
                    </div>
                </a>

                <a href="{{ route('host.bookings.index') }}"
                   class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                    <div class="p-2 bg-green-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">Manage Bookings</p>
                        <p class="text-sm text-gray-600">View and respond to bookings</p>
                    </div>
                </a>

                <a href="{{ route('host.earnings') }}" 
                   class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                    <div class="p-2 bg-purple-600 rounded-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium text-gray-900">View Earnings</p>
                        <p class="text-sm text-gray-600">Track your income</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Bookings -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Bookings</h3>
                    <a href="{{ route('host.bookings.index') }}" class="text-blue-600 hover:underline text-sm">View All</a>
                </div>

                @if($recentBookings->count() > 0)
                    <div class="space-y-4">
                        @foreach($recentBookings as $booking)
                            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                                @if($booking->user->avatar)
                                    <img src="{{ Storage::url($booking->user->avatar) }}" 
                                         alt="{{ $booking->user->name }}"
                                         class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $booking->user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $booking->property->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $booking->check_in->format('M d') }} - {{ $booking->check_out->format('M d') }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                    <p class="text-sm font-medium text-gray-900 mt-1">SAR {{ number_format($booking->total_amount) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">No recent bookings</p>
                    </div>
                @endif
            </div>

            <!-- Upcoming Check-ins -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Check-ins</h3>

                @if($upcomingCheckins->count() > 0)
                    <div class="space-y-4">
                        @foreach($upcomingCheckins as $checkin)
                            <div class="flex items-center space-x-4 p-3 bg-blue-50 rounded-lg">
                                <div class="text-center">
                                    <p class="text-lg font-bold text-blue-600">{{ $checkin->check_in->format('d') }}</p>
                                    <p class="text-xs text-blue-600">{{ $checkin->check_in->format('M') }}</p>
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $checkin->user->name }}</p>
                                    <p class="text-sm text-gray-600">{{ $checkin->property->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $checkin->guests }} guests • {{ $checkin->total_nights }} nights</p>
                                </div>
                                <div class="text-right">
                                    @if($checkin->user->phone)
                                        <a href="tel:{{ $checkin->user->phone }}" 
                                           class="text-blue-600 hover:underline text-sm">
                                            {{ $checkin->user->phone }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500">No upcoming check-ins</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="mt-8 bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Metrics</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['current_guests'] }}</p>
                    <p class="text-sm text-gray-600">Current Guests</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['upcoming_checkins'] }}</p>
                    <p class="text-sm text-gray-600">Upcoming Check-ins</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['occupancy_rate'], 1) }}%</p>
                    <p class="text-sm text-gray-600">Occupancy Rate</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['response_rate'], 1) }}%</p>
                    <p class="text-sm text-gray-600">Response Rate</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
