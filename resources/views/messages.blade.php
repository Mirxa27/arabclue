<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | HabibiStay</title>
    <meta name="description" content="Your messages and conversations on HabibiStay">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #2957c3;
            --brand-blue-light: #4a6cf7;
            --brand-blue-dark: #1e3a8a;
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, var(--brand-blue) 0%, var(--brand-blue-light) 100%);
        }
        
        .conversation-item {
            background: white;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .conversation-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        
        .conversation-item.unread {
            border-left: 4px solid var(--brand-blue);
            background: #f8faff;
        }
        
        .message-bubble {
            max-width: 80%;
            word-wrap: break-word;
            margin-bottom: 12px;
        }
        
        .message-sent {
            background: var(--brand-blue);
            color: white;
            border-radius: 18px 18px 4px 18px;
            margin-left: auto;
        }
        
        .message-received {
            background: #f1f5f9;
            color: #334155;
            border-radius: 18px 18px 18px 4px;
        }
        
        .chat-input {
            background: white;
            border-radius: 25px;
            border: 2px solid #e2e8f0;
            padding: 12px 20px;
            transition: border-color 0.3s ease;
        }
        
        .chat-input:focus {
            outline: none;
            border-color: var(--brand-blue);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        @media (max-width: 768px) {
            .messages-container {
                height: calc(100vh - 140px);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">HabibiStay</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-blue-600 transition-colors">Home</a>
                    <a href="/stays" class="text-gray-700 hover:text-blue-600 transition-colors">Stays</a>
                    <a href="/messages" class="text-blue-600 font-semibold">Messages</a>
                    <a href="/host" class="text-gray-700 hover:text-blue-600 transition-colors">Become a Host</a>
                    @auth
                        <a href="/profile" class="text-gray-700 hover:text-blue-600 transition-colors">Profile</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-blue-600 transition-colors">Logout</button>
                        </form>
                    @else
                        <a href="/login" class="text-gray-700 hover:text-blue-600 transition-colors">Login</a>
                        <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Sign Up</a>
                    @endauth
                </div>
                <div class="md:hidden">
                    <button onclick="window.history.back()" class="text-gray-700">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-20 pb-20 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Messages</h1>
                <p class="text-xl text-gray-600">Your conversations and notifications</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Conversations List -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Conversations</h2>
                            <button onclick="startNewConversation()" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <div id="conversationsList" class="space-y-3">
                            <!-- Loading state -->
                            <div id="conversationsLoading" class="text-center py-8">
                                <i class="fas fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                                <p class="text-gray-600">Loading conversations...</p>
                            </div>
                            
                            <!-- Empty state -->
                            <div id="conversationsEmpty" class="empty-state hidden">
                                <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No conversations yet</h3>
                                <p class="text-gray-600 mb-4">Start chatting with hosts or get help from Sara</p>
                                <button onclick="openSaraChat()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-robot mr-2"></i>Chat with Sara
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg h-96 lg:h-[600px] flex flex-col">
                        <!-- Chat Header -->
                        <div id="chatHeader" class="border-b p-4 hidden">
                            <div class="flex items-center space-x-3">
                                <img id="chatAvatar" src="" alt="" class="w-10 h-10 rounded-full">
                                <div>
                                    <h3 id="chatName" class="font-semibold text-gray-900"></h3>
                                    <p id="chatStatus" class="text-sm text-gray-600"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Area -->
                        <div id="messagesArea" class="flex-1 overflow-y-auto p-4 messages-container">
                            <!-- Welcome state -->
                            <div id="welcomeState" class="flex items-center justify-center h-full">
                                <div class="text-center">
                                    <i class="fas fa-comments text-6xl text-gray-300 mb-6"></i>
                                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">Welcome to Messages</h2>
                                    <p class="text-gray-600 mb-8">Select a conversation to start chatting</p>
                                    <div class="space-y-4">
                                        <button onclick="openSaraChat()" class="block bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                                            <i class="fas fa-robot mr-2"></i>Chat with Sara AI
                                        </button>
                                        <button onclick="contactSupport()" class="block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                            <i class="fas fa-headset mr-2"></i>Contact Support
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Chat messages will be loaded here -->
                            <div id="chatMessages" class="hidden space-y-4">
                                <!-- Messages will be dynamically loaded -->
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div id="chatInput" class="border-t p-4 hidden">
                            <div class="flex items-center space-x-3">
                                <input type="text" 
                                       id="messageInput" 
                                       placeholder="Type your message..." 
                                       class="chat-input flex-1"
                                       onkeypress="handleKeyPress(event)">
                                <button onclick="sendMessage()" 
                                        class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Footer Navigation -->
    @include('components.mobile-footer-nav')

    <script>
        let currentConversation = null;
        let conversations = [];

        document.addEventListener('DOMContentLoaded', function() {
            loadConversations();
        });

        function loadConversations() {
            @auth
                fetch('/api/v1/conversations')
                    .then(response => response.json())
                    .then(data => {
                        conversations = data.data || [];
                        displayConversations();
                    })
                    .catch(error => {
                        console.error('Error loading conversations:', error);
                        showEmptyConversations();
                    });
            @else
                // Redirect to login for guest users
                window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
            @endauth
        }

        function displayConversations() {
            document.getElementById('conversationsLoading').classList.add('hidden');
            
            if (conversations.length === 0) {
                showEmptyConversations();
                return;
            }

            document.getElementById('conversationsEmpty').classList.add('hidden');
            const container = document.getElementById('conversationsList');
            
            // Clear loading state
            container.innerHTML = '';

            conversations.forEach(conversation => {
                const conversationItem = createConversationItem(conversation);
                container.appendChild(conversationItem);
            });
        }

        function showEmptyConversations() {
            document.getElementById('conversationsLoading').classList.add('hidden');
            document.getElementById('conversationsEmpty').classList.remove('hidden');
        }

        function createConversationItem(conversation) {
            const item = document.createElement('div');
            item.className = `conversation-item ${conversation.unread_count > 0 ? 'unread' : ''}`;
            item.onclick = () => selectConversation(conversation);
            
            const lastMessage = conversation.last_message;
            const timeAgo = formatTimeAgo(new Date(lastMessage?.created_at || conversation.updated_at));
            
            item.innerHTML = `
                <div class="flex items-start space-x-3">
                    <img src="${conversation.participant?.avatar || '/images/default-avatar.jpg'}" 
                         alt="${conversation.participant?.name || 'User'}" 
                         class="w-12 h-12 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900 truncate">
                                ${conversation.participant?.name || 'Support'}
                            </h3>
                            <span class="text-xs text-gray-500">${timeAgo}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate mt-1">
                            ${lastMessage?.content || 'No messages yet'}
                        </p>
                        ${conversation.unread_count > 0 ? 
                            `<div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-blue-600 font-medium">
                                    ${conversation.type === 'booking' ? 'Booking inquiry' : 'Message'}
                                </span>
                                <span class="bg-blue-600 text-white text-xs rounded-full px-2 py-1">
                                    ${conversation.unread_count}
                                </span>
                            </div>` : ''
                        }
                    </div>
                </div>
            `;
            
            return item;
        }

        function selectConversation(conversation) {
            currentConversation = conversation;
            
            // Update UI
            document.getElementById('welcomeState').classList.add('hidden');
            document.getElementById('chatHeader').classList.remove('hidden');
            document.getElementById('chatMessages').classList.remove('hidden');
            document.getElementById('chatInput').classList.remove('hidden');
            
            // Update header
            document.getElementById('chatAvatar').src = conversation.participant?.avatar || '/images/default-avatar.jpg';
            document.getElementById('chatName').textContent = conversation.participant?.name || 'Support';
            document.getElementById('chatStatus').textContent = conversation.participant?.is_online ? 'Online' : 'Last seen recently';
            
            // Load messages
            loadMessages(conversation.id);
        }

        function loadMessages(conversationId) {
            fetch(`/api/v1/conversations/${conversationId}/messages`)
                .then(response => response.json())
                .then(data => {
                    displayMessages(data.data || []);
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                });
        }

        function displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            container.innerHTML = '';
            
            messages.forEach(message => {
                const messageElement = createMessageElement(message);
                container.appendChild(messageElement);
            });
            
            // Scroll to bottom
            container.scrollTop = container.scrollHeight;
        }

        function createMessageElement(message) {
            const div = document.createElement('div');
            const isOwn = message.sender_id === {{ auth()->id() ?? 'null' }};
            
            div.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;
            div.innerHTML = `
                <div class="message-bubble ${isOwn ? 'message-sent' : 'message-received'} p-3">
                    <p>${message.content}</p>
                    <div class="text-xs opacity-75 mt-1">
                        ${formatTime(new Date(message.created_at))}
                    </div>
                </div>
            `;
            
            return div;
        }

        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !currentConversation) return;
            
            // Add message to UI immediately
            const messageElement = createMessageElement({
                content: message,
                sender_id: {{ auth()->id() ?? 'null' }},
                created_at: new Date().toISOString()
            });
            document.getElementById('chatMessages').appendChild(messageElement);
            
            // Clear input
            input.value = '';
            
            // Scroll to bottom
            const container = document.getElementById('chatMessages');
            container.scrollTop = container.scrollHeight;
            
            // Send to server
            fetch(`/api/v1/conversations/send-message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    conversation_id: currentConversation.id,
                    content: message
                })
            })
            .catch(error => {
                console.error('Error sending message:', error);
                // Could add error handling here
            });
        }

        function startNewConversation() {
            // This would typically open a modal to select a host or start a support conversation
            alert('Feature coming soon: Start new conversation');
        }

        function openSaraChat() {
            window.location.href = '/sara';
        }

        function contactSupport() {
            // Create a support conversation
            fetch('/api/v1/conversations/support', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadConversations();
                }
            })
            .catch(error => console.error('Error creating support conversation:', error));
        }

        function formatTimeAgo(date) {
            const now = new Date();
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffInMinutes < 1) return 'Just now';
            if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
            if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
            return `${Math.floor(diffInMinutes / 1440)}d ago`;
        }

        function formatTime(date) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    </script>
</body>
</html>
