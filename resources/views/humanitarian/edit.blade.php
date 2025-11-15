@extends('layouts.app')

@section('title', 'Edit Request')

@section('content')
@php
$voterData = $request->voter ? [
    'id' => $request->voter->id,
    'full_name' => $request->voter->full_name,
    'city_name' => $request->voter->city->name,
    'ro_number' => $request->voter->ro_number,
    'city_id' => $request->voter->city_id
] : null;

$memberData = $request->referenceMember ? [
    'id' => $request->referenceMember->id,
    'name' => $request->referenceMember->name,
    'phone' => $request->referenceMember->phone
] : null;
@endphp
<div class="min-h-screen bg-[#fcf7f8]" x-data="humanitarianEditForm()" x-init="init()">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('humanitarian.show', $request->id) }}" @click.prevent="window.history.length > 1 ? window.history.back() : window.location.href = '{{ route('humanitarian.show', $request->id) }}'" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Edit Request</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4">
        <div class="page-container">
            <!-- Request Number Info -->
            <div class="bg-[#f8f0e2] p-4 rounded-xl mb-4 border border-[#f8f0e2]">
                <p class="text-sm font-semibold text-[#622032]">Editing: {{ $request->request_number }}</p>
                <p class="text-xs text-[#622032]/60">Created on {{ $request->request_date->format('M d, Y') }}</p>
            </div>

            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-6">

                    <!-- Voter Search Section - MANDATORY -->
                    <div class="bg-[#f8f0e2] p-4 rounded-lg border-2 border-[#931335]/20">
                        <h3 class="text-sm font-bold text-[#622032] mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Select Voter (Required) *
                        </h3>
                        <p class="text-xs text-[#622032]/70 mb-3">You can only select voters from your assigned location</p>

                        <div class="relative">
                            <input
                                type="text"
                                x-model="voterSearch"
                                @focus="voterSearchOpen = true; if(voterSearch.length >= 2) searchVoters()"
                                @input="if(voterSearch.length >= 2) { searchVoters() } else { voterResults = []; voterSearchOpen = false; }"
                                @click="if(voterSearch.length >= 2 && voterResults.length > 0) voterSearchOpen = true"
                                placeholder="Type at least 2 characters to search..."
                                class="input-field"
                                :class="{ 'border-red-500': !form.voter_id && submitAttempted }"
                                :disabled="loading"
                                autocomplete="off"
                                required />

                            <div x-show="voterSearchOpen"
                                @click.away="voterSearchOpen = false"
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
                                            @mousedown.prevent
                                            class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                            <div class="font-semibold text-[#622032]" x-text="voter.full_name"></div>
                                            <div class="text-xs text-[#622032]/60 flex items-center gap-2 mt-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span x-text="voter.city_name"></span>
                                                <span>•</span>
                                                <span x-text="voter.ro_number"></span>
                                            </div>
                                        </li>
                                    </template>

                                    <template x-if="!voterSearching && voterResults.length === 0 && voterSearch.length >= 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm italic">
                                            No voters found in your location
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div x-show="form.voter_id" x-cloak class="mt-3 p-3 bg-white rounded-lg border-2 border-[#931335]/30">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-[#931335] mb-1">✓ Selected Voter:</p>
                                    <p class="text-sm font-bold text-[#622032]" x-text="selectedVoter?.full_name"></p>
                                    <div class="flex items-center gap-2 text-xs text-[#622032]/60 mt-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span x-text="selectedVoter?.city_name"></span>
                                        <span>•</span>
                                        <span x-text="selectedVoter?.ro_number"></span>
                                    </div>
                                </div>
                                <button type="button" @click="clearVoter()" class="text-red-600 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Information Display -->
                    <div x-show="form.voter_id" x-cloak class="bg-[#fcf7f8] p-4 rounded-lg border border-[#f8f0e2]">
                        <h3 class="text-sm font-bold text-[#622032] mb-3">Requester Information (Auto-filled)</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Full Name:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.full_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">City:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.city_name"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">رقم السجل:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.ro_number"></span>
                            </div>
                            <div x-show="selectedVoter?.phone" class="flex justify-between">
                                <span class="text-[#622032]/60">Phone:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.phone"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Request Details -->
                    <div class="space-y-4 border-t border-[#f8f0e2] pt-6">
                        <h3 class="text-base font-bold text-[#622032]">Request Details</h3>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Request Type *</label>
                            <select x-model="form.subtype" class="input-field" required :disabled="loading">
                                <option value="">Select Type</option>
                                <option value="تربوية">تربوية (Educational)</option>
                                <option value="طبية">طبية (Medical)</option>
                                <option value="استشفائية">استشفائية (Hospital/Healing)</option>
                                <option value="إجتماعية">إجتماعية (Social)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Reference (PW Member) *</label>
                            <div class="relative" @click.away="memberSearchOpen = false">
                                <input
                                    type="text"
                                    x-model="memberSearch"
                                    @focus="memberSearchOpen = true; searchMembers()"
                                    @input="searchMembers()"
                                    placeholder="Search PW member..."
                                    class="input-field"
                                    :disabled="loading"
                                    autocomplete="off" />

                                <div x-show="memberSearchOpen && (memberResults.length > 0 || memberSearching)"
                                    x-transition
                                    class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                    <ul class="py-1">
                                        <template x-if="memberSearching">
                                            <li class="px-4 py-3 text-gray-500 text-sm">Searching...</li>
                                        </template>

                                        <template x-for="member in memberResults" :key="member.id">
                                            <li @click="selectMember(member)"
                                                class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer border-b border-gray-100 last:border-0">
                                                <div class="font-semibold text-[#622032]" x-text="member.name"></div>
                                                <div class="text-xs text-[#622032]/60" x-text="member.phone"></div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <div x-show="form.reference_member_id" x-cloak class="mt-2 text-sm text-[#622032]">
                                Selected: <span class="font-semibold" x-text="selectedMember?.name"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Amount (USD) *</label>
                            <input type="number" step="0.01" min="0" x-model="form.amount" class="input-field" required :disabled="loading">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Notes</label>
                            <textarea x-model="form.notes" rows="4" class="input-field" :disabled="loading"></textarea>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div x-show="errorMessage" x-cloak x-transition class="error-message">
                        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p x-text="errorMessage"></p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="button" @click="saveChanges" class="flex-1 btn-secondary" :disabled="loading">
                            <span x-show="!loading || submitAction !== 'save'">Save Changes</span>
                            <span x-show="loading && submitAction === 'save'" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                        <button type="button" @click="publishNow" class="flex-1 btn-primary" :disabled="loading">
                            <span x-show="!loading || submitAction !== 'publish'">Save & Publish</span>
                            <span x-show="loading && submitAction === 'publish'" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Publishing...
                            </span>
                        </button>
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

    function humanitarianEditForm() {
        return {
            form: {
                voter_id: '{{ $request->voter_id ?? "" }}',
                requester_first_name: '{{ $request->requester_first_name }}',
                requester_father_name: '{{ $request->requester_father_name }}',
                requester_last_name: '{{ $request->requester_last_name }}',
                requester_city_id: '{{ $request->requester_city_id }}',
                requester_ro_number: '{{ $request->requester_ro_number }}',
                requester_phone: '{{ $request->requester_phone }}',
                subtype: '{{ $request->subtype }}',
                reference_member_id: '{{ $request->reference_member_id }}',
                amount: '{{ $request->amount }}',
                notes: `{{ $request->notes }}`,
                action: 'save'
            },
            loading: false,
            submitAction: '',
            errorMessage: '',

            voterSearch: '{{ $request->voter ? $request->voter->full_name : "" }}',
            voterSearchOpen: false,
            voterSearching: false,
            voterResults: [],
            selectedVoter: @json($voterData),
            voterSearchTimeout: null,

            memberSearch: '{{ $request->referenceMember ? $request->referenceMember->name : "" }}',
            memberSearchOpen: false,
            memberSearching: false,
            memberResults: [],
            selectedMember: @json($memberData),
            memberSearchTimeout: null,

            init() {
                this.searchMembers();
            },

            async searchVoters() {
                if (this.voterSearch.length < 2) {
                    this.voterResults = [];
                    return;
                }

                clearTimeout(this.voterSearchTimeout);
                this.voterSearchTimeout = setTimeout(async () => {
                    this.voterSearching = true;
                    try {
                        const response = await fetch(`{{ route('humanitarian.search-voters') }}?search=${encodeURIComponent(this.voterSearch)}`);
                        this.voterResults = await response.json();
                    } catch (error) {
                        console.error('Voter search error:', error);
                    } finally {
                        this.voterSearching = false;
                    }
                }, 300);
            },

            selectVoter(voter) {
                this.selectedVoter = voter;
                this.form.voter_id = voter.id;
                this.voterSearch = voter.full_name;
                this.voterSearchOpen = false;
            },

            clearVoter() {
                this.selectedVoter = null;
                this.form.voter_id = '';
                this.voterSearch = '';
            },

            async searchMembers() {
                clearTimeout(this.memberSearchTimeout);
                this.memberSearchTimeout = setTimeout(async () => {
                    this.memberSearching = true;
                    try {
                        const response = await fetch(`{{ route('humanitarian.search-members') }}?search=${encodeURIComponent(this.memberSearch)}`);
                        this.memberResults = await response.json();
                    } catch (error) {
                        console.error('Member search error:', error);
                    } finally {
                        this.memberSearching = false;
                    }
                }, 300);
            },

            selectMember(member) {
                this.selectedMember = member;
                this.form.reference_member_id = member.id;
                this.memberSearch = member.name;
                this.memberSearchOpen = false;
            },

            saveChanges() {
                this.form.action = 'save';
                this.submitAction = 'save';
                this.submitForm();
            },

            publishNow() {
                this.form.action = 'publish';
                this.submitAction = 'publish';
                this.submitForm();
            },

            async submitForm() {
                if (!this.form.voter_id) {
                    if (!this.form.requester_first_name || !this.form.requester_father_name ||
                        !this.form.requester_last_name || !this.form.requester_city_id) {
                        this.errorMessage = 'Please fill in all required requester fields or select a voter';
                        return;
                    }
                }

                if (!this.form.subtype || !this.form.reference_member_id || !this.form.amount) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('{{ route("humanitarian.update", $request->id) }}', {
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
                        window.location.href = data.redirect;
                    } else {
                        this.errorMessage = data.message || 'Failed to update request';
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