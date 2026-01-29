@extends('layouts.admin')

@section('title', 'Edit Coupon')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Coupon</h1>
            <p class="text-gray-600">Update coupon details and settings</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.coupons.show', $coupon) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-eye mr-2"></i>
                View Details
            </a>
            <a href="{{ route('admin.coupons.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Coupons
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code</label>
                        <input type="text" name="code" value="{{ old('code', $coupon->code) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
                        @error('code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $coupon->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $coupon->description) }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Discount Configuration -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Discount Configuration</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                        <select name="type" id="discountType" required onchange="updateValueLabel()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror">
                            <option value="percentage" {{ old('type', $coupon->type) === 'percentage' ? 'selected' : '' }}>Percentage Off</option>
                            <option value="fixed_amount" {{ old('type', $coupon->type) === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount Off</option>
                            <option value="free_night" {{ old('type', $coupon->type) === 'free_night' ? 'selected' : '' }}>Free Night(s)</option>
                            <option value="free_cleaning" {{ old('type', $coupon->type) === 'free_cleaning' ? 'selected' : '' }}>Free Cleaning Fee</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="valueLabel">Discount Value</span> *
                        </label>
                        <input type="number" name="value" value="{{ old('value', $coupon->value) }}" 
                               step="0.01" min="0" required id="discountValue"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('value') border-red-500 @enderror">
                        @error('value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1" id="valueHelp">Enter the discount value</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (SAR)</label>
                        <input type="number" name="maximum_discount" value="{{ old('maximum_discount', $coupon->maximum_discount) }}" 
                               step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('maximum_discount') border-red-500 @enderror">
                        @error('maximum_discount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Booking Amount (SAR)</label>
                        <input type="number" name="minimum_amount" value="{{ old('minimum_amount', $coupon->minimum_amount) }}" 
                               step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('minimum_amount') border-red-500 @enderror">
                        @error('minimum_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center space-x-4 pt-8">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Usage Limits -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Usage Limits</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Usage Limit</label>
                        <input type="number" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('usage_limit') border-red-500 @enderror">
                        @error('usage_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited usage</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit Per User</label>
                        <input type="number" name="user_limit" value="{{ old('user_limit', $coupon->user_limit) }}" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('user_limit') border-red-500 @enderror">
                        @error('user_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Validity Period -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Validity Period</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="datetime-local" name="starts_at" 
                               value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('starts_at') border-red-500 @enderror">
                        @error('starts_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                        <input type="datetime-local" name="expires_at" 
                               value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expires_at') border-red-500 @enderror">
                        @error('expires_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Applicable Properties -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Applicable Properties (Optional)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Property Types</label>
                        <select name="applicable_property_types[]" multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type }}" 
                                    {{ in_array($type, old('applicable_property_types', ($coupon->applicable_to['property_types'] ?? []))) ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cities</label>
                        <select name="applicable_cities[]" multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($cities as $city)
                                <option value="{{ $city }}" 
                                    {{ in_array($city, old('applicable_cities', ($coupon->applicable_to['cities'] ?? []))) ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.coupons.index') }}" 
                   class="px-6 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Update Coupon
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function updateValueLabel() {
    const type = document.getElementById('discountType').value;
    const valueLabel = document.getElementById('valueLabel');
    const valueHelp = document.getElementById('valueHelp');
    const valueInput = document.getElementById('discountValue');
    
    switch(type) {
        case 'percentage':
            valueLabel.textContent = 'Discount Percentage (%)';
            valueHelp.textContent = 'Enter the discount percentage (0-100)';
            valueInput.max = '100';
            valueInput.step = '0.01';
            valueInput.readOnly = false;
            break;
        case 'fixed_amount':
            valueLabel.textContent = 'Discount Amount (SAR)';
            valueHelp.textContent = 'Enter the fixed discount amount in SAR';
            valueInput.removeAttribute('max');
            valueInput.step = '0.01';
            valueInput.readOnly = false;
            break;
        case 'free_night':
            valueLabel.textContent = 'Number of Free Nights';
            valueHelp.textContent = 'Enter the number of free nights to give';
            valueInput.removeAttribute('max');
            valueInput.step = '1';
            valueInput.readOnly = false;
            break;
        case 'free_cleaning':
            valueLabel.textContent = 'Value (Not Used)';
            valueHelp.textContent = 'This field is not used for free cleaning fee coupons';
            valueInput.value = '0';
            valueInput.readOnly = true;
            break;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateValueLabel();
});
</script>
@endsection