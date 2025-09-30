import './bootstrap';
import '../css/app.css';

// Import jQuery PERTAMA
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import Bootstrap
import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
window.bootstrap = bootstrap;

// 3) Shim jQuery Tooltip
import Tooltip from 'bootstrap/js/dist/tooltip';
if (window.$ && !$.fn.tooltip) {
  $.fn.tooltip = function(option, ...args) {
    return this.each(function() {
      const instance = Tooltip.getInstance(this)
        || new Tooltip(this, typeof option === 'object' ? option : {});
      if (typeof option === 'string') instance[option](...args);
    });
  };
}

// **Shim Modal**
import Modal from 'bootstrap/js/dist/modal';
if (window.$ && !$.fn.modal) {
  $.fn.modal = function(option, ...args) {
    return this.each(function() {
      const inst = Modal.getInstance(this) || new Modal(this, 
        typeof option === 'object' ? option : {});
      if (typeof option === 'string') inst[option](...args);
    });
  };
}

// Import Trix Editor
import 'trix/dist/trix.css';
import 'trix';

// Pastikan Trix list actions bekerja dengan baik
document.addEventListener('DOMContentLoaded', function() {
  // Fix untuk list functionality di Trix
  document.addEventListener('trix-initialize', function(event) {
    const editor = event.target;
    const toolbar = editor.toolbarElement;
    
    if (toolbar) {
      // Pastikan list buttons dapat diklik
      const listButtons = toolbar.querySelectorAll('[data-trix-attribute="bullet"], [data-trix-attribute="number"]');
      listButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const attribute = this.getAttribute('data-trix-attribute');
          if (attribute === 'bullet' || attribute === 'number') {
            editor.editor.toggleAttribute(attribute);
          }
        });
      });
    }
  });
});

import TomSelect from 'tom-select/dist/js/tom-select.complete.js';
import './alpine-tom-select'
import './alpine-trix-editor'

window.TomSelect = TomSelect; // agar tersedia di inline Alpine

// Event listener untuk reset Tom Select ketika modal ditutup
document.addEventListener('livewire:initialized', () => {
  document.addEventListener('hidden.bs.modal', function (e) {
    // Modal closed: reset Tom Select components
    e.target.querySelectorAll('select[data-tom-select]').forEach(select => {
      if (select.tomselect) {
        select.tomselect.clear();
        // reset performed for select
      }
    });
  });
});

// Fungsi untuk inisialisasi Trix Editor
function initTrix(selector = '.trix-editor') {
  document.querySelectorAll(selector).forEach((element) => {
    const editorId = element.getAttribute('id');
    const hiddenInput = document.getElementById(editorId + '-hidden');
    
    if (!hiddenInput) {
      console.warn(`Hidden input not found for editor: ${editorId}`);
      return;
    }
    
    // Pastikan Trix sudah sepenuhnya dimuat
    if (typeof window.Trix === 'undefined') {
      console.warn('Trix is not loaded yet, retrying...');
      setTimeout(() => initTrix(selector), 100);
      return;
    }
    
    // Set initial content dari hidden input
    const initialContent = hiddenInput.value || '';
    if (initialContent && element.value !== initialContent) {
      element.value = initialContent;
    }
    
    // Debug: log ketika editor siap
    element.addEventListener('trix-initialize', function() {
      // trix editor initialized
      
      // Pastikan toolbar buttons dapat diakses
      const toolbar = element.toolbarElement;
      if (toolbar) {
        const allButtons = toolbar.querySelectorAll('[data-trix-attribute]');
        
        // Specifically check for list buttons
        const bulletButton = toolbar.querySelector('[data-trix-attribute="bullet"]');
        const numberButton = toolbar.querySelector('[data-trix-attribute="number"]');
        
        // no-op debug comments removed
        
        if (bulletButton || numberButton) {
          // list buttons available
        }
      }
    });
    
    // Handle toolbar actions specifically for lists
    element.addEventListener('trix-action-invoke', function(event) {
      const action = event.actionName;
      if (action === 'bulletList' || action === 'numberList') {
        // Ensure list action is properly handled
      }
    });
    
    // Add keyboard shortcuts for lists as fallback
  element.addEventListener('keydown', function(event) {
      // Ctrl/Cmd + Shift + L for bullet list
      if (event.ctrlKey && event.shiftKey && event.key === 'L') {
        event.preventDefault();
        element.editor.toggleAttribute('bullet');
      }
      // Ctrl/Cmd + Shift + O for numbered list  
      if (event.ctrlKey && event.shiftKey && event.key === 'O') {
        event.preventDefault();
        element.editor.toggleAttribute('number');
      }
    });
    
    // Handle content changes
    element.addEventListener('trix-change', function(event) {
      const contents = event.target.value;
      
      // Update hidden input immediately
      hiddenInput.value = contents;
      
      // Debounce untuk Livewire update
      clearTimeout(element.livewireUpdateTimeout);
      element.livewireUpdateTimeout = setTimeout(() => {
        try {
          const wireId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
          const livewireComponent = window.Livewire?.find(wireId);
          
          if (livewireComponent) {
            // Tentukan property berdasarkan ID editor
            let propertyName = 'deskripsi'; // default
            
            if (editorId.includes('publikasi-deskripsi')) {
              propertyName = 'deskripsi';
            } else if (editorId.includes('dataset-deskripsi')) {
              propertyName = 'deskripsi';
            } else if (editorId.includes('publikasi-konten')) {
              propertyName = 'konten';
            }
            
            // Update Livewire property
            livewireComponent.set(propertyName, contents);
          }
        } catch (error) {
          console.error('Error updating Livewire property:', error);
        }
      }, 300); // Debounce 300ms
    });
  });
}

