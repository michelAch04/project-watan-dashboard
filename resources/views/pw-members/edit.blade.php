@extends('layouts.app')

@section('title', 'Edit PW Member')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="pwMemberEditForm()" x-init="init()">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('pw-members.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Edit PW Member</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container">
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-6">

                    <!-- Voter Info (Read-only) -->
                    @if($member->voter)
                    <div class="bg-[#f8f0e2] p-4 rounded-lg border-2 border-[#931335]/20">
                        <h3 class="text-sm font-bold text-[#622032] mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Linked Voter (Cannot be changed)
                        </h3>
                        <div class="text-sm text-[#622032]">
                            <div class="font-bold" lang="ar">{{ $member->voter->first_name }} {{ $member->voter->father_name }} {{ $member->voter->last_name }}</div>
                            @if($member->voter->city)
                            <div class="text-xs text-[#622032]/60 mt-1">{{ $member->voter->city->name }}</div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="bg-blue-50 p-4 rounded-lg border-2 border-blue-200">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-bold text-blue-900 mb-1">No Voter Linked</h4>
                                <p class="text-xs text-blue-700">This PW member is not linked to a voter.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- First Name -->
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            First Name *
                        </label>
                        <input
                            type="text"
                            id="first_name"
                            x-model="form.first_name"
                            class="input-field"
                            :class="{ 'border-red-500': !form.first_name && submitAttempted }"
                            :disabled="loading"
                            required
                            placeholder="Enter first name"
                            readonly
                            lang="ar"
                        />
                    </div>

                    <!-- Father Name -->
                    <div>
                        <label for="father_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Father Name *
                        </label>
                        <input
                            type="text"
                            id="father_name"
                            x-model="form.father_name"
                            class="input-field"
                            :class="{ 'border-red-500': !form.father_name && submitAttempted }"
                            :disabled="loading"
                            required
                            placeholder="Enter father name"
                            readonly
                            lang="ar"
                        />
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Last Name *
                        </label>
                        <input
                            type="text"
                            id="last_name"
                            x-model="form.last_name"
                            class="input-field"
                            :class="{ 'border-red-500': !form.last_name && submitAttempted }"
                            :disabled="loading"
                            required
                            placeholder="Enter last name"
                            readonly
                            lang="ar"
                        />
                    </div>

                    <!-- Mother Full Name -->
                    <div>
                        <label for="mother_full_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Mother Full Name *
                        </label>
                        <input
                            type="text"
                            id="mother_full_name"
                            x-model="form.mother_full_name"
                            class="input-field"
                            :class="{ 'border-red-500': !form.mother_full_name && submitAttempted }"
                            :disabled="loading"
                            required
                            placeholder="Enter mother full name"
                            readonly
                            lang="ar"
                        />
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-[#622032] mb-2">
                            Phone *
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            x-model="form.phone"
                            class="input-field"
                            :class="{ 'border-red-500': !form.phone && submitAttempted }"
                            :disabled="loading"
                            required
                            placeholder="Enter phone number"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-[#622032] mb-2">
                            Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            x-model="form.email"
                            class="input-field"
                            :disabled="loading"
                            placeholder="Enter email (optional)"
                        />
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                x-model="form.is_active"
                                class="w-5 h-5 text-[#931335] rounded border-gray-300 focus:ring-[#931335]"
                                :disabled="loading"
                            />
                            <span class="ml-2 text-sm font-semibold text-[#622032]">Active Member</span>
                        </label>
                        <p class="text-xs text-[#622032]/60 mt-1 ml-7">Active members can be assigned to tasks</p>
                    </div>

                    <!-- Error Message -->
                    <template x-if="errorMessage">
                        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-sm text-red-600" x-text="errorMessage"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Submit Buttons -->
                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="flex-1 btn-primary"
                            :class="{ 'opacity-50 cursor-not-allowed': loading }">
                            <span x-show="!loading">Update PW Member</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Updating...
                            </span>
                        </button>
                        <a href="{{ route('pw-members.index') }}" class="flex-1 btn-secondary text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function pwMemberEditForm() {
        return {
            form: {
                first_name: @json($member->first_name),
                father_name: @json($member->father_name),
                last_name: @json($member->last_name),
                mother_full_name: @json($member->mother_full_name),
                phone: @json($member->phone),
                email: @json($member->email),
                is_active: @json((bool)$member->is_active)
            },
            loading: false,
            errorMessage: '',
            submitAttempted: false,

            init() {
                // Form is initialized with member data from Blade
            },

            async submitForm() {
                this.submitAttempted = true;
                this.errorMessage = '';

                // Validation
                if (!this.form.first_name || !this.form.father_name || !this.form.last_name || !this.form.mother_full_name || !this.form.phone) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('{{ route('pw-members.update', $member->id) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.form)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        window.location.href = data.redirect || '{{ route('pw-members.index') }}';
                    } else {
                        this.errorMessage = data.message || 'Failed to update PW member';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please try again.';
                    console.error('Submit error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
