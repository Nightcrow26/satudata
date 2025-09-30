<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Walidata;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class WalidataDownloadController extends Controller
{
    /**
     * Handle walidata download with survey check
     */
    public function download(Request $request, Walidata $walidata)
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
                           ->with('download_url', route('public.walidata.download', $walidata));
        }
        
        // Jika sudah mengisi survey, lanjutkan download
        if (!$walidata->excel) {
            return redirect()->route('public.walidata.show', $walidata)
                           ->with('error', 'File Excel tidak tersedia untuk walidata ini.');
        }
        
        try {
            // Generate temporary URL for download
            $downloadUrl = Storage::disk('s3')->temporaryUrl($walidata->excel, now()->addMinutes(15));
            
            return redirect($downloadUrl);
            
        } catch (\Exception $e) {
            return redirect()->route('public.walidata.show', $walidata)
                           ->with('error', 'Terjadi kesalahan saat mengunduh file.');
        }
    }
}
