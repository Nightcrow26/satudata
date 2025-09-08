window.tomSelectComp = function ({ id, model, placeholder = 'Pilih…', multiple = false, ajax = null }) {
  return {
    id, val: model, ts: null, multiple, ajax, placeholder,
    
    init() {
      
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
            // Convert value untuk Livewire
            const newVal = this.multiple ? (v ?? []) : (v || null);
            
            // Set value ke Livewire model
            this.val = newVal;
            
            // Force Livewire sync jika perlu
            this.$nextTick(() => {    
              // Trigger Livewire update secara eksplisit jika live=false
              if (this.val !== newVal) {
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
          // Set nilai awal dari Livewire -> TomSelect
          if (this.val) {
            this.ts.setValue(this.val, true);
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
          
          // More robust comparison
          const currStr = Array.isArray(curr) ? curr.join(',') : String(curr || '');
          const targetStr = Array.isArray(target) ? target.join(',') : String(target || '');
          
          if (currStr !== targetStr) {
            this.ts.setValue(target, true);
          }
        })

        // Re-init saat modal dibuka
        window.addEventListener('show-modal', e => {
          if (e.detail?.id !== 'walidata-modal') return
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
      if (!this.ts) {
        return;
      }
      
      this.ts.clearOptions()
      opts.forEach(o => this.ts.addOption(o))
      this.ts.refreshOptions(false)
      
      if (this.val) {
        this.ts.setValue(this.val, true)
      }
    }
  }
}