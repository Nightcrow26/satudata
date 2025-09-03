<div>
  <div class="card shadow-sm">
    <div class="card-header py-3 bg-white">
      <div class="d-flex flex-column flex-lg-row justify-content-between align-items-stretch align-items-lg-center gap-2">
          {{-- Title + Per-Page --}}
          <div class="d-flex align-items-center flex-wrap">
          <h3 class="mb-0 me-2">Pengguna</h3>
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
              <th style="width: 50px;">#</th>
              <th>Name</th>
              <th>Email</th>
              <th style="text-align:center">NIK</th>
              <th>SKPD</th>   
              <th style="text-align:center">Role</th>
              <th style="width: 140px;text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $idx => $user)
              <tr>
                <td>{{ $users->firstItem() + $idx }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ Str::limit($user->email, 30) }}</td>
                <td style="text-align:center">{{ $user->nik}}</td>  
                <td>{{ Str::limit($user->skpd?->nama, 30) }}</td>
                @php
                    // Map role => varian bootstrap (tanpa prefix "bg-")
                    $badgeClasses = [
                        'admin'       => 'success',
                        'verifikator' => 'info',
                        'user'        => 'secondary',
                    ];
                @endphp

                <td class="text-center">
                    @foreach($user->getRoleNames() as $r)
                        @php
                            $variant = $badgeClasses[strtolower($r)] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $variant }}">{{ ucfirst($r) }}</span>
                    @endforeach
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                        <button type="button" class="btn btn-outline-success"
                                wire:click="showEditModal('{{ $user->id }}')" data-bs-toggle="tooltip" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @if(auth()->user()->id !== $user->id)
                        <button type="button" class="btn btn-outline-danger"
                                wire:click="confirmDelete('{{ $user->id }}')" data-bs-toggle="tooltip" title="Hapus">
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
        <x-pagination :items="$users" />
      </div>
    </div>
  </div>

    <div wire:ignore.self wire:key="user-modal" id="user-modal" class="modal fade" tabindex="-1">
      <div class="modal-dialog">
        <form wire:submit.prevent="saveUser">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                {{ $user_id ? 'Edit User' : 'Tambah User Baru' }}
              </h5>
              <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" wire:key="user-content-{{ $user_id ?: 'new' }}">
              <div class="mb-3">
                <label class="form-label">Nama</label>
                <input 
                  type="text" 
                  class="form-control @error('name') is-invalid @enderror" 
                  wire:model.defer="name"
                >
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input 
                  type="email" 
                  class="form-control @error('email') is-invalid @enderror" 
                  wire:model.defer="email"
                >
                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
              </div>

              <!-- Perbaikan pada bagian tombol Cari NIK -->
              <div class="mb-3">
                <label class="form-label">NIK</label>
                <div class="input-group" wire:key="nik-search-group">
                  <input
                    type="text"
                    class="form-control @error('nik') is-invalid @enderror"
                    wire:model.defer="nik"
                    placeholder="Masukkan NIK"
                  >

                  <!-- Tombol Cari dengan perbaikan -->
                  <button
                    type="button"
                    class="btn btn-outline-primary"
                    wire:click="cariDataAsn"
                    wire:loading.attr="disabled"
                    wire:target="cariDataAsn"
                  >
                    <span wire:loading.remove wire:target="cariDataAsn">
                      <i class="bi bi-search"></i> Cari
                    </span>
                    <span wire:loading wire:target="cariDataAsn"
                          class="spinner-border spinner-border-sm me-1"
                          role="status"
                          aria-hidden="true"
                    ></span>
                    <span wire:loading wire:target="cariDataAsn">Mencari...</span>
                  </button>
                </div>
                @error('nik') <span class="invalid-feedback">{{ $message }}</span> @enderror
              </div>

              <div class="mb-3">
                  <x-forms.select-tom
                      id="skpd"
                      label="Instansi"
                      model="skpd_uuid"
                      :options="$availableSkpds->map(fn($s)=>['id'=>$s->id,'text'=>$s->nama])->values()->all()"
                      placeholder="-- Pilih SKPD --"
                      live="true"
                  />
                  @error('skpd_uuid') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

              <div class="mb-3">
                <label class="form-label">Role</label>
                <select 
                  class="form-select @error('role') is-invalid @enderror"
                  wire:model.defer="role" {{ auth()->user()->id === optional($editingUser)->id ? 'disabled' : '' }}
                >
                  <option value="">-- Pilih Role --</option>
                  @foreach($availableRoles as $roleName)
                    <option value="{{ $roleName }}">{{ ucfirst($roleName) }}</option>
                  @endforeach
                </select>
                @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" wire:click="closeModal" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">
                {{ $user_id ? 'Update' : 'Simpan' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div wire:ignore.self id="delete-modal" class="modal fade" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">Konfirmasi Hapus</h5>
            <button type="button" class="btn-close" wire:click="cancelDelete"></button>
          </div>
          <div class="modal-body">
            <p>Apakah Anda yakin ingin menghapus user <strong>{{ $name }}</strong>?</p>
          </div>
          <div class="modal-footer">
            <button 
              type="button" 
              class="btn btn-secondary" 
              wire:click="cancelDelete"
              data-bs-dismiss="modal"
            >
              Batal
            </button>
            <button 
              type="button" 
              class="btn btn-danger" 
              wire:click="deleteUserConfirmed"
              data-bs-dismiss="modal"
            >
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>
</div>
