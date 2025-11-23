@extends('layouts.app')

@section('title', 'Budget Management')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="budgetIndex()">

    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032] flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"></path>
                        </svg>
                        Budgets
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Tabs Navigation -->
            <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden">
                <div class="flex border-b border-[#f8f0e2]">
                    <button @click="activeTab = 'regular'"
                            :class="activeTab === 'regular' ? 'bg-[#622032] text-white' : 'bg-white text-[#622032] hover:bg-[#f8f0e2]'"
                            class="flex-1 py-3 px-4 font-semibold text-sm sm:text-base transition-colors focus:outline-none">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Regular Budgets</span>
                        </div>
                    </button>
                    <button @click="activeTab = 'diaper'"
                            :class="activeTab === 'diaper' ? 'bg-[#622032] text-white' : 'bg-white text-[#622032] hover:bg-[#f8f0e2]'"
                            class="flex-1 py-3 px-4 font-semibold text-sm sm:text-base transition-colors focus:outline-none">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                            </svg>
                            <span>Diaper Budgets</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Button (changes based on active tab) -->
            @if(auth()->user()->can('create_budget'))
            <div class="flex justify-end">
                <a :href="activeTab === 'regular' ? '{{ route('budgets.create') }}' : '{{ route('diaper-budgets.create') }}'"
                   class="btn-primary text-center flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span x-text="activeTab === 'regular' ? 'Create Budget' : 'Create Diaper Budget'"></span>
                </a>
            </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <label class="block text-sm font-semibold text-[#622032] mb-2">Filter Transactions by Month</label>
                <form method="GET" action="{{ route('budgets.index') }}" class="flex flex-row gap-2">
                    <input type="month"
                        x-model="selectedMonthYear"
                        @change="submitForm()"
                        class="input-field flex-1 text-sm sm:text-base"
                        placeholder="Select month">
                    <input type="hidden" name="month" :value="getMonth()">
                    <input type="hidden" name="year" :value="getYear()">
                    <div class="flex gap-2 items-center">
                        @if($month && $year)
                        <a href="{{ route('budgets.index') }}" class="flex-1 sm:flex-initial btn-secondary whitespace-nowrap">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Regular Budgets Tab Content -->
            <div x-show="activeTab === 'regular'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @php
                $regularBudgets = $allBudgets->where('budget_type', 'regular');
            @endphp
            @forelse($regularBudgets as $budget)
            <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden" x-data="{ transactionsOpen: false }">
                <!-- Budget Header -->
                <div class="bg-gradient-to-r from-[#622032] to-[#8b2f45] p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div class="flex-1">
                            <h2 class="text-lg sm:text-xl font-bold text-white mb-1">
                                {{ $budget->description }}
                                @if($budget->budget_type === 'diaper')
                                <span class="inline-block ml-2 px-2 py-0.5 text-xs bg-white/20 rounded">Diaper Budget</span>
                                @endif
                            </h2>
                            <p class="text-sm text-white/80">{{ $budget->zone->name }}</p>
                        </div>
                        <div class="flex flex-col sm:items-end gap-1">
                            @if($budget->last_refill_date)
                            <div class="text-xs text-white/70 mt-1">
                                Last refill: {{ $budget->last_refill_date->format('M d, Y') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($budget->budget_type === 'regular')
                    <!-- Progress Bar for Regular Budget -->
                    <div class="mt-4">
                        @php
                        $percentage = $budget->monthly_amount_in_usd > 0
                        ? ($budget->current_balance / $budget->monthly_amount_in_usd) * 100
                        : 0;
                        $percentage = max(0, min(100, $percentage));
                        @endphp
                        <div class="flex flex-row sm:flex-col items-end gap-1 sm:gap-0 mb-3">
                            <div class="text-2xl sm:text-3xl font-bold text-white">
                                ${{ number_format($budget->current_balance) }}
                            </div>
                            <div class="text-xs sm:text-sm text-white/80 pb-1">
                                of ${{ number_format($budget->monthly_amount_in_usd) }} monthly
                            </div>
                        </div>
                        <div class="w-full bg-white/20 rounded-full h-2 sm:h-3 overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-300"
                                style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-white/70 mt-1">
                            <span>{{ number_format($percentage, 1) }}% remaining</span>
                            <span>${{ number_format($budget->monthly_amount_in_usd - $budget->current_balance) }} used</span>
                        </div>
                    </div>
                    @else
                    <!-- Stock Display for Diaper Budget -->
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach(['xl' => 'XL', 'l' => 'L', 'm' => 'M', 's' => 'S'] as $size => $label)
                        @php
                        $current = $budget->current_stock[$size] ?? 0;
                        $monthly = $budget->monthly_restock[$size] ?? 0;
                        $percentage = $monthly > 0 ? ($current / $monthly) * 100 : 0;
                        $percentage = max(0, min(100, $percentage));
                        @endphp
                        <div class="bg-white/10 rounded-lg p-3">
                            <div class="text-xs text-white/70 mb-1">Size {{ $label }}</div>
                            <div class="text-2xl font-bold text-white">{{ number_format($current) }}</div>
                            <div class="text-xs text-white/80 mb-2">of {{ number_format($monthly) }}</div>
                            <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-white rounded-full transition-all duration-300"
                                    style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    @can('edit_budget')
                    @if(auth()->user()->can('edit_budget') && $budget->zone->user_id === auth()->id())
                    <div class="mt-4 flex gap-2">
                        <a href="{{ $budget->budget_type === 'diaper' ? route('diaper-budgets.edit', $budget->id) : route('budgets.edit', $budget->id) }}"
                            class="flex-1 sm:flex-initial bg-white/20 hover:bg-white/30 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-colors">
                            Edit Budget
                        </a>
                    </div>
                    @endif
                    @endcan
                </div>

                <!-- Transactions List -->
                <div class="p-4 sm:p-6">
                    <button @click="transactionsOpen = !transactionsOpen"
                            class="w-full flex items-center justify-between text-base sm:text-lg font-bold text-[#622032] mb-3 sm:mb-4 hover:text-[#8b2f45] transition-colors">
                        <span>
                            Transactions
                            @if($month && $year)
                            <span class="text-sm font-normal text-gray-600">
                                ({{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }})
                            </span>
                            @endif
                        </span>
                        <svg class="w-5 h-5 transition-transform duration-300"
                             :class="{ 'rotate-180': transactionsOpen }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="transactionsOpen"
                         x-collapse>
                        @if($budget->filtered_transactions->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm sm:text-base">No transactions found</p>
                        </div>
                        @else
                        <div class="space-y-2 sm:space-y-3">
                        @foreach($budget->filtered_transactions as $transaction)
                        <div class="bg-gray-50 rounded-lg gap-2 sm:gap-4 border-l-4 p-2
                                {{ $transaction->type === 'refill' ? 'border-green-500' :
                                   ($transaction->type === 'deduction' ? 'border-red-500' :
                                   ($transaction->type === 'allocation' ? 'border-yellow-500' : 'border-blue-500')) }}">
                            <div class="flex items-center gap-2">
                                @if($transaction->type === 'refill')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                    Refill
                                </span>
                                @elseif($transaction->type === 'deduction')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                    Deduction
                                </span>
                                @elseif($transaction->type === 'allocation')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Allocation
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Adjustment
                                </span>
                                @endif
                                <span class="text-xs text-gray-500">
                                    {{ $transaction->created_at->format('M d, Y h:i A') }}
                                </span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 break-words">
                                        {{ $transaction->description }}
                                    </p>
                                    @if($transaction->request_id)
                                    <p class="text-xs text-gray-500 mt-1">
                                        Request ID: #{{ $transaction->request_id }}
                                    </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    @if($budget->budget_type === 'diaper')
                                        <!-- Diaper budget transaction quantities -->
                                        <div class="text-sm font-semibold text-gray-700 mb-1">Quantity Change:</div>
                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                            @foreach(['xl' => 'XL', 'l' => 'L', 'm' => 'M', 's' => 'S'] as $size => $label)
                                            @php $qty = $transaction->quantity_change[$size] ?? 0; @endphp
                                            @if($qty != 0)
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="text-gray-600">{{ $label }}:</span>
                                                <span class="font-semibold {{ $qty > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $qty > 0 ? '+' : '' }}{{ $qty }}
                                                </span>
                                            </div>
                                            @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <!-- Regular budget transaction amounts -->
                                        <div class="text-base sm:text-lg font-bold {{ $transaction->type === 'allocation' ? 'text-gray-600' : ($transaction->amount >= 0 ? 'text-green-600' : 'text-red-600') }}">
                                            {{ $transaction->type === 'allocation' ? ' ' : ($transaction->amount >= 0 ? '+' : '-') }}${{ number_format(abs($transaction->amount)) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Balance: ${{ number_format($transaction->balance_after) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl p-8 sm:p-12 text-center shadow-sm border border-[#f8f0e2]">
                <svg class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-lg sm:text-xl font-bold text-gray-700 mb-2">No Regular Budgets Found</h3>
                <p class="text-sm sm:text-base text-gray-500 mb-4">
                    @if(auth()->user()->can('create_budget'))
                    Get started by creating your first regular budget
                    @else
                    No regular budgets have been created yet
                    @endif
                </p>
                @if(auth()->user()->can('create_budget'))
                <a href="{{ route('budgets.create') }}" class="btn-primary inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Budget
                </a>
                @endif
            </div>
            @endforelse
            </div>

            <!-- Diaper Budgets Tab Content -->
            <div x-show="activeTab === 'diaper'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @php
                $diaperBudgets = $allBudgets->where('budget_type', 'diaper');
            @endphp
            @forelse($diaperBudgets as $budget)
            <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden" x-data="{ transactionsOpen: false }">
                <!-- Budget Header -->
                <div class="bg-gradient-to-r from-[#622032] to-[#8b2f45] p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div class="flex-1">
                            <h2 class="text-lg sm:text-xl font-bold text-white mb-1">
                                {{ $budget->description }}
                                <span class="inline-block ml-2 px-2 py-0.5 text-xs bg-white/20 rounded">Diaper Budget</span>
                            </h2>
                            <p class="text-sm text-white/80">{{ $budget->zone->name }}</p>
                        </div>
                        <div class="flex flex-col sm:items-end gap-1">
                            @if($budget->last_refill_date)
                            <div class="text-xs text-white/70 mt-1">
                                Last refill: {{ $budget->last_refill_date->format('M d, Y') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Stock Display for Diaper Budget -->
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach(['xl' => 'XL', 'l' => 'L', 'm' => 'M', 's' => 'S'] as $size => $label)
                        @php
                        $current = $budget->current_stock[$size] ?? 0;
                        $monthly = $budget->monthly_restock[$size] ?? 0;
                        $percentage = $monthly > 0 ? ($current / $monthly) * 100 : 0;
                        $percentage = max(0, min(100, $percentage));
                        @endphp
                        <div class="bg-white/10 rounded-lg p-3">
                            <div class="text-xs text-white/70 mb-1">Size {{ $label }}</div>
                            <div class="text-2xl font-bold text-white">{{ number_format($current) }}</div>
                            <div class="text-xs text-white/80 mb-2">of {{ number_format($monthly) }}</div>
                            <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-white rounded-full transition-all duration-300"
                                    style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Action Buttons -->
                    @can('edit_budget')
                    @if(auth()->user()->can('edit_budget') && $budget->zone->user_id === auth()->id())
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('diaper-budgets.edit', $budget->id) }}"
                            class="flex-1 sm:flex-initial bg-white/20 hover:bg-white/30 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-colors">
                            Edit Budget
                        </a>
                    </div>
                    @endif
                    @endcan
                </div>

                <!-- Transactions List -->
                <div class="p-4 sm:p-6">
                    <button @click="transactionsOpen = !transactionsOpen"
                            class="w-full flex items-center justify-between text-base sm:text-lg font-bold text-[#622032] mb-3 sm:mb-4 hover:text-[#8b2f45] transition-colors">
                        <span>
                            Transactions
                            @if($month && $year)
                            <span class="text-sm font-normal text-gray-600">
                                ({{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }})
                            </span>
                            @endif
                        </span>
                        <svg class="w-5 h-5 transition-transform duration-300"
                             :class="{ 'rotate-180': transactionsOpen }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="transactionsOpen"
                         x-collapse>
                        @if($budget->filtered_transactions->isEmpty())
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm sm:text-base">No transactions found</p>
                        </div>
                        @else
                        <div class="space-y-2 sm:space-y-3">
                        @foreach($budget->filtered_transactions as $transaction)
                        <div class="bg-gray-50 rounded-lg gap-2 sm:gap-4 border-l-4 p-2
                                {{ $transaction->type === 'refill' ? 'border-green-500' :
                                   ($transaction->type === 'deduction' ? 'border-red-500' :
                                   ($transaction->type === 'allocation' ? 'border-yellow-500' : 'border-blue-500')) }}">
                            <div class="flex items-center gap-2">
                                @if($transaction->type === 'refill')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    </svg>
                                    Refill
                                </span>
                                @elseif($transaction->type === 'deduction')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    </svg>
                                    Deduction
                                </span>
                                @elseif($transaction->type === 'allocation')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Allocation
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Adjustment
                                </span>
                                @endif
                                <span class="text-xs text-gray-500">
                                    {{ $transaction->created_at->format('M d, Y h:i A') }}
                                </span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-700 break-words">
                                        {{ $transaction->description }}
                                    </p>
                                    @if($transaction->request_id)
                                    <p class="text-xs text-gray-500 mt-1">
                                        Request ID: #{{ $transaction->request_id }}
                                    </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <!-- Diaper budget transaction quantities -->
                                    <div class="text-sm font-semibold text-gray-700 mb-1">Quantity Change:</div>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        @foreach(['xl' => 'XL', 'l' => 'L', 'm' => 'M', 's' => 'S'] as $size => $label)
                                        @php $qty = $transaction->quantity_change[$size] ?? 0; @endphp
                                        @if($qty != 0)
                                        <div class="flex items-center justify-end gap-1">
                                            <span class="text-gray-600">{{ $label }}:</span>
                                            <span class="font-semibold {{ $qty > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $qty > 0 ? '+' : '' }}{{ $qty }}
                                            </span>
                                        </div>
                                        @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl p-8 sm:p-12 text-center shadow-sm border border-[#f8f0e2]">
                <svg class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-lg sm:text-xl font-bold text-gray-700 mb-2">No Diaper Budgets Found</h3>
                <p class="text-sm sm:text-base text-gray-500 mb-4">
                    @if(auth()->user()->can('create_budget'))
                    Get started by creating your first diaper budget
                    @else
                    No diaper budgets have been created yet
                    @endif
                </p>
                @if(auth()->user()->can('create_budget'))
                <a href="{{ route('diaper-budgets.create') }}" class="btn-primary inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Diaper Budget
                </a>
                @endif
            </div>
            @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function budgetIndex() {
        return {
            activeTab: 'regular', // Default to regular budgets tab
            selectedMonthYear: '{{ $month && $year ? sprintf("%04d-%02d", $year, $month) : now()->format("Y-m") }}',

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
            }
        }
    }
</script>
@endsection