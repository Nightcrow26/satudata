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
  $propModel = $bind ?? $model ?? $attributes->get('bind') ?? $attributes->get('model');
  if (empty($propModel)) {
      throw new \Exception('x-forms.select-tom: prop "model" (atau "bind") wajib diisi.');
  }
  $isLive = filter_var($live, FILTER_VALIDATE_BOOLEAN);
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
      console.log('applyUpdate called for', @js($compId), detail); // Debug log
      
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

      console.log('Applying update:', { opts, v, t }); // Debug log
      console.log('Current options before update:', this.ts.options); // Debug log

      // Pastikan tidak disabled saat set
      if (this.$refs.sel.disabled) { 
        this.$refs.sel.disabled = false; 
        this.ts.enable(); 
      }

      // STEP 1: Clear semua options
      this.ts.clearOptions();
      console.log('Options cleared'); // Debug log
      
      // STEP 2: Add placeholder option jika bukan multiple
      if (!@js($multiple)) {
        this.ts.addOption({ value: '', text: @js($placeholder) });
        console.log('Placeholder added'); // Debug log
      }
      
      // STEP 3: Add new options dari server
      if (opts.length) {
        opts.forEach(o => {
          console.log('Adding option:', o); // Debug log
          this.ts.addOption({ value: o.id, text: o.text });
        });
        console.log('New options added:', opts.length); // Debug log
      }
      
      // STEP 4: Refresh options agar TomSelect aware
      this.ts.refreshOptions(false);
      console.log('Options refreshed, current options:', this.ts.options); // Debug log
      
      // STEP 5: Set value
      if (v) {
        // Double check apakah option sudah ada
        if (!this.ts.options[v]) {
          console.log('Option not found, adding manually:', { v, t }); // Debug log
          this.ts.addOption({ value: v, text: t });
        }
        
        console.log('Setting value to:', v); // Debug log
        this.ts.setValue(v, false); // false = don't trigger onChange
        this.val = v; // Set langsung ke Livewire model
        console.log('Value set, current getValue():', this.ts.getValue()); // Debug log
      } else {
        console.log('Setting empty value'); // Debug log
        this.ts.setValue('', false);
        this.val = null;
      }

      console.log('Update applied successfully for', @js($compId)); // Debug log
      
      // Debug final state
      setTimeout(() => {
        console.log('FINAL STATE - getValue():', this.ts.getValue());
        console.log('FINAL STATE - options:', Object.keys(this.ts.options));
        console.log('FINAL STATE - val (livewire):', this.val);
      }, 100);
    }
  }"
  x-init="init()"

  {{-- Event listener untuk tom-update --}}
  @tom-update.window="
    console.log('tom-update received:', $event.detail, 'for component:', @js($compId)); // Debug log
    if ($event.detail?.id === @js($compId)) {
      applyUpdate($event.detail);
    }
  "
  class="w-100"
>
  @if($label)
    <label class="form-label" for="{{ $compId }}">{{ $label }}</label>
  @endif

  <div wire:ignore>
    <select x-ref="sel" id="{{ $compId }}" class="form-select" {{ $disabled ? 'disabled' : '' }}>
      @unless($ajax)
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $opt)
          <option value="{{ $opt['id'] }}">{{ $opt['text'] }}</option>
        @endforeach
      @endunless
    </select>
  </div>
</div>