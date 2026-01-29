@extends('layouts.host')

@section('title', 'My Properties')
@section('page-title', 'My Properties')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Your Properties</h2>
            <p class="text-gray-600">Manage your property listings and performance</p>
        </div>
        <div class="flex space-x-3">
            <button id="bulk-actions" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Bulk Actions
            </button>
            <a href="{{ route('host.properties.create') }}" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Add New Property
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Properties</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-properties">3</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Listings</dt>
                            <dd class="text-lg font-medium text-gray-900" id="active-properties">2</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Views</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-views">1,247</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Saved to Wishlists</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-saves">89</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="pending">Pending Review</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div>
                <label for="type-filter" class="block text-sm font-medium text-gray-700">Property Type</label>
                <select id="type-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Types</option>
                    <option value="apartment">Apartment</option>
                    <option value="house">House</option>
                    <option value="villa">Villa</option>
                    <option value="condo">Condo</option>
                </select>
            </div>
            <div>
                <label for="booking-filter" class="block text-sm font-medium text-gray-700">Booking Type</label>
                <select id="booking-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All</option>
                    <option value="instant">Instant Book</option>
                    <option value="request">Request to Book</option>
                </select>
            </div>
            <div>
                <label for="search-properties" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search-properties" placeholder="Search properties..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
            </div>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="properties-grid">
        <!-- Properties will be loaded here -->
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="text-center py-12 hidden">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No properties</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating your first property listing.</p>
        <div class="mt-6">
            <a href="{{ route('host.properties.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-blue hover:bg-brand-blue-dark">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Property
            </a>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loading-state" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brand-blue"></div>
        <p class="mt-2 text-sm text-gray-500">Loading properties...</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFilters = {};
    
    // Load properties on page load
    loadProperties();
    
    // Filter event listeners
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('type-filter').addEventListener('change', applyFilters);
    document.getElementById('booking-filter').addEventListener('change', applyFilters);
    document.getElementById('search-properties').addEventListener('input', debounce(applyFilters, 300));
    
    function loadProperties() {
        const params = new URLSearchParams(currentFilters);
        
        fetch(`/api/v1/host/properties?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderPropertiesGrid(data.data);
                    updateStats(data.stats);
                    hideLoading();
                }
            })
            .catch(error => {
                console.error('Error loading properties:', error);
                hideLoading();
            });
    }
    
    function renderPropertiesGrid(properties) {
        const grid = document.getElementById('properties-grid');
        const emptyState = document.getElementById('empty-state');
        
        if (properties.length === 0) {
            grid.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }
        
        grid.classList.remove('hidden');
        emptyState.classList.add('hidden');
        grid.innerHTML = '';
        
        properties.forEach(property => {
            const propertyCard = createPropertyCard(property);
            grid.appendChild(propertyCard);
        });
    }
    
    function createPropertyCard(property) {
        const card = document.createElement('div');
        card.className = 'bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-200';
        
        card.innerHTML = `
            <div class="relative">
                <img class="h-48 w-full object-cover" src="${property.primary_image || '/images/property-placeholder.jpg'}" alt="${property.title}">
                <div class="absolute top-2 left-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(property.status)}">
                        ${property.status.charAt(0).toUpperCase() + property.status.slice(1)}
                    </span>
                </div>
                <div class="absolute top-2 right-2">
                    ${property.instant_booking ? 
                        '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Instant Book</span>' : 
                        ''
                    }
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 truncate">${property.title}</h3>
                    <div class="flex items-center space-x-1">
                        <button onclick="toggleFeatured(${property.id})" class="text-yellow-400 hover:text-yellow-500">
                            <svg class="w-5 h-5 ${property.is_featured ? 'fill-current' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">${property.property_type} • ${property.accommodates} guests • ${property.bedrooms} bed${property.bedrooms !== 1 ? 's' : ''}</p>
                <p class="mt-2 text-sm text-gray-600 line-clamp-2">${property.description}</p>
                
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            ${property.views || 0}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            ${property.saves || 0}
                        </div>
                        ${property.overall_rating ? `
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-yellow-400 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            ${property.overall_rating.toFixed(1)}
                        </div>
                        ` : ''}
                    </div>
                    <div class="text-lg font-semibold text-gray-900">
                        SAR ${property.price_per_night}/night
                    </div>
                </div>
                
                <div class="mt-4 flex space-x-2">
                    <a href="/host/properties/${property.id}" class="flex-1 bg-brand-blue hover:bg-brand-blue-dark text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                        View Details
                    </a>
                    <a href="/host/properties/${property.id}/edit" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-2 px-3 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                        Edit
                    </a>
                </div>
            </div>
        `;
        
        return card;
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'draft': return 'bg-gray-100 text-gray-800';
            case 'pending': return 'bg-yellow-100 text-yellow-800';
            case 'suspended': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function applyFilters() {
        currentFilters = {
            status: document.getElementById('status-filter').value,
            type: document.getElementById('type-filter').value,
            booking: document.getElementById('booking-filter').value,
            search: document.getElementById('search-properties').value
        };
        showLoading();
        loadProperties();
    }
    
    function updateStats(stats) {
        if (stats) {
            document.getElementById('total-properties').textContent = stats.total || 0;
            document.getElementById('active-properties').textContent = stats.active || 0;
            document.getElementById('total-views').textContent = (stats.views || 0).toLocaleString();
            document.getElementById('total-saves').textContent = stats.saves || 0;
        }
    }
    
    function showLoading() {
        document.getElementById('loading-state').classList.remove('hidden');
        document.getElementById('properties-grid').classList.add('hidden');
        document.getElementById('empty-state').classList.add('hidden');
    }
    
    function hideLoading() {
        document.getElementById('loading-state').classList.add('hidden');
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
    
    // Global functions for inline actions
    window.toggleFeatured = function(propertyId) {
        fetch(`/api/v1/host/properties/${propertyId}/toggle-featured`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadProperties(); // Reload to update the star
            }
        })
        .catch(error => console.error('Error toggling featured:', error));
    };
});
</script>
@endpush
