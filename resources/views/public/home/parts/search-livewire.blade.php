<section class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center">
            <div class="relative w-full max-w-2xl" x-data="{ focused: false }" @click.away="$wire.hideResults()">
                <div class="relative">
                    <!-- Icon -->
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-400 dark:text-gray-500 transition-colors duration-200"
                            viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path
                                d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.71.71l.27.28v.79l5 5 1.5-1.5-5-5ZM10 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                        </svg>
                    </span>

                    <!-- Input -->
                    <input type="text" name="q" wire:model.live.debounce.300ms="q"
                        placeholder="Cari data, publikasi, atau walidata..." autocomplete="off" 
                        @focus="focused = true" @blur="focused = false"
                        @keydown.enter.prevent=""
                        class="w-full rounded-full border border-gray-200 dark:border-gray-700
                               bg-white dark:!bg-gray-800
                               pl-11 pr-14 py-3 shadow-sm
                               text-gray-800 dark:text-gray-100
                               placeholder-gray-400 dark:placeholder-gray-500
                               focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                               transition-colors duration-200
                               @if($showResults && count($searchResults) > 0) rounded-b-none @endif" 
                        aria-label="Pencarian data" />

                    <!-- Tombol clear -->
                    <button type="button" wire:click="clearSearch" x-data
                        @class([ 'absolute inset-y-0 right-3 my-auto h-8 w-8 grid place-items-center rounded-full transition-colors duration-200'
                        , 'text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300'=>
                        true,
                        'hidden' => $q === '',
                        ])
                        aria-label="Bersihkan pencarian">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"
                            aria-hidden="true">
                            <path
                                d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3 1.4 1.4z" />
                        </svg>
                    </button>

                    <!-- Loader kecil -->
                    <div class="absolute -bottom-6 left-1 text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200"
                        wire:loading.delay.shortest>
                        Mencari…
                    </div>
                </div>

                <!-- Search Results Dropdown -->
                @if($showResults && count($searchResults) > 0)
                <div class="absolute top-full left-0 right-0 bg-white dark:!bg-gray-800 border border-t-0 border-gray-200 dark:!border-gray-700 rounded-b-lg shadow-lg z-50 max-h-96 overflow-y-auto">
                    @foreach($searchResults as $result)
                    <a href="{{ $result['url'] }}" 
                       @if(in_array($result['type'], ['dataset', 'walidata'])) wire:navigate @endif
                       class="flex items-start gap-3 p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-600 last:border-b-0 transition-colors duration-150">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-1">
                            @if($result['icon'] === 'database')
                                <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                                </svg>
                            @elseif($result['icon'] === 'book-open')
                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                                </svg>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 text-sm leading-tight">
                                    {{ $result['title'] }}
                                </h4>
                                <span class="flex-shrink-0 ml-2 px-2 py-1 text-xs font-medium rounded-full
                                    @if($result['type'] === 'dataset') bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300
                                    @elseif($result['type'] === 'publikasi') bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300
                                    @else bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300 @endif">
                                    {{ ucfirst($result['type']) }}
                                </span>
                            </div>
                            
                            @if($result['description'])
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {!! strip_tags(Str::of($result['description'])->before('</p>')->before("&nbsp;")) ?? '' !!}
                            </p>
                            @endif

                            @if(isset($result['data_info']) && $result['type'] === 'walidata')
                            <p class="text-sm text-gray-500 dark:text-gray-500 mt-1 font-mono">
                                {{ $result['data_info'] }}
                            </p>
                            @endif

                            <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $result['category'] }}</span>
                                <span>•</span>
                                <span>{{ $result['institution'] }}</span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- No Results Message -->
                @if($showResults && count($searchResults) === 0 && strlen($q) >= 2)
                <div class="absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-t-0 border-gray-200 dark:border-gray-700 rounded-b-lg shadow-lg z-50 p-4 text-center">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">
                        Tidak ada hasil untuk "{{ $q }}"
                    </p>
                </div>
                @endif

                <!-- Area hasil (accessibility) -->
                <div class="sr-only" aria-live="polite">
                    {{ $q === '' ? 'Kolom pencarian kosong' : 'Pencarian diperbarui: ' . count($searchResults) . ' hasil ditemukan' }}
                </div>
            </div>
        </div>
    </div>
</section>