// Panggil saat load & setelah Livewire update
document.addEventListener('livewire:loaded', initTrix);
Livewire.hook('message.processed', initTrix);

// Event listeners untuk modal
function handleModalTrix() {
  window.addEventListener('show-modal', (event) => {
    const modalIds = ['dataset-modal', 'publikasi-modal'];
    if (modalIds.includes(event.detail.id)) {
      // Delay untuk memastikan modal sudah terbuka penuh
      setTimeout(() => {
        initTrix('.trix-editor');
      }, 300);
    }
  });

  window.addEventListener('hide-modal', (event) => {
    const modalIds = ['dataset-modal', 'publikasi-modal'];
    if (modalIds.includes(event.detail.id)) {
      // Reset Trix editors saat modal ditutup
      document.querySelectorAll('.trix-editor').forEach((editor) => {
        if (editor.editor) {
          editor.editor.loadHTML('');
        }
      });
    }
  });
}

// Livewire event listeners
document.addEventListener('livewire:init', () => {
  // Clear content
  Livewire.on('clear-trix-content', () => {
    document.querySelectorAll('.trix-editor').forEach((editor) => {
      const editorId = editor.getAttribute('id');
      const hiddenInput = document.getElementById(editorId + '-hidden');
      
      if (editor.editor) {
        editor.editor.loadHTML('');
      }
      if (hiddenInput) {
        hiddenInput.value = '';
      }
    });
  });
  
  // Set content
  Livewire.on('set-trix-content', (event) => {
    const content = event.content || '';
    document.querySelectorAll('.trix-editor').forEach((editor) => {
      const editorId = editor.getAttribute('id');
      const hiddenInput = document.getElementById(editorId + '-hidden');
      
      if (editor.editor) {
        editor.editor.loadHTML(content);
      }
      if (hiddenInput) {
        hiddenInput.value = content;
      }
    });
  });
});

// Tambahkan event listener untuk Livewire updated
document.addEventListener('livewire:updated', () => {
  // Re-initialize Trix setelah Livewire update
  setTimeout(() => {
    initTrix('.trix-editor');
  }, 100);
});



