<?php

namespace App\Livewire\Public\Data;

use App\Domain\Datasets\Queries\FilterDatasets;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Query string state
    #[Url(as: 'q')]         public string $q = '';
    #[Url(as: 'sort')]      public string $sort = 'recent';
    #[Url(as: 'aspek')]     public array  $aspek = [];
    #[Url(as: 'instansi')]  public array  $instansi = [];
    #[Url(as: 'bidang')]    public array  $bidang = [];

    // Opsi facets untuk panel kiri
    public array $aspekOptions = [];
    public array $instansiOptions = [];
    public array $bidangOptions = [];

    // Reset halaman saat filter/search berubah
    public function updating($name, $value): void
    {
        if (in_array($name, ['q','sort','aspek','instansi','bidang'], true)) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        $facets = FilterDatasets::facetOptions();
        $this->aspekOptions    = $facets['aspek'];
        $this->instansiOptions = $facets['instansi'];
        $this->bidangOptions   = $facets['bidang'];
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->sort = 'recent';
        $this->aspek = $this->instansi = $this->bidang = [];
        $this->resetPage();
    }

    /**
     * Opsi sorting yang tersedia
     */
    public function getSortOptionsProperty(): array
    {
        return [
            'recent' => 'Terbaru',
            'oldest' => 'Terlama', 
            'popular' => 'Terpopuler',
            'name' => 'Nama A-Z'
        ];
    }

   public function getDatasetsProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return app(\App\Domain\Datasets\Queries\FilterDatasets::class)->paginate(
            q: $this->q,
            sort: $this->sort,
            aspek: $this->aspek,
            instansi: $this->instansi,
            bidang: $this->bidang,
            perPage: 10,
            );
    }


    public function render()
    {
        return view('livewire.public.data.index', [
            'datasets' => $this->datasets,
            'sortOptions' => $this->sortOptions,
        ])->layout('components.layouts.public', [
            'title' => 'data'
        ]);
    }
}
