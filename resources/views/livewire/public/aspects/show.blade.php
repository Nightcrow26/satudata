{{-- resources/views/livewire/public/aspects/show.blade.php --}}
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-14" wire:init="load">

    {{-- Grid 12 kolom: panel kiri & list kanan --}}
    <div class="mt-6 md:mt-8 grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">
        {{-- LEFT: ringkasan aspek (sticky di desktop) --}}
        <div class="md:col-span-3">
            <x-public.aspects.summary-card 
                :icon="$aspek->foto ? Storage::disk('s3')->temporaryUrl($aspek->foto, now()->addHour()) : null" 
                :name="$aspek->nama"
                :count="$dataCounts['data'] + $dataCounts['walidata'] + $dataCounts['publikasi']" />
        </div>

        {{-- RIGHT: konten dengan tabs --}}
        <div class="md:col-span-9">
            {{-- Tabs Navigation --}}
            <nav aria-label="Konten aspek" class="mb-4">
                <div class="inline-flex rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden
                            dark:border-gray-600 dark:bg-gray-800">
                    <a href="#" wire:click.prevent="$set('tab','data')"
                        @class([
                            'h-10 px-4 inline-flex items-center justify-center text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600',
                            $isDataTab 
                                ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'
                        ])
                        aria-current="{{ $isDataTab ? 'page' : 'false' }}">
                        Data ({{ $dataCounts['data'] }})
                    </a>
                    <a href="#" wire:click.prevent="$set('tab','walidata')"
                        @class([
                            'h-10 px-4 inline-flex items-center justify-center text-sm border-l border-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 dark:border-gray-600',
                            $isWalidataTab 
                                ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'
                        ])
                        aria-current="{{ $isWalidataTab ? 'page' : 'false' }}">
                        Indikator Walidata ({{ $dataCounts['walidata'] }})
                    </a>
                    <a href="#" wire:click.prevent="$set('tab','publikasi')"
                        @class([
                            'h-10 px-4 inline-flex items-center justify-center text-sm border-l border-gray-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 dark:border-gray-600',
                            $isPublikasiTab 
                                ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'
                        ])
                        aria-current="{{ $isPublikasiTab ? 'page' : 'false' }}">
                        Publikasi ({{ $dataCounts['publikasi'] }})
                    </a>
                </div>
            </nav>
            {{-- Toolbar: search (kiri) + sorting (kanan) --}}
            <div x-data="searchShortcut()" @keydown.window="handleKeydown($event)" class="mb-5 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 p-4
                       bg-gray-50/50 rounded-xl border border-gray-100
                       dark:bg-gray-800/70 dark:border-gray-700
                       transition-colors duration-200">
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
                    <label for="q" class="sr-only">
                        @if($isDataTab) Cari Data di Aspek ini
                        @elseif($isWalidataTab) Cari Indikator Walidata
                        @else Cari Publikasi
                        @endif
                    </label>
                    <div class="relative">
                        <input id="q" x-ref="searchInput" type="search" wire:model.live.debounce.300ms="q"
                            placeholder="@if($isDataTab)Cari Data di Aspek ini...@elseif($isWalidataTab)Cari Indikator Walidata...@else Cari Publikasi...@endif" aria-describedby="search-shortcut" class="h-11 w-full rounded-lg border-0 pl-12 pr-10 text-sm
                                   bg-gray-50 text-gray-900 ring-1 ring-inset ring-gray-200
                                   placeholder:text-gray-500
                                   focus:bg-white focus:ring-2 focus:ring-teal-500
                                   dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700
                                   dark:placeholder:text-gray-400 dark:focus:bg-gray-900
                                   transition-all duration-200 ease-in-out" />

                        {{-- ikon search --}}
                        <div class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 0 0 2 9z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>

                        {{-- tombol clear (muncul hanya saat ada query) --}}
                        @if($q !== '')
                        <button type="button" wire:click="clearSearch" class="absolute right-2.5 top-1/2 -translate-y-1/2 h-6 w-6 grid place-items-center rounded-md
                                       text-gray-400 hover:text-gray-600 hover:bg-gray-100
                                       focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-1
                                       dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 dark:focus-visible:ring-offset-gray-900
                                       transition-all duration-150" aria-label="Bersihkan pencarian">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                        @endif
                    </div>

                    <span id="search-shortcut" class="sr-only">Gunakan keyboard shortcut '/' untuk fokus ke
                        pencarian</span>
                </div>

                {{-- Sorting: Dropdown style --}}
                <div x-data="{
                        sort: @entangle('sort').live,
                        open: false,
                        getSortLabel() {
                            switch(this.sort) {
                                case 'recent': return 'Update Terbaru';
                                case 'oldest': return 'Update Terlama';
                                case 'name_asc': return 'Nama A–Z';
                                case 'name_desc': return 'Nama Z–A';
                                default: return 'Update Terbaru';
                            }
                        },
                        getSortIcon() {
                            switch(this.sort) {
                                case 'recent': return 'clock';
                                case 'oldest': return 'clock-reverse';
                                case 'name_asc': return 'alpha-asc';
                                case 'name_desc': return 'alpha-desc';
                                default: return 'clock';
                            }
                        }
                    }" class="relative">
                    <button @click="open = !open" type="button" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium
                               bg-white text-gray-700 border border-gray-300 rounded-lg shadow-sm
                               hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2
                               dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-900
                               transition-colors duration-200" aria-haspopup="true" :aria-expanded="open">
                        {{-- Icon --}}
                        <svg x-show="getSortIcon() === 'clock'" class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                clip-rule="evenodd" />
                        </svg>
                        <svg x-show="getSortIcon() === 'alpha-asc'" class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10.75 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0v-4.392l1.657 1.251a.75.75 0 00.896-1.202l-2.5-1.875a.75.75 0 00-.553-.132zm-6 4a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5a.75.75 0 01-.75-.75z" />
                        </svg>
                        <svg x-show="getSortIcon() === 'alpha-desc'" class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M10.75 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0v-4.392l1.657 1.251a.75.75 0 00.896-1.202l-2.5-1.875a.75.75 0 00-.553-.132zm-6 4a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5a.75.75 0 01-.75-.75z"
                                transform="rotate(180 10 10)" />
                        </svg>
                        <svg x-show="getSortIcon() === 'clock-reverse'" class="h-4 w-4 text-gray-500 dark:text-gray-400"
                            viewBox="0 0 20 20" fill="currentColor" style="transform: scaleX(-1)">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                clip-rule="evenodd" />
                        </svg>

                        <span x-text="getSortLabel()"></span>

                        {{-- Dropdown arrow --}}
                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500 transition-transform duration-200"
                            :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
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
                            <button @click="sort = 'recent'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left transition-colors duration-150
                                       hover:bg-gray-50 text-gray-700
                                       dark:text-gray-200 dark:hover:bg-gray-700"
                                :class="{ 'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'recent' }">
                                <svg class="h-4 w-4 flex-shrink-0"
                                    :class="{ 'text-teal-600 dark:text-teal-300': sort === 'recent', 'text-gray-400 dark:text-gray-400': sort !== 'recent' }"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Update Terbaru</span>
                                <svg x-show="sort === 'recent'" class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-300"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <button @click="sort = 'oldest'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left transition-colors duration-150
                                       hover:bg-gray-50 text-gray-700
                                       dark:text-gray-200 dark:hover:bg-gray-700"
                                :class="{ 'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'oldest' }">
                                <svg class="h-4 w-4 flex-shrink-0" style="transform: scaleX(-1)"
                                    :class="{ 'text-teal-600 dark:text-teal-300': sort === 'oldest', 'text-gray-400 dark:text-gray-400': sort !== 'oldest' }"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Update Terlama</span>
                                <svg x-show="sort === 'oldest'" class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-300"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <button @click="sort = 'name_asc'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left transition-colors duration-150
                                       hover:bg-gray-50 text-gray-700
                                       dark:text-gray-200 dark:hover:bg-gray-700"
                                :class="{ 'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'name_asc' }">
                                <svg class="h-4 w-4 flex-shrink-0"
                                    :class="{ 'text-teal-600 dark:text-teal-300': sort === 'name_asc', 'text-gray-400 dark:text-gray-400': sort !== 'name_asc' }"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M10.75 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0v-4.392l1.657 1.251a.75.75 0 00.896-1.202l-2.5-1.875a.75.75 0 00-.553-.132zm-6 4a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5a.75.75 0 01-.75-.75z" />
                                </svg>
                                <span>Nama A–Z</span>
                                <svg x-show="sort === 'name_asc'"
                                    class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-300" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <button @click="sort = 'name_desc'; open = false" type="button" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-left transition-colors duration-150
                                       hover:bg-gray-50 text-gray-700
                                       dark:text-gray-200 dark:hover:bg-gray-700"
                                :class="{ 'bg-teal-50 text-teal-700 font-medium dark:bg-teal-900/30 dark:text-teal-300': sort === 'name_desc' }">
                                <svg class="h-4 w-4 flex-shrink-0"
                                    :class="{ 'text-teal-600 dark:text-teal-300': sort === 'name_desc', 'text-gray-400 dark:text-gray-400': sort !== 'name_desc' }"
                                    viewBox="0 0 20 20" fill="currentColor" transform="rotate(180 10 10)">
                                    <path
                                        d="M10.75 2.75a.75.75 0 00-1.5 0v14.5a.75.75 0 001.5 0v-4.392l1.657 1.251a.75.75 0 00.896-1.202l-2.5-1.875a.75.75 0 00-.553-.132zm-6 4a.75.75 0 01.75-.75h2.5a.75.75 0 010 1.5h-2.5a.75.75 0 01-.75-.75z" />
                                </svg>
                                <span>Nama Z–A</span>
                                <svg x-show="sort === 'name_desc'"
                                    class="h-4 w-4 ml-auto text-teal-600 dark:text-teal-300" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Loading skeleton --}}
            @if(!$isReady)
            <div class="space-y-4 sm:space-y-5">
                @for($i=0;$i<5;$i++) <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-4 sm:p-5 animate-pulse
                                    dark:border-gray-700 dark:bg-gray-800 transition-colors duration-200">
                    <div class="flex gap-4 sm:gap-5">
                        <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl bg-gray-100 ring-1 ring-gray-200
                                            dark:bg-gray-700 dark:ring-gray-700"></div>
                        <div class="flex-1 space-y-3">
                            <div class="h-4 bg-gray-100 rounded w-2/3 dark:bg-gray-700"></div>
                            <div class="h-3 bg-gray-100 rounded w-1/2 dark:bg-gray-700"></div>
                            <div class="h-3 bg-gray-100 rounded w-5/6 dark:bg-gray-700"></div>
                        </div>
                    </div>
            </div>
            @endfor
        </div>

        @elseif($items->count() > 0)
        <div class="space-y-4 sm:space-y-5" 
             x-data="{
                refreshTheme() {
                    // Refresh all custom badge themes when theme changes
                    this.$el.querySelectorAll('[data-custom-badge]').forEach(badge => {
                        if (badge.updateStyle && typeof badge.updateStyle === 'function') {
                            badge.updateStyle();
                        }
                    });
                }
             }"
             @theme-changed.window="refreshTheme()"
             @storage.window="refreshTheme()">
            @if($isDataTab)
                {{-- Display Data/Datasets --}}
                @foreach($items as $dataset)
                    @php
                        // Generate badge berdasarkan aspek dataset dengan warna dari database
                        $badges = [];
                        if ($dataset && isset($dataset->aspek) && $dataset->aspek) {
                            // Gunakan warna dari database atau fallback ke warna default
                            $aspekWarna = $dataset->aspek->warna ?? '#0d9488'; // Default teal-600
                            
                            $badges[] = [
                                'label' => $dataset->aspek->nama,
                                'variant' => 'custom',
                                'color' => $aspekWarna,
                            ];
                        }
                        
                        // URL untuk detail dataset
                        $detailUrl = route('public.data.show', $dataset->id ?? '#');
                    @endphp
                    
                    <x-public.data.dataset-card 
                        :title="$dataset->nama ?? 'Tanpa Judul'" 
                        :url="$detailUrl"
                        :thumb="$dataset->aspek?->foto_url ?? null" 
                        :badges="$badges" 
                        :instansi-label="$dataset->skpd?->singkatan ?? $dataset->skpd?->nama"
                        :date-label="$dataset->created_at ? $dataset->created_at->translatedFormat('d F Y') : null" 
                        :views="$dataset->view ?? 0"
                        :desc="\Illuminate\Support\Str::of($dataset->deskripsi ?? '')->stripTags()->limit(220)" />
                @endforeach

            @elseif($isWalidataTab)
                {{-- Display Walidata/Indikator --}}
                @foreach($items as $walidata)
                    @php
                        // Generate badge berdasarkan aspek walidata dengan warna dari database
                        $badges = [];
                        if ($walidata && isset($walidata->aspek) && $walidata->aspek) {
                            $aspekWarna = $walidata->aspek->warna ?? '#0d9488'; // Default teal-600
                            $badges[] = [
                                'label' => $walidata->aspek->nama,
                                'variant' => 'custom',
                                'color' => $aspekWarna,
                            ];
                        }
                        
                        // URL untuk detail walidata
                        $detailUrl = route('public.publications.download', $walidata->id ?? '#');
                    @endphp
                    
                    <x-public.walidata.walidata-card 
                        :title="$walidata->indikator?->uraian_indikator ?? 'Indikator Tidak Ditemukan'" 
                        :url="$detailUrl"
                        :thumb="$walidata->aspek?->foto_url ?? null" 
                        :badges="$badges" 
                        :instansi-label="$walidata->skpd?->singkatan ?? $walidata->skpd?->nama"
                        :date-label="$walidata->created_at ? $walidata->created_at->translatedFormat('d F Y') : null" 
                        :data-value="$walidata->data"
                        :data-unit="$walidata->satuan"
                        :year="$walidata->tahun"
                        :downloads="$p->download"
                        :desc="$walidata->indikator?->uraian_indikator ?? 'Definisi tidak tersedia'" />
                @endforeach

            @elseif($isPublikasiTab)
                {{-- Display Publikasi --}}
                @foreach($items as $publikasi)
                    <x-public.publications.publication-card 
                        :publication="$publikasi"
                        :url="route('public.publications.download', $publikasi->id)"
                        :downloads="$publikasi->download" />
                @endforeach
            @endif
        </div>

        {{-- PAGINATION --}}
        {{-- Placeholder saat loading (menjaga tata letak) --}}
        <div class="mt-6" wire:loading>
            <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                <div class="h-5 w-28 rounded-full bg-gray-200 animate-pulse"></div>
                <div
                    class="inline-flex items-center overflow-hidden rounded-xl border border-gray-300 bg-white shadow-sm">
                    <span class="h-9 w-14 animate-pulse bg-gray-100"></span>
                    <span class="h-9 w-10 border-l border-gray-300 animate-pulse bg-gray-100"></span>
                    <span class="h-9 w-10 border-l border-gray-300 animate-pulse bg-gray-100"></span>
                    <span class="h-9 w-10 border-l border-gray-300 animate-pulse bg-gray-100"></span>
                    <span class="h-9 w-14 border-l border-gray-300 animate-pulse bg-gray-100"></span>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            @if($isDataTab)
                {{ $items->onEachSide(1)->withQueryString()->links('vendor.pagination.datasets') }}
            @elseif($isWalidataTab)
                {{ $items->onEachSide(1)->withQueryString()->links('vendor.pagination.aspects') }}
            @else
                {{ $items->onEachSide(1)->withQueryString()->links('vendor.pagination.publications') }}
            @endif
        </div>

        @else
        {{-- Empty state --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-10 text-center
                            dark:border-gray-700 dark:bg-gray-800 transition-colors duration-200">
            <div class="mx-auto h-16 w-16 rounded-full bg-gray-50 grid place-content-center dark:bg-gray-700/60">
                <svg class="h-8 w-8 text-gray-300 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
                    aria-hidden="true">
                    <path
                        d="M10 3a7 7 0 1 0 4.9 12.04l4.53 4.53a1 1 0 0 0 1.42-1.42l-4.53-4.53A7 7 0 0 0 10 3Zm-5 7a5 5 0 1 1 10 0 5 5 0 0 1-10 0Z" />
                </svg>
            </div>
            <p class="mt-4 text-gray-700 dark:text-gray-200 font-medium">
                @if($isDataTab) Tidak ada data pada aspek ini.
                @elseif($isWalidataTab) Tidak ada indikator walidata pada aspek ini.
                @else Tidak ada publikasi pada aspek ini.
                @endif
            </p>
            @if($q !== '')
            <button wire:click="clearSearch" class="mt-3 inline-flex items-center rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-sm px-4 py-2 shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2
                                   dark:focus:ring-offset-gray-900 transition-colors duration-200">
                Hapus Pencarian
            </button>
            @endif
        </div>
        @endif
    </div>
</div>
</div>
