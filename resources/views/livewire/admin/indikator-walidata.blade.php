<div>
    <div class="bg-white dark:!bg-gray-800 shadow-sm rounded-lg mb-4">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
                {{-- Title + Per-Page --}}
                <div class="flex items-center flex-wrap gap-4">
                <h3 class="mt-3 text-lg font-medium text-gray-900 dark:text-white">Indikator Walidata</h3>
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
                            wire:click="sinkronWalidata"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 disabled:opacity-50 transition-colors"
                            aria-label="Sinkron Data">
                    <span wire:loading.remove wire:target="sinkronWalidata" class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Sinkron Data
                    </span>
                    <span wire:loading wire:target="sinkronWalidata" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses…
                    </span>
                    </button>
                @endif

                {{-- Tambah Button --}}
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
                        placeholder="Cari satuan, tahun, atau data…">
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-12">#</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Satuan</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tahun</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Instansi</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aspek</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Indikator</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bidang</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:!bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($walidatas as $idx => $wd)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $walidatas->firstItem() + $idx }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $wd->satuan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">{{ $wd->tahun }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($wd->data, 60) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($wd->skpd?->singkatan ?? $wd->skpd?->nama ?? '-', 30) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-white"
                                      style="background-color: {{ $wd->aspek->warna ?? '#198754' }}">
                                    {{ $wd->aspek?->nama ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($wd->indikator?->uraian_indikator ?? $wd->indikator?->nama ?? '—' , 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ STr::limit($wd->bidang?->uraian_bidang ?? $wd->bidang?->nama ?? '—' , 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex rounded-md shadow-sm" role="group" aria-label="Aksi">
                                    <a href="{{ route('admin.walidata.show', $wd->id) }}" wire:navigate
                                        class="px-3 py-1 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:!bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-10 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:text-gray-600 dark:focus:text-gray-400 transition-colors" data-bs-toggle="tooltip" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" 
                                            class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border-t border-b border-green-300 dark:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 focus:z-10 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:text-green-600 dark:focus:text-green-400 transition-colors"
                                            wire:click="showEditModal('{{ $wd->id }}')" data-bs-toggle="tooltip" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 rounded-r-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:z-10 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:text-red-600 dark:focus:text-red-400 transition-colors"
                                            wire:click="confirmDelete('{{ $wd->id }}')" data-bs-toggle="tooltip" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach

                        @if($walidatas->isEmpty())
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data walidata yang cocok.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <x-admin.pagination :items="$walidatas" />
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <template x-teleport="body">
        <div wire:ignore.self wire:key="walidata-modal"
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
                    class="bg-white dark:!bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-600 w-full max-w-4xl opacity-100">
                <form wire:submit.prevent="saveWalidata">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                {{ $walidata_id ? 'Edit Walidata' : 'Tambah Walidata' }}
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

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Satuan</label>
                                <input type="text"
                                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('satuan') ? 'border-red-300 dark:border-red-600' : '' }}"
                                       wire:model.defer="satuan">
                                @error('satuan') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                                <input type="number"
                                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('tahun') ? 'border-red-300 dark:border-red-600' : '' }}"
                                       wire:model.defer="tahun"
                                       min="1900" max="2100" step="1">
                                @error('tahun') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Data</label>
                                <input type="text"
                                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('data') ? 'border-red-300 dark:border-red-600' : '' }}"
                                       wire:model.defer="data"
                                       placeholder="Nilai / keterangan">
                                @error('data') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-forms.select-tom 
                                    id="skpd-select"
                                    label="Instansi (Produsen Data)"
                                    placeholder="Ketik untuk mencari Produsen Data..."
                                    model="skpd_id"
                                    :options="$availableSkpds->map(function($skpd) {
                                        return [
                                            'id' => $skpd->id,
                                            'text' => $skpd->nama
                                        ];
                                    })->toArray()"
                                    live="true"
                                />
                                @error('skpd_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-forms.select-tom 
                                    id="aspek-select"
                                    label="Aspek"
                                    placeholder="Ketik untuk mencari aspek..."
                                    model="aspek_id"
                                    :options="$availableAspeks->map(function($aspek) {
                                        return [
                                            'id' => $aspek->id,
                                            'text' => $aspek->nama
                                        ];
                                    })->toArray()"
                                    live="true"
                                />
                                @error('aspek_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-forms.select-tom 
                                    id="indikator-select"
                                    label="Indikator"
                                    placeholder="Ketik untuk mencari indikator..."
                                    model="indikator_id"
                                    :options="$availableIndikators->map(function($indikator) {
                                        return [
                                            'id' => $indikator->id,
                                            'text' => trim(($indikator->kode_indikator ?? '').' '.($indikator->uraian_indikator ?? ''))
                                        ];
                                    })->toArray()"
                                    live="true"
                                />
                            </div>

                            <div>
                                <x-forms.select-tom 
                                    id="bidang-select"
                                    label="Bidang"
                                    placeholder="Ketik untuk mencari bidang..."
                                    model="bidang_id"
                                    :options="$availableBidangs->map(function($bidang) {
                                        return [
                                            'id' => $bidang->id,
                                            'text' => trim(($bidang->kode_bidang ? $bidang->kode_bidang.' - ' : '').($bidang->uraian_bidang ?? ''))
                                        ];
                                    })->toArray()"
                                    live="true"
                                />
                                @error('bidang_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                                {{ $walidata_id ? 'Update' : 'Simpan' }}
                        </button>
                        <button type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                                wire:click="closeModal"
                                @click="show = false">
                                Batal
                        </button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal Hapus --}}
    <template x-teleport="body">
        <div wire:ignore.self wire:key="walidata-delete-modal"
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
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg leading-6 font-medium text-red-600 dark:text-red-400">Konfirmasi Hapus</h3>
                            <button type="button"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                                    wire:click="cancelDelete"
                                    @click="show = false">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Yakin ingin menghapus entri walidata <strong class="text-gray-900 dark:text-gray-100">{{ $nama }}</strong>?</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-800 dark:focus:ring-red-400 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                                wire:click="deleteWalidataConfirmed"
                                @click="show = false">
                            Hapus
                        </button>
                        <button type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                                wire:click="cancelDelete"
                                @click="show = false">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>


