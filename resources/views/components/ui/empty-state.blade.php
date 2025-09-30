@props([
'title' => 'Tidak ada data',
'subtitle' => null,
'icon' => 'search', // 'search' | 'inbox' | null
'ctaText' => null,
'ctaHref' => null,
'compact' => false, // true = padding lebih kecil
])

@php
$pad = $compact ? 'p-5 sm:p-6' : 'p-6 sm:p-8';
@endphp

<div {{ $attributes->merge([
    'class' => "w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm text-center {$pad}"
    ]) }}>
    @if($icon)
    <div class="mx-auto h-12 w-12 rounded-full bg-teal-50 dark:bg-teal-900/20 text-teal-600 dark:text-teal-400 grid place-content-center">
        @if($icon === 'inbox')
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path
                d="M3 6.75A2.75 2.75 0 0 1 5.75 4h12.5A2.75 2.75 0 0 1 21 6.75V20a1 1 0 0 1-1.447.894L12 17.118 4.447 20.894A1 1 0 0 1 3 20V6.75Z" />
        </svg>
        @else
        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd"
                d="M13.78 12.72a6 6 0 1 0-1.06 1.06l3.76 3.76a.75.75 0 1 0 1.06-1.06l-3.76-3.76ZM8.5 13a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9Z"
                clip-rule="evenodd" />
        </svg>
        @endif
    </div>
    @endif

    <h3 class="mt-3 text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
        {{ $title }}
    </h3>

    @if($subtitle)
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $subtitle }}
    </p>
    @endif

    @if(trim($slot))
    <div class="mt-4">
        {{ $slot }}
    </div>
    @elseif($ctaText && $ctaHref)
    <div class="mt-4">
        <a href="{{ $ctaHref }}"
            class="inline-flex items-center rounded-full bg-teal-500 hover:bg-teal-600 dark:bg-teal-600 dark:hover:bg-teal-500 px-4 py-2.5 font-semibold text-white shadow-sm transition-colors">
            {{ $ctaText }}
        </a>
    </div>
    @endif
</div>
