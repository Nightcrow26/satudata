<div>
 <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        {{-- Header --}}
        <x-public.walidata.detail-header :walidata="$walidata" class="mb-2" />

        {{-- GRID: Konten Utama + Side Panel --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            {{-- Konten Utama: tetap di kanan saat desktop --}}
            <section class="order-1 md:order-2 md:col-span-9">
                <livewire:public.walidata.detail-content :walidata="$walidata" />
            </section>
            
             @php
                use Carbon\Carbon;
                $verifDate = null;
                if (! empty($walidata->verifikasi_data)) {
                    try {
                        $verifDate = $walidata->verifikasi_data instanceof \Illuminate\Support\Carbon ? $walidata->verifikasi_data : Carbon::parse($walidata->verifikasi_data);
                    } catch (\Exception $e) {
                        $verifDate = null;
                    }
                }
            @endphp
            {{-- Side Panel: tampil di mobile SETELAH konten, sticky di desktop --}}
            <aside class="order-2 md:order-1 md:col-span-3 md:sticky md:top-24">
                <x-public.walidata.detail-sidepanel :walidata="[
                        'agency' => [
                            'name' => $walidata->skpd->nama ?? 'Produsen Data Tidak Diketahui',
                            'logo' => $walidata->skpd->logo_url ?? null,
                        ],
                        'meta' => [
                            ['label' => 'Aspek', 'value' => $walidata->aspek->nama ?? '-'],
                            ['label' => 'Tahun', 'value' => $walidata->tahun ?? '-'],
                            ['label' => 'Data', 'value' => $walidata->data ?? '-'],
                            ['label' => 'Satuan', 'value' => $walidata->satuan ?? '-'],
                            ['label' => 'Status', 'value' => $walidata->verifikasi_data ? 'Terverifikasi' : 'Belum Terverifikasi'],
                            ['label' => 'Dibuat', 'value' => $walidata->created_at ? $verifDate->translatedFormat('d F Y') : '-'],
                        ],
                    ]" />
            </aside>
        </div>
    </div>
</div>
