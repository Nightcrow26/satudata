<div>
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        {{-- Header --}}
        <x-public.data.detail-header :dataset=$dataset class="mb-2" />

        {{-- GRID: Konten Utama + Side Panel --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            {{-- Konten Utama: tetap di kanan saat desktop --}}
            <section class="order-1 md:order-2 md:col-span-9">
                <livewire:public.data.detail-content :dataset-id="$dataset->id ?? null" />
            </section>

            {{-- Side Panel: tampil di mobile SETELAH konten, sticky di desktop --}}
            <aside class="order-2 md:order-1 md:col-span-3 md:sticky md:top-24">
                <x-public.data.detail-sidepanel :dataset="[
                        'agency' => [
                            'name' => $dataset->skpd->nama ?? 'Instansi Tidak Diketahui',
                            'logo' => $dataset->skpd->logo_url ?? null,
                        ],
                        'meta' => [
                            ['label' => 'Format', 'value' => $dataset->excel ? 'Excel/CSV' : 'Tidak Ada'],
                            ['label' => 'Ukuran', 'value' => $dataset->excel ? $this->formatFileSize($dataset->excel) : '-'],
                            ['label' => 'Sumber', 'value' => $dataset->skpd->singkatan ?? $dataset->skpd->nama ?? '-'],
                            ['label' => 'Aspek', 'value' => $dataset->aspek->nama ?? '-'],
                            ['label' => 'Tahun', 'value' => $dataset->tahun ?? '-'],
                            ['label' => 'Status', 'value' => ucfirst($dataset->status ?? '-')],
                            ['label' => 'Dibuat', 'value' => $dataset->created_at ? $dataset->created_at->translatedFormat('d F Y') : '-'],
                            ['label' => 'Views', 'value' => number_format($dataset->view ?? 0, 0, ',', '.')],
                        ],
                    ]" />
            </aside>
        </div>
    </div>
</div>