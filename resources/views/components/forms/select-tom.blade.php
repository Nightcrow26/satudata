{{-- resources/views/components/forms/select-tom.blade.php --}}
@props([
  'id' => null,
  'label' => null,
  'placeholder' => '-- Pilih --',
  'model' => null,
  'bind' => null,
  'options' => [],
  'multiple' => false,
  'ajax' => null,
  'disabled' => false,
  'live' => false,
])

@php
  $compId = $id ?? $attributes->get('id') ?? ('ts-'.\Illuminate\Support\Str::uuid());
  
  // Cek berbagai cara untuk mendapatkan model binding
  $propModel = $bind ?? $model ?? $attributes->get('bind') ?? $attributes->get('model');
  
  // Jika tidak ada prop model, cek wire:model attributes
  if (empty($propModel)) {
      $wireModelLive = $attributes->get('wire:model.live');
      $wireModelDefer = $attributes->get('wire:model.defer'); 
      $wireModel = $attributes->get('wire:model');
      
      $propModel = $wireModelLive ?? $wireModelDefer ?? $wireModel;
      
      // Tentukan apakah live berdasarkan wire:model type
      if ($wireModelLive) {
          $live = true;
      }
  }
  
  if (empty($propModel)) {
      throw new \Exception('x-forms.select-tom: prop "model" (atau "bind") atau wire:model wajib diisi.');
  }
  
  $isLive = filter_var($live, FILTER_VALIDATE_BOOLEAN);
  
  // Remove wire:model attributes dari $attributes untuk mencegah conflict
  $attributes = $attributes->except(['wire:model', 'wire:model.live', 'wire:model.defer']);
  
  // Normalize options format - support both formats:
  // Format 1: [['id' => 'value', 'text' => 'label'], ...]
  // Format 2: ['value' => 'label', ...]
  $normalizedOptions = [];
  foreach ($options as $key => $value) {
      if (is_array($value) && isset($value['id']) && isset($value['text'])) {
          // Format 1: sudah dalam format yang benar
          $normalizedOptions[] = $value;
      } else {
          // Format 2: convert ke format yang diharapkan
          $normalizedOptions[] = ['id' => $key, 'text' => $value];
      }
  }
@endphp

<div
  x-data="{
    // 2) Base helper
    ...tomSelectComp({
      id: @js($compId),
      model: @entangle($propModel){{ $isLive ? '.live' : '.defer' }},
      placeholder: @js($placeholder),
      multiple: @js($multiple),
      ajax: @js($ajax),
    }),

    // Retry-apply update bila TomSelect belum siap
    applyUpdate(detail, attempt = 0) {
      // applyUpdate called for component
      
      if (!this.ts) {
        if (attempt < 20) { // Tingkatkan retry attempts
          setTimeout(() => this.applyUpdate(detail, attempt+1), 100); 
        } else {
          console.error('TomSelect not ready after 20 attempts for', @js($compId));
        }
        return;
      }
      
      const opts = detail.options || [];
      const v    = detail.value ?? null;
      const t    = detail.text  ?? (v ?? '');

  // Applying update: opts/v/t prepared
  // Current options inspected

      // Pastikan tidak disabled saat set
      if (this.$refs.sel.disabled) { 
        this.$refs.sel.disabled = false; 
        this.ts.enable(); 
      }

  // STEP 1: Clear semua options
  this.ts.clearOptions();
      
      // STEP 2: Add placeholder option jika bukan multiple
      if (!@js($multiple)) {
        this.ts.addOption({ value: '', text: @js($placeholder) });
      }
      
      // STEP 3: Add new options dari server
      if (opts.length) {
        opts.forEach(o => {
          this.ts.addOption({ value: o.id, text: o.text });
        });
      }
      
  // STEP 4: Refresh options agar TomSelect aware
  this.ts.refreshOptions(false);
      
      // STEP 5: Set value
      if (v) {
        // Double check apakah option sudah ada
        if (!this.ts.options[v]) {
          this.ts.addOption({ value: v, text: t });
        }

        this.ts.setValue(v, false); // false = don't trigger onChange
        this.val = v; // Set langsung ke Livewire model
      } else {
        this.ts.setValue('', false);
        this.val = null;
      }
      // Update applied successfully for component
      // Final state check performed silently
      setTimeout(() => {
        // noop final verification
      }, 100);
    }
  }"
  x-init="init()"

  {{-- Event listener untuk tom-update --}}
  @tom-update.window="
    // tom-update received for component
    if ($event.detail?.id === @js($compId)) {
      applyUpdate($event.detail);
    }
  "
  class="w-full"
>
  @if($label)
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="{{ $compId }}">{{ $label }}</label>
  @endif

  <div wire:ignore>
    <select x-ref="sel" id="{{ $compId }}" name="{{ $attributes->get('name') }}" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:!bg-gray-800 dark:text-white dark:focus:ring-indigo-400 dark:focus:border-indigo-400" {{ $disabled ? 'disabled' : '' }}>
      @unless($ajax)
        <option value="">{{ $placeholder }}</option>
        @foreach($normalizedOptions as $opt)
          <option value="{{ $opt['id'] }}">{{ $opt['text'] }}</option>
        @endforeach
      @endunless
    </select>
  </div>

  @push('styles')
  <style>
    /* Styling khusus untuk Tom Select dropdown {{ $compId }} */
    .ts-dropdown {
      max-width: 600px !important;
      max-height: 300px !important;
      overflow-y: auto !important;
      border: 1px solid #d1d5db !important;
      border-radius: 0.375rem !important;
      background: white !important;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }
    
    .ts-dropdown .ts-dropdown-content {
      max-height: 280px !important;
      overflow-y: auto !important;
    }
    
    .ts-dropdown-option {
      padding: 8px 12px !important;
      cursor: pointer !important;
      border-bottom: 1px solid #f3f4f6 !important;
      font-size: 14px !important;
      line-height: 1.4 !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      max-width: 580px !important;
    }
    
    .ts-dropdown-option:hover {
      background-color: #f9fafb !important;
    }
    
    .ts-dropdown-option.selected {
      background-color: #e0e7ff !important;
      color: #3730a3 !important;
    }
    
    .ts-dropdown-option:last-child {
      border-bottom: none !important;
    }
    
    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
      .ts-dropdown {
        background: #374151 !important;
        border-color: #4b5563 !important;
      }
      
      .ts-dropdown-option {
        color: #f9fafb !important;
        border-bottom-color: #4b5563 !important;
      }
      
      .ts-dropdown-option:hover {
        background-color: #4b5563 !important;
      }
      
      .ts-dropdown-option.selected {
        background-color: #1e1b4b !important;
        color: #a5b4fc !important;
      }
    }
  </style>
  @endpush
</div>
