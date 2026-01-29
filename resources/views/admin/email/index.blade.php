@extends('layouts.admin')

@section('title', 'Email Management')

@section('content')
<div class="container-fluid px-6 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Email Management</h1>
            <p class="text-gray-600">Manage SMTP settings, send bulk emails, and monitor email performance</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
            <a href="{{ route('admin.email.smtp-config') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-cog mr-2"></i>
                SMTP Settings
            </a>
            <a href="{{ route('admin.email.templates') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                <i class="fas fa-envelope mr-2"></i>
                Email Templates
            </a>
        </div>
    </div>

    <!-- SMTP Status Alert -->
    @if(!$smtpStatus['configured'])
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>SMTP Configuration Required:</strong> 
                    Email functionality is not properly configured. 
                    <a href="{{ route('admin.email.smtp-config') }}" class="underline font-medium">Configure SMTP settings</a>
                    to enable email sending.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-paper-plane text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today']['sent']) }}</p>
                    <p class="text-xs text-gray-500">emails sent</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-envelope-open text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Opens Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today']['opened']) }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $stats['today']['sent'] > 0 ? number_format(($stats['today']['opened'] / $stats['today']['sent']) * 100, 1) : 0 }}% rate
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-full">
                    <i class="fas fa-mouse-pointer text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Clicks Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['today']['clicked']) }}</p>
                    <p class="text-xs text-gray-500">
                        {{ $stats['today']['sent'] > 0 ? number_format(($stats['today']['clicked'] / $stats['today']['sent']) * 100, 1) : 0 }}% rate
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-full">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Queue</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($queueSize) }}</p>
                    <p class="text-xs text-gray-500">pending emails</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $failedEmails->count() }}</p>
                    <p class="text-xs text-gray-500">failed emails</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Send Bulk Email -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Send Bulk Email</h2>
            <form action="{{ route('admin.email.bulk-send') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                        <select name="recipients" id="recipients" onchange="toggleCustomEmails()" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all_users">All Users</option>
                            <option value="verified_users">Verified Users Only</option>
                            <option value="hosts">Hosts Only</option>
                            <option value="guests">Guests Only</option>
                            <option value="custom">Custom Email List</option>
                        </select>
                    </div>

                    <div id="customEmailsDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Addresses</label>
                        <textarea name="custom_emails_text" rows="3" 
                                  placeholder="Enter email addresses separated by commas or new lines..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Maximum 1000 recipients</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" name="subject" required maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea name="message" rows="6" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        <p class="text-xs text-gray-500 mt-1">HTML is supported</p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="send_immediately" value="1" checked
                                   class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Send Immediately</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="send_immediately" value="0" onchange="toggleSchedule()"
                                   class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Schedule for Later</span>
                        </label>
                    </div>

                    <div id="scheduleDiv" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Date & Time</label>
                        <input type="datetime-local" name="schedule_at" min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md font-medium transition-colors">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Send Email
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Links -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="space-y-3">
                <a href="{{ route('admin.email.analytics') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chart-bar text-blue-600 text-xl mr-3"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Email Analytics</h3>
                        <p class="text-sm text-gray-600">View detailed email performance metrics</p>
                    </div>
                </a>

                <a href="{{ route('admin.email.queue') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-list-ul text-green-600 text-xl mr-3"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Email Queue</h3>
                        <p class="text-sm text-gray-600">Manage pending and failed email jobs</p>
                    </div>
                </a>

                <a href="{{ route('admin.email.templates') }}" 
                   class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-file-alt text-purple-600 text-xl mr-3"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Email Templates</h3>
                        <p class="text-sm text-gray-600">Manage and preview email templates</p>
                    </div>
                </a>

                <button onclick="testSMTP()" 
                        class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors w-full text-left">
                    <i class="fas fa-vial text-yellow-600 text-xl mr-3"></i>
                    <div>
                        <h3 class="font-medium text-gray-900">Test SMTP</h3>
                        <p class="text-sm text-gray-600">Send a test email to verify configuration</p>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Emails -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">Recent Email Jobs</h2>
            </div>
            <div class="p-6">
                @if($recentEmails->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentEmails->take(10) as $email)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Email Job #{{ $email->id }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($email->created_at)->diffForHumans() }}</p>
                            </div>
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                Queued
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No recent email activity</p>
                @endif
            </div>
        </div>

        <!-- Failed Emails -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Failed Emails</h2>
                @if($failedEmails->count() > 0)
                <button onclick="retryAllFailed()" 
                        class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
                    Retry All
                </button>
                @endif
            </div>
            <div class="p-6">
                @if($failedEmails->count() > 0)
                    <div class="space-y-3">
                        @foreach($failedEmails->take(10) as $failed)
                        <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Failed Job #{{ $failed->id }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($failed->failed_at)->diffForHumans() }}</p>
                            </div>
                            <button onclick="retryJob({{ $failed->id }})" 
                                    class="text-xs bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded">
                                Retry
                            </button>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No failed emails</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Test SMTP Modal -->
