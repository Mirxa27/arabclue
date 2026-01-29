@extends('layouts.admin')

@section('title', 'Booking Management')
@section('page-title', 'Booking Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Bookings</h2>
            <p class="text-gray-600">Manage all bookings and reservations</p>
        </div>
        <div class="flex space-x-3">
            <button id="export-bookings" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Export CSV
            </button>
            <button id="refund-actions" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Process Refunds
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Bookings</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-bookings-count">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Confirmed</dt>
                            <dd class="text-lg font-medium text-gray-900" id="confirmed-bookings-count">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                            <dd class="text-lg font-medium text-gray-900" id="pending-bookings-count">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-revenue">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="completed">Completed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div>
                <label for="payment-filter" class="block text-sm font-medium text-gray-700">Payment Status</label>
                <select id="payment-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Payments</option>
                    <option value="paid">Paid</option>
                    <option value="pending">Pending Payment</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            <div>
                <label for="date-range" class="block text-sm font-medium text-gray-700">Date Range</label>
                <select id="date-range" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" placeholder="Search bookings..." class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
            </div>
            <div class="flex items-end">
                <button id="apply-filters" class="w-full bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Bookings</h3>
            
            <!-- Loading State -->
            <div id="loading-bookings" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-gray-100">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading bookings...
                </div>
            </div>

            <!-- Bookings Table -->
            <div id="bookings-table" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody id="bookings-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Dynamic content will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>
                        <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium" id="page-start">1</span> to <span class="font-medium" id="page-end">10</span> of <span class="font-medium" id="total-bookings">0</span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Pagination controls will be generated dynamically -->
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No bookings found</h3>
                <p class="mt-1 text-sm text-gray-500">Bookings will appear here once guests start making reservations.</p>
            </div>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="booking-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Modal content will be loaded dynamically -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadBookings();
    loadBookingStats();
    
    // Event listeners
    document.getElementById('apply-filters').addEventListener('click', loadBookings);
    document.getElementById('search').addEventListener('keyup', debounce(loadBookings, 300));
    document.getElementById('export-bookings').addEventListener('click', exportBookings);
});

async function loadBookings() {
    const loadingElement = document.getElementById('loading-bookings');
    const tableElement = document.getElementById('bookings-table');
    const emptyElement = document.getElementById('empty-state');
    
    loadingElement.classList.remove('hidden');
    tableElement.classList.add('hidden');
    emptyElement.classList.add('hidden');
    
    try {
        // Get filter values
        const filters = {
            status: document.getElementById('status-filter').value,
            payment_status: document.getElementById('payment-filter').value,
            date_range: document.getElementById('date-range').value,
            search: document.getElementById('search').value
        };
        
        const queryString = new URLSearchParams(filters).toString();
        const response = await fetch(`/api/admin/bookings?${queryString}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch bookings');
        }
        
        const data = await response.json();
        
        loadingElement.classList.add('hidden');
        
        if (data.data && data.data.length > 0) {
            renderBookingsTable(data.data);
            updatePagination(data.meta || {});
            tableElement.classList.remove('hidden');
        } else {
            emptyElement.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
        loadingElement.classList.add('hidden');
        emptyElement.classList.remove('hidden');
    }
}

async function loadBookingStats() {
    try {
        const response = await fetch('/api/admin/bookings/stats', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const stats = await response.json();
            document.getElementById('total-bookings-count').textContent = stats.total || 0;
            document.getElementById('confirmed-bookings-count').textContent = stats.confirmed || 0;
            document.getElementById('pending-bookings-count').textContent = stats.pending || 0;
            document.getElementById('total-revenue').textContent = 'AED ' + (stats.total_revenue || 0);
        }
    } catch (error) {
        console.error('Error loading booking stats:', error);
    }
}

function renderBookingsTable(bookings) {
    const tbody = document.getElementById('bookings-tbody');
    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">#${booking.booking_uid}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="h-8 w-8 flex-shrink-0">
                        <img class="h-8 w-8 rounded-full object-cover" src="${booking.guest?.avatar || '/images/default-avatar.jpg'}" alt="">
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">${booking.guest?.name || 'N/A'}</div>
                        <div class="text-sm text-gray-500">${booking.guest?.email || ''}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${booking.property?.title || 'N/A'}</div>
                <div class="text-sm text-gray-500">${booking.property?.address || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${formatDate(booking.check_in)} - ${formatDate(booking.check_out)}</div>
                <div class="text-sm text-gray-500">${booking.nights} night(s)</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                AED ${booking.total_amount}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(booking.status)}">
                    ${booking.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getPaymentStatusColor(booking.payment_status)}">
                    ${booking.payment_status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="viewBooking('${booking.booking_uid}')" class="text-brand-blue hover:text-brand-blue-dark mr-2">View</button>
                <button onclick="refundBooking('${booking.booking_uid}')" class="text-red-600 hover:text-red-900">Refund</button>
            </td>
        </tr>
    `).join('');
}

function getStatusColor(status) {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800',
        'completed': 'bg-blue-100 text-blue-800',
        'refunded': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function getPaymentStatusColor(status) {
    const colors = {
        'paid': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'failed': 'bg-red-100 text-red-800',
        'refunded': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function updatePagination(meta) {
    document.getElementById('page-start').textContent = meta.from || 1;
    document.getElementById('page-end').textContent = meta.to || 0;
    document.getElementById('total-bookings').textContent = meta.total || 0;
}

async function exportBookings() {
    try {
        const response = await fetch('/api/admin/bookings/export', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'bookings.csv';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }
    } catch (error) {
        console.error('Error exporting bookings:', error);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Booking action functions
async function viewBooking(bookingUid) {
    // Implementation for viewing booking details
}

async function refundBooking(bookingUid) {
    if (confirm('Are you sure you want to process a refund for this booking?')) {
        // Implementation for processing refund
    }
}
</script>
@endsection
