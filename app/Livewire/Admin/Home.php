<?php

namespace App\Livewire\Admin;

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
        // Jika user biasa, batasi ke SKPD miliknya
        if (auth()->check() && auth()->user()->hasRole('user')) {
            $userSkpd = auth()->user()->skpd_uuid;

            $this->datasetCount = Dataset::where('instansi_id', $userSkpd)->count();
            $this->instansiCount = Skpd::whereColumn('id','unor_induk_id')->where('id', $userSkpd)->count();
            $this->publikasiCount = Publikasi::where('instansi_id', $userSkpd)->count();
        } else {
            $this->datasetCount    = Dataset::count();
            $this->instansiCount   = Skpd::whereColumn('id','unor_induk_id')->count();
            $this->publikasiCount  = Publikasi::count();
        }

        // Data Terbaru
        // Data Terbaru (batasi jika user role 'user')
        $dsQuery = Dataset::with(['aspek', 'skpd'])
            ->latest('updated_at')
            ->where('status', 'published');

        $pubQuery = Publikasi::with(['aspek', 'skpd'])
            ->latest('updated_at')
            ->where('status', 'published');

        $wdQuery = Walidata::with(['aspek', 'skpd'])
            ->latest('verifikasi_data')->where('skpd_id', '!=', null);

        if (auth()->check() && auth()->user()->hasRole('user')) {
            $userSkpd = auth()->user()->skpd_uuid;
            $dsQuery->where('instansi_id', $userSkpd);
            $pubQuery->where('instansi_id', $userSkpd);
            $wdQuery->where('skpd_id', $userSkpd);
        }

        $this->latestData = $dsQuery->take(10)->get();
        $this->latestPublikasi = $pubQuery->take(10)->get();
        $this->latestIndikator = $wdQuery->take(10)->get();

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
            $q = Dataset::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2));
            if (auth()->check() && auth()->user()->hasRole('user')) {
                $q->where('instansi_id', auth()->user()->skpd_uuid);
            }
            $this->lineData[] = $q->count();
        }
    }

    protected function initDonutChart(): void
    {
        $topAspekQuery = Dataset::with('aspek')
            ->selectRaw('aspek_id, COUNT(*) as total')
            ->groupBy('aspek_id')
            ->orderByDesc('total')
            ->take(5);

        if (auth()->check() && auth()->user()->hasRole('user')) {
            $topAspekQuery->where('instansi_id', auth()->user()->skpd_uuid);
        }

        $topAspek = $topAspekQuery->get();

        $this->donutLabels = $topAspek->map(fn($d) => $d->aspek->nama ?? '-')->toArray();
        $this->donutData   = $topAspek->map(fn($d) => $d->total)->toArray();
    }

   public function render()
    {
        if (auth()->guest() || (auth()->check() && auth()->user()->hasRole('guest'))) {
            return view('public.home.index', [
                'aspekCount'      => $this->aspekCount,
                'datasetCount'    => $this->datasetCount,
                'instansiCount'   => $this->instansiCount,
                'publikasiCount'  => $this->publikasiCount,
                'latestData'      => $this->latestData,
                'latestPublikasi' => $this->latestPublikasi,
                'latestIndikator' => $this->latestIndikator,
            ])->layout('components.layouts.public', [
                'title' => 'Beranda'
            ]);
        }

        return view('livewire.admin.home', [
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
        ])->layout('components.layouts.app', [
            'title' => 'Dashboard'
        ]);
    }
}
