{{-- resources/views/vendor/pagination/agencies.blade.php --}}
{{-- Pagination gaya "pill segmented" + info jumlah untuk Instansi --}}
@if ($paginator->hasPages())
<div class="px-4 sm:px-6 mt-6">
    <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
        {{-- Info hasil --}}
        <p class="text-sm text-gray-600 dark:text-gray-300">
            Menampilkan
            <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $paginator->count() }}</span>
            Instansi dari
            <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $paginator->total() }}</span>
        </p>

        {{-- Kontrol halaman --}}
        <nav class="inline-flex items-center rounded-xl border border-gray-300 bg-white shadow-sm overflow-hidden
                   dark:border-gray-600 dark:bg-gray-800 dark:shadow-none" role="navigation" aria-label="Pagination">

            {{-- Prev --}}
            @if ($paginator->onFirstPage())
            <span class="h-9 px-3 inline-flex items-center justify-center text-sm text-gray-400 cursor-not-allowed select-none
                           border-r border-gray-300 dark:text-gray-500 dark:border-gray-600" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M12.78 15.53a.75.75 0 0 1-1.06 0l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 1 1 1.06 1.06L9.56 10l3.22 3.22a.75.75 0 0 1 0 1.06Z" />
                </svg>
            </span>
            @else
            <a href="{{ $paginator->previousPageUrl() }}" class="h-9 px-3 inline-flex items-center justify-center text-sm
                          text-gray-700 hover:bg-gray-50 border-r border-gray-300
                          focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600
                          dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-600" rel="prev"
                aria-label="Sebelumnya">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M12.78 15.53a.75.75 0 0 1-1.06 0l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 1 1 1.06 1.06L9.56 10l3.22 3.22a.75.75 0 0 1 0 1.06Z" />
                </svg>
            </a>
            @endif

            {{-- Halaman angka + ellipsis --}}
            @foreach ($elements as $element)
            {{-- Ellipsis --}}
            @if (is_string($element))
            <span class="h-9 px-3 inline-flex items-center justify-center text-sm
                               text-gray-500 border-r border-gray-300 last:border-none select-none
                               dark:text-gray-400 dark:border-gray-600">…</span>
            @endif

            {{-- Array halaman --}}
            @if (is_array($element))
            @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
            <span aria-current="page" class="h-9 px-3 inline-flex items-center justify-center text-sm font-semibold
                                         bg-teal-500 text-white border-r border-gray-300 last:border-none
                                         dark:border-gray-600">
                {{ $page }}
            </span>
            @else
            <a href="{{ $url }}" class="h-9 px-3 inline-flex items-center justify-center text-sm
                                      text-gray-700 hover:bg-gray-50 border-r border-gray-300 last:border-none
                                      focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600
                                      dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-600"
                aria-label="Halaman {{ $page }}">
                {{ $page }}
            </a>
            @endif
            @endforeach
            @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="h-9 px-3 inline-flex items-center justify-center text-sm
                          text-gray-700 hover:bg-gray-50 border-l border-gray-300
                          focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600
                          dark:text-gray-200 dark:hover:bg-gray-700 dark:border-gray-600" rel="next"
                aria-label="Berikutnya">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M7.22 4.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L10.44 10 7.22 6.78a.75.75 0 0 1 0-1.06Z" />
                </svg>
            </a>
            @else
            <span class="h-9 px-3 inline-flex items-center justify-center text-sm
                           text-gray-400 cursor-not-allowed select-none
                           border-l border-gray-300 dark:text-gray-500 dark:border-gray-600" aria-hidden="true">
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M7.22 4.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 1 1-1.06-1.06L10.44 10 7.22 6.78a.75.75 0 0 1 0-1.06Z" />
                </svg>
            </span>
            @endif
        </nav>
    </div>
</div>
@endif
