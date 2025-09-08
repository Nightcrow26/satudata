<div>
    <button
        class="dropdown-item px-4"
        type="button"
        @click="open = false"
        wire:click="showProfileModal('{{ auth()->user()->id }}')"
    >
        <i class="bi bi-person-circle me-2"></i>Profile
    </button>
    <template x-teleport="body">
        <div wire:ignore.self wire:key="profile-modal" id="profile-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <form wire:submit.prevent="saveUser">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title">
                    {{ 'Edit User' }}
                </h5>
                <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" wire:key="user-content-{{ auth()->user()->id }}">
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
    </template>
</div>
