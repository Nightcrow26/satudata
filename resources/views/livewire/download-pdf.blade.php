<div>
    {{-- Tombol Download PDF --}}
    <button 
        wire:click="downloadPdf" 
        class="btn btn-sm btn-outline-danger btn-icon" 
        title="Download PDF"
        wire:loading.attr="disabled"
        wire:target="downloadPdf"
    >
        <span wire:loading.remove wire:target="downloadPdf">
            <i class="bi bi-filetype-pdf"></i>
        </span>
        <span wire:loading wire:target="downloadPdf">
            <i class="bi bi-hourglass-split"></i>
        </span>
    </button>

    {{-- Error message display --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
</div>