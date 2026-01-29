@extends('layouts.host')

@section('title', 'Channel Manager')
@section('page-title', 'Channel Manager')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Channel Manager</h2>
            <p class="text-gray-600">Manage your property listings across multiple platforms</p>
        </div>
        <div class="flex space-x-3">
            <button id="sync-all" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Sync All Channels
            </button>
            <button id="add-channel" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                Add Channel
            </button>
        </div>
    </div>

    <!-- Channel Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Connected Channels</dt>
                            <dd class="text-lg font-medium text-gray-900" id="connected-channels">4</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Last Sync</dt>
                            <dd class="text-lg font-medium text-gray-900" id="last-sync">2 min ago</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Sync Issues</dt>
                            <dd class="text-lg font-medium text-gray-900" id="sync-issues">0</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connected Channels -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Connected Channels</h3>
            <p class="mt-1 text-sm text-gray-500">Manage your property distribution across booking platforms</p>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="channels-grid">
                <!-- Channels will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Property Sync Status -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Property Sync Status</h3>
            <p class="mt-1 text-sm text-gray-500">Monitor synchronization status for each property</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HabibiStay</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking.com</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Airbnb</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expedia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="properties-sync-table" class="bg-white divide-y divide-gray-200">
                    <!-- Properties will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sync Logs -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Sync Activity</h3>
        </div>
        
        <div class="p-6">
            <div class="flow-root">
                <ul class="-my-5 divide-y divide-gray-200" id="sync-logs">
                    <!-- Sync logs will be loaded here -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add Channel Modal -->
