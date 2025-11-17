<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PW Dashboard') }} - @yield('title', 'Home')</title>

    <!-- Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Optional: Theme color for browser UI -->
    <meta name="theme-color" content="#4F46E5">

    <!-- Apple touch icon (if you have one in your public folder) -->
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- Enable standalone mode on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">


    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Smooth animations */
        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-enter {
            animation: slideUp 0.3s ease-out;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen antialiased" 
      x-data="{ menuOpen: false }" 
      :class="{ 'modal-open': menuOpen }"
      x-cloak>
    <div class="page-wrapper">
        <div class="page-content">
            @yield('content')
        </div>
        
        @if(Auth::check())
        @include('layouts.partials.bottom-nav')
        @endif
    </div>

    <!-- Modal Backdrop -->
    <template x-if="menuOpen">
        <div class="modal-backdrop" 
             @click="menuOpen = false"
             x-show="menuOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>
    </template>

    @stack('scripts')
    
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('Service Worker registered:', registration);
                    })
                    .catch(error => {
                        console.log('Service Worker registration failed:', error);
                    });
            });
        }

        // Comprehensive fix for mobile back button focus/active state
        function clearAllStuckStates() {
            // Blur active element
            if (document.activeElement && document.activeElement !== document.body) {
                document.activeElement.blur();
            }

            // Remove all Tailwind pseudo-class states by forcing a reflow
            document.querySelectorAll('a, button, [role="button"]').forEach(function(el) {
                // Force remove focus by re-setting tabindex
                const currentTabIndex = el.getAttribute('tabindex');
                el.setAttribute('tabindex', '-1');
                el.blur();
                if (currentTabIndex !== null) {
                    el.setAttribute('tabindex', currentTabIndex);
                } else {
                    el.removeAttribute('tabindex');
                }

                // Clear any stuck visual states
                el.style.cssText = '';
            });

            // Force a repaint
            document.body.style.display = 'none';
            document.body.offsetHeight;
            document.body.style.display = '';
        }

        // On page show (back button)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                clearAllStuckStates();
            }
        });

        // On DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', clearAllStuckStates);
        } else {
            clearAllStuckStates();
        }
    </script>
</body>

</html>