<div>
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3 bg-white">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2">
                {{-- Title + Per-Page --}}
                <div class="d-flex align-items-center flex-wrap">
                <h3 class="mb-0 me-2">Dataset</h3>
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
                            <th>Instansi</th>
                            <th style="text-align:center">Aspek</th>
                            <th style="text-align:center">Status</th>
                            <th style="text-align:center">Views</th>
                            <th style="text-align:center">Berkas</th>
                            <th style="width:140px;text-align:center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($datasets as $idx => $ds)
                        <tr>
                            <td class="text-center">{{ $datasets->firstItem() + $idx }}</td>

                            {{-- Nama dataset dengan truncation + tooltip --}}
                            <td>
                                <span class="truncate" data-bs-toggle="tooltip" title="{{ $ds->nama }}">
                                {{ $ds->nama }}
                                </span>
                            </td>

                            {{-- Instansi (singkatan -> nama) --}}
                            <td>
                                @php
                                $labelInstansi = $ds->skpd?->singkatan ?: ($ds->skpd?->nama ?? '-');
                                @endphp
                                <span class="truncate" data-bs-toggle="tooltip" title="{{ $labelInstansi }}">
                                {{ Str::limit($labelInstansi, 30) }}
                                </span>
                            </td>

                            {{-- Aspek badge --}}
                            <td class="text-center">
                                <span class="badge text-white"
                                    style="background-color: {{ $ds->aspek->warna ?? '#6c757d' }}">
                                {{ $ds->aspek?->nama ?? 'Undefined' }}
                                </span>
                            </td>

                            {{-- Status badge kecil --}}
                            <td class="text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full text-white animate-pulse
                                    {{ match($ds->status) {
                                    'published' => 'bg-green-500',
                                    'pending'   => 'bg-yellow-500',
                                    'draft'     => 'bg-gray-500',
                                    } }}">
                                    {{ ucfirst($ds->status) }}
                                </span>
                            </td>

                            {{-- Views kanan --}}
                            <td style="text-align:center">{{ number_format($ds->view ?? 0, 0, ',', '.') }}</td>

                            {{-- Gabungan berkas --}}
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Berkas">
                                @if($ds->excel)
                                    <a class="btn btn-outline-success"
                                    href="{{ Storage::disk('s3')->temporaryUrl($ds->excel, now()->addMinutes(15)) }}"
                                    target="_blank" data-bs-toggle="tooltip" title="Unduh Excel">
                                        <i class="bi bi-file-earmark-spreadsheet"></i>
                                    </a>
                                @endif
                                @if($ds->metadata)
                                    <a class="btn btn-outline-success"
                                    href="{{ Storage::disk('s3')->temporaryUrl($ds->metadata, now()->addMinutes(15)) }}"
                                    target="_blank" data-bs-toggle="tooltip" title="Unduh Metadata">
                                        <i class="bi bi-archive"></i>
                                    </a>
                                @endif
                                @if($ds->bukti_dukung ?? false)
                                    <a class="btn btn-outline-danger"
                                    href="{{ Storage::disk('s3')->temporaryUrl($ds->bukti_dukung, now()->addMinutes(15)) }}"
                                    target="_blank" data-bs-toggle="tooltip" title="Bukti Dukung (PDF)">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </a>
                                @endif
                                </div>
                            </td>

                            {{-- Aksi ringkas --}}
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                                    <a href="{{ route('dataset.show', $ds->id) }}" wire:navigate
                                        class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success"
                                            wire:click="showEditModal('{{ $ds->id }}')" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            wire:click="confirmDelete('{{ $ds->id }}')" data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada dataset yang ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <x-admin.pagination :items="$datasets" />
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div wire:ignore.self wire:key="dataset-modal" id="dataset-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form wire:submit.prevent="saveDataset">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $dataset_id ? 'Edit Dataset' : 'Tambah Dataset' }}</h5>
                        <button type="button"
                                class="btn-close"
                                wire:click="closeModal"
                                data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text"
                                       class="form-control @error('nama') is-invalid @enderror"
                                       wire:model.defer="nama">
                                @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>

                                <select class="form-select @error('status') is-invalid @enderror"
                                        wire:model.defer="status">
                                    <option value="">-- Pilih Status --</option>
                                    <option value="draft">Draft</option>
                                    @if (auth()->user()->hasRole('admin'))
                                        <option value="pending">Pending</option>
                                        <option value="published">Published</option>
                                    @endif
                                </select>

                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">File Excel</label>
                                <input type="file"
                                       class="form-control @error('excel') is-invalid @enderror"
                                       wire:model="excel"
                                       accept=".xls, .xlsx, .csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                       >
                                @error('excel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($excel)
                                    <small class="text-muted">Preview: {{ $excel->getClientOriginalName() }}</small>
                                @elseif($editingDataset?->excel)
                                    <small class="text-muted">
                                        Existing:
                                        <a href="{{ Storage::disk('s3')
                                            ->temporaryUrl($editingDataset->excel, now()->addMinutes(15)) }}"
                                        target="_blank">
                                        {{ basename($editingDataset->excel) }}
                                        </a>
                                    </small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Metadata (Excel)</label>
                                <input type="file"
                                       class="form-control @error('metadata') is-invalid @enderror"
                                       wire:model="metadata" accept=".xls, .xlsx, .csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                       >
                                @error('metadata') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($metadata)
                                    <small class="text-muted">Preview: {{ $metadata->getClientOriginalName() }}</small>
                                @elseif($editingDataset?->metadata)
                                    <small class="text-muted">
                                        Existing:
                                        <a href="{{ Storage::disk('s3')
                                            ->temporaryUrl($editingDataset->metadata, now()->addMinutes(15)) }}"
                                        target="_blank">
                                        {{ basename($editingDataset->metadata) }}
                                        </a>
                                    </small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Bukti Dukung (PDF)</label>
                                <input type="file" class="form-control @error('bukti_dukung') is-invalid @enderror"
                                    wire:model="bukti_dukung" accept="application/pdf">
                                @error('bukti_dukung') <span class="invalid-feedback">{{ $message }}</span> @enderror

                                @if(optional($editingDataset)->bukti_dukung)
                                    <small class="text-muted">
                                        File saat ini: <a href="{{ Storage::disk('s3')->temporaryUrl($editingDataset->bukti_dukung, now()->addMinutes(15)) }}" target="_blank"> {{ basename($editingDataset->bukti_dukung) }}</a>
                                    </small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tahun</label>
                                <input type="number"
                                       class="form-control @error('tahun') is-invalid @enderror"
                                       wire:model.defer="tahun">
                                @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <select class="form-select @error('aspek_id') is-invalid @enderror"
                                        wire:model.defer="aspek_id">
                                    <option value="">-- Pilih Aspek --</option>
                                    @foreach($availableAspeks as $aspek)
                                        <option value="{{ $aspek->id }}">{{ $aspek->nama }}</option>
                                    @endforeach
                                </select>
                                @error('aspek_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Deskripsi</label>
                                <div wire:ignore>
                                    <textarea 
                                        id="dataset-deskripsi-editor" 
                                        class="form-control summernote"
                                    ></textarea>
                                </div>
                                <input 
                                    type="hidden" 
                                    wire:model.defer="deskripsi" 
                                    id="dataset-deskripsi-editor-hidden"
                                    value="{{ $deskripsi }}"
                                >
                                @error('deskripsi')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Keyword</label>
                                <input type="text"
                                       class="form-control @error('keyword') is-invalid @enderror"
                                       wire:model.defer="keyword">
                                @error('keyword') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            
                            <div class="col-md-6">
                                <label class="form-label">Catatan Verifikasi</label>

                                <input type="text"
                                    class="form-control @error('catatan_verif') is-invalid @enderror"
                                    wire:model.defer="catatan_verif"
                                    {{ auth()->user()->hasRole('user') ? 'disabled' : '' }}>

                                @error('catatan_verif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="closeModal"
                                data-bs-dismiss="modal">Batal</button>
                        <button type="submit"
                                class="btn btn-primary">{{ $dataset_id ? 'Update' : 'Simpan' }}</button>
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
            <button type="button" class="btn-close"
                    wire:click="cancelDelete"
                    data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Yakin ingin menghapus dataset <strong>{{ $nama }}</strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-secondary"
                    wire:click="cancelDelete"
                    data-bs-dismiss="modal">Batal</button>
            <button type="button"
                    class="btn btn-danger"
                    wire:click="deleteDatasetConfirmed"
                    data-bs-dismiss="modal">Hapus</button>
          </div>
        </div>
      </div>
    </div>
</div>
