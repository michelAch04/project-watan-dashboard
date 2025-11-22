@extends('layouts.app')

@section('title', 'Create Diaper Budget')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="{
        submitting: false,
        async submitForm(event) {
            event.preventDefault();
            if (this.submitting) return;

            this.submitting = true;
            const formData = new FormData(event.target);

            // Build monthly_restock object from individual size inputs
            const monthly_restock = {
                xl: parseInt(formData.get('stock_xl')) || 0,
                l: parseInt(formData.get('stock_l')) || 0,
                m: parseInt(formData.get('stock_m')) || 0,
                s: parseInt(formData.get('stock_s')) || 0
            };

            const data = {
                description: formData.get('description'),
                monthly_restock: monthly_restock,
                auto_refill_day: formData.get('auto_refill_day'),
                zone_id: formData.get('zone_id')
            };

            try {
                const response = await fetch('{{ route('diaper-budgets.store') }}', {
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
                alert('An error occurred while creating the diaper budget');
                this.submitting = false;
            }
        }
    }">

    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('budgets.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032] flex items-center gap-2">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Diaper Budget
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
                    Diaper Budget Description <span class="text-red-500">*</span>
                </label>
                <input type="text" id="description" name="description" required maxlength="255"
                       class="input-field text-sm sm:text-base"
                       placeholder="e.g., Monthly Diaper Stock Budget">
                <p class="mt-1 text-xs text-gray-500">A brief description of this diaper budget's purpose</p>
            </div>

            <!-- Monthly Restock Quantities -->
            <div>
                <label class="block text-sm font-semibold text-[#622032] mb-3">
                    Monthly Restock Quantities <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- XL Size -->
                    <div>
                        <label for="stock_xl" class="block text-sm font-medium text-gray-700 mb-2">
                            Size XL (Extra Large)
                        </label>
                        <input type="number" id="stock_xl" name="stock_xl" required min="0" step="1"
                               class="input-field text-sm sm:text-base"
                               placeholder="60">
                    </div>

                    <!-- L Size -->
                    <div>
                        <label for="stock_l" class="block text-sm font-medium text-gray-700 mb-2">
                            Size L (Large)
                        </label>
                        <input type="number" id="stock_l" name="stock_l" required min="0" step="1"
                               class="input-field text-sm sm:text-base"
                               placeholder="40">
                    </div>

                    <!-- M Size -->
                    <div>
                        <label for="stock_m" class="block text-sm font-medium text-gray-700 mb-2">
                            Size M (Medium)
                        </label>
                        <input type="number" id="stock_m" name="stock_m" required min="0" step="1"
                               class="input-field text-sm sm:text-base"
                               placeholder="100">
                    </div>

                    <!-- S Size -->
                    <div>
                        <label for="stock_s" class="block text-sm font-medium text-gray-700 mb-2">
                            Size S (Small)
                        </label>
                        <input type="number" id="stock_s" name="stock_s" required min="0" step="1"
                               class="input-field text-sm sm:text-base"
                               placeholder="30">
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">The quantity of each size that will be restocked each month</p>
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
                <p class="mt-1 text-xs text-gray-500">The stock will automatically refill on this day each month (max: 28 to ensure it works for all months)</p>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-semibold mb-1">Diaper Budget Information</p>
                        <ul class="list-disc list-inside space-y-1 text-xs sm:text-sm">
                            <li>The budget will be created with initial stock equal to the monthly restock quantities</li>
                            <li>Each month on the refill day, the stock will be reset to the monthly restock quantities</li>
                            <li>All stock operations will be tracked in the transaction history</li>
                            <li>You can edit the description and monthly restock quantities later if needed</li>
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
                    <span x-show="!submitting">Create Diaper Budget</span>
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
