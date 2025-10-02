<?php

namespace App\Livewire\Public\Walidata;

use App\Domain\Walidata\Queries\FilterWalidata;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Search and filters
    #[Url(as: 'q')] public string $q = '';
    #[Url(as: 'sort')] public string $sort = 'recent';
    #[Url(as: 'aspek')] public array $selectedAspek = [];
    #[Url(as: 'instansi')] public array $selectedInstansi = [];
    #[Url(as: 'bidang')] public array $selectedBidang = [];
    #[Url(as: 'indikator')] public array $selectedIndikator = [];

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

        // Debug: log current sort to help troubleshooting client->server binding
        Log::debug('Walidata render - sort value', ['sort' => $this->sort]);
        
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
            'sortOptions' => $this->sortOptions,
        ])->layout('components.layouts.public', [
            'title' => 'data'
        ]);
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['q', 'sort', 'aspek', 'instansi', 'bidang', 'indikator'], true)) {
            $this->resetPage();
        }
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

    /**
     * Opsi sorting yang tersedia untuk panel
     */
    public function getSortOptionsProperty(): array
    {
        return [
            'recent' => 'Terbaru',
            'oldest' => 'Terlama',
            'popular' => 'Terpopuler',
            'name' => 'Nama A-Z',
        ];
    }
}