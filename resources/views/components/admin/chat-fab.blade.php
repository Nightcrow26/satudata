@props(['modalId' => 'chatbotModal'])

<style>
  .chat-fab {
    position: fixed;
    right: 18px;
    bottom: 18px;
    width: 56px;
    height: 56px;
    border-radius: 9999px;
    z-index: 1060; /* di atas navbar/tooltip */
  }
  .chat-fab .pulse {
  position: absolute;
  inset: 0;
  border-radius: 9999px;
  border: 2px solid rgba(13,110,253,.35);
  animation: pulse 2s infinite;
  pointer-events: none;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
      box-shadow: 0 0 0 0 rgba(13,110,253,0.35);
    }
    50% {
      transform: scale(1.3); /* memperbesar efek */
      box-shadow: 0 0 0 10px rgba(13,110,253,0);
    }
    100% {
      transform: scale(1);
      box-shadow: 0 0 0 0 rgba(13,110,253,0);
    }
  }

</style>

<!-- FAB -->
<button type="button"
        class="btn btn-primary shadow chat-fab d-flex align-items-center justify-content-center"
        data-bs-toggle="modal"
        data-bs-target="#{{ $modalId }}"
        aria-label="Buka Asisten Data">
  <i class="bi bi-chat-dots fs-4"></i>
  <span class="pulse" aria-hidden="true"></span>
</button>

<!-- Modal Chat -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Asisten Data • SDI HSU</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <!-- Tinggi nyaman untuk percakapan + grafik -->
      <div class="modal-body" style="min-height:50vh; max-height:70vh; overflow:auto">
        <livewire:admin.chat-sdi />
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Saat modal ditutup, minta Alpine di dalam ChatSdi menghentikan SSE (tidak boros koneksi)
  document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById(@json($modalId));
    if (!modalEl) return;
    modalEl.addEventListener('hidden.bs.modal', function () {
      window.dispatchEvent(new CustomEvent('chat-modal-closed'));
    });
  });
</script>
@endpush
