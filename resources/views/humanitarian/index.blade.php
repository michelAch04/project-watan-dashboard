@extends('layouts.app')

@section('title', 'Humanitarian Requests')

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
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                        </svg>
                        Humanitarian Requests
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
                @can('create_humanitarian')
                <a href="{{ route('humanitarian.create') }}" class="btn-primary flex items-center justify-center w-full sm:w-auto">
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
                    <h2 class="text-base sm:text-lg font-bold text-[#622032]">Zone Budgets ({{ now()->format('F Y') }})</h2>
                    <a href="{{ route('budgets.index') }}" class="text-sm text-[#931335] hover:underline">Manage Budgets</a>
                </div>

                @if($budgets->count() > 0)
                <!-- Mobile Carousel (only on small screens) -->
                <div class="md:hidden relative" x-data="{ currentSlide: 0, totalSlides: {{ $budgets->count() }} }">
                    <div class="overflow-hidden">
                        <div class="flex transition-transform duration-300 ease-out" :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                            @foreach($budgets as $budget)
                            <div class="w-full flex-shrink-0 px-1">
                                <div class="bg-[#fcf7f8] rounded-lg p-4 border border-[#f8f0e2]">
                                    <h3 class="font-semibold text-[#622032] mb-1">{{ $budget['description'] }}</h3>
                                    <p class="text-xs text-[#622032]/60 mb-3">{{ $budget['zone'] }}</p>

                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-[#622032]/70">Monthly:</span>
                                            <span class="font-semibold text-[#622032]">${{ number_format($budget['monthly_amount']) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-[#622032]/70">Remaining:</span>
                                            <span class="font-semibold {{ $budget['current_remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ${{ number_format($budget['current_remaining']) }}
                                            </span>
                                        </div>
                                        <!-- <div class="flex justify-between border-t border-[#622032]/10 pt-2">
                                            <span class="text-[#622032]/70 text-xs">Predicted EOM:</span>
                                            <span class="font-bold text-xs {{ $budget['predicted_end_of_month'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                ${{ number_format($budget['predicted_end_of_month']) }}
                                            </span>
                                        </div> -->
                                    </div>

                                    <!-- Budget bar -->
                                    <div class="mt-3">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            @php
                                                $percentage = $budget['monthly_amount'] > 0
                                                    ? max(0, min(100, ($budget['current_remaining'] / $budget['monthly_amount']) * 100))
                                                    : 0;
                                            @endphp
                                            <div class="h-2 rounded-full transition-all {{ $percentage > 50 ? 'bg-green-500' : ($percentage > 25 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                                 style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Carousel Controls (only show if more than 1 budget) -->
                    @if($budgets->count() > 1)
                    <div class="flex items-center justify-center gap-2 mt-4">
                        <button @click="currentSlide = Math.max(0, currentSlide - 1)"
                                :disabled="currentSlide === 0"
                                class="p-2 rounded-lg bg-[#f8f0e2] text-[#622032] disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>

                        <!-- Dots Indicator -->
                        <div class="flex gap-1.5">
                            @foreach($budgets as $index => $budget)
                            <button @click="currentSlide = {{ $loop->index }}"
                                    class="w-2 h-2 rounded-full transition-all"
                                    :class="currentSlide === {{ $loop->index }} ? 'bg-[#931335] w-6' : 'bg-[#622032]/30'">
                            </button>
                            @endforeach
                        </div>

                        <button @click="currentSlide = Math.min(totalSlides - 1, currentSlide + 1)"
                                :disabled="currentSlide === totalSlides - 1"
                                class="p-2 rounded-lg bg-[#f8f0e2] text-[#622032] disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Desktop Grid (hidden on mobile) -->
                <div class="hidden md:grid md:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4">
                    @foreach($budgets as $budget)
                    <div class="bg-[#fcf7f8] rounded-lg p-4 border border-[#f8f0e2]">
                        <h3 class="font-semibold text-[#622032] mb-1">{{ $budget['description'] }}</h3>
                        <p class="text-xs text-[#622032]/60 mb-3">{{ $budget['zone'] }}</p>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#622032]/70">Monthly:</span>
                                <span class="font-semibold text-[#622032]">${{ number_format($budget['monthly_amount']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/70">Remaining:</span>
                                <span class="font-semibold {{ $budget['current_remaining'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($budget['current_remaining']) }}
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-[#622032]/10 pt-2">
                                <span class="text-[#622032]/70 text-xs">Predicted EOM:</span>
                                <span class="font-bold text-xs {{ $budget['predicted_end_of_month'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    ${{ number_format($budget['predicted_end_of_month']) }}
                                </span>
                            </div>
                        </div>

                        <!-- Budget bar -->
                        <div class="mt-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                @php
                                    $percentage = $budget['monthly_amount'] > 0
                                        ? max(0, min(100, ($budget['current_remaining'] / $budget['monthly_amount']) * 100))
                                        : 0;
                                @endphp
                                <div class="h-2 rounded-full transition-all {{ $percentage > 50 ? 'bg-green-500' : ($percentage > 25 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-[#622032]/60 text-center py-4">No budgets configured for your zones yet.</p>
                @endif
            </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Active Requests -->
                <a href="{{ route('humanitarian.active') }}"
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
                <a href="{{ route('humanitarian.drafts') }}"
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
                <a href="{{ route('humanitarian.completed') }}"
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