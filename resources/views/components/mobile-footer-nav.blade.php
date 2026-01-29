<!-- Mobile Footer Navigation -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 md:hidden z-50">
    <div class="grid grid-cols-5 h-16">
        <!-- Home -->
        <a href="/"
           class="flex flex-col items-center justify-center space-y-1 {{ request()->is('/') ? 'text-blue-600' : 'text-gray-500' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="text-xs font-medium">Home</span>
        </a>

        <!-- Stays -->
        <a href="/stays"
           class="flex flex-col items-center justify-center space-y-1 {{ request()->is('stays*') ? 'text-blue-600' : 'text-gray-500' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span class="text-xs font-medium">Stays</span>
        </a>

        <!-- Sara Chatbot -->
        <a href="/sara"
           class="flex flex-col items-center justify-center space-y-1 {{ request()->is('sara*') ? 'text-blue-600' : 'text-gray-500' }} relative">
            <div class="relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <!-- Notification dot for new messages -->
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
            </div>
            <span class="text-xs font-medium">Sara</span>
        </a>

        <!-- Messages -->
        @auth
            <a href="/messages"
               class="flex flex-col items-center justify-center space-y-1 {{ request()->is('messages*') ? 'text-blue-600' : 'text-gray-500' }} relative">
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <!-- Unread messages count -->
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
                            {{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </div>
                <span class="text-xs font-medium">Messages</span>
            </a>
        @else
            <a href="/login"
               class="flex flex-col items-center justify-center space-y-1 text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <span class="text-xs font-medium">Messages</span>
            </a>
        @endauth

        <!-- Profile -->
        @auth
            <a href="/profile"
               class="flex flex-col items-center justify-center space-y-1 {{ request()->is('profile*') ? 'text-blue-600' : 'text-gray-500' }}">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}"
                         alt="Profile"
                         class="w-5 h-5 rounded-full object-cover">
                @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                @endif
                <span class="text-xs font-medium">Profile</span>
            </a>
        @else
            <a href="/login"
               class="flex flex-col items-center justify-center space-y-1 text-gray-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-xs font-medium">Login</span>
            </a>
        @endauth
    </div>
</div>


