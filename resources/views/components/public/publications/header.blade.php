{{-- resources/views/components/public/publications/header.blade.php --}}
@props(['publication'])

<section aria-labelledby="pub-title" class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 sm:p-6
                dark:border-gray-700 dark:bg-gray-800 transition-colors duration-200">
    <div class="flex flex-col gap-4">
        <div>
            <h1 id="pub-title" class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 leading-snug
                       dark:text-white">
                {{ $publication->title }}
            </h1>

            {{-- Meta ala academia.edu: penulis • tahun • instansi --}}
            <div class="mt-2 text-sm text-gray-600 flex flex-wrap items-center gap-x-3 gap-y-1
                        dark:text-gray-300">
                @if(!empty($publication->authors))
                <span class="inline-flex items-center">
                    <svg class="h-4 w-4 mr-1.5 text-gray-400 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5Zm0 2c-4 0-8 2-8 6v1h16v-1c0-4-4-6-8-6Z" />
                    </svg>
                    {{ implode(', ', $publication->authors) }}
                </span>
                @endif

                @if($publication->year)
                <span class="inline-flex items-center">
                    <svg class="h-4 w-4 mr-1.5 text-gray-400 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M7 2h10a2 2 0 0 1 2 2v3H5V4a2 2 0 0 1 2-2Zm12 7H5v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2Zm-9 3h6v2H10Zm0 4h6v2H10Z" />
                    </svg>
                    {{ $publication->year }}
                </span>
                @endif

                @if($publication->agency?->name)
                <span class="inline-flex items-center">
                    <svg class="h-4 w-4 mr-1.5 text-gray-400 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path d="M3 10l9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1Z" />
                    </svg>
                    {{ $publication->agency->name }}
                </span>
                @endif
            </div>

            {{-- Kategori / tag --}}
            @if(!empty($publication->categories))
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($publication->categories as $cat)
                <span
                    class="inline-flex items-center rounded-full bg-teal-50 px-3 py-1 text-xs font-medium text-teal-700
                           dark:bg-teal-950/40 dark:text-teal-300 dark:ring-1 dark:ring-teal-800/50 transition-colors duration-200">
                    {{ $cat }}
                </span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Aksi Unduh / Baca --}}
        <div class="mt-4 flex flex-wrap items-center gap-3">
            @if($publication->file_path)
            <a href="{{ route('public.publications.download', ['publication' => request()->route('publication')]) }}"
                class="inline-flex items-center gap-2 rounded-full bg-teal-600 px-5 py-2.5 text-white text-sm font-semibold
                      hover:bg-teal-700 focus-visible:ring-2 focus-visible:ring-teal-200
                      dark:hover:bg-teal-600/90 dark:focus-visible:ring-teal-300 transition-colors duration-200">
                Unduh
            </a>

            <button id="btn-read-pdf-header" type="button" class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2.5
                       text-sm font-semibold text-gray-800 hover:bg-gray-50 focus-visible:ring-2 focus-visible:ring-teal-600
                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80
                       transition-colors duration-200">
                Baca di Viewer
            </button>
            @endif

            {{-- DOI / tautan eksternal opsional --}}
            @if($publication->doi || $publication->external_url)
            <a href="{{ $publication->external_url ?: ('https://doi.org/'.$publication->doi) }}" target="_blank"
                rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2.5
                      text-sm font-semibold text-gray-800 hover:bg-gray-50 focus-visible:ring-2 focus-visible:ring-teal-600
                      dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700/80
                      transition-colors duration-200">
                <svg class="h-5 w-5 text-gray-700 dark:text-gray-200" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14 3h7v7h-2V6.41l-9.29 9.3-1.42-1.42 9.3-9.29H14V3ZM5 5h5v2H7v10h10v-3h2v5H5V5Z" />
                </svg>
                Sumber
            </a>
            @endif
        </div>

        {{-- Metrik kecil --}}
        <div class="text-xs text-gray-500 flex flex-wrap items-center gap-4 dark:text-gray-400">
            @if($publication->views !== null)
            <span class="inline-flex items-center">
                <svg class="h-4 w-4 mr-1.5 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7Zm0 12a5 5 0 1 1 5-5 5 5 0 0 1-5 5Z" />
                </svg>
                {{ number_format($publication->views) }} views
            </span>
            @endif
            @if($publication->downloads !== null)
            <span class="inline-flex items-center">
                <svg class="h-4 w-4 mr-1.5 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11 2h2v10l3.5-3.5 1.5 1.5-6 6-6-6 1.5-1.5L11 12Zm-7 18h16v2H4Z" />
                </svg>
                {{ number_format($publication->downloads) }} unduhan
            </span>
            @endif
        </div>
    </div>
</section>
