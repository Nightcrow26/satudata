{{-- Modal Survey Sebelum Download --}}
<div>
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-data="{ open: @entangle('isOpen') }"
         x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="$wire.closeModal()">
        
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Survey Kepuasan Pengguna
                    </h3>
                    <button wire:click="closeModal" 
                            class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-6 py-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-teal-100 dark:bg-teal-900/30 rounded-full flex items-center justify-center">
                        <i class="bi bi-clipboard-check text-teal-600 dark:text-teal-400 text-3xl"></i>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Sebelum mengunduh data, mohon bantu kami dengan memberikan penilaian terhadap website ini.
                    </p>
                </div>

                <form wire:submit.prevent="submitSurvey" class="space-y-6">
                    {{-- Rating Bintang --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Berikan rating untuk website ini <span class="text-red-500">*</span>
                        </label>
                        <div class="flex justify-center space-x-1">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    wire:click="setRating({{ $i }})"
                                    class="text-3xl transition-colors duration-200 hover:scale-110 transform
                                           {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600 hover:text-yellow-300' }}">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                            @endfor
                        </div>
                        @if($rating > 0)
                        <div class="text-center mt-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $rating }} dari 5 bintang
                            </span>
                        </div>
                        @endif
                        @error('rating')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Feedback --}}
                    <div>
                        <label for="feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Masukan dan saran perbaikan (opsional)
                        </label>
                        <textarea wire:model="feedback" 
                                  id="feedback"
                                  rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                         bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                         focus:ring-2 focus:ring-teal-500 focus:border-teal-500
                                         placeholder-gray-500 dark:placeholder-gray-400
                                         transition-colors duration-200"
                                  placeholder="Berikan masukan Anda untuk membantu kami meningkatkan kualitas website..."></textarea>
                        @error('feedback')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ strlen($feedback) }}/1000 karakter
                        </div>
                    </div>

                    {{-- Error Message --}}
                    @error('submit')
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    </div>
                    @enderror

                    {{-- Buttons --}}
                    <div class="flex space-x-3 pt-4">
                        <button type="button" 
                                wire:click="closeModal"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300
                                       bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600
                                       rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600
                                       focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2
                                       transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="submitSurvey"
                                class="flex-1 px-4 py-2 text-sm font-medium text-white
                                       bg-teal-600 hover:bg-teal-700 disabled:bg-teal-400
                                       rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2
                                       transition-colors duration-200 flex items-center justify-center">
                            <span wire:loading.remove wire:target="submitSurvey">
                                Kirim & Download
                            </span>
                            <span wire:loading wire:target="submitSurvey" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
