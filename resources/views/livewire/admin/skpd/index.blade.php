<div>
    <div class="bg-white dark:!bg-gray-800 shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
                {{-- Title + Per-Page --}}
                <div class="flex items-center flex-wrap gap-4">
                    <h3 class="mt-3 text-lg font-medium text-gray-900 dark:text-white">SKPD</h3>
                    <select wire:model.live="perPage"
                            class="block border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white text-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                {{-- Actions + Search --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                    {{-- Sinkron Data (admin only) --}}
                    @if (auth()->user()->hasRole('admin'))
                        <button type="button"
                                wire:click="sinkronUnorFromSikon"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition-colors"
                                aria-label="Sinkron Data">
                            <span wire:loading.remove wire:target="sinkronUnorFromSikon" class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Sinkron Data
                            </span>
                            <span wire:loading wire:target="sinkronUnorFromSikon" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses…
                            </span>
                        </button>
                    @endif

                    {{-- Tambah --}}
                    <button type="button"
                            wire:click="showCreateModal"
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-colors"
                            aria-label="Tambah">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah
                    </button>

                    {{-- Search --}}
                    <div class="relative">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white sm:text-sm"
                               placeholder="Cari SKPD…">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:!bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-16">
                                #
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Nama
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Singkatan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Alamat
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Telepon
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-36">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:!bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        {{-- Looping SKPD --}}
                        @forelse($skpds as $idx => $skpd)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $skpds->firstItem() + $idx }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div class="max-w-xs truncate">
                                        {{ Str::limit($skpd->nama, 50) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $skpd->singkatan }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div class="max-w-xs truncate">
                                        {{ Str::limit($skpd->alamat, 30) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">
                                    {{ $skpd->telepon }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="inline-flex rounded-md shadow-sm" role="group">
                                        <button type="button" 
                                                class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border border-green-300 dark:border-green-600 rounded-l-md hover:bg-green-50 dark:hover:bg-green-900/20 focus:z-10 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:text-green-600 dark:focus:text-green-400 transition-colors"
                                                wire:click="showEditModal('{{ $skpd->id }}')" data-bs-toggle="tooltip" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        @if (auth()->user()->hasRole('admin'))
                                        <button type="button" 
                                                class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-l-0 border-red-300 dark:border-red-600 rounded-r-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:z-10 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:text-red-600 dark:focus:text-red-400 transition-colors"
                                                wire:click="confirmDelete('{{ $skpd->id }}')" data-bs-toggle="tooltip" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data SKPD</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan SKPD baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <x-admin.pagination :items="$skpds" />
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit SKPD --}}
    <template x-teleport="body">
        <div wire:ignore.self wire:key="skpd-modal"
            x-data="{ show: $wire.entangle('showModal').live }"
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
            <div class="fixed inset-0 bg-black/50 z-40" @click="$wire.closeModal()"></div>

            <!-- CONTAINER + PANEL -->
            <div class="flex items-center justify-center min-h-screen p-4 relative z-50">
                <div x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="bg-white dark:!bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-600 w-full max-w-lg opacity-100">
                <form wire:submit.prevent="saveSkpd">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                {{ $skpd_id ? 'Edit SKPD' : 'Tambah SKPD' }}
                            </h3>
                            <button type="button"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                                    wire:click="closeModal">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4" wire:key="skpd-content-{{ $skpd_id ?: 'new' }}">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                                <input 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('nama') ? 'border-red-300 dark:border-red-600' : '' }}" 
                                    wire:model.defer="nama"
                                >
                                @error('nama') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Singkatan</label>
                                <input 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('singkatan') ? 'border-red-300 dark:border-red-600' : '' }}" 
                                    wire:model.defer="singkatan"
                                >
                                @error('singkatan') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat</label>
                                <textarea 
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('alamat') ? 'border-red-300 dark:border-red-600' : '' }}" 
                                    wire:model.defer="alamat"
                                    rows="3"
                                ></textarea>
                                @error('alamat') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon</label>
                                <input 
                                    type="text" 
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('telepon') ? 'border-red-300 dark:border-red-600' : '' }}" 
                                    wire:model.defer="telepon"
                                >
                                @error('telepon') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-forms.file-input 
                                    label="Foto SKPD"
                                    name="foto"
                                    wire:model="foto"
                                    accept="image/*"
                                    icon="photo"
                                    maxSize="2MB"
                                    :existingFile="$editingSkpd?->foto ? basename($editingSkpd->foto) : null"
                                    :existingFileUrl="$editingSkpd?->foto ? resolve_media_url($editingSkpd->foto) : null"
                                />
                            </div>
                            
                            {{-- Preview foto baru / lama --}}
                            @if($foto)
                                <div class="mt-2">
                                    <img src="{{ $foto->temporaryUrl() }}" class="w-20 h-20 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
                                </div>
                            @elseif($editingSkpd?->foto)
                                @php
                                    $fotoKey = $editingSkpd->foto;          // nilai kolom foto di DB
                                @endphp

                                <div class="mt-2">
                                @php
                                    // Cek apakah URL penuh
                                    $isUrl = filter_var($fotoKey, FILTER_VALIDATE_URL);
                                @endphp

                                @if($fotoKey && !$isUrl && Storage::disk('s3')->exists($fotoKey))
                                    {{-- File ada di S3 --}}
                                    <img
                                        src="{{ resolve_media_url($fotoKey) }}"
                                        alt="Logo SKPD"
                                        class="w-20 h-20 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-600"
                                    >

                                @elseif($fotoKey && $isUrl)
                                    {{-- Jika sudah URL penuh --}}
                                    <img
                                        src="{{ $fotoKey }}"
                                        alt="Logo SKPD"
                                        class="w-20 h-20 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-600"
                                    >

                                @elseif($fotoKey && file_exists(public_path($fotoKey)))
                                    {{-- File lokal di public --}}
                                    <img
                                        src="{{ asset($fotoKey) }}"
                                        alt="Logo SKPD"
                                        class="w-20 h-20 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-600"
                                    >

                                @else
                                    {{-- Default --}}
                                    <img
                                        src="{{ asset('images/default-logo.png') }}"
                                        alt="Default Logo"
                                        class="w-20 h-20 object-cover rounded-lg shadow-sm border border-gray-200 dark:border-gray-600 opacity-50"
                                    >
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Modal footer -->
                    <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                        >
                            {{ $skpd_id ? 'Update' : 'Simpan' }}
                        </button>
                        <button 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                            wire:click="closeModal"
                            @click="show = false"
                        >
                            Batal
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </template>


    {{-- Modal Konfirmasi Hapus --}}
    <template x-teleport="body">
        <div wire:ignore.self wire:key="skpd-delete-modal"
            x-data="{ show: $wire.entangle('showDeleteModal').live }"
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
            <div class="fixed inset-0 bg-black/50 z-40" @click="$wire.closeModal()"></div>

            <!-- CONTAINER + PANEL -->
            <div class="flex items-center justify-center min-h-screen p-4 relative z-50">
                <div x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="bg-white dark:!bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-600 w-full max-w-md opacity-100">
                    
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-red-600 dark:text-red-400">
                                    Konfirmasi Hapus
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin menghapus SKPD ini
                                        <strong> {{ $nama }}</strong>?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                            wire:click="deleteSkpdConfirmed"
                            @click="show = false"
                        >
                            Hapus
                        </button>
                        <button 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 sm:mt-0 sm:mr-3 sm:w-auto sm:text-sm transition-colors"
                            wire:click="cancelDelete"
                            @click="show = false"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>
