<div>
    @php
        $user = auth()->user();
        $role = auth()->user()->roles->first()->name ?? 'Pengguna';
    @endphp

    <div class="mb-4">
        <div class="card card-animate shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3">
                    <i class="bi bi-person-circle fs-1"></i>
                </div>
                <div>
                    <p class="mb-1">Selamat datang, {{ $user->name }}!</p>
                    <p class="mb-0 text-muted">Anda login sebagai <span class="fw-semibold text-primary">{{ ucfirst($role) }}</span></p>
                </div>
            </div>
        </div>
    </div>
    <!-- Statistik Ringkasan -->
    <div class="row g-3 mb-4">
    @foreach ([
        ['icon'=>'archive','text'=>'Data','count'=>$datasetCount,'color'=>'info'],
        ['icon'=>'book','text'=>'Publikasi','count'=>$publikasiCount,'color'=>'primary'],
        ['icon'=>'bookmark-star','text'=>'Aspek','count'=>$aspekCount,'color'=>'danger'],
        ['icon'=>'bank','text'=>'Instansi','count'=>$instansiCount,'color'=>'success'],
    ] as $item)
        <div class="col-6 col-sm-4 col-md-3">
        <div class="card card-animate shadow-sm">
            <div class="card-body d-flex align-items-center">
            
            {{-- Background ikon dengan opacity rendah --}}
            <div class="rounded px-2 me-3 bg-{{ $item['color'] }} bg-opacity-10">
                <i class="bi bi-{{ $item['icon'] }} fs-1 text-{{ $item['color'] }}"></i>
            </div>
            
            <div class="mt-3">
                <h3 class="mb-0">{{ $item['count'] }}</h3>
                <p>{{ $item['text'] }}</p>
            </div>
            </div>
        </div>
        </div>
    @endforeach
    </div>

    <div class="row g-4">
        <!-- Chart Line -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm chart-card">
            <div class="card-header">Statistik Perkembangan Data</div>
            <div class="card-body p-0">
                <div class="chart-wrapper">
                <canvas id="chartLine" wire:ignore></canvas>
                </div>
            </div>
            </div>
        </div>

        <!-- Chart Donut -->
        <div class="col-12 col-md-6">
            <div class="card shadow-sm chart-card">
            <div class="card-header">Persentase Data Berdasarkan Aspek</div>
            <div class="card-body p-0">
                <div class="chart-wrapper">
                <canvas id="chartDonut" wire:ignore></canvas>
                </div>
            </div>
            </div>
        </div>
    </div>

    <h6 class="mt-5 mb-3">Data Terbaru</h6>
    <div class="row gx-4 gy-4">
        @foreach($latestData as $item)
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="card card-animate h-100 border-success rounded shadow-sm" onclick="Livewire.navigate('{{ route('dataset.show', $item->id) }}')">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex flex-wrap mb-3">
                            <img 
                                src="{{  Storage::disk('s3')->temporaryUrl($item->aspek->foto, now()->addMinutes(15)) }}" 
                                alt="{{ $item->nama }}" 
                                class="rounded img-fluid flex-shrink-0"
                                style="max-width:100px; width:100%; height:auto; object-fit:cover;"
                            >
                            <div class="mt-sm-0 ps-2 ps-sm-3 flex-grow-1" style="min-width: 0;">
                                <span class="badge badge-hover-glow text-white mb-2" style="background-color:{{ $item->aspek->warna }}; color:#fff; font-size:0.75rem;">
                                    {{ $item->aspek->nama ?? '-' }}
                                </span>
                                <ul class="list-unstyled text-muted mb-0" style="font-size:0.7rem;">
                                    <li class="mb-1"><i class="bi bi-building me-2"></i>{{ Str::limit($item->skpd->singkatan ?? '-', 10)}}</li>
                                    <li class="mb-1"><i class="bi bi-calendar-event me-2"></i>{{ $item->created_at->format('d M Y') }}</li>
                                    <li><i class="bi bi-eye me-2"></i>{{ $item->view }}</li>
                                </ul>
                            </div>
                        </div>
                        <div style="text-align:justify;">
                            <p class="mb-2 fw-semibold" style="font-size:0.8rem;">{{ $item->nama }}</p>
                            <p class="card-text small text-muted mb-0" style="flex-grow:1; font-size:0.8rem;">
                                {{ Str::limit(strip_tags(Str::of($item->deskripsi)->before('</p>')->before("\n")), 80, '...') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h6 class="mt-5 mb-3">Indikator Walidata Terbaru</h6>
    <div class="row gx-4 gy-4">
    @foreach($latestIndikator as $item)
        @php
        // Relasi aspek bisa null
        $aspek      = $item->aspek ?? null;
        $badgeText  = $aspek->nama  ?? 'Undefined';
        $badgeColor = $aspek->warna ?? '#6c757d'; // fallback abu-abu

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

        <div class="col-12 col-sm-6 col-md-6 col-lg-3">
            <div class="card card-animate h-100 border-success rounded shadow-sm"
                onclick="Livewire.navigate('{{ route('walidata.show', $item->id) }}')">
                <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex flex-wrap mb-3">
                        <img
                        src="{{ $fotoUrl }}"
                        alt="{{ optional($item->indikator)->uraian_indikator ?? '-' }}"
                        class="rounded img-fluid flex-shrink-0"
                        style="max-width:100px; width:100%; height:auto; object-fit:cover;"
                        >

                        <div class="mt-sm-0 ps-2 ps-sm-3 flex-grow-1" style="min-width: 0;">
                        <span class="badge badge-hover-glow text-white mb-2"
                                style="background-color: {{ $badgeColor }}; color:#fff; font-size:0.75rem;">
                            {{ $badgeText }}
                        </span>

                        <ul class="list-unstyled text-muted mb-0" style="font-size:0.7rem;">
                            <li class="mb-1">
                            <i class="bi bi-building me-2"></i>{{ Str::limit(optional($item->skpd)->singkatan ?? '-', 10) }}
                            </li>
                            <li class="mb-1">
                            <i class="bi bi-calendar-event me-2"></i>{{ optional($item->created_at)->format('d M Y') ?? '-' }}
                            </li>
                            <li>
                            <i class="bi bi-eye me-2"></i>{{ $item->view ?? 0 }}
                            </li>
                        </ul>
                        </div>
                    </div>
                    <div style="text-align:justify;">
                         <p class="mb-2 fw-semibold" style="font-size:0.8rem;">
                            {{ str::limit($item->nama ?? (optional($item->indikator)->uraian_indikator ?? '-'), 50) }}
                        </p>
                        <p class="card-text small text-muted mb-0" style="flex-grow:1; font-size:0.8rem; text-align:justify;">
                            {{ Str::limit(optional($item->indikator)->uraian_indikator ?? '-', 80) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <h6 class="mt-5 mb-3">Publikasi Terbaru</h6>
    <div class="row gx-4 gy-4">
        @foreach($latestPublikasi as $item)
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="card card-animate h-100 border-success rounded shadow-sm">
                    <div class="card-body p-3 d-flex flex-column">
                        <div class="d-flex flex-wrap mb-3">
                            <img 
                                src="{{  Storage::disk('s3')->temporaryUrl($item->aspek->foto, now()->addMinutes(15)) }}" 
                                alt="{{ $item->nama }}" 
                                class="rounded img-fluid flex-shrink-0"
                                style="max-width:100px; width:100%; height:auto; object-fit:cover;"
                            >
                            <div class="mt-sm-0 ps-2 ps-sm-3 flex-grow-1" style="min-width: 0;">
                                <span class="badge badge-hover-glow text-white mb-2" style="background-color:{{ $item->aspek->warna }}; color:#fff; font-size:0.75rem;">
                                    {{ $item->aspek->nama ?? '-' }}
                                </span>
                                <ul class="list-unstyled text-muted mb-0" style="font-size:0.7rem;">
                                    <li class="mb-1"><i class="bi bi-building me-2"></i>{{ Str::limit($item->skpd->singkatan ?? '-', 10)}}</li>
                                    <li class="mb-1"><i class="bi bi-calendar-event me-2"></i>{{ $item->created_at->format('d M Y') }}</li>
                                    <li><i class="bi bi-eye me-2"></i>{{ $item->view }}</li>
                                </ul>
                            </div>
                        </div>
                        <div style="text-align:justify;">
                            <p class="mb-2 fw-semibold" style="font-size:0.8rem;">{{ $item->nama }}  
                                <a href="{{ Storage::disk('s3')->temporaryUrl($item->pdf, now()->addMinutes(15)) }}" type="button"
                                    class="badge bg-success text-white" style="font-size:0.75rem;text-decoration: none;"
                                    target="_blank"><i class="bi bi-download"></i></a>
                            </p>
                            <p class="card-text small text-muted mb-0" style="flex-grow:1; font-size:0.8rem;">
                                {{ Str::limit(strip_tags(Str::of($item->deskripsi)->before('</p>')->before("\n")), 80, '...') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<script>
  window.dashboardData = {
    lineLabels: @json($lineLabels),
    lineData: @json($lineData),
    donutLabels: @json($donutLabels),
    donutData: @json($donutData)
  };
</script>
