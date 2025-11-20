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
                    
                    <div class="bg-[#f8f0e2] p-4 rounded-lg border-2 border-[#931335]/20">
                        <h3 class="text-sm font-bold text-[#622032] mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Select PW Member (Required) *
                        </h3>
                        <p class="text-xs text-[#622032]/70 mb-3">Users must be linked to a PW member</p>
                        
                        <div class="relative" @click.away="pwMemberSearchOpen = false">
                            <input
                                type="text"
                                x-model="pwMemberSearch"
                                @focus="pwMemberSearchOpen = true"
                                @input="pwMemberSearchOpen = true"
                                @if($pwMember)
                                x-init="selectPwMember({{ $pwMember->toJson() }})"
                                @endif
                                placeholder="Search PW member..."
                                class="input-field"
                                :class="{ 'border-red-500': !form.pw_member_id && submitAttempted }"
                                :disabled="loading"
                                autocomplete="off"
                                required
                                lang="ar"
                            />
                            
                            <div x-show="pwMemberSearchOpen"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-xl max-h-60 overflow-y-auto border-2 border-[#931335]/20"
                                 style="display: none;">
                                <ul class="py-1">
                                    <template x-for="member in filteredPwMembers" :key="member.id">
                                        <li @click.stop="selectPwMember(member)"
                                            @mousedown.prevent
                                            class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                            <div class="font-semibold text-[#622032]">
                                                <span x-text="member.first_name"></span> <span x-text="member.last_name"></span>
                                            </div>
                                            <div class="text-xs text-[#622032]/60" x-text="member.phone"></div>
                                        </li>
                                    </template>
                                    
                                    <template x-if="filteredPwMembers.length === 0">
                                        <li class="px-4 py-3 text-gray-500 text-sm italic">
                                            No available PW members
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        
                        <div x-show="form.pw_member_id" x-cloak class="mt-3 p-3 bg-white rounded-lg border-2 border-[#931335]/30">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-[#931335] mb-1">✓ Selected PW Member:</p>
                                    <p class="text-sm font-bold text-[#622032] mb-1" lang="ar">
                                        <span x-text="selectedPwMember?.first_name"></span> <span x-text="selectedPwMember?.father_name"></span> <span x-text="selectedPwMember?.last_name"></span>
                                    </p>
                                    <p class="text-xs text-[#622032]/60" x-text="selectedPwMember?.phone"></p>
                                </div>
                                <button type="button" @click="clearPwMember()" class="text-red-600 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div x-show="!form.pw_member_id && submitAttempted" x-cloak class="mt-2 text-xs text-red-600">
                            Please select a PW member
                        </div>
                    </div>

                    <div x-show="form.pw_member_id" x-cloak class="bg-[#fcf7f8] p-4 rounded-lg border border-[#f8f0e2]">
                        <h3 class="text-sm font-bold text-[#622032] mb-3">User Information (Auto-filled)</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Full Name:</span>
                                <span class="font-semibold text-[#622032]" lang="ar">
                                    <span x-text="selectedPwMember?.first_name"></span> <span x-text="selectedPwMember?.father_name"></span> <span x-text="selectedPwMember?.last_name"></span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Phone:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedPwMember?.phone"></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-semibold text-[#622032] mb-2">
                            Username *
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            x-model="form.username"
                            class="input-field"
                            placeholder="Enter unique username"
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
                                <option value="{{ $role->name }}">{{ strtoupper($role->name) }}</option>
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
                                    <option :value="manager.id" x-text="manager.username"></option>
                                </template>
                            </select>

                            <div x-show="managerSearchOpen"
                                 x-transition
                                 class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                
                                <ul class="py-1">
                                    <li @click="selectManager({ id: '', username: 'No Manager (Reports to Self)' })"
                                        class="px-4 py-2 hover:bg-[#f8f0e2] cursor-pointer italic">
                                        No Manager (Reports to Self)
                                    </li>
                                    
                                    <template x-for="manager in filteredManagers" :key="manager.id">
                                        <li @click="selectManager(manager)"
                                            class="px-4 py-2 hover:bg-[#f8f0e2] cursor-pointer"
                                            x-text="manager.username">
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

</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function createUserForm() {
    return {
        form: {
            pw_member_id: '',
            username: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: '',
            manager_id: ''
        },
        loading: false,
        submitAttempted: false,
        errorMessage: '',

        // PW Member search
        pwMembers: @json($pwMembers),
        pwMemberSearch: '',
        pwMemberSearchOpen: false,
        selectedPwMember: null,

        get filteredPwMembers() {
            if (this.pwMemberSearch === '') {
                return this.pwMembers;
            }
            return this.pwMembers.filter(member => {
                const fullName = (member.first_name + ' ' + member.last_name).toLowerCase();
                return fullName.includes(this.pwMemberSearch.toLowerCase()) ||
                       member.phone.includes(this.pwMemberSearch);
            });
        },

        // Manager search
        availableManagers: [],
        managerSearch: '',
        managerSearchOpen: false,

        get filteredManagers() {
            if (this.managerSearch === '' || this.managerSearch === 'No Manager (Reports to Self)') {
                return this.availableManagers;
            }
            return this.availableManagers.filter(manager => {
                return manager.username.toLowerCase().includes(this.managerSearch.toLowerCase());
            });
        },

        init() {
            // Map user ID and Username for managers
            this.availableManagers = @json($users->map(fn($user) => ['id' => $user->id, 'username' => $user->username]));

            if (this.form.manager_id === '') {
                this.managerSearch = '';
            }
            
            this.$watch('managerSearch', (value) => {
                if (value === '') {
                    this.form.manager_id = '';
                }
            });

            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.managerSearchOpen = false;
                    this.pwMemberSearchOpen = false;
                }
            });
        },

        selectPwMember(member) {
            this.selectedPwMember = member;
            this.form.pw_member_id = member.id;
            // Display first and last name in search bar when selected
            this.pwMemberSearch = member.first_name + ' ' + member.last_name;
            this.pwMemberSearchOpen = false;
            this.submitAttempted = false;
        },

        clearPwMember() {
            this.selectedPwMember = null;
            this.form.pw_member_id = '';
            this.pwMemberSearch = '';
        },

        selectManager(manager) {
            this.form.manager_id = manager.id;
            this.managerSearch = manager.username;
            this.managerSearchOpen = false;
        },

        async submitForm() {
            this.submitAttempted = true;
            this.loading = true;
            this.errorMessage = '';

            if (!this.form.pw_member_id) {
                this.errorMessage = 'Please select a PW member';
                this.loading = false;
                return;
            }

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
                    // Automatically redirect to assign-location page
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = `/users/${data.user_id}/assign-location`;
                    }
                } else {
                    this.errorMessage = data.message || 'Failed to create user';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('Create user error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush