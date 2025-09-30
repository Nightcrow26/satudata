<?php

namespace App\Livewire\Public\Home;

use Livewire\Component;
use Livewire\Attributes\Url;

class Search extends Component
{
    #[Url(as: 'q')] // q akan tersimpan di query string (?q=)
    public string $q = '';

    public function updatedQ(string $value): void
    {
        // Rapikan input, hindari spasi berlebih
        $this->q = trim($value);
    }

    public function go()
    {
        // Enter/submit: navigasi GET ke halaman yang sama dengan ?q=
        return redirect()->route('public.home', [
            'q' => $this->q !== '' ? $this->q : null,
        ]);
    }

    public function render()
    {
        return view('public.home.parts.search-livewire');
    }
}
