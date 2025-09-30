<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Walidata;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class WalidataPdfDownloadController extends Controller
{
    /**
     * Handle walidata PDF download with survey check
     */
    public function download(Request $request, Walidata $walidata)
    {
        // Check if user has completed survey
        $sessionId = Session::getId();
        $ipAddress = $request->ip();
        
        if (!UserSurvey::hasUserCompletedSurvey($sessionId, $ipAddress)) {
            // User hasn't completed survey, redirect to show page with survey modal trigger
            return redirect()->route('public.walidata.show', $walidata)
                           ->with('show_survey', true)
                           ->with('download_url', route('public.walidata.pdf.download', $walidata));
        }

        // User has completed survey, proceed with PDF generation
        try {
            // Load data from Excel file
            $tableData = [];
            $columns = [];
            $metadata = [];

            // Check if walidata has excel file
            if (!$walidata->excel) {
                return redirect()->route('public.walidata.show', $walidata)
                               ->with('error', 'File Excel tidak tersedia untuk indikator ini.');
            }

            // Read Excel file
            $allSheets = Excel::toArray(null, $walidata->excel, 's3');
            $rows = $allSheets[0]; // first sheet
            $rawHeader = $rows[0] ?? []; // header row

            // Filter empty headers and reset index
            $header = array_values(array_filter($rawHeader, fn($col) => $col !== null && $col !== ''));
            $columns = $header;

            // Map each data row according to header
            foreach (array_slice($rows, 1) as $dataRow) {
                $cells = array_slice($dataRow, 0, count($header));
                $row = array_combine($header, $cells);
                
                // Only take rows that have data in at least one column
                if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) > 0) {
                    $tableData[] = $row;
                }
            }

            // Generate PDF with blade view
            $pdf = Pdf::loadView('pdf.walidata-data', [
                'walidata' => $walidata,
                'tableData' => $tableData,
                'columns' => $columns,
                'metadata' => $metadata
            ]);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'landscape'); // Landscape because tables are usually wide

            // Generate filename
            $filename = 'Walidata_' . str_replace(' ', '_', $walidata->indikator->uraian_indikator ?? 'Data') . '_' . date('Y-m-d_H-i-s') . '.pdf';

            // Return PDF download response
            return Response::streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            return redirect()->route('public.walidata.show', $walidata)
                           ->with('error', 'Terjadi kesalahan saat mengunduh PDF.');
        }
    }
}