<div id="channel-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Add New Channel</h3>
                <button id="close-channel-modal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="channel-form" class="space-y-4">
                <div>
                    <label for="channel-type" class="block text-sm font-medium text-gray-700">Channel Type</label>
                    <select id="channel-type" name="type" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-brand-blue focus:border-brand-blue sm:text-sm rounded-md">
                        <option value="">Select a channel</option>
                        <option value="booking">Booking.com</option>
                        <option value="airbnb">Airbnb</option>
                        <option value="expedia">Expedia</option>
                        <option value="agoda">Agoda</option>
                        <option value="vrbo">VRBO</option>
                    </select>
                </div>
                
                <div>
                    <label for="channel-name" class="block text-sm font-medium text-gray-700">Channel Name</label>
                    <input type="text" id="channel-name" name="name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>
                
                <div>
                    <label for="api-key" class="block text-sm font-medium text-gray-700">API Key</label>
                    <input type="password" id="api-key" name="api_key" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>
                
                <div>
                    <label for="api-secret" class="block text-sm font-medium text-gray-700">API Secret</label>
                    <input type="password" id="api-secret" name="api_secret" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-blue focus:border-brand-blue sm:text-sm">
                </div>
                
                <div class="flex items-center">
                    <input id="auto-sync" name="auto_sync" type="checkbox" checked class="h-4 w-4 text-brand-blue focus:ring-brand-blue border-gray-300 rounded">
                    <label for="auto-sync" class="ml-2 text-sm text-gray-700">Enable automatic synchronization</label>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancel-channel" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Cancel
                    </button>
                    <button type="submit" class="bg-brand-blue hover:bg-brand-blue-dark text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out">
                        Connect Channel
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
    // Load initial data
    loadChannels();
    loadPropertySyncStatus();
    loadSyncLogs();
    
    // Event listeners
    document.getElementById('sync-all').addEventListener('click', syncAllChannels);
    document.getElementById('add-channel').addEventListener('click', () => openChannelModal());
    document.getElementById('close-channel-modal').addEventListener('click', closeChannelModal);
    document.getElementById('cancel-channel').addEventListener('click', closeChannelModal);
    document.getElementById('channel-form').addEventListener('submit', addChannel);
    
    function loadChannels() {
        fetch('/api/v1/host/channels')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderChannels(data.data);
                    updateChannelStats(data.stats);
                }
            })
            .catch(error => console.error('Error loading channels:', error));
    }
    
    function renderChannels(channels) {
        const grid = document.getElementById('channels-grid');
        grid.innerHTML = '';
        
        channels.forEach(channel => {
            const channelCard = createChannelCard(channel);
            grid.appendChild(channelCard);
        });
    }
    
    function createChannelCard(channel) {
        const card = document.createElement('div');
        card.className = 'border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200';
        
        card.innerHTML = `
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <img src="/images/channels/${channel.type}.png" alt="${channel.name}" class="w-8 h-8 mr-3" onerror="this.src='/images/default-channel.png'">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">${channel.name}</h4>
                        <p class="text-xs text-gray-500">${channel.type}</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getChannelStatusClass(channel.status)}">
                        ${channel.status}
                    </span>
                </div>
            </div>
            
            <div class="space-y-2 text-sm text-gray-600">
                <div class="flex justify-between">
                    <span>Properties:</span>
                    <span>${channel.properties_count || 0}</span>
                </div>
                <div class="flex justify-between">
                    <span>Last Sync:</span>
                    <span>${formatDate(channel.last_sync_at)}</span>
                </div>
                <div class="flex justify-between">
                    <span>Auto Sync:</span>
                    <span>${channel.auto_sync ? 'Enabled' : 'Disabled'}</span>
                </div>
            </div>
            
            <div class="mt-4 flex space-x-2">
                <button onclick="syncChannel(${channel.id})" class="flex-1 bg-brand-blue hover:bg-brand-blue-dark text-white text-center py-2 px-3 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                    Sync Now
                </button>
                <button onclick="configureChannel(${channel.id})" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-2 px-3 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                    Configure
                </button>
            </div>
        `;
        
        return card;
    }
    
    function getChannelStatusClass(status) {
        switch(status) {
            case 'connected': return 'bg-green-100 text-green-800';
            case 'syncing': return 'bg-blue-100 text-blue-800';
            case 'error': return 'bg-red-100 text-red-800';
            case 'disconnected': return 'bg-gray-100 text-gray-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    }
    
    function loadPropertySyncStatus() {
        fetch('/api/v1/host/properties/sync-status')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderPropertySyncTable(data.data);
                }
            })
            .catch(error => console.error('Error loading property sync status:', error));
    }
    
    function renderPropertySyncTable(properties) {
        const tbody = document.getElementById('properties-sync-table');
        tbody.innerHTML = '';
        
        properties.forEach(property => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <img class="h-10 w-10 rounded object-cover" src="${property.primary_image || '/images/property-placeholder.jpg'}" alt="">
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${property.title}</div>
                            <div class="text-sm text-gray-500">${property.property_type}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${getSyncStatusBadge(property.booking_sync_status)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${getSyncStatusBadge(property.airbnb_sync_status)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${getSyncStatusBadge(property.expedia_sync_status)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button onclick="syncProperty(${property.id})" class="text-brand-blue hover:text-brand-blue-dark">Sync</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
    
    function getSyncStatusBadge(status) {
        if (!status) {
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Not Connected</span>';
        }
        
        switch(status) {
            case 'synced':
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Synced</span>';
            case 'syncing':
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Syncing</span>';
            case 'error':
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Error</span>';
            default:
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>';
        }
    }
    
    function loadSyncLogs() {
        fetch('/api/v1/host/sync-logs')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderSyncLogs(data.data);
                }
            })
            .catch(error => console.error('Error loading sync logs:', error));
    }
    
    function renderSyncLogs(logs) {
        const container = document.getElementById('sync-logs');
        container.innerHTML = '';
        
        logs.forEach(log => {
            const logItem = document.createElement('li');
            logItem.className = 'py-4';
            logItem.innerHTML = `
                <div class="flex space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 ${getLogStatusClass(log.status)} rounded-full flex items-center justify-center">
                            ${getLogStatusIcon(log.status)}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">${log.message}</p>
                        <p class="text-sm text-gray-500">${log.channel_name} • ${formatDate(log.created_at)}</p>
                    </div>
                </div>
            `;
            container.appendChild(logItem);
        });
    }
    
    function getLogStatusClass(status) {
        switch(status) {
            case 'success': return 'bg-green-100';
            case 'error': return 'bg-red-100';
            case 'warning': return 'bg-yellow-100';
            default: return 'bg-blue-100';
        }
    }
    
    function getLogStatusIcon(status) {
        switch(status) {
            case 'success':
                return '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
            case 'error':
                return '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            case 'warning':
                return '<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
            default:
                return '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        }
    }
    
    function formatDate(dateString) {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
    
    function openChannelModal() {
        document.getElementById('channel-modal').classList.remove('hidden');
    }
    
    function closeChannelModal() {
        document.getElementById('channel-modal').classList.add('hidden');
        document.getElementById('channel-form').reset();
    }
    
    function addChannel(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        fetch('/api/v1/host/channels', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeChannelModal();
                loadChannels();
                alert('Channel connected successfully!');
            } else {
                alert('Error connecting channel: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error connecting channel');
        });
    }
    
    function syncAllChannels() {
        fetch('/api/v1/host/channels/sync-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sync started for all channels!');
                loadChannels();
                loadPropertySyncStatus();
            } else {
                alert('Error starting sync: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error starting sync');
        });
    }
    
    // Global functions for inline actions
    window.syncChannel = function(channelId) {
        fetch(`/api/v1/host/channels/${channelId}/sync`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Channel sync started!');
                loadChannels();
            }
        })
        .catch(error => console.error('Error syncing channel:', error));
    };
    
    window.configureChannel = function(channelId) {
        // Open configuration modal or redirect to configuration page
        window.location.href = `/host/channels/${channelId}/configure`;
    };
    
    window.syncProperty = function(propertyId) {
        fetch(`/api/v1/host/properties/${propertyId}/sync`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Property sync started!');
                loadPropertySyncStatus();
            }
        })
        .catch(error => console.error('Error syncing property:', error));
    };
});
</script>
@endpush
