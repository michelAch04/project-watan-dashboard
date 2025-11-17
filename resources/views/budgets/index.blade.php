@extends('layouts.app')

@section('title', 'Budget Management')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="budgetIndex()">

    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" @click.prevent="window.history.length > 1 ? window.history.back() : window.location.href = '{{ route('dashboard') }}'" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
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
            <!-- Create Button -->
            @if(auth()->user()->hasRole('hor'))
            <div class="flex justify-end">
                <a href="{{ route('budgets.create') }}" class="block btn-primary text-center flex align-center">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New Budget
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

            <!-- Budgets List -->
            @forelse($budgets as $budget)
            <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden">
                <!-- Budget Header -->
                <div class="bg-gradient-to-r from-[#622032] to-[#8b2f45] p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div class="flex-1">
                            <h2 class="text-lg sm:text-xl font-bold text-white mb-1">{{ $budget->description }}</h2>
                            <!-- <p class="text-xs text-white/70 mt-1">Refills on day {{ $budget->auto_refill_day }} of each month</p> -->
                        </div>
                        <div class="flex flex-col sm:items-end gap-1">
                            <div class="flex flex-row sm:flex-col items-end gap-1 sm:gap-0 sm:items-normal">
                                <div class="text-2xl sm:text-3xl font-bold text-white">
                                    ${{ number_format($budget->current_balance) }}
                                </div>
                                <div class="text-xs sm:text-sm text-white/80 pb-1">
                                    of ${{ number_format($budget->monthly_amount_in_usd) }} monthly
                                </div>
                            </div>
                            @if($budget->last_refill_date)
                            <div class="text-xs text-white/70 mt-1">
                                Last refill: {{ $budget->last_refill_date->format('M d, Y') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        @php
                        $percentage = $budget->monthly_amount_in_usd > 0
                        ? ($budget->current_balance / $budget->monthly_amount_in_usd) * 100
                        : 0;
                        $percentage = max(0, min(100, $percentage));
                        @endphp
                        <div class="w-full bg-white/20 rounded-full h-2 sm:h-3 overflow-hidden">
                            <div class="h-full bg-white rounded-full transition-all duration-300"
                                style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs text-white/70 mt-1">
                            <span>{{ number_format($percentage, 1) }}% remaining</span>
                            <span>${{ number_format($budget->monthly_amount_in_usd - $budget->current_balance) }} used</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    @can('edit_budget')
                    @if(auth()->user()->hasRole('hor') && $budget->zone->user_id === auth()->id())
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('budgets.edit', $budget->id) }}"
                            class="flex-1 sm:flex-initial bg-white/20 hover:bg-white/30 text-white font-semibold text-sm py-2 px-4 rounded-lg transition-colors">
                            Edit Budget
                        </a>
                    </div>
                    @endif
                    @endcan
                </div>

                <!-- Transactions List -->
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-bold text-[#622032] mb-3 sm:mb-4">
                        Transactions
                        @if($month && $year)
                        <span class="text-sm font-normal text-gray-600">
                            ({{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }})
                        </span>
                        @endif
                    </h3>

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
                                   ($transaction->type === 'deduction' ? 'border-red-500' : 'border-blue-500') }}">
                            <div class="flex items-center gap-2 mb-1">
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
                            <div class="flex flex-row sm:items-center justify-between p-2">
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
                                <div class="flex items-center gap-3 sm:gap-4">
                                    <div class="text-right">
                                        <div class="text-base sm:text-lg font-bold {{ $transaction->amount >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->amount >= 0 ? '+' : '-' }}${{ number_format(abs($transaction->amount)) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Balance: ${{ number_format($transaction->balance_after) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl p-8 sm:p-12 text-center shadow-sm border border-[#f8f0e2]">
                <svg class="w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="text-lg sm:text-xl font-bold text-gray-700 mb-2">No Budgets Found</h3>
                <p class="text-sm sm:text-base text-gray-500 mb-4">
                    @if(auth()->user()->hasRole('hor'))
                    Get started by creating your first budget
                    @else
                    No budgets have been created yet
                    @endif
                </p>
                @if(auth()->user()->hasRole('hor'))
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
    </div>
</div>

<script>
    function budgetIndex() {
        return {
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