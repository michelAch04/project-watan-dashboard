@extends('layouts.app')

@section('title', 'Diapers Request Details')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="requestDetails()">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('diapers-requests.active') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Request Details</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4">
        <div class="page-container space-y-4">

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
                    <p class="text-sm text-[#622032]/60">Submitted on {{ Carbon\Carbon::parse($request->request_date)->format('F d, Y') }}</p>
                </div>

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

                <!-- Warning: Voter has rejected requests -->
                @if($voterHasRejectedRequests)
                <div class="mt-4 p-4 bg-amber-50 border-l-4 border-amber-500 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-amber-800 mb-2">Warning: Previous Rejected Requests</p>
                            <p class="text-sm text-amber-700 mb-2">This voter has {{ $rejectedRequests->count() }} rejected request(s):</p>
                            <ul class="text-xs text-amber-700 space-y-1">
                                @foreach($rejectedRequests as $rejReq)
                                <li class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-amber-600 rounded-full"></span>
                                    @if($rejReq instanceof \App\Models\HumanitarianRequest)
                                    <a href="{{ route('humanitarian.show', $rejReq->requestHeader->id) }}" class="hover:underline font-semibold">
                                        {{ $rejReq->requestHeader->request_number }}
                                    </a>
                                    @else
                                    <a href="{{ route('diapers-requests.show', $rejReq->requestHeader->id) }}" class="hover:underline font-semibold">
                                        {{ $rejReq->requestHeader->request_number }}
                                    </a>
                                    @endif
                                    <span>- Rejected on {{ $rejReq->requestHeader->updated_at->format('M d, Y') }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>

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
                            <p class="text-xs text-[#622032]/60 mb-1">Full Name</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->diapersRequest->requester_full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-[#622032]/60 mb-1">City (البلدة)</p>
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->diapersRequest->voter->city->name }} - {{ $request->diapersRequest->voter->city->name_ar }}</p>
                        </div>
                    </div>

                    @if($request->diapersRequest->voter->ro_number)
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">رقم السجل</p>
                        <p class="text-sm font-semibold text-[#622032]">{{ $request->diapersRequest->voter->ro_number }}</p>
                    </div>
                    @endif

                    @if($request->diapersRequest->voter->phone)
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">Phone Number</p>
                        <p class="text-sm font-semibold text-[#622032]">{{ $request->diapersRequest->voter->phone }}</p>
                    </div>
                    @endif

                    @if($request->diapersRequest->voter)
                    <div class="p-3 bg-[#fef9de] rounded-lg">
                        <p class="text-xs text-[#622032]/60 mb-1">Linked to Voter</p>
                        <p class="text-sm font-semibold text-[#931335]">{{ $request->diapersRequest->voter->full_name }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-lg font-bold text-[#622032] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                    </svg>
                    Request Details
                </h3>

                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-2">Diaper Items</p>
                        <div class="space-y-2">
                            @foreach($request->diapersRequest->items as $item)
                            <div class="flex items-center justify-between p-3 bg-[#fcf7f8] rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 bg-[#931335] text-white rounded font-semibold text-sm">{{ strtoupper($item['size']) }}</span>
                                    <span class="text-sm text-[#622032]">Size</span>
                                </div>
                                <span class="text-lg font-bold text-[#622032]">x {{ $item['count'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($request->referenceMember)
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">Reference (PW Member)</p>
                        <div class="p-3 bg-[#fcf7f8] rounded-lg">
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->referenceMember->name }}</p>
                            <p class="text-xs text-[#622032]/60">{{ $request->referenceMember->phone }}</p>
                        </div>
                    </div>
                    @endif

                    @if($request->diapersRequest->notes)
                    <div>
                        <p class="text-xs text-[#622032]/60 mb-1">Notes</p>
                        <div class="p-3 bg-[#fcf7f8] rounded-lg">
                            <p class="text-sm text-[#622032]">{{ $request->diapersRequest->notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

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
                            <p class="text-sm font-semibold text-[#622032]">{{ $request->sender->name }}</p>
                        </div>
                    </div>

                    @if($request->currentUser)
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        <div class="flex-1">
                            <p class="text-xs text-[#622032]/60">Current Handler</p>
                            <p class="text-sm font-semibold text-[#931335]">{{ $request->currentUser->name }}</p>
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

            <div class="space-y-3">
                @if($request->canEdit(auth()->user()))
                <a href="{{ route('diapers-requests.edit', $request->id) }}"
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

                <div class="grid grid-cols-1 gap-3">
                    @can('mark_ready_diapers')
                    @if($request->requestStatus->name === 'final_approval')
                    <button @click="markReady"
                        class="block w-full btn-primary bg-amber-600 hover:bg-amber-700">
                        Mark as Ready
                    </button>
                    @endif
                    @endcan

                    @can('mark_collected_diapers')
                    @if($request->requestStatus->name === 'ready_for_collection')
                    <button @click="markCollected"
                        class="block w-full btn-primary bg-purple-600 hover:bg-purple-700">
                        Mark as Collected
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

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
            rejectionReason: '',
            processing: false,

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
                    const response = await fetch('{{ route("diapers-requests.approve", $request->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        window.location.href = data.redirect;
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
                    const response = await fetch('{{ route("diapers-requests.reject", $request->id) }}', {
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
                    const response = await fetch('{{ route("diapers-requests.mark-ready", $request->id) }}', {
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
                    const response = await fetch('{{ route("diapers-requests.mark-collected", $request->id) }}', {
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
