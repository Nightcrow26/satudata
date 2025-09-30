<?php
// app/Livewire/Public/Aspects/Show.php

namespace App\Livewire\Public\Aspects;

use App\Models\Aspek;
use App\Models\Dataset;
use App\Models\Walidata;
use App\Models\Publikasi;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public string $slug;
    public ?Aspek $aspek = null;

    // Tab state URL (similar to agencies)
    #[Url] public string $tab = 'data';        // 'data' | 'walidata' | 'publikasi'
    #[Url] public string $q = '';
    #[Url] public string $sort = 'recent';     // recent|name_asc|name_desc|oldest
    public int $perPage = 12;

    public bool $isReady = false;

    public function mount($slug)
    {
        // Find aspek by slug (generated from nama)
        $this->aspek = Aspek::all()->filter(function ($aspek) use ($slug) {
            return $aspek->slug === $slug;
        })->first();

        if (!$this->aspek) {
            abort(404, 'Aspek tidak ditemukan');
        }
    }

    /**
     * Load data dari database
     */
    public function load(): void
    {
        $this->isReady = true;
    }

    /**
     * Get filtered datasets dari database
     */
    public function getFilteredDatasetsQuery()
    {
        $query = Dataset::where('aspek_id', $this->aspek->id)
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
        $query = Walidata::where('aspek_id', $this->aspek->id)
            ->with(['aspek', 'skpd', 'user', 'indikator', 'bidang']);

        // Search
        if (trim($this->q) !== '') {
            $query->where(function($q) {
                $q->whereHas('indikator', function($subQ) {
                    $subQ->where('nama', 'LIKE', '%' . $this->q . '%');
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
        $query = Publikasi::where('aspek_id', $this->aspek->id)
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

    public function render()
    {
        if (!$this->aspek) {
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
            'data' => Dataset::where('aspek_id', $this->aspek->id)->whereIn('status', ['published', 'approved'])->count(),
            'walidata' => Walidata::where('aspek_id', $this->aspek->id)->count(),
            'publikasi' => Publikasi::where('aspek_id', $this->aspek->id)->whereIn('status', ['published', 'approved'])->count(),
        ];
        
        return view('livewire.public.aspects.show', [
            'items' => $items,
            'dataCounts' => $dataCounts,
            'isDataTab' => $this->tab === 'data',
            'isWalidataTab' => $this->tab === 'walidata',
            'isPublikasiTab' => $this->tab === 'publikasi',
        ])
        ->title($this->aspek->nama . ' - Aspek Data')
        ->layout('components.layouts.public');
    }
}
