<div 
  x-data="datasetChart()" 
  x-init="init()" 
  wire:ignore.self
>

  {{-- 🧭 Navigasi Tab --}}
  <div class="bg-white dark:!bg-gray-800 rounded-lg shadow-sm p-3 mb-4">
    <div class="flex gap-2 px-2 py-1">
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
          <select wire:model.live="perPage" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:!bg-gray-800 dark:text-white w-15">
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">Produsen Data</th>
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
          <select wire:model="chartPerPage" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:!bg-gray-800 dark:text-white">
            @foreach($perPageOptions as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipe Chart</label>
          <select x-model="selectedChartType" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:!bg-gray-800 dark:text-white">
            <option value="bar">Bar</option>
            <option value="line">Line</option>
            <option value="pie">Pie</option>
            <option value="doughnut">Doughnut</option>
          </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">X Axis</label>
            <select wire:model="xAxis" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:!bg-gray-800 dark:text-white">
                <option value="tahun">Tahun</option>
                <option value="data">Data</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Y Axis</label>
            <select wire:model="yAxis" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 dark:!bg-gray-800 dark:text-white">
                <option value="data">Data</option>
                <option value="tahun">Tahun</option>
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
    selectedChartType: 'bar',
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
      this.chartType = this.selectedChartType;
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
      if (this.chart) {
        try {
          Object.values(Chart.instances).forEach(instance => {
            if (instance.canvas && instance.canvas.id === 'dataset-chart') {
              instance.destroy();
            }
          });
          
          this.chart.destroy();
        } catch (e) {
          console.warn('Error destroying chart:', e);
        } finally {
          this.chart = null;
        }
      }

      const canvas = document.getElementById('dataset-chart');
      if (canvas) {
        const ctx = canvas.getContext('2d');
        if (ctx) {
          ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
        if (canvas.chartInstance) {
          delete canvas.chartInstance;
        }
      }
    },

    renderChart() {
      if (this.isRendering) {
        return;
      }

      this.isRendering = true;
      
      if (!Array.isArray(this.chartData) || this.chartData.length === 0) {
        this.isRendering = false;
        return;
      }

      const canvas = document.getElementById('dataset-chart');
      if (!canvas) {
        this.isRendering = false;
        return;
      }

      this.destroyChart();

      setTimeout(() => {
        try {
          const ctx = canvas.getContext('2d');
          if (!ctx) {
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
