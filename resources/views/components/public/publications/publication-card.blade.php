@props([
'publication' => null,
'title' => null,
'url' => '#',
'thumb' => null,
'badges' => [],
'instansiLabel' => null,
'dateLabel' => null,
'downloads' => null,
'desc' => null,
])

@php
$p = $publication;

$title = $title ?? ($p?->nama ?? 'Judul Publikasi');
$url = $url ?? ($p ? route('public.publications.download', $p->id) : '#');
$thumb = $thumb ?? ($p?->foto_url ?? null);
$instansiLabel = $instansiLabel ?? ($p->skpd->singkatan ?? null);
$dateLabel = $dateLabel ?? (optional($p?->created_at)?->translatedFormat('d F Y'));
$views = $views ?? ($p?->view ?? null);
$downloads = $downloads ?? ($p?->download ?? null);
$desc = $desc ?? ($p?->deskripsi ?? null);

// Optional badge fallback - menggunakan aspek sebagai kategori
if ($p && empty($badges) && isset($p->aspek?->nama)) {
    $aspekWarna = $p->aspek->warna ?? '#0d9488'; // Default teal-600
    $badges = [[
        'label' => $p->aspek->nama, 
        'variant' => 'custom',
        'color' => $aspekWarna,
    ]];
}

// Varian badge dengan dukungan dark mode
$badgeVariants = [
'teal' => 'bg-teal-500 text-white',
'amber' => 'bg-amber-500 text-white',
'indigo' => 'bg-indigo-500 text-white',
'green' => 'bg-green-500 text-white',
'red' => 'bg-red-500 text-white',
'blue' => 'bg-blue-500 text-white',
'purple' => 'bg-purple-500 text-white',
'pink' => 'bg-pink-500 text-white',
'orange' => 'bg-orange-500 text-white',
// Gray di-tuning agar kontras di dark mode
'gray' => 'bg-gray-200 text-gray-800 dark:!bg-gray-700 dark:!text-gray-100',
];
@endphp

