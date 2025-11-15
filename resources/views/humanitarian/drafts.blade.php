@extends('layouts.app')

@section('title', 'Drafts & Rejected')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('humanitarian.index') }}" @click.prevent="window.history.length > 1 ? window.history.back() : window.location.href = '{{ route('humanitarian.index') }}'" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Drafts & Rejected</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4" x-data="{ typeFilter: 'all' }">
        <div class="page-container space-y-4">
            
            <!-- Filter Tabs -->
            <div class="bg-white rounded-xl p-2 shadow-sm border border-[#f8f0e2]">
                <div class="flex gap-2">
                    <button @click="typeFilter = 'all'" 
                            :class="typeFilter === 'all' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                        All
                    </button>
                    <button @click="typeFilter = 'draft'" 
                            :class="typeFilter === 'draft' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                        Drafts
                    </button>
                    <button @click="typeFilter = 'rejected'" 
                            :class="typeFilter === 'rejected' ? 'bg-[#931335] text-white' : 'bg-[#fcf7f8] text-[#622032]'"
                            class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                        Rejected
                    </button>
                </div>
            </div>

            <!-- Requests List -->
            <div class="space-y-3">
                @forelse($requests as $request)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]"
                     x-show="typeFilter === 'all' || typeFilter === '{{ $request->requestStatus->name }}'">
                    
                    <!-- Request Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-[#622032]">{{ $request->request_number }}</h3>
                            <p class="text-xs text-[#622032]/60">{{ $request->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold
                            @if($request->requestStatus->name === 'draft') bg-gray-100 text-gray-700
                            @else bg-red-100 text-red-700
                            @endif">
                            {{ $request->requestStatus->name_ar }}
                        </span>
                    </div>

                    <!-- Rejection Reason (if rejected) -->
                    @if($request->requestStatus->name === 'rejected' && $request->rejection_reason)
                    <div class="mb-3 p-3 bg-red-50 border-l-4 border-red-500 rounded">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-red-800 mb-1">Rejection Reason:</p>
                                <p class="text-sm text-red-700">{{ $request->rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Requester Info -->
                    <div class="mb-3 pb-3 border-b border-[#f8f0e2]">
                        <div class="flex items-start gap-2 mb-2">
                            <svg class="w-4 h-4 text-[#931335] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-[#622032]">{{ $request->requester_full_name }}</p>
                                <p class="text-xs text-[#622032]/60">{{ $request->requesterCity->name }} @if($request->requester_ro_number) • {{ $request->requester_ro_number }} @endif</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 text-xs text-[#622032]/60">
                            <span class="px-2 py-1 bg-[#fef9de] rounded">{{ $request->subtype }}</span>
                            <span>•</span>
                            <span class="font-semibold text-[#931335]">${{ number_format($request->amount, 2) }}</span>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="space-y-2 text-xs mb-3">
                        @if($request->referenceMember)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Reference:</span>
                            <span class="font-semibold text-[#622032]">{{ $request->referenceMember->name }}</span>
                        </div>
                        @endif
                        @if($request->notes)
                        <div class="flex items-start gap-2">
                            <span class="text-[#622032]/60">Notes:</span>
                            <span class="text-[#622032]">{{ Str::limit($request->notes, 100) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-3 border-t border-[#f8f0e2]">
                        <a href="{{ route('humanitarian.edit', $request->id) }}"
                           class="flex-1 bg-[#931335] hover:bg-[#622032] text-white font-semibold text-sm py-2 px-4 rounded-lg text-center transition-all active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit & Continue
                        </a>

                        <a href="{{ route('humanitarian.show', $request->id) }}"
                           class="bg-[#f8f0e2] hover:bg-[#dfd1ba] text-[#622032] font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95">
                            View
                        </a>

                        @if($request->canDelete(auth()->user()))
                        <button onclick="deleteDraft({{ $request->id }}, '{{ $request->request_number }}')"
                           class="bg-red-100 hover:bg-red-200 text-red-700 font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Drafts or Rejected Requests</h3>
                    <p class="text-sm text-[#622032]/60">All your requests are submitted</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
async function deleteDraft(requestId, requestNumber) {
    if (!confirm(`Are you sure you want to delete draft #${requestNumber}? This action cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch(`/humanitarian/${requestId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Show success message and reload page
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Failed to delete draft');
        }
    } catch (error) {
        console.error('Error deleting draft:', error);
        alert('An error occurred while deleting the draft');
    }
}
</script>
@endpush
@endsection