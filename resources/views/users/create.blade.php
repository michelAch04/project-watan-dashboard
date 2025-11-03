@extends('layouts.app')

@section('title', 'Create User')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="createUserForm()" x-init="init()">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('users.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Create User</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4">
        <div class="page-container">
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Full Name *
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            x-model="form.name"
                            class="input-field"
                            required
                            :disabled="loading"
                        >
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#622032] mb-2">
                            Email Address *
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            x-model="form.email"
                            class="input-field"
                            required
                            :disabled="loading"
                        >
                    </div>

                    <div>
                        <label for="mobile" class="block text-sm font-semibold text-[#622032] mb-2">
                            Mobile Number *
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#622032] font-semibold">+961</span>
                            <input 
                                type="tel" 
                                id="mobile" 
                                x-model="form.mobile"
                                class="input-field pl-16"
                                placeholder="03 123 456"
                                required
                                :disabled="loading"
                                @input="form.mobile = $event.target.value.replace(/[^0-9]/g, '')"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-[#622032] mb-2">
                            Password *
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            x-model="form.password"
                            class="input-field"
                            required
                            :disabled="loading"
                        >
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-[#622032] mb-2">
                            Confirm Password *
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            x-model="form.password_confirmation"
                            class="input-field"
                            required
                            :disabled="loading"
                        >
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-[#622032] mb-2">
                            Role *
                        </label>
                        <select 
                            id="role" 
                            x-model="form.role"
                            class="input-field"
                            required
                            :disabled="loading"
                        >
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="manager_search" class="block text-sm font-semibold text-[#622032] mb-2">
                            Reports To
                        </label>
                        <div class="relative" @click.away="managerSearchOpen = false">
                            <input
                                type="text"
                                id="manager_search"
                                x-model="managerSearch"
                                @focus="managerSearchOpen = true"
                                @input="managerSearchOpen = true"
                                placeholder="Search managers..."
                                class="input-field"
                                :disabled="loading"
                                autocomplete="off"
                            />

                            <select id="manager_id" x-model="form.manager_id" class="hidden">
                                <option value="">No Manager (Reports to Self)</option>
                                <template x-for="manager in availableManagers" :key="manager.id">
                                    <option :value="manager.id" x-text="manager.name"></option>
                                </template>
                            </select>

                            <div x-show="managerSearchOpen"
                                 x-transition
                                 class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                
                                <ul class="py-1">
                                    <li @click="selectManager({ id: '', name: 'No Manager (Reports to Self)' })"
                                        class="px-4 py-2 hover:bg-[#f8f0e2] cursor-pointer italic">
                                        No Manager (Reports to Self)
                                    </li>
                                    
                                    <template x-for="manager in filteredManagers" :key="manager.id">
                                        <li @click="selectManager(manager)"
                                            class="px-4 py-2 hover:bg-[#f8f0e2] cursor-pointer"
                                            x-text="manager.name">
                                        </li>
                                    </template>
                                    
                                    <template x-if="filteredManagers.length === 0">
                                        <li class="px-4 py-2 text-gray-500 italic">No matching managers.</li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        <p class="text-xs text-[#622032]/60 mt-1">Leave empty or select "No Manager" if user reports to themselves</p>
                    </div>
                    <div x-show="errorMessage" 
                         x-cloak
                         x-transition
                         class="error-message">
                        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p x-text="errorMessage"></p>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full btn-primary"
                        :disabled="loading"
                        :class="{ 'opacity-50 cursor-not-allowed': loading }">
                        <span x-show="!loading">Create User</span>
                        <span x-show="loading" class="flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div x-show="showAssignModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="skipAssignment">
        
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             x-show="showAssignModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"></div>
        
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
                 x-show="showAssignModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 @click.stop>
                
                <h2 class="text-xl font-bold text-[#622032] mb-4">Assign Location?</h2>
                <p class="text-[#622032]/70 mb-6">
                    User <strong x-text="form.name"></strong> has been created successfully. 
                    Would you like to assign a location to them now?
                </p>

                <div class="flex gap-3">
                    <button @click="skipAssignment" class="flex-1 btn-secondary">
                        Skip
                    </button>
                    <button @click="goToAssignLocation" class="flex-1 btn-primary">
                        Assign
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function createUserForm() {
    return {
        form: {
            name: '',
            email: '',
            mobile: '',
            password: '',
            password_confirmation: '',
            role: '',
            manager_id: ''
        },
        loading: false,
        errorMessage: '',
        showAssignModal: false,
        createdUserId: null,

        // **NEW**: Properties for manager search
        availableManagers: [],
        managerSearch: '',
        managerSearchOpen: false,

        // **NEW**: Computed property to filter managers
        get filteredManagers() {
            if (this.managerSearch === '' || this.managerSearch === 'No Manager (Reports to Self)') {
                return this.availableManagers;
            }
            return this.availableManagers.filter(manager => {
                return manager.name.toLowerCase().includes(this.managerSearch.toLowerCase());
            });
        },

        // **NEW**: init() function to load managers from PHP
        init() {
            // Load managers from the Blade variable into Alpine state
            // We map it to ensure we only have the data we need (id, name)
            this.availableManagers = @json($users->map(fn($user) => ['id' => $user->id, 'name' => $user->name]));

            // Set initial text for the "No Manager" default
            if (this.form.manager_id === '') {
                this.managerSearch = '';
            }
            
            // Watch for when the user manually clears the search input
            this.$watch('managerSearch', (value) => {
                if (value === '') {
                    this.form.manager_id = ''; // Set back to 'No Manager'
                }
            });

            // Close dropdown on Escape key
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.managerSearchOpen = false;
                }
            });
        },

        // **NEW**: Method to select a manager from the dropdown
        selectManager(manager) {
            // manager is an object: { id: 123, name: 'John Doe' }
            // or { id: '', name: 'No Manager (Reports to Self)' }
            this.form.manager_id = manager.id;
            this.managerSearch = manager.name;
            this.managerSearchOpen = false;
        },

        async submitForm() {
            this.loading = true;
            this.errorMessage = '';

            // **MODIFIED**: Ensure manager_id is correctly set if text was cleared
            if (this.managerSearch === '' || this.managerSearch === 'No Manager (Reports to Self)') {
                this.form.manager_id = '';
            }

            try {
                const response = await fetch('{{ route("users.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.createdUserId = data.user_id;
                    this.showAssignModal = true;
                } else {
                    this.errorMessage = data.message || 'Failed to create user';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('Create user error:', error);
            } finally {
                this.loading = false;
            }
        },

        goToAssignLocation() {
            window.location.href = `/users/${this.createdUserId}/assign-location`;
        },

        skipAssignment() {
            window.location.href = '{{ route("users.index") }}';
        }
    }
}
</script>
@endpush