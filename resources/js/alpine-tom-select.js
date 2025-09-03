window.tomSelectComp = function ({ id, model, placeholder = 'Pilih…', multiple = false, ajax = null }) {
  return {
    id, val: model, ts: null, multiple, ajax, placeholder,
    
    init() {
      console.log('Initializing TomSelect for:', id); // Debug log
      
      this.$nextTick(() => {
        const el = this.$refs.sel
        if (!el) {
          console.error('Select element not found for:', id);
          return;
        }
        
        // Destroy existing instance
        if (el.tomselect) {
          el.tomselect.destroy();
        }

        if (this.multiple) el.setAttribute('multiple', 'multiple')
        else el.removeAttribute('multiple')

        const cfg = {
          create: false,
          allowEmptyOption: true,
          plugins: ['dropdown_input'],
          placeholder: this.placeholder,
          maxOptions: 2000,
          onChange: v => { 
            console.log('TomSelect onChange for', id, ':', v);
            
            // Convert value untuk Livewire
            const newVal = this.multiple ? (v ?? []) : (v || null);
            
            console.log('Setting Livewire model for', id, 'to:', newVal);
            
            // Set value ke Livewire model
            this.val = newVal;
            
            // Force Livewire sync jika perlu
            this.$nextTick(() => {
              console.log('After nextTick - Livewire val for', id, ':', this.val);
              
              // Trigger Livewire update secara eksplisit jika live=false
              if (this.val !== newVal) {
                console.log('Force syncing for', id);
                this.val = newVal;
              }
            });
          },
        }

        if (this.ajax) {
          cfg.valueField = 'id'
          cfg.labelField = 'text'
          cfg.searchField = 'text'
          cfg.load = (q, cb) => {
            if (!q.length) return cb()
            fetch(`${this.ajax}?q=${encodeURIComponent(q)}`)
              .then(r => r.json()).then(cb).catch(() => cb())
          }
        }

        try {
          this.ts = new TomSelect(el, cfg);
          console.log('TomSelect initialized successfully for:', id);

          // Set nilai awal dari Livewire -> TomSelect
          if (this.val) {
            this.ts.setValue(this.val, true);
            console.log('Initial value set for', id, ':', this.val);
          }
        } catch (error) {
          console.error('Error initializing TomSelect for', id, ':', error);
        }

        // Sinkron 2 arah: Livewire -> TomSelect
        this.$watch('val', (v, oldVal) => {
          if (!this.ts) return;
          
          // Prevent infinite loops
          if (v === oldVal) return;
          
          const curr = this.ts.getValue()
          const target = v || (this.multiple ? [] : null)
          
          console.log('Watch triggered for', id, 'curr:', curr, 'target:', target, 'oldVal:', oldVal);
          
          // More robust comparison
          const currStr = Array.isArray(curr) ? curr.join(',') : String(curr || '');
          const targetStr = Array.isArray(target) ? target.join(',') : String(target || '');
          
          if (currStr !== targetStr) {
            console.log('Setting value for', id, 'from', currStr, 'to', targetStr);
            this.ts.setValue(target, true);
          }
        })

        // Re-init saat modal dibuka
        window.addEventListener('show-modal', e => {
          if (e.detail?.id !== 'walidata-modal') return
          console.log('Reinitializing due to modal show for:', id);
          setTimeout(() => {
            if (this.$refs.sel?.tomselect) {
              this.$refs.sel.tomselect.destroy();
            }
            this.init();
          }, 100); // Increase timeout
        })
      })
    },
    
    refreshOptions(opts) {
      console.log('refreshOptions called for', this.id, 'with:', opts);
      
      if (!this.ts) {
        console.warn('TomSelect not ready for refreshOptions:', this.id);
        return;
      }
      
      this.ts.clearOptions()
      opts.forEach(o => this.ts.addOption(o))
      this.ts.refreshOptions(false)
      
      if (this.val) {
        this.ts.setValue(this.val, true)
        console.log('Value restored after refresh for', this.id, ':', this.val);
      }
    }
  }
}