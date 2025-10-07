{{-- resources/views/components/public/walidata/detail-header.blade.php --}}
@props(['walidata' => null, 'class' => ''])

@php
$w = is_array($walidata) ? (object) $walidata : ($walidata ?? (object) []);
$title = $w->title ?? 'Judul Walidata Placeholder';
$summary = $w->summary ?? 'Deskripsi placeholder lorem ipsum dolor sit amet. Teks ini akan diganti dengan deskripsi
singkat tentang walidata ketika data riil tersedia.';

$publishedAt = $w->published_at ?? '17 Januari 2025';
$views = $w->views ?? 112;
@endphp

<div {{ $attributes->merge([
    'class' => "rounded-3xl border border-gray-200 bg-white p-4 sm:p-6 shadow-[0_8px_24px_rgba(2,6,23,0.06)]
    dark:!border-gray-700 dark:!bg-gray-800 {$class}"
    ]) }}>
    <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] md:items-start gap-4 sm:gap-6 md:gap-8">
        {{-- Kiri: judul + badges + deskripsi --}}
        <div>
            <h1
                class="text-3xl md:text-4xl font-extrabold tracking-tight leading-tight text-slate-900 dark:text-gray-100">
                {{ $w->indikator->uraian_indikator ?? 'Judul Walidata Placeholder' }}
            </h1>

            @if($w && isset($w->aspek) && $w->aspek)
            <div class="mt-3 flex flex-wrap gap-2">
                @php
                // Gunakan warna dari database aspek atau fallback ke warna default
                $aspekWarna = $w->aspek->warna ?? '#0d9488'; // Default teal-600
                $aspekWarna = is_string($aspekWarna) ? $aspekWarna : '#0d9488'; // Pastikan string
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
                @endphp
                
                <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs sm:text-[13px] font-semibold transition-colors duration-200"
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
                    {{ $w->aspek->nama }}
                </span>
            </div>
            @endif

            <p class="mt-4 text-base text-slate-600 dark:text-gray-300">
                {{ $w->indikator->uraian_indikator ?? 'Deskripsi placeholder lorem ipsum dolor sit amet. Teks ini akan diganti dengan deskripsi'}}
            </p>
        </div>

        {{-- Kanan: meta + aksi --}}
        <div class="flex flex-col md:items-end gap-3 md:gap-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-[15px] text-slate-600 dark:text-gray-300">
                <span class="inline-flex items-center gap-2 text-teal-700 dark:text-teal-400">
                    {{-- calendar --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-5 w-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008Z" />
                    </svg>
                    @php
                        use Carbon\Carbon;
                        $verifDate = null;
                        if (! empty($w->verifikasi_data)) {
                            try {
                                $verifDate = $w->verifikasi_data instanceof \Illuminate\Support\Carbon ? $w->verifikasi_data : Carbon::parse($w->verifikasi_data);
                            } catch (\Exception $e) {
                                $verifDate = null;
                            }
                        }
                    @endphp
                    <span>{{ $verifDate ? $verifDate->translatedFormat('d F Y') : '-' }}</span>
                </span>

                <span class="inline-flex items-center gap-2 text-teal-700 dark:text-teal-400">
                    {{-- eye --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-5 w-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <span>{{ $w->view ?? 0 }}</span>
                </span>
            </div>

            {{-- Bar aksi --}}
            <div class="-mx-1.5 flex items-center gap-1.5 md:gap-3">
                {{-- Share --}}
                <button
                    type="button"
                    x-data="{ copied: false, copy() { const link = window.location.href; if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(link).then(() => { this.copied = true; setTimeout(() => this.copied = false, 1800); }); } else { const ta = document.createElement('textarea'); ta.value = link; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); this.copied = true; setTimeout(() => this.copied = false, 1800); } catch (e) {} document.body.removeChild(ta); } } }"
                    @click="copy()"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-teal-600 dark:text-teal-400 bg-white dark:!bg-gray-800 border border-teal-300 dark:border-teal-600 rounded-md hover:bg-green-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    aria-label="Bagikan">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="h-5 w-4 text-teal-700 dark:text-teal-400" x-show="!copied">
                        <path fill-rule="evenodd"
                            d="M15.75 4.5a3 3 0 1 1 .825 2.066l-8.421 4.679a3.002 3.002 0 0 1 0 1.51l8.421 4.679a3 3 0 1 1-.729 1.31l-8.421-4.678a3 3 0 1 1 0-4.132l8.421-4.679a3 3 0 0 1-.096-.755Z"
                            clip-rule="evenodd" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 text-teal-700 dark:text-teal-300" x-show="copied" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span x-show="copied" x-cloak class="ml-2 text-sm font-medium text-teal-700 dark:text-teal-300">Tersalin</span>
                </button>

                {{-- Download PDF --}}
                <a 
                    href="{{ route('public.walidata.download', [$w, 'pdf']) }}" 
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                    title="Download PDF"
                >
                    <i class="bi bi-filetype-pdf"></i>
                </a>

                {{-- Download Excel --}}
                <a 
                    href="{{ route('public.walidata.download', [$w, 'excel']) }}" 
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border border-green-300 dark:border-green-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                    title="Download Excel"
                >
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </div>
    </div>
</div>
