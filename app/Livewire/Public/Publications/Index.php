<?php

namespace App\Livewire\Public\Publications;

use App\Domain\Publications\Queries\FilterPublications;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Query string state
    #[Url(as: 'q')]        public string $q = '';
    #[Url(as: 'sort')]     public string $sort = 'recent';
    #[Url(as: 'jenis')]    public array  $jenis = [];
    #[Url(as: 'instansi')] public array  $instansi = [];
    #[Url(as: 'bidang')]   public array  $bidang = [];

    // Opsi facets untuk panel kiri
    public array $jenisOptions = [];
    public array $instansiOptions = [];
    public array $bidangOptions = [];

    // Reset halaman saat filter/search berubah
    public function updating($name, $value): void
    {
        if (in_array($name, ['q', 'sort', 'jenis', 'instansi', 'bidang'], true)) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        $facets = FilterPublications::facetOptions();
        $this->jenisOptions     = $facets['jenis'] ?? [];
        $this->instansiOptions  = $facets['instansi'] ?? [];
        $this->bidangOptions    = $facets['bidang'] ?? [];
    }

    public function resetFilters(): void
    {
        $this->q = '';
        $this->sort = 'recent';
        $this->jenis = $this->instansi = $this->bidang = [];
        $this->resetPage();
    }

    public function getPublicationsProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return app(FilterPublications::class)->paginate(
            q: $this->q,
            sort: $this->sort,
            jenis: $this->jenis,
            instansi: $this->instansi,
            bidang: $this->bidang,
            perPage: 10,
        );
    }

    public function render()
    {
        return view('livewire.public.publications.index', [
            'publications' => $this->publications,
        ]);
    }
}
