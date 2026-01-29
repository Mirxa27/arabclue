@extends('layouts.app')

@section('title', 'Financial Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8" id="financial-dashboard">
    <div class="flex flex-col md:flex-row items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Financial Dashboard</h1>
            <p class="text-gray-600">Track your earnings and manage your finances</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <div class="relative">
                <select id="date-range" class="block appearance-none bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
                    <option>Last 30 days</option>
                    <option>Last 90 days</option>
                    <option>This year</option>
                    <option>Last year</option>
                    <option>Custom range</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                </div>
            </div>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Channel Manager Financial Summary -->
    <div class="bg-white rounded-lg shadow p-6 mb-8" id="channel-financial-summary">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Revenue by Channel</h2>
            <button onclick="refreshChannelFinancials()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm transition">
                <i class="fas fa-sync-alt mr-1"></i> Refresh
            </button>
        </div>
        
        <div id="channel-summary-loading" class="text-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
            <p class="text-gray-500">Loading channel data...</p>
        </div>
        
        <div id="channel-summary-content" class="hidden">
            <!-- Channel breakdown will be populated here -->
        </div>
        
        <div id="channel-summary-error" class="hidden text-center py-8">
            <div class="text-red-500 mb-2">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <p class="text-gray-600">Failed to load channel financial data</p>
            <button onclick="loadChannelFinancials()" class="mt-2 text-blue-600 hover:text-blue-800">Try again</button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">Total Revenue</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    +12.5%
                </span>
            </div>
            <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">$24,389.20</p>
                <p class="ml-2 text-sm text-gray-500">from $21,678.40</p>
            </div>
            <div class="mt-4">
                <div class="bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 75%"></div>
                </div>
                <p class="mt-2 text-xs text-gray-500">75% of your yearly goal</p>
            </div>
        </div>

        <!-- Bookings -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">Bookings</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    +8.2%
                </span>
            </div>
            <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">146</p>
                <p class="ml-2 text-sm text-gray-500">from 135</p>
            </div>
            <div class="mt-4 flex items-center justify-between text-sm">
                <div>
                    <span class="inline-block w-3 h-3 rounded-full bg-blue-400 mr-1"></span>
                    <span class="text-gray-600">Completed: 122</span>
                </div>
                <div>
                    <span class="inline-block w-3 h-3 rounded-full bg-yellow-400 mr-1"></span>
                    <span class="text-gray-600">Upcoming: 24</span>
                </div>
            </div>
        </div>

        <!-- Occupancy Rate -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">Occupancy Rate</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    +4.7%
                </span>
            </div>
            <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">78.3%</p>
                <p class="ml-2 text-sm text-gray-500">from 74.8%</p>
            </div>
            <div class="mt-4">
                <div class="bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: 78.3%"></div>
                </div>
                <div class="mt-2 grid grid-cols-12 gap-1 text-center">
                    <div class="text-xs text-gray-500">J</div>
                    <div class="text-xs text-gray-500">F</div>
                    <div class="text-xs text-gray-500">M</div>
                    <div class="text-xs text-gray-500">A</div>
                    <div class="text-xs text-gray-500">M</div>
                    <div class="text-xs text-gray-500">J</div>
                    <div class="text-xs text-gray-500">J</div>
                    <div class="text-xs text-gray-500">A</div>
                    <div class="text-xs text-gray-500">S</div>
                    <div class="text-xs text-gray-500">O</div>
                    <div class="text-xs text-gray-500">N</div>
                    <div class="text-xs text-gray-500">D</div>
                </div>
            </div>
        </div>

        <!-- Average Rating -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-gray-500 text-sm font-medium">Average Rating</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    +0.2
                </span>
            </div>
            <div class="flex items-baseline">
                <p class="text-2xl font-semibold text-gray-900">4.8</p>
                <p class="ml-2 text-sm text-gray-500">out of 5.0</p>
            </div>
            <div class="mt-4 flex items-center">
                <div class="flex text-yellow-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" opacity="0.3">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                </div>
                <p class="ml-2 text-sm text-gray-500">from 128 reviews</p>
            </div>
        </div>
    </div>

    <!-- Revenue Chart & Property Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-700">Revenue Overview</h3>
                <div class="flex space-x-4">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-blue-500 mr-1"></span>
                        <span class="text-sm text-gray-600">Revenue</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full bg-green-400 mr-1"></span>
                        <span class="text-sm text-gray-600">Bookings</span>
                    </div>
                </div>
            </div>
            <div class="h-72">
                <!-- Chart would be rendered here with JavaScript -->
                <div class="w-full h-full flex items-center justify-center">
                    <p class="text-gray-500">Revenue chart will be displayed here</p>
                </div>
            </div>
        </div>

        <!-- Property Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-700 mb-6">Property Performance</h3>
            <div class="space-y-6">
                <!-- Property 1 -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-800">Luxury Beach Villa</span>
                        <span class="text-sm text-gray-500">$9,450.40</span>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 mr-4">
                            <div class="bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 85%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-800">85%</span>
                    </div>
                </div>

                <!-- Property 2 -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-800">Downtown Apartment</span>
                        <span class="text-sm text-gray-500">$7,325.60</span>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 mr-4">
                            <div class="bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 72%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-800">72%</span>
                    </div>
                </div>

                <!-- Property 3 -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-800">Mountain Cabin</span>
                        <span class="text-sm text-gray-500">$4,780.80</span>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 mr-4">
                            <div class="bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 64%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-800">64%</span>
                    </div>
                </div>

                <!-- Property 4 -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-800">Lakeside Cottage</span>
                        <span class="text-sm text-gray-500">$2,832.40</span>
                    </div>
                    <div class="flex items-center">
                        <div class="flex-1 mr-4">
                            <div class="bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: 48%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-gray-800">48%</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-gray-200">
                <a href="/host/properties" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                    View all properties
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Transactions & Calendar Links -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow lg:col-span-2">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-700">Recent Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#INV-5723</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex-shrink-0 mr-3"></div>
                                    <div class="text-sm font-medium text-gray-900">Sarah Johnson</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Luxury Beach Villa</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">June 03, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$1,245.80</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#INV-5722</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex-shrink-0 mr-3"></div>
                                    <div class="text-sm font-medium text-gray-900">Michael Anderson</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Downtown Apartment</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">June 02, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$895.50</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#INV-5721</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex-shrink-0 mr-3"></div>
                                    <div class="text-sm font-medium text-gray-900">Emily Rodriguez</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Mountain Cabin</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">May 30, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$1,050.00</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#INV-5720</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex-shrink-0 mr-3"></div>
                                    <div class="text-sm font-medium text-gray-900">David Wilson</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Lakeside Cottage</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">May 28, 2023</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">$780.25</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <a href="/host/transactions" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                    View all transactions
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Channel Manager & Quick Links -->
        <div>
            <!-- Channel Manager Card -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700">Channel Manager</h3>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <img src="/images/channels/airbnb.png" alt="Airbnb" class="h-8 mr-3">
                                <span class="font-medium text-gray-700">Airbnb</span>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <img src="/images/channels/booking.png" alt="Booking.com" class="h-8 mr-3">
                                <span class="font-medium text-gray-700">Booking.com</span>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <img src="/images/channels/expedia.png" alt="Expedia" class="h-8 mr-3">
                                <span class="font-medium text-gray-700">Expedia</span>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Connected</span>
                        </div>
                    </div>
                    <a href="/host/channel-manager" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                        Manage channels
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Quick Links Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-700">Quick Links</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-3">
                        <li>
                            <a href="/host/bookings" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Manage Bookings</span>
                            </a>
                        </li>
                        <li>
                            <a href="/host/calendar" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Calendar Sync</span>
                            </a>
                        </li>
                        <li>
                            <a href="/host/pricing" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Pricing Tools</span>
                            </a>
                        </li>
                        <li>
                            <a href="/host/messaging" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <span>Guest Messaging</span>
                            </a>
                        </li>
                        <li>
                            <a href="/host/reports" class="flex items-center text-gray-700 hover:text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Financial Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadChannelFinancials();
});

