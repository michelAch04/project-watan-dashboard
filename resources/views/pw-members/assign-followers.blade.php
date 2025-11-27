@extends('layouts.app')

@section('title', 'Assign Followers')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="followersManager()" x-init="init()">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('pw-members.show', $member->id) }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                            <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-lg sm:text-xl font-bold text-[#622032]">Assign Followers</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Member Info Card -->
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-16 h-16 bg-[#931335] rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-[#622032]" lang="ar">{{ $member->first_name }} {{ $member->father_name }} {{ $member->last_name }}</h2>
                        <div class="mt-1 space-y-1">
                            @if($member->role)
                            <p class="text-sm text-[#622032]/70">
                                <span class="font-semibold">Role:</span> {{ $member->role->name }} @if($member->role->name_ar)({{ $member->role->name_ar }})@endif
                            </p>
                            @endif
                            @if($member->voter && $member->voter->city)
                            <p class="text-sm text-[#622032]/70">
                                <span class="font-semibold">City:</span> {{ $member->voter->city->name }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Follower Card -->
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-md font-bold text-[#622032] mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Follower
                </h3>

                <!-- Search Type Toggle -->
                <div class="mb-4">
                    <div class="flex gap-2 p-1 bg-[#f8f0e2] rounded-lg">
                        <button
                            @click="searchType = 'members'"
                            :class="searchType === 'members' ? 'bg-white shadow-sm' : 'bg-transparent'"
                            class="flex-1 py-2 px-4 rounded-md text-sm font-semibold text-[#622032] transition-all">
                            PW Members
                        </button>
                        <button
                            @click="searchType = 'voters'"
                            :class="searchType === 'voters' ? 'bg-white shadow-sm' : 'bg-transparent'"
                            class="flex-1 py-2 px-4 rounded-md text-sm font-semibold text-[#622032] transition-all">
                            Voters
                        </button>
                    </div>
                    <p class="text-xs text-[#622032]/60 mt-2">
                        <span x-show="searchType === 'members'">Search for existing PW members</span>
                        <span x-show="searchType === 'voters'">Search for voters (PW member will be auto-created)</span>
                    </p>
                </div>

                <div class="relative" @click.away="searchOpen = false">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @focus="searchOpen = true"
                        @input.debounce.400ms="if(searchQuery.length >= 2) performSearch(); else searchResults = []"
                        :placeholder="searchType === 'members' ? 'Search PW members (min 2 characters)...' : 'Search voters (min 2 characters)...'"
                        class="input-field"
                        :disabled="loading"
                        autocomplete="off"
                        lang="ar"
                    />

                    <div x-show="searchOpen && searchQuery.length >= 2"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute z-20 w-full mt-1 bg-white rounded-lg shadow-xl max-h-60 overflow-y-auto border-2 border-[#931335]/20"
                         style="display: none;">
                        <ul class="py-1">
                            <template x-if="searching">
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

                            <template x-for="result in searchResults" :key="result.id">
                                <li @click.stop="addFollower(result)"
                                    class="px-4 py-3 hover:bg-[#f8f0e2] cursor-pointer transition-colors border-b border-gray-100 last:border-b-0">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold text-[#622032]" x-text="result.first_name + ' ' + result.father_name + ' ' + result.last_name" lang="ar"></div>
                                            <div class="text-xs text-[#622032]/60" x-text="result.phone"></div>
                                            <div class="text-xs text-[#622032]/60" x-show="result.city" x-text="result.city?.name"></div>
                                        </div>
                                        <span x-show="result.is_voter" class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-semibold">Voter</span>
                                        <span x-show="!result.is_voter" class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold">Member</span>
                                    </div>
                                </li>
                            </template>

                            <template x-if="!searching && searchResults.length === 0 && searchQuery.length >= 2">
                                <li class="px-4 py-3 text-gray-500 text-sm">
                                    <span x-show="searchType === 'members'">No PW members found</span>
                                    <span x-show="searchType === 'voters'">No voters found</span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <template x-if="successMessage">
                    <div class="mt-4 bg-green-50 border-2 border-green-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-green-600" x-text="successMessage"></p>
                        </div>
                    </div>
                </template>

                <template x-if="errorMessage">
                    <div class="mt-4 bg-red-50 border-2 border-red-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-red-600" x-text="errorMessage"></p>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Current Followers -->
            <div class="bg-white rounded-xl p-4 sm:p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-md font-bold text-[#622032] mb-4 flex items-center justify-between">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Current Followers
                    </span>
                    <span class="text-sm font-normal text-[#622032]/60" x-text="followers.length + ' followers'"></span>
                </h3>

                <template x-if="followers.length === 0">
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 mx-auto text-[#622032]/20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="text-[#622032]/60 text-sm">No followers yet. Add followers using the search above.</p>
                    </div>
                </template>

                <div class="space-y-2">
                    <template x-for="follower in followers" :key="follower.id">
                        <div class="flex items-center justify-between p-3 bg-[#f8f0e2] rounded-lg border border-[#931335]/10">
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-[#622032]" x-text="follower.first_name + ' ' + follower.father_name + ' ' + follower.last_name" lang="ar"></div>
                                <div class="text-xs text-[#622032]/60" x-text="follower.phone"></div>
                            </div>
                            <button
                                @click="removeFollower(follower.id)"
                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                :disabled="loading">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Done Button -->
            <div class="sticky bottom-4">
                <a href="{{ route('pw-members.show', $member->id) }}" class="block btn-primary text-center">
                    Done
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function followersManager() {
        return {
            followers: @json($member->followers),
            searchType: 'members', // 'members' or 'voters'
            searchQuery: '',
            searchOpen: false,
            searching: false,
            searchResults: [],
            loading: false,
            successMessage: '',
            errorMessage: '',

            init() {
                // Check if we just auto-created this member
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('auto_created') === '1') {
                    this.successMessage = 'PW Member has been successfully created! You can now assign followers.';
                    setTimeout(() => {
                        this.successMessage = '';
                    }, 5000);
                }
            },

            async performSearch() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }

                // Clear results when switching search type
                this.searchResults = [];
                this.searching = true;

                try {
                    if (this.searchType === 'members') {
                        await this.searchPwMembers();
                    } else {
                        await this.searchVoters();
                    }
                } catch (error) {
                    console.error('Error performing search:', error);
                    this.searchResults = [];
                } finally {
                    this.searching = false;
                }
            },

            async searchPwMembers() {
                const response = await fetch(`{{ route('pw-members.search') }}?search=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();

                // Filter out members who are already followers and mark as PW members
                const followerIds = this.followers.map(f => f.id);
                this.searchResults = data
                    .filter(member => member.id !== {{ $member->id }} && !followerIds.includes(member.id))
                    .map(member => ({
                        ...member,
                        is_voter: false
                    }));
            },

            async searchVoters() {
                const response = await fetch(`{{ route('voters-list.search') }}?search=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();

                // Filter out voters who already have PW members that are followers
                const followerVoterIds = this.followers
                    .filter(f => f.voter_id)
                    .map(f => f.voter_id);

                this.searchResults = data
                    .filter(voter => !followerVoterIds.includes(voter.id) && voter.id !== {{ $member->voter_id ?? 'null' }})
                    .map(voter => ({
                        ...voter,
                        is_voter: true
                    }));
            },

            async addFollower(result) {
                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                try {
                    const payload = result.is_voter
                        ? { voter_id: result.id }  // If voter, send voter_id
                        : { follower_id: result.id }; // If PW member, send follower_id

                    const response = await fetch('{{ route('pw-members.add-follower', $member->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Add the newly created/existing PW member to followers list
                        this.followers.push(data.follower);
                        this.searchQuery = '';
                        this.searchResults = [];
                        this.searchOpen = false;

                        if (data.pw_member_created) {
                            this.successMessage = 'Follower added successfully! (PW Member auto-created)';
                        } else {
                            this.successMessage = 'Follower added successfully!';
                        }

                        setTimeout(() => {
                            this.successMessage = '';
                        }, 3000);
                    } else {
                        this.errorMessage = data.message || 'Failed to add follower';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please try again.';
                    console.error('Add follower error:', error);
                } finally {
                    this.loading = false;
                }
            },

            async removeFollower(followerId) {
                if (!confirm('Are you sure you want to remove this follower?')) {
                    return;
                }

                this.loading = true;
                this.errorMessage = '';
                this.successMessage = '';

                try {
                    const response = await fetch(`/pw-members/{{ $member->id }}/followers/${followerId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.followers = this.followers.filter(f => f.id !== followerId);
                        this.successMessage = 'Follower removed successfully!';
                        setTimeout(() => {
                            this.successMessage = '';
                        }, 3000);
                    } else {
                        this.errorMessage = data.message || 'Failed to remove follower';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please try again.';
                    console.error('Remove follower error:', error);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
