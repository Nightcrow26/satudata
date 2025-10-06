@props([
'bgImage' => null,
'title' => 'Satu',
'accent' => 'Data',
'subtitle' => null,
])

<section class="relative isolate">
    @if($bgImage)
    {{-- Gambar latar + overlay gradasi yang sedikit lebih kuat di dark mode --}}
    <img src="{{ resolve_media_url($bgImage) }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('kesehatan.png') }}'" />
    <div class="absolute inset-0 -z-10 bg-gradient-to-r
                   from-black/80 via-transparent to-black/80
                   dark:from-black/70 dark:via-black/20 dark:to-black/70
                   transition-colors duration-200">
    </div>
    @else
    {{-- Fallback gradien brand di light, nuansa lebih gelap di dark --}}
    <div class="absolute inset-0 -z-10 bg-gradient-to-b
                   from-teal-800 to-teal-600
                   dark:from-teal-950 dark:to-teal-800
                   transition-colors duration-200">
    </div>
    @endif

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-24 lg:py-32 text-center">
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white">
            {{ $title }}
            <span class="text-teal-300 dark:text-teal-400 transition-colors duration-200">{{ $accent }}</span><br
                class="hidden sm:block" />
            Hulu Sungai Utara
        </h1>

        @if($subtitle)
        <p class="mt-6 text-white/90 text-lg sm:text-xl max-w-3xl mx-auto">
            {{ $subtitle }}
        </p>
        @endif
    </div>
</section>
