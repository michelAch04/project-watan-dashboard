@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8 bg-[#fcf7f8]">
    <div class="w-full max-w-md text-center">
        <!-- Icon -->
        <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-6">
            <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>

        <!-- Message -->
        <h1 class="text-3xl font-bold text-[#622032] mb-4">Access Denied</h1>
        <p class="text-[#622032]/70 mb-8">
            You don't have permission to access this page. This area is restricted to administrators only.
        </p>

        <!-- Back Button -->
        <a href="{{ route('dashboard') }}" class="inline-flex items-center btn-primary">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Dashboard
        </a>
    </div>
</div>
@endsection