@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#931335] rounded-full mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-[#622032] mb-2">Welcome Back</h1>
            <p class="text-[#622032]/70">Sign in to your account</p>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-2xl shadow-xl p-8"
             x-data="loginForm()"
             @submit.prevent="submitLogin">
            
            <form>
                <!-- Mobile Number Input -->
                <div class="mb-6">
                    <label for="mobile" class="block text-sm font-semibold text-[#622032] mb-2">
                        Mobile Number
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#622032] font-semibold">+961</span>
                        <input 
                            type="tel" 
                            id="mobile" 
                            x-model="mobile"
                            class="input-field pl-16"
                            placeholder="03 123 456"
                            maxlength="10"
                            required
                            :disabled="loading"
                            @input="mobile = $event.target.value.replace(/[^0-9]/g, '')"
                        >
                    </div>
                    <p class="text-xs text-[#622032]/70 mt-1">Enter your Lebanese mobile number</p>
                </div>

                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-[#622032] mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        x-model="password"
                        class="input-field"
                        placeholder="Enter your password"
                        required
                        :disabled="loading"
                    >
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="error-message">
                    <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <p x-text="errorMessage"></p>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full btn-primary"
                    :disabled="loading"
                    :class="{ 'opacity-50 cursor-not-allowed': loading }">
                    <span x-show="!loading">Sign In</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending OTP...
                    </span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-sm text-gray-500">
                Secured by two-factor authentication
            </p>
        </div>
    </div>
</div>

<!-- OTP Modal -->
<div x-data="otpModal()" 
     x-show="showModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @keydown.escape.window="showModal = false">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         @click="showModal = false"></div>
    
    <!-- Modal -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 modal-enter"
             @click.stop>
            
            <!-- Close Button -->
            <button @click="showModal = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <!-- Icon -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-[#931335]/10 rounded-full mb-4">
                    <svg class="w-8 h-8 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-[#622032] mb-2">Enter Verification Code</h2>
                <p class="text-[#622032]/70">We sent a code to ****<span x-text="mobileHint"></span></p>
            </div>

            <!-- OTP Input -->
            <form @submit.prevent="verifyOTP">
                <div class="mb-6">
                    <input 
                        type="text" 
                        x-model="otp"
                        class="input-field text-center text-2xl tracking-widest font-bold"
                        placeholder="000000"
                        maxlength="6"
                        required
                        :disabled="loading"
                        @input="otp = $event.target.value.replace(/[^0-9]/g, '')"
                        x-ref="otpInput"
                    >
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-2"
                     class="error-message mb-4">
                    <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <p x-text="errorMessage"></p>
                </div>

                <!-- Timer -->
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-600">
                        Code expires in 
                        <span class="font-semibold text-blue-600" x-text="formatTime(timeLeft)"></span>
                    </p>
                </div>

                <!-- Verify Button -->
                <button 
                    type="submit"
                    class="w-full btn-primary mb-3"
                    :disabled="loading || otp.length !== 6"
                    :class="{ 'opacity-50 cursor-not-allowed': loading || otp.length !== 6 }">
                    <span x-show="!loading">Verify & Sign In</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying...
                    </span>
                </button>

                <!-- Resend Button -->
                <button 
                    type="button"
                    @click="resendOTP"
                    class="w-full btn-secondary"
                    :disabled="loading || timeLeft > 0"
                    :class="{ 'opacity-50 cursor-not-allowed': loading || timeLeft > 0 }">
                    <span x-show="timeLeft > 0">Resend in <span x-text="timeLeft"></span>s</span>
                    <span x-show="timeLeft === 0">Resend Code</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// CSRF Token setup for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Login Form Component
function loginForm() {
    return {
        mobile: '',
        password: '',
        loading: false,
        errorMessage: '',

        async submitLogin() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        mobile: this.mobile,
                        password: this.password
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Show OTP modal
                    window.dispatchEvent(new CustomEvent('show-otp-modal', { 
                        detail: { mobileHint: data.mobile_hint }
                    }));
                } else {
                    this.errorMessage = data.message || 'Invalid credentials';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('Login error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}

// OTP Modal Component
function otpModal() {
    return {
        showModal: false,
        otp: '',
        mobileHint: '',
        loading: false,
        errorMessage: '',
        timeLeft: 300, // 5 minutes
        timer: null,

        init() {
            // Listen for show modal event
            window.addEventListener('show-otp-modal', (e) => {
                this.mobileHint = e.detail.mobileHint;
                this.showModal = true;
                this.startTimer();
                // Focus OTP input after modal animation
                setTimeout(() => {
                    this.$refs.otpInput.focus();
                }, 300);
            });
        },

        startTimer() {
            this.timeLeft = 300;
            this.timer = setInterval(() => {
                if (this.timeLeft > 0) {
                    this.timeLeft--;
                } else {
                    clearInterval(this.timer);
                }
            }, 1000);
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        },

        async verifyOTP() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("otp.verify") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        otp: this.otp
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Success! Redirect to dashboard
                    window.location.href = data.redirect;
                } else {
                    this.errorMessage = data.message || 'Invalid OTP code';
                    this.otp = ''; // Clear OTP input
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('OTP verification error:', error);
            } finally {
                this.loading = false;
            }
        },

        async resendOTP()
        {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("otp.resend") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.startTimer();
                } else {
                    this.errorMessage = data.message || 'Failed to resend OTP';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('Resend OTP error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
} 
</script>
@endpush