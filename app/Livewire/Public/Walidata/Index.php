<?php

namespace App\Livewire\Public\Walidata;

use App\Domain\Walidata\Queries\FilterWalidata;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Search and filters
    public string $q = '';
    public string $sort = 'recent';
    public array $selectedAspek = [];
    public array $selectedInstansi = [];
    public array $selectedBidang = [];
    public array $selectedIndikator = [];

    // Available filter options from database
    public array $aspekOptions = [];
    public array $instansiOptions = [];
    public array $bidangOptions = [];
    public array $indikatorOptions = [];

    protected $queryString = [
        'q' => ['except' => ''],
        'sort' => ['except' => 'recent'],
        'selectedAspek' => ['except' => []],
        'selectedInstansi' => ['except' => []],
        'selectedBidang' => ['except' => []],
        'selectedIndikator' => ['except' => []],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->loadFilterOptions();
    }

    public function render()
    {
        $filterWalidata = app(FilterWalidata::class);
        
        $walidata = $filterWalidata->paginate(
            q: $this->q,
            sort: $this->sort,
            aspek: $this->selectedAspek,
            instansi: $this->selectedInstansi,
            bidang: $this->selectedBidang,
            indikator: $this->selectedIndikator,
            perPage: 12
        );

        return view('livewire.public.walidata.index', [
            'walidata' => $walidata,
        ])->layout('components.layouts.public', [
            'title' => 'data'
        ]);
    }

    public function updatedQ()
    {
        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function updatedSelectedAspek()
    {
        $this->resetPage();
    }

    public function updatedSelectedInstansi()
    {
        $this->resetPage();
    }

    public function updatedSelectedBidang()
    {
        $this->resetPage();
    }

    public function updatedSelectedIndikator()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            'q',
            'selectedAspek',
            'selectedInstansi', 
            'selectedBidang',
            'selectedIndikator',
        ]);
        $this->sort = 'recent';
        $this->resetPage();
    }

    private function loadFilterOptions()
    {
        $filterWalidata = app(FilterWalidata::class);
        $options = $filterWalidata->facetOptions();
        
        $this->aspekOptions = $options['aspek'];
        $this->instansiOptions = $options['instansi'];
        $this->bidangOptions = $options['bidang'];
        $this->indikatorOptions = $options['indikator'];
    }
}