<div id="testSMTPModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Test SMTP Configuration</h3>
                <button onclick="closeTestSMTP()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="testSMTPForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Test Email Address</label>
                    <input type="email" name="test_email" required
                           placeholder="Enter email address to send test email..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeTestSMTP()" 
                            class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Send Test Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleCustomEmails() {
    const recipients = document.getElementById('recipients').value;
    const customDiv = document.getElementById('customEmailsDiv');
    
    if (recipients === 'custom') {
        customDiv.classList.remove('hidden');
    } else {
        customDiv.classList.add('hidden');
    }
}

function toggleSchedule() {
    const scheduleDiv = document.getElementById('scheduleDiv');
    const isScheduled = document.querySelector('input[name="send_immediately"]:checked').value === '0';
    
    if (isScheduled) {
        scheduleDiv.classList.remove('hidden');
    } else {
        scheduleDiv.classList.add('hidden');
    }
}

function testSMTP() {
    document.getElementById('testSMTPModal').classList.remove('hidden');
}

function closeTestSMTP() {
    document.getElementById('testSMTPModal').classList.add('hidden');
}

// Handle test SMTP form submission
document.getElementById('testSMTPForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.email.test-smtp") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test email sent successfully!');
            closeTestSMTP();
        } else {
            alert('Failed to send test email: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending test email');
    });
});

function retryJob(jobId) {
    if (confirm('Retry this failed email job?')) {
        fetch('{{ route("admin.email.retry-failed") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                job_ids: [jobId]
            })
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to retry job');
            }
        });
    }
}

function retryAllFailed() {
    if (confirm('Retry all failed email jobs?')) {
        const failedIds = Array.from(document.querySelectorAll('[onclick^="retryJob"]'))
            .map(btn => btn.getAttribute('onclick').match(/\d+/)[0]);
        
        fetch('{{ route("admin.email.retry-failed") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                job_ids: failedIds
            })
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to retry jobs');
            }
        });
    }
}

// Handle bulk email form submission to process custom emails
document.querySelector('form[action="{{ route("admin.email.bulk-send") }}"]').addEventListener('submit', function(e) {
    const recipients = document.getElementById('recipients').value;
    
    if (recipients === 'custom') {
        const emailsText = document.querySelector('textarea[name="custom_emails_text"]').value;
        const emails = emailsText.split(/[,\n]/).map(email => email.trim()).filter(email => email);
        
        if (emails.length === 0) {
            e.preventDefault();
            alert('Please enter at least one email address');
            return;
        }
        
        if (emails.length > 1000) {
            e.preventDefault();
            alert('Maximum 1000 recipients allowed');
            return;
        }
        
        // Create hidden inputs for emails array
        emails.forEach((email, index) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `custom_emails[${index}]`;
            input.value = email;
            this.appendChild(input);
        });
    }
});
</script>
@endsection