<?php

namespace App\Http\Controllers;

use App\Http\Controllers;
use App\Services\ChatbotSdiService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotStreamController extends Controller
{
    public function stream(Request $req, ChatbotSdiService $svc): StreamedResponse
    {
        $msg = (string) $req->query('message', '');
        abort_if($msg === '', 422, 'message is required');

        // History dari query 'h' (base64 JSON)
        $h    = (string) $req->query('h', '');
        $hist = [];
        if ($h !== '') {
            $json = base64_decode($h, true);
            if (is_string($json)) {
                $hist = json_decode($json, true) ?: [];
            }
        }

        $headers = [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-transform',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ];

        return response()->stream(function () use ($svc, $msg, $hist) {
            $emit = function (string $event, $data) {
                echo "event: {$event}\n";
                echo "data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
                @ob_flush(); @flush();
            };
            $keepAlive = function () { echo ": ping\n\n"; @ob_flush(); @flush(); };

            // Beri konteks history ke service
            $svc->stream($msg, ['history' => $hist], $emit, $keepAlive);

            $emit('done', ['ok' => true]);
        }, 200, $headers);
    }
}