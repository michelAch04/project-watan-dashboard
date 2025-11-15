@extends('layouts.app')

@section('title', 'Create Budget')

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
                 const response = await fetch('{{ route('budgets.store') }}', {
                     method: 'POST',
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
                 alert('An error occurred while creating the budget');
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Budget
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container">
        <form @submit="submitForm" class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] p-4 sm:p-6 lg:p-8 space-y-4 sm:space-y-6">
            @csrf

            <!-- Zone Selection -->
            <div>
                <label for="zone_id" class="block text-sm font-semibold text-[#622032] mb-2">
                    Zone <span class="text-red-500">*</span>
                </label>
                <div class="input-field text-sm sm:text-base flex items-center">
                    <span class="text-gray-700">{{ $zone->name }}</span>
                </div>
                <!-- Hidden input to send zone_id in form -->
                <input type="hidden" name="zone_id" value="{{ $zone->id }}">
                <p class="mt-1 text-xs text-gray-500">Your assigned zone</p>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-semibold text-[#622032] mb-2">
                    Budget Description <span class="text-red-500">*</span>
                </label>
                <input type="text" id="description" name="description" required maxlength="255"
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
                        <option value="{{ $day }}" {{ $day == 1 ? 'selected' : '' }}>
                            Day {{ $day }} of each month
                        </option>
                    @endfor
                </select>
                <p class="mt-1 text-xs text-gray-500">The budget will automatically refill on this day each month (max: 28 to ensure it works for all months)</p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Budget Information</p>
                        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm">
                            <li>The budget will be created with an initial balance equal to the monthly amount</li>
                            <li>Each month on the refill day, the budget will be reset to the monthly amount</li>
                            <li>All budget operations will be tracked in the transaction history</li>
                            <li>You can edit the description and monthly amount later if needed</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t">
                <a href="{{ route('budgets.index') }}"
                   class="flex-1 btn-secondary text-center">
                    Cancel
                </a>
                <button type="submit"
                        :disabled="submitting"
                        class="flex-1 btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!submitting">Create Budget</span>
                    <span x-show="submitting" class="flex items-center justify-center">
                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating...
                    </span>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>
@endsection
