<div>
    <div class="bg-white dark:!bg-gray-800 shadow-sm rounded-lg mb-4">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
                {{-- Title + Per-Page --}}
                <div class="flex items-center flex-wrap gap-2">
                    <h3 class="text-lg mt-3 font-medium text-gray-900 dark:text-white">Dataset</h3>
                    <select wire:model.live="perPage"
                            class="text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                {{-- Actions + Search --}}
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    {{-- Tambah - TANPA IKON --}}
                    <button type="button"
                            wire:click="showCreateModal"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 dark:focus:ring-green-400 transition-colors"
                            aria-label="Tambah">
                        Tambah
                    </button>

                    {{-- Search --}}
                    <div class="relative">
                        <input type="text"
                            wire:model.live.debounce.300ms="search"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400"
                            placeholder="Cari satuan, tahun, atau data…">
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Instansi</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aspek</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Views</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">File</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:!bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($datasets as $idx => $ds)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">{{ $datasets->firstItem() + $idx }}</td>

                            {{-- Nama dataset dengan truncation + tooltip --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="truncate text-sm text-gray-900 dark:text-gray-100" title="{{ $ds->nama }}">
                                {{ $ds->nama }}
                                </span>
                            </td>

                            {{-- Instansi (singkatan -> nama) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                $labelInstansi = $ds->skpd?->singkatan ?: ($ds->skpd?->nama ?? '-');
                                @endphp
                                <span class="truncate text-sm text-gray-900 dark:text-gray-100" title="{{ $labelInstansi }}">
                                {{ Str::limit($labelInstansi, 30) }}
                                </span>
                            </td>

                            {{-- Aspek badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-white"
                                    style="background-color: {{ $ds->aspek->warna ?? '#6c757d' }}">
                                {{ $ds->aspek?->nama ?? 'Undefined' }}
                                </span>
                            </td>

                            {{-- Status badge kecil --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-white animate-pulse
                                    {{ match($ds->status) {
                                    'published' => 'bg-green-500',
                                    'pending'   => 'bg-yellow-500',
                                    'revisi'    => 'bg-blue-500',
                                    'draft'     => 'bg-gray-500',
                                    } }}">
                                    {{ ucfirst($ds->status) }}
                                </span>
                            </td>

                            {{-- Views kanan --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-gray-100">{{ number_format($ds->view ?? 0, 0, ',', '.') }}</td>

                            {{-- Gabungan berkas --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($ds->excel || $ds->metadata || $ds->bukti_dukung)
                                    <div class="inline-flex rounded-md shadow-sm" role="group" aria-label="Berkas">
                                        @if($ds->excel)
                                            <a class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border border-green-300 dark:border-green-600 {{ !$ds->metadata && !$ds->bukti_dukung ? 'rounded-md' : 'rounded-l-md' }} hover:bg-green-50 dark:hover:bg-green-900/20 focus:z-10 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:text-green-600 dark:focus:text-green-400 transition-colors"
                                            href="{{ Storage::disk('s3')->temporaryUrl($ds->excel, now()->addMinutes(15)) }}"
                                            target="_blank" title="Unduh Excel">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($ds->metadata)
                                            <a class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 {{ $ds->excel ? 'border-t border-b border-green-300 dark:border-green-600' : 'border border-green-300 dark:border-green-600' }} {{ !$ds->bukti_dukung && !$ds->excel ? 'rounded-md' : (!$ds->bukti_dukung ? 'rounded-r-md' : '') }} hover:bg-green-50 dark:hover:bg-green-900/20 focus:z-10 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:text-green-600 dark:focus:text-green-400 transition-colors"
                                            href="{{ Storage::disk('s3')->temporaryUrl($ds->metadata, now()->addMinutes(15)) }}"
                                            target="_blank" title="Unduh Metadata">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                </svg>
                                            </a>
                                        @endif
                                        @if($ds->bukti_dukung)
                                            <a class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 {{ !$ds->excel && !$ds->metadata ? 'rounded-md' : 'rounded-r-md' }} hover:bg-red-50 dark:hover:bg-red-900/20 focus:z-10 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:text-red-600 dark:focus:text-red-400 transition-colors"
                                            href="{{ Storage::disk('s3')->temporaryUrl($ds->bukti_dukung, now()->addMinutes(15)) }}"
                                            target="_blank" title="Bukti Dukung (PDF)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">-</span>
                                @endif
                            </td>

                            {{-- Aksi ringkas --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="inline-flex rounded-md shadow-sm" role="group" aria-label="Aksi">
                                    <a href="{{ route('admin.dataset.show', $ds->id) }}" wire:navigate
                                        class="px-3 py-1 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:!bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-l-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:z-10 focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:text-gray-600 dark:focus:text-gray-400 transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <button type="button" 
                                            class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border-t border-b border-green-300 dark:border-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 focus:z-10 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:text-green-600 dark:focus:text-green-400 transition-colors"
                                            wire:click="showEditModal('{{ $ds->id }}')" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 rounded-r-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:z-10 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:text-red-600 dark:focus:text-red-400 transition-colors"
                                            wire:click="confirmDelete('{{ $ds->id }}')" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data dataset yang cocok.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                <x-admin.pagination :items="$datasets" />
            </div>
        </div>
    </div>

    {{-- Modal Tambah/Edit --}}
    <template x-teleport="body">
        <div wire:ignore.self wire:key="dataset-modal"
            x-data="{ show: $wire.entangle('showModal').live }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
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
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="bg-white dark:!bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-600 w-full max-w-5xl opacity-100">
                            
                            <form wire:submit.prevent="saveDataset">
                                <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                            {{ $dataset_id ? 'Edit Dataset' : 'Tambah Dataset' }}
                                        </h3>
                                        <button type="button"
                                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                                                wire:click="closeModal"
                                                @click="show = false">
                                            <span class="sr-only">Close</span>
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                                            <input type="text"
                                                class="block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('nama') ? 'border-red-300 dark:border-red-600' : 'border-gray-300' }}"
                                                wire:model.defer="nama">
                                            @error('nama') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                       <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                            @php
                                                $statusOptions = ['draft' => 'Draft'];
                                                if (auth()->user()->hasRole('produsen data')) {
                                                    $statusOptions['revisi'] = 'Revisi';
                                                }
                                                if (auth()->user()->hasRole(['admin', 'verifikator'])) {
                                                    $statusOptions['pending'] = 'Pending';
                                                    $statusOptions['published'] = 'Published';
                                                }
                                                $statusDisabled = false; // Enable status field for all users
                                            @endphp
                                            <x-forms.select-tom 
                                                id="status"
                                                name="status"
                                                placeholder="-- Pilih Status --"
                                                wire:model.live="status"
                                                :options="$statusOptions"
                                                :disabled="$statusDisabled"
                                                live="true"
                                            />
                                            @error('status')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                            </div>

                                        <div>
                                            <x-forms.file-input 
                                                label="File Excel"
                                                name="excel"
                                                wire:model="excel"
                                                accept=".xls,.xlsx,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                                icon="table-cells"
                                                maxSize="5MB"
                                                :existingFile="$editingDataset?->excel ? basename($editingDataset->excel) : null"
                                                :existingFileUrl="$editingDataset?->excel ? Storage::disk('s3')->temporaryUrl($editingDataset->excel, now()->addMinutes(15)) : null"
                                            />
                                        </div>

                                        <div>
                                            <x-forms.file-input 
                                                label="Metadata (Excel)"
                                                name="metadata"
                                                wire:model="metadata"
                                                accept=".xls,.xlsx,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                                icon="table-cells"
                                                maxSize="5MB"
                                                :existingFile="$editingDataset?->metadata ? basename($editingDataset->metadata) : null"
                                                :existingFileUrl="$editingDataset?->metadata ? Storage::disk('s3')->temporaryUrl($editingDataset->metadata, now()->addMinutes(15)) : null"
                                            />
                                        </div>

                                        <div>
                                            <x-forms.file-input 
                                                label="Bukti Dukung (PDF)"
                                                name="bukti_dukung"
                                                wire:model="bukti_dukung"
                                                accept="application/pdf,.pdf"
                                                icon="document-text"
                                                maxSize="10MB"
                                                :existingFile="$editingDataset?->bukti_dukung ? basename($editingDataset->bukti_dukung) : null"
                                                :existingFileUrl="$editingDataset?->bukti_dukung ? Storage::disk('s3')->temporaryUrl($editingDataset->bukti_dukung, now()->addMinutes(15)) : null"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tahun</label>
                                            <input type="number"
                                                class="block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('tahun') ? 'border-red-300 dark:border-red-600' : 'border-gray-300' }}"
                                                wire:model.defer="tahun">
                                            @error('tahun') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instansi</label>
                                            <x-forms.select-tom 
                                                id="instansi_id"
                                                name="instansi_id"
                                                placeholder="-- Pilih Produsen Data --"
                                                wire:model.defer="instansi_id"
                                                :options="$availableSkpds->pluck('nama', 'id')->toArray()"
                                                live="true"
                                            />
                                            @error('instansi_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aspek</label>
                                            <x-forms.select-tom 
                                                id="aspek_id"
                                                name="aspek_id"
                                                placeholder="-- Pilih Aspek --"
                                                wire:model.defer="aspek_id"
                                                :options="$availableAspeks->pluck('nama', 'id')->toArray()"
                                                live="true"
                                            />
                                            @error('aspek_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div class="md:col-span-2" wire:ignore>
                                            <x-forms.trix-editor 
                                                label="Deskripsi"
                                                name="deskripsi"
                                                wire:model.defer="deskripsi"
                                                placeholder="Masukkan deskripsi dataset..."
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keyword</label>
                                            <input type="text"
                                                class="block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:border-gray-600 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('keyword') ? 'border-red-300 dark:border-red-600' : 'border-gray-300' }}"
                                                wire:model.defer="keyword">
                                            @error('keyword') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                        </div>

                                        <div>
                                                @php
                                                    $showCatatan = !auth()->user()->hasRole('produsen data') || (!empty($catatan_verif) && auth()->user()->hasRole('produsen data'));
                                                @endphp
                                                @if($showCatatan)
                                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catatan Verifikator</label>
                                                    <textarea wire:model.defer="catatan_verif"
                                                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('catatan_verif') ? 'border-red-300 dark:border-red-600' : '' }}" 
                                                                        {{ auth()->user()->hasRole('produsen data') ? 'disabled' : '' }}></textarea>
                                                    @error('catatan_verif')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                                @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                                        <button type="button"
                                            wire:click="saveDataset"
                                            wire:loading.attr="disabled"
                                            wire:target="saveDataset"
                                            aria-busy="true"
                                            class="w-full inline-flex items-center justify-center gap-2 rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:ml-3 sm:w-auto sm:text-sm">
                                        <span wire:loading wire:target="saveDataset" class="inline-flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span>{{ $dataset_id ? 'Menyimpan Perubahan…' : 'Menyimpan…' }}</span>
                                        </span>
                                        <span wire:loading.remove wire:target="saveDataset,excel,metadata,bukti_dukung">
                                            {{ $dataset_id ? 'Update' : 'Simpan' }}
                                        </span>
                                    </button>
                                    <button type="button"
                                            wire:loading.attr="disabled"
                                            wire:target="saveDataset"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
        <div wire:ignore.self wire:key="dataset-delete-modal"
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
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    Konfirmasi Hapus
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        Yakin ingin menghapus dataset <strong>{{ $nama }}</strong>?
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="button"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                wire:click="deleteDatasetConfirmed"
                                @click="show = false">
                            Hapus
                        </button>
                        <button type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
