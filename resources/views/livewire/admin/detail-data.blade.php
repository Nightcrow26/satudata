<div 
  x-data="datasetChart()" 
  x-init="init()" 
  wire:ignore.self
>
  {{-- 🔷 Header Instansi --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm px-4 py-4 border border-gray-200 dark:border-gray-700 mb-4">
    <div class="flex items-center gap-4">
      @php
          $fotoKey = $dataset->skpd->foto;  
      @endphp

      @if($fotoKey && Storage::disk('s3')->exists($fotoKey))
          {{-- Jika file ada di S3, gunakan temporaryUrl --}}
          <img
            src="{{ resolve_media_url($fotoKey) }}"
            alt="Logo SKPD"
            class="w-12 h-12 object-contain"
          >
      @elseif($fotoKey && file_exists(public_path($fotoKey)))
          {{-- Jika file tidak di S3 tapi ada di public, gunakan asset --}}
          <img
            src="{{ asset($fotoKey) }}"
            alt="Logo SKPD"
            class="w-12 h-12 object-contain"
          >
      @else
          {{-- Fallback: logo default --}}
          <img
            src="{{ asset('logo-hsu.png') }}"
            alt="Logo Default HSU"
            class="w-12 h-12 object-contain"
          >
      @endif
      <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ $dataset->skpd->nama }}
      </h1>
    </div>
  </div>

  {{-- 📄 Info Dataset --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm px-6 py-4 border border-gray-200 dark:border-gray-700 mb-4 relative min-h-[140px]">
    <div class="flex justify-between items-start gap-4">
      <div class="flex-grow min-w-0">
        <h2 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">{{ $dataset->nama }}</h2>
        <div class="flex gap-2 mb-3 flex-wrap">
          <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full text-white" style="background-color: {{ $dataset->aspek->warna ?? '#10b981' }}">
            {{ $dataset->aspek->nama }}
          </span>
        </div>
        <div class="text-gray-600 dark:text-gray-400 text-sm prose prose-sm max-w-none content-html trix-content">{!! $dataset->deskripsi !!}</div>
      </div>
      <div class="text-right text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap min-w-[150px]">
        <div class="flex items-center justify-end mb-1">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          {{ $dataset->created_at->translatedFormat('d F Y') }}
        </div>
        <div class="flex items-center justify-end">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          {{ $dataset->view }}
        </div>
      </div>
    </div>
    <div class="absolute bottom-4 right-4 flex gap-2">
      @livewire('admin.download-pdf', ['dataset' => $dataset])
      <a type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border border-green-300 dark:border-green-600 rounded-md hover:bg-green-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        aria-label="Unduh CSV"
                      href="{{ Storage::disk('s3')->temporaryUrl($dataset->excel, now()->addMinutes(15)) }}">
          <i class="bi bi-file-earmark-excel"></i>
      </a>
    </div>
  </div>

  {{-- 🧭 Navigasi Tab --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm p-3 mb-4">
        <div class="inline-flex rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden
                            dark:border-gray-600 dark:bg-gray-800">
      <button 
        class="h-10 px-4 inline-flex items-center justify-center text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600" 
        :class="tab==='tabel' ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'"
        @click="tab='tabel'"
      >Tabel</button>
      <button 
        class="h-10 px-4 inline-flex items-center justify-center text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600" 
        :class="tab==='grafik' ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'" 
        @click="tab='grafik'"
      >Grafik</button>
      <button 
        class="h-10 px-4 inline-flex items-center justify-center text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600" 
        :class="tab==='map' ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'" 
        @click="tab='map'"
      >
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        Peta
      </button>
      <button 
        class="h-10 px-4 inline-flex items-center justify-center text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600" 
        :class="tab==='metadata' ? 'bg-teal-600 text-white font-semibold' 
                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700'" 
        @click="tab='metadata'"
      >Metadata</button>
    </div>
  </div>

  {{-- 📦 Konten --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm p-6">
    {{-- TABEL --}}
    <div x-show="tab==='tabel'" x-cloak>
      {{-- Per Page Selector --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="flex items-center gap-3">
          <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Show</label>
          <select wire:model.live="perPage" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:!bg-gray-800 dark:text-white w-auto">
            @foreach($perPageOptions as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
          <span class="text-sm text-gray-500 dark:text-gray-400">entries</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 border border-gray-200 dark:border-gray-700 rounded-md">
          <thead class="bg-gray-50 dark:!bg-gray-800">
            <tr>
              @foreach($columns as $col)
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">{{ $col }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($tableData as $row)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                @foreach($columns as $col)
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row[$col] ?? '-' }}</td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination Component --}}
      <div class="mt-6">
        <x-admin.pagination :items="$datasets" />
      </div>
    </div>

    {{-- GRAFIK --}}
    <div x-show="tab==='grafik'" x-cloak class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Show</label>
          <select wire:model.live="chartPerPage" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            @foreach($perPageOptions as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipe Chart</label>
          <select x-model="selectedChartType" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            <option value="bar">Bar</option>
            <option value="line">Line</option>
            <option value="pie">Pie</option>
            <option value="doughnut">Doughnut</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">X Axis</label>
          <select x-model="selectedXAxis" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Y Axis</label>
            <select x-model="selectedYAxis" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
              @foreach($columns as $col)
                <option value="{{ $col }}">{{ $col }}</option>
              @endforeach
            </select>
        </div>
        <div class="flex items-end">
          <button 
            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors" 
            wire:click="updateChart"
            @click="applyChartSettings"
          >Terapkan</button>
        </div>
      </div>
      
      {{-- Chart Info --}}
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4 mb-6">
        <div class="flex items-center">
          <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm text-blue-700 dark:text-blue-300">
            Menampilkan {{ $chartDatasets->count() }} dari {{ $chartDatasets->total() }} data 
            (Halaman {{ $chartDatasets->currentPage() }} dari {{ $chartDatasets->lastPage() }})
          </span>
        </div>
      </div>

      <div class="bg-gray-50 dark:!bg-gray-800 rounded-lg p-4">
        <canvas 
          id="dataset-chart" 
          wire:ignore 
          style="max-height:400px; width:100%;"
        ></canvas>
      </div>
    </div>

    {{-- MAP --}}
    <div x-show="tab==='map'" x-cloak class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Show</label>
          <select wire:model.live="mapPerPage" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            @foreach($perPageOptions as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Latitude</label>
          <select wire:model.live="latitudeColumn" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Longitude</label>
          <select wire:model.live="longitudeColumn" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Label</label>
          <select wire:model.live="labelColumn" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex items-end">
          <button 
            class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white text-sm font-medium rounded-md transition-colors" 
            wire:click="applyMapSettings"
            @click="applyMapSettings"
            :disabled="!latitudeColumn || !longitudeColumn"
          >Terapkan</button>
        </div>
      </div>
      
      {{-- Map Info --}}
      <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-4 mb-6" x-show="mapData && mapData.length > 0">
        <div class="flex items-center">
          <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm text-blue-700 dark:text-blue-300" x-text="`Menampilkan ${mapData ? mapData.length : 0} lokasi`"></span>
        </div>
      </div>

      {{-- Map Setup Info --}}
      <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md p-4 mb-6" x-show="!latitudeColumn || !longitudeColumn">
        <div class="flex items-center">
          <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-sm text-yellow-700 dark:text-yellow-300">
            Pilih kolom latitude dan longitude untuk menampilkan peta
          </span>
        </div>
      </div>

      <div 
        id="dataset-map" 
        wire:ignore 
        class="h-[500px] w-full rounded-lg bg-gray-100 dark:!bg-gray-800"
        x-show="latitudeColumn && longitudeColumn"
      ></div>
    </div>

    {{-- METADATA --}}
    <div x-show="tab==='metadata'" x-cloak>
      <div class="mt-6">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          @foreach($metadata as $row)
            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $row['label'] }}</dt>
              <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $row['value'] }}</dd>
            </div>
          @endforeach
        </dl>
      </div>
    </div>
  </div>

@push('scripts')
<script>
  document.addEventListener('livewire:init', () => {
    // Listen for chart data updates
    Livewire.on('chartDataUpdated', (data) => {
      // chart data received
      if (window.datasetChartInstance) {
        window.datasetChartInstance.updateChartData(data);
      }
    });

    // Listen for map data updates
    Livewire.on('mapDataUpdated', (data) => {
      // map data received
      if (window.datasetChartInstance) {
        window.datasetChartInstance.updateMapData(data);
      }
    });
  });
</script>
@endpush
</div>

<script>
function datasetChart() {
  return {
    tab: 'tabel',
    chart: null,
    map: null,
    mapMarkers: [],
  chartType: 'bar',
  selectedChartType: @entangle('selectedChartType').live || 'bar',
  selectedXAxis: @entangle('xAxis'),
  selectedYAxis: @entangle('yAxis'),
  chartData: @entangle('chartData').live,
  // Deduping helpers to avoid double renders from entangle + explicit events
  lastChartHash: null,
  chartRenderTimer: null,
    mapData: @entangle('mapData').live,
    latitudeColumn: @entangle('latitudeColumn'),
    longitudeColumn: @entangle('longitudeColumn'),
    isRendering: false,
    isMapRendering: false,

    init() {
      window.datasetChartInstance = this;

      // Watch for tab changes
      this.$watch('tab', v => {
        if (v === 'grafik') {
          this.$nextTick(() => {
            this.scheduleRenderChart(150);
          });
        } else if (v === 'map') {
          this.$nextTick(() => {
            setTimeout(() => this.renderMap(), 300);
          });
        } else {
          // Destroy chart when leaving grafik tab
          if (v !== 'grafik') {
            this.destroyChart();
          }
          // Don't destroy map completely, just clear markers for performance
          if (v !== 'map' && this.map) {
            this.clearMapMarkers();
          }
        }
      });

      // Watch for chartData changes
      this.$watch('chartData', (newData) => {
        if (this.tab === 'grafik' && Array.isArray(newData) && newData.length > 0 && !this.isRendering) {
          this.$nextTick(() => {
            this.scheduleRenderChart(150);
          });
        }
      });

      // Watch for mapData changes
      this.$watch('mapData', (newData) => {
        if (this.tab === 'map' && Array.isArray(newData) && !this.isMapRendering) {
          this.$nextTick(() => {
            setTimeout(() => this.updateMapMarkers(newData), 300);
          });
        }
      });

      // Watch for latitude/longitude column changes
      this.$watch('latitudeColumn', () => {
        if (this.tab === 'map' && this.latitudeColumn && this.longitudeColumn && !this.isMapRendering) {
          this.$nextTick(() => {
            setTimeout(() => this.renderMap(), 300);
          });
        }
      });

      this.$watch('longitudeColumn', () => {
        if (this.tab === 'map' && this.latitudeColumn && this.longitudeColumn && !this.isMapRendering) {
          this.$nextTick(() => {
            setTimeout(() => this.renderMap(), 300);
          });
        }
      });
    },

    applyChartSettings() {
      // Sync selected controls to Livewire then request update
      this.chartType = this.selectedChartType; // apply locally so renderChart uses correct type
      this.$wire.set('selectedChartType', this.selectedChartType);
      this.$wire.set('xAxis', this.selectedXAxis);
      this.$wire.set('yAxis', this.selectedYAxis);
      this.$wire.call('updateChart');
      
      if (this.tab === 'grafik' && !this.isRendering) {
        this.$nextTick(() => {
          this.scheduleRenderChart(150);
        });
      }
    },

    applyMapSettings() {
      this.$wire.call('applyMapSettings');
      
      if (this.tab === 'map' && this.latitudeColumn && this.longitudeColumn && !this.isMapRendering) {
        this.$nextTick(() => {
          setTimeout(() => this.renderMap(), 300);
        });
      }
    },

    updateChartData(data) {
      // Normalize incoming data
      const normalized = Array.isArray(data) ? data : (data && data[0]) ? data[0] : [];

      // Compute a simple hash for deduplication
      let hash = '';
      try {
        hash = JSON.stringify(normalized);
      } catch (e) {
        hash = String(normalized);
      }

      if (this.lastChartHash && this.lastChartHash === hash) {
        // Duplicate chart data received, skipping render
        return;
      }

      this.lastChartHash = hash;
      this.chartData = normalized;

      if (this.tab === 'grafik' && !this.isRendering) {
        this.$nextTick(() => {
          this.scheduleRenderChart(150);
        });
      }
    },

    updateMapData(data) {
      this.mapData = Array.isArray(data) ? data : data[0] || [];
      
      if (this.tab === 'map' && !this.isMapRendering) {
        this.$nextTick(() => {
          setTimeout(() => this.updateMapMarkers(this.mapData), 150);
        });
      }
    },

    destroyChart() {
        // Try to destroy any existing Chart.js instance attached to the canvas
        try {
          const canvas = document.getElementById('dataset-chart');
          if (canvas) {
            // Chart.js v3+ exposes Chart.getChart(canvas)
            if (typeof Chart !== 'undefined' && typeof Chart.getChart === 'function') {
              const existing = Chart.getChart(canvas);
              if (existing) {
                try { existing.destroy(); } catch (e) { console.warn('Error destroying existing Chart via getChart:', e); }
              }
            } else if (this.chart && typeof this.chart.destroy === 'function') {
              try { this.chart.destroy(); } catch (e) { console.warn('Error destroying this.chart:', e); }
            } else if (typeof Chart !== 'undefined' && Chart.instances) {
              try {
                Object.values(Chart.instances).forEach(instance => {
                  if (instance.canvas && instance.canvas.id === 'dataset-chart') {
                    instance.destroy();
                  }
                });
              } catch (e) {
                console.warn('Error destroying Chart.instances:', e);
              }
            }

      // Clear canvas drawing
            const ctx = canvas.getContext('2d');
            if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Remove any attached reference
            if (canvas.chartInstance) delete canvas.chartInstance;
          }
        } catch (e) {
          console.warn('Error in destroyChart cleanup:', e);
        } finally {
            // clear any scheduled render
            if (this.chartRenderTimer) {
              clearTimeout(this.chartRenderTimer);
              this.chartRenderTimer = null;
            }
            this.chart = null;
        }
    },

      // Schedule chart rendering with dedupe for rapid updates
      scheduleRenderChart(delay = 150) {
        try {
          if (this.chartRenderTimer) clearTimeout(this.chartRenderTimer);
        } catch (e) {}
        this.chartRenderTimer = setTimeout(() => {
          this.chartRenderTimer = null;
          this.renderChart();
        }, delay);
      },

    renderChart() {
      if (this.isRendering) {
        // Chart rendering already in progress, skipping...
        return;
      }

      this.isRendering = true;
  // Rendering chart with data
      
        if (!Array.isArray(this.chartData) || this.chartData.length === 0) {
        // No valid chart data available
        this.isRendering = false;
        return;
      }

      const canvas = document.getElementById('dataset-chart');
      if (!canvas) {
        // Canvas element not found
        this.isRendering = false;
        return;
      }

      this.destroyChart();

      setTimeout(() => {
        try {
          const ctx = canvas.getContext('2d');
          if (!ctx) {
            // Cannot get canvas context
            this.isRendering = false;
            return;
          }
          
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          canvas.style.width = '100%';
          canvas.style.height = '400px';

          const data = {
            labels: this.chartData.map(i => String(i.x || i.label || '')),
            datasets: [{
              label: '{{ addslashes($dataset->nama) }}',
              data: this.chartData.map(i => Number(i.y || i.value || 0)),
              backgroundColor: ['pie','doughnut'].includes(this.chartType)
                ? [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)',
                    'rgba(255, 99, 255, 0.8)',
                    'rgba(99, 255, 132, 0.8)'
                  ]
                : 'rgba(54, 162, 235, 0.6)',
              borderColor: ['pie','doughnut'].includes(this.chartType)
                ? [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)',
                    'rgba(255, 99, 255, 1)',
                    'rgba(99, 255, 132, 1)'
                  ]
                : 'rgba(54, 162, 235, 1)',
              borderWidth: 1,
              fill: this.chartType === 'line' ? false : undefined
            }]
          };

          const config = {
            type: this.chartType,
            data: data,
            options: {
              responsive: true,
              maintainAspectRatio: false,
              interaction: {
                intersect: false,
                mode: 'index'
              },
              plugins: {
                legend: { 
                  display: true,
                  position: 'top'
                },
                tooltip: {
                  enabled: true
                }
              },
              ...((['bar','line'].includes(this.chartType)) && {
                scales: {
                  x: {
                    display: true,
                    grid: {
                      display: true
                    }
                  },
                  y: { 
                    beginAtZero: true,
                    display: true,
                    grid: {
                      display: true
                    }
                  }
                }
              }),
              animation: {
                duration: 500,
                easing: 'easeInOutQuart'
              }
            }
          };

          this.chart = new Chart(ctx, config);
          
        } catch (error) {
          console.error('Error creating chart:', error);
        } finally {
          this.isRendering = false;
        }
      }, 200);
    },

    renderMap() {
      if (this.isMapRendering) {
        // Map rendering already in progress, skipping...
        return;
      }

      if (!this.latitudeColumn || !this.longitudeColumn) {
        // Latitude or longitude column not selected
        return;
      }

      // Cek apakah Leaflet sudah siap
      if (typeof L === 'undefined') {
        // Leaflet not loaded yet, retrying...
        setTimeout(() => this.renderMap(), 500);
        return;
      }

      this.isMapRendering = true;
  // Rendering map...

      const mapContainer = document.getElementById('dataset-map');
      if (!mapContainer) {
        // Map container not found
        this.isMapRendering = false;
        return;
      }

      try {
        // Destroy instance jika sudah ada
        if (this.map) {
          this.map.remove(); // Leaflet way
          this.map = null;
        }

        // Inisialisasi map baru
        const defaultCenter = [-2.5489, 118.0149];
        this.map = L.map('dataset-map', {
          zoomControl: true
        }).setView(defaultCenter, 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '© OpenStreetMap contributors',
          maxZoom: 18,
        }).addTo(this.map);

  // Map initialized

        // Sinkronkan ukuran peta (untuk tab/modal)
        requestAnimationFrame(() => {
          setTimeout(() => {
            if (this.map) {
              this.map.invalidateSize();
            }
          }, 300);
        });

        // Tambahkan marker dari data
        this.updateMapMarkers(this.mapData);

      } catch (error) {
        console.error('Error rendering map:', error);
      } finally {
        this.isMapRendering = false;
      }
    },

    clearMapMarkers() {
      if (Array.isArray(this.mapMarkers) && this.mapMarkers.length > 0) {
        this.mapMarkers.forEach(marker => {
          if (this.map && marker) {
            this.map.removeLayer(marker);
          }
        });
        this.mapMarkers = [];
      }
    },

    updateMapMarkers(data) {
      if (typeof L === 'undefined' || !this.map || !Array.isArray(data)) {
        return;
      }

  // Updating map markers with data

      this.clearMapMarkers();

      const bounds = [];

      data.forEach(item => {
        if (item.lat && item.lng) {
          try {
            const marker = L.marker([item.lat, item.lng])
              .bindPopup(item.popup || item.label || 'No information')
              .addTo(this.map);

            this.mapMarkers.push(marker);
            bounds.push([item.lat, item.lng]);
          } catch (error) {
            console.warn('Error adding marker:', error);
          }
        }
      });

      if (bounds.length > 0) {
        try {
          if (bounds.length === 1) {
            this.map.setView(bounds[0], 10);
          } else {
            this.map.fitBounds(bounds, { padding: [20, 20] });
          }
        } catch (error) {
          console.warn('Error fitting bounds:', error);
        }
      }

  // Added markers to map
    }
}
}
</script>
