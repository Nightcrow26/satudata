<?php

namespace App\Livewire\Public\Home;

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Dataset;
use App\Models\Publikasi;
use App\Models\Walidata;

class Search extends Component
{
    #[Url(as: 'q')] // q akan tersimpan di query string (?q=)
    public string $q = '';
    
    public array $searchResults = [];
    public bool $showResults = false;
    public int $maxResults = 8;

    public function updatedQ(string $value): void
    {
        // Rapikan input, hindari spasi berlebih
        $this->q = trim($value);
        
        // Perform search when query length >= 2
        if (strlen($this->q) >= 2) {
            $this->performSearch();
            $this->showResults = true;
        } else {
            $this->searchResults = [];
            $this->showResults = false;
        }
    }

    public function performSearch(): void
    {
        $query = $this->q;
        $results = [];

        // Search Datasets
        $datasets = Dataset::where('status', 'published')
            ->where(function($q) use ($query) {
                $q->where('nama', 'ILIKE', '%' . $query . '%')
                  ->orWhere('deskripsi', 'ILIKE', '%' . $query . '%')
                  ->orWhere('keyword', 'ILIKE', '%' . $query . '%');
            })
            ->with(['aspek', 'skpd'])
            ->take($this->maxResults)
            ->get();

        foreach ($datasets as $dataset) {
            $results[] = [
                'type' => 'dataset',
                'id' => $dataset->id,
                'title' => $dataset->nama,
                'description' => $dataset->deskripsi ? \Str::limit($dataset->deskripsi, 100) : null,
                'category' => $dataset->aspek->nama ?? 'Dataset',
                'institution' => $dataset->skpd->nama ?? 'Unknown',
                'url' => route('public.data.show', $dataset->id),
                'icon' => 'database'
            ];
        }

        // Search Publikasi
        $publikasi = Publikasi::where('status', 'published')
            ->where(function($q) use ($query) {
                $q->where('nama', 'ILIKE', '%' . $query . '%')
                  ->orWhere('deskripsi', 'ILIKE', '%' . $query . '%')
                  ->orWhere('keyword', 'ILIKE', '%' . $query . '%');
            })
            ->with(['aspek', 'skpd'])
            ->take($this->maxResults)
            ->get();

        foreach ($publikasi as $pub) {
            $results[] = [
                'type' => 'publikasi',
                'id' => $pub->id,
                'title' => $pub->nama,
                'description' => $pub->deskripsi ? \Str::limit($pub->deskripsi, 100) : null,
                'category' => $pub->aspek->nama ?? 'Publikasi',
                'institution' => $pub->skpd->nama ?? 'Unknown',
                'url' => route('public.publications.download', $pub->id),
                'icon' => 'book-open'
            ];
        }

        // Search Walidata
        $walidata = Walidata::where('skpd_id', '!=', null)
                ->where(function($q) use ($query) {
                $q->whereHas('indikator', function($subQ) use ($query) {
                    $subQ->where('uraian_indikator', 'ILIKE', '%' . $query . '%');
                })
                ->orWhere('data', 'ILIKE', '%' . $query . '%')
                ->orWhere('satuan', 'ILIKE', '%' . $query . '%');
            })
            ->with(['aspek', 'skpd', 'indikator'])
            ->take($this->maxResults)
            ->get();

        foreach ($walidata as $wal) {
            $results[] = [
                'type' => 'walidata',
                'id' => $wal->id,
                'title' => $wal->indikator->nama ?? 'Indikator Walidata',
                'description' => $wal->indikator->uraian_indikator ? \Str::limit($wal->indikator->uraian_indikator, 100) : null,
                'category' => $wal->aspek->nama ?? 'Walidata',
                'institution' => $wal->skpd->nama ?? 'Unknown',
                'url' => route('public.walidata.show', $wal->id),
                'icon' => 'chart-bar',
                'data_info' => "Tahun {$wal->tahun}: {$wal->data} {$wal->satuan}"
            ];
        }

        // Limit total results
        $this->searchResults = array_slice($results, 0, $this->maxResults);
    }

    public function clearSearch(): void
    {
        $this->q = '';
        $this->searchResults = [];
        $this->showResults = false;
    }

    public function hideResults(): void
    {
        $this->showResults = false;
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
