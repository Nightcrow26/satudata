<div>
  <div class="bg-white dark:!bg-gray-800 shadow-sm rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex flex-col lg:flex-row justify-between items-stretch lg:items-center gap-4">
        {{-- Title + Per-Page --}}
        <div class="flex items-center flex-wrap gap-2">
          <h3 class="text-lg mt-3 font-medium text-gray-900 dark:text-white">User Survey</h3>
          <select wire:model.live="perPage"
                  class="text-sm border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>

        {{-- Search --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
          <div class="relative">
            <input type="text"
                wire:model.live.debounce.300ms="search"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 dark:placeholder-gray-500 focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400"
                placeholder="Cari session, IP, rating, feedback, user agent…">
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
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Session</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">IP Address</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Feedback</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User Agent</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
              <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-24">Aksi</th>
            </tr>
          </thead>

          <tbody class="bg-white dark:!bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($surveys as $idx => $s)
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ $surveys->firstItem() + $idx }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  <span title="{{ $s->session_id }}">{{ \Illuminate\Support\Str::limit($s->session_id, 16) }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ $s->ip_address }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-gray-100">
                  {{ $s->rating }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                  <span title="{{ $s->feedback }}">{{ \Illuminate\Support\Str::limit((string) $s->feedback, 80) ?: '-' }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                  <span title="{{ $s->user_agent }}">{{ \Illuminate\Support\Str::limit((string) $s->user_agent, 60) ?: '-' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                  {{ optional($s->created_at)->format('d M Y H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                  <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button type="button"
                            class="px-3 py-1 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:z-10 focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 transition-colors"
                            wire:click="confirmDelete('{{ $s->id }}')"
                            data-bs-toggle="tooltip" title="Hapus">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
        <x-admin.pagination :items="$surveys" />
      </div>
    </div>
  </div>

  {{-- Modal Konfirmasi Hapus --}}
  <template x-teleport="body">
    <div wire:ignore.self wire:key="survey-delete-modal"
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
        <div class="fixed inset-0 bg-black/50 z-40" @click="$wire.cancelDelete()"></div>

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
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                      Konfirmasi Hapus
                    </h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-500 dark:text-gray-300">
                        Yakin ingin menghapus entri survey berikut?
                      </p>
                      <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                        <strong>{{ $preview }}</strong>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-gray-50 dark:!bg-gray-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                <button type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        wire:click="deleteConfirmed"
                        @click="show = false">
                  Hapus
                </button>
                <button type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:!bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
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
