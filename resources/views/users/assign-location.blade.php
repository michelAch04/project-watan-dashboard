@extends('layouts.app')

@section('title', 'Assign Location')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="assignLocationForm()">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Assign Location</h1>
                    <a href="{{ route('users.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container">
            <!-- User Info -->
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2] mb-4">
                <div class="flex items-center gap-3">
                    <div class="avatar w-12 h-12 text-base">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-[#622032]">{{ $user->name }}</h3>
                        <p class="text-sm text-[#622032]/60 capitalize">{{ $user->roles->first()?->name ?? 'No Role' }}</p>
                    </div>
                </div>
            </div>

            <!-- Assignment Form -->
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Location Type Selector -->
                    <div>
                        <label for="location_type" class="block text-sm font-semibold text-[#622032] mb-2">
                            Location Type *
                        </label>
                        <select 
                            id="location_type" 
                            x-model="form.location_type"
                            @change="loadLocations"
                            class="input-field"
                            required
                            :disabled="loading"
                        >
                            <option value="">Select type</option>
                            @if($canAssignZone)
                            <option value="zone">Zone</option>
                            @endif
                            <option value="city">City</option>
                            <option value="village">Village</option>
                        </select>
                        
                        @if(!$canAssignZone)
                        <p class="text-xs text-[#622032]/60 mt-2">
                            Only users who report to themselves can be assigned a zone
                        </p>
                        @endif

                        @if($managerZone)
                        <p class="text-xs text-[#622032]/60 mt-2">
                            Only locations in <strong>{{ $managerZone->name }}</strong> zone can be assigned
                        </p>
                        @endif
                    </div>

                    <!-- Location Selector -->
                    <div x-show="form.location_type" x-cloak>
                        <label for="location_id" class="block text-sm font-semibold text-[#622032] mb-2">
                            Select Location *
                        </label>
                        <select 
                            id="location_id" 
                            x-model="form.location_id"
                            class="input-field"
                            required
                            :disabled="loading || loadingLocations"
                        >
                            <option value="">
                                <span x-show="loadingLocations">Loading locations...</span>
                                <span x-show="!loadingLocations">Select a location</span>
                            </option>
                            <template x-for="location in availableLocations" :key="location.id">
                                <option :value="location.id" x-text="`${location.name} (${location.location})`"></option>
                            </template>
                        </select>
                        
                        <div x-show="availableLocations.length === 0 && !loadingLocations && form.location_type" class="mt-2 text-sm text-amber-600">
                            No available locations found. All locations may already be assigned.
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div x-show="errorMessage" 
                         x-cloak
                         x-transition
                         class="error-message">
                        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p x-text="errorMessage"></p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('users.index') }}" class="btn-secondary text-center py-2 px-6">
                            Skip
                        </a>
                        <button 
                            type="submit" 
                            class="btn-primary py-2 px-6"
                            :disabled="loading || !form.location_type || !form.location_id"
                            :class="{ 'opacity-50 cursor-not-allowed': loading || !form.location_type || !form.location_id }">
                            <span x-show="!loading">Assign</span>
                            <span x-show="loading" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Assigning...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function assignLocationForm() {
    return {
        form: {
            location_type: '',
            location_id: ''
        },
        loading: false,
        loadingLocations: false,
        errorMessage: '',
        availableLocations: [],

        async loadLocations() {
            if (!this.form.location_type) {
                this.availableLocations = [];
                return;
            }

            this.loadingLocations = true;
            this.form.location_id = '';

            try {
                const response = await fetch(`/users/{{ $user->id }}/locations?type=${this.form.location_type}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.availableLocations = await response.json();
                }
            } catch (error) {
                console.error('Failed to load locations:', error);
                this.errorMessage = 'Failed to load locations';
            } finally {
                this.loadingLocations = false;
            }
        },

        async submitForm() {
            this.loading = true;
            this.errorMessage = '';

            try {
                const response = await fetch('{{ route("users.assign-location", $user->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    const overlay = document.createElement('div');
                    overlay.className = 'fixed inset-0 bg-white z-[60] transition-opacity duration-500';
                    overlay.style.opacity = '0';
                    document.body.appendChild(overlay);
                    
                    setTimeout(() => {
                        overlay.style.opacity = '1';
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    }, 50);
                } else {
                    this.errorMessage = data.message || 'Failed to assign location';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Please try again.';
                console.error('Assign location error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush