{{-- resources/views/components/public/agencies/sidebar-card.blade.php --}}
@props([
'name' => 'Nama Instansi',
'logo' => null,
'datasetsCount' => 0,
'walidataCount' => 0,
'publicationsCount' => 0,
'viewsCount' => 0,
'address' => null,
])

@php
    $logoUrl = resolve_media_url($logo, ['temporary' => false, 'fallback' => asset('logo-hsu.png')]);
@endphp

<div {{ $attributes->class([
    'rounded-3xl border border-gray-200 bg-white shadow-sm p-6 sm:p-7 text-center',
    'dark:!border-gray-700 dark:!bg-gray-800',
    'transition-colors duration-200',
    ]) }}
    >
    {{-- Logo / Emblem --}}
    <div class="mx-auto w-28 h-28 sm:w-32 sm:h-32 md:w-36 md:h-36 rounded-2xl overflow-hidden
               ring-1 ring-gray-200 bg-gray-50 flex items-center justify-center p-3
               dark:!ring-gray-700 dark:!bg-gray-700/50">
       @if($logoUrl)
       <img src="{{ $logoUrl }}" alt="{{ $name }}" class="max-w-full max-h-full w-auto h-auto object-contain"
           onerror="this.onerror=null;this.src='{{ asset('logo-hsu.png') }}';">
        @else
        {{-- Placeholder emblem --}}
        <svg class="w-16 h-16 text-gray-300 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
            aria-hidden="true">
            <path d="M12 2 3 7v10l9 5 9-5V7l-9-5zm0 2.2 6.8 3.8v7.9L12 19.8 5.2 15.9V8z" />
            <circle cx="12" cy="12" r="3.2" class="opacity-60" />
        </svg>
        @endif
    </div>

    {{-- Judul --}}
    <h1 class="mt-5 text-xl sm:text-2xl font-semibold text-gray-900 dark:text-gray-100 leading-tight break-words">
        {{ $name }}
    </h1>

    {{-- Metrik: Dataset | Walidata | Publikasi --}}
    <div class="mt-5 grid grid-cols-3 gap-2 sm:gap-4">
        <div>
            <div class="text-lg sm:text-2xl font-extrabold text-teal-700 dark:!text-teal-400 leading-none">
                {{ number_format($datasetsCount) }}
            </div>
            <div class="text-xs sm:text-sm text-teal-700 dark:!text-teal-400">Dataset</div>
        </div>
        <div>
            <div class="text-lg sm:text-2xl font-extrabold text-teal-700 dark:!text-teal-400 leading-none">
                {{ number_format($walidataCount) }}
            </div>
            <div class="text-xs sm:text-sm text-teal-700 dark:!text-teal-400">Indikator Walidata</div>
        </div>
        <div>
            <div class="text-lg sm:text-2xl font-extrabold text-teal-700 dark:!text-teal-400 leading-none">
                {{ number_format($publicationsCount) }}
            </div>
            <div class="text-xs sm:text-sm text-teal-700 dark:!text-teal-400">Publikasi</div>
        </div>
    </div>

    {{-- Views Count --}}
    <div class="mt-4 flex items-center justify-center gap-2">
        <svg class="w-4 h-4 text-gray-500 dark:!text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <span class="text-sm text-gray-600 dark:!text-gray-300">{{ number_format($viewsCount) }} views</span>
    </div>

    {{-- Divider aksen teal --}}
    <div class="mt-4 h-1 w-11/12 mx-auto rounded-full bg-teal-600/80"></div>

    {{-- Alamat (multiline) --}}
    @if(filled($address))
    <p class="mt-6 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
        {{ $address }}
    </p>
    @endif
</div>
