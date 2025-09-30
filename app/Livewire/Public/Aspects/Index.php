<?php
// app/Livewire/Public/Aspects/Index.php

namespace App\Livewire\Public\Aspects;

use Livewire\Component;
use Livewire\Attributes\Url;
use App\Models\Aspek;
use Illuminate\Support\Str;

class Index extends Component
{
    #[Url] public string $q = '';
    #[Url] public string $sort = 'recent'; // recent | name_asc | name_desc

    public bool $isReady = false;

    /** @var array<int, array{name:string,slug:string,datasets_count:int,icon:?string,href:?string,updated_at:string}> */
    public array $aspects = [];

    public function load(): void
    {
        $this->aspects = Aspek::query()
                                ->withCount([
                                    'publikasis as publikasis_count' => fn($q) => $q->where('status', 'published'),
                                    'walidata as walidatas_count',
                                    'datasets as datasets_count' => fn($q) => $q->where('status', 'published'),
                                ])
                                ->orderBy('nama')
                                ->get()
                                ->toArray();

        $this->isReady = true;
    }

    /** @return array<int, array<string,mixed>> */
    public function getVisibleAspects(): array
    {
        $list = collect($this->aspects);

        // search
        if (trim($this->q) !== '') {
            $q = mb_strtolower($this->q);
            $list = $list->filter(fn ($a) => str_contains(mb_strtolower($a['name']), $q));
        }

        // sort
        $list = match ($this->sort) {
            'name_asc'  => $list->sortBy(fn($a) => mb_strtolower($a['name']))->values(),
            'name_desc' => $list->sortByDesc(fn($a) => mb_strtolower($a['name']))->values(),
            default     => $list->sortByDesc('updated_at')->values(), // recent
        };

        return $list->all();
    }

    public function updatedQ(): void   { /* reset pagination nanti jika dipakai */ }
    public function updatedSort(): void{ /* reset pagination nanti jika dipakai */ }

    public function render()
    {
        return view('livewire.public.aspects.index', [
            'items' => $this->getVisibleAspects(),
        ]);
    }
}
