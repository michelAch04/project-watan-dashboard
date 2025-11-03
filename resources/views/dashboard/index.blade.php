@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div
    x-data="{ show: false }"
    x-init="() => {
         $nextTick(() => show = true);
         // Remove transition overlay if it exists
         const overlay = document.querySelector('.transition-overlay');
         if (overlay) {
             overlay.style.opacity = '0';
             setTimeout(() => overlay.remove(), 500);
         }
     }"
    x-show="show"
    x-transition:enter="page-transition-enter-active"
    x-transition:enter-start="page-transition-enter"
    style="display: none;">
    <!-- Mobile Header -->
    <header class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center justify-between">
                    <!-- User Info -->
                    <div class="flex items-center space-x-3 sm:space-x-4">
                        <div class="avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="text-base sm:text-lg font-bold text-[#622032] leading-tight">
                                {{ auth()->user()->name }}
                            </h2>
                            <p class="text-xs sm:text-sm text-[#622032]/60 capitalize">
                                {{ auth()->user()->role()->name ?? 'No Role' }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-2">
                        @if($zone)
                        <div class="flex items-center px-3 py-1.5 bg-[#fef9de] rounded-full shadow-sm">
                            <svg class="w-4 h-4 text-[#931335] mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm font-medium text-[#622032]">{{ $zone->name }}</span>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="inline-flex" onsubmit="handleLogout(event)">
                            @csrf
                            <button type="submit"
                                class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all duration-200 active:scale-95"
                                title="Logout">
                                <svg class="w-5 h-5 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>

                        <script>
                            function handleLogout(event) {
                                event.preventDefault();
                                const form = event.target;
                                
                                // Add white transition overlay instead of madder
                                const overlay = document.createElement('div');
                                overlay.className = 'fixed inset-0 bg-white z-50 transition-opacity duration-300';
                                overlay.style.opacity = '0';
                                document.body.appendChild(overlay);
                                
                                // Fade in overlay
                                setTimeout(() => {
                                    overlay.style.opacity = '1';
                                    
                                    // Submit form and redirect
                                    fetch(form.action, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                                        }
                                    }).then(() => {
                                        setTimeout(() => {
                                            window.location.href = '/login';
                                        }, 300);
                                    });
                                }, 50);
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 safe-area">
        <div class="page-container py-6">
            <!-- Welcome Message -->
            <div class="mb-6 bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]/50">
                <div class="flex items-center space-x-3 mb-3">
                    <h1 class="text-xl sm:text-2xl font-bold text-[#622032]">Welcome Back!</h1>
                    <div class="w-1.5 h-1.5 rounded-full bg-[#931335]/20"></div>
                </div>
                <p class="text-[#622032]/70">Here's what you can do today</p>
            </div>

            <!-- Feature Cards Grid -->
            <div class="grid grid-cols-1 gap-4">
                @foreach($features as $feature)
                <a href="{{ route($feature['route']) }}"
                    class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 border border-[#f8f0e2] active:scale-[0.98]">

                    <div class="flex items-start justify-between">
                        <!-- Icon & Content -->
                        <div class="flex items-start space-x-4 flex-1">
                            <!-- Icon -->
                            <div class="w-14 h-14 bg-gradient-to-br from-[#{{ $feature['color'] === 'madder' ? '931335' : '622032' }}] to-[#622032] rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                @switch($feature['icon'])
                                @case('money')
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @break

                                @case('heart')
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                </svg>
                                @break

                                @case('chart')
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                @break

                                @case('users')
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z" />
                                </svg>
                                @break

                                @case('location')
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                @break

                                @case('settings')
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 00-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 00-2.282.819l-.922 1.597a1.875 1.875 0 00.432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 000 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 00-.432 2.385l.922 1.597a1.875 1.875 0 002.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 002.28-.819l.923-1.597a1.875 1.875 0 00-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 000-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 00-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 00-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 00-1.85-1.567h-1.843zM12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" />
                                </svg>
                                @break

                                @default
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 00-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 00-2.282.819l-.922 1.597a1.875 1.875 0 00.432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 000 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 00-.432 2.385l.922 1.597a1.875 1.875 0 002.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 002.28-.819l.923-1.597a1.875 1.875 0 00-.432-2.385l-.84-.692c-.095-.078-.17-.229-.154-.43a7.614 7.614 0 000-1.139c-.016-.2.059-.352.153-.43l.84-.692c.708-.582.891-1.59.433-2.385l-.922-1.597a1.875 1.875 0 00-2.282-.818l-1.02.382c-.114.043-.282.031-.449-.083a7.49 7.49 0 00-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 00-1.85-1.567h-1.843zM12 15.75a3.75 3.75 0 100-7.5 3.75 3.75 0 000 7.5z" clip-rule="evenodd" />
                                </svg>
                                @break
                                @endswitch
                            </div>

                            <!-- Text Content -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-bold text-[#622032] mb-1 group-hover:text-[#931335] transition-colors">
                                    {{ $feature['name'] }}
                                </h3>
                                <p class="text-sm text-[#622032]/60 mb-3">
                                    {{ $feature['description'] }}
                                </p>

                                <!-- Permission Pills -->
                                <div class="flex flex-wrap gap-2">
                                    @if($feature['permissions']['create'] ?? false)
                                    <span class="inline-flex items-center px-2 py-1 bg-[#fef9de] rounded-md text-xs font-medium text-[#622032]">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Create
                                    </span>
                                    @endif

                                    @if($feature['permissions']['edit'] ?? false)
                                    <span class="inline-flex items-center px-2 py-1 bg-[#fef9de] rounded-md text-xs font-medium text-[#622032]">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit
                                    </span>
                                    @endif

                                    @if($feature['permissions']['manage'] ?? false)
                                    <span class="inline-flex items-center px-2 py-1 bg-[#fef9de] rounded-md text-xs font-medium text-[#622032]">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Manage
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Arrow Icon -->
                        <div class="flex-shrink-0 ml-4">
                            <div class="w-8 h-8 bg-[#f8f0e2] rounded-lg flex items-center justify-center group-hover:bg-[#931335] transition-colors">
                                <svg class="w-5 h-5 text-[#622032] group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>

            <!-- Empty State (if no features) -->
            @if(count($features) === 0)
            <div class="text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                    <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-[#622032] mb-2">No Access</h3>
                <p class="text-sm text-[#622032]/60">Contact your administrator for access permissions</p>
            </div>
            @endif
        </div>
    </main>
</div>
@endsection