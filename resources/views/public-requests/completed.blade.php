@extends('layouts.app')

@section('title', 'Completed Requests')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('public-requests.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Completed Requests</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4" x-data="completedRequests()">
        <div class="page-container space-y-4">

            <!-- Filter and Actions -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <div class="space-y-3">
                    <!-- Filter Form -->
                    <div class="w-full">
                        <label class="block text-sm font-semibold text-[#622032] mb-2">Filter by Month</label>
                        <form method="GET" action="{{ route('public-requests.completed') }}" class="flex flex-row gap-2">
                            <input type="month" 
                                   x-model="selectedMonthYear" 
                                   @change="submitForm()"
                                   class="input-field flex-1 text-sm sm:text-base"
                                   placeholder="Select month">
                            <input type="hidden" name="month" :value="getMonth()">
                            <input type="hidden" name="year" :value="getYear()">
                            <div class="flex gap-2">
                                @if($month && $year)
                                <a href="{{ route('public-requests.completed') }}" class="btn-secondary whitespace-nowrap flex items-center">
                                    <span class="hidden sm:inline">Clear Filter</span>
                                    <span class="sm:hidden">Clear</span>
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Export Button -->
                    @if($month && $year)
                    <div class="w-full">
                        <a href="{{ route('public-requests.export-monthly-pdf', ['month' => $month, 'year' => $year]) }}"
                            class="btn-primary flex items-center justify-center w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export to PDF
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-[#622032]/60">{{ $month && $year ? 'Filtered Results' : 'Total Collected' }}</p>
                        <p class="text-2xl font-bold text-[#622032]">{{ $requests->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Requests List -->
            <div class="space-y-3">
                @forelse($requests as $request)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">

                    <!-- Request Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-[#622032]">{{ $request->request_number }}</h3>
                            <p class="text-xs text-[#622032]/60">
                                Submitted: {{ $request->request_date->format('M d, Y') }}
                            </p>
                            <p class="text-xs text-green-600 font-semibold">
                                Collected: {{ $request->updated_at->format('M d, Y') }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                            {{ $request->requestStatus->name }}
                        </span>
                    </div>

                    <!-- Requester Info -->
                    <div class="mb-3 pb-3 border-b border-[#f8f0e2]">
                        <div class="flex items-start gap-2 mb-2">
                            <svg class="w-4 h-4 text-[#931335] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-[#622032]" lang="ar">{{ $request->publicRequest->requester_full_name }}</p>
                                <p class="text-xs text-[#622032]/60" lang="ar">{{ $request->publicRequest->city->name }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-xs text-[#622032]/60">
                            <span class="px-2 py-1 bg-[#fef9de] rounded">{{ $request->publicRequest->description }}</span>
                            <span>•</span>
                            <span class="font-semibold text-[#931335]">${{ number_format($request->publicRequest->amount, 2) }}</span>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div class="space-y-2 text-xs mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Submitted by:</span>
                            <span class="font-semibold text-[#622032]">{{ $request->sender->username }}</span>
                        </div>
                        @if($request->referenceMember)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Reference:</span>
                            <span class="font-semibold text-[#622032]" lang="ar">{{ trim($request->referenceMember->first_name . ' ' . $request->referenceMember->father_name . ' ' . $request->referenceMember->last_name) }}</span>
                        </div>
                        @endif
                        @if($request->publicRequest->budget)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Budget:</span>
                            <span class="font-semibold text-[#622032]">{{ $request->publicRequest->budget->description }}</span>
                        </div>
                        @endif
                        @if($request->ready_date)
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Ready date:</span>
                            <span class="font-semibold text-[#931335]">{{ \Carbon\Carbon::parse($request->ready_date)->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($request->publicRequest->notes)
                        <div class="flex items-start gap-2">
                            <span class="text-[#622032]/60">Notes:</span>
                            <span class="text-[#622032]">{{ Str::limit($request->publicRequest->notes, 100) }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Supporting Documents -->
                    @if($request->publicRequest->supporting_documents)
                    <div class="mb-3 pb-3 border-b border-[#f8f0e2]">
                        <x-supporting-documents
                            :documents="$request->publicRequest->supporting_documents"
                            :can-download="auth()->user()->hasRole('hor') || auth()->user()->hasRole('admin')" />
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="space-y-2 pt-3 border-t border-[#f8f0e2]">
                        <!-- Secondary actions - Grid on desktop, stacked on mobile -->
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Primary action - Full width on mobile -->
                            <a href="{{ route('public-requests.show', $request->id) }}"
                                class="block w-full bg-[#f8f0e2] hover:bg-[#dfd1ba] text-[#622032] font-semibold text-sm py-2 px-4 rounded-lg text-center transition-all active:scale-95">
                                View Details
                            </a>

                            <button @click="addToMonthlyList({{ $request->id }})"
                                class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="hidden sm:inline">Add to Monthly</span>
                                <span class="sm:hidden">Add Monthly</span>
                            </button>
                        </div>

                        @can('final_approve_public')
                        <a href="{{ route('public-requests.download', $request->id) }}"
                            class="bg-[#931335] hover:bg-[#622032] text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <span>Download</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Completed Requests</h3>
                    <p class="text-sm text-[#622032]/60">Completed requests will appear here</p>
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
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function completedRequests() {
        return {
            selectedMonthYear: '{{ $month && $year ? sprintf("%04d-%02d", $year, $month) : "" }}',

            getMonth() {
                if (!this.selectedMonthYear) return '';
                // Month input format is YYYY-MM
                return this.selectedMonthYear.split('-')[1];
            },

            getYear() {
                if (!this.selectedMonthYear) return '';
                // Month input format is YYYY-MM
                return this.selectedMonthYear.split('-')[0];
            },

            submitForm() {
                // Auto-submit when month changes
                document.querySelector('form').submit();
            },

            async addToMonthlyList(requestId) {
                try {
                    const response = await fetch('/monthly-list/add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            request_id: requestId,
                            month: new Date().getMonth() + 1,
                            year: new Date().getFullYear()
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        alert('Request added to monthly list');
                    } else {
                        alert(data.message || 'Failed to add request');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                }
            }
        }
    }
</script>
@endpush