<?php

namespace App\Http\Controllers\Public\Publications;

use App\Http\Controllers\Controller;
use App\Models\Publikasi;
use App\Models\UserSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class DownloadController extends Controller
{
    public function __invoke(string $id, Request $request)
    {
        // Find publikasi by ID
        $publikasi = Publikasi::findOrFail($id);
        
        // Check if user has completed survey
        $sessionId = Session::getId();
        $ipAddress = $request->ip();
        
        // Jika user sudah login, tidak perlu mengisi survey
        if (!auth()->check() && !UserSurvey::hasUserCompletedSurvey($sessionId, $ipAddress)) {
            // Guest belum mengisi survey -> redirect ke halaman publikasi dan trigger modal survey
            return redirect()->route('public.publications.index', $publikasi)
                           ->with('show_survey', true)
                           ->with('download_url', route('public.publications.download', $publikasi));
        }
        
        // Track download (only once per session)
        $publikasi->incrementDownloadIfNotSeen();
        
        // Check if publikasi has PDF file
        if (!$publikasi->pdf) {
            abort(Response::HTTP_NOT_FOUND, 'PDF file not found');
        }
        
        // Generate S3 temporary URL and redirect to it
        try {
            $downloadUrl = Storage::disk('s3')->temporaryUrl($publikasi->pdf, now()->addMinutes(15));
            return redirect($downloadUrl);
        } catch (\Exception $e) {
            abort(Response::HTTP_NOT_FOUND, 'PDF file not accessible');
        }
    }
}
