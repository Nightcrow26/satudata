{{-- resources/views/components/public/agencies/card.blade.php --}}
@props([
'name' => 'Nama Instansi',
'logo' => null,
'dataset' => null,
'pubs' => null,
'walidata' => null,
'updated' => null,
'views' => null,
])

@php
    // Compute a safe logo URL. Accept full http/https/data URLs or try Storage URLs (default disk, then s3),
    // finally fall back to public storage asset.
    $logoUrl = null;
    if ($logo) {
        if (\Illuminate\Support\Str::startsWith($logo, ['http://', 'https://', 'data:', '/'])) {
            $logoUrl = $logo;
        } else {
            // 1) try default Storage::url()
            try {
                $candidate = \Illuminate\Support\Facades\Storage::url($logo);
            } catch (\Throwable $e) {
                $candidate = null;
            }

            // if candidate is an absolute URL, use it
            if ($candidate && \Illuminate\Support\Str::startsWith($candidate, ['http://', 'https://'])) {
                $logoUrl = $candidate;
            } else {
                // 2) try s3 disk explicitly if configured
                try {
                    if (array_key_exists('s3', config('filesystems.disks', []))) {
                        $s3candidate = \Illuminate\Support\Facades\Storage::disk('s3')->url($logo);
                    } else {
                        $s3candidate = null;
                    }
                } catch (\Throwable $e) {
                    $s3candidate = null;
                }

                if ($s3candidate && \Illuminate\Support\Str::startsWith($s3candidate, ['http://', 'https://'])) {
                    $logoUrl = $s3candidate;
                }

                // 3) final fallback: public storage path
                if (!$logoUrl) {
                    $logoUrl = asset('storage/' . ltrim($logo, '/'));
                }
            }
        }
    }
@endphp

<div class="p-4 sm:p-5 lg:p-6 flex flex-col sm:flex-row gap-3 sm:gap-4 lg:gap-5 transition-colors duration-200">
    {{-- Logo --}}
    <div class="w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 mx-auto sm:mx-0 rounded-xl overflow-hidden grid place-items-center shrink-0
               ring-1 ring-gray-200 bg-gray-50
               dark:ring-gray-700 dark:bg-gray-700/50">
       @if($logoUrl)
       <img src="{{ $logoUrl }}" alt="{{ $name }}" class="w-16 sm:w-20 h-full object-contain"
           onerror="this.onerror=null;this.src='{{ url('logo-hsu.png') }}';">
        @else
        {{-- fallback placeholder svg --}}
        <svg class="w-12 sm:w-16 h-12 sm:h-16 text-gray-300 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
            aria-hidden="true">
            <path d="M12 2 3 7v10l9 5 9-5V7l-9-5zm0 2.2 6.8 3.8v7.9L12 19.8 5.2 15.9V8z" />
            <circle cx="12" cy="12" r="3.2" class="opacity-60" />
        </svg>
        @endif
    </div>

    {{-- Isi --}}
    <div class="min-w-0 flex-1 text-center sm:text-left">
        <h3 class="text-sm sm:text-base lg:text-lg font-semibold leading-tight line-clamp-2
                   text-gray-900 dark:text-gray-100 mb-2 sm:mb-3">
            {{ $name }}
        </h3>

        {{-- Meta --}}
        <div class="grid grid-cols-[5.5rem_1rem_1fr] sm:grid-cols-[6rem_1rem_1fr] lg:grid-cols-[6rem_1rem_1fr] 
                    gap-x-1 sm:gap-x-2 gap-y-1 text-xs sm:text-sm">
            <div class="text-gray-600 dark:text-gray-400">Dataset</div>
            <div class="text-gray-600 dark:text-gray-500">:</div>
            <div class="text-gray-800 dark:text-gray-200 font-medium">{{ filled($dataset) ? $dataset : '—' }}</div>

            <div class="text-gray-600 dark:text-gray-400">Publikasi</div>
            <div class="text-gray-600 dark:text-gray-500">:</div>
            <div class="text-gray-800 dark:text-gray-200 font-medium">{{ filled($pubs) ? $pubs : '—' }}</div>

            <div class="text-gray-600 dark:text-gray-400">Walidata</div>
            <div class="text-gray-600 dark:text-gray-500">:</div>
            <div class="text-gray-800 dark:text-gray-200 font-medium">{{ filled($walidata) ? $walidata : '—' }}</div>

            <div class="text-gray-600 dark:text-gray-400 truncate">Update</div>
            <div class="text-gray-600 dark:text-gray-500">:</div>
            <div class="text-gray-800 dark:text-gray-300 text-xs">{{ filled($updated) ? $updated->translatedFormat('d-m-Y') : '—' }}</div>
        </div>
    </div>
</div>
