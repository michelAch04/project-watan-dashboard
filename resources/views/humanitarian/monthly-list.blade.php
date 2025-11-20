@extends('layouts.app')

@section('title', 'Monthly List')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="monthlyList()">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('humanitarian.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                            <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Monthly Request List</h1>
                    </div>
                    <button @click="showPublishModal = true" :disabled="!hasRequests" class="btn-primary text-sm">
                        Publish All
                    </button>
                </div>
                <p class="text-sm text-[#622032]/60 mt-2 ml-12">{{ \Carbon\Carbon::create($currentYear, $currentMonth)->format('F Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">

            <!-- Info Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-blue-900 mb-1">About Monthly Lists</h3>
                        <p class="text-sm text-blue-800">Add recurring requests here. When you publish, all requests will be created as new submissions for this month.</p>
                    </div>
                </div>
            </div>

            <!-- Requests List -->
            <div class="space-y-3">
                @forelse($monthlyListItems as $item)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">

                    <!-- Request Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-[#622032]">{{ $item->requestHeader->request_number }}</h3>
                            <p class="text-xs text-[#622032]/60">Template Request</p>
                        </div>
                        <button @click="removeRequest({{ $item->id }})" class="p-2 hover:bg-red-50 rounded-lg transition-all">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Requester Info -->
                    <div class="mb-3 pb-3 border-b border-[#f8f0e2]">
                        <div class="flex items-start gap-2 mb-2">
                            <svg class="w-4 h-4 text-[#931335] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-[#622032]">{{ $item->requestHeader->humanitarianRequest->requester_full_name }}</p>
                                <p class="text-xs text-[#622032]/60">{{ $item->requestHeader->humanitarianRequest->voter->city->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-[#622032]/60">
                            <span class="px-2 py-1 bg-[#fef9de] rounded">{{ $item->requestHeader->humanitarianRequest->subtype }}</span>
                            <span>•</span>
                            <span class="font-semibold text-[#931335]">${{ number_format($item->requestHeader->humanitarianRequest->amount, 2) }}</span>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="flex justify-end">
                        <a href="{{ route('humanitarian.show', $item->requestHeader->id) }}" class="text-sm text-[#931335] hover:underline">
                            View Original Request
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-12 bg-white rounded-xl border border-[#f8f0e2]">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Monthly Requests</h3>
                    <p class="text-sm text-[#622032]/60 mb-4">Add requests from your completed requests to create recurring monthly submissions</p>
                    <a href="{{ route('humanitarian.completed') }}" class="btn-primary inline-block">
                        Browse Completed Requests
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Publish Confirmation Modal -->
    <div x-show="showPublishModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showPublishModal = false">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showPublishModal = false"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
                <h2 class="text-xl font-bold text-[#622032] mb-4">Publish Monthly Requests</h2>
                <p class="text-[#622032]/70 mb-6">
                    Are you sure you want to publish all {{ $monthlyListItems->count() }} request(s) from your monthly list?
                    This will create new requests with today's date.
                </p>
                <div class="flex gap-3">
                    <button @click="showPublishModal = false" class="flex-1 btn-secondary">Cancel</button>
                    <button @click="confirmPublish" :disabled="processing" class="flex-1 btn-primary">
                        <span x-show="!processing">Publish All</span>
                        <span x-show="processing">Publishing...</span>
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

function monthlyList() {
    return {
        showPublishModal: false,
        processing: false,
        hasRequests: {{ $monthlyListItems->count() > 0 ? 'true' : 'false' }},

        async removeRequest(id) {
            if (!confirm('Remove this request from monthly list?')) return;

            try {
                const response = await fetch(`/monthly-list/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to remove request');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            }
        },

        async confirmPublish() {
            this.processing = true;
            try {
                const response = await fetch('/monthly-list/publish-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        month: {{ $currentMonth }},
                        year: {{ $currentYear }}
                    })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Failed to publish requests');
                }
            } catch (error) {
                alert('Network error. Please try again.');
            } finally {
                this.processing = false;
            }
        }
    }
}
</script>
@endpush
