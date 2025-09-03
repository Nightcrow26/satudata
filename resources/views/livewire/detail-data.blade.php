<div 
  x-data="datasetChart()" 
  x-init="init()" 
  wire:ignore.self
>
  {{-- 🔷 Header Instansi --}}
  <div class="card mb-3 shadow-sm px-3 py-3 border border-1">
    <div class="d-flex align-items-center gap-3">
      @php
          $fotoKey = $dataset->skpd->foto;  
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
        {{ $dataset->skpd->nama }}
      </strong>
    </div>
  </div>

  {{-- 📄 Info Dataset --}}
  <div class="card mb-3 shadow-sm px-4 py-3 border position-relative" style="min-height:140px;">
    <div class="d-flex justify-content-between align-items-start gap-3">
      <div class="flex-grow-1" style="min-width:0;">
        <h6 class="fw-bold mb-2 text-truncate">{{ $dataset->nama }}</h6>
        <div class="d-flex gap-2 mb-2 flex-wrap">
          <span class="badge text-white" style="background-color: {{ $dataset->aspek->warna ?? '#198754' }}">
            {{ $dataset->aspek->nama }}
          </span>
        </div>
        <div class="text-muted small text-wrap editor-output">{!! $dataset->deskripsi !!}</div>
      </div>
      <div class="text-end text-muted small" style="white-space:nowrap; min-width:150px;">
        <span><i class="bi bi-calendar me-1"></i> {{ $dataset->created_at->format('d F Y') }}</span>
        <span><i class="bi bi-eye me-1"></i> {{ $dataset->view }}</span>
      </div>
    </div>
    <div class="position-absolute bottom-0 end-0 mb-3 me-3 d-flex gap-2">
      @livewire('download-pdf', ['dataset' => $dataset])
      <a href="{{ $dataset->excel_url }}" class="btn btn-sm btn-outline-success btn-icon" title="Excel">
        <i class="bi bi-file-earmark-excel-fill"></i>
      </a>
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
        :class="tab==='map' ? 'btn-success text-white' : 'btn-light'" 
        @click="tab='map'"
      >
        <i class="bi bi-geo-alt me-1"></i>Peta
      </button>
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
              @foreach($columns as $col)
                <th>{{ $col }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($tableData as $row)
              <tr>
                @foreach($columns as $col)
                  <td>{{ $row[$col] ?? '-' }}</td>
                @endforeach
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
                @foreach($columns as $col)
                <option value="{{ $col }}">{{ $col }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Y Axis</label>
            <select wire:model="yAxis" class="form-select">
                @foreach($columns as $col)
                <option value="{{ $col }}">{{ $col }}</option>
                @endforeach
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

    {{-- MAP --}}
    <div x-show="tab==='map'" x-cloak class="mb-3">
      <div class="row g-3 mb-3">
        <div class="col-md-2">
          <label class="form-label">Show</label>
          <select wire:model="mapPerPage" class="form-select">
            @foreach($perPageOptions as $option)
              <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Latitude</label>
          <select wire:model="latitudeColumn" class="form-select">
            <option value="">-- Pilih Latitude --</option>
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Longitude</label>
          <select wire:model="longitudeColumn" class="form-select">
            <option value="">-- Pilih Longitude --</option>
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Label</label>
          <select wire:model="labelColumn" class="form-select">
            <option value="">-- Pilih Label --</option>
            @foreach($columns as $col)
              <option value="{{ $col }}">{{ $col }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button 
            class="btn btn-primary w-100" 
            wire:click="applyMapSettings"
            @click="applyMapSettings"
            :disabled="!latitudeColumn || !longitudeColumn"
          >Terapkan</button>
        </div>
      </div>
      
      {{-- Map Info --}}
      <div class="alert alert-info mb-3" x-show="mapData && mapData.length > 0">
        <small>
          <i class="bi bi-info-circle me-1"></i>
          <span x-text="`Menampilkan ${mapData ? mapData.length : 0} lokasi`"></span>
        </small>
      </div>

      {{-- Map Setup Info --}}
      <div class="alert alert-warning mb-3" x-show="!latitudeColumn || !longitudeColumn">
        <small>
          <i class="bi bi-info-circle me-1"></i>
          Pilih kolom latitude dan longitude untuk menampilkan peta
        </small>
      </div>

      <div 
        id="dataset-map" 
        wire:ignore 
        style="height:500px; width:100%; border-radius:8px; background-color: #f8f9fa;"
        x-show="latitudeColumn && longitudeColumn"
      ></div>
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

    // Listen for map data updates
    Livewire.on('mapDataUpdated', (data) => {
      console.log('Map data received:', data);
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
    selectedChartType: 'bar',
    chartData: @entangle('chartData').live,
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
            setTimeout(() => this.renderChart(), 150);
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
            setTimeout(() => this.renderChart(), 150);
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
      this.chartType = this.selectedChartType;
      this.$wire.call('updateChart');
      
      if (this.tab === 'grafik' && !this.isRendering) {
        this.$nextTick(() => {
          setTimeout(() => this.renderChart(), 150);
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
      this.chartData = Array.isArray(data) ? data : data[0] || [];
      
      if (this.tab === 'grafik' && !this.isRendering) {
        this.$nextTick(() => {
          setTimeout(() => this.renderChart(), 150);
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
          console.log('Chart created successfully');
          
        } catch (error) {
          console.error('Error creating chart:', error);
        } finally {
          this.isRendering = false;
        }
      }, 200);
    },

    renderMap() {
      if (this.isMapRendering) {
        console.log('Map rendering already in progress, skipping...');
        return;
      }

      if (!this.latitudeColumn || !this.longitudeColumn) {
        console.log('Latitude or longitude column not selected');
        return;
      }

      // Cek apakah Leaflet sudah siap
      if (typeof L === 'undefined') {
        console.log('Leaflet not loaded yet, retrying...');
        setTimeout(() => this.renderMap(), 500);
        return;
      }

      this.isMapRendering = true;
      console.log('Rendering map...');

      const mapContainer = document.getElementById('dataset-map');
      if (!mapContainer) {
        console.log('Map container not found');
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

        console.log('Map initialized');

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

      console.log('Updating map markers with data:', data);

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

      console.log(`Added ${this.mapMarkers.length} markers to map`);
    }
}
}
</script>