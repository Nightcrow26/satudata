@props([
// Opsi
'sortOptions' => ['recent' => 'Terbaru', 'oldest' => 'Terlama', 'popular' => 'Terpopuler', 'name' => 'Nama A-Z'],
'jenisOptions' => [], // ['umum' => 'Umum', ...]
'instansiOptions' => [], // ['diskominfo' => 'Dinas Kominfo', ...]
'bidangOptions' => [], // ['sekretariat_daerah' => 'Sekretariat Daerah', ...]
'showSearch' => false,
'searchPlaceholder' => 'Cari publikasi yang anda butuhkan',

// Livewire binding
'qModel' => 'q',
'sortModel' => 'sort',
'jenisModel' => 'jenis',
'instansiModel' => 'instansi',
'bidangModel' => 'bidang',

// SSR selected
'selected' => [
'q' => '',
'sort' => null,
'jenis' => [],
'instansi' => [],
'bidang' => [],
],

// Aksi clear
'onReset' => 'resetFilters',

// Berapa item awal sebelum “Selengkapnya”
'previewCount' => 5,
// Optional unique id for the panel instance to avoid radio name collisions
'panelId' => null,
])

<aside {{ $attributes->merge([
    'class' => 'rounded-2xl border border-gray-200 dark:!border-gray-700 bg-white dark:!bg-gray-800 shadow-sm
    dark:shadow-gray-900/20 p-4 sm:p-5 space-y-5 md:sticky md:top-24 transition-colors duration-200'
    ]) }}>
    {{-- Search (opsional) --}}
    @if($showSearch)
    <div>
        <label for="panel-search" class="sr-only">Pencarian</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 dark:text-gray-400"
                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                    d="M13.78 12.72a6 6 0 1 0-1.06 1.06l3.76 3.76a.75.75 0 1 0 1.06-1.06l-3.76-3.76ZM8.5 13a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9Z"
                    clip-rule="evenodd" />
            </svg>
            <input id="panel-search" type="search" placeholder="{{ $searchPlaceholder }}"
                class="w-full rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 pl-10 pr-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors duration-200"
                wire:model.debounce.300ms="{{ $qModel }}"
                value="{{ is_string(data_get($selected, 'q')) ? data_get($selected, 'q') : '' }}" />
        </div>
    </div>
    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>
    @endif

    {{-- Urutkan (radio) --}}
    <fieldset>
        <legend class="text-sm font-semibold text-gray-800 dark:text-white">Urutkan</legend>
        <div class="mt-2 space-y-2">
            @foreach($sortOptions as $val => $label)
            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200 cursor-pointer select-none">
                <span class="flex-none pt-0.5">
                    <input type="radio"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-teal-600 focus:ring-teal-600 dark:focus:ring-teal-400"
                        name="{{ $panelId ?? $sortModel }}" value="{{ $val }}" wire:model.live="{{ $sortModel }}"
                        @checked(data_get($selected, 'sort' )===$val)>
                </span>
                <span class="flex-1 leading-snug">{{ $label }}</span>
            </label>
            @endforeach
        </div>
    </fieldset>

    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

    {{-- Jenis Informasi (checkbox, dengan “Selengkapnya”) --}}
    <fieldset>
        <legend class="flex items-center justify-between text-sm font-semibold text-gray-800 dark:text-white">
            <span>Aspek</span>
            <a href="{{ route('public.aspects.index') }}"
                class="ml-2 text-xs font-medium text-teal-600 hover:text-teal-700 dark:!text-teal-400 dark:!hover:text-teal-300 focus:outline-none focus:underline">
                Lihat Halaman
            </a>
        </legend>
        @php
        $jenisAll = collect($jenisOptions);
        $jenisHead = $jenisAll->slice(0, $previewCount);
        $jenisTail = $jenisAll->slice($previewCount);
        $jenisSelected = collect(data_get($selected, 'jenis', []))->filter()->values()->all();
        @endphp

        <div class="mt-2 space-y-2">
            @foreach($jenisHead as $val => $label)
            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200 cursor-pointer select-none">
                <span class="flex-none pt-0.5">
                    <input type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-teal-600 focus:ring-teal-600 dark:focus:ring-teal-400"
                        value="{{ $val }}" wire:model.live="{{ $jenisModel }}" @checked(in_array($val, $jenisSelected,
                        true))>
                </span>
                <span class="flex-1 leading-snug">{{ $label }}</span>
            </label>
            @endforeach
        </div>

        @if($jenisTail->isNotEmpty())
        <details class="mt-2 group open:pb-1">
            <summary
                class="list-none inline-flex items-center gap-1 text-[13px] font-medium text-teal-700 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 cursor-pointer transition-colors duration-200">
                <span class="select-none">Selengkapnya</span>
                <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path
                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                </svg>
            </summary>
            <div class="mt-2 max-h-48 overflow-y-auto pr-1 space-y-2">
                @foreach($jenisTail as $val => $label)
                <label
                    class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200 cursor-pointer select-none">
                    <span class="flex-none pt-0.5">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-teal-600 focus:ring-teal-600 dark:focus:ring-teal-400"
                            value="{{ $val }}" wire:model.live="{{ $jenisModel }}" @checked(in_array($val,
                            $jenisSelected, true))>
                    </span>
                    <span class="flex-1 leading-snug">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </details>
        @endif
    </fieldset>

    <div class="border-t border-gray-200 dark:border-gray-700 my-3"></div>

    {{-- Instansi (checkbox) --}}
    <fieldset>
        <legend class="text-sm font-semibold text-gray-800 dark:text-white">Produsen Data</legend>
        @php
        $instAll = collect($instansiOptions);
        $instHead = $instAll->slice(0, $previewCount);
        $instTail = $instAll->slice($previewCount);
        $instSelected = collect(data_get($selected, 'instansi', []))->filter()->values()->all();
        @endphp

        <div class="mt-2 space-y-2">
            @foreach($instHead as $val => $label)
            <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200 cursor-pointer select-none">
                <span class="flex-none pt-0.5">
                    <input type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 dark:!border-gray-600 bg-white dark:!bg-gray-800 text-teal-600 focus:ring-teal-600 dark:focus:ring-teal-400"
                        value="{{ $val }}" wire:model.live="{{ $instansiModel }}" @checked(in_array($val, $instSelected,
                        true))>
                </span>
                <span class="flex-1 leading-snug">{{ $label }}</span>
            </label>
            @endforeach
        </div>

        @if($instTail->isNotEmpty())
        <details class="mt-2 group open:pb-1">
            <summary
                class="list-none inline-flex items-center gap-1 text-[13px] font-medium text-teal-700 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 cursor-pointer transition-colors duration-200">
                <span class="select-none">Selengkapnya</span>
                <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path
                        d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" />
                </svg>
            </summary>
            <div class="mt-2 max-h-48 overflow-y-auto pr-1 space-y-2">
                @foreach($instTail as $val => $label)
                <label
                    class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200 cursor-pointer select-none">
                    <span class="flex-none pt-0.5">
                        <input type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-teal-600 focus:ring-teal-600 dark:focus:ring-teal-400"
                            value="{{ $val }}" wire:model.live="{{ $instansiModel }}" @checked(in_array($val,
                            $instSelected, true))>
                    </span>
                    <span class="flex-1 leading-snug">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </details>
        @endif
    </fieldset>

    <button type="button" wire:click="{{ $onReset }}"
        class="hidden md:block w-full rounded-full px-5 py-3 font-semibold shadow-sm bg-teal-500 dark:bg-teal-600 text-white hover:bg-teal-600 dark:hover:bg-teal-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors duration-200">
        Hapus Filter
    </button>
</aside>
