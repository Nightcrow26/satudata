<div>
    <button type="button"
    class="w-full text-left px-3 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center"
    @click="$dispatch('profile:open')">
    <i class="bi bi-person-circle mr-2"></i>Profile
    </button>

    <template x-teleport="body">
    <!-- WRAPPER OVERLAY -->
    <div wire:ignore.self wire:key="profile-modal"
        x-data="userProfileModal()"
        x-init="init()"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">

    <!-- BACKDROP -->
    <div class="fixed inset-0 bg-black/50 z-40" @click="close()"></div>

        <!-- CONTAINER + PANEL -->
    <div class="flex items-center justify-center min-h-screen p-4 relative z-50">
    <div x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="bg-white dark:!bg-gray-800 rounded-lg shadow-xl w-full max-w-md">
            <form wire:submit.prevent="saveUser">
            <!-- header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:!bg-gray-800 border-t rounded-t-lg">
                <h5 class="text-lg font-semibold text-gray-900 dark:text-white">Edit User</h5>
                <button type="button"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                @click="close()">
                <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>

            <!-- body (boleh Tailwind/Bootstrap sesuai layout admin) -->
            <div class="p-6 space-y-4  bg-gray-50 dark:!bg-gray-800" wire:key="user-content-{{ auth()->id() }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                        <input 
                        type="text" 
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('name') ? 'border-red-300 dark:border-red-600' : '' }}" 
                        wire:model.defer="name"
                        placeholder="Masukkan nama lengkap"
                        >
                        @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input 
                        type="email" 
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('email') ? 'border-red-300 dark:border-red-600' : '' }}" 
                        wire:model.defer="email"
                        placeholder="Masukkan alamat email"
                        >
                        @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <!-- Perbaikan pada bagian tombol Cari NIK -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIK</label>
                        <div class="flex" wire:key="nik-search-group">
                        <input
                            type="text"
                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-l-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('nik') ? 'border-red-300 dark:border-red-600' : '' }}"
                            wire:model.defer="nik"
                            placeholder="Masukkan NIK (16 digit)"
                        >

                        <!-- Tombol Cari dengan perbaikan -->
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 dark:border-gray-600 rounded-r-md bg-gray-50 dark:!bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 disabled:opacity-50 transition-colors"
                            wire:click="cariDataAsn"
                            wire:loading.attr="disabled"
                            wire:target="cariDataAsn"
                        >
                            <span wire:loading.remove wire:target="cariDataAsn" class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Cari
                            </span>
                            <span wire:loading wire:target="cariDataAsn" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mencari...
                            </span>
                        </button>
                        </div>
                        @error('nik') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instansi</label>
                        <x-forms.select-tom 
                            id="skpd_uuid"
                            name="skpd_uuid"
                            placeholder="-- Pilih SKPD --"
                            wire:model.live="skpd_uuid"
                            :options="$availableSkpds->pluck('nama', 'id')->toArray()"
                            :disabled="auth()->user()->id === optional($editingUser)->id"
                        />
                        @error('skpd_uuid') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <x-forms.select-tom 
                            id="role"
                            name="role"
                            placeholder="-- Pilih Role --"
                            wire:model.live="role"
                            :options="array_combine($availableRoles, array_map('ucfirst', $availableRoles))"
                            :disabled="auth()->user()->id === optional($editingUser)->id"
                        />
                        @error('role') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <x-forms.file-input 
                            label="SK Penunjukan Admin (PDF)"
                            name="sk_penunjukan"
                            wire:model.defer="sk_penunjukan"
                            accept="application/pdf"
                            :existingFile="$current_sk_penunjukan ? basename($current_sk_penunjukan) : null"
                            :existingFileUrl="$editingUser?->sk_penunjukan_url"
                        />
                        @error('sk_penunjukan') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
            </div>

            <!-- footer -->
            <div class="flex items-center justify-end space-x-3 px-6 py-4 bg-gray-50 dark:!bg-gray-800 border-t border-gray-200 dark:border-gray-600 rounded-b-lg">
                <button type="button" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:!bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 transition-colors"
                    @click="close()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Batal
                </button>
                <button type="submit" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 disabled:opacity-50 transition-colors"
                    wire:loading.attr="disabled"
                    wire:target="saveUser">
                    <span wire:loading.remove wire:target="saveUser" class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ $user_id ? 'Update' : 'Simpan' }}
                    </span>
                    <span wire:loading wire:target="saveUser" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $user_id ? 'Updating...' : 'Saving...' }}
                    </span>
                </button>
            </div>
            </form>
        </div>
        </div>
    </div>
    </template>
</div>

<script>
    function userProfileModal() {
        return {
            show: false,
            init() {
                // If Livewire opens the modal (server-driven), reflect it locally quickly
                this.$watch('$wire.showModal', (val) => {
                    // sync server -> client
                    this.show = !!val;
                });

                // Listen for the global profile:open event (dispatched from header/nav)
                window.addEventListener('profile:open', () => {
                    // open locally immediately for instant UX
                    this.show = true;
                    // ask Livewire to load data and set server state
                    // prefer calling the server-side listener method directly if available
                    if (window.Livewire) {
                        // find the component by name and call the method via emitTo
                        // we will emit an event the component listens to
                        window.Livewire.emit('profile:open');
                    }
                });
            },
            close() {
                // hide locally immediately
                this.show = false;
                // inform Livewire to update server state
                if (window.Livewire) {
                    window.Livewire.emitTo('admin.userprofile', 'closeModal');
                }
            }
        }
    }
</script>
