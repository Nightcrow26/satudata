@props([
'year' => now()->year,
'dataItems' => [], // [['title'=>'...', 'date'=>'Mei 2025', 'url'=>'#'], ...]
'pubItems' => [], // [['title'=>'...', 'date'=>'Mei 2025', 'url'=>'#'], ...]
'moreDataUrl' => '#',
'morePubUrl' => '#',
])

<section aria-labelledby="release-heading"
    class="bg-gray-50 dark:!bg-gray-900 pt-12 pb-16 transition-colors duration-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Heading --}}
        <h2 id="release-heading" class="text-center font-extrabold tracking-tight text-2xl sm:text-3xl
                   text-teal-700 dark:text-teal-300 transition-colors duration-200">
            Jadwal Rilis Data dan Publikasi
        </h2>
        <div
            class="mx-auto mt-2 h-1 w-24 rounded-full bg-teal-500/70 dark:bg-teal-500/60 transition-colors duration-200">
        </div>

        {{-- Grid 2 kolom (stack di mobile) --}}
        <div class="mt-6 grid gap-6 md:gap-8 md:grid-cols-2">
            {{-- Kartu: DATA --}}
            <article class="rounded-2xl shadow-sm p-5 sm:p-6
                            bg-white dark:!bg-gray-800
                            ring-1 ring-gray-200 dark:ring-gray-800
                            transition-colors duration-200">
                <h3 class="text-xl sm:text-2xl font-extrabold mb-4
                           text-teal-700 dark:text-teal-300 transition-colors duration-200">
                    Data {{ $year }}
                </h3>

                {{-- Header tabel (md ke atas) --}}
                <div class="hidden md:grid grid-cols-[1fr_auto] text-sm font-semibold px-2 pb-2
                            text-gray-800 dark:text-gray-200 transition-colors duration-200">
                    <div>Data</div>
                    <div class="text-right">Jadwal Rilis</div>
                </div>
                <div class="hidden md:block h-px bg-gray-200/80 dark:bg-gray-800 mb-1 transition-colors duration-200">
                </div>

                {{-- Rows responsif --}}
                <ul class="divide-y divide-gray-200/80 dark:divide-gray-800 transition-colors duration-200">
                    @forelse($dataItems as $row)
                    <li class="py-2.5">
                        <div class="grid md:grid-cols-[1fr_auto] grid-cols-1 gap-1 items-center px-2">
                            {{-- Judul (link) --}}
                            <a href="{{ route('public.data.show', $row['id']) }}" class="text-sm truncate
                                          text-gray-800 dark:text-white
                                          hover:text-teal-700 dark:hover:text-teal-300
                                          transition-colors duration-200" title="{{ $row['title'] ?? '' }}">
                                {{ $row['nama'] ?? '—' }}
                            </a>

                            {{-- Tanggal (md+) --}}
                            <div class="hidden md:block text-sm text-right
                                            text-gray-700 dark:text-gray-300 transition-colors duration-200">
                                {{ $row['created_at']->translatedFormat('M Y') ?? '—' }}
                            </div>

                            {{-- Tanggal (mobile) --}}
                            <div class="md:hidden text-xs
                                            text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                {{ $row['created_at']->translatedFormat('M Y') ?? '—' }}
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="py-4 text-center text-sm
                                   text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        Belum ada jadwal.
                    </li>
                    @endforelse
                </ul>
            </article>

            {{-- Kartu: PUBLIKASI --}}
            <article class="rounded-2xl shadow-sm p-5 sm:p-6
                            bg-white dark:!bg-gray-800
                            ring-1 ring-gray-200 dark:ring-gray-800
                            transition-colors duration-200">
                <h3 class="text-xl sm:text-2xl font-extrabold mb-4
                           text-teal-700 dark:text-teal-300 transition-colors duration-200">
                    Publikasi {{ $year }}
                </h3>

                {{-- Header tabel (md ke atas) --}}
                <div class="hidden md:grid grid-cols-[1fr_auto] text-sm font-semibold px-2 pb-2
                            text-gray-800 dark:text-gray-200 transition-colors duration-200">
                    <div>Publikasi</div>
                    <div class="text-right">Jadwal Rilis</div>
                </div>
                <div class="hidden md:block h-px bg-gray-200/80 dark:bg-gray-800 mb-1 transition-colors duration-200">
                </div>

                {{-- Rows responsif --}}
                <ul class="divide-y divide-gray-200/80 dark:divide-gray-800 transition-colors duration-200">
                    @forelse($pubItems as $row)
                    <li class="py-2.5">
                        <div class="grid md:grid-cols-[1fr_auto] grid-cols-1 gap-1 items-center px-2">
                            <a href="{{ route('public.publications.index') }}" class="text-sm truncate
                                          text-gray-900 dark:text-white
                                          hover:text-teal-700 dark:hover:text-teal-300
                                          transition-colors duration-200" title="{{ $row['title'] ?? '' }}">
                                {{ $row['nama'] ?? '—' }}
                            </a>

                            <div class="hidden md:block text-sm text-right
                                            text-gray-700 dark:text-gray-300 transition-colors duration-200">
                                {{ $row['created_at']->translatedFormat('M Y') ?? '—' }}
                            </div>

                            <div class="md:hidden text-xs
                                            text-gray-600 dark:text-gray-400 transition-colors duration-200">
                                {{ $row['created_at']->translatedFormat('M Y') ?? '—' }}
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="py-4 text-center text-sm
                                   text-gray-500 dark:text-gray-400 transition-colors duration-200">
                        Belum ada jadwal.
                    </li>
                    @endforelse
                </ul>
            </article>
        </div>
    </div>
</section>
