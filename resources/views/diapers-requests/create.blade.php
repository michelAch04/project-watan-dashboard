@extends('layouts.app')

@section('title', 'Create Diapers Request')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="diapersForm(@json(auth()->user()->hasRole('hor')))" x-init="init()">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('diapers-requests.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Create Diapers Request</h1>
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
                                <span class="text-[#622032]/60">Register Number:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedVoter?.register_number" lang="ar"></span>
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

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Reference (PW Member) *
                            </label>
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

                                <div x-show="memberSearchOpen"
                                    x-transition
                                    class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                    <ul class="py-1">
                                        <template x-if="memberSearching">
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
                                                <div class="font-semibold text-[#622032]" x-text="member.first_name + ' ' + member.father_name + ' ' + member.last_name" lang="ar"></div>
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
                                Selected: <span class="font-semibold" x-text="selectedMember?.first_name + ' ' + selectedMember?.father_name + ' ' + selectedMember?.last_name" lang="ar"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-3">
                                Diaper Items *
                            </label>

                            <div class="space-y-3">
                                <template x-for="(item, index) in form.items" :key="index">
                                    <div class="p-3 bg-[#fcf7f8] rounded-lg border border-[#f8f0e2]">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-[#622032] mb-1">Size</label>
                                                <select x-model="item.size" class="input-field text-sm" required>
                                                    <option value="">Select Size</option>
                                                    <template x-for="(count, size) in availableSizes" :key="size">
                                                        <option :value="size" x-text="size.toUpperCase() + (count > 0 ? ` (${count} available)` : ' (Out of stock)')"></option>
                                                    </template>
                                                </select>
                                            </div>
                                            <div class="flex gap-2">
                                                <div class="flex-1">
                                                    <label class="block text-xs font-semibold text-[#622032] mb-1">Count</label>
                                                    <input type="number" min="1" x-model="item.count" class="input-field text-sm" required>
                                                </div>
                                                <div class="flex items-end">
                                                    <button type="button" @click="removeItem(index)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" @click="addItem" class="w-full p-3 border-2 border-dashed border-[#931335]/30 rounded-lg text-[#931335] hover:bg-[#fcf7f8] transition-all">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Diaper Item
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Notes <span class="text-xs font-normal">(Optional)</span>
                            </label>
                            <textarea x-model="form.notes" rows="4" class="input-field" :disabled="loading"></textarea>
                        </div>
                    </div>

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
    </div>

    <!-- Budget Selection Modal (HOR only) -->
    <div x-show="showBudgetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showBudgetModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showBudgetModal = false"></div>
        <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
            <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full max-w-lg sm:max-h-[90vh] overflow-y-auto" @click.stop>
                <div class="sticky top-0 bg-white rounded-t-3xl sm:rounded-t-2xl p-4 sm:p-6 border-b border-[#f8f0e2]">
                    <h2 class="text-lg sm:text-xl font-bold text-[#622032] mb-2">Select Budget & Ready Date</h2>
                    <p class="text-sm sm:text-base text-[#622032]/70">
                        Items: <span class="text-[#931335] font-semibold" x-text="form.items.length + ' item(s)'"></span>
                    </p>
                </div>

                <div class="p-4 sm:p-6 space-y-4">
                    <!-- Budget Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-[#622032] mb-2">Select Budget</label>
                        <select x-model="selectedBudget" @change="updateBudgetPreview" class="input-field text-sm sm:text-base">
                            <option value="">-- Select Budget --</option>
                            <template x-for="budget in budgets" :key="budget.id">
                                <option :value="budget.id" x-text="`${budget.description} (${budget.zone_name})`"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Ready Date Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-[#622032] mb-2">Ready Date</label>
                        <input type="date" x-model="readyDate" @change="updateBudgetPreview" class="input-field text-sm sm:text-base" :min="new Date().toISOString().split('T')[0]">
                    </div>

                    <!-- Stock Preview -->
                    <div x-show="budgetPreview" class="bg-[#f8f0e2] rounded-lg p-3 sm:p-4 space-y-2">
                        <h3 class="font-semibold text-[#622032] mb-2 text-sm sm:text-base">Available Stock</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="(count, size) in budgetPreview?.stock" :key="size">
                                <div class="bg-white rounded px-2 py-1">
                                    <div class="text-xs uppercase font-bold text-[#622032]" x-text="size"></div>
                                    <div class="text-sm font-semibold" :class="count > 0 ? 'text-green-600' : 'text-red-600'" x-text="count"></div>
                                </div>
                            </template>
                        </div>
                        <div x-show="!budgetPreview?.has_enough_stock" class="text-xs text-red-600 font-semibold mt-2 flex items-start gap-1">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span>Insufficient stock!</span>
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
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function diapersForm(isHor) {
        return {
            isHor: isHor,
            form: {
                voter_id: '',
                requester_city_id: '',
                reference_member_id: '',
                items: [{ size: '', count: '' }],
                notes: '',
                action: 'draft',
                diaper_budget_id: '',
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
            readyDate: new Date().toISOString().split('T')[0],
            budgetPreview: null,

            voterSearch: '',
            voterSearchOpen: false,
            voterSearching: false,
            voterResults: [],
            selectedVoter: null,
            voterSearchTimeout: null,

            memberSearch: '',
            memberSearchOpen: false,
            memberSearching: false,
            memberResults: [],
            selectedMember: null,
            memberSearchTimeout: null,

            availableSizes: {},

            async init() {
                this.searchMembers();
                // Load available sizes for all users
                await this.loadDefaultSizes();
            },

            async loadDefaultSizes() {
                try {
                    const response = await fetch('{{ route("diapers-requests.get-diaper-budgets") }}');
                    const budgets = await response.json();

                    // Get sizes from first budget's stock
                    if (budgets.length > 0) {
                        this.availableSizes = budgets[0].current_stock || {};
                    }
                } catch (error) {
                    console.error('Error loading default sizes:', error);
                }
            },

            addItem() {
                this.form.items.push({ size: '', count: '' });
            },

            removeItem(index) {
                if (this.form.items.length > 1) {
                    this.form.items.splice(index, 1);
                }
            },

            async searchVoters() {
                if (this.voterSearch.length < 2) {
                    this.voterResults = [];
                    return;
                }

                this.voterSearching = true;
                try {
                    const response = await fetch(`{{ route('diapers-requests.search-voters') }}?search=${encodeURIComponent(this.voterSearch)}`, {
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
                    const response = await fetch(`{{ route('diapers-requests.search-members') }}?search=${encodeURIComponent(this.memberSearch)}`);
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
                this.memberSearch = member.first_name + ' ' + member.father_name + ' ' + member.last_name;
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

                if (!this.form.reference_member_id) {
                    this.errorMessage = 'Please select a reference member';
                    return;
                }

                if (!this.form.items.length || !this.form.items.every(item => item.size && item.count)) {
                    this.errorMessage = 'Please add at least one diaper item with size and count';
                    return;
                }

                // If HOR, show budget modal before publishing
                if (this.isHor) {
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
                    // Fetch diaper budgets
                    const response = await fetch('{{ route("diapers-requests.get-diaper-budgets") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const budgets = await response.json();

                    this.budgets = budgets;

                    // Reset selections
                    this.selectedBudget = '';
                    this.readyDate = new Date().toISOString().split('T')[0];
                    this.budgetPreview = null;

                    this.showBudgetModal = true;
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
                    // Get remaining stock for selected budget and date
                    const response = await fetch(`{{ route("diapers-requests.get-remaining-stock") }}?budget_id=${this.selectedBudget}&ready_date=${this.readyDate}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();

                    // Calculate if there's enough stock
                    let hasEnoughStock = true;
                    const requestedQuantities = {};

                    this.form.items.forEach(item => {
                        const size = item.size.toLowerCase();
                        requestedQuantities[size] = (requestedQuantities[size] || 0) + parseInt(item.count);
                    });

                    Object.keys(requestedQuantities).forEach(size => {
                        if (!data.remaining_stock[size] || data.remaining_stock[size] < requestedQuantities[size]) {
                            hasEnoughStock = false;
                        }
                    });

                    this.budgetPreview = {
                        stock: data.remaining_stock,
                        has_enough_stock: hasEnoughStock
                    };
                } catch (error) {
                    console.error('Failed to fetch budget preview');
                }
            },

            async confirmPublishWithBudget() {
                this.form.diaper_budget_id = this.selectedBudget;
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

                if (!this.form.reference_member_id) {
                    this.errorMessage = 'Please select a reference member';
                    return;
                }

                if (!this.form.items.length) {
                    this.errorMessage = 'Please add at least one diaper item';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('{{ route("diapers-requests.store") }}', {
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
