@extends('layouts.app')

@section('title', 'PW Members')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="pwMembersIndex()">
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
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M3 4.5A1.5 1.5 0 014.5 3h15A1.5 1.5 0 0121 4.5v15a1.5 1.5 0 01-1.5 1.5h-15A1.5 1.5 0 013 19.5v-15zM8.25 9a.75.75 0 000 1.5h7.5a.75.75 0 000-1.5h-7.5zM8.25 12.75a.75.75 0 000 1.5h7.5a.75.75 0 000-1.5h-7.5zM8.25 16.5a.75.75 0 000 1.5h4.5a.75.75 0 000-1.5h-4.5z" clip-rule="evenodd" />
                        </svg>
                        PW Members
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Create Button -->
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('hor'))
            <div class="flex justify-end">
                <a href="{{ route('pw-members.create') }}" class="block btn-primary text-center flex align-center">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New Member
                </a>
            </div>
            @endif

            <!-- Search and Filter Bar -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <form action="{{ route('pw-members.index') }}" method="GET" class="space-y-3">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-xs font-semibold text-[#622032] mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            class="input-field text-sm"
                            placeholder="Name, phone, or email...">
                    </div>

                    <!-- Zone Filter (Admin only) -->
                    @if(auth()->user()->hasRole('admin'))
                    <div>
                        <label for="zone_id" class="block text-xs font-semibold text-[#622032] mb-1">Filter by Zone</label>
                        <select
                            id="zone_id"
                            name="zone_id"
                            class="input-field text-sm">
                            <option value="">All Zones</option>
                            @foreach($zones as $zone)
                            <option value="{{ $zone->id }}" {{ request('zone_id') == $zone->id ? 'selected' : '' }}>
                                {{ $zone->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- City Filter (Admin and HOR) -->
                    @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('hor'))
                    <div>
                        <label for="city_id" class="block text-xs font-semibold text-[#622032] mb-1">Filter by City</label>
                        <select
                            id="city_id"
                            name="city_id"
                            class="input-field text-sm">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                {{ $city->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Status Filter
                    <div>
                        <label for="status" class="block text-xs font-semibold text-[#622032] mb-1">Status</label>
                        <select
                            id="status"
                            name="status"
                            class="input-field text-sm">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div> -->

                    <!-- Buttons -->
                    <div class="flex justify-end gap-2">
                        <button type="submit" class="btn-primary text-sm py-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Apply
                        </button>
                        @if(request('search') || request('zone_id') || request('city_id') || request('status'))
                        <a href="{{ route('pw-members.index') }}" class="btn-secondary text-sm py-2 text-center">
                            Clear
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Members List -->
            <div class="space-y-3">
                @forelse($members as $member)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="avatar w-12 h-12 text-base flex-shrink-0">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-[#622032] mb-1">{{ $member->name }}</h3>
                            <p class="text-xs text-[#622032]/60 mb-1">{{ $member->phone }}</p>
                            @if($member->email)
                            <p class="text-xs text-[#622032]/60 mb-2">{{ $member->email }}</p>
                            @endif

                            <!-- Status Badge -->
                            <span class="inline-block px-2 py-1 rounded-md text-xs font-medium mb-2 {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $member->is_active ? 'Active' : 'Inactive' }}
                            </span>

                            <!-- Location (from voter) -->
                            @if($member->voter && $member->voter->city)
                            <div class="flex items-start gap-1 text-xs text-[#622032]/70 mb-1">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>
                                    {{ $member->voter->city->name }}
                                    @if($member->voter->city->zone)
                                    - {{ $member->voter->city->zone->name }}
                                    @endif
                                </span>
                            </div>
                            @endif

                            <!-- Has User Account -->
                            @if($member->user && $member->user->cancelled == 0)
                            <div class="flex items-start gap-1 text-xs text-green-600">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Has User Account</span>
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('pw-members.show', $member->id) }}"
                                class="p-2 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all active:scale-95">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>

                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('hor'))
                            <a href="{{ route('pw-members.edit', $member->id) }}"
                                class="p-2 bg-[#f8f0e2] hover:bg-[#dfd1ba] rounded-lg transition-all active:scale-95">
                                <svg class="w-4 h-4 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>

                            <button @click="deleteMember({{ $member->id }}, '{{ $member->name }}')"
                                class="p-2 bg-red-50 hover:bg-red-100 rounded-lg transition-all active:scale-95">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No PW Members Found</h3>
                    <p class="text-sm text-[#622032]/60">Try adjusting your search or filters</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($members->hasPages())
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                {{ $members->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="showDeleteModal = false">

        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
            x-show="showDeleteModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            @click="showDeleteModal = false"></div>

        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
                x-show="showDeleteModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                @click.stop>

                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-[#622032] mb-2">Delete PW Member</h2>
                    <p class="text-[#622032]/70">Are you sure you want to delete <strong x-text="deleteMemberName"></strong>?</p>
                    <p class="text-sm text-red-600 mt-2">This action cannot be undone.</p>
                </div>

                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 btn-secondary">
                        Cancel
                    </button>
                    <button @click="confirmDelete"
                        :disabled="deleting"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg transition-all active:scale-95"
                        :class="{ 'opacity-50 cursor-not-allowed': deleting }">
                        <span x-show="!deleting">Delete</span>
                        <span x-show="deleting" class="flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function pwMembersIndex() {
        return {
            showDeleteModal: false,
            deleteMemberId: null,
            deleteMemberName: '',
            deleting: false,

            deleteMember(memberId, memberName) {
                this.deleteMemberId = memberId;
                this.deleteMemberName = memberName;
                this.showDeleteModal = true;
            },

            async confirmDelete() {
                this.deleting = true;

                try {
                    const response = await fetch(`/pw-members/${this.deleteMemberId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to delete member');
                    }
                } catch (error) {
                    alert('Network error. Please try again.');
                    console.error('Delete error:', error);
                } finally {
                    this.deleting = false;
                    this.showDeleteModal = false;
                }
            }
        }
    }
</script>
@endpush