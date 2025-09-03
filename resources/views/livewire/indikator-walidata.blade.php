<div>
    <div class="card shadow-sm mb-4">
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
                            <th style="width:50px;">#</th>
                            <th>Satuan</th>
                            <th style="text-align:center;">Tahun</th>
                            <th>Data</th>
                            <th>Instansi</th>
                            <th style="text-align:center;">Aspek</th>
                            <th>Indikator</th>
                            <th>Bidang</th>
                            <th style="width:140px;text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($walidatas as $idx => $wd)
                        <tr>
                            <td>{{ $walidatas->firstItem() + $idx }}</td>
                            <td>{{ $wd->satuan }}</td>
                            <td class="text-center">{{ $wd->tahun }}</td>
                            <td>{{ Str::limit($wd->data, 60) }}</td>
                            <td>{{ Str::limit($wd->skpd?->singkatan ?? $wd->skpd?->nama ?? '-', 30) }}</td>
                            <td class="text-center">
                                <span class="badge text-white"
                                      style="background-color: {{ $wd->aspek->warna ?? '#198754' }}">
                                    {{ $wd->aspek?->nama ?? '—' }}
                                </span>
                            </td>
                            <td>{{ Str::limit($wd->indikator?->uraian_indikator ?? $wd->indikator?->nama ?? '—' , 50) }}</td>
                            <td>{{ STr::limit($wd->bidang?->uraian_bidang ?? $wd->bidang?->nama ?? '—' , 50) }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                                    <a href="{{ route('walidata.show', $wd->id) }}" wire:navigate
                                        class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-success"
                                            wire:click="showEditModal('{{ $wd->id }}')" data-bs-toggle="tooltip" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            wire:click="confirmDelete('{{ $wd->id }}')" data-bs-toggle="tooltip" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                        @if($walidatas->isEmpty())
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Tidak ada data walidata yang cocok.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <x-pagination :items="$walidatas" />
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div wire:ignore.self wire:key="walidata-modal" id="walidata-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <form wire:submit.prevent="saveWalidata">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $walidata_id ? 'Edit Walidata' : 'Tambah Walidata' }}</h5>
                        <button type="button"
                                class="btn-close"
                                wire:click="closeModal"
                                data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Satuan</label>
                                <input type="text"
                                       class="form-control @error('satuan') is-invalid @enderror"
                                       wire:model.defer="satuan">
                                @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tahun</label>
                                <input type="number"
                                       class="form-control @error('tahun') is-invalid @enderror"
                                       wire:model.defer="tahun"
                                       min="1900" max="2100" step="1">
                                @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Data</label>
                                <input type="text"
                                       class="form-control @error('data') is-invalid @enderror"
                                       wire:model.defer="data"
                                       placeholder="Nilai / keterangan">
                                @error('data') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <x-forms.select-tom
                                    id="skpd"
                                    label="Instansi (SKPD)"
                                    model="skpd_id"
                                    :options="$availableSkpds->map(fn($s)=>['id'=>$s->id,'text'=>$s->nama])->values()->all()"
                                    placeholder="-- Pilih SKPD --"
                                    live="true"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-forms.select-tom
                                    id="aspek"
                                    label="Aspek"
                                    model="aspek_id"
                                    :options="$availableAspeks->map(fn($a)=>['id'=>$a->id,'text'=>$a->nama])->values()->all()"
                                    placeholder="-- Pilih Aspek --"
                                    live="true"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-forms.select-tom
                                    id="indikator"
                                    label="Indikator"
                                    model="indikator_id"
                                    :options="$availableIndikators->map(fn($i)=>['id'=>$i->id,'text'=>trim(($i->kode_indikator ?? '').' '.($i->uraian_indikator ?? $i->nama))])->values()->all()"
                                    placeholder="Cari indikator…"
                                    live="true"
                                />
                            </div>

                            <div class="col-md-6">
                                <x-forms.select-tom
                                    id="bidang"
                                    label="Bidang"
                                    model="bidang_id"
                                    :options="$availableBidangs->map(fn($b)=>['id'=>$b->id,'text'=>trim(($b->kode_bidang ? $b->kode_bidang.' - ' : '').($b->uraian_bidang ?? ''))])->values()->all()"
                                    placeholder="-- Pilih Bidang --"
                                    live="true"
                                />
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="closeModal"
                                data-bs-dismiss="modal">Batal</button>
                        <button type="submit"
                                class="btn btn-primary">{{ $walidata_id ? 'Update' : 'Simpan' }}</button>
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
            <p>Yakin ingin menghapus entri walidata <strong>{{ $satuan }} ({{ $tahun }})</strong>?</p>
          </div>
          <div class="modal-footer">
            <button type="button"
                    class="btn btn-secondary"
                    wire:click="cancelDelete"
                    data-bs-dismiss="modal">Batal</button>
            <button type="button"
                    class="btn btn-danger"
                    wire:click="deleteWalidataConfirmed"
                    data-bs-dismiss="modal">Hapus</button>
          </div>
        </div>
      </div>
    </div>
</div>
