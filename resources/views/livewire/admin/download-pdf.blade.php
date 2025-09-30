<div>
    {{-- Tombol Download PDF --}}
    <a 
        href="{{ route('public.data.pdf.download', $dataset) }}" 
        class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-white dark:!bg-gray-800 border border-red-300 dark:border-red-600 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
        title="Download PDF"
    >
        <i class="bi bi-filetype-pdf"></i>
    </a>

    {{-- Error message display --}}
    @if (session()->has('error'))
        <div class="flex items-center p-4 mt-2 text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg" role="alert">
            <i class="bi bi-exclamation-triangle-fill mr-2 flex-shrink-0"></i>
            <span class="flex-1">{{ session('error') }}</span>
            <button type="button" 
                    class="ml-2 -mx-1.5 -my-1.5 bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-300 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 dark:hover:bg-red-900/40 inline-flex h-8 w-8 items-center justify-center"
                    onclick="this.parentElement.style.display='none'">
                <i class="bi bi-x text-lg"></i>
            </button>
        </div>
    @endif
</div>
