@props(['modalId' => 'chatbotModal'])

<div x-data="{ 
        open: false,
        openModal() {
            this.open = true;
            // Prevent body scroll when modal opens
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.open = false;
            // Restore body scroll when modal closes
            document.body.style.overflow = '';
            // Trigger event ketika modal ditutup
            window.dispatchEvent(new CustomEvent('chat-modal-closed'));
        }
    }">
  <!-- FAB -->
  <button type="button"
          @click="openModal()"
          class="fixed bottom-4 right-4 min-w-[120px] w-auto h-14 px-4 bg-green-600 hover:bg-green-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center z-50 focus:outline-none focus:ring-4 focus:ring-green-500 focus:ring-opacity-50 text-sm font-medium whitespace-nowrap"
          aria-label="Buka Asisten Data">
    <span>Chat Ai</span>
    <i class="bi bi-chat-dots text-lg ml-2 flex-shrink-0"></i>
    <span class="absolute inset-0 rounded-full border-2 border-green-400 animate-ping opacity-35 pointer-events-none"></span>
  </button>

  <!-- Modal Chat -->
  <template x-teleport="body">
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;"
         @keydown.escape="closeModal()">
      
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/50" @click="closeModal()"></div>
      
      <!-- Modal Container -->
      <div class="flex items-center justify-center min-h-screen p-4 relative">
        <!-- Modal Panel -->
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white dark:!bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-4xl max-h-[80vh] flex flex-col relative z-10"
             @click.stop>
          
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:!bg-gray-900 rounded-t-lg">
            <h6 class="text-lg font-semibold text-gray-900 dark:text-white">Asisten Data • SDI HSU</h6>
            <button type="button" 
                    @click="closeModal()"
                    class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200" 
                    aria-label="Tutup">
              <i class="bi bi-x-lg text-lg"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex-1 p-4 overflow-auto bg-white dark:!bg-gray-900 rounded-b-lg" style="min-height:50vh; max-height:70vh;">
            <livewire:admin.chat-sdi />
          </div>
        </div>
      </div>
    </div>
  </template>
</div>
