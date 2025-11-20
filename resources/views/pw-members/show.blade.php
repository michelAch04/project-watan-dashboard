@extends('layouts.app')

@section('title', 'PW Member Details')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center">
                    <a href="{{ route('pw-members.index') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                        <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-lg sm:text-xl font-bold text-[#622032]">PW Member Details</h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            <!-- Member Info Card -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="avatar w-16 h-16 text-xl flex-shrink-0">
                        <svg class="w-6 h-6 inline" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <!-- Info -->
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-[#622032] mb-1" lang="ar">{{ $member->first_name }} {{ $member->father_name }} {{ $member->last_name }}</h2>
                        
                        <!-- Contact Info -->
                        <div class="space-y-2 mb-2">
                            <div class="flex items-center gap-2 text-[#622032]/80">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span>{{ $member->phone }}</span>
                            </div>

                            @if($member->email)
                            <div class="flex items-center gap-2 text-[#622032]/80">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ $member->email }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Status Badge -->
                        <span class="inline-block px-3 py-1 rounded-md text-sm font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $member->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Voter Info Card -->
            @if($member->voter)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-lg font-bold text-[#622032] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Linked Voter
                </h3>

                <div class="space-y-3">
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Full Name</div>
                        <div class="text-base text-[#622032]" lang="ar">{{ $member->voter->first_name }} {{ $member->voter->father_name }} {{ $member->voter->last_name }}</div>
                    </div>

                    @if($member->voter->mother_full_name)
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Mother Name</div>
                        <div class="text-base text-[#622032]" lang="ar">{{ $member->voter->mother_full_name }}</div>
                    </div>
                    @endif

                    @if($member->voter->register_number)
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Register Number</div>
                        <div class="text-base text-[#622032]" lang="ar">{{ $member->voter->register_number }}</div>
                    </div>
                    @endif

                    @if($member->voter->city)
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">City</div>
                        <div class="text-base text-[#622032]">{{ $member->voter->city->name }}</div>
                    </div>
                    @endif

                    @if($member->voter->city && $member->voter->city->zone)
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Zone</div>
                        <div class="text-base text-[#622032]">{{ $member->voter->city->zone->name }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- User Account Info -->
            @if($member->user && $member->user->cancelled == 0)
            <div class="bg-white rounded-xl p-6 shadow-sm border border-[#f8f0e2]">
                <h3 class="text-lg font-bold text-[#622032] mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    User Account
                </h3>

                <div class="space-y-3">
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Name</div>
                        <div class="text-base text-[#622032]">{{ $member->user->name }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Email</div>
                        <div class="text-base text-[#622032]">{{ $member->user->email }}</div>
                    </div>

                    @if($member->user->roles->count() > 0)
                    <div>
                        <div class="text-sm font-semibold text-[#622032]/60 mb-1">Role</div>
                        <span class="inline-block px-2 py-1 bg-[#fef9de] rounded-md text-sm font-medium text-[#622032] capitalize">
                            {{ $member->user->roles->first()->name }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @else
            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('hor'))
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-bold text-blue-900 mb-1">No User Account</h4>
                        <p class="text-sm text-blue-700 mb-3">This PW member doesn't have a user account yet.</p>
                        <a href="{{ route('users.create', ['pw_member_id' => $member->id]) }}" class="inline-block btn-primary text-sm">
                            Create User Account
                        </a>
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>
</div>
@endsection