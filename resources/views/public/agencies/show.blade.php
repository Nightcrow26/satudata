{{-- resources/views/public/agencies/show.blade.php --}}
<x-layouts.public title="Instansi">
    @livewire('public.agencies.show', ['slug' => request()->route('slug')])
</x-layouts.public>
