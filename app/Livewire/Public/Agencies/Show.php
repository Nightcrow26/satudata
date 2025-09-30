<?php
// app/Livewire/Public/Agencies/Show.php

namespace App\Livewire\Public\Agencies;

use App\Models\Dataset;
use App\Models\Publikasi;
use App\Models\Skpd;
use App\Models\Walidata;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public string $slug;
    public ?Skpd $skpd = null;

    // Tab state URL (same as aspects)
    #[Url] public string $tab = 'data';        // 'data' | 'walidata' | 'publikasi'
    #[Url] public string $q = '';
    #[Url] public string $sort = 'recent';     // recent|name_asc|name_desc|oldest
    public int $perPage = 12;

    public bool $isReady = false;

    public function mount($slug)
    {
        // Find skpd by slug (generated from nama)
        $this->skpd = Skpd::all()->filter(function ($skpd) use ($slug) {
            return $skpd->slug === $slug;
        })->first();

        if (!$this->skpd) {
            abort(404, 'Instansi tidak ditemukan');
        }
    }

    /**
     * Load data dari database
     */
    public function load(): void
    {
        $this->isReady = true;
    }

    // Reset pagination kalau state berubah
    public function updated($prop): void
    {
        if (in_array($prop, ['tab', 'q', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearSearch(): void
    {
        $this->q = '';
        $this->resetPage();
    }

    /**
     * Get filtered datasets dari database
     */
    public function getFilteredDatasetsQuery()
    {
        $query = Dataset::where('instansi_id', $this->skpd->id)
            ->whereIn('status', ['published', 'approved'])
            ->with(['aspek', 'skpd', 'user']);

        // Search
        if (trim($this->q) !== '') {
            $query->where(function($q) {
                $q->where('nama', 'LIKE', '%' . $this->q . '%')
                  ->orWhere('deskripsi', 'LIKE', '%' . $this->q . '%')
                  ->orWhere('keyword', 'LIKE', '%' . $this->q . '%');
            });
        }

        // Sort
        match ($this->sort) {
            'name_asc' => $query->orderBy('nama', 'asc'),
            'name_desc' => $query->orderBy('nama', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'), // recent
        };

        return $query;
    }

    /**
     * Get filtered walidata dari database
     */
    public function getFilteredWalidataQuery()
    {
        $query = Walidata::where('skpd_id', $this->skpd->id)
            ->with(['aspek', 'skpd', 'user', 'indikator', 'bidang']);

        // Search
        if (trim($this->q) !== '') {
            $query->where(function($q) {
                $q->whereHas('indikator', function($subQ) {
                    $subQ->where('uraian_indikator', 'LIKE', '%' . $this->q . '%');
                })
                ->orWhere('data', 'LIKE', '%' . $this->q . '%')
                ->orWhere('satuan', 'LIKE', '%' . $this->q . '%')
                ->orWhere('tahun', 'LIKE', '%' . $this->q . '%');
            });
        }

        // Sort
        match ($this->sort) {
            'name_asc' => $query->join('indikators', 'walidata.indikator_id', '=', 'indikators.id')->orderBy('indikators.nama', 'asc')->select('walidata.*'),
            'name_desc' => $query->join('indikators', 'walidata.indikator_id', '=', 'indikators.id')->orderBy('indikators.nama', 'desc')->select('walidata.*'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'), // recent
        };

        return $query;
    }

    /**
     * Get filtered publikasi dari database
     */
    public function getFilteredPublikasiQuery()
    {
        $query = Publikasi::where('instansi_id', $this->skpd->id)
            ->with(['aspek', 'skpd', 'user']);

        // Search
        if (trim($this->q) !== '') {
            $query->where(function($q) {
                $q->where('nama', 'LIKE', '%' . $this->q . '%')
                  ->orWhere('deskripsi', 'LIKE', '%' . $this->q . '%')
                  ->orWhere('keyword', 'LIKE', '%' . $this->q . '%');
            });
        }

        // Sort
        match ($this->sort) {
            'name_asc' => $query->orderBy('nama', 'asc'),
            'name_desc' => $query->orderBy('nama', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'), // recent
        };

        return $query;
    }

    public function render()
    {
        if (!$this->skpd) {
            abort(404);
        }

        // Get data based on active tab
        $items = match ($this->tab) {
            'walidata' => $this->getFilteredWalidataQuery()->paginate($this->perPage),
            'publikasi' => $this->getFilteredPublikasiQuery()->paginate($this->perPage),
            default => $this->getFilteredDatasetsQuery()->paginate($this->perPage), // 'data'
        };

        // Get total counts for each tab
        $dataCounts = [
            'data' => Dataset::where('instansi_id', $this->skpd->id)->whereIn('status', ['published', 'approved'])->count(),
            'walidata' => Walidata::where('skpd_id', $this->skpd->id)->count(),
            'publikasi' => Publikasi::where('instansi_id', $this->skpd->id)->whereIn('status', ['published', 'approved'])->count(),
        ];
        
        return view('livewire.public.agencies.show', [
            'items' => $items,
            'dataCounts' => $dataCounts,
            'isDataTab' => $this->tab === 'data',
            'isWalidataTab' => $this->tab === 'walidata',
            'isPublikasiTab' => $this->tab === 'publikasi',
        ])
        ->title($this->skpd->nama . ' - Instansi')
        ->layout('components.layouts.public');
    }
}
