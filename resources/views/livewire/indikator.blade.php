<div>
  <div class="card shadow-sm">
    <div class="card-header py-3 bg-white">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2">
              {{-- Title + Per-Page --}}
              <div class="d-flex align-items-center flex-wrap">
              <h3 class="mb-0 me-2">Indikator Bidang</h3>
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
              <th>Kode Indikator</th>
              <th>Uraian</th>
              <th>Bidang</th>
              <th style="width:140px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($indikators as $idx => $indikator)
              <tr>
                <td>{{ $indikators->firstItem() + $idx }}</td>
                <td>{{ $indikator->kode_indikator }}</td>
                <td>{{ $indikator->uraian_indikator }}</td>
                <td>{{ $indikator->bidang->uraian_bidang ?? '-' }}</td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                        <button type="button" class="btn btn-outline-success"
                                wire:click="showEditModal('{{ $indikator->id }}')" data-bs-toggle="tooltip" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger"
                                wire:click="confirmDelete('{{ $indikator->id }}')" data-bs-toggle="tooltip" title="Hapus">
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
        <x-pagination :items="$indikators" />
      </div>
    </div>
  </div>

  {{-- Modal Tambah/Edit --}}
  <div wire:ignore.self id="indikator-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog">
      <form wire:submit.prevent="saveIndikator">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $indikator_id ? 'Edit Indikator' : 'Tambah Indikator' }}</h5>
            <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Kode Indikator</label>
              <input type="text" class="form-control @error('kode_indikator') is-invalid @enderror" wire:model.defer="kode_indikator" placeholder="Masukkan kode indikator">
              @error('kode_indikator') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
              <label>Uraian Indikator</label>
              <input type="text" class="form-control @error('uraian_indikator') is-invalid @enderror" wire:model.defer="uraian_indikator" placeholder="Masukkan uraian indikator">
              @error('uraian_indikator') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <x-forms.select-tom
                    id="bidang"
                    label="Bidang"
                    model="bidang_id"
                    :options="$bidangs->map(fn($b)=>['id'=>$b->id,'text'=>trim(($b->kode_bidang ? $b->kode_bidang.' - ' : '').($b->uraian_bidang ?? ''))])->values()->all()"
                    placeholder="-- Pilih Bidang --"
                    live="true"
                />
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" wire:click="closeModal" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">{{ $indikator_id ? 'Update' : 'Simpan' }}</button>
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
          <p>Yakin ingin menghapus indikator ini?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" wire:click="cancelDelete" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" wire:click="deleteIndikatorConfirmed">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</div>
