<section class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex justify-center">
            <form wire:submit.prevent="go" class="relative w-full max-w-2xl">
                <!-- Icon -->
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5 text-gray-400 dark:text-gray-500 transition-colors duration-200"
                        viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path
                            d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.71.71l.27.28v.79l5 5 1.5-1.5-5-5ZM10 15a5 5 0 1 1 0-10 5 5 0 0 1 0 10Z" />
                    </svg>
                </span>

                <!-- Input -->
                <input type="text" name="q" wire:model.live.debounce.300ms="q"
                    placeholder="Cari data yang anda butuhkan" autocomplete="off" class="w-full rounded-full border border-gray-200 dark:border-gray-700
                           bg-white dark:!bg-gray-800
                           pl-11 pr-14 py-3 shadow-sm
                           text-gray-800 dark:text-gray-100
                           placeholder-gray-400 dark:placeholder-gray-500
                           focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-teal-600
                           transition-colors duration-200" aria-label="Pencarian data" />

                <!-- Tombol clear -->
                <button type="button" wire:click="$set('q','')" x-data
                    @class([ 'absolute inset-y-0 right-3 my-auto h-8 w-8 grid place-items-center rounded-full transition-colors duration-200'
                    , 'text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300'=>
                    true,
                    'hidden' => $q === '',
                    ])
                    aria-label="Bersihkan pencarian">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3 1.4 1.4z" />
                    </svg>
                </button>

                <!-- Loader kecil -->
                <div class="absolute -bottom-6 left-1 text-xs text-gray-500 dark:text-gray-400 transition-colors duration-200"
                    wire:loading.delay.shortest>
                    Memuat…
                </div>

                <!-- Area hasil (placeholder; nanti bisa diisi suggestion/result) -->
                <div class="sr-only" aria-live="polite">
                    {{ $q === '' ? 'Kolom pencarian kosong' : 'Pencarian diperbarui' }}
                </div>
            </form>
        </div>
    </div>
</section>
