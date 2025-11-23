@extends('layouts.app')

@section('title', 'Diapers Requests')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('requests.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032] flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                        </svg>
                        Diapers Requests
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-6">
        <div class="page-container space-y-4">

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-2">
                @can('create_diapers')
                <a href="{{ route('diapers-requests.create') }}" class="btn-primary flex items-center justify-center w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden sm:inline">Create New Request</span>
                    <span class="sm:hidden">New Request</span>
                </a>
                @endcan
            </div>

            <!-- Budget Cards (HOR only) -->
            @if($budgets)
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2] mb-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                    <h2 class="text-base sm:text-lg font-bold text-[#622032]">Diaper Budgets ({{ now()->format('F Y') }})</h2>
                    <a href="{{ route('budgets.index') }}" class="text-sm text-[#931335] hover:underline">Manage Diaper Budgets</a>
                </div>

                @if($budgets->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($budgets as $budget)
                    <div class="bg-[#fcf7f8] rounded-lg p-4 border border-[#f8f0e2]">
                        <h3 class="font-semibold text-[#622032] mb-1">{{ $budget['description'] }}</h3>
                        <p class="text-xs text-[#622032]/60 mb-3">{{ $budget['zone'] }}</p>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#622032]/70">Current Stock:</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1 text-xs">
                                @foreach($budget['current_stock'] as $size => $count)
                                <div class="flex justify-between bg-white px-2 py-1 rounded">
                                    <span class="text-[#622032]/70 uppercase">{{ $size }}:</span>
                                    <span class="font-semibold text-[#622032]">{{ $count }}</span>
                                </div>
                                @endforeach
                            </div>

                            <div class="border-t border-[#622032]/10 pt-2 mt-2">
                                <span class="text-[#622032]/70 text-xs">Monthly Stock:</span>
                                <div class="grid grid-cols-2 gap-1 text-xs mt-1">
                                    @foreach($budget['monthly_restock'] as $size => $count)
                                    <div class="flex justify-between bg-white px-2 py-1 rounded">
                                        <span class="text-[#622032]/70 uppercase">{{ $size }}:</span>
                                        <span class="font-semibold text-green-600">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-[#622032]/60 text-center py-4">No diaper budgets configured for your zones yet.</p>
                @endif
            </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Active Requests -->
                <a href="{{ route('diapers-requests.active') }}"
                   class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2] hover:shadow-md transition-all active:scale-[0.98]">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-[#622032]/70">Active Requests</h3>
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-[#622032]">{{ $activeCount }}</p>
                    <p class="text-xs text-[#622032]/60 mt-1">In progress</p>
                </a>

                <!-- Drafts & Rejected -->
                <a href="{{ route('diapers-requests.drafts') }}"
                   class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2] hover:shadow-md transition-all active:scale-[0.98]">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-[#622032]/70">Drafts & Rejected</h3>
                        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-[#622032]">{{ $draftCount }}</p>
                    <p class="text-xs text-[#622032]/60 mt-1">Need attention</p>
                </a>

                <!-- Completed -->
                <a href="{{ route('diapers-requests.completed') }}"
                   class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2] hover:shadow-md transition-all active:scale-[0.98]">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-[#622032]/70">Completed</h3>
                        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-[#622032]">{{ $completedCount }}</p>
                    <p class="text-xs text-[#622032]/60 mt-1">Collected</p>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
