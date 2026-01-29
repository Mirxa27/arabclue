@extends('layouts.admin')

@section('title', 'Property Management')
@section('page-title', 'Property Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Properties</h2>
            <p class="text-gray-600">Manage all property listings and approvals</p>
        </div>
        <div class="flex space-x-3">
            <button id="export-properties" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Export CSV
            </button>
            <button id="bulk-actions" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Bulk Actions
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Properties</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-properties-count">Loading...</dd>
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
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Approval</dt>
                            <dd class="text-lg font-medium text-gray-900" id="pending-properties-count">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Suspended</dt>
                            <dd class="text-lg font-medium text-gray-900" id="suspended-properties-count">Loading...</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Views</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-views-count">Loading...</dd>
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
                    <option value="active">Active</option>
                    <option value="pending">Pending Approval</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div>
                <label for="type-filter" class="block text-sm font-medium text-gray-700">Property Type</label>
                <select id="type-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Types</option>
                    <option value="apartment">Apartment</option>
                    <option value="house">House</option>
                    <option value="villa">Villa</option>
                    <option value="studio">Studio</option>
                    <option value="room">Room</option>
                </select>
            </div>
            <div>
                <label for="city-filter" class="block text-sm font-medium text-gray-700">City</label>
                <select id="city-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Cities</option>
                    <option value="dubai">Dubai</option>
                    <option value="abu-dhabi">Abu Dhabi</option>
                    <option value="sharjah">Sharjah</option>
                    <option value="ajman">Ajman</option>
                </select>
            </div>
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search" placeholder="Search properties..." class="mt-1 block w-full pl-3 pr-3 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
            </div>
            <div class="flex items-end">
                <button id="apply-filters" class="w-full bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Property Listings</h3>
            
            <!-- Loading State -->
            <div id="loading-properties" class="text-center py-8">
                <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-gray-100">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading properties...
                </div>
            </div>

            <!-- Properties Table -->
            <div id="properties-table" class="hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="select-all" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Host</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody id="properties-tbody" class="bg-white divide-y divide-gray-200">
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
                                Showing <span class="font-medium" id="page-start">1</span> to <span class="font-medium" id="page-end">10</span> of <span class="font-medium" id="total-properties">0</span> results
                            </p>
                        </div>
<div>
    <div class="p-6 bg-white border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Channel Manager</h2>
            <p class="text-gray-600">Manage all property listings and approvals</p>
        </div>
    </div>

    <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 p-6">
        @foreach ($properties as $property)
            @include('property.card', ['property' => $property])
        @endforeach
    </div>
</div>
        </div>
    </div>
</div>

<!-- Property Details Modal -->
<div id="property-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
    loadProperties();
    loadPropertyStats();
    
    // Event listeners
    document.getElementById('apply-filters').addEventListener('click', loadProperties);
    document.getElementById('search').addEventListener('keyup', debounce(loadProperties, 300));
    document.getElementById('export-properties').addEventListener('click', exportProperties);
    
    // Select all checkbox functionality
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="property_ids[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });
});

async function loadProperties() {
    const loadingElement = document.getElementById('loading-properties');
    const tableElement = document.getElementById('properties-table');
    const emptyElement = document.getElementById('empty-state');
    
    loadingElement.classList.remove('hidden');
    tableElement.classList.add('hidden');
    emptyElement.classList.add('hidden');
    
    try {
        // Get filter values
        const filters = {
            status: document.getElementById('status-filter').value,
            type: document.getElementById('type-filter').value,
            city: document.getElementById('city-filter').value,
            search: document.getElementById('search').value
        };
        
        const queryString = new URLSearchParams(filters).toString();
        const response = await fetch(`/api/admin/properties?${queryString}`, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch properties');
        }
        
        const data = await response.json();
        
        loadingElement.classList.add('hidden');
        
        if (data.data && data.data.length > 0) {
            renderPropertiesTable(data.data);
            updatePagination(data.meta || {});
            tableElement.classList.remove('hidden');
        } else {
            emptyElement.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading properties:', error);
        loadingElement.classList.add('hidden');
        emptyElement.classList.remove('hidden');
    }
}

async function loadPropertyStats() {
    try {
        const response = await fetch('/api/admin/properties/stats', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            const stats = await response.json();
            document.getElementById('active-properties-count').textContent = stats.active || 0;
            document.getElementById('pending-properties-count').textContent = stats.pending || 0;
            document.getElementById('suspended-properties-count').textContent = stats.suspended || 0;
            document.getElementById('total-views-count').textContent = stats.total_views || 0;
        }
    } catch (error) {
        console.error('Error loading property stats:', error);
    }
}

function renderPropertiesTable(properties) {
    const tbody = document.getElementById('properties-tbody');
    tbody.innerHTML = properties.map(property => `
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" name="property_ids[]" value="${property.id}" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                        <img class="h-10 w-10 rounded-lg object-cover" src="${property.main_image || '/images/default-property.jpg'}" alt="">
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${property.title}</div>
                        <div class="text-sm text-gray-500">${property.address}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${property.host?.name || 'N/A'}</div>
                <div class="text-sm text-gray-500">${property.host?.email || ''}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${property.type}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                AED ${property.price_per_night}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(property.status)}">
                    ${property.status}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                ${new Date(property.created_at).toLocaleDateString()}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button onclick="viewProperty(${property.id})" class="text-brand-blue hover:text-brand-blue-dark mr-2">View</button>
                <button onclick="approveProperty(${property.id})" class="text-green-600 hover:text-green-900 mr-2">Approve</button>
                <button onclick="suspendProperty(${property.id})" class="text-red-600 hover:text-red-900">Suspend</button>
            </td>
        </tr>
    `).join('');
}

function getStatusColor(status) {
    const colors = {
        'active': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'suspended': 'bg-red-100 text-red-800',
        'rejected': 'bg-gray-100 text-gray-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
}

function updatePagination(meta) {
    // Update pagination info
    document.getElementById('page-start').textContent = meta.from || 1;
    document.getElementById('page-end').textContent = meta.to || 0;
    document.getElementById('total-properties').textContent = meta.total || 0;
}

async function exportProperties() {
    try {
        const response = await fetch('/api/admin/properties/export', {
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
            a.download = 'properties.csv';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        }
    } catch (error) {
        console.error('Error exporting properties:', error);
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

// Property action functions
async function viewProperty(id) {
    // Implementation for viewing property details
}

async function approveProperty(id) {
    if (confirm('Are you sure you want to approve this property?')) {
        // Implementation for approving property
    }
}

async function suspendProperty(id) {
    if (confirm('Are you sure you want to suspend this property?')) {
        // Implementation for suspending property
    }
}
</script>
@endsection
