@extends('layouts.app')

@section('title', 'Edit Budget')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="{
         submitting: false,
         async submitForm(event) {
             event.preventDefault();
             if (this.submitting) return;

             this.submitting = true;
             const formData = new FormData(event.target);
             const data = Object.fromEntries(formData.entries());

             try {
                 const response = await fetch('{{ route('budgets.update', $budget->id) }}', {
                     method: 'PUT',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify(data)
                 });

                 const result = await response.json();

                 if (result.success) {
                     window.location.href = result.redirect;
                 } else {
                     alert(result.message || 'An error occurred');
                     this.submitting = false;
                 }
             } catch (error) {
                 console.error('Error:', error);
                 alert('An error occurred while updating the budget');
                 this.submitting = false;
             }
         }
     }">

    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('budgets.index') }}" @click.prevent="window.history.length > 1 ? window.history.back() : window.location.href = '{{ route('budgets.index') }}'" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032] flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Budget
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Current Budget Info -->
            <div class="bg-gradient-to-r from-[#622032] to-[#8b2f45] rounded-xl p-4 sm:p-6">
                <div class="text-white">
                    <p class="text-sm opacity-80 mb-1">Zone</p>
                    <p class="font-bold text-lg">{{ $budget->zone->name }}</p>
                    <div class="mt-3 pt-3 border-t border-white/20">
                        <p class="text-sm opacity-80 mb-1">Current Balance</p>
                        <p class="font-bold text-2xl">${{ number_format($budget->current_balance) }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit="submitForm" class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
                @csrf
                @method('PUT')

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-[#622032] mb-2">
                        Budget Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="description" name="description" required maxlength="255"
                        value="{{ $budget->description }}"
                        class="input-field text-sm sm:text-base"
                        placeholder="e.g., Monthly Humanitarian Aid Budget">
                    <p class="mt-1 text-xs text-gray-500">A brief description of this budget's purpose</p>
                </div>

                <!-- Monthly Amount -->
                <div>
                    <label for="monthly_amount_in_usd" class="block text-sm font-semibold text-[#622032] mb-2">
                        Monthly Budget Amount (USD) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm sm:text-base">$</span>
                        <input type="number" id="monthly_amount_in_usd" name="monthly_amount_in_usd" required min="1" step="1"
                            value="{{ $budget->monthly_amount_in_usd }}"
                            class="input-field pl-8 text-sm sm:text-base"
                            placeholder="5000">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">The amount this budget will be refilled to each month</p>
                </div>

                <!-- Auto Refill Day -->
                <div>
                    <label for="auto_refill_day" class="block text-sm font-semibold text-[#622032] mb-2">
                        Auto Refill Day of Month <span class="text-red-500">*</span>
                    </label>
                    <select id="auto_refill_day" name="auto_refill_day" required
                        class="input-field text-sm sm:text-base">
                        @for($day = 1; $day <= 28; $day++)
                            <option value="{{ $day }}" {{ $budget->auto_refill_day == $day ? 'selected' : '' }}>
                            Day {{ $day }} of each month
                            </option>
                            @endfor
                    </select>
                    <p class="mt-1 text-xs text-gray-500">The budget will automatically refill on this day each month</p>
                </div>

                <!-- Info Box -->
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex gap-2">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="font-semibold mb-2 text-sm text-amber-800">Important Notes</p>
                    </div>
                    <div class="text-sm text-amber-800">
                        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm">
                            <li>Changing the monthly amount will not affect the current balance immediately</li>
                            <li>The new monthly amount will apply at the next auto-refill</li>
                            <li>A transaction record will be created documenting this change</li>
                        </ul>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="flex flex-row gap-3 pt-4 border-t">
                    <a href="{{ route('budgets.index') }}"
                        class="flex-1 btn-secondary text-center">
                        Cancel
                    </a>
                    <button type="submit"
                        :disabled="submitting"
                        class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!submitting">Update Budget</span>
                        <span x-show="submitting" class="flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Updating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection