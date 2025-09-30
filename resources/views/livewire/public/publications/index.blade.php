{{-- resources/views/livewire/public/publications/index.blade.php --}}
<div>
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>

    <section x-data="{ filterOpen:false }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-14">
        {{-- Grid 12 kolom: panel kiri & konten kanan --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">

            {{-- PANEL FILTER (desktop) --}}
            <x-public.publications.filter-panel class="hidden md:block md:col-span-3" :show-search="false"
                :sort-options="['recent'=>'Terbaru','oldest'=>'Terlama','popular'=>'Terpopuler','name'=>'Nama A-Z']" :jenis-options="$jenisOptions"
                :instansi-options="$instansiOptions" :bidang-options="$bidangOptions" sort-model="sort"
                jenis-model="jenis" instansi-model="instansi" bidang-model="bidang" q-model="q" :selected="[
                    'q' => $q,
                    'sort' => $sort,
                    'jenis' => $jenis,
                    'instansi' => $instansi,
                    'bidang' => $bidang,
                ]" on-reset="resetFilters" />

            {{-- KOLOM KANAN --}}
            <div class="md:col-span-9 space-y-4 sm:space-y-6">
                {{-- Search bar (atas list) --}}
                <x-public.publications.search-bar model="q" :full-width="false" outer-class="mb-3 sm:mb-4" />

                {{-- Tombol buka filter (mobile, setelah search, full-width) --}}
                <div class="md:hidden">
                    <button type="button"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-full bg-teal-500 dark:bg-teal-600 px-4 py-2.5 font-semibold text-white shadow-sm hover:bg-teal-600 dark:hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                        @click="filterOpen = true" aria-controls="mobileFilter" aria-expanded="false">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path
                                d="M3.75 5.25A.75.75 0 0 1 4.5 4.5h15a.75.75 0 0 1 .6 1.2l-5.4 7.2v5.55a.75.75 0 0 1-1.05.69l-3-1.2a.75.75 0 0 1-.45-.69v-4.35l-5.4-7.2a.75.75 0 0 1 .6-1.2Z" />
                        </svg>
                        Tampilkan Filter
                    </button>
                </div>

                {{-- LIST PUBLIKASI --}}
                <div class="space-y-4 sm:space-y-5">
                    @forelse ($publications as $p)
                    @php
                    // Buat badge dari aspek publikasi dengan warna dari database
                    $badges = [];
                    if ($p && isset($p->aspek) && $p->aspek) {
                        // Gunakan warna dari database atau fallback ke warna default
                        $aspekWarna = $p->aspek->warna ?? '#0d9488'; // Default teal-600
                        
                        $badges = [[
                            'label' => $p->aspek->nama,
                            'variant' => 'custom',
                            'color' => $aspekWarna,
                        ]];
                    }
                    @endphp

                    <x-public.publications.publication-card 
                        :publication="$p" 
                        :title="$p->nama ?? 'Tanpa Judul'" 
                        :url="route('public.publications.download', $p->id)"
                        :thumb="$p->foto_url" 
                        :badges="$badges"
                        :instansi-label="$p->skpd?->singkatan ?? $p->skpd?->nama"
                        :date-label="$p->created_at ? $p->created_at->translatedFormat('d F Y') : null"
                        :downloads="$p->download"
                        :desc="\Illuminate\Support\Str::of($p->deskripsi ?? '')->stripTags()->limit(220)" />
                    @empty
                    {{-- Empty state untuk ketika tidak ada publikasi --}}
                    <div class="text-center py-12">
                        <div class="mx-auto h-24 w-24 text-gray-400">
                            <svg class="h-full w-full" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 3v2m0 0V3h6v2H9zm0 0h6v4H9V5z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">Tidak ada publikasi ditemukan</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">Coba ubah filter atau kata kunci pencarian Anda.</p>
                    </div>
                    @endforelse
                </div>

                {{-- PAGINATION --}}
                {{-- Placeholder saat loading (paritas dengan halaman Data) --}}
                <div class="mt-6" wire:loading>
                    <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                        <div class="h-5 w-28 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                        <div
                            class="inline-flex items-center overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm dark:shadow-gray-900/20">
                            <span class="h-9 w-14 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-10 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-10 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-10 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-14 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                        </div>
                    </div>
                </div>

                {{-- Pagination real (segmented pill) --}}
                <div class="mt-6" wire:loading.remove>
                    {{ $publications->onEachSide(1)->links('vendor.pagination.publications') }}
                </div>

                {{-- Fallback saat hasil kosong (links() tidak merender apa pun) --}}
                @unless($publications->count())
                <div class="mt-6">
                    <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                        <div class="h-5 w-28 rounded-full bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                        <div
                            class="inline-flex items-center overflow-hidden rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm dark:shadow-gray-900/20">
                            <span class="h-9 w-14 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-10 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-10 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-10 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                            <span
                                class="h-9 w-14 border-l border-gray-300 dark:border-gray-600 animate-pulse bg-gray-100 dark:bg-gray-700"></span>
                        </div>
                    </div>
                </div>
                @endunless
            </div>
        </div>

        {{-- MODAL FILTER (mobile) --}}
        <div x-cloak x-show="filterOpen" x-transition.opacity
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/40" @click="filterOpen=false" aria-hidden="true"></div>

            {{-- Sheet content --}}
            <div id="mobileFilter" x-transition
                class="relative w-full sm:max-w-lg sm:rounded-2xl sm:my-8 bg-white dark:bg-gray-800 rounded-t-2xl shadow-xl overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Filter</h3>
                    <button type="button"
                        class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200"
                        @click="filterOpen=false" aria-label="Tutup">
                        <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                d="M6.28 6.22a.75.75 0 0 1 1.06 0L10 8.88l2.66-2.66a.75.75 0 1 1 1.06 1.06L11.06 10l2.66 2.66a.75.75 0 0 1-1.06 1.06L10 11.06l-2.66 2.66a.75.75 0 0 1-1.06-1.06L8.94 10 6.28 7.34a.75.75 0 0 1 0-1.12Z" />
                        </svg>
                    </button>
                </div>

                {{-- Body: komponen filter-panel yang sama --}}
                <div class="max-h-[75vh] overflow-y-auto p-4">
                    <x-public.publications.filter-panel :show-search="false"
                        :sort-options="['recent'=>'Terbaru','oldest'=>'Terlama','popular'=>'Terpopuler','name'=>'Nama A-Z']" :jenis-options="$jenisOptions"
                        :instansi-options="$instansiOptions" :bidang-options="$bidangOptions" sort-model="sort"
                        jenis-model="jenis" instansi-model="instansi" bidang-model="bidang" q-model="q" :selected="[
                            'q' => $q,
                            'sort' => $sort,
                            'jenis' => $jenis,
                            'instansi' => $instansi,
                            'bidang' => $bidang,
                        ]" on-reset="resetFilters" />
                </div>

                {{-- Footer: Hapus & Terapkan (mobile only) --}}
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                    <div class="flex gap-3">
                        {{-- Tombol Hapus Filter --}}
                        <button type="button"
                            class="flex-1 rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2.5 font-semibold text-gray-700 dark:text-white shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                            wire:click="resetFilters">
                            Hapus Filter
                        </button>
                        {{-- Tombol Terapkan --}}
                        <button type="button"
                            class="flex-1 rounded-full bg-teal-500 dark:bg-teal-600 px-4 py-2.5 font-semibold text-white shadow-sm hover:bg-teal-600 dark:hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors duration-200"
                            @click="filterOpen=false">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
