<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class ChatSdi extends Component
{
    public string $input = '';
    /** @var array<int,array{role:string,content:string,sources?:array,viz?:array,data_preview?:array,ts?:string}> */
    public array $history = [];

    public function startStream(): void
    {
        $msg = trim($this->input);
        if ($msg === '') return;

        // Tambahkan USER ke riwayat server
        $this->history[] = [
            'role'    => 'user',
            'content' => $msg,
            'ts'      => now()->toIso8601String(),
        ];

        // Minta browser mulai SSE + kirim history agar model punya konteks
        $this->dispatch('chat-start', message: $msg, history: $this->history);

        $this->input = '';
    }

    public function appendAssistant($content, $sources = [], $viz = [], $data_preview = [], $insights = [])
    {
        $this->history[] = [
            'role'         => 'assistant',
            'content'      => $content,
            'sources'      => $sources,
            'viz'          => $viz,
            'data_preview' => $data_preview,
            'insights'     => $insights,
        ];
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

