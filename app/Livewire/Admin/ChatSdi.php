<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ChatSdi extends Component
{
    public string $input = '';
    /** @var array<int,array{role:string,content:string,sources?:array,viz?:array,data_preview?:array,ts?:string}> */
    public array $history = [];
    public bool $isStreaming = false;
    public ?string $currentRequestId = null;

    public function startStream(): void
    {
        $msg = trim($this->input);
        if ($msg === '') return;
        
        // Prevent multiple concurrent streams
        if ($this->isStreaming) {
            \Log::warning('ChatSdi: Attempted to start stream while already streaming', [
                'current_request_id' => $this->currentRequestId,
                'is_streaming' => $this->isStreaming
            ]);
            return;
        }

        // Additional check to prevent rapid duplicate calls - reduced to 200ms for better UX
        $lastRequestTime = session('last_chat_request_time', 0);
        $currentTime = microtime(true);
        if (($currentTime - $lastRequestTime) < 0.2) { // Prevent requests within 200ms
            \Log::warning('ChatSdi: Request too soon after previous request', [
                'time_diff' => $currentTime - $lastRequestTime
            ]);
            return;
        }
        
        session(['last_chat_request_time' => $currentTime]);

        $this->isStreaming = true;
        $requestId = uniqid('chat_', true);
        $this->currentRequestId = $requestId;

        \Log::info('ChatSdi: Starting new stream', [
            'request_id' => $requestId,
            'message' => substr($msg, 0, 50),
            'is_streaming' => $this->isStreaming,
            'current_request_id' => $this->currentRequestId
        ]);

        // Tambahkan USER ke riwayat server
        $this->history[] = [
            'role'    => 'user',
            'content' => $msg,
            'ts'      => now()->toIso8601String(),
            'request_id' => $requestId,
        ];

        // Minta browser mulai SSE + kirim history agar model punya konteks
        $this->dispatch('chat-start', message: $msg, history: $this->history, requestId: $requestId);

        $this->input = '';
    }

    public function appendAssistant($content, $sources = [], $viz = [], $data_preview = [], $insights = [])
    {
        \Log::info('ChatSdi: Appending assistant response', [
            'content_length' => strlen($content),
            'sources_count' => count($sources),
            'is_streaming_before' => $this->isStreaming,
            'current_request_id' => $this->currentRequestId
        ]);

        // Prevent duplicate responses
        if (!$this->isStreaming) {
            \Log::warning('ChatSdi: Attempted to append response when not streaming');
            return;
        }

        $this->history[] = [
            'role'         => 'assistant',
            'content'      => $content,
            'sources'      => $sources,
            'viz'          => $viz,
            'data_preview' => $data_preview,
            'insights'     => $insights,
            'request_id'   => $this->currentRequestId,
        ];
        
        // Reset streaming flag when response is complete
        $this->isStreaming = false;
        $this->currentRequestId = null;
        
        \Log::info('ChatSdi: Assistant response appended, streaming reset', [
            'is_streaming_after' => $this->isStreaming,
            'current_request_id' => $this->currentRequestId
        ]);
    }

    public function clearHistory(): void
    {
        $this->history = [];
    }

    public function render()
    {
        return view('livewire.admin.chat-sdi');
    }
}

