@props([
// Livewire binding
'model' => 'q',
'debounce' => '50ms', // Sangat responsif namun tetap mencegah cursor jump

// UI
'placeholder' => 'Cari indikator walidata...',
'size' => 'md', // sm | md | lg
'fullWidth' => true, // true -> dibatasi max-width & center
'maxWidth' => 'max-w-3xl', // dipakai saat fullWidth=true
'outerClass' => 'px-4 sm:px-6 mb-4 sm:mb-6', // wrapper luar (padding halaman)
'id' => null,
'showClear' => true,
])

@php
$inputId = $id ?: ('search-'.\Illuminate\Support\Str::random(6));

$sizeMap = [
'sm' => 'pl-9 pr-3 py-2 text-sm',
'md' => 'pl-11 pr-4 py-2.5 text-[15px] sm:text-base',
'lg' => 'pl-12 pr-5 py-3 text-base',
];
$inputSize = $sizeMap[$size] ?? $sizeMap['md'];

$container = $fullWidth
? "relative mx-auto w-full {$maxWidth}"
: "relative w-full";
@endphp

<div class="{{ $outerClass }}">
    <div class="{{ $container }}">
        <label for="{{ $inputId }}" class="sr-only">Pencarian</label>

        {{-- Icon --}}
        <svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 dark:text-gray-400"
            viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M13.78 12.72a6 6 0 1 0-1.06 1.06l3.76 3.76a.75.75 0 1 0 1.06-1.06l-3.76-3.76ZM8.5 13a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9Z"
                clip-rule="evenodd" />
        </svg>

        {{-- Input --}}
        <input id="{{ $inputId }}" type="search" placeholder="{{ $placeholder }}"
            class="w-full rounded-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 {{ $inputSize }} text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 shadow-sm dark:shadow-gray-950/20 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900 transition-colors duration-200"
            wire:model.lazy="{{ $model }}" wire:key="search-{{ $inputId }}" {{ $attributes }} />

        {{-- Clear button (opsional) --}}
        @if($showClear)
        <button type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 dark:text-gray-300 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 dark:focus:ring-teal-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
            aria-label="Hapus pencarian" wire:click="$set('{{ $model }}','')">
            &times;
        </button>
        @endif
    </div>
</div>