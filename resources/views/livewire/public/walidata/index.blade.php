{{-- resources/views/livewire/public/walidata/index.blade.php --}}
<div>
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    <section x-data="{ filterOpen:false }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-14">
        {{-- Grid 12 kolom: panel kiri & konten kanan --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">

            {{-- PANEL FILTER (desktop saja) --}}
            <x-public.walidata.filter-panel class="hidden md:block md:col-span-3" :show-search="false"
                :sort-options="$sortOptions" :aspek-options="$aspekOptions" :instansi-options="$instansiOptions"
                :bidang-options="$bidangOptions" :indikator-options="$indikatorOptions"
                sort-model="sort" aspek-model="selectedAspek" instansi-model="selectedInstansi"
                bidang-model="selectedBidang" indikator-model="selectedIndikator"
                q-model="q"
                :selected="[
                    'q' => $q,
                    'sort' => $sort,
                    'selectedAspek' => $selectedAspek,
                    'selectedInstansi' => $selectedInstansi,
                    'selectedBidang' => $selectedBidang,
                    'selectedIndikator' => $selectedIndikator,
                ]" on-reset="clearFilters" panel-id="walidata-panel-desktop" />

            {{-- KOLOM KANAN --}}
            <div class="md:col-span-9 space-y-4 sm:space-y-6">
                {{-- Search bar (atas list) --}}
                <x-public.walidata.search-bar model="q" :full-width="false" outer-class="mb-3 sm:mb-4" />

                {{-- Tombol buka filter (mobile, setelah search, full-width) --}}
                <div class="md:hidden">
                    <button @click="filterOpen = !filterOpen" type="button"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-teal-500 transition-colors">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
                        </svg>
                        Filter & Urutkan
                    </button>
                </div>

                {{-- Panel filter mobile (collapsed) --}}
                <div x-show="filterOpen" x-cloak x-transition.duration.300ms 
                    class="md:hidden">
                    <x-public.walidata.filter-panel :show-search="true" :sort-options="$sortOptions"
                        :aspek-options="$aspekOptions" :instansi-options="$instansiOptions"
                        :bidang-options="$bidangOptions" :indikator-options="$indikatorOptions"
                        sort-model="sort" aspek-model="selectedAspek" instansi-model="selectedInstansi"
                        bidang-model="selectedBidang" indikator-model="selectedIndikator"
                        q-model="q"
                        :selected="[
                            'q' => $q,
                            'sort' => $sort,
                            'selectedAspek' => $selectedAspek,
                            'selectedInstansi' => $selectedInstansi,
                            'selectedBidang' => $selectedBidang,
                            'selectedIndikator' => $selectedIndikator,
                        ]" on-reset="clearFilters" panel-id="walidata-panel-mobile" />
                </div>

                {{-- LIST WALIDATA --}}
                <div id="walidata-list" class="space-y-4 sm:space-y-5">
                    @forelse ($walidata as $w)
                        @php
                            // Generate badge berdasarkan aspek dan bidang dengan warna dari database
                            $badges = [];
                            if ($w && isset($w->aspek) && $w->aspek) {
                                // Gunakan warna dari database atau fallback ke warna default
                                $aspekWarna = $w->aspek->warna ?? '#0d9488'; // Default teal-600
                                
                                $badges[] = [
                                    'label' => $w->aspek->nama ?? 'Unclassified',
                                    'variant' => 'custom',
                                    'color' => $aspekWarna,
                                ];
                            }
                            
                            
                            // URL untuk detail walidata (bisa disesuaikan jika ada halaman detail)
                            $detailUrl = route('public.walidata.show', $w->id ?? '#');
                        @endphp
                        
                        <x-public.walidata.walidata-card 
                            :title="$w->indikator?->uraian_indikator ?? 'Indikator Tanpa Nama'" 
                            :url="$detailUrl"
                            :thumb="$w->aspek?->foto_url ?? null" 
                            :badges="$badges" 
                            :instansi-label="$w->skpd?->singkatan ?? $w->skpd?->nama ?? 'instansi Belum Ditetapkan'"
                            :date-label="$w->created_at ? $w->created_at->translatedFormat('d F Y') : null"
                            :data-value="$w->data ?? '-'"
                            :data-unit="$w->satuan ?? ''"
                            :year="$w->tahun ?? null"
                            :views="$w->views ?? 0 "
                            :desc="$w->indikator?->uraian_indikator ?? 'Tidak ada deskripsi'" />
                    @empty
                    {{-- Empty state untuk ketika tidak ada data --}}
                    <div class="text-center py-12">
                        <div class="mx-auto h-24 w-24 text-gray-400">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Tidak ada indikator walidata ditemukan</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Coba ubah filter atau kata kunci pencarian Anda.</p>
                    </div>
                    @endforelse
                </div>

                {{-- PAGINATION --}}
                <div class="mt-8">
                    {{ $walidata->links() }}
                </div>
            </div>
        </div>
    </section>
</div>