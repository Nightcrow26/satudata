{{-- resources/views/components/public/walidata/detail-sidepanel.blade.php --}}
@props([
/**
* walidata.agency.name : string
* walidata.agency.logo : url/path (opsional)
* walidata.meta : array of ['label' => '...', 'value' => '...']
*/
'walidata' => null,

/** Tambahan kelas dari luar (mis. md:sticky md:top-24 atau hidden md:block) */
'class' => '',
])

@php
$w = is_array($walidata) ? (object) $walidata : ($walidata ?? (object) []);

$agency = (object) ($w->agency ?? []);
$agencyName = $agency->name ?? 'Dinas Komunikasi, Informatika, dan Persandian';
$agencyLogo = $agency->logo ?? null;

// Placeholder meta 8 baris bila belum ada
$meta = collect($w->meta ?? [
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
['label' => 'Metadata', 'value' => 'Metadata'],
]);
@endphp

<div {{ $attributes->merge([
    'class' => "rounded-2xl border border-gray-200 bg-white shadow-sm p-5 {$class}
    dark:!border-gray-700 dark:!bg-gray-800 transition-colors duration-200"
    ]) }}>
    {{-- Logo --}}
    <div class="flex justify-center">
        @if($agencyLogo)
    <img src="{{ resolve_media_url($agencyLogo, ['temporary'=>false, 'fallback'=>asset('logo-hsu.png')]) }}" alt="Logo {{ $agencyName }}" class="h-28 w-auto object-contain" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('logo-hsu.png') }}'">
        @else
        {{-- Placeholder logo (shield) --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
            class="h-28 w-24 text-teal-600/40 dark:text-teal-400/40" fill="currentColor" aria-hidden="true">
            <path
                d="M12 2.25c3.728 2.22 6.9 2.22 10.5 0v9.141c0 5.25-3.75 8.803-10.5 10.359C5.25 20.194 1.5 16.641 1.5 11.391V2.25c3.6 2.22 6.772 2.22 10.5 0z" />
        </svg>
        @endif
    </div>

    {{-- Nama Instansi --}}
    <h3 class="mt-4 text-center text-base font-semibold text-gray-900 leading-snug dark:text-gray-100">
        {{ $agencyName }}
    </h3>

    {{-- Metadata list --}}
    <dl class="mt-6 space-y-2.5">
        @foreach($meta as $item)
        @php($label = $item['label'] ?? ($item->label ?? '—'))
        @php($value = $item['value'] ?? ($item->value ?? '—'))
        <div class="flex items-baseline text-sm">
            <dt class="shrink-0 text-gray-600 dark:text-gray-400">{{ $label }}</dt>
            <span class="mx-2 grow border-b border-dotted border-gray-300 dark:border-gray-600"></span>
            <dd class="shrink-0 font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</dd>
        </div>
        @endforeach
    </dl>
</div>
