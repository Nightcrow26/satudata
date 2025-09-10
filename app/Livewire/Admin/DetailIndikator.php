<?php

namespace App\Livewire\Admin;

use App\Models\Walidata;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Str;

class DetailIndikator extends Component
{
    use WithPagination;

    public Walidata $walidata; // satu baris utama
    public array $chartData = [];
    public array $allTableData = [];
    public array $metadata = [];
    public array $columns = ['tahun', 'data'];
    public string $xAxis = 'tahun';
    public string $yAxis = 'data';
    public int $perPage = 10;
    public int $chartPerPage = 10;
    public array $perPageOptions = [10, 25, 50, 100];

    protected $queryString = [
        'perPage' => ['except' => 10],
        'chartPerPage' => ['except' => 10],
        'page' => ['except' => 1]
    ];

    public function mount(Walidata $walidata): void
    {
        // simpan baris utama walidata (sudah terbind dari route model binding)
        $this->walidata = $walidata->load(['skpd', 'aspek', 'bidang', 'indikator']);

        // isi metadata
        $this->buildMetadata($this->walidata);

        // ambil semua walidata berdasarkan indikator_id untuk tabel/grafik
        $this->allTableData = Walidata::where('indikator_id', $this->walidata->indikator_id)
            ->with(['skpd', 'indikator'])
            ->orderBy('tahun')
            ->get()
            ->map(fn($item) => [
                'tahun' => $item->tahun,
                'Uraian' => $item->indikator->uraian_indikator,
                'data' => $item->data,
                'satuan' => $item->satuan,
                'verifikasi_data' => $item->verifikasi_data ? 'Terverifikasi' : 'Belum Terverifikasi',
                'skpd' => $item->skpd->nama ?? '-',
            ])
            ->toArray();

        // isi chart
        $this->updateChart();
    }

    private function buildMetadata(Walidata $walidata): void
    {
        $this->metadata = [
            ['label' => 'No. Rekomendasi Statistik', 'value' => '-'],
            ['label' => 'Judul Data', 'value' => $walidata->indikator->uraian_indikator ?? '-'],
            ['label' => 'Data di Buat', 'value' => $walidata->created_at?->format('Y') ?? '-'],
            ['label' => 'Data di Perbaharui', 'value' => $walidata->updated_at?->format('Y') ?? '-'],
            ['label' => 'Penyelenggara', 'value' => $walidata->skpd->nama ?? '-'],
            ['label' => 'Alamat Penyelenggara', 'value' => $walidata->skpd->alamat ?? '-'],
            ['label' => 'Penanggung Jawab', 'value' => '-'],
            ['label' => 'Jabatan Penanggung Jawab', 'value' => '-'],
            ['label' => 'Tujuan Kegiatan', 'value' => '-'],
            ['label' => 'Frekuensi Penyelenggaraan', 'value' => '-'],
            ['label' => 'Frekuensi Pengumpulan Data', 'value' => 'Tahunan'],
            ['label' => 'Variabel Utama', 'value' => '-'],
            ['label' => 'Cakupan Wilayah', 'value' => 'Kabupaten Hulu Sungai Utara'],
            ['label' => 'Cara Pengumpulan Data', 'value' => '-'],
            ['label' => 'Petugas Pengumpulan Data', 'value' => '-'],
        ];
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedChartPerPage()
    {
        // Tidak melakukan apa-apa, harus klik terapkan dulu
    }

    public function updatedXAxis()
    {
        $this->updateChart();
    }

    public function updatedYAxis()
    {
        $this->updateChart();
    }

    public function updateChart(): void
    {
        $currentPageData = $this->getChartPaginatedData();

        $this->chartData = [];
        foreach ($currentPageData as $row) {
            if (isset($row[$this->xAxis], $row[$this->yAxis])) {
                $yValue = is_numeric($row[$this->yAxis]) ? (float) $row[$this->yAxis] : 0;
                $this->chartData[] = [
                    'x' => $row[$this->xAxis],
                    'y' => $yValue,
                ];
            }
        }

        $this->dispatch('chartDataUpdated', $this->chartData);
    }

    private function getChartPaginatedData()
    {
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->chartPerPage;

        return array_slice($this->allTableData, $offset, $this->chartPerPage);
    }

    private function getPaginatedData()
    {
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;

        return array_slice($this->allTableData, $offset, $this->perPage);
    }

    public function getPaginatedTableData()
    {
        $total = count($this->allTableData);
        $currentPage = $this->getPage();

        $items = collect($this->getPaginatedData());

        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function getChartPaginatedTableData()
    {
        $total = count($this->allTableData);
        $currentPage = $this->getPage();

        $items = collect($this->getChartPaginatedData());

        return new LengthAwarePaginator(
            $items,
            $total,
            $this->chartPerPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function render()
    {
        $paginatedData = $this->getPaginatedTableData();
        $chartPaginatedData = $this->getChartPaginatedTableData();

        return view('livewire.admin.detail-indikator', [
            'tableData' => $paginatedData->items(),
            'datasets' => $paginatedData,
            'chartDatasets' => $chartPaginatedData,
        ]);
    }

    // Adaptor: bentuk object yang kompatibel dengan view pdf.dataset-data
    protected function makeDatasetForPdf(): object
    {
        // bentuk object mirip model Dataset agar view pdf tidak perlu diubah
        return (object) [
            'nama'       => $this->walidata->indikator->uraian_indikator ?? 'Detail Indikator',
            'created_at' => $this->walidata->created_at,
            // relasi pseudo agar $dataset->skpd->nama dan $dataset->aspek->nama tetap jalan
            'skpd'       => (object) [
                                'nama'   => $this->walidata->skpd->nama ?? null,
                                'alamat' => $this->walidata->skpd->alamat ?? null,
                            ],
            'aspek'      => (object) [
                                'nama'   => $this->walidata->aspek->nama ?? null,
                            ],
        ];
    }

    public function downloadPdf()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.dataset-data', [
            'dataset'   => $this->makeDatasetForPdf(), // <- penting
            'columns'   => $this->columns,
            'tableData' => $this->allTableData,
            'metadata'  => $this->metadata,
        ])->setPaper('a4', 'landscape');

        $filename = 'detail-indikator-' . Str::slug($this->walidata->indikator->uraian_indikator ?? 'indikator') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function downloadExcel()
    {
        $columns = $this->columns;

        $rows = array_map(function ($row) use ($columns) {
            $ordered = [];
            foreach ($columns as $col) {
                $ordered[] = $row[$col] ?? null;
            }
            return $ordered;
        }, $this->allTableData);

        $export = new class($columns, $rows) implements FromArray, WithHeadings, ShouldAutoSize {
            public function __construct(private array $columns, private array $rows) {}
            public function array(): array { return $this->rows; }
            public function headings(): array { return $this->columns; }
        };

        $filename = 'detail-indikator-' . Str::slug($this->walidata->indikator->uraian_indikator ?? 'indikator') . '.xlsx';
        return Excel::download($export, $filename);
    }
}
