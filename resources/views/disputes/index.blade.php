@extends('layouts.app')

@section('title', 'My Disputes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Disputes</h1>
            <p class="text-gray-600">Manage your booking disputes and resolutions</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Disputes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Open Disputes</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['open'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Resolved</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['resolved'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Filter -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('disputes.index') }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Disputes
                </a>
                <a href="{{ route('disputes.index', ['status' => 'open']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'open' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Open
                </a>
                <a href="{{ route('disputes.index', ['status' => 'in_review']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'in_review' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    In Review
                </a>
                <a href="{{ route('disputes.index', ['status' => 'resolved']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'resolved' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Resolved
                </a>
                <a href="{{ route('disputes.index', ['status' => 'closed']) }}" 
                   class="px-4 py-2 rounded-lg {{ $status === 'closed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Closed
                </a>
            </div>
        </div>

        <!-- Disputes List -->
        @if($disputes->count() > 0)
            <div class="space-y-6">
                @foreach($disputes as $dispute)
                    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $dispute->subject }}</h3>
                                        <span class="ml-3 px-3 py-1 rounded-full text-sm font-medium
                                            @if($dispute->status === 'open') bg-red-100 text-red-800
                                            @elseif($dispute->status === 'in_review') bg-yellow-100 text-yellow-800
                                            @elseif($dispute->status === 'waiting_response') bg-blue-100 text-blue-800
                                            @elseif($dispute->status === 'resolved') bg-green-100 text-green-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                        </span>
                                        <span class="ml-2 px-2 py-1 rounded text-xs font-medium
                                            @if($dispute->priority === 'urgent') bg-red-100 text-red-700
                                            @elseif($dispute->priority === 'high') bg-orange-100 text-orange-700
                                            @elseif($dispute->priority === 'medium') bg-yellow-100 text-yellow-700
                                            @else bg-gray-100 text-gray-700
                                            @endif">
                                            {{ ucfirst($dispute->priority) }}
                                        </span>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">
                                        Dispute ID: <span class="font-medium">{{ $dispute->dispute_id }}</span>
                                    </p>
                                    <p class="text-gray-600 text-sm">
                                        Property: <a href="{{ route('properties.show', $dispute->booking->property->slug) }}" 
                                                    class="text-blue-600 hover:underline">{{ $dispute->booking->property->title }}</a>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">{{ $dispute->created_at->format('M d, Y') }}</p>
                                    @if($dispute->amount_disputed)
                                        <p class="text-sm font-medium text-gray-900 mt-1">
                                            Amount: {{ $dispute->booking->currency }} {{ number_format($dispute->amount_disputed, 2) }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="text-gray-700">{{ Str::limit($dispute->description, 200) }}</p>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span>Type: {{ ucfirst(str_replace('_', ' ', $dispute->type)) }}</span>
                                    @if($dispute->assignedAdmin)
                                        <span>Assigned to: {{ $dispute->assignedAdmin->name }}</span>
                                    @endif
                                    @if($dispute->resolved_at)
                                        <span>Resolved: {{ $dispute->resolved_at->format('M d, Y') }}</span>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <a href="{{ route('disputes.show', $dispute) }}" 
                                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                        View Details
                                    </a>
                                    @if(in_array($dispute->status, ['open', 'in_review', 'waiting_response']))
                                        <button onclick="openMessageModal({{ $dispute->id }})" 
                                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                                            Add Message
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $disputes->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No disputes found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($status === 'all')
                            You haven't raised any disputes yet.
                        @else
                            No {{ str_replace('_', ' ', $status) }} disputes found.
                        @endif
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('disputes.help') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Learn About Disputes
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Quick Message Modal -->
<div id="message-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Message</h3>
                <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="message-form" method="POST">
                @csrf
                <div class="mb-4">
                    <textarea name="message" rows="4" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Type your message here..." required></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeMessageModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMessageModal(disputeId) {
    document.getElementById('message-form').action = `/disputes/${disputeId}/messages`;
    document.getElementById('message-modal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('message-modal').classList.add('hidden');
    document.getElementById('message-form').reset();
}
</script>
@endsection
