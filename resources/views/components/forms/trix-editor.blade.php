{{-- resources/views/components/forms/trix-editor.blade.php --}}
@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'placeholder' => 'Start writing...',
    'required' => false,
    'disabled' => false,
    'toolbar' => true,
    'minHeight' => '120px',
])

@php
    $editorId = $id ?? $name ?? 'trix-editor-' . Str::uuid();
    $hiddenInputId = $editorId . '-hidden';
    $required = $required === true || $required === 'true';
    $disabled = $disabled === true || $disabled === 'true';
@endphp

<div class="w-full">
    @if($label)
        <label for="{{ $editorId }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif

    <div 
        x-data="trixEditor()"
        data-editor-id="{{ $editorId }}"
        data-hidden-id="{{ $hiddenInputId }}"
        class="relative"
    >
        <!-- Hidden input for Livewire -->
        <input 
            id="{{ $hiddenInputId }}" 
            name="{{ $name }}"
            type="hidden" 
            {{ $attributes->only(['wire:model', 'wire:model.live', 'wire:model.defer']) }}
        />

        <!-- Trix Editor -->
        <trix-editor 
            id="{{ $editorId }}"
            input="{{ $hiddenInputId }}"
            class="trix-editor block w-full rounded-md border border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-white transition-colors duration-200"
            style="min-height: {{ $minHeight }};"
            placeholder="{{ $placeholder }}"
            @if($disabled) disabled @endif
            @if(!$toolbar) data-trix-toolbar="false" @endif
        ></trix-editor>
    </div>

    <!-- Error message -->
    @error($name)
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

