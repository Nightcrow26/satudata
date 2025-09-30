@if(flash()->message)
<div class="mb-4">
    <div class="flex items-center p-4 rounded-lg border 
        @if(flash()->class == 'danger') bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 @endif
        @if(flash()->class == 'warning') bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-700 dark:text-yellow-300 @endif
        @if(flash()->class == 'success') bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 @endif
        @if(flash()->class == 'info') bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 @endif
        {{ !in_array(flash()->class, ['danger', 'warning', 'success', 'info']) ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300' : '' }}" 
        role="alert">
       
        @if(flash()->class == 'danger' || flash()->class == 'warning')
            <i class="bi bi-exclamation-triangle-fill mr-2 flex-shrink-0"></i>
            <span>{{ flash()->message }}</span>
        @endif

        @if(flash()->class == 'success' || !in_array(flash()->class, ['danger', 'warning', 'info']))
            <i class="bi bi-check-circle-fill mr-2 flex-shrink-0"></i>
            <span>{{ flash()->message }}</span>
        @endif

        @if(flash()->class == 'info')
            <i class="bi bi-info-circle-fill mr-2 flex-shrink-0"></i>
            <span>{{ flash()->message }}</span>
        @endif
    </div>
</div>
@endif
