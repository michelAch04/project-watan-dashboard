@extends('layouts.app')

@section('title', 'Request Details')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="requestDetails()">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('public-requests.active') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Request Details</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">

            <!-- Request Number & Status -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h2 class="text-2xl font-bold text-[#622032] mb-1">{{ $request->request_number }}</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($request->requestStatus->name === 'draft') bg-gray-100 text-gray-700
                        @elseif($request->requestStatus->name === 'published') bg-blue-100 text-blue-700
                        @elseif($request->requestStatus->name === 'approved') bg-green-100 text-green-700
                        @elseif($request->requestStatus->name === 'rejected') bg-red-100 text-red-700
                        @elseif($request->requestStatus->name === 'final_approval') bg-purple-100 text-purple-700
                        @elseif($request->requestStatus->name === 'ready_for_collection') bg-amber-100 text-amber-700
                        @elseif($request->requestStatus->name === 'collected') bg-green-100 text-green-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ $request->requestStatus->name }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-[#622032]/60">Submitted on {{ $request->request_date->format('F d, Y') }}</p>
                </div>

                <!-- Rejection Reason -->
                @if($request->requestStatus->name === 'rejected' && $request->rejection_reason)
                <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-red-800 mb-1">Rejection Reason:</p>
                            <p class="text-sm text-red-700">{{ $request->rejection_reason }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Requester Information -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-lg font-bold text-[#622032] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Requester Information
                </h3>

                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-[#622032]/60 mb-1">Full Name (اسم مقدم الطلب)</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->publicRequest->requester_full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#622032]/60 mb-1">Phone Number</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->publicRequest->requester_phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">City (البلدة)</p>
                        <div class="p-3 bg-[#fcf7f8] rounded-lg">
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->publicRequest->city->name }} - {{ $request->publicRequest->city->name_ar }}</p>
                            <p class="text-xs text-[#622032]/60 mt-1">{{ $request->publicRequest->city->zone->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Details -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-lg font-bold text-[#622032] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M4.5 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5h-.75V3.75a.75.75 0 000-1.5h-15zM9 6a.75.75 0 000 1.5h1.5a.75.75 0 000-1.5H9zm-.75 3.75A.75.75 0 019 9h1.5a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM9 12a.75.75 0 000 1.5h1.5a.75.75 0 000-1.5H9zm3.75-5.25A.75.75 0 0113.5 6H15a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM13.5 9a.75.75 0 000 1.5H15A.75.75 0 0015 9h-1.5zm-.75 3.75a.75.75 0 01.75-.75H15a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM9 19.5v-2.25a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v2.25a.75.75 0 01-.75.75h-4.5A.75.75 0 019 19.5z" clip-rule="evenodd" />
                    </svg>
                    Public Facility Request Details
                </h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">Description (التفاصيل)</p>
                        <div class="p-3 bg-[#fcf7f8] rounded-lg">
                            <p class="text-sm text-[#622032]">{{ $request->publicRequest->description }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-[#622032]/60 mb-1">Amount</p>
                            <p class="text-2xl font-bold text-[#931335]">${{ number_format($request->publicRequest->amount, 2) }}</p>
                        </div>
                        @if($request->referenceMember)
                        <div>
                            <p class="text-xs text-[#622032]/60 mb-1">Reference (PW Member)</p>
                            <div class="p-3 bg-[#fcf7f8] rounded-lg">
                                <p class="text-sm font-semibold text-[#622032]">{{ $request->referenceMember->first_name }} {{ $request->referenceMember->father_name }} {{ $request->referenceMember->last_name }}</p>
                                <p class="text-xs text-[#622032]/60">{{ $request->referenceMember->phone }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($request->publicRequest->notes)
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">Notes</p>
                        <div class="p-3 bg-[#fcf7f8] rounded-lg">
                            <p class="text-sm text-[#622032]">{{ $request->publicRequest->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Supporting Documents -->
            @if($request->publicRequest->supporting_documents)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <x-supporting-documents
                    :documents="$request->publicRequest->supporting_documents"
                    :can-download="auth()->user()->hasRole('hor') || auth()->user()->hasRole('admin')" />
            </div>
            @endif

            <!-- Workflow Information -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-lg font-bold text-[#622032] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Workflow Status
                </h3>

                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-[#931335]"></div>
                        <div class="flex-1">
                            <p class="text-xs text-[#622032]/60">Submitted by</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->sender->username }}</p>
                        </div>
                    </div>

                    @if($request->currentUser)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <div class="flex-1">
                            <p class="text-xs text-[#622032]/60">Current Handler</p>
                            <p class="text-sm font-semibold text-[#931335]">{{ $request->currentUser->username }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->publicRequest->budget)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                        <div class="flex-1">
                            <p class="text-xs text-[#622032]/60">Budget</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->publicRequest->budget->description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->ready_date)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <div class="flex-1">
                            <p class="text-xs text-[#622032]/60">Ready Date</p>
                            <p class="text-sm font-semibold text-[#931335]">{{ \Carbon\Carbon::parse($request->ready_date)->format('M d, Y') }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                        <div class="flex-1">
                            <p class="text-xs text-[#622032]/60">Last Updated</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                @if($request->canEdit(auth()->user()))
                <a href="{{ route('public-requests.edit', $request->id) }}"
                    class="block w-full btn-primary text-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Request
                </a>
                @endif

                @if($request->canApproveReject(auth()->user()))
                <div class="grid grid-cols-2 gap-3">
                    <button @click="showApproveModal"
                        class="btn-primary bg-green-600 hover:bg-green-700">
                        Approve
                    </button>
                    <button @click="showRejectModal"
                        class="btn-primary bg-red-600 hover:bg-red-700">
                        Reject
                    </button>
                </div>
                @endif

                <div class="grid @if($request->requestStatus->name !== 'final_approval' && $request->requestStatus->name !== 'ready_for_collection')
                                        grid-cols-1
                                    @else grid-cols-2 @endif gap-3">
                    @can('mark_ready_public')
                    @if($request->requestStatus->name === 'final_approval')
                    <button @click="markReady"
                        class="block w-full btn-primary bg-amber-600 hover:bg-amber-700">
                        Mark as Ready
                    </button>
                    @endif
                    @endcan

                    @can('mark_collected_public')
                    @if($request->requestStatus->name === 'ready_for_collection')
                    <button @click="markCollected"
                        class="block w-full btn-primary bg-purple-600 hover:bg-purple-700">
                        Mark as Collected
                    </button>
                    @endif
                    @endcan

                    @can('final_approve_public')
                    @if(in_array($request->requestStatus->name, ['final_approval', 'ready_for_collection', 'collected']))
                    <a href="{{ route('public-requests.download', $request->id) }}"
                        class="block w-full btn-secondary text-center">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download
                    </a>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div x-show="showApprove" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showApprove = false">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showApprove = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <h2 class="text-xl font-bold text-[#622032] mb-4">Approve Request</h2>
                    <p class="text-[#622032]/70 mb-6">
                        Are you sure you want to approve request <strong>{{ $request->request_number }}</strong>?
                    </p>
                    <div class="flex gap-3">
                        <button @click="showApprove = false" class="flex-1 btn-secondary">Cancel</button>
                        <button @click="confirmApprove" :disabled="processing" class="flex-1 btn-primary">
                            <span x-show="!processing">Approve</span>
                            <span x-show="processing">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div x-show="showReject" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showReject = false">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showReject = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <h2 class="text-xl font-bold text-[#622032] mb-4">Reject Request</h2>
                    <p class="text-[#622032]/70 mb-4">
                        Please provide a reason for rejecting request <strong>{{ $request->request_number }}</strong>:
                    </p>
                    <textarea x-model="rejectionReason" rows="4" class="input-field mb-4" placeholder="Enter rejection reason..."></textarea>
                    <div class="flex gap-3">
                        <button @click="showReject = false" class="flex-1 btn-secondary">Cancel</button>
                        <button @click="confirmReject" :disabled="processing || !rejectionReason" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-all active:scale-95">
                            <span x-show="!processing">Reject</span>
                            <span x-show="processing">Processing...</span>
                        </button>
                    </div>
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
                            Final approval for request <strong class="break-all">{{ $request->request_number }}</strong>
                            <span class="text-[#931335] font-semibold">($<span x-text="requestAmount"></span>)</span>
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
                        <button @click="confirmFinalApprove" :disabled="processing || !selectedBudget || !readyDate || !budgetPreview?.has_enough" class="w-full sm:flex-1 btn-primary">
                            <span x-show="!processing">Approve & Allocate</span>
                            <span x-show="processing">Processing...</span>
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

    function requestDetails() {
        return {
            showApprove: false,
            showReject: false,
            showBudgetModal: false,
            rejectionReason: '',
            processing: false,
            budgets: [],
            selectedBudget: '',
            readyDate: '',
            budgetPreview: null,
            requestAmount: 0,

            showApproveModal() {
                this.showApprove = true;
            },

            showRejectModal() {
                this.rejectionReason = '';
                this.showReject = true;
            },

            async confirmApprove() {
                this.processing = true;
                try {
                    const response = await fetch('{{ route("public-requests.approve", $request->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    console.log(data.needs_budget_selection);
                    if (response.ok && data.success) {
                        if (data.needs_budget_selection) {
                            // HOR needs to select budget - show modal
                            this.showApprove = false;
                            await this.showBudgetSelectionModal();
                        } else {
                            window.location.href = data.redirect;
                        }
                    } else {
                        alert(data.message || 'Failed to approve request');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                } finally {
                    this.processing = false;
                }
            },

            async showBudgetSelectionModal() {
                try {
                    // Fetch user's zone budgets for public requests
                    const response = await fetch('/api/budgets/my-zones?request_type=public', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.budgets = data.budgets;

                        // Get request amount
                        const requestResponse = await fetch(`/public-requests/{{ $request->id }}/amount`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        });
                        const requestData = await requestResponse.json();
                        this.requestAmount = requestData.amount;

                        // Reset selections
                        this.selectedBudget = '';
                        this.readyDate = '';
                        this.budgetPreview = null;

                        this.showBudgetModal = true;
                    } else {
                        alert('Failed to load budgets');
                    }
                } catch (error) {
                    alert('Failed to load budgets');
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
                            amount: this.requestAmount,
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

            async confirmFinalApprove() {
                this.processing = true;
                try {
                    const response = await fetch(`/public-requests/{{ $request->id }}/final-approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            budget_id: this.selectedBudget,
                            ready_date: this.readyDate
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to approve request');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                } finally {
                    this.processing = false;
                }
            },

            async confirmReject() {
                this.processing = true;
                try {
                    const response = await fetch('{{ route("public-requests.reject", $request->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            rejection_reason: this.rejectionReason
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        window.location.href = data.redirect;
                    } else {
                        alert(data.message || 'Failed to reject request');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                } finally {
                    this.processing = false;
                }
            },

            async markReady() {
                if (!confirm('Mark this request as ready for collection?')) return;

                try {
                    const response = await fetch('{{ route("public-requests.mark-ready", $request->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to mark as ready');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                }
            },

            async markCollected() {
                if (!confirm('Mark this request as collected?')) return;

                try {
                    const response = await fetch('{{ route("public-requests.mark-collected", $request->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to mark as collected');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                }
            }
        }
    }
</script>
@endpush