<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- PWA Meta Tags --}}
    <meta name="theme-color" content="#2957c3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    
    {{-- Dynamic Meta Tags --}}
    <title>@yield('title', 'HabibiStay - Exceptional Stays. Exceptional Returns.')</title>
    <meta name="description" content="@yield('description', 'Book memorable getaways, unlock steady income, and grow your capital—all with HabibiStay. Premium property rentals in Riyadh, Saudi Arabia.')">
    
    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', 'HabibiStay - Exceptional Stays. Exceptional Returns.')">
    <meta property="og:description" content="@yield('og_description', 'Book memorable getaways, unlock steady income, and grow your capital—all with HabibiStay.')">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/habibistay-og.jpg'))">
    
    {{-- Icons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Styles --}}
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    {{-- Dynamic Theme Styles --}}
    {{-- @if($activeTheme = \App\Models\Theme::active())
    <style id="theme-styles">
        {!! $activeTheme->compileCss() !!}
    </style>
    @endif --}}
    
    {{-- Custom Styles --}}
    <style>
        /* PWA Safe Area Support */
        .safe-top { padding-top: env(safe-area-inset-top); }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        .safe-left { padding-left: env(safe-area-inset-left); }
        .safe-right { padding-right: env(safe-area-inset-right); }
        
        /* Mobile Touch Optimizations */
        .touch-manipulation { touch-action: manipulation; }
        .touch-none { touch-action: none; }
        
        /* Smooth Scrolling */
        html { scroll-behavior: smooth; }
        .scroll-smooth { scroll-behavior: smooth; }
        
        /* Hide Scrollbar but Keep Functionality */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        
        /* Bottom Navigation Safe Area */
        .bottom-nav {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
        }
        
        /* Loading Skeleton Animation */
        @keyframes skeleton-loading {
            0% { background-color: #f3f4f6; }
            50% { background-color: #e5e7eb; }
            100% { background-color: #f3f4f6; }
        }
        .skeleton {
            animation: skeleton-loading 1.5s infinite ease-in-out;
        }
        
        /* PWA Install Prompt */
        .install-prompt {
            transform: translateY(100%);
            transition: transform 0.3s ease-out;
        }
        .install-prompt.show {
            transform: translateY(0);
        }
    </style>
    
    @yield('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    {{-- PWA Install Prompt --}}
    <div id="install-prompt" class="install-prompt fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 p-4 safe-bottom z-50 hidden">
        <div class="flex items-center justify-between max-w-lg mx-auto">
            <div class="flex items-center space-x-3">
                <img src="/assets/icons/icon-72x72.png" alt="HabibiStay" class="w-12 h-12 rounded-lg">
                <div>
                    <p class="font-semibold text-sm">Install HabibiStay App</p>
                    <p class="text-xs text-gray-600">Add to home screen for better experience</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="dismissInstallPrompt()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
                <button onclick="installPWA()" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                    Install
                </button>
            </div>
        </div>
    </div>
    
    {{-- Mobile Header --}}
    <header class="lg:hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-40 safe-top">
        <div class="flex items-center justify-between px-4 py-3">
            <button onclick="toggleMobileMenu()" class="touch-manipulation" aria-label="Open menu">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <a href="/" class="flex items-center" aria-label="HabibiStay Home">
                <img src="/assets/images/logo.svg" alt="HabibiStay" class="h-8">
            </a>
            <button onclick="toggleSearch()" class="touch-manipulation" aria-label="Open search">
                <i class="fas fa-search text-xl"></i>
            </button>
        </div>
    </header>
    
    {{-- Desktop Header --}}
    <header class="hidden lg:block fixed top-0 left-0 right-0 bg-white shadow-sm z-40">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="/" class="flex items-center">
                        <img src="/assets/images/logo.svg" alt="HabibiStay" class="h-10">
                    </a>
                    <nav class="hidden lg:flex space-x-6">
                        <a href="/stays" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">Stays</a>
                        <a href="/how-it-works" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">How it Works</a>
                        <a href="/invest" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">Invest</a>
                        <a href="/about" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">About</a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    @guest
                    <a href="/login" class="text-gray-700 hover:text-purple-600 font-medium transition-colors">Login</a>
                    <a href="/register" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                        Sign Up
                    </a>
                    @else
                    <button onclick="toggleNotifications()" class="relative text-gray-700 hover:text-purple-600 transition-colors">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </button>
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-700 hover:text-purple-600 transition-colors">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full">
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                    </div>
                    @endguest
                </div>
            </div>
        </div>
    </header>
    
    {{-- Mobile Menu Overlay --}}
    <div id="mobile-menu" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="bg-white w-80 h-full overflow-y-auto safe-left">
            <div class="p-4 border-b border-gray-200 safe-top">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Menu</h2>
                    <button onclick="toggleMobileMenu()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <nav class="p-4">
                @auth
                <div class="mb-6 pb-6 border-b border-gray-200">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-12 h-12 rounded-full">
                        <div>
                            <p class="font-semibold">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-600">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    <a href="/profile" class="block py-2 text-gray-700 hover:text-purple-600">
                        <i class="fas fa-user w-5 mr-3"></i> My Profile
                    </a>
                    <a href="/bookings" class="block py-2 text-gray-700 hover:text-purple-600">
                        <i class="fas fa-calendar w-5 mr-3"></i> My Bookings
                    </a>
                    <a href="/wishlist" class="block py-2 text-gray-700 hover:text-purple-600">
                        <i class="fas fa-heart w-5 mr-3"></i> Wishlist
                    </a>
                </div>
                @endauth
                
                <div class="space-y-2">
                    <a href="/stays" class="block py-3 text-gray-700 hover:text-purple-600 font-medium">
                        <i class="fas fa-home w-5 mr-3"></i> Find Stays
                    </a>
                    <a href="/host" class="block py-3 text-gray-700 hover:text-purple-600 font-medium">
                        <i class="fas fa-plus-circle w-5 mr-3"></i> List Your Property
                    </a>
                    <a href="/invest" class="block py-3 text-gray-700 hover:text-purple-600 font-medium">
                        <i class="fas fa-chart-line w-5 mr-3"></i> Investment Opportunities
                    </a>
                    <a href="/how-it-works" class="block py-3 text-gray-700 hover:text-purple-600 font-medium">
                        <i class="fas fa-info-circle w-5 mr-3"></i> How It Works
                    </a>
                    <a href="/about" class="block py-3 text-gray-700 hover:text-purple-600 font-medium">
                        <i class="fas fa-users w-5 mr-3"></i> About Us
                    </a>
                    <a href="/contact" class="block py-3 text-gray-700 hover:text-purple-600 font-medium">
                        <i class="fas fa-envelope w-5 mr-3"></i> Contact
                    </a>
                </div>
                
                @guest
                <div class="mt-6 pt-6 border-t border-gray-200 space-y-3">
                    <a href="/login" class="block w-full text-center py-3 border border-purple-600 text-purple-600 rounded-lg font-medium hover:bg-purple-50 transition-colors">
                        Login
                    </a>
                    <a href="/register" class="block w-full text-center py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                        Sign Up
                    </a>
                </div>
                @else
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left py-3 text-red-600 hover:text-red-700 font-medium">
                            <i class="fas fa-sign-out-alt w-5 mr-3"></i> Logout
                        </button>
                    </form>
                </div>
                @endauth
            </nav>
        </div>
    </div>
    
    {{-- Main Content --}}
    <main class="pt-16 lg:pt-20 min-h-screen">
        @yield('content')
    </main>
    
    {{-- Mobile Bottom Navigation --}}
    {{-- <x-mobile-footer-nav /> --}}
    
    {{-- Sara Chatbot Modal --}}
    <div id="sara-chat" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="absolute inset-x-0 bottom-0 lg:inset-auto lg:right-8 lg:bottom-8 lg:w-96 lg:h-[600px] bg-white rounded-t-2xl lg:rounded-2xl shadow-2xl flex flex-col" style="max-height: 90vh;">
            {{-- Chat Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-2 rounded-full">
                        <i class="fas fa-robot text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold">Sara</h3>
                        <p class="text-xs text-gray-600">AI Booking Assistant</p>
                    </div>
                </div>
                <button onclick="closeSaraChat()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            {{-- Chat Messages --}}
            <div id="sara-messages" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-hide">
                <div class="flex items-start space-x-3">
                    <div class="bg-purple-100 p-2 rounded-full flex-shrink-0">
                        <i class="fas fa-robot text-purple-600"></i>
                    </div>
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none p-4 max-w-[80%]">
                        <p class="text-sm">Hi! I'm Sara, your AI assistant. I can help you find the perfect property, make bookings, or answer any questions. What can I help you with today?</p>
                    </div>
                </div>
            </div>
            
            {{-- Quick Actions --}}
            <div class="px-4 py-2 border-t border-gray-100">
                <div class="flex space-x-2 overflow-x-auto scrollbar-hide">
                    <button onclick="sendQuickAction('search')" class="bg-gray-100 px-4 py-2 rounded-full text-sm whitespace-nowrap hover:bg-gray-200 transition-colors">
                        🔍 Search Properties
                    </button>
                    <button onclick="sendQuickAction('bookings')" class="bg-gray-100 px-4 py-2 rounded-full text-sm whitespace-nowrap hover:bg-gray-200 transition-colors">
                        📅 My Bookings
                    </button>
                    <button onclick="sendQuickAction('help')" class="bg-gray-100 px-4 py-2 rounded-full text-sm whitespace-nowrap hover:bg-gray-200 transition-colors">
                        ❓ Help
                    </button>
                </div>
            </div>
            
            {{-- Chat Input --}}
            <div class="p-4 border-t border-gray-200 safe-bottom">
                <form onsubmit="sendSaraMessage(event)" class="flex items-center space-x-2">
                    <input type="text" id="sara-input" placeholder="Type your message..." 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-purple-600 focus:border-transparent">
                    <button type="submit" class="bg-purple-600 text-white p-2 rounded-full hover:bg-purple-700 transition-colors">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Toast Notifications --}}
    <div id="toast-container" class="fixed top-20 right-4 z-50 space-y-2"></div>
    
    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                <span class="font-medium">Loading...</span>
            </div>
        </div>
    </div>
    
    {{-- Scripts --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    
    {{-- PWA Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(registration => console.log('SW registered:', registration))
                    .catch(error => console.log('SW registration failed:', error));
            });
        }
        
        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('install-prompt').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('install-prompt').classList.add('show');
            }, 100);
        });
        
        function installPWA() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('User accepted the install prompt');
                    }
                    deferredPrompt = null;
                    dismissInstallPrompt();
                });
            }
        }
        
        function dismissInstallPrompt() {
            document.getElementById('install-prompt').classList.remove('show');
            setTimeout(() => {
                document.getElementById('install-prompt').classList.add('hidden');
            }, 300);
        }
    </script>
    
    {{-- Main Application Scripts --}}
    <script>
        // Setup CSRF token for axios
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }
        
        // Sara Chat Functions
        let saraConversationId = null;
        
        function openSaraChat() {
            document.getElementById('sara-chat').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            
            // Initialize conversation if not already started
            if (!saraConversationId) {
                initializeSaraConversation();
            }
        }
        
        function closeSaraChat() {
            document.getElementById('sara-chat').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        
        async function initializeSaraConversation() {
            try {
                const response = await axios.post('/api/sara/start', {
                    channel: 'web',
                    context: {
                        page: window.location.pathname,
                        viewport: {
                            width: window.innerWidth,
                            height: window.innerHeight
                        }
                    }
                });
                
                saraConversationId = response.data.data.conversation.id;
                
                // Load conversation history
                if (response.data.data.messages.length > 0) {
                    response.data.data.messages.forEach(msg => {
                        appendSaraMessage(msg.content, msg.role);
                    });
                }
            } catch (error) {
                console.error('Failed to initialize Sara:', error);
                showToast('Failed to start chat. Please try again.', 'error');
            }
        }
        
        async function sendSaraMessage(event) {
            event.preventDefault();
            
            const input = document.getElementById('sara-input');
            const message = input.value.trim();
            
            if (!message || !saraConversationId) return;
            
            // Add user message to chat
            appendSaraMessage(message, 'user');
            input.value = '';
            
            // Show typing indicator
            showSaraTyping();
            
            try {
                const response = await axios.post('/api/sara/message', {
                    conversation_id: saraConversationId,
                    message: message
                });
                
                hideSaraTyping();
                
                // Add Sara's response
                appendSaraMessage(response.data.data.response.message, 'assistant');
                
                // Handle suggested actions
                if (response.data.data.response.suggested_actions) {
                    showSuggestedActions(response.data.data.response.suggested_actions);
                }
                
                // Handle property cards or other data
                if (response.data.data.response.data) {
                    handleSaraData(response.data.data.response.data);
                }
                
            } catch (error) {
                hideSaraTyping();
                console.error('Failed to send message:', error);
                appendSaraMessage('Sorry, I encountered an error. Please try again.', 'assistant');
            }
        }
        
        function appendSaraMessage(content, role) {
            const messagesContainer = document.getElementById('sara-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start space-x-3';
            
            if (role === 'user') {
                messageDiv.innerHTML = `
                    <div class="ml-auto bg-purple-600 text-white rounded-2xl rounded-tr-none p-4 max-w-[80%]">
                        <p class="text-sm">${escapeHtml(content)}</p>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="bg-purple-100 p-2 rounded-full flex-shrink-0">
                        <i class="fas fa-robot text-purple-600"></i>
                    </div>
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none p-4 max-w-[80%]">
                        <p class="text-sm">${content}</p>
                    </div>
                `;
            }
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        
        function showSaraTyping() {
            const indicator = document.createElement('div');
            indicator.id = 'sara-typing';
            indicator.className = 'flex items-start space-x-3';
            indicator.innerHTML = `
                <div class="bg-purple-100 p-2 rounded-full flex-shrink-0">
                    <i class="fas fa-robot text-purple-600"></i>
                </div>
                <div class="bg-gray-100 rounded-2xl rounded-tl-none p-4">
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
                    </div>
                </div>
            `;
            document.getElementById('sara-messages').appendChild(indicator);
        }
        
        function hideSaraTyping() {
            const typing = document.getElementById('sara-typing');
            if (typing) typing.remove();
        }
        
        function sendQuickAction(action) {
            const actions = {
                'search': 'I want to search for properties',
                'bookings': 'Show me my bookings',
                'help': 'I need help'
            };
            
            document.getElementById('sara-input').value = actions[action] || '';
            sendSaraMessage(new Event('submit'));
        }
        
        // Toast Notifications
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-500' :
                type === 'error' ? 'bg-red-500' :
                type === 'warning' ? 'bg-yellow-500' :
                'bg-blue-500'
            } text-white`;
            
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${
                        type === 'success' ? 'fa-check-circle' :
                        type === 'error' ? 'fa-times-circle' :
                        type === 'warning' ? 'fa-exclamation-circle' :
                        'fa-info-circle'
                    }"></i>
                    <span>${escapeHtml(message)}</span>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
                toast.classList.add('translate-x-0');
            }, 100);
            
            // Remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
        
        // Utility Functions
        function escapeHtml(text) {
            const map = {
                '&': '&',
                '<': '<',
                '>': '>',
                '"': '"',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
        
        // Mobile Touch Optimizations
        document.addEventListener('DOMContentLoaded', () => {
            // Disable pull-to-refresh on mobile
            document.body.addEventListener('touchmove', (e) => {
                if (e.target.closest('.scrollable')) {
                    return;
                }
                if (window.scrollY === 0 && e.touches[0].clientY > 0) {
                    e.preventDefault();
                }
            }, { passive: false });
            
            // Fast click for mobile
            if ('ontouchstart' in window) {
                document.body.addEventListener('touchstart', () => {}, { passive: true });
            }
        });
        
        // Dynamic Theme Loading
        function loadTheme(themeId) {
            axios.get(`/api/themes/${themeId}/css`)
                .then(response => {
                    document.getElementById('theme-styles').innerHTML = response.data.css;
                })
                .catch(error => console.error('Failed to load theme:', error));
        }
    </script>
    
    @yield('scripts')
</body>
</html>
