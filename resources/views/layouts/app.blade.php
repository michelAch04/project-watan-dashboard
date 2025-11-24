<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Theme color for browser UI (using madder color) -->
    <meta name="theme-color" content="#931335">

    <!-- Apple touch icon -->
    <link rel="apple-touch-icon" href="{{ asset('images/icons/icon-192x192.png') }}">

    <!-- Enable standalone mode on iOS -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">


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

<body class="bg-gray-50 min-h-screen antialiased" x-data="{ menuOpen: false }" :class="{ 'modal-open': menuOpen }"
    data-authenticated="{{ Auth::check() ? 'true' : 'false' }}" x-cloak>

    <!-- Page Loading Overlay -->
    <div id="pageLoadingOverlay" class="page-loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    @if(Auth::check())
        @include('components.notification-permission')
    @endif

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
        <div class="modal-backdrop" @click="menuOpen = false" x-show="menuOpen"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        </div>
    </template>

    @stack('scripts')

    <!-- Page Loading Behavior (PWA-Optimized) -->
    <script>
(function() {
            const overlay = document.getElementById('pageLoadingOverlay');
            let hideTimer = null;

            // 1. Show loading overlay
            function showLoadingOverlay() {
                if (!overlay) return;
                if (hideTimer) {
                    clearTimeout(hideTimer);
                    hideTimer = null;
                }
                
                overlay.classList.add('active');
                
                // Fallback: hide after 5s
                hideTimer = setTimeout(hideLoadingOverlay, 5000);
            }

            // 2. Hide loading overlay
            function hideLoadingOverlay() {
                if (!overlay) return;
                if (hideTimer) {
                    clearTimeout(hideTimer);
                    hideTimer = null;
                }
                overlay.classList.remove('active');
            }

            // 3. Mobile Touch Logic
            let isTouchDevice = false;
            let touchMoved = false;

            document.addEventListener('touchmove', () => { touchMoved = true; }, { passive: true });
            document.addEventListener('touchstart', () => { 
                isTouchDevice = true; 
                touchMoved = false; 
            }, { passive: true });

            // 4. THE IOS PWA FIX: Intercept Clicks
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');

                // Ignore if user scrolled (mobile)
                if (isTouchDevice && touchMoved) return;

                // Strict link validation
                if (link &&
                    link.href &&
                    !link.hasAttribute('download') &&
                    !link.hasAttribute('target') &&
                    link.protocol === window.location.protocol &&
                    link.host === window.location.host &&
                    !link.href.startsWith('javascript:') &&
                    !link.href.includes('#') &&
                    link.href !== window.location.href &&
                    !e.ctrlKey && !e.metaKey && !e.shiftKey && e.button === 0) {

                    // --- THE CRITICAL CHANGES FOR IOS PWA ---
                    
                    // A. Stop the browser from handling the link automatically
                    e.preventDefault();

                    // B. Show the spinner
                    showLoadingOverlay();

                    // C. Manually navigate after a tiny delay. 
                    // This ensures the browser "paints" the spinner 
                    // BEFORE it freezes the UI to load the next page.
                    setTimeout(function() {
                        window.location.href = link.href;
                    }, 50); // 50ms delay is imperceptible to humans but enough for the DOM
                }
            }, true);

            // 5. Cleanup on Load (Bfcache/History support)
            function forceHideOnLoad() { hideLoadingOverlay(); }

            window.addEventListener('DOMContentLoaded', forceHideOnLoad);
            window.addEventListener('load', forceHideOnLoad);
            window.addEventListener('pageshow', function(event) {
                // Crucial for iOS Back Button
                if (event.persisted) forceHideOnLoad();
            });
        })();
    </script>

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

        // Optimized fix for mobile back button focus/active state
        function clearAllStuckStates() {
            // 1. Simply blur the currently active element. 
            // This fixes 99% of "sticky hover" issues on mobile.
            if (document.activeElement && document.activeElement instanceof HTMLElement) {
                document.activeElement.blur();
            }

            // 2. Only if absolutely necessary for Tailwind rings/shadows:
            // Remove focus classes manually from the specific element that was clicked
            // (Instead of scanning the whole DOM which is slow)
            const focused = document.querySelector(':focus');
            if (focused) focused.blur();
        }

        // On page show (back button or tab switch)
        window.addEventListener('pageshow', function (event) {
            // If page was served from back-forward cache
            if (event.persisted) {
                clearAllStuckStates();

                // Ensure overlay is hidden specifically for BFcache
                const overlay = document.getElementById('pageLoadingOverlay');
                if (overlay) overlay.classList.remove('active');
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