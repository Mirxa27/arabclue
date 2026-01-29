@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Bookings</h1>
            <p class="text-gray-600">Manage your trips and booking history</p>
        </div>

        <!-- Status Filter -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('bookings.index') }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Bookings
                </a>
                <a href="{{ route('bookings.index', ['status' => 'upcoming']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Upcoming
                </a>
                <a href="{{ route('bookings.index', ['status' => 'current']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'current' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Current
                </a>
                <a href="{{ route('bookings.index', ['status' => 'past']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'past' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Past
                </a>
                <a href="{{ route('bookings.index', ['status' => 'cancelled']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'cancelled' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Cancelled
                </a>
            </div>
        </div>

        <!-- Bookings List -->
        @if($bookings->count() > 0)
            <div class="space-y-6">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="md:flex">
                            <!-- Property Image -->
                            <div class="md:w-1/3">
                                @if($booking->property->images->count() > 0)
                                    <img src="{{ Storage::url($booking->property->images->first()->image_path) }}" 
                                         alt="{{ $booking->property->title }}"
                                         class="w-full h-48 md:h-full object-cover">
                                @else
                                    <div class="w-full h-48 md:h-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400">No Image</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Booking Details -->
                            <div class="md:w-2/3 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900 mb-1">
                                            <a href="{{ route('properties.show', $booking->property->slug) }}" 
                                               class="hover:text-blue-600">
                                                {{ $booking->property->title }}
                                            </a>
                                        </h3>
                                        <p class="text-gray-600">{{ $booking->property->city }}, {{ $booking->property->country }}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium
                                        @if($booking->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($booking->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Check-in</p>
                                        <p class="font-medium">{{ $booking->check_in->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Check-out</p>
                                        <p class="font-medium">{{ $booking->check_out->format('M d, Y') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Guests</p>
                                        <p class="font-medium">{{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}</p>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-500">Total Amount</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('bookings.show', $booking) }}" 
                                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            View Details
                                        </a>
                                        @if($booking->status === 'confirmed' && $booking->check_out < now() && !$booking->review_submitted)
                                            <a href="{{ route('bookings.review', $booking) }}" 
                                               class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                                Write Review
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($status === 'all')
                            You haven't made any bookings yet.
                        @else
                            No {{ $status }} bookings found.
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('properties.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Browse Properties
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
