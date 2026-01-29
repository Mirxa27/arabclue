@extends('layouts.admin')

@section('title', 'Create Coupon')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Create New Coupon</h1>
            <p class="text-gray-600">Set up a new discount coupon for your platform</p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Coupons
        </a>
    </div>

    <!-- Create Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Basic Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code</label>
                        <input type="text" name="code" value="{{ old('code') }}" 
                               placeholder="Leave empty to auto-generate"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
                        @error('code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Leave empty to auto-generate a unique code</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="e.g., Summer Sale 2024"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" rows="3" required
                                  placeholder="Describe what this coupon is for..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
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
                            <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage Off</option>
                            <option value="fixed_amount" {{ old('type') === 'fixed_amount' ? 'selected' : '' }}>Fixed Amount Off</option>
                            <option value="free_night" {{ old('type') === 'free_night' ? 'selected' : '' }}>Free Night(s)</option>
                            <option value="free_cleaning" {{ old('type') === 'free_cleaning' ? 'selected' : '' }}>Free Cleaning Fee</option>
                        </select>
                        @error('type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span id="valueLabel">Discount Value</span> *
                        </label>
                        <input type="number" name="value" value="{{ old('value') }}" 
                               step="0.01" min="0" required id="discountValue"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('value') border-red-500 @enderror">
                        @error('value')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1" id="valueHelp">Enter the discount percentage (0-100)</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Discount (SAR)</label>
                        <input type="number" name="maximum_discount" value="{{ old('maximum_discount') }}" 
                               step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('maximum_discount') border-red-500 @enderror">
                        @error('maximum_discount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Optional: Set maximum discount amount</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Booking Amount (SAR)</label>
                        <input type="number" name="minimum_amount" value="{{ old('minimum_amount') }}" 
                               step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('minimum_amount') border-red-500 @enderror">
                        @error('minimum_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Optional: Minimum booking amount to use this coupon</p>
                    </div>

                    <div class="flex items-center space-x-4 pt-8">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
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
                        <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('usage_limit') border-red-500 @enderror">
                        @error('usage_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Leave empty for unlimited usage</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit Per User</label>
                        <input type="number" name="user_limit" value="{{ old('user_limit', 1) }}" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('user_limit') border-red-500 @enderror">
                        @error('user_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">How many times each user can use this coupon</p>
                    </div>
                </div>
            </div>

            <!-- Validity Period -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Validity Period</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('starts_at') border-red-500 @enderror">
                        @error('starts_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Leave empty to start immediately</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expires_at') border-red-500 @enderror">
                        @error('expires_at')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Leave empty for no expiry</p>
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
                                <option value="{{ $type }}" {{ in_array($type, old('applicable_property_types', [])) ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple. Leave empty for all types.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cities</label>
                        <select name="applicable_cities[]" multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ in_array($city, old('applicable_cities', [])) ? 'selected' : '' }}>
                                    {{ $city }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple. Leave empty for all cities.</p>
                    </div>
                </div>
            </div>

            <!-- Booking Restrictions -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Booking Restrictions (Optional)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Nights</label>
                        <input type="number" name="minimum_nights" value="{{ old('minimum_nights') }}" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Minimum number of nights required</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Nights</label>
                        <input type="number" name="maximum_nights" value="{{ old('maximum_nights') }}" 
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Maximum number of nights allowed</p>
                    </div>
                </div>
            </div>

            <!-- Blackout Dates -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Blackout Dates (Optional)</h2>
                <div id="blackoutDates">
                    <div class="blackout-date-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" name="blackout_dates[0][start]" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" name="blackout_dates[0][end]"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="removeBlackoutDate(this)" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addBlackoutDate()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    Add Blackout Period
                </button>
                <p class="text-xs text-gray-500 mt-2">Dates when this coupon cannot be used for check-in/checkout</p>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.coupons.index') }}" 
                   class="px-6 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Create Coupon
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let blackoutDateIndex = 1;

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
            break;
        case 'fixed_amount':
            valueLabel.textContent = 'Discount Amount (SAR)';
            valueHelp.textContent = 'Enter the fixed discount amount in SAR';
            valueInput.removeAttribute('max');
            valueInput.step = '0.01';
            break;
        case 'free_night':
            valueLabel.textContent = 'Number of Free Nights';
            valueHelp.textContent = 'Enter the number of free nights to give';
            valueInput.removeAttribute('max');
            valueInput.step = '1';
            break;
        case 'free_cleaning':
            valueLabel.textContent = 'Value (Not Used)';
            valueHelp.textContent = 'This field is not used for free cleaning fee coupons';
            valueInput.value = '0';
            valueInput.readOnly = true;
            return;
        default:
            break;
    }
    valueInput.readOnly = false;
}

function addBlackoutDate() {
    const container = document.getElementById('blackoutDates');
    const newRow = document.createElement('div');
    newRow.className = 'blackout-date-row grid grid-cols-1 md:grid-cols-3 gap-4 mb-4';
    newRow.innerHTML = `
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input type="date" name="blackout_dates[${blackoutDateIndex}][start]" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input type="date" name="blackout_dates[${blackoutDateIndex}][end]"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="removeBlackoutDate(this)" 
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md">
                Remove
            </button>
        </div>
    `;
    container.appendChild(newRow);
    blackoutDateIndex++;
}

function removeBlackoutDate(button) {
    const row = button.closest('.blackout-date-row');
    if (document.querySelectorAll('.blackout-date-row').length > 1) {
        row.remove();
    } else {
        // Clear the inputs instead of removing the last row
        row.querySelectorAll('input').forEach(input => input.value = '');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateValueLabel();
});
</script>
@endsection