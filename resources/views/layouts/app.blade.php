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

<body class="bg-gray-50 min-h-screen antialiased"
      x-data="{ menuOpen: false }"
      :class="{ 'modal-open': menuOpen }"
      data-authenticated="{{ Auth::check() ? 'true' : 'false' }}"
      x-cloak>

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

    <!-- Page Loading Behavior -->
    <script>
        (function() {
            const overlay = document.getElementById('pageLoadingOverlay');
            let navigationTimeout = null;
            const LOADING_DELAY = 100; // Only show loader if navigation takes longer than 100ms

            // Show loading overlay with delay
            function showLoadingOverlay() {
                navigationTimeout = setTimeout(() => {
                    if (overlay) {
                        overlay.classList.add('active');
                    }
                }, LOADING_DELAY);
            }

            // Hide loading overlay immediately
            function hideLoadingOverlay() {
                if (navigationTimeout) {
                    clearTimeout(navigationTimeout);
                    navigationTimeout = null;
                }
                if (overlay) {
                    overlay.classList.remove('active');
                }
            }

            // Intercept all internal link clicks
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');

                // Check if it's a valid internal navigation link
                if (link &&
                    link.href &&
                    !link.hasAttribute('download') &&
                    !link.hasAttribute('target') &&
                    link.protocol === window.location.protocol &&
                    link.host === window.location.host &&
                    !link.href.startsWith('javascript:') &&
                    !link.href.includes('#') &&
                    !e.ctrlKey &&
                    !e.metaKey &&
                    !e.shiftKey &&
                    e.button === 0) {

                    // Don't show loader if we're already on this page
                    if (link.href !== window.location.href) {
                        showLoadingOverlay();
                    }
                }
            }, true);

            // Handle browser back/forward buttons
            window.addEventListener('pageshow', function(event) {
                hideLoadingOverlay();
            });

            // Hide overlay when page loads
            window.addEventListener('load', hideLoadingOverlay);

            // Hide overlay on DOM ready (faster)
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', hideLoadingOverlay);
            } else {
                hideLoadingOverlay();
            }

            // Failsafe: hide overlay after 10 seconds
            setTimeout(hideLoadingOverlay, 10000);
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