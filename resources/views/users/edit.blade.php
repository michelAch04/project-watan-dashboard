@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="editUserForm()" x-init="init()">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('users.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Edit User</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4">
        <div class="page-container">
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <div class="mb-6 p-4 bg-[#fcf7f8] rounded-lg">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="avatar w-12 h-12 text-base">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-[#622032]">{{ $user->name }}</h3>
                            <p class="text-sm text-[#622032]/60">{{ $user->email }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-[#622032]/60">
                        <span class="font-semibold">Mobile:</span> {{ $user->mobile }}
                    </p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label for="role" class="block text-sm font-semibold text-[#622032] mb-2">
                            Role *
                        </label>
                        <select
                            id="role"
                            x-model="form.role"
                            class="input-field"
                            required
                            :disabled="loading">
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    @if($locationType === 'zone')
                    <div>
                        <label class="block text-sm font-semibold text-[#622032] mb-2">
                            Current Location (Zone - Not Editable)
                        </label>
                        <div class="input-field bg-gray-50 text-gray-600 cursor-not-allowed">
                            Zone: {{ $currentLocation->name }}
                        </div>
                        <p class="text-xs text-[#622032]/60 mt-1">Zone managers cannot change their assigned zone</p>
                    </div>
                    @else
                    <div>
                        <label for="location_type" class="block text-sm font-semibold text-[#622032] mb-2">
                            Assign Location
                        </label>
                        <select
                            id="location_type"
                            x-model="form.location_type"
                            @change="loadLocations"
                            class="input-field mb-2"
                            :disabled="loading">
                            <option value="none">No Location</option>
                            <option value="city" {{ $locationType === 'city' ? 'selected' : '' }}>City</option>
                            <option value="village" {{ $locationType === 'village' ? 'selected' : '' }}>Village</option>
                        </select>

                        <!-- Current Location Info -->
                        <div x-show="form.location_type !== 'none'" class="mb-2 text-sm text-[#622032]/70" x-cloak>
                            @if($currentLocation)
                            <div>Current: <span class="font-semibold">{{ $currentLocation->name }}</span></div>
                            @endif
                        </div>

                        <div x-show="form.location_type !== 'none'" x-cloak>
                            <div class="relative" @click.away="searchDropdownOpen = false">
                                <input
                                    type="text"
                                    id="location_search"
                                    x-model="locationSearch"
                                    @focus="searchDropdownOpen = true"
                                    @input="searchDropdownOpen = true"
                                    :placeholder="loadingLocations ? 'Loading...' : 'Search or select a location'"
                                    class="input-field"
                                    :disabled="loading || loadingLocations"
                                    autocomplete="off"
                                />
                                
                                <select id="location_id" x-model="form.location_id" class="hidden">
                                    <option value=""></option>
                                    <template x-for="location in availableLocations" :key="location.id">
                                        <option :value="location.id" x-text="`${location.name} (${location.location})`"></option>
                                    </template>
                                </select>

                                <div x-show="searchDropdownOpen"
                                     x-transition
                                     class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                    
                                    <ul class="py-1">
                                        <template x-for="location in filteredLocations" :key="location.id">
                                            <li @click="selectLocation(location)"
                                                class="px-4 py-2 hover:bg-[#f8f0e2] cursor-pointer"
                                                x-text="`${location.name} (${location.location})`">
                                            </li>
                                        </template>
                                        <template x-if="filteredLocations.length === 0 && !loadingLocations">
                                            <li class="px-4 py-2 text-gray-500 italic">
                                                <span x-show="availableLocations.length > 0">No matching locations.</span>
                                                <span x-show="availableLocations.length === 0">No locations available.</span>
                                            </li>
                                        </template>
                                        <template x-if="loadingLocations">
                                             <li class="px-4 py-2 text-gray-500 italic">Loading...</li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        @if($managerZone)
                        <p class="text-xs text-[#622032]/60 mt-2">
                            Only locations in <strong>{{ $managerZone->name }}</strong> zone can be assigned
                        </p>
                        @endif
                    </div>
                    @endif

                    <div x-show="errorMessage"
                        x-cloak
                        x-transition
                        class="error-message">
                        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p x-text="errorMessage"></p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="w-50 btn-primary"
                            :disabled="loading"
                            :class="{ 'opacity-50 cursor-not-allowed': loading }">
                            <span x-show="!loading">Update User</span>
                            <span x-show="loading" class="flex items-center justify-center" style="display: none;">
                                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 
                                            5.291A7.962 7.962 0 014 12H0c0 
                                            3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Updating...
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

    function editUserForm() {
        return {
            form: {
                role: '{{ $user->roles->first()?->name ?? "" }}',
                location_type: '{{ $locationType === "zone" ? "none" : ($locationType ?? "none") }}',
                location_id: '{{ $currentLocation?->id ?? "" }}'
            },
            loading: false,
            loadingLocations: false,
            errorMessage: '',
            availableLocations: [],
            locationSearch: '',
            searchDropdownOpen: false,

            // Computed property to filter locations based on search
            get filteredLocations() {
                if (this.locationSearch === '') {
                    return this.availableLocations;
                }
                return this.availableLocations.filter(loc => {
                    const searchTerm = this.locationSearch.toLowerCase();
                    const locName = `${loc.name} (${loc.location})`.toLowerCase();
                    return locName.includes(searchTerm);
                });
            },

            // **MODIFIED**: Removed the 'selectedLocationName' computed property

            init() {
                if (this.form.location_type !== 'none' && this.form.location_type !== '') {
                    this.loadLocations();
                }

                // Close dropdown on escape key
                window.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.searchDropdownOpen = false;
                    }
                });
            },

            // **MODIFIED**: Updated loadLocations to set the input value
            async loadLocations() {
                if (this.form.location_type === 'none') {
                    this.availableLocations = [];
                    this.form.location_id = '';
                    this.locationSearch = ''; // Reset here
                    return;
                }

                this.loadingLocations = true;
                this.searchDropdownOpen = false;
                // Reset search text when type changes
                this.locationSearch = ''; 

                try {
                    const response = await fetch(`/users/{{ $user->id }}/locations?type=${this.form.location_type}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        this.availableLocations = await response.json();
                        
                        // If current location_id is not in the new list, reset it
                        if (this.form.location_id && !this.availableLocations.some(loc => loc.id == this.form.location_id)) {
                            this.form.location_id = '';
                            this.locationSearch = '';
                        } else if (this.form.location_id) {
                            // **NEW**: If ID is valid, find name and set the input text
                            const selected = this.availableLocations.find(loc => loc.id == this.form.location_id);
                            if (selected) {
                                this.locationSearch = `${selected.name} (${selected.location})`;
                            }
                        }
                    }
                } catch (error) {
                    console.error('Failed to load locations:', error);
                } finally {
                    this.loadingLocations = false;
                }
            },
            
            // Method to select a location from the dropdown
            selectLocation(location) {
                this.form.location_id = location.id;
                this.locationSearch = `${location.name} (${location.location})`; // Set input text
                this.searchDropdownOpen = false; // Close dropdown
            },

            async submitForm() {
                this.loading = true;
                this.errorMessage = '';

                // Ensure the search text is cleared if the selection is empty
                if (!this.form.location_id) {
                    this.locationSearch = '';
                }
                
                // **NEW**: Check if locationSearch text matches a valid location
                // If text is present but ID is missing, try to find a match
                if (this.locationSearch && !this.form.location_id) {
                    const found = this.availableLocations.find(loc => `${loc.name} (${loc.location})` === this.locationSearch);
                    if (found) {
                        this.form.location_id = found.id;
                    }
                }
                
                // If text is manually cleared, clear the ID
                if (this.locationSearch === '' && this.form.location_type !== 'none') {
                    this.form.location_id = '';
                }


                try {
                    const response = await fetch('{{ route("users.update", $user->id) }}', {
                        method: 'PUT',
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
                        this.errorMessage = data.message || 'Failed to update user';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please try again.';
                    console.error('Update user error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush