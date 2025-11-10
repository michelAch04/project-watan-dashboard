<!-- Bottom Navigation (Mobile) -->
<nav class="bottom-nav">
    <div class="bottom-nav-container">
        <div class="flex items-center justify-around">
            <!-- Home -->
            <a href="{{ route('dashboard') }}"
                class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('dashboard') ? 'currentColor' : 'none' }}"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-xs font-medium mt-1">Home</span>
                @if(request()->routeIs('dashboard'))
                <span class="bottom-nav-indicator"></span>
                @endif
            </a>

            <!-- Profile -->
            <a href="{{ route('profile') }}"
                class="bottom-nav-item {{ request()->routeIs('profile') ? 'active' : '' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-xs font-medium mt-1">Profile</span>
                @if(request()->routeIs('profile'))
                <span class="bottom-nav-indicator"></span>
                @endif
            </a>

            <!-- Find the inbox nav item and update it -->
            @can('view_inbox')
            <a href="{{ route('inbox.index') }}"
                class="bottom-nav-item {{ request()->routeIs('inbox.*') ? 'active' : '' }}"
                x-data="{ unreadCount: 0 }"
                x-init="async function() {
                    try {
                        const response = await fetch('{{ route('inbox.unread-count') }}');
                        const data = await response.json();
                        unreadCount = data.count;
                        console.log(unreadCount);
                    } catch (error) {
                        console.error('Failed to fetch unread count:', error);
                    }
                    // Poll every 30 seconds
                    setInterval(async () => {
                        try {
                            const response = await fetch('{{ route('inbox.unread-count') }}');
                            const data = await response.json();
                            unreadCount = data.count;
                        } catch (error) {
                            console.error('Failed to fetch unread count:', error);
                        }
                    }, 30000);
                }">
                <div class="relative">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>

                    <span x-show="unreadCount > 0"
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
                        x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                </div>
                <span class="text-xs font-medium mt-1">Inbox</span>

                @if(request()->routeIs('inbox.*'))
                <span class="bottom-nav-indicator"></span>
                @endif
            </a>
            @endcan

            <!-- Settings/Menu -->
            <button @click="$dispatch('open-menu')" class="bottom-nav-item">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span class="text-xs font-medium mt-1">Menu</span>
            </button>
        </div>
    </div>
</nav>