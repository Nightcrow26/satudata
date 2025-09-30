<div 
  x-data="datasetChart()" 
  x-init="init()" 
  wire:ignore.self
>
  {{-- 🔷 Header Instansi --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm px-4 py-4 border border-gray-200 dark:border-gray-700 mb-4">
    <div class="flex items-center gap-4">
      @php
          $fotoKey = $walidata->skpd->foto ?? null;
      @endphp

      @if($fotoKey && Storage::disk('s3')->exists($fotoKey))
          {{-- Jika file ada di S3, gunakan temporaryUrl --}}
          <img
            src="{{ Storage::disk('s3')->temporaryUrl($fotoKey, now()->addMinutes(15)) }}"
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
        {{ $walidata->skpd->nama ?? 'SKPD' }}
      </h1>
    </div>
  </div>

  {{-- 📄 Info Dataset --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm px-6 py-4 border border-gray-200 dark:border-gray-700 mb-4 relative min-h-[140px]">
    <div class="flex justify-between items-start gap-4">
      <div class="flex-grow min-w-0">
        <h2 class="text-xl font-bold mb-3 text-gray-900 dark:text-white">{{ $walidata->indikator->uraian_indikator ?? 'Indikator' }}</h2>
        <div class="flex gap-2 mb-3 flex-wrap">
          <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full text-white" style="background-color: {{ $walidata->aspek->warna ?? '#10b981' }}">
            {{ $walidata->aspek->nama }}
          </span>
        </div>
        <div class="text-gray-600 dark:text-gray-400 text-sm">{{ $walidata->indikator->uraian_indikator ?? 'Deskripsi indikator tidak tersedia.' }}</div>
      </div>
      <div class="text-right text-gray-500 dark:text-gray-400 text-sm whitespace-nowrap min-w-[150px]">
        <div class="flex items-center justify-end mb-1">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          {{ $walidata ? $walidata->created_at->translatedFormat('d F Y') : '-' }}
        </div>
        <div class="flex items-center justify-end">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
          </svg>
          0
        </div>
      </div>
    </div>
    <div class="absolute bottom-4 right-4 flex gap-2">
       <button 
          wire:click="downloadPdf" 
          class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
          title="Download PDF"
          wire:loading.attr="disabled"
          wire:target="downloadPdf"
        >
            <span wire:loading.remove wire:target="downloadPdf">
                <i class="bi bi-filetype-pdf"></i>
            </span>
            <span wire:loading wire:target="downloadPdf">
                <i class="bi bi-hourglass-split"></i>
            </span>
        </button>

        {{-- Download CSV --}}
        <button 
            wire:click="downloadExcel" 
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border border-green-300 dark:border-green-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
            title="Download Excel"
            wire:loading.attr="disabled"
            wire:target="downloadExcel"
        >
            <span wire:loading.remove wire:target="downloadExcel">
                <i class="bi bi-file-earmark-excel"></i>
            </span>
            <span wire:loading wire:target="downloadExcel">
                <i class="bi bi-hourglass-split"></i>
            </span>
        </button>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Tahun</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Indikator</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Data</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Satuan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Status Verifikasi</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">SKPD</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($tableData as $row)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row['tahun'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row['Uraian'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row['data'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row['satuan'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row['verifikasi_data'] ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $row['skpd'] ?? '-' }}</td>
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
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
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
          <select x-model="selectedChartType" id="selected-chart-type" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
            <option value="bar">Bar</option>
            <option value="line">Line</option>
            <option value="pie">Pie</option>
            <option value="doughnut">Doughnut</option>
          </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">X Axis</label>
            <select x-model="selectedXAxis" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
              @foreach(['tahun' => 'Tahun', 'data' => 'Data'] as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
              @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Y Axis</label>
            <select x-model="selectedYAxis" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm w-full dark:!bg-gray-800 dark:text-white">
              @foreach(['data' => 'Data', 'tahun' => 'Tahun'] as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
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
      // Chart data received from Livewire; update chart instance if present
      // console.log removed to reduce noisy logs in production
      if (window.datasetChartInstance) {
        window.datasetChartInstance.updateChartData(data);
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
    chartType: 'bar',
    selectedChartType: @entangle('selectedChartType').live || 'bar',
    selectedXAxis: @entangle('xAxis'),
    selectedYAxis: @entangle('yAxis'),
    chartData: @entangle('chartData').live,
    isRendering: false,

    init() {
      window.datasetChartInstance = this;

      // Watch for tab changes
      this.$watch('tab', v => {
        if (v === 'grafik') {
          this.$nextTick(() => {
            setTimeout(() => this.renderChart(), 150);
          });
        } else {
          // Destroy chart when leaving grafik tab
          if (v !== 'grafik') {
            this.destroyChart();
          }
        }
      });

      // Watch for chartData changes
      this.$watch('chartData', (newData) => {
        if (this.tab === 'grafik' && Array.isArray(newData) && newData.length > 0 && !this.isRendering) {
          this.$nextTick(() => {
            setTimeout(() => this.renderChart(), 150);
          });
        }
      });
    },

    applyChartSettings() {
      // Apply locally and sync to Livewire only when user clicks Terapkan
      this.chartType = this.selectedChartType;
      this.$wire.set('selectedChartType', this.selectedChartType);
      this.$wire.set('xAxis', this.selectedXAxis);
      this.$wire.set('yAxis', this.selectedYAxis);
      this.$wire.call('updateChart');
      
      if (this.tab === 'grafik' && !this.isRendering) {
        this.$nextTick(() => {
          setTimeout(() => this.renderChart(), 150);
        });
      }
    },

    updateChartData(data) {
      this.chartData = Array.isArray(data) ? data : data[0] || [];
      
      if (this.tab === 'grafik' && !this.isRendering) {
        this.$nextTick(() => {
          setTimeout(() => this.renderChart(), 150);
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
          this.chart = null;
        }
    },

    renderChart() {
      if (this.isRendering) {
        // Chart rendering already in progress, skipping...
        return;
      }
      this.isRendering = true;
      // Rendering chart with data (logging removed)
      
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
              label: '{{ addslashes($walidata->indikator->uraian_indikator ?? 'Data Indikator') }}',
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
          // Chart created successfully
          
        } catch (error) {
          console.error('Error creating chart:', error);
        } finally {
          this.isRendering = false;
        }
      }, 200);
    }
  }
}
</script>
