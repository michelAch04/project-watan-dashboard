@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        {{-- Results Summary --}}
        <div class="text-xs sm:text-sm text-[#622032]/70">
            @if ($paginator->firstItem())
                <span class="font-semibold">{{ $paginator->firstItem() }}</span>
                <span class="hidden sm:inline">to</span>
                <span class="sm:hidden">-</span>
                <span class="font-semibold">{{ $paginator->lastItem() }}</span>
                <span>of {{ number_format($paginator->total()) }}</span>
            @else
                <span class="font-semibold">{{ number_format($paginator->total()) }}</span>
                <span>results</span>
            @endif
        </div>

        {{-- Navigation Buttons --}}
        <div class="flex items-center gap-2 w-full sm:w-auto">
            {{-- Previous Button --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn-disabled flex-1 sm:flex-none" aria-disabled="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="ml-2">Previous</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn pagination-btn-nav flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="ml-2">Previous</span>
                </a>
            @endif

            {{-- Page Indicator --}}
            <div class="flex items-center gap-2 px-4 py-2 bg-[#f8f0e2] rounded-lg">
                <span class="text-xs sm:text-sm font-semibold text-[#622032]">
                    Page {{ $paginator->currentPage() }}
                </span>
            </div>

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn pagination-btn-nav flex-1 sm:flex-none">
                    <span class="mr-2">Next</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="pagination-btn pagination-btn-disabled flex-1 sm:flex-none" aria-disabled="true">
                    <span class="mr-2">Next</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
