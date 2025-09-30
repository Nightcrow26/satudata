{{-- resources/views/public/data/show.blade.php --}}
<x-layouts.public :title="$dataset->title ?? 'Detail Data'">
                <livewire:public.data.detail-content :dataset-id="$dataset->id ?? null" />
</x-layouts.public>
