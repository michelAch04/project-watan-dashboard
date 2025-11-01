@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 py-4">
                        <h1 class="text-xl font-bold flex items-center row gap-2 text-[#622032]">
                            <svg class="w-6 h-6 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            View Your Profile
                        </h1>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                        @csrf
                        <button type="submit"
                            class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all duration-200 active:scale-95"
                            title="Logout">
                            <svg class="w-5 h-5 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#f8f0e2]">
            <div class="text-center mb-6">
                <div class="avatar w-20 h-20 mx-auto text-3xl mb-4">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <h2 class="text-xl font-bold text-[#622032] mb-1">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-[#622032]/60 mb-2">{{ auth()->user()->email }}</p>
                <p class="text-sm text-[#622032]/60">{{ auth()->user()->mobile }}</p>
            </div>

            <!-- Info Grid -->
            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-[#f8f0e2]">
                    <span class="text-sm text-[#622032]/60">Role</span>
                    <span class="text-sm font-semibold text-[#622032] capitalize">{{ auth()->user()->role()->name }}</span>
                </div>

                @if(auth()->user()->zone)
                <div class="flex justify-between items-center py-3 border-b border-[#f8f0e2]">
                    <span class="text-sm text-[#622032]/60">Zone</span>
                    <span class="text-sm font-semibold text-[#622032]">{{ auth()->user()->zone->name }}</span>
                </div>
                @endif

                <div class="flex justify-between items-center py-3">
                    <span class="text-sm text-[#622032]/60">Member Since</span>
                    <span class="text-sm font-semibold text-[#622032]">{{ auth()->user()->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection