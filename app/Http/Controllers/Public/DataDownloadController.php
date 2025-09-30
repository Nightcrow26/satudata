<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dataset;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class DataDownloadController extends Controller
{
    /**
     * Handle dataset download with survey check
     */
    public function download(Request $request, Dataset $dataset)
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
        
        // Jika belum mengisi survey, redirect ke halaman dataset dengan parameter survey
        if (!$surveyCompleted) {
            return redirect()->route('public.data.show', $dataset)
                           ->with('show_survey', true)
                           ->with('download_url', route('public.data.download', $dataset));
        }
        
        // Jika sudah mengisi survey, lanjutkan download
        if (!$dataset->excel) {
            return redirect()->route('public.data.show', $dataset)
                           ->with('error', 'File Excel tidak tersedia untuk dataset ini.');
        }
        
        try {
            // Generate temporary URL for download
            $downloadUrl = Storage::disk('s3')->temporaryUrl($dataset->excel, now()->addMinutes(15));
            
            // Track download if needed (similar download tracking could be added here)
            
            return redirect($downloadUrl);
            
        } catch (\Exception $e) {
            return redirect()->route('public.data.show', $dataset)
                           ->with('error', 'Terjadi kesalahan saat mengunduh file.');
        }
    }
}
