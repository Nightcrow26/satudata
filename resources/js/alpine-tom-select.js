window.tomSelectComp = function ({ id, model, placeholder = 'Pilih…', multiple = false, ajax = null }) {
  return {
    id, val: model, ts: null, multiple, ajax, placeholder,
    isInitializing: false,
    preventAutoOpen: false,
    
    init() {
      this.isInitializing = true;
      
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

        // Detect dark mode
        const isDark = document.documentElement.classList.contains('dark');
        
        const cfg = {
          create: false,
          allowEmptyOption: true,
          plugins: ['dropdown_input'],
          placeholder: this.placeholder,
          maxOptions: 50,
          dropdownParent: 'body',
          render: {
            dropdown: function() {
              const isDark = document.documentElement.classList.contains('dark');
              return `<div class="ts-dropdown ${isDark ? 'dark' : ''}" style="z-index: 10002 !important;"></div>`;
            },
            option: function(data, escape) {
              return `<div class="option">${escape(data.text)}</div>`;
            },
            item: function(data, escape) {
              return `<div class="item">${escape(data.text)}</div>`;
            }
          },
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
          
          // Override open method untuk mencegah auto-open
          const originalOpen = this.ts.open.bind(this.ts);
          const originalShow = this.ts.showDropdown ? this.ts.showDropdown.bind(this.ts) : null;
          const originalFocus = this.ts.focus ? this.ts.focus.bind(this.ts) : null;
          
          this.ts.open = () => {
            const isDark = document.documentElement.classList.contains('dark');
            
            // EXTRA protection for dark mode
            if (isDark && (this.preventAutoOpen || this.isInitializing || globalPreventAutoOpen)) {
              // prevented auto-open in dark mode
              return;
            }
            
            if (this.preventAutoOpen || this.isInitializing || globalPreventAutoOpen) {
              // prevented auto-open
              return;
            }
            
            return originalOpen();
          };
          
          // Override showDropdown method jika ada
          if (originalShow) {
            this.ts.showDropdown = () => {
              const isDark = document.documentElement.classList.contains('dark');
              if (isDark && (this.preventAutoOpen || this.isInitializing || globalPreventAutoOpen)) {
                return;
              }
              return originalShow();
            };
          }
          
          // Override focus method untuk mencegah auto-open via focus
          if (originalFocus) {
            this.ts.focus = () => {
              const isDark = document.documentElement.classList.contains('dark');
              if (isDark && (this.preventAutoOpen || this.isInitializing || globalPreventAutoOpen)) {
                return;
              }
              return originalFocus();
            };
          }
          
          // Set nilai awal dari Livewire -> TomSelect
          if (this.val) {
            this.ts.setValue(this.val, true);
          }
          
          // Langsung terapkan dark mode setelah inisialisasi
          this.updateDarkMode();
          this.applyDarkMode();
          
          // Prevent auto-open dropdown untuk beberapa saat setelah init
          this.preventAutoOpen = true;
          setTimeout(() => {
            this.preventAutoOpen = false;
          }, 1000);
          
        } catch (error) {
          console.error('Error initializing TomSelect for', id, ':', error);
        } finally {
          this.isInitializing = false;
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

        // Modal handling dihapus untuk mencegah dropdown auto-open

        // Event listener untuk update options dan value dari Livewire
        window.addEventListener('tom-update', e => {
          const { target, options, value } = e.detail;
          
          // tom-update received for target
          
          // Check if this is the right target (by name attribute)
          if (target !== this.$refs.sel?.name && target !== this.id) return;
          
          if (!this.ts) {
            console.warn('TomSelect not initialized for target:', target);
            return;
          }
          
          try {
            // processing tom-update for target
            
            // Clear existing options
            this.ts.clearOptions();
            
            // Add new options
            if (options && Array.isArray(options)) {
              options.forEach(option => {
                if (option.id && option.text) {
                  this.ts.addOption({
                    value: option.id,
                    text: option.text
                  });
                }
              });
              // total options added
            }
            
            // Refresh options
            this.ts.refreshOptions(false);
            
            // Set selected value if provided
            if (value !== undefined && value !== null && value !== '') {
              this.ts.setValue(value, true);
              // Also sync with Alpine/Livewire model
              this.val = value;

              // Verify value was set
              setTimeout(() => {
                const actualValue = this.ts.getValue();
                if (actualValue !== value && actualValue !== String(value)) {
                  this.ts.setValue(String(value), false);
                }
              }, 50);
            }
            
            // tom-select update completed for target
          } catch (error) {
            console.error('Error updating TomSelect:', error);
          }
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
    },
    
    // Method to update dark mode styling
    updateDarkMode() {
      if (!this.ts) return;
      
      const isDark = document.documentElement.classList.contains('dark');
      const control = this.ts.control;
      const wrapper = this.ts.wrapper;
      
      // Update control dan wrapper saja, jangan sentuh dropdown
      if (wrapper) {
        if (isDark) {
          wrapper.classList.add('dark-mode');
        } else {
          wrapper.classList.remove('dark-mode');
        }
      }
      
      if (control) {
        if (isDark) {
          control.classList.add('dark-mode');
        } else {
          control.classList.remove('dark-mode');
        }
      }
      
      // Hanya update dropdown jika memang sedang terbuka
      const dropdown = this.ts.dropdown;
      if (dropdown && dropdown.style.display !== 'none' && dropdown.offsetParent !== null) {
        if (isDark) {
          dropdown.classList.add('dark');
        } else {
          dropdown.classList.remove('dark');
        }
      }
    },
    
    // Simple dark mode class application
    applyDarkMode() {
      if (!this.ts) return;
      
      const isDark = document.documentElement.classList.contains('dark');
      if (isDark) {
        this.ts.wrapper?.classList.add('dark-mode');
        this.ts.control?.classList.add('dark-mode');
      }
    }
  }
}

// Global protection untuk mencegah auto-open
let globalPreventAutoOpen = false;

// Listen untuk modal events dan protect Tom Select
document.addEventListener('show.bs.modal', function() {
  globalPreventAutoOpen = true;
  setTimeout(() => {
    globalPreventAutoOpen = false;
  }, 2000);
});

document.addEventListener('shown.bs.modal', function() {
  globalPreventAutoOpen = true;
  setTimeout(() => {
    globalPreventAutoOpen = false;
  }, 1500);
});

// Global dark mode observer for Tom Select
document.addEventListener('DOMContentLoaded', function() {
  // Observer untuk perubahan dark mode
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
  const isDark = document.documentElement.classList.contains('dark');
        
        // Update hanya styling Tom Select, jangan sentuh dropdown functionality
        document.querySelectorAll('.ts-wrapper, .ts-control').forEach(function(element) {
          if (isDark) {
            element.classList.add('dark-mode');
          } else {
            element.classList.remove('dark-mode');
          }
        });
        
        // HANYA update styling dropdown yang BENAR-BENAR sudah terbuka
        // Jangan sentuh apapun yang bisa trigger open/close
        setTimeout(() => {
          document.querySelectorAll('.ts-dropdown').forEach(function(dropdown) {
            // Triple check: dropdown harus benar-benar visible dan aktif
            const computedStyle = window.getComputedStyle(dropdown);
            const isReallyVisible = computedStyle.display !== 'none' && 
                                  computedStyle.visibility !== 'hidden' &&
                                  dropdown.offsetHeight > 0 &&
                                  dropdown.offsetWidth > 0;
            
            if (isReallyVisible) {
              if (isDark) {
                dropdown.classList.add('dark');
              } else {
                dropdown.classList.remove('dark');
              }
            }
          });
        }, 50); // Small delay to avoid interference
      }
    });
  });
  
  observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
  });
});