@extends('layouts.app')

@section('title', 'Voters List')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="votersListIndex()">
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
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                        </svg>
                        Voters List
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Info Banner -->
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-blue-700">This is a read-only view of voters. You can create PW members from voters listed here.</p>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <form action="{{ route('voters-list.index') }}" method="GET" class="space-y-3">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-xs font-semibold text-[#622032] mb-1">Search</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            class="input-field text-sm"
                            placeholder="Name, phone, or RO number...">
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

                    <!-- Buttons -->
                    <div class="flex justify-end gap-2">
                        <button type="submit" class="btn-primary text-sm py-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Apply
                        </button>
                        @if(request('search') || request('zone_id') || request('city_id'))
                        <a href="{{ route('voters-list.index') }}" class="btn-secondary text-sm py-2 text-center">
                            Clear
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Voters List -->
            <div class="space-y-3">
                @forelse($voters as $voter)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="avatar w-12 h-12 text-base flex-shrink-0">
                            {{ strtoupper(substr($voter->first_name ?? 'V', 0, 1)) }}
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-[#622032] mb-1">
                                {{ $voter->first_name }} {{ $voter->second_name }} {{ $voter->third_name }} {{ $voter->last_name }}
                            </h3>

                            @if($voter->phone)
                            <p class="text-xs text-[#622032]/60 mb-1">{{ $voter->phone }}</p>
                            @endif

                            @if($voter->ro_number)
                            <p class="text-xs text-[#622032]/60 mb-2">RO: {{ $voter->ro_number }}</p>
                            @endif

                            <!-- Location -->
                            @if($voter->city)
                            <div class="flex items-start gap-1 text-xs text-[#622032]/70 mb-1">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>
                                    {{ $voter->city->name }}
                                    @if($voter->city->zone)
                                    - {{ $voter->city->zone->name }}
                                    @endif
                                </span>
                            </div>
                            @endif

                            <!-- Has PW Member -->
                            @php
                                $hasPwMember = $voter->pwMember && $voter->pwMember->cancelled == 0;
                            @endphp
                            @if($hasPwMember)
                            <div class="flex items-start gap-1 text-xs text-green-600">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Has PW Member</span>
                            </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('hor'))
                        <div class="flex flex-col gap-2">
                            @if(!$hasPwMember)
                            <a href="{{ route('pw-members.create', ['voter_id' => $voter->id]) }}"
                                class="p-2 bg-green-50 hover:bg-green-100 rounded-lg transition-all active:scale-95"
                                title="Create PW Member">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </a>
                            @else
                            <a href="{{ route('pw-members.show', $voter->pwMember->id) }}"
                                class="p-2 bg-blue-50 hover:bg-blue-100 rounded-lg transition-all active:scale-95"
                                title="View PW Member">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Voters Found</h3>
                    <p class="text-sm text-[#622032]/60">Try adjusting your search or filters</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($voters->hasPages())
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                {{ $voters->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function votersListIndex() {
        return {
            // No interactive functionality needed for read-only list
        }
    }
</script>
@endpush
