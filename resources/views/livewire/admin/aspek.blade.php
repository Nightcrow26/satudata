<div>
  <div class="card shadow-sm">
        <div class="card-header py-3 bg-white">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2">
              {{-- Title + Per-Page --}}
              <div class="d-flex align-items-center flex-wrap">
              <h3 class="mb-0 me-2">Aspek</h3>
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
              <th style="width:50px;">#</th>
              <th>Nama</th>
              <th style="text-align:center">Warna</th>
              <th style="text-align:center">Foto</th>
              <th style="width:140px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($aspeks as $idx => $aspek)
              <tr>
                <td>{{ $aspeks->firstItem() + $idx }}</td>
                <td>{{ $aspek->nama }}</td>
                <td style="text-align:center">
                  <span class="badge" style="background-color:{{ $aspek->warna }}; color:#fff;">
                    {{ $aspek->warna }}
                  </span>
                </td>
                <td style="text-align:center">
                  @if($aspek->foto)
                    <img src="{{ Storage::disk('s3')->temporaryUrl($aspek->foto, now()->addMinutes(15)) }}" class="rounded class mx-auto w-auto" style="width:50px; height:50px; object-fit:cover;">
                  @endif
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                        <button type="button" class="btn btn-outline-success"
                                wire:click="showEditModal('{{ $aspek->id }}')" data-bs-toggle="tooltip" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger"
                                wire:click="confirmDelete('{{ $aspek->id }}')" data-bs-toggle="tooltip" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        <x-admin.pagination :items="$aspeks" />
      </div>
    </div>
  </div>

  {{-- Modal Tambah/Edit --}}
  <div wire:ignore.self wire:key="aspek-modal" id="aspek-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <form wire:submit.prevent="saveAspek">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $aspek_id ? 'Edit Aspek' : 'Tambah Aspek' }}</h5>
            <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" wire:key="aspek-content-{{ $aspek_id ?: 'new' }}">
            <div class="mb-3">
              <label>Nama Aspek</label>
              <input type="text" class="form-control @error('nama') is-invalid @enderror" wire:model.defer="nama">
              @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
              <label>Warna</label>
              <input type="color" class="form-control form-control-color @error('warna') is-invalid @enderror" wire:model.defer="warna">
              @error('warna') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
              <label>Foto Aspek</label>
              <input type="file" class="form-control @error('foto') is-invalid @enderror" wire:model="foto" accept="image/*">
              @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @if($foto)
              <img src="{{ $foto->temporaryUrl() }}" class="img-thumbnail" style="width:80px">
            @elseif($editingAspek?->foto)
              <img src="{{  Storage::disk('s3')->temporaryUrl($editingAspek->foto, now()->addMinutes(15)) }}" class="img-thumbnail" style="width:80px">
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" wire:click="closeModal" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">{{ $aspek_id ? 'Update' : 'Simpan' }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Konfirmasi Hapus --}}
  <div wire:ignore.self id="delete-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close" wire:click="cancelDelete" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Yakin ingin menghapus aspek?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" wire:click="cancelDelete" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" wire:click="deleteAspekConfirmed">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</div>
