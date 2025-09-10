<div>
  <div class="card shadow-sm">
    <div class="card-header py-3 bg-white">
          <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2">
              {{-- Title + Per-Page --}}
              <div class="d-flex align-items-center flex-wrap">
              <h3 class="mb-0 me-2">Publikasi</h3>
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
              <th style="width:50px;text-align:center">#</th>
              <th>Nama</th>
              <th style="text-align:center">Tahun</th>
              <th>SKPD</th>
              <th style="text-align:center">Aspek</th>
              <th style="text-align:center">Status</th>
              <th style="text-align:center">Views</th>
              <th style="text-align:center">Download</th>
              <th style="width:140px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($publikasis as $i => $pub)
              <tr>
                <td style="text-align:center">{{ $publikasis->firstItem() + $i }}</td>
                <td>{{ $pub->nama }}</td>
                <td style="text-align:center">{{ $pub->tahun }}</td>
                <td>{{ Str::limit($pub->skpd?->singkatan ?? $pub->skpd?->nama ?? '-', 30) }}</td>
                <td style="text-align:center"><span class="badge text-white" style="background-color: {{ $pub->aspek->warna ?? '#198754' }}">{{ $pub->aspek?->nama}}</span></td>
                <td class="text-center">
                    <span class="px-2 py-1 text-xs font-semibold rounded-full text-white animate-pulse
                        {{ match($pub->status) {
                        'published' => 'bg-green-500',
                        'pending'   => 'bg-yellow-500',
                        'draft'     => 'bg-gray-500',
                        } }}">
                        {{ ucfirst($pub->status) }}
                    </span>
                </td>
                <td style="text-align:center">{{ $pub->view }}</td>
                <td style="text-align:center">
                    @if($pub->pdf)
                        <a class="btn btn-outline-danger btn-sm"
                          href="{{ Storage::disk('s3')->temporaryUrl($pub->pdf, now()->addMinutes(15)) }}"
                          target="_blank" data-bs-toggle="tooltip" title="Publikasi (PDF)">
                          <i class="bi bi-filetype-pdf"></i>
                        </a>
                    @endif
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                      <button type="button" class="btn btn-outline-success"
                              wire:click="showEditModal('{{ $pub->id }}')" data-bs-toggle="tooltip" title="Edit">
                          <i class="bi bi-pencil"></i>
                      </button>
                      <button type="button" class="btn btn-outline-danger"
                              wire:click="confirmDelete('{{ $pub->id }}')" data-bs-toggle="tooltip" title="Hapus">
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
        <x-admin.pagination :items="$publikasis" />
      </div>
    </div>
  </div>

  {{-- Modal Create / Edit --}}
  <div wire:ignore.self id="publikasi-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <form wire:submit.prevent="savePublikasi">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $publikasi_id ? 'Edit Publikasi' : 'Tambah Publikasi' }}</h5>
            <button type="button" class="btn-close" wire:click="cancelDelete" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" wire:model.defer="nama"
                      class="form-control @error('nama') is-invalid @enderror">
                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Status</label>
                <select wire:model.defer="status"
                        class="form-select @error('status') is-invalid @enderror">
                  <option value="draft">Draft</option>
                  @if (auth()->user()->hasRole('admin'))
                    <option value="pending">Pending</option>
                    <option value="published">Published</option>
                  @endif
                </select>
                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <x-forms.select-tom
                    id="skpd"
                    label="Instansi"
                    model="instansi_id"
                    :options="$availableSkpds->map(fn($s)=>['id'=>$s->id,'text'=>$s->nama])->values()->all()"
                    placeholder="-- Pilih SKPD --"
                    live="true"
                />
                @error('instansi_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">Aspek</label>
                <select wire:model.defer="aspek_id"
                        class="form-select @error('aspek_id') is-invalid @enderror">
                  <option value="">-- Pilih Aspek --</option>
                  @foreach($availableAspeks as $asp)
                    <option value="{{ $asp->id }}">{{ $asp->nama }}</option>
                  @endforeach
                </select>
                @error('aspek_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">File PDF</label>
                <input type="file" wire:model="pdf"
                      class="form-control @error('pdf') is-invalid @enderror" accept="application/pdf,.pdf">
                @error('pdf')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($pdf)
                  <small class="text-muted">Preview: {{ $pdf->getClientOriginalName() }}</small>
                @elseif($editingPublikasi?->pdf)
                    <small class="text-muted">
                        Existing:
                        <a href="{{ Storage::disk('s3')
                            ->temporaryUrl($editingPublikasi->pdf, now()->addMinutes(15)) }}"
                        target="_blank">
                        {{ basename($editingPublikasi->pdf) }}
                        </a>
                    </small>
                @endif
              </div>
              <div class="col-md-6">
                <label class="form-label">Foto</label>
                <input type="file" wire:model="foto"
                      class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($foto)
                  <img src="{{ $foto->temporaryUrl() }}"
                      class="img-thumbnail mt-1" style="width:80px">
                @elseif($editingPublikasi?->foto)
                  <img src="{{ Storage::disk('s3')->temporaryUrl($editingPublikasi->foto, now()->addMinutes(15)) }}"
                      class="img-thumbnail mt-1" style="width:80px">
                @endif
              </div>

              <div class="col-md-6">
                <label class="form-label">Tahun</label>
                <input type="number" wire:model.defer="tahun"
                      class="form-control @error('tahun') is-invalid @enderror">
                @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">Keyword</label>
                <input type="text" wire:model.defer="keyword"
                      class="form-control @error('keyword') is-invalid @enderror">
                @error('keyword')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

             <div class="col-md-12">
                <label class="form-label">Deskripsi</label>
                <div wire:ignore>
                    <textarea 
                        id="publikasi-deskripsi-editor" 
                        class="form-control summernote"
                    ></textarea>
                </div>
                <input 
                    type="hidden" 
                    wire:model.defer="deskripsi" 
                    id="publikasi-deskripsi-editor-hidden"
                    value="{{ $deskripsi }}"
                >
                @error('deskripsi')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
             </div>

             <div class="col-md-12">
                <label class="form-label">Catatan Verif</label>
                <textarea wire:model.defer="catatan_verif"
                          class="form-control @error('catatan_verif') is-invalid @enderror" 
                          {{ auth()->user()->hasRole('user') ? 'disabled' : '' }}></textarea>
                @error('catatan_verif')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary"
                    wire:click="cancelDelete" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">
              {{ $publikasi_id ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- Modal Hapus --}}
  <div wire:ignore.self id="delete-modal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
          <button type="button" class="btn-close" wire:click="cancelDelete"></button>
        </div>
        <div class="modal-body">
          <p>Yakin ingin menghapus publikasi <strong>{{ $nama }}</strong>?</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" wire:click="cancelDelete" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger" wire:click="deletePublikasiConfirmed" data-bs-dismiss="modal">Hapus</button>
        </div>
      </div>
    </div>
  </div>
</div>