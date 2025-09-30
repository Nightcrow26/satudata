<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DownloadSurveyController extends Controller
{
    /**
     * Check if user needs to complete survey before download
     */
    public function checkSurveyRequired(Request $request)
    {
        $sessionId = Session::getId();
        $ipAddress = $request->ip();
        
        // Check if survey completed in this session
        $surveyCompleted = Session::get('survey_completed', false);
        
        // Or check if user has completed survey from database
        if (!$surveyCompleted) {
            $surveyCompleted = UserSurvey::hasUserCompletedSurvey($sessionId, $ipAddress);
            if ($surveyCompleted) {
                Session::put('survey_completed', true);
            }
        }
        
        return response()->json([
            'survey_required' => !$surveyCompleted
        ]);
    }

    /**
     * Submit survey and mark as completed
     */
    public function submitSurvey(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000'
        ]);

        try {
            UserSurvey::createSurvey([
                'session_id' => Session::getId(),
                'ip_address' => $request->ip(),
                'rating' => $request->rating,
                'feedback' => $request->feedback,
                'user_agent' => $request->userAgent(),
            ]);

            // Mark survey as completed for this session
            Session::put('survey_completed', true);

            return response()->json([
                'success' => true,
                'message' => 'Survey berhasil disimpan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan survey'
            ], 500);
        }
    }
}
