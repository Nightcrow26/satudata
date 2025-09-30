<?php

namespace App\Livewire\Public\Data;

use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Dataset;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class DetailContent extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $tab = 'table'; // 'table' | 'chart' | 'meta'

    public int $page = 1;
    public ?string $datasetId = null;
    public ?Dataset $dataset = null;
    public array $chartData = [];
    public array $mapData = [];
    public array $allTableData = [];
    public array $metadata = [];
    public array $columns = [];
    public string $xAxis = '';
    public string $yAxis = '';
    public string $latitudeColumn = '';
    public string $longitudeColumn = '';
    public string $labelColumn = '';
    public bool $hasMapData = false;
    public int $perPage = 10; // Default 10 items per page
    public int $chartPerPage = 10; // Separate per page for chart
    public int $mapPerPage = 100; // Default for map data
    public array $perPageOptions = [10, 25, 50, 100];

    protected $queryString = [
        'perPage' => ['except' => 10],
        'chartPerPage' => ['except' => 10],
        'mapPerPage' => ['except' => 100],
        'page' => ['except' => 1]
    ];

    public function mount($datasetId = null): void
    {
        $this->datasetId = (string) $datasetId;
        if (!in_array($this->tab, ['table','chart','meta'], true)) {
            $this->tab = 'table';
        }

        if ($this->datasetId) {
            $this->loadDataset();
        }
    }

    private function loadDataset(): void
    {
        // Load dataset dengan relasi
        $this->dataset = Dataset::with(['aspek', 'skpd', 'user'])
            ->where('id', $this->datasetId)
            ->where(function ($query) {
                $query->where('status', 'published')
                      ->orWhere('status', 'approved');
            })
            ->first();

        if (!$this->dataset) {
            return;
        }

        // Track view for this dataset (only increment once per session)
        $this->dataset->incrementViewIfNotSeen();

        // Load metadata jika ada
        if ($this->dataset->metadata) {
            try {
                $metaSheet = Excel::toArray(null, $this->dataset->metadata, 's3')[0];
                $this->metadata = collect($metaSheet)
                    ->filter(fn($row) => isset($row[0], $row[1]))
                    ->map(fn($row) => ['label' => $row[0], 'value' => $row[1]])
                    ->values()
                    ->toArray();
            } catch (\Exception $e) {
                $this->metadata = [];
            }
        }

        // Load data Excel jika ada
        if ($this->dataset->excel) {
            try {
                $allSheets = Excel::toArray(null, $this->dataset->excel, 's3');
                $rows = $allSheets[0]; // sheet pertama
                $rawHeader = $rows[0] ?? [];

                // Filter header kosong dan reset index
                $header = array_values(array_filter($rawHeader, fn($col) => $col !== null && $col !== ''));
                $this->columns = $header;

                // Mapping setiap baris data sesuai header
                $this->allTableData = [];
                foreach (array_slice($rows, 1) as $dataRow) {
                    $cells = array_slice($dataRow, 0, count($header));
                    $row = array_combine($header, $cells);
                    
                    // Hanya ambil baris yang memiliki data minimal di satu kolom
                    if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) > 0) {
                        $this->allTableData[] = $row;
                    }
                }

                // Inisialisasi sumbu default
                $this->xAxis = $header[0] ?? '';
                $this->yAxis = $header[1] ?? '';
                
                // Deteksi kolom latitude dan longitude
                $this->detectMapColumns();
                
                $this->updateChart();
                if ($this->hasMapData) {
                    $this->updateMapData();
                }
            } catch (\Exception $e) {
                $this->allTableData = [];
                $this->columns = [];
            }
        }
    }

    private function detectMapColumns(): void
    {
        $latPatterns = ['latitude', 'lat', 'lintang'];
        $lngPatterns = ['longitude', 'lng', 'lon', 'bujur'];
        
        foreach ($this->columns as $column) {
            $columnLower = strtolower($column);
            
            // Deteksi latitude
            if (empty($this->latitudeColumn)) {
                foreach ($latPatterns as $pattern) {
                    if (str_contains($columnLower, $pattern)) {
                        $this->latitudeColumn = $column;
                        break;
                    }
                }
            }
            
            // Deteksi longitude
            if (empty($this->longitudeColumn)) {
                foreach ($lngPatterns as $pattern) {
                    if (str_contains($columnLower, $pattern)) {
                        $this->longitudeColumn = $column;
                        break;
                    }
                }
            }
        }
        
        // Set label column (preferably name/nama or first column)
        $namePatterns = ['nama', 'name', 'title', 'judul'];
        foreach ($this->columns as $column) {
            $columnLower = strtolower($column);
            foreach ($namePatterns as $pattern) {
                if (str_contains($columnLower, $pattern)) {
                    $this->labelColumn = $column;
                    break 2;
                }
            }
        }
        
        // If no name column found, use first column
        if (empty($this->labelColumn)) {
            $this->labelColumn = $this->columns[0] ?? '';
        }
        
        $this->hasMapData = !empty($this->latitudeColumn) && !empty($this->longitudeColumn);
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedChartPerPage()
    {
        // Tidak melakukan apa-apa, harus klik terapkan dulu
    }

    public function updatedXAxis()
    {
        $this->updateChart();
    }

    public function updatedYAxis()
    {
        $this->updateChart();
    }

    public function updatedLatitudeColumn()
    {
        $this->hasMapData = !empty($this->latitudeColumn) && !empty($this->longitudeColumn);
        if ($this->hasMapData) {
            $this->updateMapData();
        }
    }

    public function updatedLongitudeColumn()
    {
        $this->hasMapData = !empty($this->latitudeColumn) && !empty($this->longitudeColumn);
        if ($this->hasMapData) {
            $this->updateMapData();
        }
    }

    public function updatedLabelColumn()
    {
        if ($this->hasMapData) {
            $this->updateMapData();
        }
    }

    public function updateChart(): void
    {
        // Ambil data sesuai dengan chartPerPage untuk chart
        $currentPageData = $this->getChartPaginatedData();
        
        $this->chartData = [];
        foreach ($currentPageData as $row) {
            if (isset($row[$this->xAxis], $row[$this->yAxis])) {
                $this->chartData[] = [
                    'x' => $row[$this->xAxis],
                    'y' => (float) $row[$this->yAxis],
                ];
            }
        }

        // Kirim event ke frontend dengan data terbaru
        $this->dispatch('chartDataUpdated', $this->chartData);
    }

    public function updateMapData(): void
    {
        if (!$this->hasMapData) {
            return;
        }

        // Ambil data sesuai dengan mapPerPage untuk map
        $currentPageData = $this->getMapPaginatedData();
        
        $this->mapData = [];
        foreach ($currentPageData as $row) {
            $lat = $row[$this->latitudeColumn] ?? null;
            $lng = $row[$this->longitudeColumn] ?? null;
            $label = $row[$this->labelColumn] ?? 'No Label';
            
            // Validasi koordinat
            if (is_numeric($lat) && is_numeric($lng) && 
                $lat >= -90 && $lat <= 90 && 
                $lng >= -180 && $lng <= 180) {
                
                // Buat popup content dengan semua data
                $popupContent = "<strong>{$label}</strong><br>";
                foreach ($row as $key => $value) {
                    if ($key !== $this->labelColumn && !empty($value)) {
                        $popupContent .= "<small><strong>{$key}:</strong> {$value}</small><br>";
                    }
                }
                
                $this->mapData[] = [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'label' => $label,
                    'popup' => $popupContent
                ];
            }
        }

        // Kirim event ke frontend dengan data map terbaru
        $this->dispatch('mapDataUpdated', $this->mapData);
    }

    public function applyMapSettings(): void
    {
        $this->hasMapData = !empty($this->latitudeColumn) && !empty($this->longitudeColumn);
        if ($this->hasMapData) {
            $this->updateMapData();
        }
    }

    private function getChartPaginatedData()
    {
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->chartPerPage;
        
        return array_slice($this->allTableData, $offset, $this->chartPerPage);
    }

    private function getMapPaginatedData()
    {
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->mapPerPage;
        
        return array_slice($this->allTableData, $offset, $this->mapPerPage);
    }

    private function getPaginatedData()
    {
        $currentPage = $this->getPage();
        $offset = ($currentPage - 1) * $this->perPage;
        
        return array_slice($this->allTableData, $offset, $this->perPage);
    }

    public function getPaginatedTableData()
    {
        $total = count($this->allTableData);
        $currentPage = $this->getPage();
        
        $items = collect($this->getPaginatedData());
        
        return new LengthAwarePaginator(
            $items,
            $total,
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function getChartPaginatedTableData()
    {
        $total = count($this->allTableData);
        $currentPage = $this->getPage();
        
        $items = collect($this->getChartPaginatedData());
        
        return new LengthAwarePaginator(
            $items,
            $total,
            $this->chartPerPage,
            $currentPage
        );
    }

    public function getMapPaginatedTableData()
    {
        $total = count($this->allTableData);
        $currentPage = $this->getPage();
        
        $items = collect($this->getMapPaginatedData());
        
        return new LengthAwarePaginator(
            $items,
            $total,
            $this->mapPerPage,
            $currentPage
        );
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['table','chart','meta'], true)) {
            $this->tab = $tab; // URL akan tersinkron otomatis tanpa reload
        }
    }

    public function render()
    {
        $paginatedData = $this->getPaginatedTableData();
        $chartPaginatedData = $this->getChartPaginatedTableData();
        $mapPaginatedData = $this->getMapPaginatedTableData();
        
        // Debug pagination
        \Log::info('DetailContent Pagination Debug', [
            'total_data' => count($this->allTableData),
            'per_page' => $this->perPage,
            'current_page' => $this->getPage(),
            'paginated_total' => $paginatedData->total(),
            'paginated_count' => $paginatedData->count(),
            'has_pages' => $paginatedData->hasPages()
        ]);
        
        return view('livewire.public.data.detail-content', [
            'tableData' => $paginatedData->items(),
            'datasets' => $paginatedData, // Untuk pagination tabel
            'chartDatasets' => $chartPaginatedData, // Untuk pagination chart
            'mapDatasets' => $mapPaginatedData, // Untuk pagination map
            'dataset' => $this->dataset // Add dataset to view
        ]);
    }
}
