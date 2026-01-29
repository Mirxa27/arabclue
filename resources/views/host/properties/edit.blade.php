@extends('layouts.host')

@section('title', 'Edit Property - ' . $property->title)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Property</h1>
            <p class="text-gray-600 mt-2">Update your property listing details</p>
        </div>

        <form id="propertyForm" action="{{ route('host.properties.update', $property) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700">Property Title *</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $property->title) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $property->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="property_type" class="block text-sm font-medium text-gray-700">Property Type *</label>
                        <select id="property_type" name="property_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type }}" {{ old('property_type', $property->property_type) === $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('property_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="room_type" class="block text-sm font-medium text-gray-700">Room Type *</label>
                        <select id="room_type" name="room_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($roomTypes as $type)
                                <option value="{{ $type }}" {{ old('room_type', $property->room_type) === $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('room_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Property Details -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Property Details</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <label for="accommodates" class="block text-sm font-medium text-gray-700">Guests *</label>
                        <input type="number" id="accommodates" name="accommodates" value="{{ old('accommodates', $property->accommodates) }}" 
                               min="1" max="20" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('accommodates')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bedrooms" class="block text-sm font-medium text-gray-700">Bedrooms *</label>
                        <input type="number" id="bedrooms" name="bedrooms" value="{{ old('bedrooms', $property->bedrooms) }}" 
                               min="0" max="20" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('bedrooms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="beds" class="block text-sm font-medium text-gray-700">Beds *</label>
                        <input type="number" id="beds" name="beds" value="{{ old('beds', $property->beds) }}" 
                               min="1" max="50" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('beds')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bathrooms" class="block text-sm font-medium text-gray-700">Bathrooms *</label>
                        <input type="number" id="bathrooms" name="bathrooms" value="{{ old('bathrooms', $property->bathrooms) }}" 
                               min="0.5" max="20" step="0.5" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('bathrooms')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Location -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Location</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $property->address) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City *</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $property->city) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700">State/Province</label>
                        <input type="text" id="state" name="state" value="{{ old('state', $property->state) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('state')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700">Country *</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $property->country) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $property->postal_code) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude *</label>
                        <input type="number" id="latitude" name="latitude" value="{{ old('latitude', $property->latitude) }}" 
                               step="any" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude *</label>
                        <input type="number" id="longitude" name="longitude" value="{{ old('longitude', $property->longitude) }}" 
                               step="any" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Pricing -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Pricing</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="price_per_night" class="block text-sm font-medium text-gray-700">Price per Night (SAR) *</label>
                        <input type="number" id="price_per_night" name="price_per_night" 
                               value="{{ old('price_per_night', $property->price_per_night) }}" 
                               min="1" step="0.01" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('price_per_night')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cleaning_fee" class="block text-sm font-medium text-gray-700">Cleaning Fee (SAR)</label>
                        <input type="number" id="cleaning_fee" name="cleaning_fee" 
                               value="{{ old('cleaning_fee', $property->cleaning_fee) }}" 
                               min="0" step="0.01"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('cleaning_fee')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="security_deposit" class="block text-sm font-medium text-gray-700">Security Deposit (SAR)</label>
                        <input type="number" id="security_deposit" name="security_deposit" 
                               value="{{ old('security_deposit', $property->security_deposit) }}" 
                               min="0" step="0.01"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('security_deposit')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Current Images -->
            @if($property->images && $property->images->count() > 0)
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Images</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($property->images as $image)
                        <div class="relative">
                            <img src="{{ $image->url }}" alt="Property image" class="w-full h-32 object-cover rounded-lg">
                            <button type="button" onclick="removeImage({{ $image->id }})" 
                                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                ×
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- New Images -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Add New Images</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="main_image" class="block text-sm font-medium text-gray-700">New Main Image</label>
                        <input type="file" id="main_image" name="main_image" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('main_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="images" class="block text-sm font-medium text-gray-700">Additional Images</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">You can select multiple images (max 10)</p>
                        @error('images')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Amenities</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($amenities as $amenity)
                        <div class="flex items-center">
                            <input type="checkbox" id="amenity_{{ $amenity->id }}" name="amenities[]" value="{{ $amenity->id }}"
                                   {{ $property->amenities->contains($amenity->id) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="amenity_{{ $amenity->id }}" class="ml-2 text-sm text-gray-700">
                                {{ $amenity->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <a href="{{ route('host.properties.index') }}" 
                   class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                
                <div class="flex space-x-4">
                    <button type="button" onclick="saveDraft()" 
                            class="px-6 py-3 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition-colors">
                        Save as Draft
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Update Property
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function removeImage(imageId) {
    if (confirm('Are you sure you want to remove this image?')) {
        fetch(`/host/properties/images/${imageId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing image: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing image');
        });
    }
}

function saveDraft() {
    const form = document.getElementById('propertyForm');
    const formData = new FormData(form);
    formData.append('status', 'draft');
    
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Property saved as draft!');
        } else {
            alert('Error saving draft: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving draft');
    });
}
</script>
@endsection
