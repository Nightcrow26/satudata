<div 
  x-data="datasetChart()" 
  x-init="init()" 
  wire:ignore.self
>
  {{-- 🔷 Header Instansi --}}
  <div class="card mb-3 shadow-sm px-3 py-3 border border-1">
    <div class="d-flex align-items-center gap-3">
      @php
          $fotoKey = $walidata->skpd->foto ?? null;
      @endphp

      @if($fotoKey && Storage::disk('s3')->exists($fotoKey))
          {{-- Jika file ada di S3, gunakan temporaryUrl --}}
          <img
            src="{{ Storage::disk('s3')->temporaryUrl($fotoKey, now()->addMinutes(15)) }}"
            alt="Logo SKPD"
            style="width:5%"
          >
      @elseif($fotoKey && file_exists(public_path($fotoKey)))
          {{-- Jika file tidak di S3 tapi ada di public, gunakan asset --}}
          <img
            src="{{ asset($fotoKey) }}"
            alt="Logo SKPD"
            style="width:5%"
          >
      @else
          {{-- Fallback: logo default --}}
          <img
            src="{{ asset('logo-hsu.png') }}"
            alt="Logo Default HSU"
            style="width:5%"
          >
      @endif
      <strong class="fs-6 text-dark mb-0">
        {{ $walidata->skpd->nama ?? 'SKPD' }}
      </strong>
    </div>
  </div>

  {{-- 📄 Info Dataset --}}
  <div class="card mb-3 shadow-sm px-4 py-3 border position-relative" style="min-height:140px;">
    <div class="d-flex justify-content-between align-items-start gap-3">
      <div class="flex-grow-1" style="min-width:0;">
        <h6 class="fw-bold mb-2 text-truncate">{{ $walidata->indikator->uraian_indikator ?? 'Indikator' }}</h6>
        <div class="d-flex gap-2 mb-2 flex-wrap">
          <span class="badge text-white" style="background-color: {{ $walidata->aspek->warna ?? '#198754' }}">
            {{ $walidata->aspek->nama }}
          </span>
        </div>
        <div class="text-muted small text-wrap editor-output">{{ $walidata->indikator->uraian_indikator ?? 'Deskripsi indikator tidak tersedia.' }}</div>
      </div>
      <div class="text-end text-muted small" style="white-space:nowrap; min-width:150px;">
        <span><i class="bi bi-calendar me-1"></i> {{ $walidata ? $walidata->created_at->format('d F Y') : '-' }}</span>
        <span><i class="bi bi-eye me-1"></i> 0 </span>
      </div>
    </div>
    <div class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex gap-2">
       <button type="button" wire:click="downloadPdf" class="btn btn-sm btn-outline-danger btn-icon">
             <span wire:loading.remove wire:target="downloadPdf">
                <i class="bi bi-filetype-pdf"></i>
            </span>
            <span wire:loading wire:target="downloadPdf">
                <i class="bi bi-hourglass-split"></i>
            </span>
        </button>
        <button type="button" wire:click="downloadExcel" class="btn btn-sm btn-outline-success btn-icon">
            <span wire:loading.remove wire:target="downloadExcel">
                <i class="bi bi-file-earmark-excel-fill"></i> 
            </span>
            <span wire:loading wire:target="downloadExcel">
                <i class="bi bi-hourglass-split"></i>
            </span>
        </button>
    </div>
  </div>

  {{-- 🧭 Navigasi Tab --}}
  <div class="card mb-3 shadow-sm p-2">
    <div class="d-flex gap-2 px-2 py-1">
      <button 
        class="btn btn-sm" 
        :class="tab==='tabel' ? 'btn-success text-white' : 'btn-light'" 
        @click="tab='tabel'"
      >Tabel</button>
      <button 
        class="btn btn-sm" 
        :class="tab==='grafik' ? 'btn-success text-white' : 'btn-light'" 
        @click="tab='grafik'"
      >Grafik</button>
      <button 
        class="btn btn-sm" 
        :class="tab==='metadata' ? 'btn-success text-white' : 'btn-light'" 
        @click="tab='metadata'"
      >Metadata</button>
    </div>
  </div>

  {{-- 📦 Konten --}}
  <div class="card shadow-sm p-4">
    {{-- TABEL --}}
    <div x-show="tab==='tabel'" x-cloak>
      {{-- Per Page Selector --}}
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0">Show</label>
            <select wire:model.live="perPage" class="form-select" style="width: auto;">
              @foreach($perPageOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
              @endforeach
            </select>
            <span class="text-muted">entries</span>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Tahun</th>
              <th>Indikator</th>
              <th>Data</th>
              <th>Satuan</th>
              <th>Status Verifikasi</th>
              <th>SKPD</th>
            </tr>
          </thead>
          <tbody>
            @foreach($tableData as $row)
              <tr>
                <td>{{ $row['tahun'] ?? '-' }}</td>
                <td>{{ $row['Uraian'] ?? '-' }}</td>
                <td>{{ $row['data'] ?? '-' }}</td>
                <td>{{ $row['satuan'] ?? '-' }}</td>
                <td>{{ $row['verifikasi_data'] ?? '-' }}</td>
                <td>{{ $row['skpd'] ?? '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- Pagination Component --}}
      <x-pagination :items="$datasets" />
    </div>

    {{-- GRAFIK --}}
    <div x-show="tab==='grafik'" x-cloak class="mb-3">
      <div class="row g-3 mb-3">
        <div class="col-md-2">
          <label class="form-label">Show</label>
          <select wire:model="chartPerPage" class="form-select">
            @foreach($perPageOptions as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Tipe Chart</label>
          <select x-model="selectedChartType" class="form-select">
            <option value="bar">Bar</option>
            <option value="line">Line</option>
            <option value="pie">Pie</option>
            <option value="doughnut">Doughnut</option>
          </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">X Axis</label>
            <select wire:model="xAxis" class="form-select">
                <option value="tahun">Tahun</option>
                <option value="data">Data</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Y Axis</label>
            <select wire:model="yAxis" class="form-select">
                <option value="data">Data</option>
                <option value="tahun">Tahun</option>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button 
            class="btn btn-primary w-100" 
            wire:click="updateChart"
            @click="applyChartSettings"
          >Terapkan</button>
        </div>
      </div>
      
      {{-- Chart Info --}}
      <div class="alert alert-info mb-3">
        <small>
          <i class="bi bi-info-circle me-1"></i>
          Menampilkan {{ $chartDatasets->count() }} dari {{ $chartDatasets->total() }} data 
          (Halaman {{ $chartDatasets->currentPage() }} dari {{ $chartDatasets->lastPage() }})
        </small>
      </div>

      <canvas 
        id="dataset-chart" 
        wire:ignore 
        style="max-height:400px; width:100%;"
      ></canvas>
    </div>

    {{-- METADATA --}}
    <div x-show="tab==='metadata'" x-cloak>
      <dl class="row mt-3">
        @foreach($metadata as $row)
          <dt class="col-sm-4">{{ $row['label'] }}</dt>
          <dd class="col-sm-8">{{ $row['value'] }}</dd>
        @endforeach
      </dl>
    </div>
  </div>

@push('scripts')
<script>
  document.addEventListener('livewire:init', () => {
    // Listen for chart data updates
    Livewire.on('chartDataUpdated', (data) => {
      console.log('Chart data received:', data);
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
        console.log('Chart rendering already in progress, skipping...');
        return;
      }

      this.isRendering = true;
      console.log('Rendering chart with data:', this.chartData);
      
      if (!Array.isArray(this.chartData) || this.chartData.length === 0) {
        console.log('No valid chart data available');
        this.isRendering = false;
        return;
      }

      const canvas = document.getElementById('dataset-chart');
      if (!canvas) {
        console.log('Canvas element not found');
        this.isRendering = false;
        return;
      }

      this.destroyChart();

      setTimeout(() => {
        try {
          const ctx = canvas.getContext('2d');
          if (!ctx) {
            console.log('Cannot get canvas context');
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
          console.log('Chart created successfully');
          
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