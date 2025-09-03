<div>
  <div class="card shadow-sm">
        <div class="card-header py-3 bg-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2">
                {{-- Title + Per-Page --}}
                <div class="d-flex align-items-center flex-wrap">
                <h3 class="mb-0 me-2">Indikator Walidata</h3>
                <select wire:model.live="perPage"
                        class="form-select form-select-sm w-auto shadow-sm border border-success rounded ms-0 ms-sm-2 mt-2 mt-sm-0">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                </div>

                {{-- Actions + Search --}}
                <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">

                {{-- Sinkron Data (admin only) - TANPA IKON --}}
                @if (auth()->user()->hasRole('admin'))
                    <button type="button"
                            wire:click="sinkronWalidata"
                            wire:loading.attr="disabled"
                            class="btn btn-success"
                            aria-label="Sinkron Data">
                    <span wire:loading.remove wire:target="sinkronWalidata">Sinkron Data</span>
                    <span wire:loading wire:target="sinkronWalidata">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Memproses…
                    </span>
                    </button>
                @endif

                {{-- Tambah - TANPA IKON --}}
                <button type="button"
                        wire:click="showCreateModal"
                        class="btn btn-success"
                        aria-label="Tambah">
                    Tambah
                </button>

                {{-- Search --}}
                <div class="input-group">
                    <input type="text"
                        wire:model.live.debounce.300ms="search"
                        class="form-control"
                        placeholder="Cari satuan, tahun, atau data…">
                </div>
              </div>
          </div>
    </div>

    <div class="card-body p-3">
      <div class="table-responsive">
          <table class="table table-borderless mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 50px;">#</th>
              <th>Nama</th>
              <th>Singkatan</th>
              <th>Alamat</th>
              <th style="text-align:center">Telepon</th>
              <th style="width: 140px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {{-- Pesan jika tidak ada data --}}
            {{-- Looping SKPD --}}
            @foreach($skpds as $idx => $skpd)
              <tr>
                <td>{{ $skpds->firstItem() + $idx }}</td>
                <td>{{ Str::limit($skpd->nama, 50) }}</td>
                <td>{{ $skpd->singkatan }}</td>
                <td>{{ Str::limit($skpd->alamat, 30) }}</td>
                <td style="text-align:center">{{ $skpd->telepon }}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                        <button type="button" class="btn btn-outline-success"
                                wire:click="showEditModal('{{ $skpd->id }}')" data-bs-toggle="tooltip" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if (auth()->user()->hasRole('admin'))
                        <button type="button" class="btn btn-outline-danger"
                                wire:click="confirmDelete('{{ $skpd->id }}')" data-bs-toggle="tooltip" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                        @endif
                    </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <x-pagination :items="$skpds" />
      </div>
    </div>

    <div  wire:key="skpd-modal" id="skpd-modal" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <form wire:submit.prevent="saveSkpd">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                {{ $skpd_id ? 'Edit SKPD' : 'Tambah SKPD' }}
              </h5>
              <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" wire:key="skpd-content-{{ $skpd_id ?: 'new' }}">
              <div class="mb-3">
                <label>Nama</label>
                <input type="text" class="form-control" wire:model.defer="nama">
              </div>
              <div class="mb-3">
                <label>Singkatan</label>
                <input type="text" class="form-control" wire:model.defer="singkatan">
              </div>
              <div class="mb-3">
                <label>Alamat</label>
                <textarea class="form-control" wire:model.defer="alamat"></textarea>
              </div>
              <div class="mb-3">
                <label>Telepon</label>
                <input type="text" class="form-control" wire:model.defer="telepon">
              </div>
              <div class="mb-3">
                <label>Foto SKPD</label>
                <input type="file" class="form-control" wire:model="foto">
              </div>
              {{-- Preview foto baru / lama --}}
              @if($foto)
                <img src="{{ $foto->temporaryUrl() }}" class="img-thumbnail" style="width:80px">
              @elseif($editingSkpd?->foto)
                @php
                    $fotoKey = $editingSkpd->foto;          // nilai kolom foto di DB
                @endphp

                @if($fotoKey && Storage::disk('s3')->exists($fotoKey))
                    {{-- Jika file ada di S3, gunakan temporaryUrl --}}
                    <img
                      src="{{ Storage::disk('s3')->temporaryUrl($fotoKey, now()->addMinutes(15)) }}"
                      alt="Logo SKPD"
                      class="img-thumbnail" style="width:80px"
                    >
                @elseif($fotoKey && file_exists(public_path($fotoKey)))
                    {{-- Jika file tidak di S3 tapi ada di public, gunakan asset --}}
                    <img
                      src="{{ asset($fotoKey) }}"
                      alt="Logo SKPD"
                      class="img-thumbnail" style="width:80px"
                    >
                @else
                    {{-- Fallback: logo default --}}
                    <img
                      src="{{ asset('logo-hsu.png') }}"
                      alt="Logo Default HSU"
                      class="img-thumbnail" style="width:80px"
                    >
                @endif
              @endif
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" wire:click="closeModal" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">
                {{ $skpd_id ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>


  {{-- Modal Konfirmasi Hapus --}}
    <div wire:ignore.self wire:key="skpd-modal" id="delete-modal" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" wire:click="cancelDelete" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus SKPD ini?</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" wire:click="cancelDelete" data-bs-dismiss="modal">
              Batal
            </button>
            <button type="button" class="btn btn-danger" wire:click="deleteSkpdConfirmed">
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
</div>