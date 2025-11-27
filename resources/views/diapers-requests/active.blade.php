@extends('layouts.app')

@section('title', 'Active Diapers Requests')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('diapers-requests.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Active Diapers Requests</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4" x-data="activeRequests()">
        <div class="page-container space-y-4">

            <div class="bg-white rounded-xl p-2 shadow-sm border border-[#f8f0e2] overflow-x-auto">
                <div class="flex gap-2 min-w-max">
                    <button @click="statusFilter = 'all'"
                        :class="statusFilter === 'all' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
                        All
                    </button>
                    <button @click="statusFilter = 'published'"
                        :class="statusFilter === 'published' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
                        Published
                    </button>
                    <button @click="statusFilter = 'approved'"
                        :class="statusFilter === 'approved' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
                        Approved
                    </button>
                    <button @click="statusFilter = 'final_approval'"
                        :class="statusFilter === 'final_approval' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
                        Final Approval
                    </button>
                    <button @click="statusFilter = 'ready_for_collection'"
                        :class="statusFilter === 'ready_for_collection' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap">
                        Ready
                    </button>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($requests as $request)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]"
                    x-show="statusFilter === 'all' || statusFilter === '{{ $request->requestStatus->name }}'">

                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-[#622032]">{{ $request->request_number }}</h3>
                            <p class="text-xs text-[#622032]/60">{{ $request->request_date->format('M d, Y') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($request->requestStatus->name === 'published') bg-blue-100 text-blue-700
                            @elseif($request->requestStatus->name === 'approved') bg-green-100 text-green-700
                            @elseif($request->requestStatus->name === 'final_approval') bg-purple-100 text-purple-700
                            @elseif($request->requestStatus->name === 'ready_for_collection') bg-amber-100 text-amber-700
                            @else bg-gray-100 text-gray-700
                            @endif">
                            {{ $request->requestStatus->name }}
                        </span>
                    </div>

                    <div class="mb-3 pb-3 border-b border-[#f8f0e2]">
                        <div class="flex items-start gap-2 mb-2">
                            <svg class="w-4 h-4 text-[#931335] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-[#622032]" lang="ar">{{ $request->diapersRequest->requester_full_name }}</p>
                                <p class="text-xs text-[#622032]/60" lang="ar">{{ $request->diapersRequest->voter->city->name }} @if($request->diapersRequest->voter->register_number) • {{ $request->diapersRequest->voter->register_number }} @endif</p>
                            </div>
                        </div>

                        <div class="text-xs text-[#622032]/60 space-y-1">
                            @foreach($request->diapersRequest->items as $item)
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 bg-[#fef9de] rounded font-semibold">{{ strtoupper($item['size']) }}</span>
                                <span>x {{ $item['count'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3 space-y-2 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Submitted by:</span>
                            <span class="font-semibold text-[#622032]">{{ $request->sender->username }}</span>
                        </div>
                        @if($request->currentUser)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Current handler:</span>
                            <span class="font-semibold text-[#931335]">{{ $request->currentUser->username }}</span>
                        </div>
                        @endif
                        @if($request->referenceMember)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Reference:</span>
                            <span class="font-semibold text-[#622032]" lang="ar">{{ trim($request->referenceMember->first_name . ' ' . $request->referenceMember->father_name . ' ' . $request->referenceMember->last_name) }}</span>
                        </div>
                        @endif
                        @if($request->diapersRequest->budget)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Budget:</span>
                            <span class="font-semibold text-[#622032]">{{ $request->diapersRequest->budget->description }}</span>
                        </div>
                        @endif
                        @if($request->ready_date)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Ready date:</span>
                            <span class="font-semibold text-[#931335]">{{ \Carbon\Carbon::parse($request->ready_date)->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 pt-3 border-t border-[#f8f0e2]">
                        <a href="{{ route('diapers-requests.show', $request->id) }}"
                            class="flex-1 bg-[#f8f0e2] hover:bg-[#dfd1ba] text-[#622032] font-semibold text-sm py-2 px-4 rounded-lg text-center transition-all active:scale-95">
                            View Details
                        </a>

                        <div class="grid grid-cols-2 gap-1">
                            @if($request->canApproveReject(auth()->user()))
                            <button @click="showApproveModal({{ $request->id }}, '{{ $request->request_number }}', {{ json_encode($request->diapersRequest->items) }})"
                                class="bg-green-600 hover:bg-green-700 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95">
                                Approve
                            </button>
                            <button @click="showRejectModal({{ $request->id }}, '{{ $request->request_number }}')"
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95">
                                Reject
                            </button>
                            @endif

                            @can('mark_ready_diapers')
                            @if($request->requestStatus->name === 'final_approval')
                            <button @click="markReady({{ $request->id }})"
                                class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95">
                                Mark as Ready
                            </button>
                            @endif
                            @endcan

                            @can('mark_collected_diapers')
                            @if($request->requestStatus->name === 'ready_for_collection')
                            <button @click="markCollected({{ $request->id }})"
                                class="bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95">
                                Mark as Collected
                            </button>
                            @endif
                            @endcan
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Active Requests</h3>
                    <p class="text-sm text-[#622032]/60">All requests have been completed</p>
                </div>
                @endforelse
            </div>

            @if($requests->hasPages())
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                {{ $requests->links() }}
            </div>
            @endif
        </div>

        <div x-show="showApprove" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showApprove = false">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showApprove = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <h2 class="text-xl font-bold text-[#622032] mb-4">Approve Request</h2>
                    <p class="text-[#622032]/70 mb-6">
                        Are you sure you want to approve request <strong x-text="selectedRequestNumber"></strong>?
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

        <div x-show="showReject" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showReject = false">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showReject = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                    <h2 class="text-xl font-bold text-[#622032] mb-4">Reject Request</h2>
                    <p class="text-[#622032]/70 mb-4">
                        Please provide a reason for rejecting request <strong x-text="selectedRequestNumber"></strong>:
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
                            Final approval for request <strong class="break-all" x-text="selectedRequestNumber"></strong>
                        </p>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4">
                        <!-- Budget Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Select Budget</label>
                            <select x-model="selectedBudget" @change="updateBudgetStock" class="input-field text-sm sm:text-base">
                                <option value="">-- Select Budget --</option>
                                <template x-for="budget in budgets" :key="budget.id">
                                    <option :value="budget.id" x-text="`${budget.description} (${budget.zone_name})`"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Ready Date Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Ready Date</label>
                            <input type="date" x-model="readyDate" @change="updateBudgetStock" class="input-field text-sm sm:text-base" :min="new Date().toISOString().split('T')[0]">
                        </div>

                        <!-- Stock Preview -->
                        <div x-show="budgetStock" x-cloak class="bg-[#f8f0e2] rounded-lg p-3 sm:p-4 space-y-2">
                            <h3 class="font-semibold text-[#622032] mb-2 text-sm sm:text-base">Available Stock</h3>
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                <template x-for="(count, size) in budgetStock" :key="size">
                                    <div class="bg-white rounded px-2 py-1 text-center">
                                        <div class="text-xs font-bold text-[#622032] uppercase" x-text="size"></div>
                                        <div class="text-sm font-semibold" :class="count > 0 ? 'text-green-600' : 'text-red-600'" x-text="count"></div>
                                    </div>
                                </template>
                            </div>
                            <div x-show="!hasEnoughStock" class="text-xs text-red-600 font-semibold mt-2 flex items-start gap-1">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Insufficient stock for some requested sizes!</span>
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

    // Helper function to handle PW member creation response
    function handlePwMemberCreation(data) {
        if (data.pw_member_created) {
            // Show success toast
            const toast = document.createElement('div');
            toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-semibold">PW Member created successfully!</span>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);

            // Redirect to assign-followers page
            setTimeout(() => {
                window.location.href = data.redirect + '?auto_created=1';
            }, 1000);
            return true;
        }
        return false;
    }

    function activeRequests() {
        return {
            statusFilter: 'all',
            showApprove: false,
            showReject: false,
            showBudgetModal: false,
            selectedRequestId: null,
            selectedRequestNumber: '',
            selectedRequestItems: [],
            rejectionReason: '',
            processing: false,
            budgets: [],
            selectedBudget: '',
            readyDate: new Date().toISOString().split('T')[0],
            budgetStock: null,
            hasEnoughStock: true,

            showApproveModal(id, number, items = []) {
                this.selectedRequestId = id;
                this.selectedRequestNumber = number;
                this.selectedRequestItems = items;
                this.showApprove = true;
            },

            showRejectModal(id, number) {
                this.selectedRequestId = id;
                this.selectedRequestNumber = number;
                this.rejectionReason = '';
                this.showReject = true;
            },

            async confirmApprove() {
                this.processing = true;
                try {
                    const response = await fetch(`/diapers-requests/${this.selectedRequestId}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        if (data.needs_budget_selection) {
                            // HOR needs to select budget
                            this.showApprove = false;
                            await this.showBudgetSelectionModal();
                        } else {
                            window.location.reload();
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
                    // Fetch available diaper budgets
                    const response = await fetch('{{ route("diapers-requests.get-diaper-budgets") }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const data = await response.json();

                    if (response.ok) {
                        this.budgets = data;

                        // Reset selections
                        this.selectedBudget = '';
                        this.readyDate = new Date().toISOString().split('T')[0];
                        this.budgetStock = null;
                        this.hasEnoughStock = true;

                        this.showBudgetModal = true;
                    } else {
                        alert('Failed to load budgets');
                    }
                } catch (error) {
                    alert('Failed to load budgets');
                }
            },

            async updateBudgetStock() {
                if (!this.selectedBudget || !this.readyDate) {
                    this.budgetStock = null;
                    return;
                }

                try {
                    const response = await fetch(`{{ route("diapers-requests.get-remaining-stock") }}?budget_id=${this.selectedBudget}&ready_date=${this.readyDate}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    const data = await response.json();
                    if (response.ok) {
                        this.budgetStock = data.remaining_stock;

                        // Check if there's enough stock for all requested items
                        this.hasEnoughStock = this.selectedRequestItems.every(item => {
                            const availableCount = this.budgetStock[item.size] || 0;
                            return availableCount >= item.count;
                        });
                    }
                } catch (error) {
                    console.error('Failed to fetch budget stock');
                }
            },

            async confirmFinalApprove() {
                if (!this.selectedBudget || !this.readyDate) {
                    alert('Please select a budget and ready date');
                    return;
                }

                this.processing = true;
                try {
                    const response = await fetch(`/diapers-requests/${this.selectedRequestId}/final-approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            diaper_budget_id: this.selectedBudget,
                            ready_date: this.readyDate
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        if (handlePwMemberCreation(data)) return;
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
                    const response = await fetch(`/diapers-requests/${this.selectedRequestId}/reject`, {
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
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to reject request');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                } finally {
                    this.processing = false;
                }
            },

            async markReady(id) {
                if (!confirm('Mark this request as ready for collection?')) return;

                try {
                    const response = await fetch(`/diapers-requests/${id}/mark-ready`, {
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

            async markCollected(id) {
                if (!confirm('Mark this request as collected?')) return;

                try {
                    const response = await fetch(`/diapers-requests/${id}/mark-collected`, {
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
