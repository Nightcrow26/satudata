<?php

namespace App\Livewire\Public\Walidata;

use App\Models\Walidata;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class Show extends Component
{
    public int $page = 1;
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


    
    public function mount(Walidata $walidata): void
    {
        // Load walidata dengan relasi yang diperlukan
        $this->walidata = $walidata->load([
            'skpd', 
            'aspek', 
            'indikator', 
            'bidang', 
            'user'
        ]);
        
        // Use ViewTracker trait untuk increment view dengan session tracking
        $result = $this->walidata->incrementViewIfNotSeen();
        
        // Debug logging (remove after testing)
        \Log::info('Walidata View Tracking', [
            'id' => $this->walidata->id,
            'before_view' => $this->walidata->getOriginal('view'),
            'after_view' => $this->walidata->view,
            'increment_result' => $result,
            'session_key' => 'viewed_Walidata_' . $this->walidata->id
        ]);
    }

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

    public function render()
    {
        return view('livewire.public.walidata.show')
            ->title(($this->walidata->indikator->nama ?? 'Walidata') . ' - Indikator Walidata')
            ->layout('components.layouts.public');
    }
}
