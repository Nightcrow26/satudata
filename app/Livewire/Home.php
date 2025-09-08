<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Aspek;
use App\Models\Dataset;
use App\Models\Skpd;
use App\Models\Publikasi;
use Carbon\Carbon;
use App\Models\Walidata;

#[Title('Dashboard')]
class Home extends Component
{
    public int $aspekCount;
    public int $datasetCount;
    public int $instansiCount;
    public int $publikasiCount;
    public $latestData;
    public $latestPublikasi;
    public $latestIndikator;

    // 🟦 Chart Data
    public array $lineLabels = [];
    public array $lineData   = [];
    public array $donutLabels = [];
    public array $donutData   = [];

    public function mount()
    {
        \Carbon\Carbon::setLocale('id');
        $this->aspekCount      = Aspek::count();
        $this->datasetCount    = Dataset::count();
        $this->instansiCount   = Skpd::whereColumn('id','unor_induk_id')->count();
        $this->publikasiCount  = Publikasi::count();

        // Data Terbaru
        $this->latestData = Dataset::with(['aspek', 'skpd'])
            ->latest('updated_at')
            ->where('status', 'published')
            ->take(4)
            ->get();

        $this->latestPublikasi = Publikasi::with(['aspek', 'skpd'])
            ->latest('updated_at')
            ->where('status', 'published')
            ->take(4)
            ->get();

        $this->latestIndikator = Walidata::with(['aspek', 'skpd'])
            ->latest('verifikasi_data')
            ->take(4)
            ->get();

        // Inisialisasi Chart
        $this->initLineChart();
        $this->initDonutChart();
    }

    protected function initLineChart(): void
    {
        $months = collect(range(0, 4))
            ->map(fn($i) => now()->subMonths($i)->format('Y-m'))
            ->reverse();

        foreach ($months as $month) {
            $this->lineLabels[] = Carbon::createFromFormat('Y-m', $month)->translatedFormat('M Y');
            $this->lineData[]   = Dataset::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count();
        }
    }

    protected function initDonutChart(): void
    {
        $topAspek = Dataset::with('aspek')
            ->selectRaw('aspek_id, COUNT(*) as total')
            ->groupBy('aspek_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $this->donutLabels = $topAspek->map(fn($d) => $d->aspek->nama ?? '-')->toArray();
        $this->donutData   = $topAspek->map(fn($d) => $d->total)->toArray();
    }

    public function render()
    {
        return view('livewire.home', [
            'aspekCount'      => $this->aspekCount,
            'datasetCount'    => $this->datasetCount,
            'instansiCount'   => $this->instansiCount,
            'publikasiCount'  => $this->publikasiCount,
            'latestData'      => $this->latestData,
            'latestPublikasi' => $this->latestPublikasi,
            'latestIndikator' => $this->latestIndikator,
            'lineLabels'      => $this->lineLabels,
            'lineData'        => $this->lineData,
            'donutLabels'     => $this->donutLabels,
            'donutData'       => $this->donutData,
        ]);
    }
}
