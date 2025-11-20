@extends('layouts.app')

@section('title', 'Create Humanitarian Request')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="humanitarianForm(@json(auth()->user()->hasRole('hor')))" x-init="init()">
    {{-- ... (Header is unchanged) ... --}}
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('humanitarian.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Create Request</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4">
        <div class="page-container">
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-8">

                    <div class="bg-[#f8f0e2] p-4 rounded-lg border-2 border-[#931335]/20">
                        <h3 class="text-sm font-bold text-[#622032] mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Select Voter (Required) *
                        </h3>
                        <p class="text-xs text-[#622032]/70 mb-3">You can only select voters from your assigned location</p>

                        <div class="relative" @click.away="voterSearchOpen = false">
                            <input
                                type="text"
                                x-model="voterSearch"
                                {{--
                                    FIXED (Problem 1): Simplified directives.
                                    - @focus now ONLY opens the dropdown.
                                    - @input.debounce handles the search and clears results if < 2 chars.
                                --}}
                                @focus="voterSearchOpen = true"
                                @input.debounce.400ms="if(voterSearch.length >= 2) searchVoters(); else voterResults = []"
                                placeholder="Type at least 2 characters to search..."
                                class="input-field"
                                :class="{ 'border-red-500': !form.voter_id && submitAttempted }"
                                :disabled="loading"
                                autocomplete="off"
                                required
                                lang="ar" />

                            <div x-show="voterSearchOpen"
                                {{-- FIXED (Problem 1): Removed @click.away from here --}}
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-xl max-h-60 overflow-y-auto border-2 border-[#931335]/20"
                                style="display: none;"> {{-- style="display: none;" is OK here, x-show overrides it --}}
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
                                            <div class="font-semibold text-[#622032]" x-text="voter.first_name + ' ' + voter.father_name + ' ' + voter.last_name" lang="ar"></div>
                                            <div class="text-xs text-[#622032]/60 flex items-center gap-2 mt-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span x-text="voter.city_name"></span>
                                                <span>•</span>
                                                <span x-text="voter.register_number"></span>
                                            </div>
                                        </li>
                                    </template>

                                    <template x-if="!voterSearching && voterResults.length === 0 && voterSearch.length >= 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm italic">
                                            No voters found in your location
                                        </li>
                                    </template>

                                    <template x-if="!voterSearching && voterSearch.length < 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm italic">
                                            Type at least 2 characters to search
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        {{-- ... (Selected Voter display is unchanged and correct) ... --}}
                        <div x-show="form.voter_id" x-cloak class="mt-3 p-3 bg-white rounded-lg border-2 border-[#931335]/30">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-[#931335] mb-1">✓ Selected Voter:</p>
                                    <p class="text-sm font-bold text-[#622032]" x-text="selectedVoter?.first_name + ' ' + selectedVoter?.father_name + ' ' + selectedVoter?.last_name" lang="ar"></p>
                                    <p class="text-xs font-bold text-[#622032]/60" x-text="selectedVoter?.mother_full_name" lang="ar"></p>
                                    <div class="flex items-center gap-2 text-xs text-[#622032]/60 mt-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span x-text="selectedVoter?.city_name"></span>
                                        <span>•</span>
                                        <span x-text="selectedVoter?.register_number"></span>
                                    </div>
                                </div>
                                <button type="button" @click="clearVoter()" class="text-red-600 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="!form.voter_id && submitAttempted" x-cloak class="mt-2 text-xs text-red-600">
                            Please select a voter from your location
                        </div>
                    </div>

                    {{-- ... (Auto-filled info is unchanged and correct) ... --}}
                    <div x-show="form.voter_id" x-cloak class="bg-[#fcf7f8] p-4 rounded-lg border border-[#f8f0e2]">
                        <h3 class="text-sm font-bold text-[#622032] mb-3">Requester Information (Auto-filled)</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Full Name:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.first_name + ' ' + selectedVoter?.father_name + ' ' + selectedVoter?.last_name" lang="ar"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Mother's Full Name:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.mother_full_name" lang="ar"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">رقم السجل:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.register_number"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">City:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.city_name"></span>
                            </div>
                            <div x-show="selectedVoter?.phone" class="flex justify-between">
                                <span class="text-[#622032]/60">Phone:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.phone"></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-[#f8f0e2]">
                        <h3 class="text-base font-bold text-[#622032]">Request Details</h3>

                        {{-- ... (Request Type is unchanged and correct) ... --}}
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Request Type * <span class="text-xs font-normal">(نوع الطلب)</span>
                            </label>
                            <select x-model="form.subtype" class="input-field" required :disabled="loading">
                                <option value="">Select Type</option>
                                <option value="تربوية">تربوية (Educational)</option>
                                <option value="طبية">طبية (Medical)</option>
                                <option value="استشفائية">استشفائية (Hospital/Healing)</option>
                                <option value="إجتماعية">إجتماعية (Social)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Reference (PW Member) *
                            </label>
                            {{-- This parent div already had the correct @click.away --}}
                            <div class="relative" @click.away="memberSearchOpen = false">
                                <input
                                    type="text"
                                    x-model="memberSearch"
                                    @focus="memberSearchOpen = true"
                                    @input.debounce.300ms="if(memberSearch.length >= 2) searchMembers(); else memberResults = []"
                                    placeholder="Search PW member (min 2 chars)..."
                                    class="input-field"
                                    :disabled="loading"
                                    autocomplete="off"
                                    lang="ar" />

                                {{--
                                    FIXED (Problem 2): Simplified x-show.
                                    It now stays open even if results are empty.
                                --}}
                                <div x-show="memberSearchOpen"
                                    x-transition
                                    class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                    <ul class="py-1">
                                        <template x-if="memberSearching">
                                            {{-- FIXED (Problem 2): Added spinner --}}
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

                                        <template x-for="member in memberResults" :key="member.id">
                                            <li @click="selectMember(member)"
                                                class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer border-b border-gray-100 last:border-0">
                                                <div class="font-semibold text-[#622032]" x-text="member.first_name + ' ' + member.last_name" lang="ar"></div>
                                                <div class="text-xs text-[#622032]/60" x-text="member.phone"></div>
                                            </li>
                                        </template>

                                        <template x-if="!memberSearching && memberSearch.length < 2">
                                            <li class="px-4 py-3 text-gray-500 text-sm italic">
                                                Type at least 2 characters to search
                                            </li>
                                        </template>

                                        <template x-if="!memberSearching && memberResults.length === 0 && memberSearch.length >= 2">
                                            <li class="px-4 py-3 text-gray-500 text-sm italic">
                                                No members found
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <div x-show="form.reference_member_id" x-cloak class="mt-2 text-sm text-[#622032]">
                                Selected: <span class="font-semibold" x-text="selectedMember?.first_name + ' ' + selectedMember?.last_name" lang="ar"></span>
                            </div>
                        </div>

                        {{-- ... (Amount and Notes are unchanged and correct) ... --}}
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Amount (USD) *
                            </label>
                            <input type="number" step="0.01" min="0" x-model="form.amount" class="input-field" required :disabled="loading">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Notes <span class="text-xs font-normal">(Optional)</span>
                            </label>
                            <textarea x-model="form.notes" rows="4" class="input-field" :disabled="loading"></textarea>
                        </div>
                    </div>

                    {{-- ... (Error Message and Buttons are unchanged and correct) ... --}}
                    <div x-show="errorMessage" x-cloak x-transition class="error-message">
                        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p x-text="errorMessage"></p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="button" @click="submitAsDraft" class="flex-1 btn-secondary" :disabled="loading">
                            <span x-show="!loading || submitAction !== 'draft'">Save as Draft</span>
                            <span x-show="loading && submitAction === 'draft'" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                        <button type="button" @click="submitAndPublish" class="flex-1 btn-primary" :disabled="loading">
                            <span x-show="!loading || submitAction !== 'publish'">Submit & Publish</span>
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

        <!-- Budget Selection Modal (HOR only) -->
        <div x-show="showBudgetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showBudgetModal = false">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showBudgetModal = false"></div>
            <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
                <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full max-w-lg sm:max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="sticky top-0 bg-white rounded-t-3xl sm:rounded-t-2xl p-4 sm:p-6 border-b border-[#f8f0e2]">
                        <h2 class="text-lg sm:text-xl font-bold text-[#622032] mb-2">Select Budget & Ready Date</h2>
                        <p class="text-sm sm:text-base text-[#622032]/70">
                            Amount: <span class="text-[#931335] font-semibold">($<span x-text="form.amount"></span>)</span>
                        </p>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4">
                        <!-- Budget Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Select Budget</label>
                            <select x-model="selectedBudget" @change="updateBudgetPreview" class="input-field text-sm sm:text-base">
                                <option value="">-- Select Budget --</option>
                                <template x-for="budget in budgets" :key="budget.id">
                                    <option :value="budget.id" x-text="`${budget.description} ($${budget.monthly_amount_in_usd})`"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Ready Date Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Ready Date</label>
                            <input type="date" x-model="readyDate" @change="updateBudgetPreview" class="input-field text-sm sm:text-base" :min="new Date().toISOString().split('T')[0]">
                        </div>

                        <!-- Budget Preview -->
                        <div x-show="budgetPreview" class="bg-[#f8f0e2] rounded-lg p-3 sm:p-4 space-y-2">
                            <h3 class="font-semibold text-[#622032] mb-2 text-sm sm:text-base">Budget Preview</h3>
                            <div class="flex justify-between text-xs sm:text-sm">
                                <span class="text-[#622032]/70">Monthly Budget:</span>
                                <span class="font-semibold text-[#622032]">$<span x-text="budgetPreview?.monthly_budget || 0"></span></span>
                            </div>
                            <div class="flex justify-between text-xs sm:text-sm">
                                <span class="text-[#622032]/70">Current Remaining:</span>
                                <span class="font-semibold" :class="budgetPreview?.current_remaining >= 0 ? 'text-green-600' : 'text-red-600'">
                                    $<span x-text="budgetPreview?.current_remaining || 0"></span>
                                </span>
                            </div>
                            <div class="flex justify-between text-xs sm:text-sm border-t border-[#622032]/20 pt-2">
                                <span class="text-[#622032]/70">After Request:</span>
                                <span class="font-bold" :class="budgetPreview?.after_request >= 0 ? 'text-green-600' : 'text-red-600'">
                                    $<span x-text="budgetPreview?.after_request || 0"></span>
                                </span>
                            </div>
                            <div x-show="!budgetPreview?.has_enough" class="text-xs text-red-600 font-semibold mt-2 flex items-start gap-1">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Insufficient budget!</span>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white p-4 sm:p-6 border-t border-[#f8f0e2] flex flex-col sm:flex-row gap-3">
                        <button @click="showBudgetModal = false" class="w-full sm:flex-1 btn-secondary">Cancel</button>
                        <button @click="confirmPublishWithBudget" :disabled="loading || !selectedBudget || !readyDate" class="w-full sm:flex-1 btn-primary">
                            <span x-show="!loading">Publish & Allocate</span>
                            <span x-show="loading">Publishing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function humanitarianForm(isHor) {
        return {
            form: {
                voter_id: '',
                requester_city_id: '',
                subtype: '',
                reference_member_id: '',
                amount: '',
                notes: '',
                action: 'draft',
                budget_id: '',
                ready_date: ''
            },
            loading: false,
            submitAction: '',
            submitAttempted: false,
            errorMessage: '',

            // Budget modal for HOR
            showBudgetModal: false,
            budgets: [],
            selectedBudget: '',
            readyDate: new Date().toISOString().split('T')[0], // Default to today
            budgetPreview: null,
            userIsHor: isHor,

            // Voter search
            voterSearch: '',
            voterSearchOpen: false,
            voterSearching: false,
            voterResults: [],
            selectedVoter: null,
            voterSearchTimeout: null,

            // Member search
            memberSearch: '',
            memberSearchOpen: false,
            memberSearching: false,
            memberResults: [],
            selectedMember: null,
            memberSearchTimeout: null,

            init() {
                // Load all PW members initially
                this.searchMembers();
            },

            async searchVoters() {
                if (this.voterSearch.length < 2) {
                    this.voterResults = [];
                    return;
                }

                this.voterSearching = true;
                try {
                    const response = await fetch(`{{ route('humanitarian.search-voters') }}?search=${encodeURIComponent(this.voterSearch)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    this.voterResults = data;
                } catch (error) {
                    console.error('Voter search error:', error);
                    this.voterResults = [];
                } finally {
                    this.voterSearching = false;
                }
            },

            selectVoter(voter) {
                this.selectedVoter = voter;
                this.form.voter_id = voter.id;
                this.form.requester_city_id = voter.city_id;
                this.voterSearch = voter.full_name;
                this.voterSearchOpen = false;
                this.submitAttempted = false;
            },

            clearVoter() {
                this.selectedVoter = null;
                this.form.voter_id = '';
                this.form.requester_city_id = '';
                this.voterSearch = '';
                this.voterResults = [];
            },

            async searchMembers() {
                if (this.memberSearch.length < 2) {
                    this.memberResults = [];
                    return;
                }

                this.memberSearching = true;
                try {
                    const response = await fetch(`{{ route('humanitarian.search-members') }}?search=${encodeURIComponent(this.memberSearch)}`);
                    this.memberResults = await response.json();
                } catch (error) {
                    console.error('Member search error:', error);
                } finally {
                    this.memberSearching = false;
                }
            },

            selectMember(member) {
                this.selectedMember = member;
                this.form.reference_member_id = member.id;
                this.memberSearch = member.name;
                this.memberSearchOpen = false;
            },

            submitAsDraft() {
                this.form.action = 'draft';
                this.submitAction = 'draft';
                this.submitForm();
            },

            async submitAndPublish() {
                this.submitAttempted = true;

                if (!this.form.voter_id) {
                    this.errorMessage = 'Please select a voter from your assigned location';
                    return;
                }

                if (!this.form.subtype || !this.form.reference_member_id || !this.form.amount) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                // If HOR, show budget modal before publishing
                if (this.userIsHor) {
                    this.form.action = 'publish';
                    this.submitAction = 'publish';
                    await this.showBudgetSelectionModal();
                } else {
                    // Non-HOR users just publish normally
                    this.form.action = 'publish';
                    this.submitAction = 'publish';
                    this.submitForm();
                }
            },

            async showBudgetSelectionModal() {
                try {
                    // Fetch user's zone budgets
                    const response = await fetch('/api/budgets/my-zones', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.budgets = data.budgets;

                        // Reset selections
                        this.selectedBudget = '';
                        this.readyDate = '';
                        this.budgetPreview = null;

                        this.showBudgetModal = true;
                    } else {
                        this.errorMessage = 'Failed to load budgets';
                    }
                } catch (error) {
                    this.errorMessage = 'Failed to load budgets';
                }
            },

            async updateBudgetPreview() {
                if (!this.selectedBudget || !this.readyDate) {
                    this.budgetPreview = null;
                    return;
                }

                try {
                    const response = await fetch('/api/budgets/preview', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            budget_id: this.selectedBudget,
                            amount: this.form.amount,
                            ready_date: this.readyDate
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.budgetPreview = data;
                    }
                } catch (error) {
                    console.error('Failed to fetch budget preview');
                }
            },

            async confirmPublishWithBudget() {
                this.form.budget_id = this.selectedBudget;
                this.form.ready_date = this.readyDate;
                this.showBudgetModal = false;
                await this.submitForm();
            },

            async submitForm() {
                this.submitAttempted = true;

                if (!this.form.voter_id) {
                    this.errorMessage = 'Please select a voter from your assigned location';
                    return;
                }

                if (!this.form.subtype || !this.form.reference_member_id || !this.form.amount) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('{{ route("humanitarian.store") }}', {
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
                        window.location.href = data.redirect;
                    } else {
                        this.errorMessage = data.message || 'Failed to create request';
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