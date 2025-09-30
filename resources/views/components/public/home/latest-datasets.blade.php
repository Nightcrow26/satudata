@props([
'title' => 'Data Terbaru',
'items' => [],
'moreUrl' => '#',
'showArrows' => true,
])

<section aria-labelledby="latest-heading" class="pt-6 pb-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 id="latest-heading"
            class="text-center font-extrabold tracking-tight text-2xl sm:text-3xl text-teal-700 dark:text-teal-300 transition-colors duration-200">
            {{ $title }}
        </h2>
        <div
            class="mx-auto mt-2 h-1 w-24 rounded-full bg-teal-500/70 dark:bg-teal-500/60 transition-colors duration-200">
        </div>

        <div x-data="{
                atStart: true,
                atEnd: false,
                update() {
                    const el = this.$refs.track
                    if (!el) return
                    const sl = Math.ceil(el.scrollLeft)
                    const max = Math.ceil(el.scrollWidth - el.clientWidth)
                    this.atStart = sl <= 0
                    this.atEnd = sl >= max
                },
                left() { this.$refs.track.scrollBy({ left: -this.$refs.track.clientWidth, behavior: 'smooth' }) },
                right(){ this.$refs.track.scrollBy({ left:  this.$refs.track.clientWidth, behavior: 'smooth' }) },
            }" x-init="update()" @resize.window.debounce.150ms="update()" class="relative mt-6">
            {{-- TRACK --}}
            <div x-ref="track" @scroll.debounce.120ms="update()" id="latest-track" class="flex gap-4 sm:gap-6 overflow-x-auto overflow-y-visible scroll-smooth
                       snap-x snap-mandatory snap-center sm:snap-start scroll-px-4 pb-2" role="group"
                aria-label="Carousel Data Terbaru">
                @forelse ($items as $i)
                @php
                // Gunakan warna dari database atau fallback ke warna default
                $aspekWarna = $i->aspek->warna ?? '#0d9488'; // Default teal-600
                
                // Konversi hex ke RGB untuk light dan dark theme
                $hex = ltrim($aspekWarna, '#');
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));
                
                // Untuk light theme: background ringan dengan text yang lebih gelap
                $lightBg = "rgba({$r}, {$g}, {$b}, 0.3)";
                $lightText = "rgb({$r}, {$g}, {$b})";
                
                // Untuk dark theme: background lebih gelap dengan text yang lebih terang
                $darkBg = "rgba({$r}, {$g}, {$b}, 0.2)";
                $darkR = min(255, $r + 80);
                $darkG = min(255, $g + 80);
                $darkB = min(255, $b + 80);
                $darkText = "rgb({$darkR}, {$darkG}, {$darkB})";
                @endphp

                <article class="relative snap-always shrink-0 transition-colors duration-200
                               rounded-2xl border border-gray-200 dark:border-gray-800
                               bg-white dark:!bg-gray-800 shadow-sm hover:shadow-md
                               min-w-[calc(100vw-3rem)] max-w-[calc(100vw-3rem)]
                               sm:min-w-[18rem] sm:max-w-[18rem] md:min-w-[20rem] md:max-w-[20rem]">
                    <a href="{{ route('public.data.show', $i->id) }}" class="group block p-4" wire:navigate>
                        {{-- ROW atas --}}
                        <div class="flex items-start gap-4">
                            {{-- Thumbnail --}}
                            <div
                                class="h-24 w-24 rounded-xl overflow-hidden grid place-items-center
                                            bg-teal-50 dark:bg-teal-800/20
                                            ring-1 ring-teal-100 dark:ring-teal-800 transition-colors duration-200 shrink-0">
                                @if(!empty($i->aspek->foto))
                                <img src="{{  Storage::disk('s3')->temporaryUrl($i->aspek->foto, now()->addMinutes(15)) }}"  alt="" class="h-full w-full object-cover" />
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="h-10 w-10 text-teal-400 dark:text-teal-300 transition-colors duration-200">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                </svg>
                                @endif
                            </div>

                            {{-- Kolom kanan --}}
                            <div class="flex-1">
                                @if(!empty($i->aspek->nama))
                                <div class="mb-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold transition-colors duration-200"
                                          x-data="{
                                            updateStyle() {
                                              this.$el.style.backgroundColor = localStorage.getItem('theme') === 'dark' ? '{{ $darkBg }}' : '{{ $lightBg }}';
                                              this.$el.style.color = localStorage.getItem('theme') === 'dark' ? '{{ $darkText }}' : '{{ $lightText }}';
                                            }
                                          }"
                                          x-init="updateStyle()"
                                          @storage.window="updateStyle()"
                                          @theme-changed.window="updateStyle()"
                                          style="background-color: {{ $lightBg }}; color: {{ $lightText }};">
                                        {{ ucfirst($i->aspek->nama) }}
                                    </span>
                                </div>
                                @endif

                                <ul
                                    class="text-[11px] text-gray-700 dark:text-gray-300 space-y-1 sm:space-y-1.5 transition-colors duration-200">
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path fill-rule="evenodd"
                                                d="M9.674 2.075a.75.75 0 0 1 .652 0l7.25 3.5A.75.75 0 0 1 17 6.957V16.5h.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H3V6.957a.75.75 0 0 1-.576-1.382l7.25-3.5ZM11 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.5 9.75a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $i->skpd?->singkatan ?? $i->skpd?->nama ?? '—' }}</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path
                                                d="M5.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H6a.75.75 0 0 1-.75-.75V12ZM6 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H6ZM7.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H8a.75.75 0 0 1-.75-.75V12ZM8 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H8ZM9.25 10a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H10a.75.75 0 0 1-.75-.75V10ZM10 11.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V12a.75.75 0 0 0-.75-.75H10ZM9.25 14a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H10a.75.75 0 0 1-.75-.75V14ZM12 9.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V10a.75.75 0 0 0-.75-.75H12ZM11.25 12a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H12a.75.75 0 0 1-.75-.75V12ZM12 13.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V14a.75.75 0 0 0-.75-.75H12ZM13.25 10a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.75H14a.75.75 0 0 1-.75-.75V10ZM14 11.25a.75.75 0 0 0-.75.75v.01c0 .414.336.75.75.75h.01a.75.75 0 0 0 .75-.75V12a.75.75 0 0 0-.75-.75H14Z" />
                                            <path fill-rule="evenodd"
                                                d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $i->created_at->translatedFormat('d F Y') ?? '—' }}</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                            <path fill-rule="evenodd"
                                                d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $i['view'] ?? 0 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Judul & deskripsi --}}
                        <h3 class="mt-3 text-[15px] font-semibold leading-snug
                                       text-gray-800 dark:text-white
                                       group-hover:text-teal-700 dark:group-hover:text-teal-300
                                       transition-colors duration-200"
                            style="-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $i['nama'] ?? '—' }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 transition-colors duration-200"
                            style="-webkit-line-clamp:4;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {!! strip_tags(Str::of($i['deskripsi'])->before('</p>')->before("&nbsp;")) ?? '' !!}
                        </p>
                    </a>
                </article>
                @empty
                <div
                    class="text-center text-sm text-gray-500 dark:text-gray-400 py-8 w-full transition-colors duration-200">
                    Belum ada data.
                </div>
                @endforelse
            </div>

            {{-- Panah (sembunyi di mobile) --}}
            @if($showArrows)
            <button type="button" @click="left()" :disabled="atStart"
                :class="atStart ? 'opacity-40 cursor-not-allowed' : ''" class="hidden md:grid place-items-center absolute top-1/2 -translate-y-1/2 -left-3 lg:-left-5 h-10 w-10 rounded-full
                               bg-teal-500 text-white shadow-lg ring-1 ring-teal-600/50" aria-label="Geser ke kiri"
                aria-controls="latest-track">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M15.4 4.6 8 12l7.4 7.4 1.4-1.4L10.8 12l6-6-1.4-1.4Z" />
                </svg>
            </button>

            <button type="button" @click="right()" :disabled="atEnd"
                :class="atEnd ? 'opacity-40 cursor-not-allowed' : ''" class="hidden md:grid place-items-center absolute top-1/2 -translate-y-1/2 -right-3 lg:-right-5 h-10 w-10 rounded-full
                               bg-teal-500 text-white shadow-lg ring-1 ring-teal-600/50" aria-label="Geser ke kanan"
                aria-controls="latest-track">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="m8.6 19.4 7.4-7.4-7.4-7.4-1.4 1.4 6 6-6 6 1.4 1.4Z" />
                </svg>
            </button>
            @endif
        </div>

        <div class="text-center">
            <a href="{{ $moreUrl }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-teal-500 px-5 py-2.5 text-white text-sm font-semibold
                      hover:bg-teal-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"
                wire:navigate>
                Lihat Lebih Banyak
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13 5l7 7-7 7v-4H4v-6h9V5Z" />
                </svg>
            </a>
        </div>
    </div>
</section>
