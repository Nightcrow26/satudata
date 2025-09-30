<?php
// app/Livewire/Public/Agencies/Index.php

namespace App\Livewire\Public\Agencies;

use App\Models\Skpd;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $q = '';
    #[Url] public string $sort = 'recent';
    public int $perPage = 12;

    protected function getSkpdQuery()
    {
        // Query SKPD hanya yang merupakan induk (unor_induk_id = id)
        $query = Skpd::whereColumn('id', 'unor_induk_id')
            ->withCount([
                'datasets' => function ($q) {
                    $q->whereIn('status', ['published', 'approved']);
                },
                'publikasis' => function ($q) {
                    $q->whereIn('status', ['published', 'approved']);
                },
                'walidata'
            ]);

        // Search berdasarkan nama atau singkatan
        $q = trim($this->q);
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('nama', 'ilike', "%{$q}%")
                        ->orWhere('singkatan', 'ilike', "%{$q}%");
            });
        }

        // Sorting
        switch ($this->sort) {
            case 'name':
                $query->orderBy('nama', 'asc');
                break;
            case 'data':
                $query->orderByDesc('datasets_count')
                      ->orderByDesc('publikasis_count')
                      ->orderByDesc('walidata_count');
                break;
            case 'recent':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    public function updated($prop): void
    {
        // setiap perubahan state reset halaman (best practice)
        if (in_array($prop, ['q', 'sort'], true)) {
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
        // Ambil data SKPD dengan pagination langsung dari database
        $agencies = $this->getSkpdQuery()->paginate($this->perPage);

        // Transform data untuk compatibility dengan template
        $agencies->getCollection()->transform(function ($skpd) {
            return [
                'id' => $skpd->id,
                'name' => $skpd->nama,
                'singkatan' => $skpd->singkatan,
                'logo' => $skpd->foto_url ?: asset('images/placeholders/agency-badge.svg'),
                'dataset' => $skpd->datasets_count,
                'pubs' => $skpd->publikasis_count,
                'walidata' => $skpd->walidata_count,
                'updated' => $skpd->updated_at,
                'views' => null, // SKPD tidak memiliki views
                'href' => route('public.agencies.show', ['slug' => Str::slug($skpd->nama)]),
            ];
        });

        return view('livewire.public.agencies.index', [
            'agencies' => $agencies,
            'total' => $agencies->total(),
        ]);
    }
}