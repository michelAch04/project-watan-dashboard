@extends('layouts.app')

@section('title', 'Requests')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]">
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
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Requests
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-6">
        <div class="page-container space-y-6">

            <!-- Monthly List Button -->
            <div class="flex justify-end">
                <a href="{{ route('monthly-list.index') }}" class="bg-[#f8f0e2] hover:bg-[#dfd1ba] text-[#622032] font-semibold py-2 px-4 rounded-lg transition-all flex items-center justify-center w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Monthly List
                </a>
            </div>

            <!-- Request Type Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <!-- Humanitarian Requests Card -->
                @can('view_humanitarian')
                <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden hover:shadow-md transition-all">
                    <a href="{{ route('humanitarian.index') }}" class="block">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-7 h-7 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                    </svg>
                                </div>
                                <svg class="w-5 h-5 text-[#622032]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-[#622032] mb-2">Humanitarian Requests</h3>
                            <p class="text-sm text-[#622032]/60 mb-4">Track aid and assistance requests</p>

                            <!-- Stats Summary -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-blue-600 font-medium mb-1">Active</p>
                                    <p class="text-lg font-bold text-blue-700">{{ $humanitarian['active'] }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-amber-600 font-medium mb-1">Drafts</p>
                                    <p class="text-lg font-bold text-amber-700">{{ $humanitarian['drafts'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-green-600 font-medium mb-1">Done</p>
                                    <p class="text-lg font-bold text-green-700">{{ $humanitarian['completed'] }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endcan

                <!-- Public Requests Card -->
                @can('view_public')
                <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden hover:shadow-md transition-all">
                    <a href="{{ route('public-requests.index') }}" class="block">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-7 h-7 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M4.5 2.25a.75.75 0 000 1.5v16.5h-.75a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5h-.75V3.75a.75.75 0 000-1.5h-15zM9 6a.75.75 0 000 1.5h1.5a.75.75 0 000-1.5H9zm-.75 3.75A.75.75 0 019 9h1.5a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM9 12a.75.75 0 000 1.5h1.5a.75.75 0 000-1.5H9zm3.75-5.25A.75.75 0 0113.5 6H15a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM13.5 9a.75.75 0 000 1.5H15A.75.75 0 0015 9h-1.5zm-.75 3.75a.75.75 0 01.75-.75H15a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zM9 19.5v-2.25a.75.75 0 01.75-.75h4.5a.75.75 0 01.75.75v2.25a.75.75 0 01-.75.75h-4.5A.75.75 0 019 19.5z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <svg class="w-5 h-5 text-[#622032]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-[#622032] mb-2">Public Facilities</h3>
                            <p class="text-sm text-[#622032]/60 mb-4">Manage infrastructure requests</p>

                            <!-- Stats Summary -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-blue-600 font-medium mb-1">Active</p>
                                    <p class="text-lg font-bold text-blue-700">{{ $public['active'] }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-amber-600 font-medium mb-1">Drafts</p>
                                    <p class="text-lg font-bold text-amber-700">{{ $public['drafts'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-green-600 font-medium mb-1">Done</p>
                                    <p class="text-lg font-bold text-green-700">{{ $public['completed'] }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endcan

                <!-- Diapers Requests Card -->
                @can('view_diapers')
                <div class="bg-white rounded-xl shadow-sm border border-[#f8f0e2] overflow-hidden hover:shadow-md transition-all">
                    <a href="{{ route('diapers-requests.index') }}" class="block">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-7 h-7 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/>
                                    </svg>
                                </div>
                                <svg class="w-5 h-5 text-[#622032]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-[#622032] mb-2">Diapers Requests</h3>
                            <p class="text-sm text-[#622032]/60 mb-4">Manage diaper distribution</p>

                            <!-- Stats Summary -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="bg-blue-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-blue-600 font-medium mb-1">Active</p>
                                    <p class="text-lg font-bold text-blue-700">{{ $diapers['active'] }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-amber-600 font-medium mb-1">Drafts</p>
                                    <p class="text-lg font-bold text-amber-700">{{ $diapers['drafts'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-2 text-center">
                                    <p class="text-xs text-green-600 font-medium mb-1">Done</p>
                                    <p class="text-lg font-bold text-green-700">{{ $diapers['completed'] }}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endcan

            </div>

        </div>
    </div>
</div>
@endsection
