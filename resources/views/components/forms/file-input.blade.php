{{-- resources/views/components/forms/file-input.blade.php --}}
@props([
    'label' => null,
    'name' => null,
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'placeholder' => 'No file chosen',
    'icon' => null,
    'maxSize' => null,
    'existingFile' => null,
    'existingFileUrl' => null,
    'disabled' => false,
])

@php
    $inputId = $name ?? 'file-input-' . Str::uuid();
    $multiple = $multiple === true || $multiple === 'true';
    $required = $required === true || $required === 'true';
    $disabled = $disabled === true || $disabled === 'true';
    
    // Default icons based on accept type
    $defaultIcon = 'document';
    if ($accept) {
        if (str_contains($accept, 'image')) $defaultIcon = 'photo';
        elseif (str_contains($accept, 'pdf')) $defaultIcon = 'document-text';
        elseif (str_contains($accept, 'excel') || str_contains($accept, 'spreadsheet')) $defaultIcon = 'table-cells';
    }
    $icon = $icon ?? $defaultIcon;
@endphp

<div class="w-full" x-data="{ 
    fileName: '{{ $placeholder }}', 
    isDragOver: false,
    isSelected: false,
    isUploading: false,
    progress: 0,
    disabled: {{ $disabled ? 'true' : 'false' }}
}"
    x-on:livewire-upload-start="isUploading = true; progress = 0"
    x-on:livewire-upload-finish="progress = 100; setTimeout(() => { isUploading = false }, 400)"
    x-on:livewire-upload-error="isUploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <!-- Hidden file input -->
        <input 
            type="file" 
            id="{{ $inputId }}" 
            name="{{ $name }}"
            class="sr-only"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->except(['class', 'label', 'name', 'accept', 'multiple', 'required', 'placeholder', 'icon', 'maxSize', 'existingFile', 'existingFileUrl', 'disabled']) }}
            x-ref="fileInput"
            x-on:change="
                const files = $refs.fileInput.files;
                if (files.length > 0) {
                    fileName = Array.from(files).map(file => file.name).join(', ');
                    isSelected = true;
                    
                    // Trigger Livewire change event untuk sync dengan backend
                    $refs.fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                } else {
                    fileName = '{{ $placeholder }}';
                    isSelected = false;
                }
            "
        />

        <!-- Custom file input area -->
        <div 
            x-ref="uploadArea"
            :class="{
                'border-blue-500 bg-blue-50 dark:bg-blue-900/20': isSelected || isDragOver,
                'border-gray-300 dark:border-gray-600': !isSelected && !isDragOver,
                'opacity-50': disabled || isUploading,
                'cursor-pointer': !disabled && !isUploading,
                'cursor-not-allowed': disabled || isUploading
            }"
            class="relative border-2 border-dashed rounded-lg p-4 transition-all duration-200 hover:border-blue-400 hover:bg-gray-50 dark:hover:bg-gray-700/50"
            @if(!$disabled)
                @click="if(!isUploading){ $refs.fileInput.click() }"
                @dragover.prevent="if(!isUploading){ isDragOver = true }"
                @dragleave.prevent="if(!isUploading){ isDragOver = false }"
                @drop.prevent="
                    if(isUploading) return;
                    isDragOver = false;
                    const files = $event.dataTransfer.files;
                    if (files.length > 0) {
                        $refs.fileInput.files = files;
                        // Trigger change dan input events untuk Livewire
                        $refs.fileInput.dispatchEvent(new Event('change'));
                        $refs.fileInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                "
            @endif
        >
            <div class="flex items-center justify-center space-x-3">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    @if($icon === 'photo')
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    @elseif($icon === 'document-text')
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    @elseif($icon === 'table-cells')
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                        </svg>
                    @else
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 2a1 1 0 000 2h6a1 1 0 100-2H9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4a2 2 0 012-2h12a2 2 0 012 2v16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                        </svg>
                    @endif
                </div>

                <!-- Text content -->
                <div class="flex-1 text-center">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        @if(!$disabled)
                            <template x-if="!isUploading">
                                <span>
                                    <span class="text-blue-600 dark:text-blue-400 hover:text-blue-500 cursor-pointer">Choose file</span>
                                    <span class="text-gray-500"> or drag and drop</span>
                                </span>
                            </template>
                            <template x-if="isUploading">
                                <span class="text-gray-500">Uploading… please wait</span>
                            </template>
                        @else
                            <span class="text-gray-500">File input disabled</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate px-2" x-text="fileName">
                    </div>
                    @if($maxSize)
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            Max size: {{ $maxSize }}
                        </div>
                    @endif
                </div>

                <!-- Upload button -->
                <div class="flex-shrink-0">
                    <button 
                        type="button" 
                        @if(!$disabled) @click.stop="if(!isUploading){ $refs.fileInput.click() }" @endif
                        x-bind:disabled="disabled || isUploading"
                        :class="{ 'opacity-50 cursor-not-allowed': disabled || isUploading }"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors @if($disabled) opacity-50 cursor-not-allowed @endif"
                    >
                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Browse
                    </button>
                </div>
            </div>
        </div>
        <!-- Progress Bar -->
        <div x-show="isUploading" x-cloak class="mt-3" aria-live="polite" aria-atomic="true">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-200" :style="`width: ${progress}%`"></div>
            </div>
            <div class="mt-1 text-xs text-gray-600 dark:text-gray-300" x-text="`Mengunggah… ${progress}%`"></div>
        </div>
    </div>

    <!-- Existing file info -->
    @if($existingFile)
        <div class="mt-2 flex items-center text-sm text-gray-600 dark:text-gray-400">
            <svg class="h-4 w-4 mr-1.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Current: </span>
            @if($existingFileUrl)
                <a href="{{ $existingFileUrl }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-500 ml-1 underline">
                    {{ $existingFile }}
                </a>
            @else
                <span class="ml-1 font-medium">{{ $existingFile }}</span>
            @endif
        </div>
    @endif

    <!-- Error message slot -->
    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>