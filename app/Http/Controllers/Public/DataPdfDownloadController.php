<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class DataPdfDownloadController extends Controller
{
    /**
     * Handle dataset PDF download with survey check
     */
    public function download(Request $request, Dataset $dataset)
    {
        // Check if user has completed survey
        $sessionId = Session::getId();
        $ipAddress = $request->ip();
        
        if (!UserSurvey::hasUserCompletedSurvey($sessionId, $ipAddress)) {
            // User hasn't completed survey, redirect to show page with survey modal trigger
            return redirect()->route('public.data.show', $dataset)
                           ->with('show_survey', true)
                           ->with('download_url', route('public.data.pdf.download', $dataset));
        }

        // User has completed survey, proceed with PDF generation
        try {
            // Load data from Excel files
            $tableData = [];
            $columns = [];
            $metadata = [];

            // 1) Read metadata (sheet 1)
            $metaSheet = Excel::toArray(null, $dataset->metadata, 's3')[0];
            $metadata = collect($metaSheet)
                ->filter(fn($row) => isset($row[0], $row[1]))
                ->map(fn($row) => ['label' => $row[0], 'value' => $row[1]])
                ->values()
                ->toArray();

            // 2) Read main data (sheet 1)
            $allSheets = Excel::toArray(null, $dataset->excel, 's3');
            $rows = $allSheets[0]; // first sheet
            $rawHeader = $rows[0] ?? []; // header row

            // 3) Filter empty headers and reset index
            $header = array_values(array_filter($rawHeader, fn($col) => $col !== null && $col !== ''));
            $columns = $header;

            // 4) Map each data row according to header
            foreach (array_slice($rows, 1) as $dataRow) {
                $cells = array_slice($dataRow, 0, count($header));
                $row = array_combine($header, $cells);
                
                // Only take rows that have data in at least one column
                if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) > 0) {
                    $tableData[] = $row;
                }
            }

            // Generate PDF with blade view
            $pdf = Pdf::loadView('pdf.dataset-data', [
                'dataset' => $dataset,
                'tableData' => $tableData,
                'columns' => $columns,
                'metadata' => $metadata
            ]);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'landscape'); // Landscape because tables are usually wide

            // Generate filename
            $filename = 'Dataset_' . str_replace(' ', '_', $dataset->nama) . '_' . date('Y-m-d_H-i-s') . '.pdf';

            // Return PDF download response
            return Response::streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return redirect()->route('public.data.show', $dataset)
                           ->with('error', 'Terjadi kesalahan saat mengunduh PDF.');
        }
    }
}