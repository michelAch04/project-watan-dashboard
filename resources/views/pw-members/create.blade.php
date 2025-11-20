@extends('layouts.app')

@section('title', 'Create PW Member')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="pwMemberForm()" x-init="init()">
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
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Create PW Member</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container">
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-6">

                    <!-- Voter Selection -->
                    <div class="bg-[#f8f0e2] p-4 rounded-lg border-2 border-[#931335]/20">
                        <h3 class="text-sm font-bold text-[#622032] mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Select Voter (Required) *
                        </h3>
                        <p class="text-xs text-[#622032]/70 mb-3">Search for a voter to link with this PW member</p>

                        <div class="relative" @click.away="voterSearchOpen = false" lang="ar">
                            <input
                                type="text"
                                x-model="voterSearch"
                                @focus="voterSearchOpen = true"
                                @input.debounce.400ms="if(voterSearch.length >= 2) searchVoters(); else voterResults = []"
                                placeholder="Type at least 2 characters to search..."
                                class="input-field"
                                :class="{ 'border-red-500': !form.voter_id && submitAttempted }"
                                :disabled="loading || voterPreFilled"
                                autocomplete="off"
                                required
                            />

                            <div x-show="voterSearchOpen && !voterPreFilled"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-xl max-h-60 overflow-y-auto border-2 border-[#931335]/20"
                                 style="display: none;">
                                <ul class="py-1">
                                    <template x-if="voterSearching">
                                        <li class="px-4 py-3 text-gray-500 text-sm">
                                            <div class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Searching...
                                            </div>
                                        </li>
                                    </template>

                                    <template x-for="voter in voterResults" :key="voter.id">
                                        <li @click.stop="selectVoter(voter)"
                                            class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                            <div class="text-sm font-semibold text-[#622032]" x-text="voter.first_name + ' ' + voter.father_name + ' ' + voter.last_name" lang="ar"></div>
                                            <div class="text-xs text-[#622032]/60" x-text="voter.city ? voter.city.name : 'N/A'"></div>
                                        </li>
                                    </template>

                                    <template x-if="!voterSearching && voterResults.length === 0 && voterSearch.length >= 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm">
                                            No voters found
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <!-- Selected Voter Display -->
                        <template x-if="form.voter_id && selectedVoter">
                            <div class="mt-3 p-3 bg-white rounded-lg border-2 border-[#931335]/70">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-semibold text-[#931335] mb-1">✓ Selected Voter:</p>
                                        <div class="text-sm font-bold text-[#622032] mb-1" x-text="selectedVoter.first_name + ' ' + selectedVoter.father_name + ' ' + selectedVoter.last_name" lang="ar"></div>
                                        <div class="text-xs text-[#622032]/60" x-text="selectedVoter.city ? selectedVoter.city.name : 'N/A'"></div>
                                    </div>
                                    <button type="button" @click="clearVoter()" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- First Name (Auto-filled from voter) -->
                    <div>
                        <label for="first_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            First Name (Auto-filled) *
                        </label>
                        <input
                            type="text"
                            id="first_name"
                            x-model="form.first_name"
                            class="input-field bg-gray-50"
                            :class="{ 'border-red-500': !form.first_name && submitAttempted }"
                            disabled
                            readonly
                            required
                            placeholder="Will be auto-filled from voter selection"
                            lang="ar"
                        />
                    </div>

                    <!-- Father Name (Auto-filled from voter) -->
                    <div>
                        <label for="father_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Father Name (Auto-filled) *
                        </label>
                        <input
                            type="text"
                            id="father_name"
                            x-model="form.father_name"
                            class="input-field bg-gray-50"
                            :class="{ 'border-red-500': !form.father_name && submitAttempted }"
                            disabled
                            readonly
                            required
                            placeholder="Will be auto-filled from voter selection"
                            lang="ar"
                        />
                    </div>

                    <!-- Last Name (Auto-filled from voter) -->
                    <div>
                        <label for="last_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Last Name (Auto-filled) *
                        </label>
                        <input
                            type="text"
                            id="last_name"
                            x-model="form.last_name"
                            class="input-field bg-gray-50"
                            :class="{ 'border-red-500': !form.last_name && submitAttempted }"
                            disabled
                            readonly
                            required
                            placeholder="Will be auto-filled from voter selection"
                            lang="ar"
                        />
                    </div>

                    <!-- Mother Full Name (Auto-filled from voter) -->
                    <div>
                        <label for="mother_full_name" class="block text-sm font-semibold text-[#622032] mb-2">
                            Mother Full Name (Auto-filled) *
                        </label>
                        <input
                            type="text"
                            id="mother_full_name"
                            x-model="form.mother_full_name"
                            class="input-field bg-gray-50"
                            :class="{ 'border-red-500': !form.mother_full_name && submitAttempted }"
                            disabled
                            readonly
                            required
                            placeholder="Will be auto-filled from voter selection"
                            lang="ar"
                        />
                    </div>

                    <!-- Phone (Auto-filled from voter) -->
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-[#622032] mb-2">
                            Phone (Auto-filled) *
                        </label>
                        <input
                            type="tel"
                            id="phone"
                            x-model="form.phone"
                            class="input-field bg-gray-50"
                            :class="{ 'border-red-500': !form.phone && submitAttempted }"
                            disabled
                            readonly
                            required
                            placeholder="Will be auto-filled from voter selection"
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
                            <span x-show="!loading">Create Member</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating...
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

    function pwMemberForm() {
        return {
            form: {
                voter_id: @json(request('voter_id') ?? null),
                first_name: '',
                father_name: '',
                last_name: '',
                mother_full_name: '',
                phone: '',
                email: '',
                is_active: true
            },
            voterSearch: '',
            voterSearchOpen: false,
            voterSearching: false,
            voterResults: [],
            selectedVoter: null,
            voterPreFilled: false,
            loading: false,
            errorMessage: '',
            submitAttempted: false,

            init() {
                // If voter_id is pre-filled from URL, load voter data
                @if(isset($voter))
                this.selectedVoter = @json($voter);
                this.form.voter_id = '{{ $voter->id }}';
                this.voterSearch = "{{ trim(($voter->first_name ?? '') . ' ' . ($voter->father_name ?? '') . ' ' . ($voter->last_name ?? '')) }}";
                this.form.first_name = "{{ $voter->first_name ?? '' }}";
                this.form.father_name = "{{ $voter->father_name ?? '' }}";
                this.form.last_name = "{{ $voter->last_name ?? '' }}";
                this.form.mother_full_name = "{{ $voter->mother_full_name ?? '' }}";
                this.form.phone = "{{ $voter->phone ?? '' }}";
                this.voterPreFilled = true;
                @endif
            },

            async searchVoters() {
                if (this.voterSearch.length < 2) {
                    this.voterResults = [];
                    return;
                }

                this.voterSearching = true;

                try {
                    const response = await fetch(`{{ route('pw-members.search-available-voters') }}?search=${encodeURIComponent(this.voterSearch)}`);
                    const data = await response.json();

                    this.voterResults = data;
                } catch (error) {
                    console.error('Error searching voters:', error);
                    this.voterResults = [];
                } finally {
                    this.voterSearching = false;
                }
            },

            selectVoter(voter) {
                this.form.voter_id = voter.id;
                this.selectedVoter = voter;
                this.voterSearch = voter.full_name;
                // Auto-fill fields from voter
                this.form.first_name = voter.first_name || '';
                this.form.father_name = voter.father_name || '';
                this.form.last_name = voter.last_name || '';
                this.form.mother_full_name = voter.mother_full_name || '';
                this.form.phone = voter.phone || '';
                this.voterSearchOpen = false;
                this.voterResults = [];
            },

            clearVoter() {
                if (!this.voterPreFilled) {
                    this.form.voter_id = null;
                    this.selectedVoter = null;
                    this.voterSearch = '';
                    this.form.first_name = '';
                    this.form.father_name = '';
                    this.form.last_name = '';
                    this.form.mother_full_name = '';
                    this.form.phone = '';
                }
            },

            async submitForm() {
                this.submitAttempted = true;
                this.errorMessage = '';

                // Validation
                if (!this.form.voter_id) {
                    this.errorMessage = 'Please select a voter';
                    return;
                }
                if (!this.form.first_name || !this.form.father_name || !this.form.last_name || !this.form.mother_full_name || !this.form.phone) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('{{ route('pw-members.store') }}', {
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
                        window.location.href = data.redirect || '{{ route('pw-members.index') }}';
                    } else {
                        this.errorMessage = data.message || 'Failed to create PW member';
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
