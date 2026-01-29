@extends('layouts.admin')

@section('title', 'Coupon Details')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Coupon Details</h1>
            <p class="text-gray-600">View coupon information and usage statistics</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="toggleStatus()" 
                    class="bg-{{ $coupon->is_active ? 'yellow' : 'green' }}-600 hover:bg-{{ $coupon->is_active ? 'yellow' : 'green' }}-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }} mr-2"></i>
                {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
            </button>
            <a href="{{ route('admin.coupons.edit', $coupon) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Edit Coupon
            </a>
            <a href="{{ route('admin.coupons.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to List
            </a>
        </div>
    </div>

    <!-- Coupon Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Coupon Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Coupon Code</label>
                        <div class="flex items-center">
                            <span class="text-2xl font-bold text-blue-600 mr-3">{{ $coupon->code }}</span>
                            <button onclick="copyToClipboard('{{ $coupon->code }}')" 
                                    class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                        <div class="flex items-center">
                            @if($coupon->isValid())
                                <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i>Active
                                </span>
                            @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-times-circle mr-1"></i>Expired
                                </span>
                            @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-exclamation-circle mr-1"></i>Used Up
                                </span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                    <i class="fas fa-pause-circle mr-1"></i>Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Name</label>
                        <p class="text-gray-900 font-medium">{{ $coupon->name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Type</label>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($coupon->type === 'percentage') bg-blue-100 text-blue-800
                            @elseif($coupon->type === 'fixed_amount') bg-green-100 text-green-800
                            @elseif($coupon->type === 'free_night') bg-purple-100 text-purple-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $coupon->type)) }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Discount Value</label>
                        <p class="text-gray-900 font-medium">
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @elseif($coupon->type === 'fixed_amount')
                                SAR {{ number_format($coupon->value, 2) }}
                            @elseif($coupon->type === 'free_night')
                                {{ $coupon->value }} {{ $coupon->value == 1 ? 'night' : 'nights' }}
                            @else
                                Free cleaning
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Created By</label>
                        <p class="text-gray-900">{{ $coupon->creator->name ?? 'System' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-600 mb-1">Description</label>
                        <p class="text-gray-900">{{ $coupon->description }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Usage Statistics</h2>
                
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">Total Uses</span>
                            <span class="text-lg font-bold text-gray-900">{{ $stats['total_uses'] }}</span>
                        </div>
                        @if($coupon->usage_limit)
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" 
                                     style="width: {{ min(100, ($coupon->used_count / $coupon->usage_limit) * 100) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $coupon->used_count }} / {{ $coupon->usage_limit }} uses</p>
                        @endif
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Unique Users</span>
                            <span class="text-lg font-bold text-gray-900">{{ $stats['unique_users'] }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Total Discount Given</span>
                            <span class="text-lg font-bold text-green-600">SAR {{ number_format($stats['total_discount_given'], 2) }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Average Discount</span>
                            <span class="text-lg font-bold text-gray-900">SAR {{ number_format($stats['average_discount'], 2) }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Remaining Uses</span>
                            <span class="text-lg font-bold text-gray-900">{{ $stats['remaining_uses'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Validity & Limits -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Validity & Limits</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Start Date:</span>
                    <span class="font-medium">{{ $coupon->starts_at ? $coupon->starts_at->format('M j, Y H:i') : 'Immediate' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Expiry Date:</span>
                    <span class="font-medium">{{ $coupon->expires_at ? $coupon->expires_at->format('M j, Y H:i') : 'No expiry' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Minimum Amount:</span>
                    <span class="font-medium">{{ $coupon->minimum_amount ? 'SAR ' . number_format($coupon->minimum_amount, 2) : 'No minimum' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Maximum Discount:</span>
                    <span class="font-medium">{{ $coupon->maximum_discount ? 'SAR ' . number_format($coupon->maximum_discount, 2) : 'No limit' }}</span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">User Limit:</span>
                    <span class="font-medium">{{ $coupon->user_limit }} per user</span>
                </div>
            </div>
        </div>

        <!-- Applicable Properties -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Applicable Properties</h2>
            
            <div class="space-y-4">
                <div>
                    <span class="text-gray-600 block mb-2">Property Types:</span>
                    @if(!empty($coupon->applicable_to['property_types']))
                        <div class="flex flex-wrap gap-2">
                            @foreach($coupon->applicable_to['property_types'] as $type)
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">{{ ucfirst($type) }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-400 italic">All property types</span>
                    @endif
                </div>
                
                <div>
                    <span class="text-gray-600 block mb-2">Cities:</span>
                    @if(!empty($coupon->applicable_to['cities']))
                        <div class="flex flex-wrap gap-2">
                            @foreach($coupon->applicable_to['cities'] as $city)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-sm">{{ $city }}</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-400 italic">All cities</span>
                    @endif
                </div>

                @if(!empty($coupon->restrictions['minimum_nights']) || !empty($coupon->restrictions['maximum_nights']))
                <div>
                    <span class="text-gray-600 block mb-2">Night Restrictions:</span>
                    <div class="text-sm">
                        @if(!empty($coupon->restrictions['minimum_nights']))
                            <p>Minimum: {{ $coupon->restrictions['minimum_nights'] }} nights</p>
                        @endif
                        @if(!empty($coupon->restrictions['maximum_nights']))
                            <p>Maximum: {{ $coupon->restrictions['maximum_nights'] }} nights</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Usage -->
    @if($recentUsages->count() > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Recent Usage</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Used</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentUsages as $usage)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $usage->user->name ?? 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">{{ $usage->user->email ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            SAR {{ number_format($usage->discount_amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $usage->booking_reference ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $usage->created_at->format('M j, Y H:i') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-8 text-center">
        <div class="text-gray-400 mb-4">
            <i class="fas fa-ticket-alt text-4xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Usage Yet</h3>
        <p class="text-gray-600">This coupon hasn't been used by any customers yet.</p>
    </div>
    @endif
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success feedback
        const event = new CustomEvent('show-toast', {
            detail: { message: 'Coupon code copied to clipboard!', type: 'success' }
        });
        window.dispatchEvent(event);
    });
}

function toggleStatus() {
    if (confirm('Are you sure you want to toggle this coupon status?')) {
        fetch(`{{ route('admin.coupons.toggle-status', $coupon) }}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error toggling coupon status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error toggling coupon status');
        });
    }
}
</script>
@endsection