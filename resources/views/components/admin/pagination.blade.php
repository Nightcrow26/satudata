<div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    {{-- Kolom kiri: Showing --}}
    <div class="text-sm text-gray-600 dark:text-gray-400">
      Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} results
    </div>

    {{-- Kolom kanan: Pagination --}}
    <div class="flex justify-end">
      {{ $items->links() }}
    </div>
  </div>
</div>