function initCharts() {
    const { lineLabels, lineData, donutLabels, donutData } = window.dashboardData || {};

    try {
        const lineEl = document.getElementById('chartLine');
        if (lineEl && lineLabels && lineData) {
            if (lineEl._chart) lineEl._chart.destroy();
            lineEl._chart = new Chart(lineEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: lineLabels,
                    datasets: [{
                        label: 'Jumlah Data per Bulan',
                        data: lineData,
                        fill: true,
                        tension: 0.4,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    } catch (e) {
        console.error('Gagal inisialisasi Line Chart:', e);
    }

    try {
        const donutEl = document.getElementById('chartDonut');
        if (donutEl && donutLabels && donutData) {
            if (donutEl._chart) donutEl._chart.destroy();
            donutEl._chart = new Chart(donutEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutData,
                        backgroundColor: [
                            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                }
            });
        }
    } catch (e) {
        console.error('Gagal inisialisasi Donut Chart:', e);
    }
}

function registerModalListeners() {
    Livewire.on('show-modal', ({ id }) => {
        const el = document.getElementById(id);
        if (!el) return;
        bootstrap.Modal.getOrCreateInstance(el).show();
    });
    
    Livewire.on('hide-modal', ({ id }) => {
        const el = document.getElementById(id);
        if (!el) return;
        bootstrap.Modal.getOrCreateInstance(el).hide();
    });
}

document.addEventListener('DOMContentLoaded', () => {
  initCharts();
  registerModalListeners(); 
  handleModalTrix();
});

document.addEventListener('livewire:navigated', () => {
  initCharts();
  registerModalListeners();
  handleModalTrix();
});

// Modifikasi event listener swal untuk tidak menutup modal jika ada flag keepModalOpen
window.addEventListener('swal', e => {
    // Hanya tutup modal jika tidak ada flag keepModalOpen
    if (!e.detail.keepModalOpen) {
        document.querySelectorAll('.modal.show').forEach(el => {
            bootstrap.Modal.getOrCreateInstance(el)?.hide();
        });
    }

    Swal.fire({
        title: e.detail.title,
        text: e.detail.text,
        icon: e.detail.icon,
        toast: true,
        position: e.detail.position ?? 'bottom-end',
        timer: e.detail.timer ?? 3000,
        timerProgressBar: true,
        showConfirmButton: false,
        //heightAuto: false,
    });
});

// ===== GLOBAL THEME MANAGEMENT =====
(function () {
    function apply(mode) {
        const root = document.documentElement;

        if (mode === 'system') {
            localStorage.removeItem('theme');
            mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        const isDark = mode === 'dark';
        root.classList.toggle('dark', isDark);
        localStorage.setItem('theme', isDark ? 'dark' : 'light');

        // Bootstrap theme sync for admin layout compatibility
        root.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme: isDark ? 'dark' : 'light' }
        }));

        // Force repaint untuk memastikan Tailwind classes ter-apply
        requestAnimationFrame(() => {
            document.body.style.display = 'none';
            document.body.offsetHeight; // trigger reflow
            document.body.style.display = '';
            
            // Dispatch Livewire refresh untuk komponen dinamis
            if (window.Livewire && window.Livewire.emit) {
                window.Livewire.emit('themeChanged', { theme: isDark ? 'dark' : 'light' });
            }
        });
        
        // sinkronkan semua switch legacy untuk admin
        document.querySelectorAll('.js-theme-switch').forEach(sw => {
            sw.checked = !isDark; // REVERSED: checked = light mode
        });
    }

    // API global: bisa dipakai di tombol manapun
    window.__setTheme = apply;
    window.__toggleTheme = function () {
        const isDark = document.documentElement.classList.contains('dark');
        apply(isDark ? 'light' : 'dark');
    };

    // Legacy function for backward compatibility
    window.applyTheme = function(theme) {
        apply(theme);
    };

    // Sinkron perubahan preferensi sistem jika user tidak memaksa tema
    const mql = window.matchMedia('(prefers-color-scheme: dark)');
    const syncSystem = () => {
        if (!localStorage.getItem('theme')) {
            document.documentElement.classList.toggle('dark', mql.matches);
            window.dispatchEvent(new CustomEvent('theme-changed', {
                detail: { theme: mql.matches ? 'dark' : 'light' }
            }));
        }
    };
    try { mql.addEventListener('change', syncSystem); }
    catch { mql.addListener?.(syncSystem); }

    // Komponen Alpine yang dipanggil dari x-data="darkModeToggle()"
    if (!window.darkModeToggle) {
      window.darkModeToggle = () => ({
          isDark: false,
          init() {
              this.isDark = document.documentElement.classList.contains('dark');
              window.addEventListener('theme-changed', (e) => {
                  this.isDark = e.detail.theme === 'dark';
              });
          },
          toggle() { window.__toggleTheme(); }
      });
    }
})();

