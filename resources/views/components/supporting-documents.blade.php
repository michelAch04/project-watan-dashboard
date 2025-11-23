@props(['documents' => [], 'canDownload' => false])

@if(!empty($documents) && is_array($documents))
<div x-data="documentViewer()" class="space-y-3">
    <h3 class="text-sm font-bold text-[#622032] flex items-center gap-2">
        <svg class="w-5 h-5 text-[#931335]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
        </svg>
        Supporting Documents ({{ count($documents) }})
    </h3>

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        @foreach($documents as $index => $doc)
        @php
            $isPdf = str_ends_with(strtolower($doc), '.pdf');
            $fileUrl = asset('storage/' . $doc);
        @endphp
        <div class="relative bg-[#fcf7f8] rounded-lg border border-[#f8f0e2] p-2 group hover:shadow-md transition-all">
            <div class="aspect-square rounded overflow-hidden bg-white mb-2 cursor-pointer"
                 @click="openLightbox('{{ $fileUrl }}', {{ $isPdf ? 'true' : 'false' }})">
                @if($isPdf)
                    <div class="w-full h-full flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-[#931335]" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"></path>
                            <path d="M14 2v6h6"></path>
                            <path d="M10 12h4M10 15h4M10 18h4" stroke="white" stroke-width="1"></path>
                        </svg>
                        <span class="text-xs text-[#622032] mt-2">PDF</span>
                    </div>
                @else
                    <img src="{{ $fileUrl }}"
                         alt="Document {{ $index + 1 }}"
                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-200"
                         loading="lazy">
                @endif
            </div>

            <p class="text-xs text-[#622032] truncate mb-1">Document {{ $index + 1 }}</p>

            @if($canDownload)
            <a href="{{ $fileUrl }}"
               download
               class="absolute bottom-2 right-2 bg-[#931335] text-white rounded-full p-1.5 hover:bg-[#622032] transition-all opacity-0 group-hover:opacity-100 shadow-lg"
               title="Download">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </a>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Lightbox Modal -->
    <div x-show="lightboxOpen"
         x-cloak
         @click="closeLightbox()"
         @keydown.escape.window="closeLightbox()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-90 p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <button @click="closeLightbox()"
                class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <div @click.stop class="max-w-6xl max-h-[90vh] w-full">
            <div x-show="!currentIsPdf" class="flex items-center justify-center">
                <img :src="currentUrl"
                     alt="Document preview"
                     class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-2xl">
            </div>

            <div x-show="currentIsPdf" class="bg-white rounded-lg shadow-2xl h-[90vh]">
                <iframe :src="currentUrl"
                        class="w-full h-full rounded-lg"
                        frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function documentViewer() {
    return {
        lightboxOpen: false,
        currentUrl: '',
        currentIsPdf: false,

        openLightbox(url, isPdf) {
            this.currentUrl = url;
            this.currentIsPdf = isPdf;
            this.lightboxOpen = true;
            document.body.style.overflow = 'hidden';
        },

        closeLightbox() {
            this.lightboxOpen = false;
            this.currentUrl = '';
            this.currentIsPdf = false;
            document.body.style.overflow = '';
        }
    }
}
</script>
@endif
