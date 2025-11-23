@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        {{-- Mobile: Compact Info + Page Jumper --}}
        <div class="flex sm:hidden items-center justify-between w-full gap-3">
            {{-- Results Summary --}}
            <div class="text-xs text-[#622032]/60">
                <span class="font-semibold">{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}</span>
                <span>of</span>
                <span class="font-semibold">{{ $paginator->total() }}</span>
            </div>

            {{-- Page Input --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-[#622032]/60">Page</span>
                <input
                    type="number"
                    min="1"
                    max="{{ $paginator->lastPage() }}"
                    value="{{ $paginator->currentPage() }}"
                    x-data="{ goto(e) { window.location.href = '{{ $paginator->url(1) }}'.replace('page=1', 'page=' + e.target.value) } }"
                    @change="goto($event)"
                    class="w-14 px-2 py-1 text-xs text-center border border-[#f8f0e2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#622032]/20 focus:border-[#622032]"
                />
                <span class="text-xs text-[#622032]/60">of {{ $paginator->lastPage() }}</span>
            </div>
        </div>

        {{-- Desktop: Full Info --}}
        <div class="hidden sm:flex text-sm text-[#622032]/70">
            Showing
            @if ($paginator->firstItem())
                <span class="font-semibold mx-1">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-semibold mx-1">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-semibold mx-1">{{ $paginator->total() }}</span>
                results
            @else
                <span class="font-semibold mx-1">{{ $paginator->total() }}</span>
                results
            @endif
        </div>

        {{-- Pagination Buttons --}}
        <div class="flex items-center gap-1">
            {{-- Previous Button --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn pagination-btn-disabled" aria-disabled="true" aria-label="Previous">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline ml-1">Previous</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pagination-btn pagination-btn-nav" aria-label="Previous">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline ml-1">Previous</span>
                </a>
            @endif

            {{-- Page Numbers --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="pagination-btn pagination-btn-disabled" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="pagination-btn pagination-btn-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="pagination-btn pagination-btn-page" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Mobile: Current Page Indicator --}}
            <div class="flex sm:hidden items-center px-3 py-2 text-xs font-semibold text-[#622032] bg-[#f8f0e2] rounded-lg">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </div>

            {{-- Next Button --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pagination-btn pagination-btn-nav" aria-label="Next">
                    <span class="hidden sm:inline mr-1">Next</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="pagination-btn pagination-btn-disabled" aria-disabled="true" aria-label="Next">
                    <span class="hidden sm:inline mr-1">Next</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
