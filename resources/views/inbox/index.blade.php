@extends('layouts.app')

@section('title', 'Inbox')

@section('content')
<div class="min-h-screen bg-[#fcf7f8]" x-data="inboxManager()">
    <!-- Header -->
    <div class="mobile-header">
        <div class="safe-area">
            <div class="page-container py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="p-2 hover:bg-[#f8f0e2] rounded-lg transition-all mr-2">
                            <svg class="w-5 h-5 text-[#622032]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </a>
                        <h1 class="text-lg sm:text-xl font-bold text-[#622032] flex items-center gap-2">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Inbox
                        </h1>
                    </div>
                    
                    @if($unreadCount > 0)
                    <button @click="markAllAsRead" 
                            class="text-sm text-[#931335] hover:text-[#622032] font-semibold">
                        Mark all read
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="safe-area py-4">
        <div class="page-container space-y-4">
            
            <!-- Unread Count Badge -->
            @if($unreadCount > 0)
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-[#931335] rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm">{{ $unreadCount }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-[#622032]">Unread Notifications</p>
                            <p class="text-xs text-[#622032]/60">{{ $unreadCount }} new {{ Str::plural('notification', $unreadCount) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notifications List -->
            <div class="space-y-3">
                @forelse($notifications as $notification)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2] transition-all hover:shadow-md
                            {{ !$notification->is_read ? 'border-l-4 border-l-[#931335]' : '' }}">
                    
                    <div class="flex items-start gap-3">
                        <!-- Icon based on notification type -->
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                    @if($notification->type === 'request_published') bg-blue-50
                                    @elseif($notification->type === 'request_approved') bg-green-50
                                    @elseif($notification->type === 'request_rejected') bg-red-50
                                    @elseif($notification->type === 'request_final_approved') bg-purple-50
                                    @elseif($notification->type === 'request_ready') bg-amber-50
                                    @elseif($notification->type === 'request_collected') bg-green-50
                                    @else bg-gray-50
                                    @endif">
                            
                            @if($notification->type === 'request_published')
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            @elseif($notification->type === 'request_approved')
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @elseif($notification->type === 'request_rejected')
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @elseif($notification->type === 'request_final_approved')
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            @elseif($notification->type === 'request_ready')
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                                <h3 class="font-semibold text-[#622032] text-sm">{{ $notification->title }}</h3>
                                @if(!$notification->is_read)
                                <span class="w-2 h-2 bg-[#931335] rounded-full flex-shrink-0 ml-2"></span>
                                @endif
                            </div>
                            
                            <p class="text-sm text-[#622032]/70 mb-2">{{ $notification->message }}</p>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-[#622032]/50">{{ $notification->created_at->diffForHumans() }}</span>
                                
                                <div class="flex gap-2">
                                    @if($notification->request)
                                    <a href="{{ route('humanitarian.show', $notification->request_id) }}" 
                                       class="text-xs text-[#931335] hover:text-[#622032] font-semibold">
                                        View Request →
                                    </a>
                                    @endif
                                    
                                    @if(!$notification->is_read)
                                    <button @click="markAsRead({{ $notification->id }})" 
                                            class="text-xs text-[#622032]/60 hover:text-[#931335]">
                                        Mark read
                                    </button>
                                    @endif
                                    
                                    <button @click="deleteNotification({{ $notification->id }})" 
                                            class="text-xs text-red-600 hover:text-red-700">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-[#f8f0e2] rounded-full mb-4">
                        <svg class="w-8 h-8 text-[#622032]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-[#622032] mb-2">No Notifications</h3>
                    <p class="text-sm text-[#622032]/60">You're all caught up!</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
            <div class="bg-white rounded-xl p-4 shadow-sm border border-[#f8f0e2]">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function inboxManager() {
    return {
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/inbox/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('/inbox/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        async deleteNotification(notificationId) {
            if (!confirm('Delete this notification?')) return;

            try {
                const response = await fetch(`/inbox/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch (error) {
                console.error('Error deleting notification:', error);
            }
        }
    }
}
</script>
@endpush