async function loadChannelFinancials() {
    const loadingEl = document.getElementById('channel-summary-loading');
    const contentEl = document.getElementById('channel-summary-content');
    const errorEl = document.getElementById('channel-summary-error');
    
    // Show loading
    loadingEl.classList.remove('hidden');
    contentEl.classList.add('hidden');
    errorEl.classList.add('hidden');
    
    try {
        const response = await fetch('/api/channel-manager/financial-summary', {
            headers: {
                'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content,
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch channel financial data');
        }
        
        const data = await response.json();
        
        if (data.success) {
            renderChannelFinancials(data.data);
            loadingEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
        } else {
            throw new Error(data.message || 'Failed to load data');
        }
        
    } catch (error) {
        console.error('Error loading channel financials:', error);
        loadingEl.classList.add('hidden');
        errorEl.classList.remove('hidden');
    }
}

function renderChannelFinancials(data) {
    const contentEl = document.getElementById('channel-summary-content');
    
    if (!data.channels || Object.keys(data.channels).length === 0) {
        contentEl.innerHTML = `
            <div class="text-center py-8">
                <div class="text-gray-400 mb-2">
                    <i class="fas fa-chart-line text-3xl"></i>
                </div>
                <p class="text-gray-600 mb-4">No channel data available</p>
                <a href="/host/channel-manager" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Connect Channels
                </a>
            </div>
        `;
        return;
    }
    
    let channelsHtml = '';
    let totalRevenue = 0;
    
    Object.values(data.channels).forEach(channel => {
        totalRevenue += channel.revenue;
        const percentage = data.summary.total_revenue > 0 ? 
            ((channel.revenue / data.summary.total_revenue) * 100).toFixed(1) : 0;
        
        channelsHtml += `
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full bg-blue-500 mr-3"></div>
                    <div>
                        <p class="font-medium text-gray-800">${channel.channel_name}</p>
                        <p class="text-sm text-gray-500">${channel.bookings} bookings</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-800">$${channel.revenue.toFixed(2)}</p>
                    <p class="text-sm text-gray-500">${percentage}%</p>
                </div>
            </div>
        `;
    });
    
    contentEl.innerHTML = `
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">Total Revenue</span>
                <span class="text-lg font-bold text-gray-800">$${data.summary.total_revenue.toFixed(2)}</span>
            </div>
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>${data.summary.total_bookings} total bookings</span>
                <span>Avg: $${data.summary.avg_booking_value.toFixed(2)}</span>
            </div>
        </div>
        <div class="space-y-1">
            ${channelsHtml}
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Period: ${data.period.start_date} to ${data.period.end_date}</span>
                <button onclick="syncAllChannelCalendars()" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-sync-alt mr-1"></i> Sync Calendars
                </button>
            </div>
        </div>
    `;
}

function refreshChannelFinancials() {
    loadChannelFinancials();
}

async function syncAllChannelCalendars() {
    try {
        const response = await fetch('/api/channel-manager/sync-calendars', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]')?.content,
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success message
            showToast('Calendars synced successfully', 'success');
            // Refresh the financial data
            setTimeout(() => {
                loadChannelFinancials();
            }, 1000);
        } else {
            showToast(data.message || 'Failed to sync calendars', 'error');
        }
        
    } catch (error) {
        console.error('Error syncing calendars:', error);
        showToast('Error syncing calendars', 'error');
    }
}

function showToast(message, type = 'info') {
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endsection
