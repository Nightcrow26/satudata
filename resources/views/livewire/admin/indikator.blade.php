<div>
  <div class="bg-white dark:!bg-gray-800 shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
        {{-- Title + Per-Page --}}
        <div class="flex items-center flex-wrap gap-2">
          <h3 class="text-lg mt-3 font-medium text-gray-900 dark:text-white">Indikator Bidang</h3>
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
          {{-- Tambah Button --}}
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
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kode Indikator</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uraian</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Bidang</th>
              <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:!bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($indikators as $idx => $indikator)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $indikators->firstItem() + $idx }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $indikator->kode_indikator }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $indikator->uraian_indikator }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $indikator->bidang->uraian_bidang ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <button type="button" 
                                class="px-3 py-1 text-sm font-medium text-green-600 dark:text-green-400 bg-white dark:!bg-gray-800 border border-green-300 dark:border-green-600 rounded-l-md hover:bg-green-50 dark:hover:bg-green-900/20 focus:z-10 focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:text-green-600 dark:focus:text-green-400 transition-colors"
                                wire:click="showEditModal('{{ $indikator->id }}')" data-bs-toggle="tooltip" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button type="button" 
                                class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-l-0 border-red-300 dark:border-red-600 rounded-r-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:z-10 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:text-red-600 dark:focus:text-red-400 transition-colors"
                                wire:click="confirmDelete('{{ $indikator->id }}')" data-bs-toggle="tooltip" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-6">
        <x-admin.pagination :items="$indikators" />
      </div>
    </div>
  </div>

  {{-- Modal Tambah/Edit --}}
  <template x-teleport="body">
  <div wire:ignore.self wire:key="indikator-modal"
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
        <form wire:submit.prevent="saveIndikator">
          <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                {{ $indikator_id ? 'Edit Indikator' : 'Tambah Indikator' }}
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
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Indikator</label>
                <input type="text" 
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('kode_indikator') ? 'border-red-300 dark:border-red-600' : '' }}" 
                       wire:model.defer="kode_indikator" 
                       placeholder="Masukkan kode indikator">
                @error('kode_indikator') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Uraian Indikator</label>
                <input type="text" 
                       class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400 {{ $errors->has('uraian_indikator') ? 'border-red-300 dark:border-red-600' : '' }}" 
                       wire:model.defer="uraian_indikator" 
                       placeholder="Masukkan uraian indikator">
                @error('uraian_indikator') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bidang</label>
                @php
                    $bidangOptions = [];
                    foreach($bidangs as $bidang) {
                        $bidangOptions[$bidang->id] = trim(($bidang->kode_bidang ? $bidang->kode_bidang.' - ' : '').($bidang->uraian_bidang ?? ''));
                    }
                @endphp
                <x-forms.select-tom 
                    id="bidang_id"
                    name="bidang_id"
                    placeholder="-- Pilih Bidang --"
                    wire:model.defer="bidang_id"
                    :options="$bidangs->pluck('uraian_bidang', 'id')->mapWithKeys(function($uraian, $id) use ($bidangs) { $bidang = $bidangs->find($id); return [$id => trim(($bidang->kode_bidang ? $bidang->kode_bidang.' - ' : '').$uraian)]; })->toArray()"
                />
                @error('bidang_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
              </div>
            </div>
          </div>
          <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button type="submit"
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
              {{ $indikator_id ? 'Update' : 'Simpan' }}
            </button>
            <button type="button"
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 dark:focus:ring-indigo-400 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                    wire:click="closeModal">
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
  <div wire:ignore.self wire:key="indikator-delete-modal"
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
                              Yakin ingin menghapus indikator <strong>{{ $nama }}</strong>?
                          </p>
                      </div>
                  </div>
              </div>
          </div>
          <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
              <button type="button"
                      class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                      wire:click="deleteIndikatorConfirmed"
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
