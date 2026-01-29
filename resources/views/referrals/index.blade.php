@extends('layouts.app')

@section('title', 'Referral Program')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Referral Program</h1>
            <p class="text-gray-600">Earn credits by inviting friends to HabibiStay</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button onclick="openInviteModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                <i class="fas fa-paper-plane mr-2"></i>
                Send Invitations
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Available Credits</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($availableCredits, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Referred</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_referred'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-user-check text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Successful Signups</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['successful_signups'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-trophy text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Earned</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($stats['total_earned'], 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Referral Link</h2>
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Referral Code</label>
                <div class="flex">
                    <input type="text" id="referralCode" value="{{ $referralCode }}" readonly 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                    <button onclick="copyToClipboard('referralCode')" 
                            class="px-4 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Referral Link</label>
                <div class="flex">
                    <input type="text" id="referralLink" value="{{ $referralLink }}" readonly 
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50">
                    <button onclick="copyToClipboard('referralLink')" 
                            class="px-4 py-2 bg-gray-200 border border-l-0 border-gray-300 rounded-r-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-4 flex gap-4">
            <button onclick="shareViaWhatsApp()" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <i class="fab fa-whatsapp mr-2"></i>
                WhatsApp
            </button>
            <button onclick="shareViaFacebook()" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fab fa-facebook-f mr-2"></i>
                Facebook
            </button>
            <button onclick="shareViaTwitter()" class="flex items-center px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors">
                <i class="fab fa-twitter mr-2"></i>
                Twitter
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recentActivity->count() > 0)
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h2>
        <div class="space-y-4">
            @foreach($recentActivity as $activity)
            <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div class="flex items-center">
                    <div class="bg-green-100 p-2 rounded-full mr-3">
                        <i class="fas fa-user-plus text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">
                            {{ $activity->referred_user ? $activity->referred_user->name : $activity->email }} 
                            @if($activity->status === 'signed_up')
                                signed up
                            @elseif($activity->status === 'completed')
                                completed their first booking
                            @elseif($activity->status === 'credited')
                                earned you credits
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">{{ $activity->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
                <div class="text-right">
                    @if($activity->status === 'credited')
                        <span class="text-green-600 font-medium">${{ config('referrals.referrer_credit', 25) }}</span>
                    @else
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">{{ ucfirst($activity->status) }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Referral History -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Referral History</h2>
        </div>
        <div class="p-6">
            @if($referrals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Invited User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date Invited
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Credits Earned
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($referrals as $referral)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $referral->referred_user ? $referral->referred_user->name : 'Pending' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $referral->email }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($referral->status === 'credited') bg-green-100 text-green-800
                                        @elseif($referral->status === 'completed') bg-blue-100 text-blue-800
                                        @elseif($referral->status === 'signed_up') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $referral->created_at->format('M j, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($referral->status === 'credited')
                                        <span class="text-green-600 font-medium">${{ config('referrals.referrer_credit', 25) }}</span>
                                    @else
                                        <span class="text-gray-400">$0</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">
                    {{ $referrals->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-users text-4xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No referrals yet</h3>
                    <p class="text-gray-600 mb-4">Start inviting friends to earn credits!</p>
                    <button onclick="openInviteModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        Send Your First Invitation
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Invite Modal -->
<div id="inviteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Send Invitations</h3>
                <button onclick="closeInviteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="inviteForm" action="{{ route('referrals.send-invites') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                    <textarea name="emails_text" id="emailsText" rows="4" 
                              placeholder="Enter email addresses, separated by commas or new lines..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs text-gray-500 mt-1">You can invite up to 10 people at once</p>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Personal Message (Optional)</label>
                    <textarea name="personal_message" rows="3" maxlength="500"
                              placeholder="Add a personal message to your invitation..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeInviteModal()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Send Invitations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openInviteModal() {
    document.getElementById('inviteModal').classList.remove('hidden');
}

function closeInviteModal() {
    document.getElementById('inviteModal').classList.add('hidden');
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    
    // Show feedback
    const button = element.nextElementSibling;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>';
    setTimeout(() => {
        button.innerHTML = originalHTML;
    }, 2000);
}

function shareViaWhatsApp() {
    const text = `Join HabibiStay and get ${{ config('referrals.signup_credit', 25) }} credit! Use my referral link: {{ $referralLink }}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
}

function shareViaFacebook() {
    const url = '{{ $referralLink }}';
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
}

function shareViaTwitter() {
    const text = `Join HabibiStay and get ${{ config('referrals.signup_credit', 25) }} credit!`;
    const url = '{{ $referralLink }}';
    window.open(`https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`, '_blank');
}

// Handle form submission
document.getElementById('inviteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const emailsText = document.getElementById('emailsText').value;
    const emails = emailsText.split(/[,\n]/).map(email => email.trim()).filter(email => email);
    
    if (emails.length === 0) {
        alert('Please enter at least one email address');
        return;
    }
    
    if (emails.length > 10) {
        alert('You can invite up to 10 people at once');
        return;
    }
    
    // Create hidden inputs for emails array
    emails.forEach((email, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `emails[${index}]`;
        input.value = email;
        this.appendChild(input);
    });
    
    this.submit();
});
</script>
@endsection