// ===== CHAT BOX FUNCTIONALITY =====
// NOTE: Moved to component-specific scripts in templates
// Commented out to avoid conflicts with component-specific implementations
function encodeHistoryBase64(history){ try{ return btoa(unescape(encodeURIComponent(JSON.stringify(history)))); }catch{ return ''; } }

  // Render chart untuk bubble permanen
  window.renderChart = (viz, data, canvasId) => ({
    chart:null,
    render(){
      const el=document.getElementById(canvasId);
      if(!el || !viz || !Array.isArray(data) || !data.length) return;
      const labels=data.map(d=>d[viz.x]);
      const datasets=(viz.y||[]).map(y=>({label:y,data:data.map(d=>d[y])}));
      if(this.chart) this.chart.destroy();
      this.chart=new Chart(el,{type:viz.type||'line',data:{labels,datasets},
        options:{responsive:true,plugins:{title:{display:!!viz?.options?.title,text:viz?.options?.title||''}}}});
    }
  });

  // Controller chat (Alpine)
  window.chatBox = (hasHistoryInit=false)=>({
    es:null, streaming:false, hasAnyChat:hasHistoryInit,
    pending:{answer:'',sources:[],viz:null,data:[],canvasId:'pendingChart'},

    init(){
      // Mulai SSE dari Livewire
      window.addEventListener('chat-start',(e)=>{
        const msg=e.detail?.message||''; const h=e.detail?.history||[];
        if(!msg) return; this.start(msg,h); this.hasAnyChat=true;
      });
      // Tutup stream jika modal ditutup
      window.addEventListener('chat-modal-closed', this.stop.bind(this));
    },

    start(message,history){
      this.stop(); this.streaming=true;
      this.pending={answer:'',sources:[],viz:null,data:[],canvasId:'pendingChart'};
      this.$nextTick(()=>this.scrollBottom());

      const base=window.SDI_STREAM_URL || '/api/chatbot/stream';
      const h=encodeHistoryBase64(history||[]);
      const url=`${base}?message=${encodeURIComponent(message)}${h?('&h='+encodeURIComponent(h)):""}`;
      this.es=new EventSource(url);

      this.es.addEventListener('delta',ev=>{
        const {text}=JSON.parse(ev.data||'{}');
        if(typeof text==='string'){ this.pending.answer+=text; this.scrollBottom(); }
      });

      this.es.addEventListener('final', async (ev) => {
        let data = {};
        try { data = JSON.parse(ev.data || '{}'); } catch (e) { console.error('parse final', e); }

        // 1) viz -> array
        const viz = Array.isArray(data.viz) ? data.viz : (data.viz ? [data.viz] : []);

        // 2) data_preview -> dukung 2 bentuk:
        //    a) array of {source, rows: [...]}
        //    b) array of rows (flat)
        let preview = data.data_preview ?? [];
        if (!Array.isArray(preview)) preview = [];
        if (preview.length && !('rows' in (preview[0] || {}))) {
          // flat → bungkus sebagai satu sumber
          preview = [{ rows: preview }];
        }

        // 3) sematkan headline bila ada
        const content = data.headline
          ? `${data.headline}\n\n${data.answer || ''}`
          : (data.answer || '');

        await this.$wire.appendAssistant(
          content,
          data.sources || [],
          viz,
          preview,
          data.insights || []
        );

        // bereskan state UI
        this.streaming = false; // jika Anda pakai flag ini
        this.es?.close(); this.es = null;
        this.$nextTick(() => this.scrollBottom());
      });


      this.es.addEventListener('error',()=>{ this.streaming=false; this.es?.close(); this.es=null; });
    },

    stop(){ if(this.es){ this.es.close(); this.es=null; } this.streaming=false; },

    scrollBottom(){ const box=this.$refs.scrollArea; if(!box) return; box.scrollTop=box.scrollHeight; }
});

// ===== CHART VISUALIZATION COMPONENT =====
if (!window.chartViz) {
    window.chartViz = () => ({
        chart: null,
        init() {
            // Initialize chart when component loads
        },
        renderChart(data, viz) {
            if (!data || !viz || !window.Chart) return;
            
            const el = this.$el.querySelector('canvas');
            if (!el) return;
            
            const labels = data.map(d => d[viz.x] || '');
            const datasets = (viz.y || []).map(y => ({
                label: y,
                data: data.map(d => d[y] || 0),
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                fill: false
            }));
            
            if (this.chart) {
                this.chart.destroy();
            }
            
            this.chart = new Chart(el, {
                type: viz.type || 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: !!viz?.options?.title,
                            text: viz?.options?.title || ''
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        destroy() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
        }
    });
}

// ===== NAVIGATION CLEANUP =====
// Turbo Drive configuration for better navigation
document.addEventListener('turbo:before-visit', () => {
    // Cleanup any active charts before navigation
    if (window.activeCharts) {
        window.activeCharts.forEach(chart => chart.destroy());
        window.activeCharts = [];
    }
});

// Pastikan tema tetap konsisten setelah Livewire wire:navigate
document.addEventListener('livewire:navigated', () => {
    try {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const useDark = saved ? (saved === 'dark') : prefersDark;

        // Re-apply kelas .dark di <html> jika sempat hilang
        document.documentElement.classList.toggle('dark', useDark);

        // Beritahu komponen (Alpine/Livewire) yang memantau event ini
        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { theme: useDark ? 'dark' : 'light' }
        }));
    } catch (_) { }
});