<article {{ $attributes->merge([
    'class' => 'group relative w-full rounded-2xl border border-gray-200 dark:!border-gray-700
    bg-white dark:!bg-gray-800 shadow-sm dark:!shadow-gray-900/20
    hover:bg-gray-50 dark:hover:!bg-gray-700 hover:shadow-md dark:hover:!shadow-gray-900/30
    transition-colors transition-shadow duration-200
    focus-within:outline-none focus-within:ring-2 focus-within:ring-teal-600 dark:focus-within:!ring-teal-400
    focus-within:ring-offset-2 focus-within:ring-offset-white dark:focus-within:!ring-offset-gray-900'
    ]) }}>
    <a href="{{ $url !== '#' && $url ? $url : '#' }}" class="absolute inset-0" aria-label="{{ $title }}"></a>

    <div class="p-4 sm:p-5 flex gap-4 sm:gap-5">
        {{-- Thumbnail lebih tinggi (sesuai preferensi publikasi) --}}
        <div
            class="w-20 h-28 sm:w-24 sm:h-32 rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-700 bg-gray-50 dark:bg-gray-700 shrink-0">
            @if($thumb)
            <img src="{{ resolve_media_url($thumb) }}" alt="" class="h-full w-full object-cover" onerror="this.onerror=null;this.src='{{ asset('kesehatan.png') }}'">
            @else
            <div class="h-full w-full grid place-content-center text-gray-300 dark:text-gray-400">
                <svg class="h-10 w-10 sm:h-12 sm:w-12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path
                        d="M3 5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v13.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V5.25Zm3 9.94 2.97-2.97a1.5 1.5 0 0 1 2.12 0l3.69 3.69h-8.78Zm9.19 0h2.56a.75.75 0 0 0 .75-.75v-7.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 1-.75.75v9.06l2.7-2.7a3 3 0 0 1 4.24 0l2.45 2.45Z" />
                </svg>
            </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <h3 class="text-base sm:text-lg font-semibold leading-tight text-gray-900 dark:text-white line-clamp-1">
                <a href="{{ $url !== '#' && $url ? $url : '#' }}" class="relative">{{ $title }}</a>
            </h3>

            @if(!empty($badges))
            <div class="mt-1.5 flex flex-wrap gap-2">
                @foreach($badges as $b)
                @php
                if (($b['variant'] ?? 'gray') === 'custom' && isset($b['color'])) {
                    // Gunakan warna dari database
                    $aspekWarna = $b['color'];
                    $hex = ltrim($aspekWarna, '#');
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b_val = hexdec(substr($hex, 4, 2));
                    
                    // Untuk light theme: background ringan dengan text yang lebih gelap
                    $lightBg = "rgba({$r}, {$g}, {$b_val}, 0.3)";
                    $lightText = "rgb({$r}, {$g}, {$b_val})";
                    
                    // Untuk dark theme: background lebih gelap dengan text yang lebih terang
                    $darkBg = "rgba({$r}, {$g}, {$b_val}, 0.2)";
                    $darkR = min(255, $r + 80);
                    $darkG = min(255, $g + 80);
                    $darkB = min(255, $b_val + 80);
                    $darkText = "rgb({$darkR}, {$darkG}, {$darkB})";
                    
                    $customStyle = "background-color: {$lightBg}; color: {$lightText};";
                    $variant = '';
                } else {
                    $variant = $badgeVariants[$b['variant'] ?? 'gray'] ?? $badgeVariants['gray'];
                    $customStyle = '';
                }
                @endphp
                
                @if($customStyle)
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium transition-colors duration-200"
                      x-data="{
                        updateStyle() {
                          this.$el.style.backgroundColor = localStorage.getItem('theme') === 'dark' ? '{{ $darkBg }}' : '{{ $lightBg }}';
                          this.$el.style.color = localStorage.getItem('theme') === 'dark' ? '{{ $darkText }}' : '{{ $lightText }}';
                        }
                      }"
                      x-init="updateStyle()"
                      @storage.window="updateStyle()"
                      @theme-changed.window="updateStyle()"
                      style="{{ $customStyle }}">
                    {{ $b['label'] ?? '' }}
                </span>
                @else
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium {{ $variant }}">
                    {{ $b['label'] ?? '' }}
                </span>
                @endif
                @endforeach
            </div>
            @endif

            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[13px] text-gray-600 dark:text-gray-300">
                @if($instansiLabel)
                <span class="inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                        <path fill-rule="evenodd"
                            d="M9.674 2.075a.75.75 0 0 1 .652 0l7.25 3.5A.75.75 0 0 1 17 6.957V16.5h.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H3V6.957a.75.75 0 0 1-.576-1.382l7.25-3.5ZM11 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.5 9.75a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $instansiLabel }}
                </span>
                @endif

                @if($dateLabel)
                <span class="inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                        <path
                            d="M5.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H6a.75.75 0 0 1-.75-.75V12ZM6 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H6ZM7.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H8a.75.75 0 0 1-.75-.75V12ZM8 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H8ZM9.25 10a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H10a.75.75 0 0 1-.75-.75V10ZM10 11.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V12a.75.75 0 0 0-.75-.75H10ZM9.25 14a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H10a.75.75 0 0 1-.75-.75V14ZM12 9.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V10a.75.75 0 0 0-.75-.75H12ZM11.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H12a.75.75 0 0 1-.75-.75V12ZM12 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H12ZM13.25 10a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H14a.75.75 0 0 1-.75-.75V10ZM14 11.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V12a.75.75 0 0 0-.75-.75H14Z" />
                        <path fill-rule="evenodd"
                            d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $dateLabel }}
                </span>
                @endif

                @if(!is_null($downloads))
                <span class="inline-flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                        <path fill-rule="evenodd"
                            d="M10 2a.75.75 0 0 1 .75.75v7.5l2.72-2.72a.75.75 0 1 1 1.06 1.06l-4 4a.75.75 0 0 1-1.06 0l-4-4a.75.75 0 1 1 1.06-1.06L9.25 10.25V2.75A.75.75 0 0 1 10 2ZM2.75 15a.75.75 0 0 1 .75-.75h13a.75.75 0 0 1 0 1.5h-13a.75.75 0 0 1-.75-.75Z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $downloads }}
                </span>
                @endif
            </div>

            @if($desc)
            <div class="mt-2 text-sm text-gray-700 dark:text-gray-200 line-clamp-2 content-html trix-content">
                {!! strip_tags(Str::of($desc)->before('</p>')->before("&nbsp;")) ?? '' !!}
            </div>
            @endif
        </div>
    </div>
</article>
