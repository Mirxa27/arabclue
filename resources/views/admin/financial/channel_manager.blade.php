@extends('layouts.admin')

@section('title', 'Channel Manager')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Channel Manager
    </h2>

    <!-- Channel Integration Card -->
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700">Connected Channels</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Airbnb Channel -->
                <div class="border rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-red-50 border-b flex items-center justify-between">
                        <div class="flex items-center">
                            <img src="/images/channels/airbnb.png" alt="Airbnb" class="h-6 mr-2">
                            <h4 class="font-medium text-red-800">Airbnb</h4>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Listings</span>
                            <span class="text-sm font-medium">12</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Bookings</span>
                            <span class="text-sm font-medium">24</span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="text-sm text-gray-600">Last Sync</span>
                            <span class="text-sm font-medium">Today, 10:45 AM</span>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition">Sync Now</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition">Settings</button>
                        </div>
                    </div>
                </div>

                <!-- Booking.com Channel -->
                <div class="border rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-blue-50 border-b flex items-center justify-between">
                        <div class="flex items-center">
                            <img src="/images/channels/booking.png" alt="Booking.com" class="h-6 mr-2">
                            <h4 class="font-medium text-blue-800">Booking.com</h4>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Listings</span>
                            <span class="text-sm font-medium">8</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Bookings</span>
                            <span class="text-sm font-medium">16</span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="text-sm text-gray-600">Last Sync</span>
                            <span class="text-sm font-medium">Today, 09:30 AM</span>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition">Sync Now</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition">Settings</button>
                        </div>
                    </div>
                </div>

                <!-- Expedia Channel -->
                <div class="border rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-yellow-50 border-b flex items-center justify-between">
                        <div class="flex items-center">
                            <img src="/images/channels/expedia.png" alt="Expedia" class="h-6 mr-2">
                            <h4 class="font-medium text-yellow-800">Expedia</h4>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Listings</span>
                            <span class="text-sm font-medium">6</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Bookings</span>
                            <span class="text-sm font-medium">9</span>
                        </div>
                        <div class="flex justify-between mb-4">
                            <span class="text-sm text-gray-600">Last Sync</span>
                            <span class="text-sm font-medium">Today, 08:15 AM</span>
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition">Sync Now</button>
                            <button class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 transition">Settings</button>
                        </div>
                    </div>
                </div>

                <!-- Add New Channel -->
                <div class="border rounded-lg overflow-hidden border-dashed border-gray-300">
                    <div class="flex flex-col items-center justify-center p-6 h-full">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <h4 class="font-medium text-gray-700 mb-1">Connect New Channel</h4>
                        <p class="text-sm text-gray-500 text-center mb-3">Add more booking platforms to expand your reach</p>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Channel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Sync -->
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700">Calendar Synchronization</h3>
        </div>
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h4 class="font-medium text-gray-700 mb-1">iCal Sync Settings</h4>
                    <p class="text-sm text-gray-500">Synchronize your availability calendar across platforms</p>
                </div>
                <div class="mt-3 md:mt-0">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Configure iCal</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">iCal URL</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Sync</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Luxury Beach Villa</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">https://example.com/ical/property-123.ics</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 10:45 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900">Copy URL</a>
                                <span class="text-gray-300 mx-1">|</span>
                                <a href="#" class="text-blue-600 hover:text-blue-900">Sync</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Downtown Apartment</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">https://example.com/ical/property-456.ics</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 09:30 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900">Copy URL</a>
                                <span class="text-gray-300 mx-1">|</span>
                                <a href="#" class="text-blue-600 hover:text-blue-900">Sync</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Mountain Cabin</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">https://example.com/ical/property-789.ics</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Warning</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yesterday, 15:20 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="#" class="text-blue-600 hover:text-blue-900">Copy URL</a>
                                <span class="text-gray-300 mx-1">|</span>
                                <a href="#" class="text-blue-600 hover:text-blue-900">Sync</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sync History -->
    <div class="mb-8 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-700">Sync History</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 10:45 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="/images/channels/airbnb.png" alt="Airbnb" class="h-5 mr-2">
                                    <span class="text-sm text-gray-900">Airbnb</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Calendar Sync</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Successfully synchronized calendars for 12 properties</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 09:30 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="/images/channels/booking.png" alt="Booking.com" class="h-5 mr-2">
                                    <span class="text-sm text-gray-900">Booking.com</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Booking Import</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Imported 3 new bookings</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today, 08:15 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="/images/channels/expedia.png" alt="Expedia" class="h-5 mr-2">
                                    <span class="text-sm text-gray-900">Expedia</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rate Update</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Error</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Failed to update rates for 2 properties</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yesterday, 15:20 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img src="/images/channels/airbnb.png" alt="Airbnb" class="h-5 mr-2">
                                    <span class="text-sm text-gray-900">Airbnb</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Listing Update</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Updated information for 5 listings</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
