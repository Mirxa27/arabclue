@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Users</h2>
            <p class="text-gray-600">Manage all users, hosts, and administrators</p>
        </div>
        <div class="flex space-x-3">
            <button id="export-users" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Export CSV
            </button>
            <button id="add-user" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Add New User
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="role-filter" class="block text-sm font-medium text-gray-700">Role</label>
                <select id="role-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Roles</option>
                    <option value="guest">Guest</option>
                    <option value="host">Host</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div>
                <label for="verification-filter" class="block text-sm font-medium text-gray-700">Verification</label>
                <select id="verification-filter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                    <option value="">All</option>
                    <option value="verified">Verified</option>
                    <option value="unverified">Unverified</option>
                </select>
            </div>
            <div>
                <label for="search-users" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" id="search-users" placeholder="Search users..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">All Users</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all" class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verification</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- Users will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button id="prev-mobile" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </button>
                <button id="next-mobile" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span id="showing-from">1</span> to <span id="showing-to">10</span> of <span id="total-users">0</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" id="pagination-nav">
                        <!-- Pagination buttons will be generated here -->
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div id="user-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Add New User</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="user-form" class="space-y-4">
                <input type="hidden" id="user-id" name="id">
                
                <div>
                    <label for="user-name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="user-name" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>
                
                <div>
                    <label for="user-email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="user-email" name="email" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>
                
                <div>
                    <label for="user-phone" class="block text-sm font-medium text-gray-700">Phone</label>
                    <input type="tel" id="user-phone" name="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>
                
                <div>
                    <label for="user-role" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="user-role" name="role" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                        <option value="guest">Guest</option>
                        <option value="host">Host</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div>
                    <label for="user-status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="user-status" name="status" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                
                <div id="password-section">
                    <label for="user-password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="user-password" name="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password (when editing)</p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancel-user" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Cancel
                    </button>
                    <button type="submit" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentFilters = {};
    
    // Load users on page load
    loadUsers();
    
    // Filter event listeners
    document.getElementById('role-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('verification-filter').addEventListener('change', applyFilters);
    document.getElementById('search-users').addEventListener('input', debounce(applyFilters, 300));
    
    // Modal event listeners
    document.getElementById('add-user').addEventListener('click', () => openUserModal());
    document.getElementById('close-modal').addEventListener('click', closeUserModal);
    document.getElementById('cancel-user').addEventListener('click', closeUserModal);
    document.getElementById('user-form').addEventListener('submit', saveUser);
    
    // Export functionality
    document.getElementById('export-users').addEventListener('click', exportUsers);
    
    function loadUsers(page = 1) {
        const params = new URLSearchParams({
            page: page,
            ...currentFilters
        });
        
        fetch(`/api/v1/admin/users?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUsersTable(data.data.data);
                    renderPagination(data.data);
                    updateStats(data.data);
                }
            })
            .catch(error => console.error('Error loading users:', error));
    }
    
    function renderUsersTable(users) {
        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = '';
        
        users.forEach(user => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="user-checkbox h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded" value="${user.id}">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded-full" src="${user.avatar || '/images/default-avatar.jpg'}" alt="">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${user.name}</div>
                            <div class="text-sm text-gray-500">${user.email}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getRoleBadgeClass(user.role)}">
                        ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusBadgeClass(user.status)}">
                        ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${user.identity_verified ? 
                        '<span class="text-green-600">✓ Verified</span>' : 
                        '<span class="text-gray-400">Unverified</span>'
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${new Date(user.created_at).toLocaleDateString()}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="editUser(${user.id})" class="text-brand-blue hover:text-brand-blue-dark mr-3">Edit</button>
                    <button onclick="deleteUser(${user.id})" class="text-red-600 hover:text-red-900">Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    function getRoleBadgeClass(role) {
        switch(role) {
            case 'admin': return 'bg-purple-100 text-purple-800';
            case 'host': return 'bg-blue-100 text-blue-800';
            case 'guest': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function getStatusBadgeClass(status) {
        switch(status) {
            case 'active': return 'bg-green-100 text-green-800';
            case 'inactive': return 'bg-yellow-100 text-yellow-800';
            case 'suspended': return 'bg-red-100 text-red-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function applyFilters() {
        currentFilters = {
            role: document.getElementById('role-filter').value,
            status: document.getElementById('status-filter').value,
            verification: document.getElementById('verification-filter').value,
            search: document.getElementById('search-users').value
        };
        currentPage = 1;
        loadUsers(currentPage);
    }
    
    function openUserModal(userId = null) {
        const modal = document.getElementById('user-modal');
        const title = document.getElementById('modal-title');
        const form = document.getElementById('user-form');
        
        if (userId) {
            title.textContent = 'Edit User';
            loadUserData(userId);
        } else {
            title.textContent = 'Add New User';
            form.reset();
            document.getElementById('user-id').value = '';
        }
        
        modal.classList.remove('hidden');
    }
    
    function closeUserModal() {
        document.getElementById('user-modal').classList.add('hidden');
    }
    
    function saveUser(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const userId = formData.get('id');
        const url = userId ? `/api/v1/admin/users/${userId}` : '/api/v1/admin/users';
        const method = userId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeUserModal();
                loadUsers(currentPage);
                alert('User saved successfully!');
            } else {
                alert('Error saving user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving user');
        });
    }
    
    function exportUsers() {
        const params = new URLSearchParams(currentFilters);
        window.open(`/api/v1/admin/users/export?${params}`, '_blank');
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
    window.editUser = function(userId) {
        openUserModal(userId);
    };
    
    window.deleteUser = function(userId) {
        if (confirm('Are you sure you want to delete this user?')) {
            fetch(`/api/v1/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadUsers(currentPage);
                    alert('User deleted successfully!');
                } else {
                    alert('Error deleting user: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting user');
            });
        }
    };
});
</script>
@endpush
