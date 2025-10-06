{{-- resources/views/components/public/publications/info-panel.blade.php --}}
@props(['publication', 'fileSize' => null])

<aside class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 sm:p-6 md:sticky md:top-24
           dark:border-gray-700 dark:bg-gray-800 transition-colors duration-200">
    {{-- Logo Instansi --}}
    <div class="flex justify-center">
        @if($publication->agency?->logo_url)
              <img src="{{ resolve_media_url($publication->agency->logo_url, ['temporary'=>false, 'fallback'=>asset('logo-hsu.png')]) }}" alt="{{ $publication->agency->name }}"
                  class="h-20 sm:h-24 w-auto object-contain"
                  onerror="this.onerror=null;this.src='{{ asset('logo-hsu.png') }}'">
        @else
        <div class="h-20 sm:h-24 w-32 grid place-content-center text-teal-600/40 dark:text-teal-300/40">
            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2 2 7v2h20V7ZM4 11v9h6v-6h4v6h6v-9Z" />
            </svg>
        </div>
        @endif
    </div>

    <div class="mt-3 text-center">
        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
            {{ $publication->agency?->name }}
        </p>
    </div>

    {{-- Metadata list (dot leaders style) --}}
    <ul class="mt-5 space-y-2">
        @if($publication->year)
        <li class="flex items-baseline text-sm">
            <span class="text-gray-600 dark:text-gray-300 font-medium">Tahun</span>
            <span class="flex-1 border-b border-dotted border-gray-300 dark:border-gray-600 mx-2"></span>
            <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $publication->year }}</span>
        </li>
        @endif

        @if($publication->pages)
        <li class="flex items-baseline text-sm">
            <span class="text-gray-600 dark:text-gray-300 font-medium">Jumlah Hal.</span>
            <span class="flex-1 border-b border-dotted border-gray-300 dark:border-gray-600 mx-2"></span>
            <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $publication->pages }}</span>
        </li>
        @endif

        @if($fileSize)
        <li class="flex items-baseline text-sm">
            <span class="text-gray-600 dark:text-gray-300 font-medium">Ukuran File</span>
            <span class="flex-1 border-b border-dotted border-gray-300 dark:border-gray-600 mx-2"></span>
            <span class="text-gray-900 dark:text-gray-100 font-semibold">{{ $fileSize }}</span>
        </li>
        @endif

        @if($publication->doi)
        <li class="flex items-baseline text-sm">
            <span class="text-gray-600 dark:text-gray-300 font-medium">DOI</span>
            <span class="flex-1 border-b border-dotted border-gray-300 dark:border-gray-600 mx-2"></span>
            <a href="{{ 'https://doi.org/'.$publication->doi }}" target="_blank" rel="noopener"
                class="text-teal-700 dark:text-teal-300 font-semibold hover:underline">
                {{ $publication->doi }}
            </a>
        </li>
        @endif

        @if($publication->external_url)
        <li class="flex items-baseline text-sm">
            <span class="text-gray-600 dark:text-gray-300 font-medium">Link Asli</span>
            <span class="flex-1 border-b border-dotted border-gray-300 dark:border-gray-600 mx-2"></span>
            <a href="{{ $publication->external_url }}" target="_blank" rel="noopener"
                class="text-teal-700 dark:text-teal-300 font-semibold hover:underline">
                Kunjungi
            </a>
        </li>
        @endif
    </ul>
</aside>
