<div>
    @php
        $user = auth()->user();
        $role = auth()->user()->roles->first()->name ?? 'Pengguna';
    @endphp

    <div class="mb-4">
        <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:!border-gray-700 hover:shadow-lg transition-shadow">
            <div class="p-4 flex items-center">
                <div class="mr-3">
                    <i class="bi bi-person-circle text-4xl text-gray-700 dark:!text-gray-400"></i>
                </div>
                <div>
                    <p class="mb-1 text-gray-900 dark:!text-white">Selamat datang, {{ $user->name }}!</p>
                    <p class="mb-0 text-gray-700 dark:!text-gray-400">Anda login sebagai <span class="font-semibold text-blue-600 dark:!text-blue-400">{{ ucfirst($role) }}</span></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Statistik Ringkasan -->
    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-4 gap-3 mb-4">
        @foreach ([
            ['icon'=>'archive','text'=>'Data','count'=>$datasetCount,'color'=>'info'],
            ['icon'=>'book','text'=>'Publikasi','count'=>$publikasiCount,'color'=>'primary'],
            ['icon'=>'bookmark-star','text'=>'Aspek','count'=>$aspekCount,'color'=>'danger'],
            ['icon'=>'bank','text'=>'Instansi','count'=>$instansiCount,'color'=>'success'],
        ] as $item)
        <div>
            <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:!border-gray-700 hover:shadow-lg transition-shadow">
                <div class="p-4 flex items-center">
                
                {{-- Background ikon dengan opacity rendah --}}
                    <div class="rounded px-2 mr-3 h-15 w-15 flex items-center justify-center
                        @if($item['color'] == 'info') bg-cyan-100 dark:!bg-cyan-900/20 @endif
                        @if($item['color'] == 'primary') bg-blue-100 dark:!bg-blue-900/20 @endif
                        @if($item['color'] == 'danger') bg-red-100 dark:!bg-red-900/20 @endif
                        @if($item['color'] == 'success') bg-green-100 dark:!bg-green-900/20 @endif
                    ">
                        <i class="bi bi-{{ $item['icon'] }} text-5xl 
                            @if($item['color'] == 'info') text-cyan-600 dark:!text-cyan-400 @endif
                            @if($item['color'] == 'primary') text-blue-600 dark:!text-blue-400 @endif
                            @if($item['color'] == 'danger') text-red-600 dark:!text-red-400 @endif
                            @if($item['color'] == 'success') text-green-600 dark:!text-green-400 @endif
                        "></i>
                    </div>
                    
                    <div class="mt-2">
                        <p class="mb-0 text-2xl font-bold text-gray-900 dark:!text-white">{{ $item['count'] }}</p>
                        <p class="mt-0 text-gray-800 dark:!text-gray-400">{{ $item['text'] }}</p>
                    </div>
                </div>
            </div>
        </div>  
    @endforeach
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Chart Line -->
        <div>
            <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:!border-gray-700 hover:shadow-lg transition-shadow">
                <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 border-b border-gray-200 dark:!border-gray-600 rounded-t-lg">
                    <h5 class="text-lg font-semibold text-gray-900 dark:!text-white mb-0">Statistik Perkembangan Data</h5>
                </div>
                <div class="p-0">
                    <div class="chart-wrapper" style="height: 300px;">
                        <canvas id="chartLine" wire:ignore></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Donut -->
        <div>
            <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:!border-gray-700 hover:shadow-lg transition-shadow">
            <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 border-b border-gray-200 dark:!border-gray-600 rounded-t-lg">
                <h5 class="text-lg font-semibold text-gray-900 dark:!text-white mb-0">Persentase Data Berdasarkan Aspek</h5>
            </div>
            <div class="p-0">
                <div class="chart-wrapper" style="height: 300px;">
                    <canvas id="chartDonut" wire:ignore></canvas>
                </div>
            </div>
            </div>
        </div>
    </div>

    <h6 class="mt-8 mb-3 text-lg font-semibold text-gray-900 dark:!text-white">Data Terbaru</h6>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($latestData->take(4) as $item)
            @php
                // Dynamic color calculations for theme support
                $aspekColor = $item->aspek->warna ?? '#6b7280';
                $rgb = array_map(fn($x) => hexdec($x), str_split(ltrim($aspekColor, '#'), 2));
                $lightBg = sprintf('rgba(%d, %d, %d, 0.1)', $rgb[0], $rgb[1], $rgb[2]);
                $lightText = $aspekColor;
                $darkBg = sprintf('rgba(%d, %d, %d, 0.2)', $rgb[0], $rgb[1], $rgb[2]);
                $darkText = sprintf('rgba(%d, %d, %d, 0.9)', min(255, $rgb[0] + 60), min(255, $rgb[1] + 60), min(255, $rgb[2] + 60));
            @endphp
            
            <article class="group">
                <a href="{{ route('admin.dataset.show', $item->id) }}" wire:navigate
                   class="block bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:!border-gray-700
                          hover:shadow-lg hover:border-teal-300 dark:hover:!border-teal-600
                          transition-all duration-200 h-full">
                    <div class="p-4">
                        {{-- Layout kiri-kanan --}}
                        <div class="flex gap-3 mb-3">
                            {{-- Kolom kiri: thumbnail --}}
                            <div class="flex-shrink-0">
                                @if(!empty($item->aspek->foto))
                                <img src="{{ Storage::disk('s3')->temporaryUrl($item->aspek->foto, now()->addMinutes(15)) }}" 
                                     alt="{{ $item->nama }}" 
                                     class="w-25 h-25 object-cover rounded-lg bg-gray-100 dark:!bg-gray-700">
                                @else
                                <div class="w-25 h-25 bg-gray-100 dark:!bg-gray-700 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" 
                                         class="w-8 h-8 text-gray-400 dark:!text-gray-500">
                                        <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909.47.47a.75.75 0 1 1-1.06 1.06L6.53 8.091a.75.75 0 0 0-1.06 0l-2.97 2.97ZM12 7a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                @endif
                            </div>

                            {{-- Kolom kanan: metadata --}}
                            <div class="flex-1 min-w-0">
                                @if(!empty($item->aspek->nama))
                                <div class="mb-2">
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
                                        {{ ucfirst($item->aspek->nama) }}
                                    </span>
                                </div>
                                @endif

                                <ul class="text-[11px] text-gray-700 dark:text-gray-300 space-y-1 transition-colors duration-200">
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path fill-rule="evenodd"
                                                d="M9.674 2.075a.75.75 0 0 1 .652 0l7.25 3.5A.75.75 0 0 1 17 6.957V16.5h.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H3V6.957a.75.75 0 0 1-.576-1.382l7.25-3.5ZM11 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.5 9.75a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ Str::limit($item->skpd->singkatan ?? '—', 10) }}</span>
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
                                        <span>{{ $item->created_at->translatedFormat('d F Y') ?? '—' }}</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                            <path fill-rule="evenodd"
                                                d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $item->view ?? 0 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Judul & deskripsi --}}
                        <h3 class="mt-3 text-[15px] font-semibold leading-snug
                                   text-gray-900 dark:text-white
                                   group-hover:text-teal-700 dark:group-hover:text-teal-300
                                   transition-colors duration-200"
                            style="-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $item->nama ?? '—' }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 transition-colors duration-200"
                           style="-webkit-line-clamp:3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ strip_tags(Str::of($item->deskripsi)->before('</p>')->before("\n")) ?? '' }}
                        </p>
                    </div>
                </a>
            </article>
        @endforeach
    </div>

    <h6 class="mt-8 mb-3 text-lg font-semibold text-gray-900 dark:text-white">Indikator Walidata Terbaru</h6>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($latestIndikator->take(4) as $item)
            @php
                // Relasi aspek bisa null
                $aspek = $item->aspek ?? null;
                $badgeText = $aspek->nama ?? 'Undefined';
                $badgeColor = $aspek->warna ?? '#6b7280'; // fallback abu-abu

                // Dynamic color calculations for theme support
                $rgb = array_map(fn($x) => hexdec($x), str_split(ltrim($badgeColor, '#'), 2));
                $lightBg = sprintf('rgba(%d, %d, %d, 0.1)', $rgb[0], $rgb[1], $rgb[2]);
                $lightText = $badgeColor;
                $darkBg = sprintf('rgba(%d, %d, %d, 0.2)', $rgb[0], $rgb[1], $rgb[2]);
                $darkText = sprintf('rgba(%d, %d, %d, 0.9)', min(255, $rgb[0] + 60), min(255, $rgb[1] + 60), min(255, $rgb[2] + 60));

                // Gambar: jika ada foto di S3 pakai temporaryUrl, jika tidak pakai public/kesehatan.png
                $fotoUrl = asset('kesehatan.png');
                if (!empty(optional($aspek)->foto)) {
                    try {
                        $fotoUrl = Storage::disk('s3')->temporaryUrl($aspek->foto, now()->addMinutes(15));
                    } catch (\Throwable $e) {
                        $fotoUrl = asset('kesehatan.png');
                    }
                }
            @endphp

            <article class="group">
                <a href="{{ route('admin.walidata.show', $item->id) }}" wire:navigate
                   class="block bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700
                          hover:shadow-lg hover:border-teal-300 dark:hover:border-teal-600
                          transition-all duration-200 h-full">
                    <div class="p-4">
                        {{-- Layout kiri-kanan --}}
                        <div class="flex gap-3 mb-3">
                            {{-- Kolom kiri: thumbnail --}}
                            <div class="flex-shrink-0">
                                <img src="{{ $fotoUrl }}" 
                                     alt="{{ optional($item->indikator)->uraian_indikator ?? '-' }}" 
                                     class="w-25 h-25 object-cover rounded-lg bg-gray-100 dark:bg-gray-700">
                            </div>

                            {{-- Kolom kanan: metadata --}}
                            <div class="flex-1 min-w-0">
                                <div class="mb-2">
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
                                        {{ ucfirst($badgeText) }}
                                    </span>
                                </div>

                                <ul class="text-[11px] text-gray-700 dark:text-gray-300 space-y-1 transition-colors duration-200">
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path fill-rule="evenodd"
                                                d="M9.674 2.075a.75.75 0 0 1 .652 0l7.25 3.5A.75.75 0 0 1 17 6.957V16.5h.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H3V6.957a.75.75 0 0 1-.576-1.382l7.25-3.5ZM11 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.5 9.75a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ Str::limit($item->skpd->singkatan ?? '—', 10) }}</span>
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
                                        <span>{{ optional($item->created_at)->translatedFormat('d F Y') ?? '—' }}</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                            <path fill-rule="evenodd"
                                                d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $item->view ?? 0 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Judul & deskripsi --}}
                        <h3 class="mt-3 text-[15px] font-semibold leading-snug
                                   text-gray-900 dark:text-white
                                   group-hover:text-teal-700 dark:group-hover:text-teal-300
                                   transition-colors duration-200"
                            style="-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ Str::limit($item->nama ?? (optional($item->indikator)->uraian_indikator ?? '—'), 60) }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 transition-colors duration-200"
                           style="-webkit-line-clamp:3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ optional($item->indikator)->uraian_indikator ?? '' }}
                        </p>
                    </div>
                </a>
            </article>
        @endforeach
    </div>

    <h6 class="mt-8 mb-3 text-lg font-semibold text-gray-900 dark:text-white">Publikasi Terbaru</h6>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($latestPublikasi->take(4) as $item)
            @php
                // Dynamic color calculations for theme support
                $aspekColor = $item->aspek->warna ?? '#6b7280';
                $rgb = array_map(fn($x) => hexdec($x), str_split(ltrim($aspekColor, '#'), 2));
                $lightBg = sprintf('rgba(%d, %d, %d, 0.1)', $rgb[0], $rgb[1], $rgb[2]);
                $lightText = $aspekColor;
                $darkBg = sprintf('rgba(%d, %d, %d, 0.2)', $rgb[0], $rgb[1], $rgb[2]);
                $darkText = sprintf('rgba(%d, %d, %d, 0.9)', min(255, $rgb[0] + 60), min(255, $rgb[1] + 60), min(255, $rgb[2] + 60));
            @endphp
            
            <article class="group">
                <a href="{{ route('public.publications.download', $item->id) }}" target="_blank"
                   class="block bg-white dark:!bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:!border-gray-700
                          hover:shadow-lg hover:border-teal-300 dark:hover:!border-teal-600
                          transition-all duration-200 h-full">
                    <div class="p-4">
                        {{-- Layout kiri-kanan --}}
                        <div class="flex gap-3 mb-3">
                            {{-- Kolom kiri: thumbnail --}}
                            <div class="flex-shrink-0">
                                @if(!empty($item->aspek->foto))
                                <img src="{{ Storage::disk('s3')->temporaryUrl($item->aspek->foto, now()->addMinutes(15)) }}" 
                                     alt="{{ $item->nama }}" 
                                     class="w-25 h-25 object-cover rounded-lg bg-gray-100 dark:!bg-gray-700">
                                @else
                                <div class="w-25 h-25 bg-gray-100 dark:!bg-gray-700 rounded-lg flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" 
                                         class="w-8 h-8 text-gray-400 dark:!text-gray-500">
                                        <path fill-rule="evenodd" d="M1 5.25A2.25 2.25 0 0 1 3.25 3h13.5A2.25 2.25 0 0 1 19 5.25v9.5A2.25 2.25 0 0 1 16.75 17H3.25A2.25 2.25 0 0 1 1 14.75v-9.5Zm1.5 5.81v3.69c0 .414.336.75.75.75h13.5a.75.75 0 0 0 .75-.75v-2.69l-2.22-2.219a.75.75 0 0 0-1.06 0l-1.91 1.909.47.47a.75.75 0 1 1-1.06 1.06L6.53 8.091a.75.75 0 0 0-1.06 0l-2.97 2.97ZM12 7a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                @endif
                            </div>

                            {{-- Kolom kanan: metadata --}}
                            <div class="flex-1 min-w-0">
                                @if(!empty($item->aspek->nama))
                                <div class="mb-2">
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
                                        {{ ucfirst($item->aspek->nama) }}
                                    </span>
                                </div>
                                @endif

                                <ul class="text-[11px] text-gray-700 dark:text-gray-300 space-y-1 transition-colors duration-200">
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path fill-rule="evenodd"
                                                d="M9.674 2.075a.75.75 0 0 1 .652 0l7.25 3.5A.75.75 0 0 1 17 6.957V16.5h.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H3V6.957a.75.75 0 0 1-.576-1.382l7.25-3.5ZM11 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM7.5 9.75a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Zm3.25 0a.75.75 0 0 0-1.5 0v5.5a.75.75 0 0 0 1.5 0v-5.5Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ Str::limit($item->skpd->singkatan ?? '—', 10) }}</span>
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
                                        <span>{{ $item->created_at->translatedFormat('d F Y') ?? '—' }}</span>
                                    </li>
                                    <li class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                            class="h-4 w-4 text-teal-600 dark:text-teal-400 transition-colors duration-200">
                                            <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03L10.75 11.364V2.75Z" />
                                            <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" />
                                        </svg>
                                        <span>{{ $item->download ?? 0 }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Judul & deskripsi --}}
                        <h3 class="mt-3 text-[15px] font-semibold leading-snug
                                   text-gray-900 dark:text-white
                                   group-hover:text-teal-700 dark:group-hover:text-teal-300
                                   transition-colors duration-200"
                            style="-webkit-line-clamp:2;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ $item->nama ?? '—' }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400 transition-colors duration-200"
                           style="-webkit-line-clamp:3;display:-webkit-box;-webkit-box-orient:vertical;overflow:hidden;">
                            {{ strip_tags(Str::of($item->deskripsi)->before('</p>')->before("\n")) ?? '' }}
                        </p>
                    </div>
                </a>
            </article>
        @endforeach
    </div>
</div>

@push('scripts')
    <script>
    window.dashboardData = {
        lineLabels: @json($lineLabels),
        lineData: @json($lineData),
        donutLabels: @json($donutLabels),
        donutData: @json($donutData)
    };
    </script>
@endpush
