{{-- resources/views/components/public/agencies/entry-card.blade.php --}}
@props([
'title' => 'Judul',
'excerpt' => null,
'tags' => [],
'publisher' => null,
'date' => null,
'subs' => null, // contoh: "5 Sub Indikator" (khusus data)
'views' => null,
'thumb' => null,
])

<div class="p-4 sm:p-5 md:p-6 flex gap-4 sm:gap-5 transition-colors duration-200">
    {{-- thumb --}}
    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl overflow-hidden ring-1 ring-gray-200 bg-gray-50 shrink-0 grid place-items-center
               dark:ring-gray-700 dark:bg-gray-700/50">
        @if($thumb)
        <img src="{{ $thumb }}" alt="" class="w-full h-full object-cover">
        @else
        <svg class="h-10 w-10 text-gray-300 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
            aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="3" class="opacity-60"></rect>
            <path d="M8 13h8v2H8zM8 9h8v2H8z" />
        </svg>
        @endif
    </div>

    <div class="min-w-0">
        <h3 class="text-base sm:text-lg font-semibold leading-tight text-gray-900 dark:text-gray-100">
            {{ $title }}
        </h3>

        {{-- chips --}}
        @if(!empty($tags))
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($tags as $t)
            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs
                               border border-teal-200 bg-teal-50 text-teal-700
                               dark:border-teal-700/40 dark:bg-teal-900/30 dark:text-teal-300">
                {{ $t }}
            </span>
            @endforeach
        </div>
        @endif

        {{-- excerpt --}}
        @if($excerpt)
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
            {{ $excerpt }}
        </p>
        @endif

        {{-- meta row --}}
        <div
            class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-gray-700 dark:text-gray-300">
            @if($publisher)
            <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 6a2 2 0 0 1 2-2h6l2 2h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z" />
                </svg>
                {{ $publisher }}
            </span>
            @endif

            @if($date)
            <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M7 3a1 1 0 0 1 1 1v1h8V4a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v2H3V7a2 2 0 0 1 2-2h1V4a1 1 0 0 1 1-1zM3 10h18v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                </svg>
                {{ $date }}
            </span>
            @endif

            @if($subs)
            <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h10v2H4z" />
                </svg>
                {{ $subs }}
            </span>
            @endif

            @if($views !== null)
            <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 5c-5 0-9 4.5-10 7 1 2.5 5 7 10 7s9-4.5 10-7c-1-2.5-5-7-10-7zm0 11a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                </svg>
                {{ $views }}
            </span>
            @endif
        </div>
    </div>
</div>
