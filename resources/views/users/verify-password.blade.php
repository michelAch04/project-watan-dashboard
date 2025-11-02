@extends('layouts.app')

@section('title', 'Verify Password')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">
        <!-- Lock Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-[#931335]/10 rounded-full mb-4">
                <svg class="w-10 h-10 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-[#622032] mb-2">Admin Verification</h1>
            <p class="text-[#622032]/70">Enter your password to access user management</p>
        </div>

        <!-- Verification Form -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8"
             x-data="verifyPasswordForm()"
             @submit.prevent="submitPassword">
            
            <form>
                <!-- Password Input -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-[#622032] mb-2">
                        Admin Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        x-model="password"
                        class="input-field"
                        placeholder="Enter your password"
                        required
                        :disabled="loading"
                        x-ref="passwordInput"
                    >
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" 
                     x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-2"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
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
                    <span x-show="!loading">Verify & Continue</span>
                    <span x-show="loading" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying...
                    </span>
                </button>

                <!-- Back to Dashboard -->
                <a href="{{ route('dashboard') }}" class="block text-center mt-4 text-sm text-[#622032]/70 hover:text-[#931335]">
                    Back to Dashboard
                </a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function verifyPasswordForm() {
    return {
        password: '',
        loading: false,
        errorMessage: '',

        init() {
            setTimeout(() => {
                this.$refs.passwordInput.focus();
            }, 300);
        },

        async submitPassword() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("users.verify-password.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        password: this.password
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Create transition overlay
                    const overlay = document.createElement('div');
                    overlay.className = 'fixed inset-0 bg-white z-[60] transition-opacity duration-500';
                    overlay.style.opacity = '0';
                    document.body.appendChild(overlay);
                    
                    setTimeout(() => {
                        overlay.style.opacity = '1';
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    }, 50);
                } else {
                    this.errorMessage = data.message || 'Invalid password';
                    this.password = '';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('Password verification error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush