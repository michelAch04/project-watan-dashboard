@extends('layouts.app')

@section('title', 'Edit Request')

@section('content')
@php
$pwMemberData = $request->teamSupportRequest->pwMember ? [
'id' => $request->teamSupportRequest->pwMember->id,
'first_name' => $request->teamSupportRequest->pwMember->first_name,
'father_name' => $request->teamSupportRequest->pwMember->father_name,
'last_name' => $request->teamSupportRequest->pwMember->last_name,
'mother_full_name' => $request->teamSupportRequest->pwMember->mother_full_name,
'phone' => $request->teamSupportRequest->pwMember->phone,
'city_name' => $request->teamSupportRequest->pwMember->voter->city->name ?? '',
'register_number' => $request->teamSupportRequest->pwMember->voter->register_number ?? '',
'city_id' => $request->teamSupportRequest->pwMember->voter->city_id ?? ''
] : null;

$memberData = $request->referenceMember ? [
'id' => $request->referenceMember->id,
'first_name' => $request->referenceMember->first_name,
'father_name' => $request->referenceMember->father_name,
'last_name' => $request->referenceMember->last_name,
'phone' => $request->referenceMember->phone
] : null;
@endphp
<div class="min-h-screen bg-[#fcf7f8]" x-data="teamSupportEditForm(@json(auth()->user()->hasRole('hor')))" x-init="init()">
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('team-support.drafts') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Edit Request</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="safe-area py-4">
        <div class="page-container">
            <!-- Request Number Info -->
            <div class="bg-[#f8f0e2] p-4 rounded-xl mb-4 border border-[#f8f0e2]">
                <p class="text-sm font-semibold text-[#622032]">Editing: {{ $request->request_number }}</p>
                <p class="text-xs text-[#622032]/60">Created on {{ $request->request_date->format('M d, Y') }}</p>
            </div>

            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <form @submit.prevent="submitForm" class="space-y-8">

                    <div class="bg-[#f8f0e2] p-4 rounded-lg border-2 border-[#931335]/20">
                        <h3 class="text-sm font-bold text-[#622032] mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Select PW Member (Required) *
                        </h3>
                        <p class="text-xs text-[#622032]/70 mb-3">You can only select pw members from your assigned location</p>

                        <div class="relative" @click.away="pwMemberSearchOpen = false">
                            <input
                                type="text"
                                x-model="pwMemberSearch"
                                @focus="pwMemberSearchOpen = true"
                                @input.debounce.400ms="if(pwMemberSearch.length >= 2) searchPwMembers(); else pwMemberResults = []"
                                placeholder="Type at least 2 characters to search..."
                                class="input-field"
                                :class="{ 'border-red-500': !form.voter_id && submitAttempted }"
                                :disabled="loading"
                                autocomplete="off"
                                required
                                lang="ar" />

                            <div x-show="pwMemberSearchOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-xl max-h-60 overflow-y-auto border-2 border-[#931335]/20"
                                style="display: none;">
                                <ul class="py-1">
                                    <!-- Show prompt to type more characters -->
                                    <template x-if="pwMemberSearch.length < 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm italic">
                                            Type at least 2 characters to search
                                        </li>
                                    </template>

                                    <!-- Show loading spinner while searching -->
                                    <template x-if="pwMemberSearching && pwMemberSearch.length >= 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm">
                                            <div class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Searching...
                                            </div>
                                        </li>
                                    </template>

                                    <!-- Show results -->
                                    <template x-for="pwMember in pwMemberResults" :key="pwMember.id">
                                        <li @click.stop="selectPwMember(pwMember)"
                                            @mousedown.prevent
                                            class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer border-b border-gray-100 last:border-0 transition-colors">
                                            <div class="font-semibold text-[#622032]" x-text="pwMember.first_name + ' ' + pwMember.father_name + ' ' + pwMember.last_name" lang="ar"></div>
                                            <div class="text-xs text-[#622032]/60 flex items-center gap-2 mt-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span x-text="pwMember.city_name"></span>
                                                <span>•</span>
                                                <span x-text="pwMember.register_number"></span>
                                            </div>
                                        </li>
                                    </template>

                                    <!-- Show "no results" message after search completes with no results -->
                                    <template x-if="!pwMemberSearching && pwMemberResults.length === 0 && pwMemberSearch.length >= 2">
                                        <li class="px-4 py-3 text-gray-500 text-sm italic">
                                            No PW members found in your location
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>

                        <div x-show="form.voter_id" x-cloak class="mt-3 p-3 bg-white rounded-lg border-2 border-[#931335]/30">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-[#931335] mb-1">✓ Selected PW Member:</p>
                                    <p class="text-sm font-bold text-[#622032]" x-text="selectedPwMember?.first_name + ' ' + selectedPwMember?.father_name + ' ' + selectedPwMember?.last_name" lang="ar"></p>
                                    <p class="text-xs font-bold text-[#622032]/60" x-text="selectedPwMember?.mother_full_name" lang="ar"></p>
                                    <div class="flex items-center gap-2 text-xs text-[#622032]/60 mt-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span x-text="selectedPwMember?.city_name"></span>
                                        <span>•</span>
                                        <span x-text="selectedPwMember?.register_number"></span>
                                    </div>
                                </div>
                                <button type="button" @click="clearPwMember()" class="text-red-600 hover:text-red-700 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="!form.voter_id && submitAttempted" x-cloak class="mt-2 text-xs text-red-600">
                            Please select a pw member from your location
                        </div>
                    </div>

                    <div x-show="form.voter_id" x-cloak class="bg-[#fcf7f8] p-4 rounded-lg border border-[#f8f0e2]">
                        <h3 class="text-sm font-bold text-[#622032] mb-3">Requester Information (Auto-filled)</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Full Name:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedPwMember?.first_name + ' ' + selectedPwMember?.father_name + ' ' + selectedPwMember?.last_name" lang="ar"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Mother's Full Name:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedPwMember?.mother_full_name" lang="ar"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">Register Number:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedPwMember?.register_number" lang="ar"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#622032]/60">City:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedPwMember?.city_name"></span>
                            </div>
                            <div x-show="selectedPwMember?.phone" class="flex justify-between">
                                <span class="text-[#622032]/60">Phone:</span>
                                <span class="font-semibold text-[#622032]" x-text="selectedPwMember?.phone"></span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-[#f8f0e2]">
                        <h3 class="text-base font-bold text-[#622032]">Request Details</h3>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Request Type *
                            </label>
                            <select x-model="form.subtype" class="input-field" required :disabled="loading">
                                <option value="">Select Type</option>
                                <option value="تربوية">تربوية (Educational)</option>
                                <option value="طبية">طبية (Medical)</option>
                                <option value="استشفائية">استشفائية (Hospital/Healing)</option>
                                <option value="إجتماعية">إجتماعية (Social)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Reference (PW Member) *
                            </label>
                            <div class="relative" @click.away="memberSearchOpen = false">
                                <input
                                    type="text"
                                    x-model="memberSearch"
                                    @focus="memberSearchOpen = true"
                                    @input.debounce.300ms="if(memberSearch.length >= 2) searchMembers(); else memberResults = []"
                                    placeholder="Search PW member (min 2 chars)..."
                                    class="input-field"
                                    :disabled="loading"
                                    autocomplete="off"
                                    lang="ar" />

                                <div x-show="memberSearchOpen"
                                    x-transition
                                    class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-lg max-h-60 overflow-y-auto border border-gray-200">
                                    <ul class="py-1">
                                        <template x-if="memberSearching">
                                            <li class="px-4 py-3 text-gray-500 text-sm">
                                                <div class="flex items-center gap-2">
                                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    Searching...
                                                </div>
                                            </li>
                                        </template>

                                        <template x-for="member in memberResults" :key="member.id">
                                            <li @click="selectMember(member)"
                                                class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer border-b border-gray-100 last:border-0">
                                                <div class="font-semibold text-[#622032]" x-text="member.first_name + ' ' + member.father_name + ' ' + member.last_name" lang="ar"></div>
                                                <div class="text-xs text-[#622032]/60" x-text="member.phone"></div>
                                            </li>
                                        </template>

                                        <template x-if="!memberSearching && memberSearch.length < 2">
                                            <li class="px-4 py-3 text-gray-500 text-sm italic">
                                                Type at least 2 characters to search
                                            </li>
                                        </template>

                                        <template x-if="!memberSearching && memberResults.length === 0 && memberSearch.length >= 2">
                                            <li class="px-4 py-3 text-gray-500 text-sm italic">
                                                No members found
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <div x-show="form.reference_member_id" x-cloak class="mt-2 text-sm text-[#622032]">
                                Selected: <span class="font-semibold" x-text="selectedMember?.first_name + ' ' + selectedMember?.father_name + ' ' + selectedMember?.last_name" lang="ar"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Amount (USD) *
                            </label>
                            <input type="number" step="0.01" min="0" x-model="form.amount" class="input-field" required :disabled="loading" inputmode="numeric">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Notes <span class="text-xs font-normal">(Optional)</span>
                            </label>
                            <textarea x-model="form.notes" rows="4" class="input-field" :disabled="loading"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">
                                Supporting Documents <span class="text-xs font-normal">(Optional)</span>
                            </label>
                            <p class="text-xs text-[#622032]/60 mb-3">Upload images or documents to support your request (Max: 5 files, 5MB each)</p>

                            <div class="space-y-3">
                                <input
                                    type="file"
                                    @change="handleFileSelect($event)"
                                    accept="image/*,.pdf"
                                    multiple
                                    class="hidden"
                                    x-ref="fileInput"
                                    :disabled="loading || (supportingDocuments.length + existingDocuments.length) >= 5"
                                />

                                <button
                                    type="button"
                                    @click="$refs.fileInput.click()"
                                    :disabled="loading || (supportingDocuments.length + existingDocuments.length) >= 5"
                                    class="w-full p-4 border-2 border-dashed border-[#931335]/30 rounded-lg text-[#931335] hover:bg-[#fcf7f8] transition-all flex items-center justify-center gap-2"
                                    :class="{ 'opacity-50 cursor-not-allowed': (supportingDocuments.length + existingDocuments.length) >= 5 }">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <span x-text="(supportingDocuments.length + existingDocuments.length) >= 5 ? 'Maximum 5 files reached' : 'Click to upload files'"></span>
                                </button>

                                <!-- Display existing documents -->
                                <div x-show="existingDocuments.length > 0" class="space-y-2">
                                    <p class="text-xs font-semibold text-[#622032]">Existing Documents:</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        <template x-for="(doc, index) in existingDocuments" :key="'existing-' + index">
                                            <div class="relative bg-[#fcf7f8] rounded-lg border border-[#f8f0e2] p-2">
                                                <div class="aspect-square rounded overflow-hidden bg-white mb-2">
                                                    <img
                                                        :src="'/storage/' + doc"
                                                        :alt="'Existing Document ' + (index + 1)"
                                                        class="w-full h-full object-cover"
                                                        x-show="!doc.endsWith('.pdf')"
                                                    />
                                                    <div x-show="doc.endsWith('.pdf')" class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"></path>
                                                            <path d="M14 2v6h6"></path>
                                                            <path d="M10 12h4M10 15h4M10 18h4" stroke="white" stroke-width="1"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#622032] truncate mb-1">Document <span x-text="index + 1"></span></p>
                                                <button
                                                    type="button"
                                                    @click="removeExistingDocument(index)"
                                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Display new documents -->
                                <div x-show="supportingDocuments.length > 0" class="space-y-2">
                                    <p class="text-xs font-semibold text-[#622032]" x-show="existingDocuments.length > 0">New Documents:</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        <template x-for="(doc, index) in supportingDocuments" :key="'new-' + index">
                                            <div class="relative bg-[#fcf7f8] rounded-lg border border-[#f8f0e2] p-2">
                                                <div class="aspect-square rounded overflow-hidden bg-white mb-2">
                                                    <img
                                                        :src="doc.preview"
                                                        :alt="'Document ' + (index + 1)"
                                                        class="w-full h-full object-cover"
                                                        x-show="doc.type === 'image'"
                                                    />
                                                    <div x-show="doc.type === 'pdf'" class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-12 h-12 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"></path>
                                                            <path d="M14 2v6h6"></path>
                                                            <path d="M10 12h4M10 15h4M10 18h4" stroke="white" stroke-width="1"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#622032] truncate mb-1" x-text="doc.name"></p>
                                                <p class="text-xs text-[#622032]/60" x-text="(doc.size / 1024).toFixed(1) + ' KB'"></p>
                                                <button
                                                    type="button"
                                                    @click="removeDocument(index)"
                                                    class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div x-show="fileUploadError" x-cloak class="text-sm text-red-600 bg-red-50 p-3 rounded-lg" x-text="fileUploadError"></div>
                            </div>
                        </div>
                    </div>

                    <div x-show="errorMessage" x-cloak x-transition class="error-message">
                        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p x-text="errorMessage"></p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="button" @click="submitAsDraft" class="flex-1 btn-secondary" :disabled="loading">
                            <span x-show="!loading || submitAction !== 'save'">Save Changes</span>
                            <span x-show="loading && submitAction === 'save'" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                        <button type="button" @click="submitAndPublish" class="flex-1 btn-primary" :disabled="loading">
                            <span x-show="!loading || submitAction !== 'publish'">Save & Publish</span>
                            <span x-show="loading && submitAction === 'publish'" class="flex items-center justify-center">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Publishing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Budget Selection Modal (HOR only) -->
        <div x-show="showBudgetModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="showBudgetModal = false">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="showBudgetModal = false"></div>
            <div class="flex items-end sm:items-center justify-center min-h-screen p-0 sm:p-4">
                <div class="relative bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full max-w-lg sm:max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="sticky top-0 bg-white rounded-t-3xl sm:rounded-t-2xl p-4 sm:p-6 border-b border-[#f8f0e2]">
                        <h2 class="text-lg sm:text-xl font-bold text-[#622032] mb-2">Select Budget & Ready Date</h2>
                        <p class="text-sm sm:text-base text-[#622032]/70">
                            Amount: <span class="text-[#931335] font-semibold">($<span x-text="form.amount"></span>)</span>
                        </p>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4">
                        <!-- Budget Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Select Budget</label>
                            <select x-model="selectedBudget" @change="updateBudgetPreview" class="input-field text-sm sm:text-base">
                                <option value="">-- Select Budget --</option>
                                <template x-for="budget in budgets" :key="budget.id">
                                    <option :value="budget.id" x-text="`${budget.description} ($${budget.monthly_amount_in_usd})`"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Ready Date Selection -->
                        <div>
                            <label class="block text-sm font-semibold text-[#622032] mb-2">Ready Date</label>
                            <input type="date" x-model="readyDate" @change="updateBudgetPreview" class="input-field text-sm sm:text-base" :min="new Date().toISOString().split('T')[0]">
                        </div>

                        <!-- Budget Preview -->
                        <div x-show="budgetPreview" class="bg-[#f8f0e2] rounded-lg p-3 sm:p-4 space-y-2">
                            <h3 class="font-semibold text-[#622032] mb-2 text-sm sm:text-base">Budget Preview</h3>
                            <div class="flex justify-between text-xs sm:text-sm">
                                <span class="text-[#622032]/70">Monthly Budget:</span>
                                <span class="font-semibold text-[#622032]">$<span x-text="budgetPreview?.monthly_budget || 0"></span></span>
                            </div>
                            <div class="flex justify-between text-xs sm:text-sm">
                                <span class="text-[#622032]/70">Current Remaining:</span>
                                <span class="font-semibold" :class="budgetPreview?.current_remaining >= 0 ? 'text-green-600' : 'text-red-600'">
                                    $<span x-text="budgetPreview?.current_remaining || 0"></span>
                                </span>
                            </div>
                            <div class="flex justify-between text-xs sm:text-sm border-t border-[#622032]/20 pt-2">
                                <span class="text-[#622032]/70">After Request:</span>
                                <span class="font-bold" :class="budgetPreview?.after_request >= 0 ? 'text-green-600' : 'text-red-600'">
                                    $<span x-text="budgetPreview?.after_request || 0"></span>
                                </span>
                            </div>
                            <div x-show="!budgetPreview?.has_enough" class="text-xs text-red-600 font-semibold mt-2 flex items-start gap-1">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>Insufficient budget!</span>
                            </div>
                        </div>
                    </div>

                    <div class="sticky bottom-0 bg-white p-4 sm:p-6 border-t border-[#f8f0e2] flex flex-col sm:flex-row gap-3">
                        <button @click="showBudgetModal = false" class="w-full sm:flex-1 btn-secondary">Cancel</button>
                        <button @click="confirmPublishWithBudget" :disabled="loading || !selectedBudget || !readyDate" class="w-full sm:flex-1 btn-primary">
                            <span x-show="!loading">Publish & Allocate</span>
                            <span x-show="loading">Publishing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function teamSupportEditForm(isHor) {
        return {
            form: {
                voter_id: '{{ $request->teamSupportRequest->pw_member_id ?? "" }}',
                subtype: '{{ $request->teamSupportRequest->subtype }}',
                reference_member_id: '{{ $request->reference_member_id }}',
                amount: '{{ $request->teamSupportRequest->amount }}',
                notes: `{{ $request->teamSupportRequest->notes }}`,
                action: 'save',
                budget_id: '',
                ready_date: ''
            },
            loading: false,
            submitAction: '',
            submitAttempted: false,
            errorMessage: '',

            // Budget modal for HOR
            showBudgetModal: false,
            budgets: [],
            selectedBudget: '',
            readyDate: new Date().toISOString().split('T')[0],
            budgetPreview: null,
            userIsHor: isHor,

            // PW Member search
            pwMemberSearch: '{{ $pwMemberData ? ($pwMemberData["first_name"] . " " . $pwMemberData["father_name"] . " " . $pwMemberData["last_name"]) : "" }}',
            pwMemberSearchOpen: false,
            pwMemberSearching: false,
            pwMemberResults: [],
            selectedPwMember: @json($pwMemberData),
            pwMemberSearchTimeout: null,

            // Member search
            memberSearch: "{{ $memberData ? ($memberData['first_name'] . ' ' . $memberData['father_name'] . ' ' . $memberData['last_name']) : "" }}",
            memberSearchOpen: false,
            memberSearching: false,
            memberResults: [],
            selectedMember: @json($memberData),
            memberSearchTimeout: null,

            // Supporting documents
            supportingDocuments: [],
            existingDocuments: @json($request->teamSupportRequest->supporting_documents ?? []),
            removedDocuments: [],
            fileUploadError: '',

            init() {
                // Load all PW members initially
                this.searchMembers();
            },

            handleFileSelect(event) {
                const files = Array.from(event.target.files);
                this.fileUploadError = '';

                const totalDocuments = this.existingDocuments.length + this.supportingDocuments.length;
                const remainingSlots = 5 - totalDocuments;

                if (files.length > remainingSlots) {
                    this.fileUploadError = `You can only upload ${remainingSlots} more file(s). Maximum is 5 files.`;
                    event.target.value = '';
                    return;
                }

                files.forEach(file => {
                    // Validate file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        this.fileUploadError = `File "${file.name}" is too large. Maximum size is 5MB.`;
                        return;
                    }

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
                    if (!validTypes.includes(file.type)) {
                        this.fileUploadError = `File "${file.name}" is not a valid type. Only images and PDFs are allowed.`;
                        return;
                    }

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.supportingDocuments.push({
                            file: file,
                            preview: e.target.result,
                            name: file.name,
                            size: file.size,
                            type: file.type.startsWith('image/') ? 'image' : 'pdf'
                        });
                    };
                    reader.readAsDataURL(file);
                });

                event.target.value = '';
            },

            removeDocument(index) {
                this.supportingDocuments.splice(index, 1);
                this.fileUploadError = '';
            },

            removeExistingDocument(index) {
                const removedDoc = this.existingDocuments.splice(index, 1)[0];
                this.removedDocuments.push(removedDoc);
                this.fileUploadError = '';
            },

            async searchPwMembers() {
                if (this.pwMemberSearch.length < 2) {
                    this.pwMemberResults = [];
                    return;
                }

                this.pwMemberSearching = true;
                try {
                    const response = await fetch(`{{ route('team-support.search-members') }}?search=${encodeURIComponent(this.pwMemberSearch)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    const data = await response.json();
                    this.pwMemberResults = data;
                } catch (error) {
                    console.error('PW Member search error:', error);
                    this.pwMemberResults = [];
                } finally {
                    this.pwMemberSearching = false;
                }
            },

            selectPwMember(pwMember) {
                this.selectedPwMember = pwMember;
                this.form.voter_id = pwMember.id;
                this.pwMemberSearch = pwMember.first_name + ' ' + pwMember.father_name + ' ' + pwMember.last_name;
                this.pwMemberSearchOpen = false;
                this.submitAttempted = false;
            },

            clearPwMember() {
                this.selectedPwMember = null;
                this.form.voter_id = '';
                this.pwMemberSearch = '';
                this.pwMemberResults = [];
            },

            async searchMembers() {
                if (this.memberSearch.length < 2) {
                    this.memberResults = [];
                    return;
                }

                this.memberSearching = true;
                try {
                    const response = await fetch(`{{ route('team-support.search-members') }}?search=${encodeURIComponent(this.memberSearch)}`);
                    this.memberResults = await response.json();
                } catch (error) {
                    console.error('Member search error:', error);
                } finally {
                    this.memberSearching = false;
                }
            },

            selectMember(member) {
                this.selectedMember = member;
                this.form.reference_member_id = member.id;
                this.memberSearch = member.first_name + ' ' + member.father_name + ' ' + member.last_name;
                this.memberSearchOpen = false;
            },

            submitAsDraft() {
                this.form.action = 'save';
                this.submitAction = 'save';
                this.submitForm();
            },

            async submitAndPublish() {
                this.submitAttempted = true;

                if (!this.form.voter_id) {
                    this.errorMessage = 'Please select a pw member from your assigned location';
                    return;
                }

                if (!this.form.subtype || !this.form.reference_member_id || !this.form.amount) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                // If HOR, show budget modal before publishing
                if (this.userIsHor) {
                    this.form.action = 'publish';
                    this.submitAction = 'publish';
                    await this.showBudgetSelectionModal();
                } else {
                    // Non-HOR users just publish normally
                    this.form.action = 'publish';
                    this.submitAction = 'publish';
                    this.submitForm();
                }
            },

            async showBudgetSelectionModal() {
                try {
                    // Fetch user's zone budgets for team-support requests
                    const response = await fetch('/api/budgets/my-zones?request_type=team_support', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });
                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.budgets = data.budgets;

                        // Reset selections
                        this.selectedBudget = '';
                        this.readyDate = new Date().toISOString().split('T')[0];
                        this.budgetPreview = null;

                        this.showBudgetModal = true;
                    } else {
                        this.errorMessage = 'Failed to load budgets';
                    }
                } catch (error) {
                    this.errorMessage = 'Failed to load budgets';
                }
            },

            async updateBudgetPreview() {
                if (!this.selectedBudget || !this.readyDate) {
                    this.budgetPreview = null;
                    return;
                }

                try {
                    const response = await fetch('/api/budgets/preview', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            budget_id: this.selectedBudget,
                            amount: this.form.amount,
                            ready_date: this.readyDate
                        })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.budgetPreview = data;
                    }
                } catch (error) {
                    console.error('Failed to fetch budget preview');
                }
            },

            async confirmPublishWithBudget() {
                this.form.budget_id = this.selectedBudget;
                this.form.ready_date = this.readyDate;
                this.showBudgetModal = false;
                await this.submitForm();
            },

            async submitForm() {
                this.submitAttempted = true;

                if (!this.form.voter_id) {
                    this.errorMessage = 'Please select a pw member from your assigned location';
                    return;
                }

                if (!this.form.subtype || !this.form.reference_member_id || !this.form.amount) {
                    this.errorMessage = 'Please fill in all required fields';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    // Create FormData to handle file uploads
                    const formData = new FormData();

                    // Add _method for Laravel's PUT request handling
                    formData.append('_method', 'PUT');

                    // Append form fields
                    Object.keys(this.form).forEach(key => {
                        if (this.form[key] !== null && this.form[key] !== '') {
                            formData.append(key, this.form[key]);
                        }
                    });

                    // Append new supporting documents
                    this.supportingDocuments.forEach((doc, index) => {
                        formData.append(`supporting_documents[${index}]`, doc.file);
                    });

                    // Append existing documents (so they're not lost)
                    formData.append('existing_documents', JSON.stringify(this.existingDocuments));

                    // Append removed documents (so backend can delete them)
                    formData.append('removed_documents', JSON.stringify(this.removedDocuments));

                    const response = await fetch('{{ route("team-support.update", $request->id) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        window.location.href = data.redirect;
                    } else {
                        this.errorMessage = data.message || 'Failed to update request';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please try again.';
                    console.error('Submit error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
