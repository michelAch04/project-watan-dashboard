@extends('layouts.app')

@section('title', 'Completed Requests')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('humanitarian.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
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
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            
            <!-- Stats Summary -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-[#622032]/60">Total Collected</p>
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
                        <div class="flex items-center gap-2">
                            <span class="text-[#622032]/60">Submitted by:</span>
                            <span class="font-semibold text-[#622032]">{{ $request->sender->name }}</span>
                        </div>
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
                        <a href="{{ route('humanitarian.show', $request->id) }}" 
                           class="flex-1 bg-[#f8f0e2] hover:bg-[#dfd1ba] text-[#622032] font-semibold text-sm py-2 px-4 rounded-lg text-center transition-all active:scale-95">
                            View Details
                        </a>
                        
                        @can('final_approve_humanitarian')
                        <a href="{{ route('humanitarian.download', $request->id) }}" 
                           class="bg-[#931335] hover:bg-[#622032] text-white font-semibold text-sm py-2 px-4 rounded-lg transition-all active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download
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