<div class="mt-3 border-top pt-3">
  <div class="row align-items-center">
    {{-- Kolom kiri: Showing --}}
    <div class="col-12 col-md-6">
      <small class="text-muted">
        Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} results
      </small>
    </div>

    {{-- Kolom kanan: Pagination center --}}
    <div class="col-12 col-md-6">
      <div class="d-flex justify-content-end">
        {{ $items->links() }}
      </div>
    </div>
  </div>
</div>
