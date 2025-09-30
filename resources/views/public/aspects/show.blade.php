{{-- resources/views/public/aspects/show.blade.php --}}
<x-layouts.public :title="'Aspek'">
    @livewire('public.aspects.show', ['slug' => request('slug')])
</x-layouts.public>
