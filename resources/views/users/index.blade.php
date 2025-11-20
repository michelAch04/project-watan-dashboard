@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="usersIndex()">
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
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z" />
                        </svg>
                        Users
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Create Button -->
            @if(auth()->user()->can('create_users'))
            <div class="flex justify-end">
                <a href="{{ route('users.create') }}" class="block btn-primary text-center flex align-center">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New User
                </a>
            </div>
            @endif
            <!-- Search and Filter Bar -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <form action="{{ route('users.index') }}" method="GET" class="space-y-3">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-xs font-semibold text-[#622032] mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            class="input-field text-sm"
                            placeholder="Name, email, or mobile...">
                    </div>

                    <!-- Zone Filter -->
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

                    <!-- Buttons -->
                    <div class="flex justify-end gap-2">
                        <button type="submit" class="btn-primary text-sm py-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Apply
                        </button>
                        @if(request('search') || request('zone_id'))
                        <a href="{{ route('users.index') }}" class="btn-secondary text-sm py-2 text-center">
                            Clear
                        </a>
                        @endif
                    </div>
                    @endif
                </form>
            </div>

            <!-- Users List -->
            <div class="space-y-3">
                @forelse($users as $user)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="avatar w-12 h-12 text-base flex-shrink-0">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-[#622032] mb-1">{{ $user->username }}</h3>
                            <p class="text-xs text-[#622032]/60 mb-2">{{ $user->email }}</p>

                            <!-- Role Badge -->
                            <span class="inline-block px-2 py-1 bg-[#fef9de] rounded-md text-xs font-medium text-[#622032] mb-2 capitalize">
                                {{ $user->roles->first()?->name ?? 'No Role' }}
                            </span>

                            <!-- Location -->
                            <div class="flex items-start gap-1 text-xs text-[#622032]/70 mb-1">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>
                                    @if($user->zones()->count() > 0)
                                    Zone: {{ $user->zones()->first()->username }}
                                    @elseif($user->cities()->count() > 0)
                                    City: {{ $user->cities()->first()->username }}
                                    @else
                                    N/A
                                    @endif
                                </span>
                            </div>

                            <!-- Manager -->
                            <div class="flex items-start gap-1 text-xs text-[#622032]/70">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>
                                    Reports to: {{ $user->manager ? $user->manager->username : 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('users.edit', $user->id) }}"
                                class="p-2 bg-[#f8f0e2] hover:bg-[#dfd1ba] rounded-lg transition-all active:scale-95">
                                <svg class="w-4 h-4 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @if($user->id !== Auth::id())
                            <button @click="deleteUser({{ $user->id }}, '{{ $user->name }}')"
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
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Users Found</h3>
                    <p class="text-sm text-[#622032]/60">Try adjusting your search or filters</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                {{ $users->links() }}
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
                    <h2 class="text-xl font-bold text-[#622032] mb-2">Delete User</h2>
                    <p class="text-[#622032]/70">Are you sure you want to delete <strong x-text="deleteUserName"></strong>?</p>
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

    function usersIndex() {
        return {
            showDeleteModal: false,
            deleteUserId: null,
            deleteUserName: '',
            deleting: false,

            deleteUser(userId, userName) {
                this.deleteUserId = userId;
                this.deleteUserName = userName;
                this.showDeleteModal = true;
            },

            async confirmDelete() {
                this.deleting = true;

                try {
                    const response = await fetch(`/users/${this.deleteUserId}`, {
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
                        alert(data.message || 'Failed to delete user');
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