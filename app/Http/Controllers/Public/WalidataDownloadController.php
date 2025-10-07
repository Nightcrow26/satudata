<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Walidata;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Barryvdh\DomPDF\Facade\Pdf;

class WalidataDownloadController extends Controller
{
    /**
     * Handle walidata download with survey check
     */
    public function download(Request $request, Walidata $walidata, string $format = 'excel')
    {
        // Cek apakah user sudah mengisi survey
        $sessionId = Session::getId();
        $ipAddress = $request->ip();
        
        $surveyCompleted = Session::get('survey_completed', false);
        
        if (!$surveyCompleted) {
            $surveyCompleted = UserSurvey::hasUserCompletedSurvey($sessionId, $ipAddress);
            if ($surveyCompleted) {
                Session::put('survey_completed', true);
            }
        }
        
        // Jika belum mengisi survey, redirect ke halaman walidata dengan parameter survey
        if (!$surveyCompleted) {
            return redirect()->route('public.walidata.show', $walidata)
                           ->with('show_survey', true)
                           ->with('download_url', route('public.walidata.download', [$walidata, $format]));
        }
        
        try {
            // Increment download counter
            $walidata->increment('view');
            
            // Generate file based on format
            if ($format === 'pdf') {
                return $this->downloadPdf($walidata);
            } else {
                return $this->downloadExcel($walidata);
            }
            
        } catch (\Exception $e) {
            \Log::error('Walidata download failed', [
                'walidata_id' => $walidata->id,
                'format' => $format,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('public.walidata.show', $walidata)
                           ->with('error', 'Terjadi kesalahan saat mengunduh file: ' . $e->getMessage());
        }
    }

    /**
     * Download walidata as Excel file
     */
    public function downloadExcel(Walidata $walidata)
    {
        // Load walidata with relations
        $walidata->load(['skpd', 'aspek', 'bidang', 'indikator']);
        
        // Get all related walidata by indikator_id for complete dataset
        $allWalidataData = Walidata::where('indikator_id', $walidata->indikator_id)
            ->with(['skpd', 'indikator'])
            ->orderBy('tahun')
            ->get()
            ->map(fn($item) => [
                'tahun' => $item->tahun,
                'uraian_indikator' => $item->indikator->uraian_indikator ?? '-',
                'data' => $item->data,
                'satuan' => $item->satuan,
                'status_verifikasi' => $item->verifikasi_data ? 'Terverifikasi' : 'Belum Terverifikasi',
                'skpd' => $item->skpd->nama ?? '-',
                'aspek' => $item->aspek->nama ?? '-',
                'bidang' => $item->bidang->nama ?? '-',
            ])
            ->toArray();

        $columns = ['tahun', 'uraian_indikator', 'data', 'satuan', 'status_verifikasi', 'skpd', 'aspek', 'bidang'];
        
        $rows = array_map(function ($row) use ($columns) {
            $ordered = [];
            foreach ($columns as $col) {
                $ordered[] = $row[$col] ?? '-';
            }
            return $ordered;
        }, $allWalidataData);

        $export = new class($columns, $rows) implements FromArray, WithHeadings, ShouldAutoSize {
            public function __construct(private array $columns, private array $rows) {}
            public function array(): array { return $this->rows; }
            public function headings(): array { 
                return [
                    'Tahun',
                    'Uraian Indikator', 
                    'Data',
                    'Satuan',
                    'Status Verifikasi',
                    'SKPD',
                    'Aspek',
                    'Bidang'
                ];
            }
        };

        $filename = 'walidata-' . Str::slug($walidata->indikator->uraian_indikator ?? 'data') . '-' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download($export, $filename);
    }

    /**
     * Download walidata as PDF file
     */
    public function downloadPdf(Walidata $walidata)
    {
        // Load walidata with relations
        $walidata->load(['skpd', 'aspek', 'bidang', 'indikator']);
        
        // Get all related walidata by indikator_id
        $allWalidataData = Walidata::where('indikator_id', $walidata->indikator_id)
            ->with(['skpd', 'indikator', 'aspek', 'bidang'])
            ->orderBy('tahun')
            ->get()
            ->map(fn($item) => [
                'tahun' => $item->tahun,
                'uraian_indikator' => $item->indikator->uraian_indikator ?? '-',
                'data' => $item->data,
                'satuan' => $item->satuan,
                'status_verifikasi' => $item->verifikasi_data ? 'Terverifikasi' : 'Belum Terverifikasi',
                'skpd' => $item->skpd->nama ?? '-',
            ])
            ->toArray();

        // Create dataset object for PDF view compatibility
        $dataset = (object) [
            'nama' => $walidata->indikator->uraian_indikator ?? 'Data Walidata',
            'created_at' => $walidata->created_at,
            'skpd' => (object) [
                'nama' => $walidata->skpd->nama ?? '-',
                'alamat' => $walidata->skpd->alamat ?? '-',
            ],
            'aspek' => (object) [
                'nama' => $walidata->aspek->nama ?? '-',
            ],
        ];

        // Build metadata
        $metadata = [
            ['label' => 'Judul Data', 'value' => $walidata->indikator->uraian_indikator ?? '-'],
            ['label' => 'Data Dibuat', 'value' => $walidata->created_at?->format('Y') ?? '-'],
            ['label' => 'Data Diperbaharui', 'value' => $walidata->updated_at?->format('Y') ?? '-'],
            ['label' => 'Penyelenggara', 'value' => $walidata->skpd->nama ?? '-'],
            ['label' => 'Alamat Penyelenggara', 'value' => $walidata->skpd->alamat ?? '-'],
            ['label' => 'Aspek', 'value' => $walidata->aspek->nama ?? '-'],
            ['label' => 'Bidang', 'value' => $walidata->bidang->nama ?? '-'],
            ['label' => 'Cakupan Wilayah', 'value' => 'Kabupaten Hulu Sungai Utara'],
            ['label' => 'Frekuensi Pengumpulan Data', 'value' => 'Tahunan'],
        ];

        $columns = ['tahun', 'uraian_indikator', 'data', 'satuan', 'status_verifikasi', 'skpd'];

        $pdf = Pdf::loadView('pdf.dataset-data', [
            'dataset' => $dataset,
            'columns' => $columns,
            'tableData' => $allWalidataData,
            'metadata' => $metadata,
        ])->setPaper('a4', 'landscape');

        $filename = 'walidata-' . Str::slug($walidata->indikator->uraian_indikator ?? 'data') . '-' . now()->format('Y-m-d') . '.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }
}
