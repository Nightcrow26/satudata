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


function initSummernoteGlobal() {
  $('.summernote').each(function() {
    // Destroy jika sudah ada
    if ($(this).next('.note-editor').length) {
      $(this).summernote('destroy');
    }
    // Init baru
    $(this).summernote({
      height: 200,
      toolbar: [ /* toolbar Anda */ ],
      callbacks: {
        onChange(contents) {
          // Update field hidden & Livewire property
          $('#'+$(this).attr('id')+'-hidden').val(contents);
          window.Livewire.find(
            document.querySelector('[wire\\:id]').getAttribute('wire:id')
          ).set('deskripsi', contents);
        }
      }
    });
  });
}

// Panggil saat load & setelah Livewire update
document.addEventListener('livewire:load', initSummernoteGlobal);
Livewire.hook('message.processed', initSummernoteGlobal);

function handleSummernoteOnModal() {
  window.addEventListener('show-modal', (event) => {
    // Proses untuk modal dataset dan publikasi
    if (event.detail.id === 'dataset-modal' || event.detail.id === 'publikasi-modal') {
      setTimeout(() => {
        // Hancurkan instance lama, lalu inisialisasi ulang semua .summernote
        $('.summernote').each(function() {
          if ($(this).next('.note-editor').length) {
            $(this).summernote('destroy');
          }
          
          // Inisialisasi Summernote
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
                // Update hidden input
                const hiddenInput = $('#' + $(this).attr('id') + '-hidden');
                if (hiddenInput.length) {
                  hiddenInput.val(contents);
                }
                
                // Sinkron ke Livewire - tentukan property berdasarkan ID
                const wireId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                if (wireId && window.Livewire) {
                  try {
                    const editorId = $(this).attr('id');
                    let propertyName = 'deskripsi'; // default
                    
                    if (editorId.includes('publikasi-konten')) {
                      propertyName = 'konten';
                    } else if (editorId.includes('dataset-deskripsi')) {
                      propertyName = 'deskripsi';
                    }
                    
                    window.Livewire.find(wireId).set(propertyName, contents);
                  } catch (e) {
                    console.error('Error updating Livewire property:', e);
                  }
                }
              }
            }
          });
          
          // Set nilai dari hidden input setelah inisialisasi
          // Delay sedikit untuk memastikan hidden input sudah ter-update dari Livewire
          setTimeout(() => {
            const hiddenInput = $('#' + $(this).attr('id') + '-hidden');
            const content = hiddenInput.val() || '';
            $(this).summernote('code', content);
          }, 100);
        });
      }, 200);
    }
  });

  window.addEventListener('hide-modal', (event) => {
    // Hancurkan semua instance saat modal ditutup
    if (event.detail.id === 'dataset-modal' || event.detail.id === 'publikasi-modal') {
      $('.summernote').each(function() {
        if ($(this).next('.note-editor').length) {
          $(this).summernote('destroy');
        }
      });
    }
  });
}

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
  handleSummernoteOnModal();
});

document.addEventListener('livewire:navigated', () => {
  initCharts();
  registerModalListeners();
  handleSummernoteOnModal();
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
        heightAuto: false,
    });
});

