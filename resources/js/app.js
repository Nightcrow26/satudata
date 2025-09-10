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

// Import Summernote SETELAH jQuery dan Bootstrap
import 'summernote/dist/summernote-bs5.min.css';
import 'summernote/dist/summernote-bs5.min.js';
import TomSelect from 'tom-select/dist/js/tom-select.complete.js';
import 'tom-select/dist/css/tom-select.bootstrap5.css';
import './alpine-tom-select'

window.TomSelect = TomSelect; // agar tersedia di inline Alpine


// Fungsi untuk inisialisasi Summernote yang lebih clean
function initSummernote(selector = '.summernote') {
  $(selector).each(function() {
    const $editor = $(this);
    const editorId = $editor.attr('id');
    const $hiddenInput = $('#' + editorId + '-hidden');
    
    // Destroy existing instance
    if ($editor.next('.note-editor').length) {
      $editor.summernote('destroy');
    }
    
    // Initialize Summernote
    $editor.summernote({
      height: 200,
      toolbar: [
        ['style', ['style']],
        ['font', ['bold','italic','underline','clear']],
        ['fontname', ['fontname']],
        ['color', ['color']],
        ['para', ['ul','ol','paragraph']],
        ['table', ['table']],
        ['insert', ['link','picture','video']],
        ['view', ['fullscreen','codeview','help']]
      ],
      callbacks: {
        onChange: function(contents, $editable) {
          // Update hidden input immediately
          $hiddenInput.val(contents);
          
          // Debounce untuk Livewire update
          clearTimeout(this.livewireUpdateTimeout);
          this.livewireUpdateTimeout = setTimeout(() => {
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
        }
      }
    });
    
    // Set initial content dari hidden input
    const initialContent = $hiddenInput.val() || '';
    if (initialContent) {
      $editor.summernote('code', initialContent);
    }
  });
}

// Panggil saat load & setelah Livewire update
document.addEventListener('livewire:load', initSummernote);
Livewire.hook('message.processed', initSummernote);

// Event listeners untuk modal
function handleModalSummernote() {
  window.addEventListener('show-modal', (event) => {
    const modalIds = ['dataset-modal', 'publikasi-modal'];
    if (modalIds.includes(event.detail.id)) {
      // Delay untuk memastikan modal sudah terbuka penuh
      setTimeout(() => {
        initSummernote('.summernote');
      }, 300);
    }
  });

  window.addEventListener('hide-modal', (event) => {
    const modalIds = ['dataset-modal', 'publikasi-modal'];
    if (modalIds.includes(event.detail.id)) {
      // Destroy summernote saat modal ditutup
      $('.summernote').each(function() {
        if ($(this).next('.note-editor').length) {
          $(this).summernote('destroy');
        }
      });
    }
  });
}

// Livewire event listeners
document.addEventListener('livewire:init', () => {
  // Clear content
  Livewire.on('clear-summernote-content', () => {
    $('.summernote').each(function() {
      const $editor = $(this);
      const editorId = $editor.attr('id');
      const $hiddenInput = $('#' + editorId + '-hidden');
      
      $editor.summernote('code', '');
      $hiddenInput.val('');
    });
  });
  
  // Set content
  Livewire.on('set-summernote-content', (event) => {
    const content = event.content || '';
    $('.summernote').each(function() {
      const $editor = $(this);
      const editorId = $editor.attr('id');
      const $hiddenInput = $('#' + editorId + '-hidden');
      
      $editor.summernote('code', content);
      $hiddenInput.val(content);
    });
  });
});

// Tambahkan event listener untuk Livewire updated
document.addEventListener('livewire:updated', () => {
  // Re-initialize summernote setelah Livewire update
  setTimeout(() => {
    $('.summernote').each(function() {
      if (!$(this).next('.note-editor').length) {
        const hiddenInput = $('#' + $(this).attr('id') + '-hidden');
        const initialContent = hiddenInput.val() || '';
        
        $(this).summernote({
          height: 200,
          toolbar: [
            ['style', ['style']],
            ['font', ['bold','italic','underline','clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul','ol','paragraph']],
            ['table', ['table']],
            ['insert', ['link','picture','video']],
            ['view', ['fullscreen','codeview','help']]
          ],
          callbacks: {
            onChange: function(contents) {
              hiddenInput.val(contents);
              const wireId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
              if (wireId && window.Livewire) {
                try {
                  window.Livewire.find(wireId).set('deskripsi', contents);
                } catch (e) {
                  console.error('Error updating Livewire property:', e);
                }
              }
            }
          }
        });
        
        // Set initial content
        if (initialContent) {
          $(this).summernote('code', initialContent);
        }
      }
    });
  }, 100);
});

// Atau menggunakan Livewire listener
document.addEventListener('livewire:init', () => {
  Livewire.on('clear-summernote-content', () => {
      $('#dataset-deskripsi-editor').summernote('code', '');
      $('#dataset-deskripsi-editor-hidden').val('');
      $('#publikasi-deskripsi-editor').summernote('code', '');
      $('#publikasi-deskripsi-editor-hidden').val('');
  });
  
  Livewire.on('set-summernote-content', (event) => {
      const content = event.content || '';
      $('#dataset-deskripsi-editor').summernote('code', content);
      $('#dataset-deskripsi-editor-hidden').val(content);
      $('#publikasi-deskripsi-editor').summernote('code', content);
      $('#publikasi-deskripsi-editor-hidden').val(content);
  });
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
  handleModalSummernote();
});

document.addEventListener('livewire:navigated', () => {
  initCharts();
  registerModalListeners();
  handleModalSummernote();
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

