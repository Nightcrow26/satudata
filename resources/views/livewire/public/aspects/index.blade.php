{{-- resources/views/livewire/public/aspects/index.blade.php --}}
<div wire:init="load">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-14">
        {{-- Toolbar: search (kiri) + segmented pills sorting (kanan) --}}
        <div x-data="searchShortcut()" @keydown.window="handleKeydown($event)" class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6 p-4
                    bg-gray-50/50 rounded-xl border border-gray-100
                    dark:bg-gray-800/70 dark:border-gray-700 transition-colors duration-200">

            <script>
                function searchShortcut() {
                    return {
                        handleKeydown(event) {
                            // Keyboard shortcut untuk fokus search (/)
                            if (
                                event.key === '/' &&
                                !['INPUT','TEXTAREA','SELECT'].includes(event.target.tagName) &&
                                event.target.getAttribute('contenteditable') !== 'true' &&
                                !event.metaKey && !event.ctrlKey && !event.altKey
                            ) {
                                event.preventDefault();
                                this.$refs.searchInput?.focus();
                            }
                        }
                    }
                }
            </script>

            {{-- Search --}}
            <div class="relative w-full sm:w-80">
                <label for="q" class="sr-only">Cari aspek</label>
                <div class="relative">
                    <input id="q" x-ref="searchInput" type="search" wire:model.live.debounce.300ms="q"
                        placeholder="Cari nama aspek..." aria-describedby="search-shortcut" class="h-11 w-full rounded-lg border-0 bg-gray-50 pl-12 pr-10 text-sm text-gray-900
                                  ring-1 ring-inset ring-gray-200 placeholder:text-gray-500
                                  focus:bg-white focus:ring-2 focus:ring-teal-500
                                  dark:bg-gray-800 dark:text-gray-100 dark:placeholder:text-gray-400 dark:ring-gray-700
                                  dark:focus:bg-gray-900
                                  transition-all duration-200 ease-in-out" />

                    {{-- ikon search --}}
                    <div class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2">
                        <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 0 0 2 9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>

                    {{-- tombol clear (muncul hanya saat ada query) --}}
                    @if($q !== '')
                    <button type="button" wire:click="clearSearch"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 h-6 w-6 grid place-items-center rounded-md
                                       text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                       dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700
                                       transition-all duration-150
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-1"
                        aria-label="Bersihkan pencarian">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path
                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                        </svg>
                    </button>
                    @endif
                </div>

                <span id="search-shortcut" class="sr-only">Gunakan keyboard shortcut '/' untuk fokus ke pencarian</span>
            </div>

            {{-- Sorting: Dropdown style --}}
            <div x-data="{
                    sort: @entangle('sort').live,
                    open: false,
                    getSortLabel() {
                        switch(this.sort) {
                            case 'recent': return 'Terbaru';
                            case 'name_asc': return 'Nama (A-Z)';
                            case 'name_desc': return 'Nama (Z-A)';
                            default: return 'Terbaru';
                        }
                    },
                    getSortIcon() { return this.sort === 'recent' ? 'clock' : 'list'; }
                }" class="relative">
                <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                               text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm
                               hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2
                               dark:text-gray-200 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700
                               transition-colors duration-200" aria-haspopup="true" :aria-expanded="open">

                    {{-- Icon --}}
                    <svg x-show="getSortIcon() === 'clock'" class="h-4 w-4 text-gray-500 dark:text-gray-400"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg x-show="getSortIcon() === 'list'" class="h-4 w-4 text-gray-500 dark:text-gray-400"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M2 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 5zm0 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 10zm0 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 15zm8.5-10a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm0 5a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm0 5a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75z" />
                    </svg>

                    <span x-text="getSortLabel()"></span>

                    {{-- Dropdown arrow --}}
                    <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"
                        viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                {{-- Dropdown menu --}}
                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-0 sm:right-0 sm:left-auto z-10 mt-2 w-48 origin-top-left sm:origin-top-right
                            rounded-lg bg-white shadow-lg ring-1 ring-black/5 focus:outline-none
                            dark:bg-gray-800 dark:ring-white/10" style="min-width: 100%;">
                    <div class="py-1">
                        <button @click="sort = 'recent'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left
                                       hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150" :class="{
                                    'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'recent',
                                    'text-gray-700 dark:text-gray-200': sort !== 'recent'
                                }">
                            <svg class="h-4 w-4 flex-shrink-0" :class="{
                                    'text-teal-600 dark:text-teal-400': sort === 'recent',
                                    'text-gray-400 dark:text-gray-500': sort !== 'recent'
                                 }" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Terbaru</span>
                            <svg x-show="sort === 'recent'" class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button @click="sort = 'name_asc'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left
                                       hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150" :class="{
                                    'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'name_asc',
                                    'text-gray-700 dark:text-gray-200': sort !== 'name_asc'
                                }">
                            <svg class="h-4 w-4 flex-shrink-0" :class="{
                                    'text-teal-600 dark:text-teal-400': sort === 'name_asc',
                                    'text-gray-400 dark:text-gray-500': sort !== 'name_asc'
                                 }" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M2 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 5zm0 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 10zm0 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 15zm8.5-10a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm0 5a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm0 5a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75z" />
                            </svg>
                            <span>Nama (A–Z)</span>
                            <svg x-show="sort === 'name_asc'" class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <button @click="sort = 'name_desc'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left
                                       hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150" :class="{
                                    'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'name_desc',
                                    'text-gray-700 dark:text-gray-200': sort !== 'name_desc'
                                }">
                            <svg class="h-4 w-4 flex-shrink-0" :class="{
                                    'text-teal-600 dark:text-teal-400': sort === 'name_desc',
                                    'text-gray-400 dark:text-gray-500': sort !== 'name_desc'
                                 }" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M2 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 5zm0 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 10zm0 5a.75.75 0 01.75-.75h4.5a.75.75 0 010 1.5h-4.5A.75.75 0 012 15zm8.5-10a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm0 5a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75zm0 5a.75.75 0 01.75-.75h6a.75.75 0 010 1.5h-6a.75.75 0 01-.75-.75z" />
                            </svg>
                            <span>Nama (Z–A)</span>
                            <svg x-show="sort === 'name_desc'" class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-400"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid Kartu Aspek (responsive & accessibility) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6 md:gap-8" role="list"
            aria-label="Daftar aspek">
            {{-- Skeleton loading --}}
            @if(!$isReady)
            @for($i=0;$i<10;$i++) <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 sm:p-5 animate-pulse
                                dark:border-gray-700 dark:bg-gray-800">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-xl bg-gray-100 dark:bg-gray-700 mx-auto"></div>
                <div class="mt-3 sm:mt-4 space-y-2">
                    <div class="h-3.5 bg-gray-100 dark:bg-gray-700 rounded w-3/5 mx-auto"></div>
                    <div class="h-2.5 bg-gray-100 dark:bg-gray-700 rounded w-2/5 mx-auto"></div>
                </div>
        </div>
        @endfor

        @elseif(count($items))
        {{-- resources/views/livewire/public/aspects/index.blade.php --}}
        @foreach($items as $a)
        <x-public.aspects.card :nama="$a['nama']" :slug="$a['slug']" :countd="$a['datasets_count']" :countw="$a['walidatas_count']" :countp="$a['publikasis_count']" :icon="$a['foto']" />
        @endforeach

        @else
        {{-- Empty state --}}
        <div class="col-span-2 sm:col-span-3 lg:col-span-4 xl:col-span-5">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-8 text-center text-gray-600
                                dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" role="status"
                aria-live="polite">
                <div class="mx-auto w-16 h-16 mb-4 text-gray-300 dark:text-gray-600">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-full h-full">
                        <path
                            d="M10 3a7 7 0 1 0 4.9 12.04l4.53 4.53a1 1 0 0 0 1.42-1.42l-4.53-4.53A7 7 0 0 0 10 3Zm-5 7a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z"
                            opacity="0.3" />
                    </svg>
                </div>
                <p class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada aspek</p>
                <p class="text-sm">Belum ada aspek yang dapat ditampilkan untuk kriteria pencarian ini.</p>
            </div>
        </div>
        @endif
</div>
</section>
</div>
