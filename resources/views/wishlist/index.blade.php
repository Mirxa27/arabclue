@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Wishlist</h1>
            <p class="text-gray-600">Properties you've saved for later</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_items'] }}</div>
                <div class="text-sm text-gray-600">Saved Properties</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-green-600">SAR {{ number_format($stats['total_value']) }}</div>
                <div class="text-sm text-gray-600">Total Value</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['cities_count'] }}</div>
                <div class="text-sm text-gray-600">Cities</div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['countries_count'] }}</div>
                <div class="text-sm text-gray-600">Countries</div>
            </div>
        </div>

        @if($wishlists->count() > 0)
            <!-- Filters and Actions -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                    <!-- Filters -->
                    <form method="GET" class="flex flex-wrap items-center gap-4">
                        <select name="city" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">All Cities</option>
                            @foreach($filterOptions['cities'] as $city)
                                <option value="{{ $city }}" {{ ($filters['city'] ?? '') === $city ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>

                        <select name="country" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">All Countries</option>
                            @foreach($filterOptions['countries'] as $country)
                                <option value="{{ $country }}" {{ ($filters['country'] ?? '') === $country ? 'selected' : '' }}>
                                    {{ $country }}
                                </option>
                            @endforeach
                        </select>

                        <select name="property_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="">All Types</option>
                            @foreach($filterOptions['property_types'] as $type)
                                <option value="{{ $type }}" {{ ($filters['property_type'] ?? '') === $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>

                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                            Apply Filters
                        </button>

                        @if(array_filter($filters))
                            <a href="{{ route('wishlist.index') }}" 
                               class="text-gray-600 hover:text-gray-800 text-sm">
                                Clear Filters
                            </a>
                        @endif
                    </form>

                    <!-- Bulk Actions -->
                    <div class="flex items-center space-x-2">
                        <button onclick="selectAll()" 
                                class="text-blue-600 hover:text-blue-800 text-sm">
                            Select All
                        </button>
                        <button onclick="clearSelection()" 
                                class="text-gray-600 hover:text-gray-800 text-sm">
                            Clear Selection
                        </button>
                        <button onclick="shareSelected()" 
                                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                            Share Selected
                        </button>
                        <button onclick="deleteSelected()" 
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                            Remove Selected
                        </button>
                    </div>
                </div>
            </div>

            <!-- Wishlist Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($wishlists as $wishlist)
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow wishlist-item">
                        <!-- Selection Checkbox -->
                        <div class="relative">
                            <input type="checkbox" 
                                   class="absolute top-3 left-3 z-10 wishlist-checkbox" 
                                   value="{{ $wishlist->id }}"
                                   data-property-id="{{ $wishlist->property->id }}">
                            
                            <!-- Property Image -->
                            <div class="relative h-48">
                                @if($wishlist->property->images->count() > 0)
                                    <img src="{{ Storage::url($wishlist->property->images->first()->image_path) }}" 
                                         alt="{{ $wishlist->property->title }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-400">No Image</span>
                                    </div>
                                @endif

                                <!-- Remove Button -->
                                <button onclick="removeFromWishlist({{ $wishlist->id }})" 
                                        class="absolute top-3 right-3 p-2 bg-white bg-opacity-80 rounded-full hover:bg-opacity-100 transition-all">
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Property Details -->
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold text-gray-900 text-lg leading-tight">
                                    <a href="{{ route('properties.show', $wishlist->property->slug) }}" 
                                       class="hover:text-blue-600">
                                        {{ $wishlist->property->title }}
                                    </a>
                                </h3>
                                @if($wishlist->property->average_rating)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
                                        </svg>
                                        <span class="ml-1 text-sm text-gray-600">{{ number_format($wishlist->property->average_rating, 1) }}</span>
                                    </div>
                                @endif
                            </div>

                            <p class="text-gray-600 text-sm mb-3">{{ $wishlist->property->city }}, {{ $wishlist->property->country }}</p>
                            
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ $wishlist->property->currency }} {{ number_format($wishlist->property->price_per_night) }}
                                    </span>
                                    <span class="text-gray-600 text-sm">/ night</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    Added {{ $wishlist->created_at->diffForHumans() }}
                                </div>
                            </div>

                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('properties.show', $wishlist->property->slug) }}" 
                                   class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 text-sm font-medium text-center">
                                    View Property
                                </a>
                                <button onclick="shareProperty({{ $wishlist->property->id }})" 
                                        class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 text-sm font-medium">
                                    Share
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $wishlists->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Your wishlist is empty</h3>
                    <p class="mt-1 text-sm text-gray-500">Start saving properties you love to build your perfect travel wishlist.</p>
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

<!-- Bulk Action Forms -->
<form id="bulk-action-form" method="POST" action="{{ route('wishlist.bulk-action') }}" class="hidden">
    @csrf
    <input type="hidden" name="action" id="bulk-action">
    <div id="bulk-wishlist-ids"></div>
</form>

<script>
function selectAll() {
    document.querySelectorAll('.wishlist-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function clearSelection() {
    document.querySelectorAll('.wishlist-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function getSelectedItems() {
    const selected = [];
    document.querySelectorAll('.wishlist-checkbox:checked').forEach(checkbox => {
        selected.push(checkbox.value);
    });
    return selected;
}

function shareSelected() {
    const selected = getSelectedItems();
    if (selected.length === 0) {
        alert('Please select items to share');
        return;
    }
    
    document.getElementById('bulk-action').value = 'share';
    const container = document.getElementById('bulk-wishlist-ids');
    container.innerHTML = '';
    
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'wishlist_ids[]';
        input.value = id;
        container.appendChild(input);
    });
    
    document.getElementById('bulk-action-form').submit();
}

function deleteSelected() {
    const selected = getSelectedItems();
    if (selected.length === 0) {
        alert('Please select items to remove');
        return;
    }
    
    if (!confirm(`Are you sure you want to remove ${selected.length} items from your wishlist?`)) {
        return;
    }
    
    document.getElementById('bulk-action').value = 'delete';
    const container = document.getElementById('bulk-wishlist-ids');
    container.innerHTML = '';
    
    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'wishlist_ids[]';
        input.value = id;
        container.appendChild(input);
    });
    
    document.getElementById('bulk-action-form').submit();
}

function removeFromWishlist(wishlistId) {
    if (!confirm('Remove this property from your wishlist?')) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/wishlist/${wishlistId}`;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    
    form.appendChild(csrfToken);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

function shareProperty(propertyId) {
    const url = `${window.location.origin}/properties/${propertyId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Check out this property',
            url: url
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Property link copied to clipboard!');
        });
    }
}
</script>
@endsection
