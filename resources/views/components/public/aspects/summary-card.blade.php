{{-- resources/views/components/public/aspects/summary-card.blade.php --}}
@props(['icon'=>null,'name'=>'','countData'=>0,'countWalidata'=>0,'countPublikasi'=>0])

<aside class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5 md:sticky md:top-24
              dark:!border-gray-700 dark:!bg-gray-800 transition-colors duration-200">
    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-xl overflow-hidden ring-1 ring-gray-200 bg-gray-50 mx-auto
                dark:!ring-gray-700 dark:!bg-gray-900">
        @if($icon)
    <img src="{{ resolve_media_url($icon, ['temporary'=>false]) }}" alt="Ikon {{ $name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='{{ asset('kesehatan.png') }}'">
        @else
        <div class="h-full w-full grid place-content-center text-gray-300 dark:!text-gray-500">
            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 15h-2v-2h2Zm0-4h-2V7h2Z" />
            </svg>
        </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="mt-3 sm:mt-4 text-center space-y-1">
        <h3 class="text-sm sm:text-base font-semibold text-gray-900 group-hover:text-gray-950
                   dark:!text-gray-100 dark:group-hover:!text-white">
            {{ $name }}
        </h3>
        <div class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5
                    dark:!border-gray-700 dark:!bg-gray-600">
            <span class="text-[11px] sm:text-xs text-gray-600 dark:!text-gray-300">
                {{ $countData }} Dataset
            </span>
        </div>
        <div class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5
                    dark:border-gray-700 dark:bg-gray-600">
            <span class="text-[11px] sm:text-xs text-gray-600 dark:text-gray-300">
                {{ $countWalidata }} Indikator
            </span>
        </div>
        <div class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5
                    dark:border-gray-700 dark:bg-gray-600">
            <span class="text-[11px] sm:text-xs text-gray-600 dark:text-gray-300">
                {{ $countPublikasi }} Publikasi
            </span>
        </div>
    </div>
</aside>
