@extends('layouts.admin')

@section('title', 'Coupon Management')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Coupon Management</h1>
            <p class="text-gray-600">Create and manage discount coupons for your platform</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <button onclick="openBulkGenerateModal()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-layer-group mr-2"></i>
                Bulk Generate
            </button>
            <a href="{{ route('admin.coupons.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Coupon
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Coupons</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_coupons']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Coupons</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['active_coupons']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Expired</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['expired_coupons']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Usage</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_usage']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Discount</p>
                    <p class="text-2xl font-bold text-gray-900">SAR {{ number_format($stats['total_discount_given'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.coupons.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by code, name, or description..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="used_up" {{ request('status') === 'used_up' ? 'selected' : '' }}>Used Up</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.coupons.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md">
                        <i class="fas fa-times mr-2"></i>Clear
                    </a>
                </div>
                
                <div class="flex items-end justify-end">
                    <a href="{{ route('admin.coupons.export') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-download mr-2"></i>Export CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Coupons Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Coupons ({{ $coupons->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            @if($coupons->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($coupons as $coupon)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $coupon->code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $coupon->name }}</div>
                                <div class="text-sm text-gray-500">{{ Str::limit($coupon->description, 50) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($coupon->type === 'percentage') bg-blue-100 text-blue-800
                                    @elseif($coupon->type === 'fixed_amount') bg-green-100 text-green-800
                                    @elseif($coupon->type === 'free_night') bg-purple-100 text-purple-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $coupon->type)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($coupon->type === 'percentage')
                                    {{ $coupon->value }}%
                                @elseif($coupon->type === 'fixed_amount')
                                    SAR {{ $coupon->value }}
                                @elseif($coupon->type === 'free_night')
                                    {{ $coupon->value }} {{ $coupon->value == 1 ? 'night' : 'nights' }}
                                @else
                                    Free cleaning
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $coupon->usages_count }} / {{ $coupon->usage_limit ?? '∞' }}
                                @if($coupon->usage_limit)
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             style="width: {{ min(100, ($coupon->usages_count / $coupon->usage_limit) * 100) }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($coupon->isValid())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @elseif($coupon->expires_at && $coupon->expires_at->isPast())
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Expired
                                    </span>
                                @elseif($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Used Up
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $coupon->expires_at ? $coupon->expires_at->format('M j, Y') : 'No expiry' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.coupons.show', $coupon) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" 
                                       class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="toggleStatus({{ $coupon->id }})" 
                                            class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas {{ $coupon->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                    <button onclick="deleteCoupon({{ $coupon->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $coupons->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-ticket-alt text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No coupons found</h3>
                    <p class="text-gray-600 mb-4">Get started by creating your first coupon.</p>
                    <a href="{{ route('admin.coupons.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Create Coupon
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Generate Modal -->
<div id="bulkGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Bulk Generate Coupons</h3>
                <button onclick="closeBulkGenerateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="bulkGenerateForm">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input type="number" name="quantity" min="1" max="1000" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name Prefix</label>
                        <input type="text" name="name_prefix" required
                               placeholder="e.g., Summer Sale"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="percentage">Percentage</option>
                            <option value="fixed_amount">Fixed Amount</option>
                            <option value="free_night">Free Night(s)</option>
                            <option value="free_cleaning">Free Cleaning</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Value</label>
                        <input type="number" name="value" step="0.01" min="0" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expires At (Optional)</label>
                        <input type="date" name="expires_at"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeBulkGenerateModal()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Generate Coupons
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBulkGenerateModal() {
    document.getElementById('bulkGenerateModal').classList.remove('hidden');
}

function closeBulkGenerateModal() {
    document.getElementById('bulkGenerateModal').classList.add('hidden');
}

function toggleStatus(couponId) {
    if (confirm('Are you sure you want to toggle this coupon status?')) {
        fetch(`/admin/coupons/${couponId}/toggle-status`, {
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
        });
    }
}

function deleteCoupon(couponId) {
    if (confirm('Are you sure you want to delete this coupon? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/coupons/${couponId}`;
        form.innerHTML = `
            @csrf
            @method('DELETE')
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Handle bulk generate form
document.getElementById('bulkGenerateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.coupons.generate-bulk") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeBulkGenerateModal();
            location.reload();
        } else {
            alert('Error generating coupons');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating coupons');
    });
});
</script>
@endsection