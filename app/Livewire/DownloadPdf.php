<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Dataset;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class DownloadPdf extends Component
{
    public Dataset $dataset;
    public array $tableData = [];
    public array $columns = [];
    public array $metadata = [];

    public function mount(Dataset $dataset)
    {
        $this->dataset = $dataset;
        $this->loadData();
    }

    private function loadData()
    {
        // 1) Baca metadata (sheet 1)
        $metaSheet = Excel::toArray(null, $this->dataset->metadata, 's3')[0];
        $this->metadata = collect($metaSheet)
            ->filter(fn($row) => isset($row[0], $row[1]))
            ->map(fn($row) => ['label' => $row[0], 'value' => $row[1]])
            ->values()
            ->toArray();

        // 2) Baca data utama (sheet 1)
        $allSheets = Excel::toArray(null, $this->dataset->excel, 's3');
        $rows = $allSheets[0]; // sheet pertama
        $rawHeader = $rows[0] ?? []; // baris header

        // 3) Filter header kosong dan reset index
        $header = array_values(array_filter($rawHeader, fn($col) => $col !== null && $col !== ''));
        $this->columns = $header;

        // 4) Mapping setiap baris data sesuai header
        $this->tableData = [];
        foreach (array_slice($rows, 1) as $dataRow) {
            $cells = array_slice($dataRow, 0, count($header));
            $row = array_combine($header, $cells);
            
            // Hanya ambil baris yang memiliki data minimal di satu kolom
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) > 0) {
                $this->tableData[] = $row;
            }
        }
    }

    public function downloadPdf()
    {
        try {
            // Generate PDF dengan view blade
            $pdf = Pdf::loadView('pdf.dataset-data', [
                'dataset' => $this->dataset,
                'tableData' => $this->tableData,
                'columns' => $this->columns,
                'metadata' => $this->metadata
            ]);

            // Set paper size dan orientation
            $pdf->setPaper('A4', 'landscape'); // Landscape karena tabel biasanya lebar

            // Generate filename
            $filename = 'Dataset_' . str_replace(' ', '_', $this->dataset->nama) . '_' . date('Y-m-d_H-i-s') . '.pdf';

            // Return PDF download response
            return Response::streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            // Log error dan kirim notifikasi ke user
            \Log::error('PDF Download Error: ' . $e->getMessage());
            
            session()->flash('error', 'Gagal mengunduh PDF. Silakan coba lagi.');
            return null;
        }
    }

    public function render()
    {
        return view('livewire.download-pdf');
    }
}